<?php
/**
 * FRONT-END partial — room card. Expects $room; optional $stay = ['check_in','check_out','guests']
 * to carry search dates through to the details / booking pages.
 */
$query = isset($stay) && $stay ? '&' . http_build_query($stay) : '';
$facs  = facilities_list($room['facilities']);
?>
<article class="card room-card h-100">
    <div class="room-img-wrap">
        <img src="<?= img_url($room['image']) ?>" class="card-img-top" alt="<?= e($room['room_name']) ?>" loading="lazy">
        <span class="badge room-type-badge"><?= e($room['room_type']) ?></span>
    </div>
    <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <h3 class="h5 card-title mb-1"><?= e($room['room_name']) ?></h3>
            <?php if (!empty($room['avg_rating'])): ?>
                <span class="small text-nowrap"><?= stars((float) $room['avg_rating']) ?></span>
            <?php endif; ?>
        </div>
        <p class="small text-muted mb-2"><i class="bi bi-people"></i> Up to <?= (int) $room['max_guests'] ?> guests</p>
        <p class="card-text small flex-grow-1"><?= e(mb_strimwidth($room['description'], 0, 120, '…')) ?></p>
        <ul class="facility-tags list-unstyled mb-3">
            <?php foreach (array_slice($facs, 0, 4) as $f): ?>
                <li><?= e($f) ?></li>
            <?php endforeach; ?>
            <?php if (count($facs) > 4): ?><li>+<?= count($facs) - 4 ?> more</li><?php endif; ?>
        </ul>
        <div class="d-flex justify-content-between align-items-center mt-auto">
            <p class="price mb-0"><?= money($room['price_per_night']) ?><small> / night</small></p>
        </div>
        <div class="d-flex gap-2 mt-3">
            <a href="<?= url('room.php?id=' . (int) $room['room_id'] . $query) ?>" class="btn btn-outline-dark btn-sm flex-fill">Details</a>
            <a href="<?= url('book.php?room_id=' . (int) $room['room_id'] . $query) ?>" class="btn btn-sunset btn-sm flex-fill">Book</a>
        </div>
    </div>
</article>
