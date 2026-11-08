<?php
/**
 * FRONT-END VIEW — My Bookings — a registered guest sees ONLY their own bookings and can
 * Only HTML output: all data comes from backend/controllers/my_bookings.php
 */
require __DIR__ . '/../layout/header.php';
?>

<header class="page-header">
    <div class="container">
        <h1>My bookings</h1>
        <p class="mb-0">Track the status of your booking requests and cancel any that are still pending.</p>
    </div>
</header>

<div class="container py-4">
    <!-- Status filter tabs -->
    <ul class="nav nav-pills mb-4 gap-2 flex-wrap">
        <?php
        $tabs = ['' => 'All', 'Pending' => 'Pending', 'Confirmed' => 'Confirmed', 'Cancelled' => 'Cancelled'];
        foreach ($tabs as $value => $label):
            $n = $value === '' ? array_sum($counts) : ($counts[$value] ?? 0); ?>
            <li class="nav-item">
                <a class="nav-link <?= $statusFilter === $value ? 'active' : '' ?>" href="<?= url('my_bookings.php' . ($value ? '?status=' . $value : '')) ?>">
                    <?= $label ?> <span class="badge bg-light text-dark ms-1"><?= (int) $n ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (!$bookings): ?>
        <div class="empty-state">
            <i class="bi bi-calendar2-plus"></i>
            <p>You have no bookings<?= $statusFilter ? ' with this status' : ' yet' ?>.</p>
            <a href="<?= url('search.php') ?>" class="btn btn-sunset">Find a room</a>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($bookings as $b):
                $isPast = $b['check_out'] <= $today; ?>
                <div class="col-12">
                    <article class="card booking-item <?= $highlight === (int) $b['booking_id'] ? 'is-new' : '' ?>">
                        <div class="row g-0">
                            <div class="col-md-3">
                                <img src="<?= img_url($b['image']) ?>" alt="<?= e($b['room_name']) ?>" class="booking-thumb">
                            </div>
                            <div class="col-md-9">
                                <div class="card-body">
                                    <div class="d-flex flex-wrap justify-content-between gap-2">
                                        <div>
                                            <h2 class="h5 mb-1"><?= e($b['room_name']) ?> <small class="text-muted">· <?= e($b['room_type']) ?></small></h2>
                                            <p class="small text-muted mb-2">Booking #<?= (int) $b['booking_id'] ?> · requested <?= format_date($b['booking_date']) ?></p>
                                        </div>
                                        <div><?= status_badge($b['booking_status']) ?></div>
                                    </div>
                                    <dl class="row booking-facts small mb-2">
                                        <dt class="col-6 col-md-3">Check-in</dt><dd class="col-6 col-md-3"><?= format_date($b['check_in']) ?></dd>
                                        <dt class="col-6 col-md-3">Check-out</dt><dd class="col-6 col-md-3"><?= format_date($b['check_out']) ?></dd>
                                        <dt class="col-6 col-md-3">Nights / guests</dt><dd class="col-6 col-md-3"><?= (int) $b['nights'] ?> / <?= (int) $b['num_guests'] ?></dd>
                                        <dt class="col-6 col-md-3">Total</dt><dd class="col-6 col-md-3 fw-semibold"><?= money($b['total_amount']) ?></dd>
                                    </dl>
                                    <?php if ($b['special_request']): ?>
                                        <p class="small mb-2"><i class="bi bi-chat-left-text"></i> <?= e($b['special_request']) ?></p>
                                    <?php endif; ?>

                                    <div class="d-flex flex-wrap gap-2">
                                        <?php if ($b['booking_status'] === 'Pending'): ?>
                                            <form method="post" class="js-confirm" data-confirm="Cancel booking #<?= (int) $b['booking_id'] ?>? This cannot be undone.">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="cancel_id" value="<?= (int) $b['booking_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i> Cancel booking</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($b['booking_status'] === 'Confirmed' && $isPast && !$b['reviewed']): ?>
                                            <a class="btn btn-sm btn-outline-primary" href="<?= url('room.php?id=' . (int) $b['room_id'] . '#reviews') ?>"><i class="bi bi-star"></i> Review your stay</a>
                                        <?php endif; ?>
                                        <a class="btn btn-sm btn-outline-dark" href="<?= url('room.php?id=' . (int) $b['room_id']) ?>">View room</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
