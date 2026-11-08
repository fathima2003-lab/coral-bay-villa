<?php
/**
 * FRONT-END VIEW — Admin — room list. View, edit, activate/deactivate or delete rooms.
 * Only HTML output: all data comes from backend/controllers/admin/rooms.php
 */
require __DIR__ . '/../../layout/header.php';
require __DIR__ . '/../../partials/admin_nav.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="small text-muted mb-0"><?= count($rooms) ?> room(s)</p>
        <a href="<?= url('admin/room_form.php') ?>" class="btn btn-sunset btn-sm"><i class="bi bi-plus-lg"></i> Add room</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0 admin-table">
                <thead class="table-light">
                    <tr><th>Room</th><th>Type</th><th class="text-end">Price / night</th><th class="text-center">Max guests</th>
                        <th class="text-center">Bookings</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($rooms as $r): ?>
                    <tr class="<?= $r['room_status'] === 'inactive' ? 'table-row-muted' : '' ?>">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= img_url($r['image']) ?>" alt="" class="admin-thumb">
                                <div>
                                    <strong><?= e($r['room_name']) ?></strong><br>
                                    <span class="small text-muted"><?= e(mb_strimwidth($r['facilities'], 0, 50, '…')) ?></span>
                                </div>
                            </div>
                        </td>
                        <td><?= e($r['room_type']) ?></td>
                        <td class="text-end text-nowrap"><?= money($r['price_per_night']) ?></td>
                        <td class="text-center"><?= (int) $r['max_guests'] ?></td>
                        <td class="text-center"><?= (int) $r['total_bookings'] ?><?php if ($r['upcoming']): ?> <span class="badge bg-ocean" title="Upcoming confirmed stays"><?= (int) $r['upcoming'] ?> upcoming</span><?php endif; ?></td>
                        <td><span class="badge bg-<?= $r['room_status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst(e($r['room_status'])) ?></span></td>
                        <td class="text-end text-nowrap">
                            <?php if ($r['room_status'] === 'active'): ?>
                                <a class="btn btn-sm btn-outline-dark" href="<?= url('room.php?id=' . (int) $r['room_id']) ?>" title="View public page"><i class="bi bi-eye"></i></a>
                            <?php endif; ?>
                            <a class="btn btn-sm btn-outline-primary" href="<?= url('admin/room_form.php?id=' . (int) $r['room_id']) ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="room_id" value="<?= (int) $r['room_id'] ?>">
                                <button class="btn btn-sm btn-outline-secondary" name="action" value="toggle"
                                        title="<?= $r['room_status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                    <i class="bi bi-<?= $r['room_status'] === 'active' ? 'pause-circle' : 'play-circle' ?>"></i>
                                </button>
                            </form>
                            <form method="post" class="d-inline js-confirm" data-confirm="Delete <?= e($r['room_name']) ?> permanently?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="room_id" value="<?= (int) $r['room_id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" name="action" value="delete" title="Delete"
                                    <?= $r['total_bookings'] > 0 ? 'disabled' : '' ?>><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <p class="small text-muted mt-2"><i class="bi bi-info-circle"></i> Rooms with booking history can't be deleted (to keep guest records intact) — deactivate them to hide them from guests.</p>
</div>

<?php require __DIR__ . '/../../layout/footer.php'; ?>
