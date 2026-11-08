<?php
/**
 * FRONT-END layout — shared page header + navigation bar.
 * Set $pageTitle and $activePage before including this file.
 */
$pageTitle  = $pageTitle  ?? SITE_NAME;
$activePage = $activePage ?? '';
$user       = current_user();

function nav_active(string $page, string $active): string
{
    return $page === $active ? ' active" aria-current="page' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Coral Bay Villa — boutique beachside villa in Mirissa, Sri Lanka. Browse rooms, check availability and book online.">
    <title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?></title>

    <!-- Bootstrap 5.3.3 + Bootstrap Icons 1.11.3 (bundled locally so the site works offline) -->
    <link href="<?= asset_url('vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset_url('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
    <!-- Google Fonts (falls back to system fonts when offline) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Custom styles -->
    <link href="<?= asset_url('css/style.css') ?>" rel="stylesheet">
</head>
<body data-base-url="<?= e(BASE_URL) ?>">

<a class="visually-hidden-focusable skip-link" href="#main">Skip to content</a>

<nav class="navbar navbar-expand-lg navbar-dark site-nav sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('index.php') ?>">
            <i class="bi bi-water"></i>
            <span><?= e(SITE_NAME) ?> <small class="brand-sub">Mirissa</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link<?= nav_active('home', $activePage) ?>" href="<?= url('index.php') ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('rooms', $activePage) ?>" href="<?= url('rooms.php') ?>">Rooms</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('search', $activePage) ?>" href="<?= url('search.php') ?>">Availability</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('index.php#gallery') ?>">Gallery</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('index.php#location') ?>">Location</a></li>
            </ul>

            <ul class="navbar-nav align-items-lg-center gap-lg-2">
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link<?= nav_active('admin', $activePage) ?>" href="<?= url('admin/index.php') ?>"><i class="bi bi-speedometer2"></i> Admin Panel</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link<?= nav_active('my-bookings', $activePage) ?>" href="<?= url('my_bookings.php') ?>"><i class="bi bi-calendar-check"></i> My Bookings</a></li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?= e(explode(' ', $user['name'])[0]) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted"><?= e($user['email']) ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="<?= url('logout.php') ?>" method="post" class="px-3">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-box-arrow-right"></i> Log out</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link<?= nav_active('login', $activePage) ?>" href="<?= url('login.php') ?>">Log in</a></li>
                    <li class="nav-item"><a class="nav-link<?= nav_active('register', $activePage) ?>" href="<?= url('register.php') ?>">Register</a></li>
                <?php endif; ?>
                <?php if (!$user || $user['role'] !== 'admin'): ?>
                    <li class="nav-item ms-lg-2"><a class="btn btn-sunset btn-sm px-3" href="<?= url('search.php') ?>">Book Now</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main id="main">
<?php $flashes = get_flashes(); if ($flashes): ?>
    <div class="container mt-3" id="flash-area">
        <?php foreach ($flashes as $f): ?>
            <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($f['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
