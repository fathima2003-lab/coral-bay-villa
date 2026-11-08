<?php
/**
 * FRONT-END VIEW — Administrator dashboard — booking statistics, charts, pending
 * Only HTML output: all data comes from backend/controllers/admin/dashboard.php
 */
require __DIR__ . '/../../layout/header.php';
require __DIR__ . '/../../partials/admin_nav.php';
?>

<div class="container py-4">
    <!-- Stat cards -->
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['Total bookings',   (int) $stats['total'],       'bi-journal-text',  'ocean'],
            ['Pending requests', (int) $stats['pending'],     'bi-hourglass-split','warning'],
            ['Confirmed revenue', money($stats['revenue']),   'bi-cash-coin',     'success'],
            ['Occupancy (' . date('M') . ')', $occupancy . '%', 'bi-pie-chart',   'sunset'],
            ['Active rooms',     $activeRooms,                'bi-door-open',     'ocean'],
            ['Registered guests',$guestCount,                 'bi-people',        'secondary'],
        ];
        foreach ($cards as [$label, $value, $icon, $tone]): ?>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card tone-<?= $tone ?> h-100">
                    <i class="bi <?= $icon ?>"></i>
                    <p class="stat-value"><?= e($value) ?></p>
                    <p class="stat-label"><?= e($label) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 mb-3">Booking requests per month</h2>
                    <div class="chart-box"><canvas id="bookingsChart" data-chart='<?= e(json_encode($chartData)) ?>' aria-label="Bar chart of bookings per month by status" role="img"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 mb-3">Confirmed revenue by room</h2>
                    <?php foreach ($byRoom as $r): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small">
                                <span><?= e($r['room_name']) ?></span>
                                <span class="text-muted"><?= money($r['revenue']) ?></span>
                            </div>
                            <div class="progress" style="height:8px" role="progressbar" aria-label="<?= e($r['room_name']) ?> revenue">
                                <div class="progress-bar bg-ocean" style="width: <?= round($r['revenue'] / $maxRevenue * 100) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h6 mb-0">Pending requests</h2>
                        <a href="<?= url('admin/bookings.php?status=Pending') ?>" class="small">View all</a>
                    </div>
                    <?php if (!$pending): ?>
                        <p class="text-muted small mb-0">No pending requests — all caught up!</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead><tr><th>#</th><th>Guest</th><th>Room</th><th>Dates</th><th class="text-end">Total</th><th class="text-end">Action</th></tr></thead>
                                <tbody>
                                <?php foreach ($pending as $b): ?>
                                    <tr>
                                        <td><?= (int) $b['booking_id'] ?></td>
                                        <td><?= e($b['guest_name']) ?></td>
                                        <td><?= e($b['room_name']) ?></td>
                                        <td class="small text-nowrap"><?= date('d M', strtotime($b['check_in'])) ?> → <?= date('d M', strtotime($b['check_out'])) ?></td>
                                        <td class="text-end small"><?= money($b['total_amount']) ?></td>
                                        <td class="text-end text-nowrap"><?= booking_action_buttons($b) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h6 mb-3">Arrivals in the next 14 days</h2>
                    <?php if (!$arrivals): ?>
                        <p class="text-muted small mb-0">No confirmed arrivals in the next two weeks.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($arrivals as $a): ?>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <div>
                                        <strong><?= e($a['guest_name']) ?></strong> · <?= e($a['room_name']) ?><br>
                                        <span class="small text-muted"><?= (int) $a['nights'] ?> night(s) · <?= (int) $a['num_guests'] ?> guest(s) · <?= e($a['contact_number']) ?></span>
                                    </div>
                                    <span class="badge bg-ocean align-self-start"><?= format_date($a['check_in']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../layout/footer.php'; ?>
