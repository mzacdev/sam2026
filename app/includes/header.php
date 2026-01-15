<?php
$pageTitle = isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME;
$currentUser = null;

if (function_exists('getAuth')) {
    $auth = getAuth();
    if ($auth && $auth->isLoggedIn()) {
        $currentUser = $auth->getUser();
    }
}

$userName = $currentUser['full_name'] ?? 'Pengguna';
$userEmail = $currentUser['email'] ?? '';
?>
<!doctype html>
<html class="no-js" lang="<?php echo htmlspecialchars($_COOKIE['sam_lang'] ?? 'ms', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?php echo defined('SITE_DESCRIPTION') ? SITE_DESCRIPTION : SITE_NAME; ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo asset('img/favicon.ico'); ?>">

    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo asset('light/css/vendor/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/vendor/material-design-iconic-font.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/vendor/font-awesome.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/vendor/themify-icons.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/vendor/cryptocurrency-icons.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/plugins/plugins.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/helper.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/style.css'); ?>">
    <link id="themeStylesheet" rel="stylesheet" href="<?php echo asset('light/css/style-primary.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/custom.css'); ?>">
    <script>
        (function(){
            try {
                var theme = localStorage.getItem('sam_theme') || 'style-primary.css';
                var themeLink = document.getElementById('themeStylesheet');
                if (themeLink && theme) {
                    // ensure we only use filenames (no path injection)
                    var allowed = ['style-primary.css','style-red.css','style-green.css','style-brown.css','style-indigo.css','style-orange.css','style-pink.css','style-purple.css','style-cyan.css','style-teal.css','style-yellow.css','style-gray.css'];
                    if (allowed.indexOf(theme) === -1) theme = 'style-primary.css';
                    themeLink.href = '<?php echo asset("light/css/"); ?>' + theme;
                }
            } catch(e) { console && console.warn && console.warn(e); }
        })();
    </script>
        <script>
            (function(){
                try {
                    var theme = localStorage.getItem('sam_theme') || 'style-primary.css';
                    var themeLink = document.getElementById('themeStylesheet');
                    if (themeLink && theme) {
                        // ensure we only use filenames (no path injection)
                        var allowed = ['style-primary.css','style-red.css','style-green.css','style-brown.css','style-indigo.css','style-orange.css','style-pink.css','style-purple.css','style-cyan.css','style-teal.css','style-yellow.css','style-gray.css'];
                        if (allowed.indexOf(theme) === -1) theme = 'style-primary.css';
                        themeLink.href = '<?php echo asset("light/css/"); ?>' + theme;
                    }
                } catch(e) { console && console.warn && console.warn(e); }
            })();
        </script>
</head>
<body>

