<?php
/**
 * FRONT-END VIEW — Admin — all reservations with filters; confirm or cancel bookings.
 * Only HTML output: all data comes from backend/controllers/admin/bookings.php
 */
require __DIR__ . '/../../layout/header.php';
require __DIR__ . '/../../partials/admin_nav.php';
?>

<div class="container py-4">
    <form class="card filter-bar mb-4" method="get">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="a-status">Status</label>
                    <select class="form-select form-select-sm" id="a-status" name="status">
                        <option value="">All</option>
                        <?php foreach (['Pending', 'Confirmed', 'Cancelled'] as $s): ?>
                            <option <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small" for="a-room">Room</label>
                    <select class="form-select form-select-sm" id="a-room" name="room_id">
                        <option value="0">All rooms</option>
                        <?php foreach ($rooms as $r): ?>
                            <option value="<?= (int) $r['room_id'] ?>" <?= $roomId === (int) $r['room_id'] ? 'selected' : '' ?>><?= e($r['room_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small" for="a-q">Guest name, email or #</label>
                    <input type="search" class="form-control form-control-sm" id="a-q" name="q" value="<?= e($search) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="a-from">Staying after</label>
                    <input type="date" class="form-control form-control-sm" id="a-from" name="from" value="<?= e($from) ?>">
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button class="btn btn-sm btn-ocean flex-fill" type="submit"><i class="bi bi-funnel"></i> Filter</button>
                    <a class="btn btn-sm btn-outline-secondary" href="<?= url('admin/bookings.php') ?>">Reset</a>
                </div>
            </div>
        </div>
    </form>

    <p class="small text-muted"><?= count($bookings) ?> booking(s)</p>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th>
                        <th class="text-center">Nights</th><th class="text-end">Total</th><th>Status</th><th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$bookings): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No bookings match these filters.</td></tr>
                <?php endif; ?>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= (int) $b['booking_id'] ?></td>
                        <td>
                            <strong><?= e($b['guest_name']) ?></strong><br>
                            <span class="small text-muted"><?= e($b['email']) ?> · <?= e($b['contact_number']) ?></span>
                            <?php if ($b['special_request']): ?>
                                <br><span class="small"><i class="bi bi-chat-left-text"></i> <?= e($b['special_request']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($b['room_name']) ?><br><span class="small text-muted"><?= (int) $b['num_guests'] ?> guest(s)</span></td>
                        <td class="text-nowrap"><?= format_date($b['check_in']) ?></td>
                        <td class="text-nowrap"><?= format_date($b['check_out']) ?></td>
                        <td class="text-center"><?= (int) $b['nights'] ?></td>
                        <td class="text-end text-nowrap"><?= money($b['total_amount']) ?></td>
                        <td>
                            <?= status_badge($b['booking_status']) ?>
                            <?php if ($b['booking_status'] === 'Pending' && $b['has_conflict']): ?>
                                <br><span class="badge bg-danger mt-1" title="Overlaps a confirmed booking for this room"><i class="bi bi-exclamation-triangle"></i> Clash</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap"><?= booking_action_buttons($b) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../layout/footer.php'; ?>
