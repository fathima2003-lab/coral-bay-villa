<?php
/**
 * BACK-END CONTROLLER
 *
 * Admin — all reservations with filters; confirm or cancel bookings.
 * Pending requests that clash with a confirmed booking are flagged.
 */
require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/admin_actions.php';
require_admin();

$query = $_SERVER['QUERY_STRING'] ?? '';
handle_booking_action($pdo, 'admin/bookings.php' . ($query ? '?' . $query : ''));

$pageTitle  = 'Bookings';
$activePage = 'admin';
$adminTab   = 'bookings';

$status = $_GET['status'] ?? '';
$roomId = (int) ($_GET['room_id'] ?? 0);
$search = trim($_GET['q'] ?? '');
$from   = $_GET['from'] ?? '';

$sql = "SELECT b.*, u.name AS guest_name, u.email, u.contact_number, r.room_name,
               EXISTS (SELECT 1 FROM bookings c
                       WHERE c.room_id = b.room_id AND c.booking_status = 'Confirmed'
                         AND c.booking_id <> b.booking_id
                         AND c.check_in < b.check_out AND c.check_out > b.check_in) AS has_conflict
        FROM bookings b
        JOIN users u ON u.user_id = b.user_id
        JOIN rooms r ON r.room_id = b.room_id
        WHERE 1 = 1";
$params = [];

if (in_array($status, ['Pending', 'Confirmed', 'Cancelled'], true)) {
    $sql .= ' AND b.booking_status = :status';
    $params[':status'] = $status;
}
if ($roomId > 0) {
    $sql .= ' AND b.room_id = :room';
    $params[':room'] = $roomId;
}
if ($search !== '') {
    $sql .= ' AND (u.name LIKE :q1 OR u.email LIKE :q2 OR b.booking_id = :qid)';
    $params[':q1'] = $params[':q2'] = '%' . $search . '%';
    $params[':qid'] = ctype_digit($search) ? (int) $search : 0;
}
if (is_valid_date($from)) {
    $sql .= ' AND b.check_out >= :from';
    $params[':from'] = $from;
}
$sql .= " ORDER BY FIELD(b.booking_status, 'Pending', 'Confirmed', 'Cancelled'), b.check_in";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$rooms = $pdo->query('SELECT room_id, room_name FROM rooms ORDER BY room_name')->fetchAll();

// Data prepared above is rendered by frontend/views/pages/admin/bookings.php
