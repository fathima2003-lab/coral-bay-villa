<?php
/**
 * FRONT-END VIEW — Admin — add a new room or edit an existing one (incl. image upload).
 * Only HTML output: all data comes from backend/controllers/admin/room_form.php
 */
require __DIR__ . '/../../layout/header.php';
require __DIR__ . '/../../partials/admin_nav.php';
?>

<div class="container py-4">
    <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="js-validate" novalidate>
        <?= csrf_field() ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label" for="room_name">Room name</label>
                                <input class="form-control" id="room_name" name="room_name" value="<?= e($room['room_name']) ?>" required minlength="3" maxlength="100">
                                <div class="invalid-feedback">3–100 characters.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="room_type">Type</label>
                                <select class="form-select" id="room_type" name="room_type" required>
                                    <?php foreach (['Standard', 'Deluxe', 'Suite', 'Family'] as $t): ?>
                                        <option <?= $room['room_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required minlength="20"><?= e($room['description']) ?></textarea>
                                <div class="invalid-feedback">At least 20 characters.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="price">Price per night (<?= CURRENCY ?>)</label>
                                <input type="number" class="form-control" id="price" name="price_per_night" value="<?= e($room['price_per_night']) ?>" min="1000" max="500000" step="0.01" required>
                                <div class="invalid-feedback">Between 1,000 and 500,000.</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="max_guests">Max guests</label>
                                <input type="number" class="form-control" id="max_guests" name="max_guests" value="<?= (int) $room['max_guests'] ?>" min="1" max="10" required>
                                <div class="invalid-feedback">1 to 10.</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="room_status">Status</label>
                                <select class="form-select" id="room_status" name="room_status">
                                    <option value="active"   <?= $room['room_status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $room['room_status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <span class="form-label d-block">Facilities</span>
                                <div class="facility-checks">
                                    <?php foreach ($commonFacilities as $i => $f): ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="fac<?= $i ?>" name="facilities[]" value="<?= e($f) ?>" <?= in_array($f, $currentFacs, true) ? 'checked' : '' ?>>
                                            <label class="form-check-label small" for="fac<?= $i ?>"><?= e($f) ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <input class="form-control form-control-sm mt-2" name="extra_facilities" placeholder="Other facilities, comma separated" value="<?= e(implode(', ', $extraFacs)) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body p-4">
                        <span class="form-label d-block">Room image</span>
                        <img src="<?= img_url($room['image']) ?>" alt="Current room image" class="img-fluid rounded mb-3 js-image-preview">
                        <input type="file" class="form-control js-image-input" name="image" accept="image/jpeg,image/png,image/webp" aria-label="Upload room image">
                        <div class="form-text">JPG, PNG or WebP, up to 2 MB. <?= $editing ? 'Leave empty to keep the current image.' : '' ?></div>
                    </div>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <button type="submit" class="btn btn-sunset btn-lg"><?= $editing ? 'Save changes' : 'Add room' ?></button>
                    <a href="<?= url('admin/rooms.php') ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../layout/footer.php'; ?>
