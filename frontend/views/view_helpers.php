<?php
/**
 * FRONT-END view helpers — small functions used by the templates to
 * format and escape output. They never query the database.
 */

/** Escape a value for safe HTML output (prevents XSS). */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** URL of a room/villa image in frontend/assets/img/. */
function img_url(?string $file): string
{
    $file = basename($file ?: 'default.svg');
    $dir  = __DIR__ . '/../assets/img/';

    // If the photo is missing, fall back to another file with the same name
    // (e.g. room-ocean-breeze.svg) and finally to the default image.
    if (!is_file($dir . $file)) {
        $base = pathinfo($file, PATHINFO_FILENAME);
        $file = 'default.svg';
        foreach (['jpg', 'jpeg', 'png', 'webp', 'svg'] as $ext) {
            if (is_file($dir . $base . '.' . $ext)) {
                $file = $base . '.' . $ext;
                break;
            }
        }
    }
    return asset_url('img/' . rawurlencode($file));
}

function format_date(string $date): string
{
    return date('d M Y', strtotime($date));
}

/** Bootstrap badge colour for a booking status. */
function status_badge(string $status): string
{
    $map = ['Pending' => 'warning text-dark', 'Confirmed' => 'success', 'Cancelled' => 'secondary'];
    $cls = $map[$status] ?? 'light';
    return '<span class="badge bg-' . $cls . '">' . e($status) . '</span>';
}

/** Render 1–5 stars as text for ratings. */
function stars(float $rating): string
{
    $full = (int) round($rating);
    return '<span class="stars" aria-label="' . e($rating) . ' out of 5">'
         . str_repeat('★', $full) . str_repeat('☆', 5 - $full) . '</span>';
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** URL of a file in frontend/assets/ (css, js, img, vendor). */
function asset_url(string $path): string
{
    return url('frontend/assets/' . ltrim($path, '/'));
}

/** Small inline form with Confirm / Cancel buttons for one booking. */
function booking_action_buttons(array $b): string
{
    $html = '';
    $id   = (int) $b['booking_id'];
    if ($b['booking_status'] === 'Pending') {
        $html .= '<form method="post" class="d-inline">' . csrf_field()
              .  '<input type="hidden" name="booking_id" value="' . $id . '">'
              .  '<button class="btn btn-sm btn-success" name="booking_action" value="confirm" title="Confirm booking"><i class="bi bi-check-lg"></i><span class="d-none d-xl-inline"> Confirm</span></button></form> ';
    }
    if ($b['booking_status'] !== 'Cancelled') {
        $html .= '<form method="post" class="d-inline js-confirm" data-confirm="Cancel booking #' . $id . '?">' . csrf_field()
              .  '<input type="hidden" name="booking_id" value="' . $id . '">'
              .  '<button class="btn btn-sm btn-outline-danger" name="booking_action" value="cancel" title="Cancel booking"><i class="bi bi-x-lg"></i><span class="d-none d-xl-inline"> Cancel</span></button></form>';
    }
    return $html ?: '<span class="text-muted small">—</span>';
}

/** Bootstrap validation class + message for a field. */
function field_state(array $errors, string $key): string
{
    return isset($errors[$key]) ? ' is-invalid' : '';
}
