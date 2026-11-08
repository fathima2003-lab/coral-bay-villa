<?php
/**
 * FRONT-END VIEW — Rooms & Accommodation — all active rooms with search filters
 * Only HTML output: all data comes from backend/controllers/rooms.php
 */
require __DIR__ . '/../layout/header.php';
?>

<header class="page-header">
    <div class="container">
        <h1>Rooms &amp; Accommodation</h1>
        <p class="mb-0">Choose from standard rooms, deluxe garden rooms, sea-view suites and family rooms.</p>
    </div>
</header>

<div class="container py-4">
    <!-- Filters -->
    <form class="card filter-bar mb-4" method="get" action="<?= url('rooms.php') ?>">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="f-type">Room type</label>
                    <select class="form-select" id="f-type" name="type">
                        <option value="">Any</option>
                        <?php foreach (['Standard', 'Deluxe', 'Suite', 'Family'] as $t): ?>
                            <option <?= $filters['type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small" for="f-price">Max price / night: <strong class="js-range-out"><?= $filters['max_price'] ? money($filters['max_price']) : 'Any' ?></strong></label>
                    <input type="range" class="form-range js-price-range" id="f-price" name="max_price" min="5000" max="30000" step="500"
                           value="<?= e($filters['max_price'] ?: 30000) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="f-fac">Facility</label>
                    <select class="form-select" id="f-fac" name="facility">
                        <option value="">Any</option>
                        <?php foreach ($facilityOptions as $f): ?>
                            <option <?= $filters['facility'] === $f ? 'selected' : '' ?>><?= e($f) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="f-guests">Guests</label>
                    <input type="number" class="form-control" id="f-guests" name="guests" min="1" max="10" value="<?= $guests ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="f-sort">Sort by</label>
                    <select class="form-select" id="f-sort" name="sort">
                        <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Price: low → high</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: high → low</option>
                        <option value="guests"     <?= $sort === 'guests'     ? 'selected' : '' ?>>Most guests</option>
                        <option value="rating"     <?= $sort === 'rating'     ? 'selected' : '' ?>>Top rated</option>
                    </select>
                </div>
                <div class="col-6 col-md-1 d-grid">
                    <button class="btn btn-ocean" type="submit" aria-label="Apply filters"><i class="bi bi-funnel"></i></button>
                </div>
            </div>
        </div>
    </form>

    <p class="text-muted small mb-3"><?= count($rooms) ?> room<?= count($rooms) === 1 ? '' : 's' ?> found
        <?php if (array_filter($filters) || $guests > 1): ?> · <a href="<?= url('rooms.php') ?>">Clear filters</a><?php endif; ?>
    </p>

    <?php if (!$rooms): ?>
        <div class="empty-state">
            <i class="bi bi-search"></i>
            <p>No rooms match those filters. Try a higher price or fewer facilities.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($rooms as $room): ?>
                <div class="col-md-6 col-lg-4">
                    <?php include __DIR__ . '/../partials/room_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
