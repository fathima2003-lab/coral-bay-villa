<?php
/**
 * BACK-END CONTROLLER
 *
 * My Bookings — a registered guest sees ONLY their own bookings and can
 * cancel bookings that are still Pending.
 */
require_once __DIR__ . '/../core/init.php';
require_guest_account();

$pageTitle  = 'My Bookings';
$activePage = 'my-bookings';
$userId     = current_user()['user_id'];

/* ---------- Cancel a pending booking ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    verify_csrf();
    $bookingId = (int) $_POST['cancel_id'];

    // user_id in the WHERE clause stops guests cancelling other people's bookings
    $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled'
                           WHERE booking_id = ? AND user_id = ? AND booking_status = 'Pending'");
    $stmt->execute([$bookingId, $userId]);

    if ($stmt->rowCount() === 1) {
        flash('success', "Booking #$bookingId has been cancelled.");
    } else {
        flash('danger', 'Only your own pending bookings can be cancelled.');
    }
    redirect('my_bookings.php');
}

/* ---------- Load this guest's bookings ---------- */
$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT b.*, r.room_name, r.room_type, r.image,
               (SELECT COUNT(*) FROM reviews v WHERE v.booking_id = b.booking_id) AS reviewed
        FROM bookings b
        JOIN rooms r ON r.room_id = b.room_id
        WHERE b.user_id = :uid";
$params = [':uid' => $userId];
if (in_array($statusFilter, ['Pending', 'Confirmed', 'Cancelled'], true)) {
    $sql .= ' AND b.booking_status = :status';
    $params[':status'] = $statusFilter;
}
$sql .= ' ORDER BY b.check_in DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$counts = $pdo->prepare("SELECT booking_status, COUNT(*) AS n FROM bookings WHERE user_id = ? GROUP BY booking_status");
$counts->execute([$userId]);
$counts = array_column($counts->fetchAll(), 'n', 'booking_status');

$highlight = (int) ($_GET['highlight'] ?? 0);
$today     = date('Y-m-d');

// Data prepared above is rendered by frontend/views/pages/my_bookings.php
