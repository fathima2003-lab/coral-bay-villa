/**
 * Coral Bay Villa — client-side behaviour
 * ---------------------------------------
 *  1. Date pickers: block invalid ranges, show number of nights
 *  2. Form validation (Bootstrap styles + custom rules)
 *  3. Booking form: live price calculation (seasonal rates + long-stay discount)
 *  4. AJAX availability check (backend/api/availability.php)
 *  5. Small UI helpers: gallery lightbox, confirm dialogs, password tools,
 *     character counter, price slider label, image preview
 *
 * All server rules are repeated in PHP — JavaScript only improves the experience.
 */
(function () {
    'use strict';

    const BASE_URL = document.body.dataset.baseUrl || '';
    const DAY_MS = 24 * 60 * 60 * 1000;

    /* ================= Date helpers ================= */

    /** Parse "YYYY-MM-DD" as a UTC date (avoids time-zone / DST surprises). */
    function parseDate(value) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;
        const [y, m, d] = value.split('-').map(Number);
        const date = new Date(Date.UTC(y, m - 1, d));
        return date.getUTCMonth() === m - 1 ? date : null;   // rejects 2026-02-31
    }

    function toISO(date) {
        return date.toISOString().slice(0, 10);
    }

    function todayISO() {
        const now = new Date();
        return toISO(new Date(Date.UTC(now.getFullYear(), now.getMonth(), now.getDate())));
    }

    function nightsBetween(inValue, outValue) {
        const a = parseDate(inValue), b = parseDate(outValue);
        if (!a || !b) return 0;
        return Math.round((b - a) / DAY_MS);
    }

    function formatMoney(amount, currency) {
        return (currency || 'LKR') + ' ' + Number(amount).toLocaleString('en-US', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }

    function prettyDate(value) {
        const d = parseDate(value);
        return d ? d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC' }) : value;
    }

    /* ================= 1. Date range pickers ================= */

    /**
     * Validates a check-in / check-out pair and shows feedback.
     * Returns the number of nights, or 0 if the range is invalid.
     */
    function validateDateRange(form) {
        const inInput  = form.querySelector('.js-check-in');
        const outInput = form.querySelector('.js-check-out');
        const label    = form.querySelector('.js-nights-label');
        if (!inInput || !outInput) return 0;

        const maxNights = 30;
        const today = todayISO();
        let message = '';

        inInput.setCustomValidity('');
        outInput.setCustomValidity('');

        if (!parseDate(inInput.value)) {
            inInput.setCustomValidity('Choose a check-in date.');
        } else if (inInput.value < today) {
            inInput.setCustomValidity('Check-in cannot be in the past.');
            message = 'Check-in cannot be in the past.';
        }

        const nights = nightsBetween(inInput.value, outInput.value);
        if (!parseDate(outInput.value)) {
            outInput.setCustomValidity('Choose a check-out date.');
        } else if (nights <= 0) {
            outInput.setCustomValidity('Check-out must be after check-in.');
            message = 'Check-out must be after check-in.';
        } else if (nights > maxNights) {
            outInput.setCustomValidity('Maximum stay is ' + maxNights + ' nights.');
            message = 'Maximum stay is ' + maxNights + ' nights.';
        }

        const valid = inInput.validity.valid && outInput.validity.valid;
        if (label) {
            label.textContent = valid
                ? nights + ' night' + (nights > 1 ? 's' : '') + ' · ' + prettyDate(inInput.value) + ' → ' + prettyDate(outInput.value)
                : message;
            label.classList.toggle('text-danger', !valid && message !== '');
        }
        return valid ? nights : 0;
    }

    function initDateForms() {
        document.querySelectorAll('.js-date-form').forEach(function (form) {
            const inInput  = form.querySelector('.js-check-in');
            const outInput = form.querySelector('.js-check-out');
            if (!inInput || !outInput) return;

            inInput.min = inInput.min || todayISO();

            function syncMin() {
                const start = parseDate(inInput.value);
                if (!start) return;
                const next = toISO(new Date(start.getTime() + DAY_MS));
                outInput.min = next;
                // If check-out is now before check-in, move it to the next day
                if (!parseDate(outInput.value) || outInput.value <= inInput.value) {
                    outInput.value = next;
                }
            }

            inInput.addEventListener('change', function () { syncMin(); validateDateRange(form); form.dispatchEvent(new Event('stay-change')); });
            outInput.addEventListener('change', function () { validateDateRange(form); form.dispatchEvent(new Event('stay-change')); });

            syncMin();
            validateDateRange(form);

            // Forms that only use dates (no .js-validate) still need blocking
            if (!form.classList.contains('js-validate')) {
                form.addEventListener('submit', function (ev) {
                    if (!validateDateRange(form)) {
                        ev.preventDefault();
                        form.classList.add('was-validated');
                        (form.querySelector(':invalid') || inInput).focus();
                    }
                });
            }
        });
    }

    /* ================= 2. Form validation ================= */

    function runCustomRules(form) {
        // "Confirm password" must match its partner field
        form.querySelectorAll('[data-match]').forEach(function (field) {
            const other = form.querySelector('#' + field.dataset.match);
            field.setCustomValidity(other && field.value !== other.value ? 'Passwords do not match.' : '');
        });

        // Trim-sensitive text fields (no all-space names)
        form.querySelectorAll('input[type="text"][required], textarea[required]').forEach(function (field) {
            field.setCustomValidity(field.value !== '' && field.value.trim() === '' ? 'This field cannot be blank.' : '');
        });

        if (form.classList.contains('js-date-form')) validateDateRange(form);
        if (form.classList.contains('js-booking-form')) checkGuests(form);
    }

    function initValidation() {
        document.querySelectorAll('form.js-validate').forEach(function (form) {
            form.addEventListener('submit', function (ev) {
                runCustomRules(form);
                if (!form.checkValidity()) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) firstInvalid.focus();
                }
                form.classList.add('was-validated');
            });

            // Re-check live after the first submit attempt
            form.addEventListener('input', function () {
                if (form.classList.contains('was-validated')) runCustomRules(form);
            });

            // Clear server-side error styling as soon as the user edits the field
            form.querySelectorAll('.is-invalid').forEach(function (field) {
                field.addEventListener('input', function () { field.classList.remove('is-invalid'); }, { once: true });
            });
        });
    }

    /* ================= 3. Booking form: live price ================= */

    function checkGuests(form) {
        const select = form.querySelector('.js-room-select');
        const guests = form.querySelector('.js-guests');
        if (!select || !guests) return true;

        const max = Number(select.selectedOptions[0].dataset.max);
        guests.max = max;
        const n = Number(guests.value);
        const fb = form.querySelector('.js-guests-feedback');
        if (!Number.isInteger(n) || n < 1) {
            guests.setCustomValidity('At least 1 guest.');
            if (fb) fb.textContent = 'At least 1 guest.';
        } else if (n > max) {
            guests.setCustomValidity('This room sleeps up to ' + max + ' guests.');
            if (fb) fb.textContent = 'This room sleeps up to ' + max + ' guests.';
        } else {
            guests.setCustomValidity('');
        }
        return guests.validity.valid;
    }

    /** Same rules as calculate_booking_price() in backend/core/functions.php. */
    function calculatePrice(base, checkIn, checkOut, cfg) {
        const start = parseDate(checkIn), end = parseDate(checkOut);
        const seasonal = {};
        let subtotal = 0, nights = 0;

        for (let t = start.getTime(); t < end.getTime(); t += DAY_MS) {
            const iso = toISO(new Date(t));
            let multiplier = 1;
            for (const s of cfg.seasons) {
                if (iso >= s.start && iso <= s.end) {
                    multiplier = s.multiplier;
                    seasonal[s.name] = (seasonal[s.name] || 0) + 1;
                    break;
                }
            }
            subtotal += Math.round(base * multiplier * 100) / 100;
            nights++;
        }
        const discount = nights >= cfg.longStayNights ? Math.round(subtotal * cfg.longStayDiscount * 100) / 100 : 0;
        return { nights, subtotal, discount, total: subtotal - discount, seasonal };
    }

    function initBookingForm() {
        const form = document.querySelector('.js-booking-form');
        if (!form) return;

        const cfg     = JSON.parse(form.dataset.pricing);
        const select  = form.querySelector('.js-room-select');
        const guests  = form.querySelector('.js-guests');
        const summary = form.querySelector('.js-summary');
        const inInput = form.querySelector('.js-check-in');
        const outInput = form.querySelector('.js-check-out');

        function row(label, value, cls) {
            const tr = document.createElement('tr');
            if (cls) tr.className = cls;
            const th = document.createElement('td');
            const td = document.createElement('td');
            th.textContent = label;
            td.textContent = value;
            td.className = 'text-end';
            tr.append(th, td);
            return tr;
        }

        function update() {
            checkGuests(form);
            const nights = validateDateRange(form);
            const option = select.selectedOptions[0];
            const base   = Number(option.dataset.price);

            summary.replaceChildren();   // clear old rows (DOM manipulation)

            if (!nights) {
                summary.append(row('Choose valid dates to see the total.', ''));
                return;
            }

            const q = calculatePrice(base, inInput.value, outInput.value, cfg);
            summary.append(row('Room', option.textContent.split('—')[0].trim()));
            summary.append(row('Base rate', formatMoney(base, cfg.currency) + ' / night'));
            summary.append(row('Nights', String(q.nights)));
            Object.keys(q.seasonal).forEach(function (name) {
                const s = cfg.seasons.find(x => x.name === name);
                const pct = Math.round((s.multiplier - 1) * 100);
                summary.append(row(name + ' (' + (pct >= 0 ? '+' : '') + pct + '%)', q.seasonal[name] + ' night(s)', 'text-muted small'));
            });
            summary.append(row('Subtotal', formatMoney(q.subtotal, cfg.currency)));
            if (q.discount > 0) {
                summary.append(row('Long-stay discount (' + Math.round(cfg.longStayDiscount * 100) + '%)', '− ' + formatMoney(q.discount, cfg.currency), 'text-success'));
            } else {
                const more = cfg.longStayNights - q.nights;
                summary.append(row('Stay ' + more + ' more night(s) to save ' + Math.round(cfg.longStayDiscount * 100) + '%', '', 'text-muted small'));
            }
            summary.append(row('Estimated total', formatMoney(q.total, cfg.currency), 'summary-total'));
        }

        [select, guests].forEach(el => el.addEventListener('input', update));
        select.addEventListener('change', update);
        form.addEventListener('stay-change', update);
        update();

        // AJAX availability for the booking form too
        attachAvailability(form, () => select.value, () => guests.value);
    }

    /* ================= 4. AJAX availability ================= */

    function attachAvailability(form, getRoomId, getGuests) {
        const box = form.querySelector('.js-availability-result');
        if (!box) return;
        let timer = null, controller = null;

        function show(type, icon, text) {
            box.className = 'availability-result js-availability-result alert alert-' + type + ' py-2 small mb-0';
            box.innerHTML = '';
            const i = document.createElement('i');
            i.className = 'bi ' + icon + ' me-1';
            box.append(i, document.createTextNode(text));
        }

        function check() {
            const inVal = form.querySelector('.js-check-in').value;
            const outVal = form.querySelector('.js-check-out').value;
            if (!validateDateRange(form)) { box.className = 'availability-result js-availability-result'; box.textContent = ''; return; }

            if (controller) controller.abort();
            controller = new AbortController();
            show('light', 'bi-hourglass-split', 'Checking availability…');

            const params = new URLSearchParams({ room_id: getRoomId(), check_in: inVal, check_out: outVal, guests: getGuests() });
            fetch(BASE_URL + '/backend/api/availability.php?' + params, { signal: controller.signal, headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(function (data) {
                    if (!data.ok) return show('warning', 'bi-exclamation-triangle', data.message);
                    if (data.available) {
                        show('success', 'bi-check-circle', data.message + ' ' + data.nights + ' night(s) — ' + data.quote.formatted);
                    } else {
                        show('danger', 'bi-x-circle', data.message);
                    }
                })
                .catch(function (err) {
                    if (err.name !== 'AbortError') show('secondary', 'bi-wifi-off', 'Could not check availability right now.');
                });
        }

        function schedule() { clearTimeout(timer); timer = setTimeout(check, 300); }   // debounce
        form.addEventListener('stay-change', schedule);
        form.addEventListener('change', function (ev) {
            if (ev.target.matches('select, .js-guests')) schedule();
        });
        check();
    }

    function initRoomAvailability() {
        const form = document.querySelector('.js-availability-form');
        if (!form) return;
        attachAvailability(form, () => form.dataset.roomId, () => form.querySelector('[name="guests"]').value);
    }

    /* ================= 5. UI helpers ================= */

    function initGallery() {
        const modalEl = document.getElementById('galleryModal');
        if (!modalEl || !window.bootstrap) return;
        const modal = new bootstrap.Modal(modalEl);
        const img = document.getElementById('galleryImage');
        const caption = document.getElementById('galleryCaption');

        document.querySelectorAll('.js-gallery').forEach(function (btn) {
            btn.addEventListener('click', function () {
                img.src = btn.dataset.src;
                img.alt = btn.dataset.caption;
                caption.textContent = btn.dataset.caption;
                modal.show();
            });
        });
    }

    /** Ask before destructive actions. Uses the submit event so the clicked button's value is kept. */
    function initConfirmForms() {
        document.querySelectorAll('form.js-confirm').forEach(function (form) {
            form.addEventListener('submit', function (ev) {
                if (!window.confirm(form.dataset.confirm || 'Are you sure?')) ev.preventDefault();
            });
        });
    }

    function initPasswordTools() {
        document.querySelectorAll('.js-toggle-password').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const input = document.getElementById(btn.dataset.target);
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        });

        const pwd = document.querySelector('.js-register-form #password');
        const bar = document.querySelector('.js-strength-bar');
        const text = document.querySelector('.js-strength-text');
        if (!pwd || !bar) return;

        pwd.addEventListener('input', function () {
            const v = pwd.value;
            let score = 0;
            if (v.length >= 8) score++;
            if (v.length >= 12) score++;
            if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
            if (/\d/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            const levels = [
                ['0%', '', 'Use 8+ characters with letters and numbers.'],
                ['20%', 'weak', 'Weak'], ['40%', 'weak', 'Weak'],
                ['60%', 'fair', 'Fair'], ['80%', 'good', 'Good'], ['100%', 'strong', 'Strong']
            ];
            const [w, cls, label] = levels[v ? Math.max(1, score) : 0];
            bar.style.width = w;
            bar.className = 'js-strength-bar ' + cls;
            text.textContent = v ? 'Password strength: ' + label : label;
        });
    }

    function initCharCounters() {
        document.querySelectorAll('.js-char-count').forEach(function (area) {
            const out = area.closest('div').querySelector('.js-char-out');
            if (!out) return;
            const update = () => { out.textContent = area.value.length; };
            area.addEventListener('input', update);
            update();
        });
    }

    function initPriceRange() {
        const range = document.querySelector('.js-price-range');
        const out = document.querySelector('.js-range-out');
        if (!range || !out) return;
        const update = () => {
            out.textContent = Number(range.value) >= Number(range.max) ? 'Any' : formatMoney(range.value);
        };
        range.addEventListener('input', update);
        update();
    }

    function initImagePreview() {
        const input = document.querySelector('.js-image-input');
        const preview = document.querySelector('.js-image-preview');
        if (!input || !preview) return;
        input.addEventListener('change', function () {
            const file = input.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                alert('Image must be 2 MB or smaller.');
                input.value = '';
                return;
            }
            preview.src = URL.createObjectURL(file);
        });
    }

    /* ================= Start ================= */
    document.addEventListener('DOMContentLoaded', function () {
        initDateForms();
        initValidation();
        initBookingForm();
        initRoomAvailability();
        initGallery();
        initConfirmForms();
        initPasswordTools();
        initCharCounters();
        initPriceRange();
        initImagePreview();
    });
})();
