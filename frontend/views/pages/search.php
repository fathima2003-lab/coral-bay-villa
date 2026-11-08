<?php
/**
 * FRONT-END VIEW — Room Availability Search — visitors pick dates and number of guests;
 * Only HTML output: all data comes from backend/controllers/search.php
 */
require __DIR__ . '/../layout/header.php';
?>

<header class="page-header">
    <div class="container">
        <h1>Check room availability</h1>
        <p class="mb-0">Pick your dates and number of guests to see rooms that are free for your whole stay.</p>
    </div>
</header>

<div class="container py-4">
    <form class="card filter-bar mb-4 js-date-form" method="get" action="<?= url('search.php') ?>" novalidate>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-6 col-md-3">
                    <label for="s-in" class="form-label small">Check-in</label>
                    <input type="date" class="form-control js-check-in" id="s-in" name="check_in" min="<?= date('Y-m-d') ?>"
                           value="<?= e($checkIn ?: date('Y-m-d')) ?>" required>
                    <div class="invalid-feedback">Choose a check-in date (today or later).</div>
                </div>
                <div class="col-6 col-md-3">
                    <label for="s-out" class="form-label small">Check-out</label>
                    <input type="date" class="form-control js-check-out" id="s-out" name="check_out"
                           value="<?= e($checkOut ?: date('Y-m-d', strtotime('+1 day'))) ?>" required>
                    <div class="invalid-feedback">Check-out must be after check-in.</div>
                </div>
                <div class="col-4 col-md-2">
                    <label for="s-guests" class="form-label small">Guests</label>
                    <select class="form-select" id="s-guests" name="guests">
                        <?php for ($g = 1; $g <= 5; $g++): ?>
                            <option value="<?= $g ?>" <?= $g === $guests ? 'selected' : '' ?>><?= $g ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-8 col-md-2">
                    <label for="s-type" class="form-label small">Room type</label>
                    <select class="form-select" id="s-type" name="type">
                        <option value="">Any</option>
                        <?php foreach (['Standard', 'Deluxe', 'Suite', 'Family'] as $t): ?>
                            <option <?= $filters['type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-grid">
                    <button type="submit" class="btn btn-ocean"><i class="bi bi-search"></i> Search</button>
                </div>
            </div>
            <p class="small text-muted mt-2 mb-0 js-nights-label" aria-live="polite"></p>
        </div>
    </form>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger"><?= e($err) ?></div>
    <?php endforeach; ?>

    <?php if ($searched && !$errors): ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="h5 mb-0">
                <?= count($rooms) ?> room<?= count($rooms) === 1 ? '' : 's' ?> available
                <small class="text-muted fw-normal">· <?= format_date($checkIn) ?> → <?= format_date($checkOut) ?> · <?= $nights ?> night<?= $nights > 1 ? 's' : '' ?> · <?= $guests ?> guest<?= $guests > 1 ? 's' : '' ?></small>
            </h2>
        </div>

        <?php if (!$rooms): ?>
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <p>Sorry, no rooms are free for those dates. Try different dates or fewer guests.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($rooms as $room): $quote = $room['quote']; ?>
                    <div class="col-md-6 col-lg-4 d-flex flex-column search-col">
                        <?php include __DIR__ . '/../partials/room_card.php'; ?>
                        <p class="small text-center mt-2 mb-0 stay-total">
                            Total for your stay: <strong><?= money($quote['total']) ?></strong>
                            <?php if ($quote['discount'] > 0): ?><span class="badge bg-success ms-1">Long-stay discount</span><?php endif; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php elseif (!$searched): ?>
        <div class="empty-state">
            <i class="bi bi-calendar-range"></i>
            <p>Choose your dates above to see available rooms.</p>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
