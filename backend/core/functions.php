<?php
/**
 * BACK-END helper functions: redirects, flash messages, CSRF protection,
 * authentication & access control, date validation, pricing and
 * room availability queries.
 * (Presentation-only helpers such as e(), stars() and status_badge()
 *  live in frontend/views/view_helpers.php.)
 */

/* ---------------------------------------------------------------
 *  Shared helpers (used by controllers AND views)
 * ------------------------------------------------------------- */

/** Build a URL relative to the project root. */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function money($amount): string
{
    return CURRENCY . ' ' . number_format((float) $amount, 2);
}

/** Turn the comma-separated facilities column into an array. */
function facilities_list(?string $csv): array
{
    return array_values(array_filter(array_map('trim', explode(',', (string) $csv))));
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/* ---------------------------------------------------------------
 *  Flash messages (shown once on the next page load)
 * ------------------------------------------------------------- */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/* ---------------------------------------------------------------
 *  CSRF protection for every POST form
 * ------------------------------------------------------------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Stop the request if the CSRF token is missing or wrong. */
function verify_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Invalid or expired form submission. Please go back, refresh the page and try again.');
    }
}

/* ---------------------------------------------------------------
 *  Authentication & access control
 * ------------------------------------------------------------- */

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function is_admin(): bool
{
    return is_logged_in() && $_SESSION['user']['role'] === 'admin';
}

/** Redirect to login (remembering where the user wanted to go). */
function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        flash('warning', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_guest_account(): void
{
    require_login();
    if (is_admin()) {
        flash('info', 'Administrators manage bookings from the admin panel.');
        redirect('admin/index.php');
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        flash('danger', 'You do not have permission to access that page.');
        redirect('index.php');
    }
}

/** Store the user in the session after a successful login. */
function login_user(array $user): void
{
    session_regenerate_id(true);   // prevent session fixation
    $_SESSION['user'] = [
        'user_id' => (int) $user['user_id'],
        'name'    => $user['name'],
        'email'   => $user['email'],
        'role'    => $user['role'],
    ];
}

/* ---------------------------------------------------------------
 *  Date validation
 * ------------------------------------------------------------- */

/** True if $value is a real calendar date in YYYY-MM-DD format. */
function is_valid_date(string $value): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d && $d->format('Y-m-d') === $value;
}

/**
 * Validate a check-in / check-out pair.
 * Returns [array $errors, int $nights].
 */
function validate_stay_dates(string $checkIn, string $checkOut): array
{
    $errors = [];
    if (!is_valid_date($checkIn) || !is_valid_date($checkOut)) {
        return [['Please choose valid check-in and check-out dates.'], 0];
    }

    $today = new DateTime('today');
    $in    = new DateTime($checkIn);
    $out   = new DateTime($checkOut);

    if ($in < $today) {
        $errors[] = 'Check-in date cannot be in the past.';
    }
    if ($out <= $in) {
        $errors[] = 'Check-out date must be after the check-in date.';
    }

    $nights = $out > $in ? (int) $in->diff($out)->days : 0;
    if ($nights > MAX_NIGHTS) {
        $errors[] = 'Bookings are limited to ' . MAX_NIGHTS . ' nights.';
    }
    return [$errors, $nights];
}

/* ---------------------------------------------------------------
 *  Pricing (seasonal rates + long-stay discount) — server side
 * ------------------------------------------------------------- */

