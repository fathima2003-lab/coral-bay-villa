# Coral Bay Villa — Coastal Villa & Room Booking System

**Module:** IT1208 – Web Technologies · Continuous Assessment 2 (Individual)
**Student:** Keshomigha · 22CSE0392
**Stack:** HTML5 · CSS3 · Bootstrap 5 · JavaScript (vanilla) · PHP 8 (PDO) · MySQL / MariaDB

A dynamic web application for **Coral Bay Villa**, a fictional boutique villa in **Mirissa, Sri Lanka**.
Visitors explore the villa, browse rooms, check availability and send booking requests.
The administrator manages rooms, views reservations and confirms or cancels bookings.

---

## 1. Implemented features

### Guest / visitor side
| Page | Features |
|---|---|
| **Home** (`index.php`) | Hero with villa intro, quick availability search, facilities, featured rooms, image gallery with lightbox, guest reviews, interactive map, "Book Now" buttons |
| **Rooms** (`rooms.php`) | All active rooms with image, description, facilities, max guests, price/night; **filters** by type, max price (slider), facility, guests; sorting |
| **Room details** (`room.php`) | Image carousel, full facilities, **live AJAX availability + price check**, reviews, review form for completed stays |
| **Availability search** (`search.php`) | Check-in / check-out / guests → only rooms with **no overlapping confirmed booking**; total price per room for the stay |
| **Booking form** (`book.php`) | Guest name, email, contact number, room, dates, guests, special requests; **live price breakdown** (nights, seasonal rates, long-stay discount) |
| **My Bookings** (`my_bookings.php`) | Guest's own bookings only, status badges (Pending / Confirmed / Cancelled), filter tabs, **cancel pending bookings** |
| **Register / Login / Logout** | Password hashing, sessions, redirect back to the page you were on |

### Administrator side (`admin/`)
| Page | Features |
|---|---|
| **Dashboard** | Total bookings, pending, confirmed revenue, monthly occupancy %, active rooms, guests; **Chart.js bar chart** of bookings per month; revenue per room; quick confirm/cancel; arrivals in the next 14 days |
| **Bookings** | All reservations with filters (status, room, guest, date); **confirm / cancel**; "Clash" warning for requests that overlap a confirmed booking |
| **Rooms** | View all rooms, edit, activate/deactivate, delete (only rooms without bookings) |
| **Add / Edit room** | All room fields, facility checkboxes, **image upload** (JPG/PNG/WebP, 2 MB) with live preview |

### Additional (optional) features
- AJAX room-availability checking (`backend/api/availability.php`, JSON)
- Search & filter rooms by price, facilities, room type and guests
- Image gallery (lightbox) + per-room carousel
- Administrator dashboard with booking statistics and chart
- **Seasonal pricing** (Festive Peak +25%, High Season +15%, Green Season −15%) and **10% long-stay discount** (7+ nights)
- Guest reviews and star ratings (only for completed, confirmed stays)
- Interactive location map (OpenStreetMap)
- Password strength meter and show/hide password

---

## 2. Requirement checklist

| Requirement | Where |
|---|---|
| Responsive layout (desktop / tablet / mobile) | Bootstrap grid + media queries in `frontend/assets/css/style.css` |
| JS form validation | `frontend/assets/js/main.js` → `initValidation()`, registration + booking forms |
| Prevent invalid date ranges | `validateDateRange()` – past dates, check-out ≤ check-in, > 30 nights |
| Nights + estimated cost | `calculatePrice()` in `main.js` |
| DOM manipulation | price summary table, availability messages, nights label, gallery, char counter |
| ≥ 3 related tables | 6 tables: `users`, `rooms`, `bookings`, `room_images`, `reviews`, `seasonal_rates` |
| Password hashing | `password_hash()` / `password_verify()` in `backend/controllers/register.php`, `login.php` |
| Sessions + logout | `backend/core/init.php`, `login_user()`, `backend/controllers/logout.php` |
| Room CRUD | `backend/controllers/admin/rooms.php`, `room_form.php` |
| Booking requests stored/retrieved | `backend/controllers/book.php`, `my_bookings.php`, `admin/bookings.php` |
| Confirm / cancel bookings | `backend/core/admin_actions.php` |
| **Prevent overlapping confirmed bookings** | `room_is_available()` in `backend/core/functions.php`; checked on booking **and** again when the admin confirms (in a transaction with row locks) |
| Server-side validation & price | `backend/controllers/book.php` re-validates everything; `calculate_booking_price()` |
| Prepared statements | PDO with `ATTR_EMULATE_PREPARES = false` everywhere |
| Access control | `require_login()`, `require_guest_account()`, `require_admin()` |
| Guests can't see/cancel others' bookings | every guest query filters by `user_id` from the session |

