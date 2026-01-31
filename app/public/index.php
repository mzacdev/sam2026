<?php
/**
 * Public Index / Home
 * Simple landing page linking to public sections.
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Home';

ob_start();
?>
<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-12">
            <?php
            // Build banner slide list (reuse assets/img/banners if present)
            $bannerDir = __DIR__ . '/../assets/img/banners';
            $bannerFiles = [];
            if (is_dir($bannerDir)) {
                $files = glob($bannerDir . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
                if ($files) {
                    usort($files, function($a,$b){ return strcmp(basename($a), basename($b)); });
                    foreach ($files as $f) {
                        $bannerFiles[] = asset('img/banners/' . basename($f));
                    }
                }
            }
            if (empty($bannerFiles)) {
                $bannerFiles = [ asset('img/banners/fallback/sam2026-banner.jpg') ];
            }
            $slidesJson = json_encode($bannerFiles);
            ?>

            <div id="publicBanner" class="public-banner mb-4" data-slides='<?php echo htmlspecialchars($slidesJson, ENT_QUOTES, 'UTF-8'); ?>'>
                <div class="banner-viewport"></div>
                <div class="banner-overlay">
                    <div class="banner-overlay-inner">
                        <h1 class="banner-title text-white">SAM 2026</h1>
                        <p class="banner-sub text-white">Hosted by the National Defence University of Malaysia</p>
                        <div class="mt-3">
                            <a href="<?php echo url('public/medal-standings.php'); ?>" class="btn btn-warning btn-lg me-2">View Medal Standings</a>
                            <a href="<?php echo url('public/contingents.php'); ?>" class="btn btn-outline-light btn-lg">View Contingents</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="row g-3 align-items-stretch">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Contingents</h5>
                    <p class="card-text text-muted small">Browse all participating contingents, with brief profiles and codes.</p>
                    <div class="mt-auto">
                        <a href="<?php echo url('public/contingents.php'); ?>" class="btn btn-primary">Explore Contingents</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Medals</h5>
                    <p class="card-text text-muted small">Follow live medal standings and view recipients for each event.</p>
                    <div class="mt-auto">
                        <a href="<?php echo url('public/medal-standings.php'); ?>" class="btn btn-primary">View Medals</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Official Website</h5>
                    <p class="card-text text-muted small">Visit the official SAM2026 site for schedules, results and official announcements.</p>
                    <div class="mt-auto">
                        <a href="https://sam2026.upnm.edu.my/" class="btn btn-primary" target="_blank" rel="noopener noreferrer">Official SAM2026 Site</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-4 text-center text-muted small">
        <div>© 2026 Sukan Asasi Malaysia, Universiti Pertahanan Nasional Malaysia — All rights reserved.</div>
    </footer>

    <style>
    /* Professional hero + cards */
    /* Professional hero + cards */
    .public-banner{ position:relative; width:100%; height:clamp(320px,36vh,640px); border-radius:10px; overflow:hidden; background:#0b1221 }
    .public-banner .banner-viewport{ position:absolute; inset:0 }
    .public-banner img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:0; transition:opacity .6s ease }
    .public-banner img.active{ opacity:1 }
    .public-banner .banner-overlay{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center }
    .public-banner .banner-overlay-inner{ background:linear-gradient(180deg, rgba(3,7,18,0.6), rgba(3,7,18,0.25)); padding:22px 28px; border-radius:8px; text-align:center; color:#fff }
    .banner-title{ font-size:1.9rem; margin:0; font-weight:700 }
    .banner-sub{ margin:6px 0 0; opacity:0.9 }
    .card{ border:0 }
    .card .card-body{ padding:1.25rem }
    footer{ opacity:0.8 }
    @media(max-width:767px){ .public-banner{ height:44vh } .banner-title{ font-size:1.25rem } }
    </style>

    <script>
    (function(){
        var root = document.getElementById('publicBanner');
        if (!root) return;
        var slides = [];
        try { slides = JSON.parse(root.getAttribute('data-slides') || '[]'); } catch(e){ slides = []; }
        var viewport = root.querySelector('.banner-viewport');
        slides.forEach(function(src,i){ var img = document.createElement('img'); img.src=src; img.alt='Banner '+(i+1); if(i===0) img.classList.add('active'); viewport.appendChild(img); });
        var imgs = viewport.querySelectorAll('img'); if(!imgs.length) return; var idx=0; var t=null; var interval=4500;
        function show(i){ imgs.forEach(function(im,ii){ im.classList.toggle('active', ii===i); }); idx=i; }
        function next(){ show((idx+1)%imgs.length); }
        t=setInterval(next, interval);
        root.addEventListener('mouseenter', function(){ clearInterval(t); t=null }); root.addEventListener('mouseleave', function(){ if(!t) t=setInterval(next, interval); });

        // No schedule interception: schedule links removed for a professional layout

    })();
    </script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout_public.php';
?>