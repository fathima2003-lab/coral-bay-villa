<?php
/**
 * BACK-END CONTROLLER
 *
 * Rooms & Accommodation — all active rooms with search filters
 * (room type, max price, facility, guests) and sorting.
 */
require_once __DIR__ . '/../core/init.php';

$pageTitle  = 'Rooms & Accommodation';
$activePage = 'rooms';

$filters = [
    'type'      => $_GET['type'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'facility'  => trim($_GET['facility'] ?? ''),
];
// The slider's top value (30,000) means "any price"
if ($filters['max_price'] !== '' && (float) $filters['max_price'] >= 30000) {
    $filters['max_price'] = '';
}
$guests = max(1, min(10, (int) ($_GET['guests'] ?? 1)));
$sort   = $_GET['sort'] ?? 'price_asc';

$sortSql = [
    'price_asc'  => 'r.price_per_night ASC',
    'price_desc' => 'r.price_per_night DESC',
    'guests'     => 'r.max_guests DESC',
    'rating'     => 'avg_rating DESC',
][$sort] ?? 'r.price_per_night ASC';

$params = [':guests' => $guests];
$sql = "SELECT r.*, (SELECT ROUND(AVG(rating),1) FROM reviews v WHERE v.room_id = r.room_id) AS avg_rating
        FROM rooms r
        WHERE r.room_status = 'active' AND r.max_guests >= :guests";
$sql .= apply_room_filters($filters, $params);
$sql .= " ORDER BY $sortSql";   // $sortSql comes only from the whitelist above

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

$facilityOptions = all_facilities($pdo);
$stay = null;

// Data prepared above is rendered by frontend/views/pages/rooms.php
