<?php
/**
 * BACK-END API — AJAX endpoint — checks whether a room is free for a date range.
 *   GET backend/api/availability.php?room_id=1&check_in=2026-12-01&check_out=2026-12-04&guests=2
 * Returns JSON: { ok, available, message, nights, quote }
 */
require_once __DIR__ . '/../core/init.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

$roomId   = (int) ($_GET['room_id'] ?? 0);
$checkIn  = (string) ($_GET['check_in'] ?? '');
$checkOut = (string) ($_GET['check_out'] ?? '');
$guests   = (int) ($_GET['guests'] ?? 1);

$stmt = $pdo->prepare("SELECT room_id, room_name, price_per_night, max_guests FROM rooms WHERE room_id = ? AND room_status = 'active'");
$stmt->execute([$roomId]);
$room = $stmt->fetch();
if (!$room) {
    respond(['ok' => false, 'message' => 'Room not found.'], 404);
}

[$errors, $nights] = validate_stay_dates($checkIn, $checkOut);
if ($errors) {
    respond(['ok' => false, 'message' => $errors[0]], 422);
}
if ($guests < 1 || $guests > (int) $room['max_guests']) {
    respond(['ok' => true, 'available' => false, 'message' => $room['room_name'] . ' sleeps up to ' . $room['max_guests'] . ' guests.']);
}

$available = room_is_available($pdo, $roomId, $checkIn, $checkOut);
$quote     = calculate_booking_price($pdo, (float) $room['price_per_night'], $checkIn, $checkOut);

respond([
    'ok'        => true,
    'available' => $available,
    'message'   => $available
        ? 'Available for your dates!'
        : 'Already booked for some of these dates. Please try other dates.',
    'nights'    => $quote['nights'],
    'quote'     => [
        'subtotal'  => $quote['subtotal'],
        'discount'  => $quote['discount'],
        'total'     => $quote['total'],
        'formatted' => money($quote['total']),
    ],
]);
