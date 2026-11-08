<?php
/**
 * BACK-END CONTROLLER
 *
 * Room Booking Form — registered guests choose a room and dates.
 * JavaScript shows a live price estimate; PHP re-validates everything,
 * recalculates the price on the server and stores a Pending booking.
 */
require_once __DIR__ . '/../core/init.php';
require_guest_account();

$pageTitle = 'Book a Room';
$user      = current_user();

// Full account details (for pre-filling contact information)
$acc = $pdo->prepare('SELECT name, email, contact_number FROM users WHERE user_id = ?');
$acc->execute([$user['user_id']]);
$account = $acc->fetch();

$rooms = $pdo->query("SELECT room_id, room_name, room_type, price_per_night, max_guests
                      FROM rooms WHERE room_status = 'active' ORDER BY price_per_night")->fetchAll();

// Defaults (from query string when coming from a room / search page)
$form = [
    'room_id'         => (int) ($_GET['room_id'] ?? ($rooms[0]['room_id'] ?? 0)),
    'check_in'        => $_GET['check_in']  ?? date('Y-m-d'),
    'check_out'       => $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day')),
    'num_guests'      => (int) ($_GET['guests'] ?? 1),
    'name'            => $account['name'],
    'contact_number'  => $account['contact_number'],
    'special_request' => '',
];
$errors = [];

/* ---------------- Process submission ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $form = [
        'room_id'         => (int) ($_POST['room_id'] ?? 0),
        'check_in'        => trim($_POST['check_in'] ?? ''),
        'check_out'       => trim($_POST['check_out'] ?? ''),
        'num_guests'      => (int) ($_POST['num_guests'] ?? 0),
        'name'            => trim($_POST['name'] ?? ''),
        'contact_number'  => trim($_POST['contact_number'] ?? ''),
        'special_request' => trim($_POST['special_request'] ?? ''),
    ];

    // 1. Guest details
    if (mb_strlen($form['name']) < 3 || mb_strlen($form['name']) > 100 || !preg_match("/^[\p{L} .'-]+$/u", $form['name'])) {
        $errors[] = 'Please enter your full name (letters only, 3–100 characters).';
    }
    if (!preg_match('/^(?:\+94|0)\d{9}$/', str_replace(' ', '', $form['contact_number']))) {
        $errors[] = 'Please enter a valid Sri Lankan contact number, e.g. 0771234567 or +94771234567.';
    }
    if (mb_strlen($form['special_request']) > 500) {
        $errors[] = 'Special requests must be 500 characters or fewer.';
    }

    // 2. Room exists and is active
    $rs = $pdo->prepare("SELECT * FROM rooms WHERE room_id = ? AND room_status = 'active'");
    $rs->execute([$form['room_id']]);
    $room = $rs->fetch();
    if (!$room) {
        $errors[] = 'Please choose a valid room.';
    }

    // 3. Dates and guest count
    [$dateErrors, $nights] = validate_stay_dates($form['check_in'], $form['check_out']);
    $errors = array_merge($errors, $dateErrors);

    if ($room && ($form['num_guests'] < 1 || $form['num_guests'] > (int) $room['max_guests'])) {
        $errors[] = 'This room allows 1 to ' . (int) $room['max_guests'] . ' guests.';
    }

    if (!$errors) {
        // 4. Availability against CONFIRMED bookings
        if (!room_is_available($pdo, $room['room_id'], $form['check_in'], $form['check_out'])) {
            $errors[] = 'Sorry, this room is already booked for some of those dates. Please choose other dates or another room.';
        }

        // 5. Same guest must not request the same room twice for overlapping dates
        $dup = $pdo->prepare("SELECT COUNT(*) FROM bookings
                              WHERE user_id = ? AND room_id = ? AND booking_status = 'Pending'
                                AND check_in < ? AND check_out > ?");
        $dup->execute([$user['user_id'], $room['room_id'], $form['check_out'], $form['check_in']]);
        if ($dup->fetchColumn() > 0) {
            $errors[] = 'You already have a pending request for this room on overlapping dates.';
        }
    }

    if (!$errors) {
        // 6. Server-side price calculation (never trust the price shown in the browser)
        $quote = calculate_booking_price($pdo, (float) $room['price_per_night'], $form['check_in'], $form['check_out']);

        $pdo->beginTransaction();
        try {
            // Keep the guest's profile details up to date
            $up = $pdo->prepare('UPDATE users SET name = ?, contact_number = ? WHERE user_id = ?');
            $up->execute([$form['name'], str_replace(' ', '', $form['contact_number']), $user['user_id']]);
            $_SESSION['user']['name'] = $form['name'];

            $ins = $pdo->prepare(
                "INSERT INTO bookings (user_id, room_id, check_in, check_out, num_guests, nights, total_amount, special_request, booking_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')"
            );
            $ins->execute([
                $user['user_id'], $room['room_id'], $form['check_in'], $form['check_out'],
                $form['num_guests'], $quote['nights'], $quote['total'],
                $form['special_request'] !== '' ? $form['special_request'] : null,
            ]);
            $newId = (int) $pdo->lastInsertId();
            $pdo->commit();
        } catch (Throwable $ex) {
            $pdo->rollBack();
            error_log($ex->getMessage());
            $errors[] = 'Something went wrong while saving your booking. Please try again.';
        }

        if (!$errors) {
            flash('success', 'Booking request #' . $newId . ' sent! Total ' . money($quote['total'])
                . ' for ' . $quote['nights'] . ' night(s). Status: Pending — the villa will confirm it shortly.');
            redirect('my_bookings.php?highlight=' . $newId);
        }
    }
}

// Pricing data for the JavaScript estimate (same rules as the server)
$seasons  = get_seasons($pdo);
$jsConfig = [
    'seasons'          => array_map(fn($s) => [
        'name' => $s['season_name'], 'start' => $s['start_date'], 'end' => $s['end_date'], 'multiplier' => (float) $s['rate_multiplier'],
    ], $seasons),
    'longStayNights'   => LONG_STAY_NIGHTS,
    'longStayDiscount' => LONG_STAY_DISCOUNT,
    'maxNights'        => MAX_NIGHTS,
    'currency'         => CURRENCY,
];

// Data prepared above is rendered by frontend/views/pages/book.php
