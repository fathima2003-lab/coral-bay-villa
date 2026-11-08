<?php
/** FRONT-END partial — admin section header + tab navigation. Set $adminTab before including. */
$adminTab = $adminTab ?? '';
$tabs = [
    'dashboard' => ['index.php',     'bi-speedometer2', 'Dashboard'],
    'bookings'  => ['bookings.php',  'bi-calendar-check', 'Bookings'],
    'rooms'     => ['rooms.php',     'bi-door-open', 'Rooms'],
    'add-room'  => ['room_form.php', 'bi-plus-circle', 'Add Room'],
];
?>
<header class="page-header admin-header">
    <div class="container">
        <p class="eyebrow mb-1"><i class="bi bi-shield-lock"></i> Administrator</p>
        <h1 class="mb-3"><?= e($pageTitle) ?></h1>
        <ul class="nav admin-tabs flex-nowrap overflow-auto">
            <?php foreach ($tabs as $key => [$file, $icon, $label]): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $adminTab === $key ? 'active' : '' ?>" href="<?= url('admin/' . $file) ?>">
                        <i class="bi <?= $icon ?>"></i> <?= $label ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</header>
