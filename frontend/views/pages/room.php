<?php
/**
 * FRONT-END VIEW — Room details — gallery, facilities, live availability check (AJAX),
 * Only HTML output: all data comes from backend/controllers/room.php
 */
require __DIR__ . '/../layout/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('rooms.php') ?>">Rooms</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($room['room_name']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Gallery -->
        <div class="col-lg-7">
            <div id="roomCarousel" class="carousel slide room-carousel" data-bs-ride="false">
                <div class="carousel-inner">
                    <?php foreach ($images as $i => $img): ?>
                        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                            <img src="<?= img_url($img['file_name']) ?>" class="d-block w-100" alt="<?= e($img['caption'] ?: $room['room_name']) ?>">
                            <?php if ($img['caption']): ?><div class="carousel-caption"><p class="mb-0"><?= e($img['caption']) ?></p></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($images) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span><span class="visually-hidden">Previous</span></button>
                    <button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span><span class="visually-hidden">Next</span></button>
                <?php endif; ?>
            </div>

            <h1 class="section-title mt-4 mb-1"><?= e($room['room_name']) ?></h1>
            <p class="text-muted">
                <span class="badge bg-ocean me-2"><?= e($room['room_type']) ?></span>
                <i class="bi bi-people"></i> Up to <?= (int) $room['max_guests'] ?> guests
                <?php if ($avgRating): ?> · <?= stars($avgRating) ?> <?= $avgRating ?> (<?= count($reviews) ?>)<?php endif; ?>
            </p>
            <p><?= nl2br(e($room['description'])) ?></p>

            <h2 class="h5 mt-4">Facilities</h2>
            <ul class="facility-tags list-unstyled">
                <?php foreach (facilities_list($room['facilities']) as $f): ?>
                    <li><i class="bi bi-check2"></i> <?= e($f) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Price + availability box -->
        <div class="col-lg-5">
            <div class="card booking-box shadow-sm sticky-lg-top">
                <div class="card-body p-4">
                    <p class="price fs-3 mb-0"><?= money($room['price_per_night']) ?><small> / night</small></p>
                    <p class="small text-muted">Seasonal rates apply Dec–Apr. <?= LONG_STAY_NIGHTS ?>+ nights save <?= LONG_STAY_DISCOUNT * 100 ?>%.</p>

                    <form class="js-date-form js-availability-form" action="<?= url('book.php') ?>" method="get" data-room-id="<?= (int) $room['room_id'] ?>" novalidate>
                        <input type="hidden" name="room_id" value="<?= (int) $room['room_id'] ?>">
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="r-in" class="form-label small">Check-in</label>
                                <input type="date" id="r-in" name="check_in" class="form-control js-check-in" min="<?= date('Y-m-d') ?>" value="<?= e($checkIn) ?>" required>
                            </div>
                            <div class="col-6">
                                <label for="r-out" class="form-label small">Check-out</label>
                                <input type="date" id="r-out" name="check_out" class="form-control js-check-out" value="<?= e($checkOut) ?>" required>
                            </div>
                            <div class="col-12">
                                <label for="r-guests" class="form-label small">Guests</label>
                                <select id="r-guests" name="guests" class="form-select">
                                    <?php for ($g = 1; $g <= $room['max_guests']; $g++): ?>
                                        <option value="<?= $g ?>" <?= $g === $guests ? 'selected' : '' ?>><?= $g ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <p class="small text-muted mt-2 mb-2 js-nights-label" aria-live="polite"></p>
                        <!-- Filled by AJAX (assets/js/main.js -> api/availability.php) -->
                        <div class="availability-result js-availability-result" aria-live="polite"></div>
                        <button type="submit" class="btn btn-sunset w-100 mt-3"><i class="bi bi-calendar2-check"></i> Book this room</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews -->
    <section id="reviews" class="mt-5">
        <h2 class="h4">Guest reviews</h2>
        <?php if (!$reviews): ?>
            <p class="text-muted">No reviews yet for this room.</p>
        <?php endif; ?>
        <div class="row g-3">
            <?php foreach ($reviews as $r): ?>
                <div class="col-md-6">
                    <figure class="review-card mb-0 h-100">
                        <?= stars((float) $r['rating']) ?>
                        <blockquote class="mt-2 mb-2">“<?= e($r['comment']) ?>”</blockquote>
                        <figcaption class="small text-muted">— <?= e($r['name']) ?>, <?= format_date($r['created_at']) ?></figcaption>
                    </figure>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($reviewable): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h3 class="h5">Review your stay</h3>
                    <?php foreach ($reviewErrors as $err): ?>
                        <div class="alert alert-danger py-2"><?= e($err) ?></div>
                    <?php endforeach; ?>
                    <form method="post" class="js-validate" novalidate>
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="rv-booking">Stay</label>
                                <select class="form-select" id="rv-booking" name="booking_id" required>
                                    <?php foreach ($reviewable as $b): ?>
                                        <option value="<?= (int) $b['booking_id'] ?>"><?= format_date($b['check_in']) ?> → <?= format_date($b['check_out']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <span class="form-label d-block">Rating</span>
                                <div class="star-input" role="radiogroup" aria-label="Rating">
                                    <?php for ($s = 5; $s >= 1; $s--): ?>
                                        <input type="radio" id="star<?= $s ?>" name="rating" value="<?= $s ?>" <?= $s === 5 ? 'checked' : '' ?>>
                                        <label for="star<?= $s ?>" title="<?= $s ?> stars">★</label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="rv-comment">Your review</label>
                                <textarea class="form-control" id="rv-comment" name="comment" rows="3" minlength="10" maxlength="1000" required></textarea>
                                <div class="invalid-feedback">Please write at least 10 characters.</div>
                            </div>
                        </div>
                        <button class="btn btn-ocean mt-3" type="submit" name="submit_review" value="1">Submit review</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
