<?php
/**
 * BACK-END CONTROLLER
 *
 * Room details — gallery, facilities, live availability check (AJAX),
 * guest reviews and review form for guests who have completed a stay.
 */
require_once __DIR__ . '/../core/init.php';

$roomId = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_id = ? AND room_status = 'active'");
$stmt->execute([$roomId]);
$room = $stmt->fetch();

if (!$room) {
    flash('warning', 'That room could not be found.');
    redirect('rooms.php');
}

/* ---------- Handle a new review (POST) ---------- */
$reviewErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    verify_csrf();
    require_guest_account();

    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    $rating    = (int) ($_POST['rating'] ?? 0);
    $comment   = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5)                        $reviewErrors[] = 'Please choose a rating from 1 to 5 stars.';
    if (mb_strlen($comment) < 10 || mb_strlen($comment) > 1000) $reviewErrors[] = 'Your review should be 10–1000 characters.';

    // The booking must belong to this guest, be for this room, be confirmed,
    // be finished, and not already reviewed.
    $chk = $pdo->prepare(
        "SELECT b.booking_id FROM bookings b
         LEFT JOIN reviews v ON v.booking_id = b.booking_id
         WHERE b.booking_id = ? AND b.user_id = ? AND b.room_id = ?
           AND b.booking_status = 'Confirmed' AND b.check_out <= CURDATE()
           AND v.review_id IS NULL"
    );
    $chk->execute([$bookingId, current_user()['user_id'], $roomId]);
    if (!$chk->fetch()) $reviewErrors[] = 'You can only review a completed, confirmed stay in this room.';

    if (!$reviewErrors) {
        $ins = $pdo->prepare('INSERT INTO reviews (booking_id, user_id, room_id, rating, comment) VALUES (?,?,?,?,?)');
        $ins->execute([$bookingId, current_user()['user_id'], $roomId, $rating, $comment]);
        flash('success', 'Thank you! Your review has been published.');
        redirect('room.php?id=' . $roomId . '#reviews');
    }
}

/* ---------- Data for the page ---------- */
$images = $pdo->prepare('SELECT file_name, caption FROM room_images WHERE room_id = ?');
$images->execute([$roomId]);
$images = $images->fetchAll();
if (!$images) $images = [['file_name' => $room['image'], 'caption' => $room['room_name']]];

$rv = $pdo->prepare(
    'SELECT v.rating, v.comment, v.created_at, u.name
     FROM reviews v JOIN users u ON u.user_id = v.user_id
     WHERE v.room_id = ? ORDER BY v.created_at DESC'
);
$rv->execute([$roomId]);
$reviews   = $rv->fetchAll();
$avgRating = $reviews ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : null;

// Completed stays this guest can still review
$reviewable = [];
if (is_logged_in() && !is_admin()) {
    $q = $pdo->prepare(
        "SELECT b.booking_id, b.check_in, b.check_out FROM bookings b
         LEFT JOIN reviews v ON v.booking_id = b.booking_id
         WHERE b.user_id = ? AND b.room_id = ? AND b.booking_status = 'Confirmed'
           AND b.check_out <= CURDATE() AND v.review_id IS NULL"
    );
    $q->execute([current_user()['user_id'], $roomId]);
    $reviewable = $q->fetchAll();
}

$checkIn  = $_GET['check_in']  ?? date('Y-m-d');
$checkOut = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
$guests   = (int) ($_GET['guests'] ?? 1);

$pageTitle  = $room['room_name'];
$activePage = 'rooms';

// Data prepared above is rendered by frontend/views/pages/room.php