### Security measures
- PDO prepared statements (SQL injection)
- `htmlspecialchars()` on all output via `e()` (XSS)
- CSRF token on every POST form
- `session_regenerate_id()` on login and logout (session fixation)
- HttpOnly + SameSite session cookie
- Same error message for unknown email / wrong password
- Uploaded images checked by real image type and size, renamed randomly
- Server never trusts the price sent by the browser — total is recalculated in PHP

---

## 3. Software requirements
- **XAMPP 8.x** (Apache + PHP 8.0 or newer + MariaDB/MySQL) — or WAMP/MAMP equivalent
- A modern browser (Chrome, Edge, Firefox)
- No Composer or Node needed. Bootstrap, Bootstrap Icons and Chart.js are included in `frontend/assets/vendor/`, so the site works offline (only Google Fonts and the map need internet).

---

## 4. Installation (XAMPP on Windows)

1. Copy the `coral-bay-villa` folder into `C:\xampp\htdocs\`
2. Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.
3. Open **http://localhost/phpmyadmin** → **Import** tab → choose `backend/database/db_structure.sql` → **Go**.
   This creates the `coral_bay_villa` database with tables and sample data.
4. If your MySQL root user has a password, edit `backend/config/config.php` (`DB_USER`, `DB_PASS`).
5. Visit **http://localhost/coral-bay-villa/**

> If you rename the folder, the base URL is detected automatically.
> Admin image uploads are saved in `frontend/assets/img/` — make sure that folder is writable.

---

## 5. Sample login credentials

| Role | Email | Password |
|---|---|---|
| Administrator | `admin@coralbay.lk` | `Admin@123` |
| Guest | `nimal@example.com` | `Guest@123` |
| Guest | `emma@example.com` | `Guest@123` |
| Guest | `kasun@example.com` | `Guest@123` |

Sample data includes confirmed, pending and cancelled bookings, two reviews and three pricing seasons.
(Nimal has a confirmed booking for Coconut Grove Deluxe on 20–23 Nov 2026 — try searching those dates to see it excluded.)

---

## 6. Project structure — front-end vs back-end

The code is split into two clearly separated layers:

| Layer | Folder | Responsibility |
|---|---|---|
| **Front-end** | `frontend/` | Everything the browser sees: HTML templates (views), CSS, JavaScript, images, Bootstrap/Chart.js. Views **never** query the database. |
| **Back-end** | `backend/` | Request handling, validation, sessions, authentication, business rules (pricing, availability), SQL queries, JSON API, database script. |

Each page works in two steps — the root file is only a thin entry point that keeps the URL simple:

```
browser → rooms.php ─┬─► backend/controllers/rooms.php   (validate input, run SQL, prepare $rooms)
                     └─► frontend/views/pages/rooms.php   (render $rooms as HTML)

