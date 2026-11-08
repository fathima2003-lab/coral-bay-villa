<?php
/**
 * BACK-END CONTROLLER
 *
 * Administrator dashboard — booking statistics, charts, pending
 * requests and upcoming arrivals.
 */
require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/admin_actions.php';
require_admin();
handle_booking_action($pdo, 'admin/index.php');

$pageTitle  = 'Dashboard';
$activePage = 'admin';
$adminTab   = 'dashboard';

/* ---------- Headline statistics ---------- */
$stats = $pdo->query(
    "SELECT
        COUNT(*)                                                      AS total,
        SUM(booking_status = 'Pending')                               AS pending,
        SUM(booking_status = 'Confirmed')                             AS confirmed,
        SUM(booking_status = 'Cancelled')                             AS cancelled,
        COALESCE(SUM(CASE WHEN booking_status = 'Confirmed' THEN total_amount END), 0) AS revenue
     FROM bookings"
)->fetch();

$activeRooms = (int) $pdo->query("SELECT COUNT(*) FROM rooms WHERE room_status = 'active'")->fetchColumn();
$guestCount  = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'guest'")->fetchColumn();

// Occupancy this month = confirmed room-nights in this month / available room-nights
$monthStart  = date('Y-m-01');
$monthEnd    = date('Y-m-d', strtotime('first day of next month'));
$daysInMonth = (int) date('t');
$occ = $pdo->prepare(
    "SELECT COALESCE(SUM(DATEDIFF(LEAST(check_out, :end1), GREATEST(check_in, :start1))), 0)
     FROM bookings
     WHERE booking_status = 'Confirmed' AND check_in < :end2 AND check_out > :start2"
);
$occ->execute([':start1' => $monthStart, ':end1' => $monthEnd, ':start2' => $monthStart, ':end2' => $monthEnd]);
$bookedNights = (int) $occ->fetchColumn();
$occupancy    = $activeRooms ? round($bookedNights / ($activeRooms * $daysInMonth) * 100, 1) : 0;

/* ---------- Chart data: bookings per month (last 6 months, by booking date) ---------- */
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $months[date('Y-m', strtotime("first day of -$i month"))] = ['confirmed' => 0, 'pending' => 0, 'cancelled' => 0, 'revenue' => 0];
}
$rows = $pdo->query(
    "SELECT DATE_FORMAT(booking_date, '%Y-%m') AS ym, booking_status, COUNT(*) AS n,
            SUM(total_amount) AS amount
     FROM bookings
     WHERE booking_date >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 5 MONTH)
     GROUP BY ym, booking_status"
)->fetchAll();
foreach ($rows as $r) {
    if (!isset($months[$r['ym']])) continue;
    $months[$r['ym']][strtolower($r['booking_status'])] = (int) $r['n'];
    if ($r['booking_status'] === 'Confirmed') $months[$r['ym']]['revenue'] = (float) $r['amount'];
}
$chartData = [
    'labels'    => array_map(fn($ym) => date('M Y', strtotime($ym . '-01')), array_keys($months)),
    'confirmed' => array_column($months, 'confirmed'),
    'pending'   => array_column($months, 'pending'),
    'cancelled' => array_column($months, 'cancelled'),
];

/* ---------- Revenue by room ---------- */
$byRoom = $pdo->query(
    "SELECT r.room_name, COUNT(b.booking_id) AS bookings,
            COALESCE(SUM(b.total_amount), 0) AS revenue
     FROM rooms r
     LEFT JOIN bookings b ON b.room_id = r.room_id AND b.booking_status = 'Confirmed'
     GROUP BY r.room_id, r.room_name
     ORDER BY revenue DESC"
)->fetchAll();
$maxRevenue = max(array_column($byRoom, 'revenue') ?: [1]) ?: 1;

/* ---------- Pending requests & upcoming arrivals ---------- */
$pending = $pdo->query(
    "SELECT b.*, u.name AS guest_name, u.email, r.room_name
     FROM bookings b JOIN users u ON u.user_id = b.user_id JOIN rooms r ON r.room_id = b.room_id
     WHERE b.booking_status = 'Pending'
     ORDER BY b.check_in ASC LIMIT 8"
)->fetchAll();

$arrivals = $pdo->query(
    "SELECT b.booking_id, b.check_in, b.nights, b.num_guests, u.name AS guest_name, u.contact_number, r.room_name
     FROM bookings b JOIN users u ON u.user_id = b.user_id JOIN rooms r ON r.room_id = b.room_id
     WHERE b.booking_status = 'Confirmed' AND b.check_in BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
     ORDER BY b.check_in"
)->fetchAll();

$extraScripts = [asset_url('vendor/chartjs/chart.umd.js'), asset_url('js/admin.js')];

// Data prepared above is rendered by frontend/views/pages/admin/dashboard.php