<div class="main-wrapper">

    <!-- Header Section Start -->
    <div class="header-section">
        <div class="container-fluid">
            <div class="row justify-content-between align-items-center">

                <!-- Header Logo (Header Left) Start -->
                <div class="header-logo col-auto">
                    <a href="<?php echo url('pages/dashboard.php'); ?>">
                        <img src="<?php echo asset('img/logos/logo-main.png'); ?>" alt="<?php echo SITE_NAME; ?>">
                        <img src="<?php echo asset('img/logos/logo-main.png'); ?>" class="logo-light" alt="<?php echo SITE_NAME; ?>">
                    </a>
                </div><!-- Header Logo (Header Left) End -->

                <!-- Header Right Start -->
                <div class="header-right flex-grow-1 col-auto">
                    <div class="row justify-content-between align-items-center">

                        <!-- Side Header Toggle & Search Start -->
                        <div class="col-auto">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <button class="side-header-toggle"><i class="zmdi zmdi-menu"></i></button>
                                </div>
                                <div class="col-auto">
                                    <div class="header-search">
                                        <button class="header-search-open d-block d-xl-none"><i class="zmdi zmdi-search"></i></button>
                                        <div class="header-search-form">
                                            <form action="#">
                                                <input type="text" placeholder="Cari...">
                                                <button><i class="zmdi zmdi-search"></i></button>
                                            </form>
                                            <button class="header-search-close d-block d-xl-none"><i class="zmdi zmdi-close"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- Side Header Toggle & Search End -->

                        <!-- Header User Area Start -->
                        <div class="col-auto">
                            <ul class="header-notification-area">
                                <!-- Sports icons (up to 10) loaded from assets/img/sukan -->
                                <?php
                                $sukanDir = realpath(__DIR__ . '/../../assets/img/sukan');
                                $sukanLogos = [];
                                if ($sukanDir && is_dir($sukanDir)) {
                                    $files = glob($sukanDir . '/*.{png,jpg,jpeg,svg,gif}', GLOB_BRACE);
                                    if ($files) {
                                        // sort alphabetically
                                        usort($files, function($a,$b){ return strcmp(basename($a), basename($b)); });
                                        foreach (array_slice($files, 0, 10) as $f) {
                                            $sukanLogos[] = basename($f);
                                        }
                                    }
                                }
                                // If scanning failed, provide a safe fallback list of common icons
                                if (empty($sukanLogos)) {
                                    $fallback = ['badminton.png','bola-jaring.png','bola-sepak.png','catur.png','mlbb-pubg.png','olahraga.png','ragbi.png','takraw.png','tenpin-bowling.png','volleyball.png'];
                                    $sukanLogos = $fallback;
                                }
                                if (!empty($sukanLogos)): ?>
                                    <li class="col-auto d-flex align-items-center header-sukan-logos" style="gap:.5rem;">
                                        <?php foreach ($sukanLogos as $logo): ?>
                                            <?php $label = htmlspecialchars(ucwords(str_replace(array('-', '_'), ' ', pathinfo($logo, PATHINFO_FILENAME))), ENT_QUOTES, 'UTF-8'); ?>
                                            <img src="<?php echo asset('img/sukan/' . $logo); ?>" alt="<?php echo $label; ?>" title="<?php echo $label; ?>" aria-label="<?php echo $label; ?>" width="48" height="48" onerror="this.style.display='none'" />
                                        <?php endforeach; ?>
                                    </li>
                                <?php endif; ?>

                                <!-- Language & Theme Selector removed per request -->
                                <?php if ($currentUser): ?>
                                    <?php
                                    // Resolve avatar: prefer explicit user fields, allow URL or local filenames, fallback to default in assets/img/avatar
                                    $defaultAvatar = asset('img/avatar/profiles.jpg');
                                    $avatarSrc = $defaultAvatar;
                                    $candidateKeys = ['avatar','f_avatar','profile_image','photo','image','avatar_url'];
                                    foreach ($candidateKeys as $k) {
                                        if (!empty($currentUser[$k])) {
                                            $val = trim((string)$currentUser[$k]);
                                            if ($val === '') continue;
                                            // If looks like absolute URL or root-relative path, use as-is
                                            if (preg_match('#^https?://#i', $val) || strpos($val, '/') === 0) {
                                                $avatarSrc = $val;
                                                break;
                                            }

                                            // Try common local asset locations (server-side existence check)
                                            $candidates = [
                                                __DIR__ . '/../../assets/img/avatar/' . $val,
                                                __DIR__ . '/../../assets/light/images/avatar/' . $val,
                                                __DIR__ . '/../../assets/img/users/' . $val,
                                                __DIR__ . '/../../assets/img/' . $val,
                                            ];
                                            foreach ($candidates as $i => $sp) {
                                                if (file_exists($sp)) {
                                                    // map server path index to public asset helper
                                                    switch ($i) {
                                                        case 0: $avatarSrc = asset('img/avatar/' . $val); break;
                                                        case 1: $avatarSrc = asset('light/images/avatar/' . $val); break;
                                                        case 2: $avatarSrc = asset('img/users/' . $val); break;
                                                        default: $avatarSrc = asset('img/' . $val); break;
                                                    }
                                                    break 2;
                                                }
                                            }
                                            // If no server file found, still try treating value as filename under avatar folder
                                            $avatarSrc = asset('img/avatar/' . $val);
                                            break;
                                        }
                                    }
                                    ?>
                                    <li class="adomx-dropdown col-auto">
                                        <a class="toggle" href="#">
                                            <span class="user">
                                                <span class="avatar">
                                                    <img src="<?php echo htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>" onerror="this.onerror=null;this.src='<?php echo $defaultAvatar; ?>'">
                                                    <span class="status"></span>
                                                </span>
                                                <span class="name"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </span>
                                        </a>

                                        <div class="adomx-dropdown-menu dropdown-menu-user">
                                            <div class="head">
                                                <h5 class="name">
                                                    <a href="#"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></a>
                                                </h5>
                                                <?php if ($userEmail): ?>
                                                    <a class="mail" href="#"><?php echo htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8'); ?></a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="body">
                                                <ul>
                                                    <!-- Removed duplicate 'Tetapan' entry from header (already in sidebar) -->
                                                    <li><a class="trigger-change-password" href="#"><i class="zmdi zmdi-key"></i> Tukar Kata Laluan</a></li>
                                                    <li><a class="confirm-logout" href="<?php echo url('auth/logout.php'); ?>"><i class="zmdi zmdi-lock-open"></i>Log keluar</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                <?php else: ?>
                                    <li class="col-auto">
                                        <a class="button button-primary button-outline" href="<?php echo url('auth/login.php'); ?>">
                                            Log Masuk
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div><!-- Header User Area End -->

                    </div>
                </div><!-- Header Right End -->

            </div>
        </div>
    </div><!-- Header Section End -->

    <!-- Language and theme selection UI removed -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function(){
            function bindLogoutConfirm() {
                try {
                    document.querySelectorAll('.confirm-logout').forEach(function(el){
                        el.addEventListener('click', function(e){
                            e.preventDefault();
                            var href = this.getAttribute('href');
                            if (window.Swal) {
                                Swal.fire({
                                    title: 'Log Keluar?',
                                    text: 'Anda pasti mahu log keluar?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Ya, log keluar!',
                                    cancelButtonText: 'Batal'
                                }).then(function(result){
                                    if (result.isConfirmed) {
                                        window.location.href = href;
                                    }
                                });
                            } else {
                                if (confirm('Log keluar?')) window.location.href = href;
                            }
                        });
                    });
                } catch(e) { console && console.warn && console.warn(e); }
            }
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bindLogoutConfirm);
            else bindLogoutConfirm();
        })();
    </script>
        <!-- Change Password Modal -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">Tukar Kata Laluan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <form id="changePasswordForm">
                        <div class="modal-body">
                            <div class="mb-2 text-muted small">Anda log masuk sebagai <?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?><?php if($userEmail) echo ' — ' . htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="mb-3">
                                <label class="form-label">Kata Laluan Semasa</label>
                                <input type="password" name="current_password" class="form-control" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kata Laluan Baru</label>
                                <input type="password" id="newPassword" name="new_password" class="form-control" required minlength="8" autocomplete="new-password">
                            </div>
                            <div id="passwordPolicy" class="mb-2 small text-muted">
                                <strong>Polisi Kata Laluan:</strong>
                                <ul class="mb-0" style="padding-left:1rem;">
                                    <?php if(defined('PASSWORD_MIN_LENGTH')): ?>
                                        <li data-policy="minlength">Panjang sekurang-kurangnya <?php echo (int)PASSWORD_MIN_LENGTH; ?> aksara</li>
                                    <?php endif; ?>
                                    <?php if(defined('PASSWORD_REQUIRE_UPPERCASE') && PASSWORD_REQUIRE_UPPERCASE): ?>
                                        <li data-policy="uppercase">Mengandungi sekurang-kurangnya satu huruf besar (A-Z)</li>
                                    <?php endif; ?>
                                    <?php if(defined('PASSWORD_REQUIRE_NUMBER') && PASSWORD_REQUIRE_NUMBER): ?>
                                        <li data-policy="number">Mengandungi sekurang-kurangnya satu nombor (0-9)</li>
                                    <?php endif; ?>
                                    <?php if(defined('PASSWORD_REQUIRE_SPECIAL') && PASSWORD_REQUIRE_SPECIAL): ?>
                                        <li data-policy="special">Mengandungi sekurang-kurangnya satu simbol khas (contoh: !@#$%)</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sahkan Kata Laluan Baru</label>
                                <input type="password" id="confirmPassword" name="confirm_password" class="form-control" required minlength="8" autocomplete="new-password">
                            </div>
                            <div id="changePasswordMessage" class="text-muted small"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
                (function(){
                        function bindChangePassword(){
                                try{
                                        document.querySelectorAll('.trigger-change-password').forEach(function(el){
                                                el.addEventListener('click', function(e){
                                                        e.preventDefault();
                                                        var modalEl = document.getElementById('changePasswordModal');
                                        // adjust modal position to avoid overlapping header
                                        function adjustModalPosition(){
                                            try{
                                                var header = document.querySelector('.header-section');
                                                var offset = 70; if (header) offset = header.offsetHeight + 12;
                                                var dialog = modalEl.querySelector('.modal-dialog');
                                                if (dialog) { dialog.style.transform = 'none'; dialog.style.marginTop = offset + 'px'; }
                                            }catch(e){ }
                                        }
                                        adjustModalPosition();
                                        if (window.bootstrap && bootstrap.Modal) {
                                            var m = new bootstrap.Modal(modalEl);
                                            m.show();
                                        } else {
                                            modalEl.style.display = 'block';
                                        }
                                        // re-adjust on resize
                                        window.addEventListener('resize', adjustModalPosition);
                                                });
                                        });

                                        var form = document.getElementById('changePasswordForm');
                                        var newPwd = document.getElementById('newPassword');
                                        var confPwd = document.getElementById('confirmPassword');
                                        var submitBtn = form ? form.querySelector('button[type="submit"]') : null;

                                        // build policy config from server-side constants
                                        var pwdPolicy = {
                                            minLength: <?php echo defined('PASSWORD_MIN_LENGTH') ? (int)PASSWORD_MIN_LENGTH : 0; ?>,
                                            requireUpper: <?php echo (defined('PASSWORD_REQUIRE_UPPERCASE') && PASSWORD_REQUIRE_UPPERCASE) ? 'true' : 'false'; ?>,
                                            requireNumber: <?php echo (defined('PASSWORD_REQUIRE_NUMBER') && PASSWORD_REQUIRE_NUMBER) ? 'true' : 'false'; ?>,
                                            requireSpecial: <?php echo (defined('PASSWORD_REQUIRE_SPECIAL') && PASSWORD_REQUIRE_SPECIAL) ? 'true' : 'false'; ?>
                                        };

                                        function updatePolicyUI(pwd){
                                            try{
                                                var list = document.querySelectorAll('#passwordPolicy [data-policy]');
                                                list.forEach(function(li){
                                                    var ok = false;
                                                    var key = li.getAttribute('data-policy');
                                                    if (key === 'minlength') ok = pwd && pwd.length >= (pwdPolicy.minLength || 0);
                                                    if (key === 'uppercase') ok = /[A-Z]/.test(pwd || '');
                                                    if (key === 'number') ok = /[0-9]/.test(pwd || '');
                                                    if (key === 'special') ok = /[^a-zA-Z0-9]/.test(pwd || '');
                                                    li.style.color = ok ? '#198754' : '#6c757d';
                                                    li.dataset.ok = ok ? '1' : '0';
                                                });
                                            }catch(e){ }
                                        }

                                        function validateFormState(){
                                            try{
                                                var pwd = newPwd ? newPwd.value : '';
                                                var conf = confPwd ? confPwd.value : '';
                                                updatePolicyUI(pwd);
                                                var allOk = true;
                                                document.querySelectorAll('#passwordPolicy [data-policy]').forEach(function(li){ if (li.dataset.ok !== '1') allOk = false; });
                                                if (!pwd || !conf) allOk = false;
                                                if (pwd !== conf) allOk = false;
                                                if (submitBtn) submitBtn.disabled = !allOk;
                                                return allOk;
                                            }catch(e){ return false; }
                                        }

                                        if (newPwd) newPwd.addEventListener('input', validateFormState);
                                        if (confPwd) confPwd.addEventListener('input', validateFormState);

                                        if (!form) return;
                                        // disable submit initially
                                        if (form && form.querySelector('button[type="submit"]')) form.querySelector('button[type="submit"]').disabled = true;

                                        form.addEventListener('submit', function(e){
                                            e.preventDefault();
                                            if (!validateFormState()) {
                                                var msg = document.getElementById('changePasswordMessage'); if (msg) msg.textContent = 'Sila penuhi polisi kata laluan.'; return;
                                            }
                                            var fd = new FormData(form);
                                            var msg = document.getElementById('changePasswordMessage');
                                            msg.textContent = '';
                                                fetch('<?php echo url('ajax/change_password.php'); ?>', {
                                                        method: 'POST',
                                                        credentials: 'same-origin',
                                                        body: fd,
                                                        headers: { 'Accept': 'application/json' }
                                                }).then(function(r){ return r.json(); }).then(function(res){
                                                        if (res && res.success){
                                                                if (window.Swal) Swal.fire({ icon: 'success', title: 'Berjaya', text: res.message || 'Kata laluan telah dikemaskini.' });
                                                                // hide modal
                                                                if (window.bootstrap && bootstrap.Modal) {
                                                                        var modalEl = document.getElementById('changePasswordModal');
                                                                        var inst = bootstrap.Modal.getInstance(modalEl);
                                                                        if (inst) inst.hide();
                                                                }
                                                                form.reset();
                                                        } else {
                                                                var text = (res && res.message) ? res.message : 'Gagal mengemaskini kata laluan.';
                                                                if (window.Swal) Swal.fire({ icon: 'error', title: 'Ralat', text: text });
                                                                else if (msg) msg.textContent = text;
                                                        }
                                                }).catch(function(err){
                                                        console.error('change_password error', err);
                                                        if (window.Swal) Swal.fire({ icon: 'error', title: 'Ralat', text: 'Ralat rangkaian. Sila cuba lagi.' });
                                                });
                                        });
                                } catch(e) { console && console.warn && console.warn(e); }
                        }
                        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bindChangePassword);
                        else bindChangePassword();
                })();
        </script>
    <script>
        (function(){
            function basename(path){
                try { return path.split('/').filter(Boolean).pop() || path; } catch(e) { return path; }
            }

            function setActiveOnElement(link){
                try {
                    var menu = document.getElementById('side-header-menu');
                    if (!menu || !link) return;
                    // clear active/open
                    menu.querySelectorAll('li').forEach(function(li){ li.classList.remove('active'); });
                    menu.querySelectorAll('li.has-sub-menu').forEach(function(li){ li.classList.remove('open','active'); li.querySelectorAll('.side-header-sub-menu').forEach(function(ul){ ul.style.display='none'; }); });

                    var li = link.closest('li'); if (li) li.classList.add('active');
                    var parentSub = link.closest('.side-header-sub-menu');
                    if (parentSub) {
                        parentSub.style.display = 'block';
                        var parentLi = parentSub.closest('li.has-sub-menu');
                        if (parentLi) parentLi.classList.add('open','active');
                    }
                } catch(e) { console && console.warn && console.warn(e); }
            }

            function findAndActivateByHref(href){
                try{
                    var menu = document.getElementById('side-header-menu'); if (!menu) return false;
                    // try exact selector match first
                    var el = menu.querySelector('a[href="' + href + '"]'); if (el) { setActiveOnElement(el); return true; }

                    var anchors = menu.querySelectorAll('a[href]');
                    var loc = window.location;
                    var currentPath = loc.pathname || '/';
                    var currentFull = (loc.pathname || '') + (loc.search || '');

                    for (var i=0;i<anchors.length;i++){
                        var a = anchors[i];
                        var raw = a.getAttribute('href');
                        if (!raw) continue;
                        // resolve to absolute URL using location as base
                        var target;
                        try { target = new URL(raw, loc.origin + '/'); } catch(e) { continue; }

                        var tPath = target.pathname || '/';
                        var tFull = (target.pathname || '') + (target.search || '');

                        // exact pathname or pathname+search match
                        if (tFull === currentFull || tPath === currentPath) { setActiveOnElement(a); return true; }

                        // endsWith match (handles directories or different base prefixes)
                        if (currentPath.endsWith(tPath) || tPath.endsWith(currentPath)) { setActiveOnElement(a); return true; }

                        // fallback to basename match
                        if (basename(tPath) && basename(tPath) === basename(currentPath)) { setActiveOnElement(a); return true; }
                    }
                    return false;
                } catch(e) { console && console.warn && console.warn(e); return false; }
            }

            function bindSidebarClicks(){
                try{
                    document.querySelectorAll('#side-header-menu a[href]').forEach(function(a){
                        a.addEventListener('click', function(e){
                            var href = this.getAttribute('href');
                            if (!href || href.indexOf('#') === 0) return;
                            try { localStorage.setItem('sidebar_active', href); } catch(e) {}
                            setActiveOnElement(this);
                        });
                    });
                }catch(e){ console && console.warn && console.warn(e); }
            }

            function initSidebarActive(){
                try{
                    // Prefer matching current URL to highlight
                    if (!findAndActivateByHref(window.location.href)){
                        var s = null;
                        try { s = localStorage.getItem('sidebar_active'); } catch(e) {}
                        if (s) findAndActivateByHref(s);
                    }
                }catch(e){ console && console.warn && console.warn(e); }
            }

            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function(){ bindSidebarClicks(); initSidebarActive(); });
            else { bindSidebarClicks(); initSidebarActive(); }

            // Ensure activation runs after other template scripts (in footer) by re-applying on window load
            try {
                window.addEventListener('load', function(){ setTimeout(initSidebarActive, 120); });
            } catch(e) {}
        })();
    </script>
