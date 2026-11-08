<?php
/**
 * Confirm / cancel booking actions used by the admin dashboard and
 * the admin bookings page.
 */

/**
 * Process a POSTed booking action: action = confirm | cancel, booking_id.
 * Confirming re-checks for overlapping CONFIRMED bookings inside a
 * transaction (row lock on the room) so two admins can't double-book.
 */
function handle_booking_action(PDO $pdo, string $returnTo): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['booking_action'])) {
        return;
    }
    verify_csrf();

    $action    = $_POST['booking_action'];
    $bookingId = (int) ($_POST['booking_id'] ?? 0);

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE booking_id = ? FOR UPDATE');
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch();

        if (!$booking) {
            throw new RuntimeException('Booking not found.');
        }

        if ($action === 'confirm') {
            if ($booking['booking_status'] !== 'Pending') {
                throw new RuntimeException('Only pending bookings can be confirmed.');
            }
            // Lock the room row so concurrent confirmations for this room run one at a time
            $pdo->prepare('SELECT room_id FROM rooms WHERE room_id = ? FOR UPDATE')->execute([$booking['room_id']]);

            if (!room_is_available($pdo, (int) $booking['room_id'], $booking['check_in'], $booking['check_out'], $bookingId)) {
                throw new RuntimeException("Booking #$bookingId overlaps an already confirmed booking for this room. Cancel it or confirm a different request.");
            }
            $pdo->prepare("UPDATE bookings SET booking_status = 'Confirmed' WHERE booking_id = ?")->execute([$bookingId]);
            $message = "Booking #$bookingId confirmed.";

        } elseif ($action === 'cancel') {
            if ($booking['booking_status'] === 'Cancelled') {
                throw new RuntimeException('That booking is already cancelled.');
            }
            $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = ?")->execute([$bookingId]);
            $message = "Booking #$bookingId cancelled.";

        } else {
            throw new RuntimeException('Unknown action.');
        }

        $pdo->commit();
        flash('success', $message);
    } catch (RuntimeException $ex) {
        $pdo->rollBack();
        flash('danger', $ex->getMessage());
    } catch (Throwable $ex) {
        $pdo->rollBack();
        error_log($ex->getMessage());
        flash('danger', 'Could not update the booking. Please try again.');
    }
    redirect($returnTo);
}