function get_seasons(PDO $pdo): array
{
    return $pdo->query('SELECT season_name, start_date, end_date, rate_multiplier
                        FROM seasonal_rates ORDER BY start_date')->fetchAll();
}

/**
 * Calculate the full price of a stay, night by night.
 * Each night is charged at the base price × the multiplier of the season
 * it falls in (1.00 if none). Stays of LONG_STAY_NIGHTS or more get a discount.
 */
function calculate_booking_price(PDO $pdo, float $basePrice, string $checkIn, string $checkOut): array
{
    $seasons  = get_seasons($pdo);
    $night    = new DateTime($checkIn);
    $end      = new DateTime($checkOut);
    $subtotal = 0.0;
    $nights   = 0;
    $seasonal = [];   // season name => number of nights

    while ($night < $end) {
        $date       = $night->format('Y-m-d');
        $multiplier = 1.0;
        foreach ($seasons as $s) {
            if ($date >= $s['start_date'] && $date <= $s['end_date']) {
                $multiplier = (float) $s['rate_multiplier'];
                $seasonal[$s['season_name']] = ($seasonal[$s['season_name']] ?? 0) + 1;
                break;
            }
        }
        $subtotal += round($basePrice * $multiplier, 2);
        $nights++;
        $night->modify('+1 day');
    }

    $discount = $nights >= LONG_STAY_NIGHTS ? round($subtotal * LONG_STAY_DISCOUNT, 2) : 0.0;

    return [
        'nights'   => $nights,
        'subtotal' => round($subtotal, 2),
        'discount' => $discount,
        'total'    => round($subtotal - $discount, 2),
        'seasonal' => $seasonal,
    ];
}

/* ---------------------------------------------------------------
 *  Availability
 * ------------------------------------------------------------- */

/**
 * True if no CONFIRMED booking for the room overlaps the given stay.
 * Two stays overlap when (existing.check_in < new.check_out) AND
 * (existing.check_out > new.check_in). Check-out day = next check-in day is OK.
 */
function room_is_available(PDO $pdo, int $roomId, string $checkIn, string $checkOut, ?int $excludeBookingId = null): bool
{
    $sql = "SELECT COUNT(*) FROM bookings
            WHERE room_id = :room
              AND booking_status = 'Confirmed'
              AND check_in  < :out
              AND check_out > :in";
    $params = [':room' => $roomId, ':in' => $checkIn, ':out' => $checkOut];

    if ($excludeBookingId !== null) {
        $sql .= ' AND booking_id <> :exclude';
        $params[':exclude'] = $excludeBookingId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() === 0;
}

/**
 * Active rooms that fit the guests and have no confirmed booking in the range.
 * Optional filters: type, max_price, facility.
 */
function find_available_rooms(PDO $pdo, string $checkIn, string $checkOut, int $guests, array $filters = []): array
{
    $sql = "SELECT r.*,
                   (SELECT ROUND(AVG(rating),1) FROM reviews v WHERE v.room_id = r.room_id) AS avg_rating
            FROM rooms r
            WHERE r.room_status = 'active'
              AND r.max_guests >= :guests
              AND NOT EXISTS (
                    SELECT 1 FROM bookings b
                    WHERE b.room_id = r.room_id
                      AND b.booking_status = 'Confirmed'
                      AND b.check_in  < :out
                      AND b.check_out > :in)";
    $params = [':guests' => $guests, ':in' => $checkIn, ':out' => $checkOut];

    $sql .= apply_room_filters($filters, $params);
    $sql .= ' ORDER BY r.price_per_night';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** Builds extra WHERE clauses for room filters (adds bound parameters). */
function apply_room_filters(array $filters, array &$params): string
{
    $sql = '';
    $types = ['Standard', 'Deluxe', 'Suite', 'Family'];

    if (!empty($filters['type']) && in_array($filters['type'], $types, true)) {
        $sql .= ' AND r.room_type = :type';
        $params[':type'] = $filters['type'];
    }
    if (!empty($filters['max_price']) && is_numeric($filters['max_price'])) {
        $sql .= ' AND r.price_per_night <= :max_price';
        $params[':max_price'] = (float) $filters['max_price'];
    }
    if (!empty($filters['facility'])) {
        $sql .= ' AND r.facilities LIKE :facility';
        $params[':facility'] = '%' . $filters['facility'] . '%';
    }
    return $sql;
}

/** All distinct facilities across active rooms (for the filter dropdown). */
function all_facilities(PDO $pdo): array
{
    $list = [];
    foreach ($pdo->query("SELECT facilities FROM rooms WHERE room_status='active'") as $row) {
        $list = array_merge($list, facilities_list($row['facilities']));
    }
    $list = array_unique($list);
    sort($list);
    return $list;
}

