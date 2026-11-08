<?php
/**
 * BACK-END CONTROLLER
 *
 * Admin — room list. View, edit, activate/deactivate or delete rooms.
 * Rooms that have bookings cannot be deleted (foreign key) — deactivate instead.
 */
require_once __DIR__ . '/../../core/init.php';
require_admin();

$pageTitle  = 'Rooms';
$activePage = 'admin';
$adminTab   = 'rooms';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $roomId = (int) ($_POST['room_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle') {
        $stmt = $pdo->prepare("UPDATE rooms SET room_status = IF(room_status = 'active', 'inactive', 'active') WHERE room_id = ?");
        $stmt->execute([$roomId]);
        flash('success', 'Room status updated.');

    } elseif ($action === 'delete') {
        $count = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE room_id = ?');
        $count->execute([$roomId]);
        if ($count->fetchColumn() > 0) {
            flash('warning', 'This room has booking records, so it was not deleted. Deactivate it instead to hide it from guests.');
        } else {
            $img = $pdo->prepare('SELECT image FROM rooms WHERE room_id = ?');
            $img->execute([$roomId]);
            $image = $img->fetchColumn();

            $pdo->prepare('DELETE FROM rooms WHERE room_id = ?')->execute([$roomId]);

            // Remove an uploaded image file (uploads are prefixed "upload_")
            if ($image && str_starts_with($image, 'upload_') && is_file(UPLOAD_DIR . basename($image))) {
                unlink(UPLOAD_DIR . basename($image));
            }
            flash('success', 'Room deleted.');
        }
    }
    redirect('admin/rooms.php');
}

$rooms = $pdo->query(
    "SELECT r.*,
            (SELECT COUNT(*) FROM bookings b WHERE b.room_id = r.room_id) AS total_bookings,
            (SELECT COUNT(*) FROM bookings b WHERE b.room_id = r.room_id
               AND b.booking_status = 'Confirmed' AND b.check_out >= CURDATE()) AS upcoming
     FROM rooms r ORDER BY r.room_status, r.room_name"
)->fetchAll();

// Data prepared above is rendered by frontend/views/pages/admin/rooms.php
