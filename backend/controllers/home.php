<?php
/**
 * BACK-END CONTROLLER
 *
 * Home page — introduction, facilities, featured rooms, gallery,
 * guest reviews and location map.
 */
require_once __DIR__ . '/../core/init.php';

$pageTitle  = 'Beachside Villa in Mirissa';
$activePage = 'home';

// Three featured active rooms with their average rating
$featured = $pdo->query(
    "SELECT r.*, (SELECT ROUND(AVG(rating),1) FROM reviews v WHERE v.room_id = r.room_id) AS avg_rating
     FROM rooms r WHERE r.room_status = 'active'
     ORDER BY r.price_per_night DESC LIMIT 3"
)->fetchAll();

// Latest guest reviews
$reviews = $pdo->query(
    "SELECT v.rating, v.comment, v.created_at, u.name, r.room_name
     FROM reviews v
     JOIN users u ON u.user_id = v.user_id
     JOIN rooms r ON r.room_id = v.room_id
     ORDER BY v.created_at DESC LIMIT 3"
)->fetchAll();

$gallery = [
    ['villa-hero.svg',        'The villa at golden hour'],
    ['villa-pool.svg',        'Infinity pool facing the bay'],
    ['villa-beach.svg',       'Mirissa beach, two minutes away'],
    ['room-sunset-suite.svg', 'Sunset Sea-View Suite'],
    ['room-coconut-grove.svg','Coconut Grove Deluxe'],
    ['villa-dining.svg',      'Seafood dinners on the terrace'],
];

$today    = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Data prepared above is rendered by frontend/views/pages/home.php
