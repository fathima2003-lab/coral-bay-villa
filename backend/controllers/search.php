<?php
/**
 * BACK-END CONTROLLER
 *
 * Room Availability Search — visitors pick dates and number of guests;
 * only active rooms with enough capacity and no overlapping CONFIRMED
 * booking are shown.
 */
require_once __DIR__ . '/../core/init.php';

$pageTitle  = 'Check Availability';
$activePage = 'search';

$checkIn  = $_GET['check_in']  ?? '';
$checkOut = $_GET['check_out'] ?? '';
$guests   = max(1, min(10, (int) ($_GET['guests'] ?? 2)));
$filters  = ['type' => $_GET['type'] ?? '', 'max_price' => $_GET['max_price'] ?? ''];

$searched = $checkIn !== '' || $checkOut !== '';
$errors   = [];
$rooms    = [];
$nights   = 0;

if ($searched) {
    [$errors, $nights] = validate_stay_dates($checkIn, $checkOut);
    if (!$errors) {
        $rooms = find_available_rooms($pdo, $checkIn, $checkOut, $guests, $filters);
        // Price each room for the whole stay (seasonal rates + long-stay discount)
        foreach ($rooms as &$room) {
            $room['quote'] = calculate_booking_price($pdo, (float) $room['price_per_night'], $checkIn, $checkOut);
        }
        unset($room);
    }
}

$stay = $searched && !$errors ? ['check_in' => $checkIn, 'check_out' => $checkOut, 'guests' => $guests] : null;

// Data prepared above is rendered by frontend/views/pages/search.php
