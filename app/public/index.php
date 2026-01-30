<?php
/**
 * Public Index / Home
 * Simple landing page linking to public sections.
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Home';

ob_start();
?>
<div class="container mt-3 mb-4">
    <div class="row mb-4">
    <div class="col-12 text-center">
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
            // fallback to a known image if exists
            $bannerFiles = [ asset('img/banners/fallback/sam2026-banner.jpg') ];
        }
        $slidesJson = json_encode($bannerFiles);
        ?>
        <div id="publicBanner" class="public-banner mt-3" data-slides='<?php echo htmlspecialchars($slidesJson, ENT_QUOTES, 'UTF-8'); ?>'>
            <div class="banner-viewport">
                <!-- images inserted by JS -->
            </div>
            <!-- Centered overlay text -->
            <div class="banner-overlay" aria-hidden="false">
                <div class="banner-overlay-inner">
                    <h2 class="banner-title">Welcome to Sukan Asasi Malaysia 2026</h2>
                    <p class="banner-sub">Hosted by the National Defence University of Malaysia</p>
                </div>
            </div>
            <button class="banner-prev" aria-label="Previous">‹</button>
            <button class="banner-next" aria-label="Next">›</button>
            <div class="banner-dots" role="tablist"></div>
        </div>

<!-- sport icons removed as requested -->

    <style>
    /* Public banner styles */
    /* Centered banner: keep container width but image fills the banner area */
    .public-banner{ position:relative; max-width:980px; margin:0 auto; border-radius:10px; overflow:hidden; background:#f3f4f6 }
    .public-banner .banner-viewport{ position:relative; width:100%; height:clamp(360px, 38vh, 820px); }
    /* Use cover so images fill the slideshow area without letterboxing */
    .public-banner img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; object-position:center center; opacity:0; transition:opacity .6s ease; }
    .public-banner img.active{ opacity:1; z-index:2 }
    /* Overlay text centered on banner */
    .public-banner .banner-overlay{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; z-index:4; pointer-events:none }
    .public-banner .banner-overlay-inner{ pointer-events:auto; background:rgba(0,0,0,0.32); padding:14px 20px; border-radius:10px; max-width:86%; text-align:center }
    .banner-title{ color:#fff; font-size:1.6rem; line-height:1.05; margin:0; font-weight:700; text-shadow:0 6px 22px rgba(2,6,23,0.45) }
    .banner-sub{ color:rgba(255,255,255,0.92); margin:6px 0 0; font-size:0.98rem; text-shadow:0 4px 18px rgba(2,6,23,0.36) }
    @media(min-width:992px){ .banner-title{ font-size:2.15rem } .banner-sub{ font-size:1.05rem } }
    @media(max-width:576px){ .public-banner .banner-overlay-inner{ padding:10px 12px } .banner-title{ font-size:1.15rem } .banner-sub{ font-size:0.9rem } }
    .public-banner .banner-prev, .public-banner .banner-next{ position:absolute; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.35); color:#fff; border:none; width:36px; height:36px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:20px }
    .public-banner .banner-prev{ left:12px }
    .public-banner .banner-next{ right:12px }
    .public-banner .banner-dots{ position:absolute; bottom:12px; left:50%; transform:translateX(-50%); display:flex; gap:8px; z-index:3 }
    .public-banner .banner-dots button{ width:10px; height:10px; border-radius:50%; border:1px solid rgba(255,255,255,0.8); background:rgba(255,255,255,0.6); cursor:pointer }
    .public-banner .banner-dots button.active{ background:#fff }
    @media(max-width:992px){ .public-banner .banner-viewport{ height:560px } }
    @media(max-width:576px){ .public-banner .banner-viewport{ height:420px } }
    </style>

    <script>
    (function(){
        var root = document.getElementById('publicBanner');
        if (!root) return;
        var slides = [];
        try { slides = JSON.parse(root.getAttribute('data-slides') || '[]'); } catch(e){ slides = []; }
        var viewport = root.querySelector('.banner-viewport');
        var dots = root.querySelector('.banner-dots');
        var prev = root.querySelector('.banner-prev');
        var next = root.querySelector('.banner-next');
        var index = 0; var timer = null; var interval = 4000;

        slides.forEach(function(src, i){
            var img = document.createElement('img'); img.src = src; if(i===0) img.classList.add('active'); img.setAttribute('alt','Banner '+(i+1));
            viewport.appendChild(img);
            var b = document.createElement('button'); if(i===0) b.classList.add('active'); b.setAttribute('aria-label','Slide '+(i+1)); b.addEventListener('click', function(){ go(i); });
            dots.appendChild(b);
        });

        var imgs = viewport.querySelectorAll('img');
        function show(i){ imgs.forEach(function(im,idx){ im.classList.toggle('active', idx===i); }); Array.from(dots.children).forEach(function(d,idx){ d.classList.toggle('active', idx===i); }); index = i; }
        function nextSlide(){ show((index+1) % imgs.length); }
        function prevSlide(){ show((index-1+imgs.length) % imgs.length); }
        function go(i){ show(i); restart(); }
        function play(){ stop(); timer = setInterval(nextSlide, interval); }
        function stop(){ if(timer){ clearInterval(timer); timer=null; } }
        function restart(){ stop(); play(); }

        if(next) next.addEventListener('click', function(e){ e.preventDefault(); nextSlide(); restart(); });
        if(prev) prev.addEventListener('click', function(e){ e.preventDefault(); prevSlide(); restart(); });
        root.addEventListener('mouseenter', stop); root.addEventListener('mouseleave', play);
        if (imgs.length>1) play();
    })();
    </script>

    </div> <!-- /.container -->

    <?php
    $content = ob_get_clean();
    require_once __DIR__ . '/../includes/layout_public.php';
    ?>