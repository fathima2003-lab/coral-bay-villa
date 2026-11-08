<?php
/**
 * FRONT-END VIEW — Room Booking Form — registered guests choose a room and dates.
 * Only HTML output: all data comes from backend/controllers/book.php
 */
require __DIR__ . '/../layout/header.php';
?>

<header class="page-header">
    <div class="container">
        <h1>Book your stay</h1>
        <p class="mb-0">Send a booking request — the villa will confirm it by email or phone.</p>
    </div>
</header>

<div class="container py-4">
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <strong>Please fix the following:</strong>
            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <?php if (!$rooms): ?>
        <div class="empty-state"><p>No rooms are open for booking right now.</p></div>
    <?php else: ?>
    <form method="post" id="bookingForm" class="js-validate js-date-form js-booking-form" novalidate
          data-pricing='<?= e(json_encode($jsConfig)) ?>'>
        <?= csrf_field() ?>
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3"><i class="bi bi-person"></i> Guest details</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="b-name">Full name</label>
                                <input type="text" class="form-control" id="b-name" name="name" value="<?= e($form['name']) ?>"
                                       required minlength="3" maxlength="100" pattern="[A-Za-zÀ-ÿ .'\-]+" autocomplete="name">
                                <div class="invalid-feedback">Enter your full name (letters only).</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="b-email">Email</label>
                                <input type="email" class="form-control" id="b-email" value="<?= e($account['email']) ?>" readonly>
                                <div class="form-text">Your account email.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="b-phone">Contact number</label>
                                <input type="tel" class="form-control" id="b-phone" name="contact_number" value="<?= e($form['contact_number']) ?>"
                                       required pattern="(\+94|0)[0-9]{9}" placeholder="0771234567" autocomplete="tel">
                                <div class="invalid-feedback">Use 0771234567 or +94771234567.</div>
                            </div>
                        </div>

                        <h2 class="h5 mt-4 mb-3"><i class="bi bi-door-open"></i> Stay details</h2>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="b-room">Room</label>
                                <select class="form-select js-room-select" id="b-room" name="room_id" required>
                                    <?php foreach ($rooms as $r): ?>
                                        <option value="<?= (int) $r['room_id'] ?>"
                                                data-price="<?= e($r['price_per_night']) ?>"
                                                data-max="<?= (int) $r['max_guests'] ?>"
                                            <?= (int) $r['room_id'] === $form['room_id'] ? 'selected' : '' ?>>
                                            <?= e($r['room_name']) ?> (<?= e($r['room_type']) ?>) — <?= money($r['price_per_night']) ?>/night · max <?= (int) $r['max_guests'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label" for="b-in">Check-in</label>
                                <input type="date" class="form-control js-check-in" id="b-in" name="check_in" min="<?= date('Y-m-d') ?>" value="<?= e($form['check_in']) ?>" required>
                                <div class="invalid-feedback">Choose today or a later date.</div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label" for="b-out">Check-out</label>
                                <input type="date" class="form-control js-check-out" id="b-out" name="check_out" value="<?= e($form['check_out']) ?>" required>
                                <div class="invalid-feedback">Must be after check-in (max <?= MAX_NIGHTS ?> nights).</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="b-guests">Guests</label>
                                <input type="number" class="form-control js-guests" id="b-guests" name="num_guests" min="1" max="10" value="<?= max(1, $form['num_guests']) ?>" required>
                                <div class="invalid-feedback js-guests-feedback">Too many guests for this room.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="b-req">Special requests <span class="text-muted small">(optional)</span></label>
                                <textarea class="form-control js-char-count" id="b-req" name="special_request" rows="2" maxlength="500"
                                          placeholder="e.g. late arrival, airport pick-up, baby cot"><?= e($form['special_request']) ?></textarea>
                                <div class="form-text"><span class="js-char-out">0</span>/500</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live price summary (DOM updated by main.js) -->
            <div class="col-lg-5">
                <div class="card booking-box shadow-sm sticky-lg-top">
                    <div class="card-body p-4">
                        <h2 class="h5">Price summary</h2>
                        <div class="availability-result js-availability-result mb-3" aria-live="polite"></div>
                        <table class="table table-sm summary-table mb-2">
                            <tbody class="js-summary">
                                <tr><td colspan="2" class="text-muted small">Choose your dates to see the total.</td></tr>
                            </tbody>
                        </table>
                        <p class="small text-muted mb-3">Final price is calculated by the villa's system when you submit. No payment is taken online.</p>
                        <button type="submit" class="btn btn-sunset w-100 btn-lg"><i class="bi bi-send"></i> Send booking request</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
