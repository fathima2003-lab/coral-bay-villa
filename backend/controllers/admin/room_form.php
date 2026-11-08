<?php
/**
 * BACK-END CONTROLLER
 *
 * Admin — add a new room or edit an existing one (incl. image upload).
 *   room_form.php        -> add
 *   room_form.php?id=3   -> edit room 3
 */
require_once __DIR__ . '/../../core/init.php';
require_admin();

$roomId  = (int) ($_GET['id'] ?? 0);
$editing = $roomId > 0;

$commonFacilities = ['Air conditioning', 'Fan', 'Free Wi-Fi', 'Hot water', 'Sea view', 'Partial sea view', 'Garden view',
                     'Private balcony', 'Private terrace', 'Pool access', 'Smart TV', 'Mini fridge', 'Kitchenette',
                     'Bathtub', 'Rain shower', 'Two bedrooms', 'Breakfast included'];

$room = [
    'room_name' => '', 'room_type' => 'Standard', 'description' => '', 'facilities' => '',
    'price_per_night' => '', 'max_guests' => 2, 'image' => 'default.svg', 'room_status' => 'active',
];

if ($editing) {
    $stmt = $pdo->prepare('SELECT * FROM rooms WHERE room_id = ?');
    $stmt->execute([$roomId]);
    $room = $stmt->fetch();
    if (!$room) {
        flash('warning', 'Room not found.');
        redirect('admin/rooms.php');
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $checked = array_intersect((array) ($_POST['facilities'] ?? []), $commonFacilities);
    $extra   = array_filter(array_map('trim', explode(',', $_POST['extra_facilities'] ?? '')));
    $facs    = array_unique(array_merge($checked, $extra));

    $room = array_merge($room, [
        'room_name'       => trim($_POST['room_name'] ?? ''),
        'room_type'       => $_POST['room_type'] ?? '',
        'description'     => trim($_POST['description'] ?? ''),
        'facilities'      => implode(',', $facs),
        'price_per_night' => $_POST['price_per_night'] ?? '',
        'max_guests'      => (int) ($_POST['max_guests'] ?? 0),
        'room_status'     => $_POST['room_status'] ?? 'active',
    ]);

    if (mb_strlen($room['room_name']) < 3 || mb_strlen($room['room_name']) > 100) $errors[] = 'Room name must be 3–100 characters.';
    if (!in_array($room['room_type'], ['Standard', 'Deluxe', 'Suite', 'Family'], true))   $errors[] = 'Choose a valid room type.';
    if (mb_strlen($room['description']) < 20)                                             $errors[] = 'Description must be at least 20 characters.';
    if (strlen($room['facilities']) > 255)                                                $errors[] = 'Too many facilities (255 characters max).';
    if (!is_numeric($room['price_per_night']) || $room['price_per_night'] < 1000 || $room['price_per_night'] > 500000) {
        $errors[] = 'Price per night must be between 1,000 and 500,000.';
    }
    if ($room['max_guests'] < 1 || $room['max_guests'] > 10)                              $errors[] = 'Max guests must be between 1 and 10.';
    if (!in_array($room['room_status'], ['active', 'inactive'], true))                    $errors[] = 'Choose a valid status.';

    // Duplicate room name check
    $dup = $pdo->prepare('SELECT COUNT(*) FROM rooms WHERE room_name = ? AND room_id <> ?');
    $dup->execute([$room['room_name'], $roomId]);
    if ($dup->fetchColumn() > 0) $errors[] = 'Another room already uses this name.';

    // ---- Optional image upload ----
    $newImage = null;
    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed (error code ' . (int) $file['error'] . ').';
        } elseif ($file['size'] > MAX_UPLOAD_BYTES) {
            $errors[] = 'Image must be 2 MB or smaller.';
        } else {
            // Check the real file type, not the name the browser sent
            // (getimagesize reads the image header, so a renamed .php file is rejected)
            $info = @getimagesize($file['tmp_name']);
            $mime = $info['mime'] ?? '';
            if (!isset($allowed[$mime])) {
                $errors[] = 'Image must be a JPG, PNG or WebP file.';
            } else {
                $newImage = 'upload_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
            }
        }
    }

    if (!$errors) {
        if ($newImage) {
            if (!move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $newImage)) {
                $errors[] = 'Could not save the uploaded image. Check that assets/img is writable.';
            } else {
                // Delete the previous uploaded image (sample images are kept)
                if (str_starts_with($room['image'], 'upload_') && is_file(UPLOAD_DIR . basename($room['image']))) {
                    unlink(UPLOAD_DIR . basename($room['image']));
                }
                $room['image'] = $newImage;
            }
        }
    }

    if (!$errors) {
        $params = [
            $room['room_name'], $room['room_type'], $room['description'], $room['facilities'],
            round((float) $room['price_per_night'], 2), $room['max_guests'], $room['image'], $room['room_status'],
        ];
        if ($editing) {
            $params[] = $roomId;
            $pdo->prepare('UPDATE rooms SET room_name=?, room_type=?, description=?, facilities=?, price_per_night=?,
                           max_guests=?, image=?, room_status=? WHERE room_id=?')->execute($params);
            flash('success', 'Room "' . $room['room_name'] . '" updated. Existing bookings keep their original price.');
        } else {
            $pdo->prepare('INSERT INTO rooms (room_name, room_type, description, facilities, price_per_night, max_guests, image, room_status)
                           VALUES (?,?,?,?,?,?,?,?)')->execute($params);
            $newId = (int) $pdo->lastInsertId();
            $pdo->prepare('INSERT INTO room_images (room_id, file_name, caption) VALUES (?,?,?)')
                ->execute([$newId, $room['image'], $room['room_name']]);
            flash('success', 'Room "' . $room['room_name'] . '" added.');
        }
        redirect('admin/rooms.php');
    }
}

$currentFacs = facilities_list($room['facilities']);
$extraFacs   = array_diff($currentFacs, $commonFacilities);

$pageTitle  = $editing ? 'Edit Room' : 'Add Room';
$activePage = 'admin';
$adminTab   = $editing ? 'rooms' : 'add-room';

// Data prepared above is rendered by frontend/views/pages/admin/room_form.php
