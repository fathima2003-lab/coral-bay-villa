<?php
/**
 * Entry point: my_bookings.php
 * 1) back-end controller handles the request and prepares data
 * 2) front-end view renders the HTML
 */
require __DIR__ . '/backend/controllers/my_bookings.php';
require __DIR__ . '/frontend/views/pages/my_bookings.php';
