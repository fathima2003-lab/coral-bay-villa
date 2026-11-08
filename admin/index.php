<?php
/**
 * Entry point: admin/index.php
 * 1) back-end controller handles the request and prepares data
 * 2) front-end view renders the HTML
 */
require __DIR__ . '/../backend/controllers/admin/dashboard.php';
require __DIR__ . '/../frontend/views/pages/admin/dashboard.php';