browser JS (fetch) ─────► backend/api/availability.php      (JSON response)
```

```
coral-bay-villa/
│
├── index.php, rooms.php, room.php, search.php, book.php,      ← entry points (2 lines each:
│   my_bookings.php, register.php, login.php, logout.php          controller + view)
├── admin/index.php, bookings.php, rooms.php, room_form.php
│
├── frontend/                         ★ FRONT-END
│   ├── views/
│   │   ├── layout/header.php         navbar, <head>, flash messages
│   │   ├── layout/footer.php         footer, script tags
│   │   ├── partials/room_card.php    reusable room card
│   │   ├── partials/admin_nav.php    admin tab bar
│   │   ├── pages/                    one template per page
│   │   │   ├── home.php, rooms.php, room.php, search.php, book.php,
│   │   │   ├── my_bookings.php, register.php, login.php
│   │   │   └── admin/ dashboard.php, bookings.php, rooms.php, room_form.php
│   │   └── view_helpers.php          e(), stars(), status_badge(), asset_url() …
│   └── assets/
│       ├── css/style.css             custom design + media queries
│       ├── js/main.js                validation, date checks, price calc, AJAX, DOM updates
│       ├── js/admin.js               dashboard chart
│       ├── img/                      room & villa images (+ admin uploads)
│       └── vendor/                   Bootstrap 5.3.3, Bootstrap Icons 1.11.3, Chart.js 4.4.1
│
└── backend/                          ★ BACK-END
    ├── config/config.php             DB settings, booking rules
    ├── core/
    │   ├── init.php                  loads config, DB, session, helpers
    │   ├── db.php                    PDO connection
    │   ├── functions.php             auth, CSRF, flash, date validation,
    │   │                             pricing, availability queries
    │   └── admin_actions.php         confirm / cancel booking (transaction)
    ├── controllers/                  one per page (handles GET/POST, runs SQL)
    │   ├── home.php, rooms.php, room.php, search.php, book.php,
    │   ├── my_bookings.php, register.php, login.php, logout.php
    │   └── admin/ dashboard.php, bookings.php, rooms.php, room_form.php
    ├── api/availability.php          AJAX JSON endpoint
    └── database/db_structure.sql     tables + sample records
```

`backend/.htaccess` and `frontend/views/.htaccess` block direct browser access to internal
files (only `backend/api/` is public), so users can only reach pages through the entry points.

### Key logic — overlap check
Two stays overlap when `existing.check_in < new.check_out AND existing.check_out > new.check_in`.
Only **Confirmed** bookings block a room, so several guests can request the same dates; the admin
can confirm only one of them (others show a "Clash" badge and confirming them is refused).
Check-out day = next guest's check-in day is allowed.

---

## 7. Testing summary
Tested on PHP 8.3 + MariaDB with an automated browser script (30 checks, all passing), including:
date auto-correction and invalid-range messages, room filters, availability excluding booked rooms,
login redirect for booking, client-side blocking of invalid registration, seasonal + long-stay price
(JS and PHP agree: 8 festive nights in the suite = LKR 216,000), guest cancellation, server rejecting
past dates and overlapping bookings without JavaScript, guest unable to cancel another guest's booking,
CSRF rejection, admin-only pages, confirm/cancel, overlap blocked on confirm, room add/edit/deactivate/delete
with image upload, reviews, and no horizontal scrolling at 390 px mobile width.

---

## 8. Acknowledgements & external resources
- [Bootstrap 5.3.3](https://getbootstrap.com) (MIT) — layout, components
- [Bootstrap Icons 1.11.3](https://icons.getbootstrap.com) (MIT)
- [Chart.js 4.4.1](https://www.chartjs.org) (MIT) — dashboard chart
- Google Fonts: Playfair Display, Inter (OFL)
- OpenStreetMap embed for the location map (© OpenStreetMap contributors)
- Room and villa photos were taken from public hotel listings and are used **only for this
  non-commercial academic assignment**; all rights belong to their owners:
  Booking.com (villa-pool, room-palm-shade, room-coral-reef), Agoda (room-coconut-grove),
  Expedia / Vrbo (room-ocean-breeze, villa-dining), Villa-Finder – The Beach House Mirissa (villa-beach),
  Saii Hotels (room-lagoon-family), Google Images (villa-hero, room-sunset-suite).
- If a photo is missing, the site falls back to the original SVG illustration made for this project.
- Coral Bay Villa, its address and all guest records are fictional.

## 9. Generative AI declaration
Generative AI (Claude, by Anthropic) was used to help scaffold the code structure, write parts of the
PHP/JavaScript/CSS, and draft this README. I reviewed, tested and understand all submitted code and
can explain how each part works.
<!-- Edit this section so it accurately describes how you used AI. -->
