<?php
/**
 * FRONT-END VIEW — Home page — introduction, facilities, featured rooms, gallery,
 * Only HTML output: all data comes from backend/controllers/home.php
 */
require __DIR__ . '/../layout/header.php';
?>

<!-- ===== Hero ===== -->
<section class="hero" style="background-image:url('<?= img_url('villa-hero.jpg') ?>')">
    <div class="hero-overlay"></div>
    <div class="container position-relative">
        <div class="row align-items-center g-4">
            <div class="col-lg-7 text-white">
                <p class="eyebrow mb-2"><i class="bi bi-geo-alt-fill"></i> Mirissa · Southern Province · Sri Lanka</p>
                <h1 class="display-4 hero-title">Wake up to the sound of the Indian Ocean</h1>
                <p class="lead mb-4">Six hand-crafted rooms, an infinity pool and a private path to Mirissa beach — your quiet base for whale watching, surfing and sunsets at Parrot Rock.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= url('search.php') ?>" class="btn btn-sunset btn-lg"><i class="bi bi-calendar2-check"></i> Book Now</a>
                    <a href="<?= url('rooms.php') ?>" class="btn btn-outline-light btn-lg">View Rooms</a>
                </div>
            </div>

            <!-- Quick availability search -->
            <div class="col-lg-5">
                <form class="card quick-search shadow-lg js-date-form" action="<?= url('search.php') ?>" method="get" novalidate>
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Check availability</h2>
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="qs-in" class="form-label small">Check-in</label>
                                <input type="date" class="form-control js-check-in" id="qs-in" name="check_in" min="<?= $today ?>" value="<?= $today ?>" required>
                            </div>
                            <div class="col-6">
                                <label for="qs-out" class="form-label small">Check-out</label>
                                <input type="date" class="form-control js-check-out" id="qs-out" name="check_out" min="<?= $tomorrow ?>" value="<?= $tomorrow ?>" required>
                            </div>
                            <div class="col-12">
                                <label for="qs-guests" class="form-label small">Guests</label>
                                <select class="form-select" id="qs-guests" name="guests">
                                    <?php for ($g = 1; $g <= 5; $g++): ?>
                                        <option value="<?= $g ?>" <?= $g === 2 ? 'selected' : '' ?>><?= $g ?> guest<?= $g > 1 ? 's' : '' ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <p class="small text-muted mt-2 mb-3 js-nights-label" aria-live="polite"></p>
                        <button type="submit" class="btn btn-ocean w-100"><i class="bi bi-search"></i> Search rooms</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- ===== Introduction + facilities ===== -->
<section class="container py-5">
    <div class="row g-5 align-items-center">
        <div class="col-lg-6">
            <p class="eyebrow text-ocean">Welcome to Coral Bay</p>
            <h2 class="section-title">A small villa with a big ocean view</h2>
            <p>Coral Bay Villa is a family-run boutique stay on Beach Road, Mirissa — one of Sri Lanka's most loved coastal towns. We are a short walk from the beach, the harbour where whale-watching boats depart, and the cafés along the bay.</p>
            <p class="mb-0">Every room is designed for slow, comfortable days: cool interiors, fresh tropical breakfasts and staff who know every hidden cove from Weligama to Tangalle.</p>
        </div>
        <div class="col-lg-6">
            <div class="row g-3 facility-grid">
                <?php
                $facilities = [
                    ['bi-water',         'Beach access',      '2-minute walk to Mirissa beach'],
                    ['bi-droplet-half',  'Infinity pool',     'Open 7 am – 9 pm'],
                    ['bi-wifi',          'Free Wi-Fi',        'Fast fibre throughout'],
                    ['bi-cup-hot',       'Breakfast',         'Sri Lankan & continental'],
                    ['bi-binoculars',    'Whale watching',    'Tours arranged daily'],
                    ['bi-car-front',     'Airport transfer',  'Pick-up on request'],
                ];
                foreach ($facilities as [$icon, $title, $text]): ?>
                    <div class="col-6 col-md-4">
                        <div class="facility-card h-100">
                            <i class="bi <?= $icon ?>"></i>
                            <h3 class="h6 mb-1"><?= e($title) ?></h3>
                            <p class="small text-muted mb-0"><?= e($text) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ===== Featured rooms ===== -->
<section class="bg-sand py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4">
            <div>
                <p class="eyebrow text-ocean mb-1">Stay with us</p>
                <h2 class="section-title mb-0">Featured rooms</h2>
            </div>
            <a href="<?= url('rooms.php') ?>" class="btn btn-outline-dark">All rooms <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($featured as $room): ?>
                <div class="col-md-6 col-lg-4">
                    <?php include __DIR__ . '/../partials/room_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== Gallery ===== -->
<section class="container py-5" id="gallery">
    <p class="eyebrow text-ocean mb-1">Gallery</p>
    <h2 class="section-title mb-4">Life at the villa</h2>
    <div class="row g-3">
        <?php foreach ($gallery as $i => [$file, $caption]): ?>
            <div class="col-6 col-md-4">
                <button type="button" class="gallery-item js-gallery" data-src="<?= img_url($file) ?>" data-caption="<?= e($caption) ?>">
                    <img src="<?= img_url($file) ?>" alt="<?= e($caption) ?>" loading="lazy">
                    <span><?= e($caption) ?></span>
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== Reviews ===== -->
<?php if ($reviews): ?>
<section class="bg-ocean-soft py-5">
    <div class="container">
        <p class="eyebrow text-ocean mb-1">Guest reviews</p>
        <h2 class="section-title mb-4">What our guests say</h2>
        <div class="row g-4">
            <?php foreach ($reviews as $r): ?>
                <div class="col-md-6 col-lg-4">
                    <figure class="review-card h-100 mb-0">
                        <?= stars((float) $r['rating']) ?>
                        <blockquote class="mt-2 mb-3">“<?= e($r['comment']) ?>”</blockquote>
                        <figcaption class="small text-muted">— <?= e($r['name']) ?>, stayed in <?= e($r['room_name']) ?></figcaption>
                    </figure>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== Location ===== -->
<section class="container py-5" id="location">
    <div class="row g-4 align-items-center">
        <div class="col-lg-5">
            <p class="eyebrow text-ocean mb-1">Location</p>
            <h2 class="section-title">Find us on Beach Road</h2>
            <ul class="list-unstyled location-list">
                <li><i class="bi bi-geo-alt"></i> 24 Beach Road, Mirissa 81740</li>
                <li><i class="bi bi-signpost-2"></i> 2 min walk to Mirissa beach</li>
                <li><i class="bi bi-life-preserver"></i> 5 min to Mirissa harbour (whale watching)</li>
                <li><i class="bi bi-car-front"></i> 15 min to Weligama · 35 min to Galle Fort</li>
                <li><i class="bi bi-airplane"></i> ~3 hrs from Bandaranaike International Airport</li>
            </ul>
            <a href="<?= url('search.php') ?>" class="btn btn-sunset mt-2">Book your stay</a>
        </div>
        <div class="col-lg-7">
            <div class="map-wrap">
                <iframe title="Map showing Mirissa, Sri Lanka" loading="lazy"
                        src="https://www.openstreetmap.org/export/embed.html?bbox=80.440%2C5.935%2C80.480%2C5.958&amp;layer=mapnik&amp;marker=5.9455%2C80.4590"></iframe>
            </div>
        </div>
    </div>
</section>

<!-- Gallery lightbox modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryCaption" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-0">
                <h2 class="modal-title h6" id="galleryCaption"></h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0"><img id="galleryImage" src="" alt="" class="w-100"></div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
