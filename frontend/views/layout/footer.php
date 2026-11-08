<?php /** FRONT-END layout — shared footer + scripts */ ?>
</main>

<footer class="site-footer mt-5">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-5">
                <h5 class="footer-brand"><i class="bi bi-water"></i> <?= e(SITE_NAME) ?></h5>
                <p class="small mb-2">A boutique beachside villa on the south coast of Sri Lanka — whale watching, surf and golden sunsets on your doorstep.</p>
                <p class="small mb-0 text-white-50">This is a fictional villa created for an academic assignment.</p>
            </div>
            <div class="col-6 col-md-3">
                <h6>Explore</h6>
                <ul class="list-unstyled small">
                    <li><a href="<?= url('rooms.php') ?>">Rooms &amp; Suites</a></li>
                    <li><a href="<?= url('search.php') ?>">Check Availability</a></li>
                    <li><a href="<?= url('index.php#gallery') ?>">Gallery</a></li>
                    <li><a href="<?= url('my_bookings.php') ?>">My Bookings</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-4">
                <h6>Contact</h6>
                <ul class="list-unstyled small">
                    <li><i class="bi bi-geo-alt"></i> 24 Beach Road, Mirissa, Sri Lanka</li>
                    <li><i class="bi bi-telephone"></i> +94 41 225 0000</li>
                    <li><i class="bi bi-envelope"></i> stay@coralbay.lk</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary">
        <p class="small text-center text-white-50 mb-0">&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?> · IT1208 Web Technologies CA2</p>
    </div>
</footer>

<script src="<?= asset_url('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset_url('js/main.js') ?>"></script>
<?php if (!empty($extraScripts)) foreach ($extraScripts as $src): ?>
<script src="<?= e($src) ?>"></script>
<?php endforeach; ?>
</body>
</html>
