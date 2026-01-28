
<!doctype html>
<html class="no-js" lang="ms">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Setup Pertandingan - Sukan Asasi Malaysia 2026</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistem Pengurusan Kejohanan Sukan Asasi Malaysia">
    <link rel="shortcut icon" type="image/x-icon" href="/assets/img/favicon.ico">

    <!-- CSS -->
    <link rel="stylesheet" href="/assets/light/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/light/css/vendor/material-design-iconic-font.min.css">
    <link rel="stylesheet" href="/assets/light/css/vendor/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/light/css/vendor/themify-icons.css">
    <link rel="stylesheet" href="/assets/light/css/vendor/cryptocurrency-icons.css">
    <link rel="stylesheet" href="/assets/light/css/plugins/plugins.css">
    <link rel="stylesheet" href="/assets/light/css/helper.css">
    <link rel="stylesheet" href="/assets/light/css/style.css">
    <link id="themeStylesheet" rel="stylesheet" href="/assets/light/css/style-primary.css">
    <link rel="stylesheet" href="/assets/light/css/custom.css">
    <script>
        (function(){
            try {
                var theme = localStorage.getItem('sam_theme') || 'style-primary.css';
                var themeLink = document.getElementById('themeStylesheet');
                if (themeLink && theme) {
                    // ensure we only use filenames (no path injection)
                    var allowed = ['style-primary.css','style-red.css','style-green.css','style-brown.css','style-indigo.css','style-orange.css','style-pink.css','style-purple.css','style-cyan.css','style-teal.css','style-yellow.css','style-gray.css'];
                    if (allowed.indexOf(theme) === -1) theme = 'style-primary.css';
                    themeLink.href = '/assets/light/css/' + theme;
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
                        themeLink.href = '/assets/light/css/' + theme;
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
                    <a href="/pages/dashboard.php">
                        <img src="/assets/img/logos/logo-main.png" alt="Sukan Asasi Malaysia 2026">
                        <img src="/assets/img/logos/logo-main.png" class="logo-light" alt="Sukan Asasi Malaysia 2026">
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
                                                                    <li class="col-auto d-flex align-items-center header-sukan-logos" style="gap:.5rem;">
                                                                                                                                <img src="/assets/img/sukan/badminton.png" alt="Badminton" title="Badminton" aria-label="Badminton" width="48" height="48" onerror="this.onerror=null;this.src='/assets/img/logos/logo-main.png'" />
                                                                                                                                <img src="/assets/img/sukan/bola-jaring.png" alt="Bola Jaring" title="Bola Jaring" aria-label="Bola Jaring" width="48" height="48" onerror="this.onerror=null;this.src='/assets/img/logos/logo-main.png'" />
                                                                                                                                <img src="/assets/img/sukan/bola-sepak.png" alt="Bola Sepak" title="Bola Sepak" aria-label="Bola Sepak" width="48" height="48" onerror="this.onerror=null;this.src='/assets/img/logos/logo-main.png'" />
                                                                                                                                <img src="/assets/img/sukan/catur.png" alt="Catur" title="Catur" aria-label="Catur" width="48" height="48" onerror="this.onerror=null;this.src='/assets/img/logos/logo-main.png'" />
                                                                                                                                <img src="/assets/img/sukan/mlbb-pubg.png" alt="Mlbb Pubg" title="Mlbb Pubg" aria-label="Mlbb Pubg" width="48" height="48" onerror="this.onerror=null;this.src='/assets/img/logos/logo-main.png'" />
                                                                                                                                <img src="/assets/img/sukan/olahraga.png" alt="Olahraga" title="Olahraga" aria-label="Olahraga" width="48" height="48" onerror="this.onerror=null;this.src='/assets/img/logos/logo-main.png'" />
                                                                                                                                <img src="/assets/img/sukan/ragbi.png" alt="Ragbi" title="Ragbi" aria-label="Ragbi" width="48" height="48" onerror="this.onerror=null;this.src='/assets/img/logos/logo-main.png'" />
                                                                                                                                <img src="/assets/img/sukan/takraw.png" alt="Takraw" title="Takraw" aria-label="Takraw" width="48" height="48" onerror="this.onerror=null;this.src='/assets/img/logos/logo-main.png'" />
                                                                                                                                <img src="/assets/img/sukan/tenpin-bowling.png" alt="Tenpin Bowling" title="Tenpin Bowling" aria-label="Tenpin Bowling" width="48" height="48" onerror="this.onerror=null;this.src='/assets/img/logos/logo-main.png'" />
                                                                                                                                <img src="/assets/img/sukan/volleyball.png" alt="Volleyball" title="Volleyball" aria-label="Volleyball" width="48" height="48" onerror="this.onerror=null;this.src='/assets/img/logos/logo-main.png'" />
                                                                            </li>
                                
                                <!-- Language & Theme Selector removed per request -->
                                                                                                        <li class="adomx-dropdown col-auto">
                                        <a class="toggle" href="#">
                                            <span class="user">
                                                <span class="avatar">
                                                    <img src="/assets/img/avatar/profiles.jpg" alt="TS. NORFIRDAUS HARUN" onerror="this.onerror=null;this.src='/assets/img/avatar/profiles.jpg'">
                                                    <span class="status"></span>
                                                </span>
                                                <span class="name">TS. NORFIRDAUS HARUN</span>
                                            </span>
                                        </a>

                                        <div class="adomx-dropdown-menu dropdown-menu-user">
                                            <div class="head">
                                                <h5 class="name">
                                                    <a href="#">TS. NORFIRDAUS HARUN</a>
                                                </h5>
                                                                                                    <a class="mail" href="#">norfirdaus@upnm.edu.my</a>
                                                                                            </div>
                                            <div class="body">
                                                <ul>
                                                    <!-- Removed duplicate 'Tetapan' entry from header (already in sidebar) -->
                                                    <li><a class="trigger-change-password" href="#"><i class="zmdi zmdi-key"></i> Tukar Kata Laluan</a></li>
                                                    <li><a class="confirm-logout" href="/auth/logout.php"><i class="zmdi zmdi-lock-open"></i>Log keluar</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
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
                            <div class="mb-2 text-muted small">Anda log masuk sebagai TS. NORFIRDAUS HARUN — norfirdaus@upnm.edu.my</div>
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
                                                                            <li data-policy="minlength">Panjang sekurang-kurangnya 8 aksara</li>
                                                                                                                <li data-policy="uppercase">Mengandungi sekurang-kurangnya satu huruf besar (A-Z)</li>
                                                                                                                <li data-policy="number">Mengandungi sekurang-kurangnya satu nombor (0-9)</li>
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
                                            minLength: 8,
                                            requireUpper: true,
                                            requireNumber: true,
                                            requireSpecial: false                                        };

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
                                                fetch('/ajax/change_password.php', {
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
<!-- Side Header Start -->
<div class="side-header show">
    <button class="side-header-close"><i class="zmdi zmdi-close"></i></button>
    <div class="side-header-inner custom-scroll">
        <nav class="side-header-menu" id="side-header-menu">
            <ul>
                                                    <li class="">
                        <a href="/index.php"><i class="ti-home"></i> <span>Dashboard</span></a>
                    </li>
                
                                                                                                            <li class="has-sub-menu">
                            <a href="#"><i class="ti-list"></i> <span>Pengurusan</span></a>
                            <ul class="side-header-sub-menu" >
                                                                                <li class="">
                                        <a href="/pages/contingent.php"><i class="ti-user"></i> <span>Kontinjen</span></a>
                                    </li>
                                                                                            <li class="">
                                        <a href="/pages/sports.php"><i class="zmdi zmdi-gamepad"></i> <span>Sukan</span></a>
                                    </li>
                                                                                            <li class="">
                                        <a href="/pages/pasukan.php"><i class="ti-user"></i> <span>Pasukan</span></a>
                                    </li>
                                                                                            <li class="">
                                        <a href="/pages/venues.php"><i class="ti-map"></i> <span>Venue</span></a>
                                    </li>
                                                                                            <li class="">
                                        <a href="/pages/keputusan.php"><i class="ti-cup"></i> <span>Keputusan</span></a>
                                    </li>
                                                                                            <li class="">
                                        <a href="/pages/contingent-user.php"><i class="ti-user"></i> <span>Kontinjen User</span></a>
                                    </li>
                                                            </ul>
                        </li>
                                                                                            <li class="has-sub-menu">
                            <a href="#"><i class="ti-list"></i> <span>Laporan</span></a>
                            <ul class="side-header-sub-menu" >
                                                                                <li class="">
                                        <a href="/pages/ringkasan.php"><i class="ti-bar-chart"></i> <span>Ringkasan</span></a>
                                    </li>
                                                                                            <li class="">
                                        <a href="/pages/results.php"><i class="ti-cup"></i> <span>Keputusan</span></a>
                                    </li>
                                                                                            <li class="">
                                        <a href="/pages/contingent-admin.php"><i class="ti-user"></i> <span>Kontinjen</span></a>
                                    </li>
                                                                                            <li class="">
                                        <a href="/pages/checklist.php"><i class="ti-angle-right"></i> <span>Checklist</span></a>
                                    </li>
                                                            </ul>
                        </li>
                                                                                            <li class="has-sub-menu">
                            <a href="#"><i class="ti-list"></i> <span>Tetapan</span></a>
                            <ul class="side-header-sub-menu" >
                                                                                <li class="">
                                        <a href="/pages/settings.php"><i class="ti-settings"></i> <span>General</span></a>
                                    </li>
                                                                                            <li class="">
                                        <a href="/pages/university.php"><i class="ti-angle-right"></i> <span>Universiti</span></a>
                                    </li>
                                                                                            <li class="">
                                        <a href="/pages/pengurusan-pengguna.php"><i class="ti-id-badge"></i> <span>Pengguna</span></a>
                                    </li>
                                                                                            <li class="">
                                        <a href="/pages/ic_audit.php"><i class="ti-id-badge"></i> <span>Audit MyKad</span></a>
                                    </li>
                                                                                            <li class="">
                                        <a href="/pages/matrix-access.php"><i class="ti-angle-right"></i> <span>Akses Matrix</span></a>
                                    </li>
                                                            </ul>
                        </li>
                                                                                            <li class="has-sub-menu active open">
                            <a href="#"><i class="ti-list"></i> <span>Pertandingan</span></a>
                            <ul class="side-header-sub-menu" style="display:block;">
                                                                                <li class="active">
                                        <a href="/pages/setup-pertandingan.php"><i class="ti-settings"></i> <span>Setup</span></a>
                                    </li>
                                                            </ul>
                        </li>
                                                                        <li class="logout-link">
                        <a class="confirm-logout" href="/auth/logout.php">
                            <i class="ti-power-off"></i> <span>Log Keluar</span>
                        </a>
                    </li>
                            </ul>
        </nav>
    </div>
</div>
<!-- Side Header End -->

<!-- Content Body Start -->
<div class="content-body">
    <div class="container-fluid">
        <div class="container-fluid px-3">
	<style>
		.row-assigned { background-color: #eafaf1; }
		.status-icon { color: #198754; font-weight: 600; font-size: 1rem; display:inline-block; }
		.team-status { text-align: center; vertical-align: middle; }
	</style>
		<div class="row mb-3">
		<div class="col-12">
			<h2 class="mb-1">Setup Pertandingan</h2>
			<p class="text-muted mb-0">Langkah 1: Maklumat Pertandingan</p>
		</div>
	</div>

	<div class="card">
		<div class="card-body">
			<ul class="nav nav-pills mb-3" id="setupTabs" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="tab-1-btn" data-bs-toggle="pill" data-bs-target="#tab-1" type="button" role="tab">1. Maklumat Pertandingan</button>
				</li>
								<li class="nav-item" role="presentation">
					<button class="nav-link " id="tab-2-btn" data-bs-toggle="pill" data-bs-target="#tab-2" type="button">2. Struktur Kumpulan</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link disabled" id="tab-3-btn" aria-disabled="true" type="button">3. Agihan Pasukan</button>
				</li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane show active" id="tab-1" role="tabpanel">
					<form id="form-tab1" method="post">
						<input type="hidden" name="action" value="save_tab1">
						<div class="row">
							<div class="col-md-6">
								<h5>Maklumat Asas</h5>
								<div class="mb-3">
									<label class="form-label">Sukan <span class="text-danger">*</span></label>
									<select id="sukan_id" name="sukan_id" class="form-select" required>
										<option value="">-- Pilih Sukan --</option>
																					<option value="1">Badminton</option>
																					<option value="2">Bola Jaring</option>
																					<option value="3">Bola Tampar</option>
																					<option value="5">Bolasepak</option>
																					<option value="4">Catur</option>
																					<option value="8">MLBB</option>
																					<option value="11">Olahraga</option>
																					<option value="9">PUBG</option>
																					<option value="6">Ragbi 7s</option>
																					<option value="7">Sepak Takraw</option>
																					<option value="10">Tenpin Bowling</option>
																			</select>
								</div>

								<div class="mb-3">
									<label class="form-label">Kategori / Acara <span class="text-danger">*</span></label>
									<select id="kategori_id" name="kategori_id" class="form-select" required>
										<option value="">-- Pilih Kategori --</option>
									</select>
									<div id="kategori-help" class="form-text text-danger small d-none"></div>
								</div>

								<div class="mb-3">
									<label class="form-label">Nama Event <span class="text-danger">*</span></label>
									<input id="nama_event" name="nama_event" type="text" class="form-control" required>
								</div>

								<div class="mb-3">
									<label class="form-label">Tarikh Mula</label>
									<input name="tarikh_mula" type="date" class="form-control">
								</div>

								<div class="mb-3">
									<label class="form-label">Tarikh Tamat</label>
									<input name="tarikh_tamat" type="date" class="form-control">
								</div>

									<div class="mb-3">
										<label class="form-label">Status</label>
										<select name="status" class="form-select">
											<option value="ongoing" selected>ongoing</option>
											<option value="completed">completed</option>
											<option value="cancelled">cancelled</option>
										</select>
									</div>

								<div class="mb-3">
									<button id="save-and-continue" type="submit" class="btn btn-primary">Simpan & Teruskan</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			<div class="tab-pane" id="tab-2" role="tabpanel">
					<form id="form-tab2" method="post">
						<input type="hidden" name="action" value="save_tab2">
						<div class="row">
							<div class="col-md-6">
								<h5>Struktur Kumpulan <span class="badge bg-info ms-2">EDIT MODE</span></h5>
								<div class="mb-3">
									<label class="form-label">Nama Round</label>
									<select class="form-select" id="nama_round" name="nama_round"  >
																																	<option value="Peringkat Kumpulan" selected>Peringkat Kumpulan</option>
																														</select>
								</div>

								<div class="mb-3">
									<label class="form-label">Bilangan Kumpulan <span class="text-danger">*</span></label>
									<input id="bilangan_kumpulan" name="bilangan_kumpulan" type="number" min="1" class="form-control" required value="3" readonly>
																			<div class="form-text text-warning small">Bilangan kumpulan tidak boleh diubah kerana beberapa pasukan telah ditetapkan ke kumpulan. Untuk menukar bilangan, kosongkan assignment pasukan terlebih dahulu.</div>
																	</div>

								<div class="mb-3">
									<label class="form-label">Format Kumpulan</label>
									<select id="format_kumpulan" name="format_kumpulan" class="form-select" disabled >
										<option value="alphabetical" selected>Alphabetical (A, B, C)</option>
										<option value="numeric" >Numeric (1, 2, 3)</option>
									</select>
								</div>

								<h6>Peraturan Kelayakan (optional)</h6>
								<div class="mb-3">
									<label class="form-label">Top N Lulus</label>
									<input id="qualification_topn" name="qualification_topn" type="number" min="1" class="form-control" value="2">
								</div>
								<div class="mb-3">
									<label class="form-label">Kriteria</label>
									<select id="qualification_criteria" name="qualification_criteria" class="form-select">
										<option value="">-- Tiada --</option>
										<option value="mata" selected>mata</option>
										<option value="score" >score</option>
										<option value="masa" >masa</option>
									</select>
								</div>

								<div class="mb-3">
									<button id="save-groups" type="submit" class="btn btn-primary">Simpan Group</button>
								</div>
							</div>

							<div class="col-md-6">
								<h5>Preview Kumpulan</h5>
								<div id="group-preview-area">
									<table class="table table-sm table-bordered" id="group-preview-table">
										<thead>
											<tr><th>Group</th><th>Nama Round</th><th>Order</th></tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
							</div>
						</div>
					</form>
				</div>

			<div class="tab-pane" id="tab-3" role="tabpanel">
					<div class="row">
						<div class="col-md-6">
							<h5>Senarai Pasukan</h5>
														<div class="mb-2">
								<div class="d-flex gap-2 mb-2">
									<input id="search-team" class="form-control" placeholder="Cari nama pasukan">
								</div>
							</div>

							<div id="assign-progress" class="small text-muted mb-2"></div>
							<div id="assign-notice" class="alert alert-info small d-none">Sebahagian pasukan telah diagihkan. Anda boleh sambung pengagihan.</div>

									<!-- Kontinjen summary removed per UX request -->
														<div style="max-height:420px;overflow:auto;">
								<table class="table table-sm" id="teams-table">
									<thead>
										<tr>
											<th><input type="checkbox" id="select-all-teams"></th>
											<th>Nama Pasukan</th>
											<th>Kontinjen</th>
											<th style="width:80px; text-align:center;">Status</th>
										</tr>
									</thead>
									<tbody>
																																<tr data-team-id="138" data-kontinjen-id="20" data-assigned-group="" >
												<td><input type="checkbox" class="team-checkbox"></td>
												<td class="team-name">APM BOLA SEPAK 9-SEBELAH</td>
												<td>APM</td>
												<td class="team-status" title="">
																									</td>
											</tr>
																																<tr data-team-id="53" data-kontinjen-id="6" data-assigned-group="" >
												<td><input type="checkbox" class="team-checkbox"></td>
												<td class="team-name">UIAM BOLA SEPAK 9-SEBELAH</td>
												<td>UIAM</td>
												<td class="team-status" title="">
																									</td>
											</tr>
																																<tr data-team-id="122" data-kontinjen-id="22" data-assigned-group="A" class="row-assigned">
												<td><input type="checkbox" class="team-checkbox"></td>
												<td class="team-name">UITM BOLA SEPAK 9-SEBELAH</td>
												<td>UiTM</td>
												<td class="team-status" title="Pasukan telah berjaya diagihkan ke kumpulan">
																											<span class="status-icon" role="img" aria-label="assigned">✔</span>
																									</td>
											</tr>
																																<tr data-team-id="65" data-kontinjen-id="1" data-assigned-group="" >
												<td><input type="checkbox" class="team-checkbox"></td>
												<td class="team-name">UM BOLA SEPAK 9-SEBELAH</td>
												<td>UM</td>
												<td class="team-status" title="">
																									</td>
											</tr>
																																<tr data-team-id="43" data-kontinjen-id="19" data-assigned-group="" >
												<td><input type="checkbox" class="team-checkbox"></td>
												<td class="team-name">UMK BOLA SEPAK 9-SEBELAH</td>
												<td>UMK</td>
												<td class="team-status" title="">
																									</td>
											</tr>
																																<tr data-team-id="99" data-kontinjen-id="17" data-assigned-group="" >
												<td><input type="checkbox" class="team-checkbox"></td>
												<td class="team-name">UMT BOLA SEPAK 9-SEBELAH</td>
												<td>UMT</td>
												<td class="team-status" title="">
																									</td>
											</tr>
																																<tr data-team-id="149" data-kontinjen-id="16" data-assigned-group="" >
												<td><input type="checkbox" class="team-checkbox"></td>
												<td class="team-name">UNISZA BOLA SEPAK 9-SEBELAH</td>
												<td>UniSZA</td>
												<td class="team-status" title="">
																									</td>
											</tr>
																																<tr data-team-id="110" data-kontinjen-id="4" data-assigned-group="" >
												<td><input type="checkbox" class="team-checkbox"></td>
												<td class="team-name">UPM BOLA SEPAK 9-SEBELAH</td>
												<td>UPM</td>
												<td class="team-status" title="">
																									</td>
											</tr>
																																<tr data-team-id="202" data-kontinjen-id="18" data-assigned-group="A" class="row-assigned">
												<td><input type="checkbox" class="team-checkbox"></td>
												<td class="team-name">UPNM BOLA SEPAK 9-SEBELAH</td>
												<td>UPNM</td>
												<td class="team-status" title="Pasukan telah berjaya diagihkan ke kumpulan">
																											<span class="status-icon" role="img" aria-label="assigned">✔</span>
																									</td>
											</tr>
																																<tr data-team-id="77" data-kontinjen-id="11" data-assigned-group="" >
												<td><input type="checkbox" class="team-checkbox"></td>
												<td class="team-name">USIM BOLA SEPAK 9-SEBELAH</td>
												<td>USIM</td>
												<td class="team-status" title="">
																									</td>
											</tr>
																																<tr data-team-id="89" data-kontinjen-id="7" data-assigned-group="A" class="row-assigned">
												<td><input type="checkbox" class="team-checkbox"></td>
												<td class="team-name">UUM BOLA SEPAK 9-SEBELAH</td>
												<td>UUM</td>
												<td class="team-status" title="Pasukan telah berjaya diagihkan ke kumpulan">
																											<span class="status-icon" role="img" aria-label="assigned">✔</span>
																									</td>
											</tr>
																			</tbody>
								</table>
								<div id="teams-empty-msg" class="small text-muted text-center mt-2 d-none">Tiada pasukan ditemui.</div>
							</div>
							<!-- assign controls: moved below team table as requested -->
							<div class="mt-2">
								<label class="form-label">Pilih Kumpulan</label>
								<div class="d-flex gap-2 mb-2">
									<select id="assign-group-select" class="form-select" style="flex:1;">
										<option value="">-- Pilih Kumpulan --</option>
																					<option value="A">Kumpulan A</option>
																					<option value="B">Kumpulan B</option>
																					<option value="C">Kumpulan C</option>
																			</select>
									<button id="assign-btn" class="btn btn-primary" >Assign ke Kumpulan</button>
								</div>
							</div>
						</div>

						<div class="col-md-6">
							<h5>Kumpulan</h5>
							<div id="groups-container">
																<table class="table table-sm table-bordered" id="groups-table">
									<thead><tr><th style="width:120px">Kumpulan</th><th>Anggota Pasukan</th></tr></thead>
									<tbody>
																																<tr data-group-code="A">
												<td class="align-top">Kumpulan A</td>
												<td>
													<ul class="list-group list-group-flush group-list" data-group-code="A">
																													<li class="list-group-item p-1" data-team-id="122">UITM BOLA SEPAK 9-SEBELAH</li>
																													<li class="list-group-item p-1" data-team-id="202">UPNM BOLA SEPAK 9-SEBELAH</li>
																													<li class="list-group-item p-1" data-team-id="89">UUM BOLA SEPAK 9-SEBELAH</li>
																											</ul>
												</td>
											</tr>
																																<tr data-group-code="B">
												<td class="align-top">Kumpulan B</td>
												<td>
													<ul class="list-group list-group-flush group-list" data-group-code="B">
																											</ul>
												</td>
											</tr>
																																<tr data-group-code="C">
												<td class="align-top">Kumpulan C</td>
												<td>
													<ul class="list-group list-group-flush group-list" data-group-code="C">
																											</ul>
												</td>
											</tr>
																			</tbody>
								</table>
							</div>
							<div class="mt-3">
								<div id="tab3-warning" class="text-danger small mb-2 d-none">
																	</div>
                                
								<button id="save-assignment" class="btn btn-primary" >Simpan Agihan Pasukan</button>
							</div>
						</div>
					</div>
				</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(() => {
	// TAB3: Assign teams to groups
	const teamsTable = document.getElementById('teams-table');
	const searchInput = document.getElementById('search-team');
	const filterKont = document.getElementById('kontinjen-filters');
	const selectAll = document.getElementById('select-all-teams');
	const assignSelect = document.getElementById('assign-group-select');
	const groupsContainer = document.getElementById('groups-container');
	const saveBtn = document.getElementById('save-assignment');

	// helper: mark/unmark a team row as assigned (adds class + status icon + tooltip)
	function setTeamRowAssigned(tr, assigned) {
		try {
			if (!tr) return;
			const statusTd = tr.querySelector('.team-status');
			if (!statusTd) return;
			if (assigned && assigned.toString().trim() !== '') {
				tr.classList.add('row-assigned');
				statusTd.innerHTML = '<span class="status-icon" role="img" aria-label="assigned" title="Pasukan telah berjaya diagihkan ke kumpulan">✔</span>';
				statusTd.setAttribute('title', 'Pasukan telah berjaya diagihkan ke kumpulan');
			} else {
				tr.classList.remove('row-assigned');
				statusTd.innerHTML = '';
				statusTd.removeAttribute('title');
			}
		} catch (e) { console.error('setTeamRowAssigned error', e); }
	}

	function getCheckedTeamRows() {
		return Array.from(document.querySelectorAll('.team-checkbox')).filter(c => c.checked).map(c => c.closest('tr'));
	}

		function updateSaveButtonState() {
			const rows = getCheckedTeamRows();
			const group = assignSelect ? assignSelect.value : null;
			// enable save if there are any staged assignments OR (selected rows + chosen group)
			const staged = Array.from(document.querySelectorAll('#teams-table tbody tr')).some(tr => (tr.getAttribute('data-assigned-group') || '').trim() !== '');
			if (saveBtn) saveBtn.disabled = !(staged || (rows.length > 0 && group));
		}

	function renderAssigned() {
		// clear groups
		document.querySelectorAll('.group-list').forEach(ul => ul.innerHTML = '');
		// find all rows and check for assigned data attribute
		document.querySelectorAll('#teams-table tbody tr').forEach(tr => {
			const assigned = tr.getAttribute('data-assigned-group');
			const tid = tr.getAttribute('data-team-id');
			const name = tr.querySelector('.team-name')?.textContent || '';
			if (assigned) {
				const ul = document.querySelector('.group-list[data-group-code="' + assigned + '"]');
				if (ul) {
					const li = document.createElement('li');
					li.className = 'list-group-item p-1';
					li.textContent = name;
					li.setAttribute('data-team-id', tid);
					ul.appendChild(li);
				}
			}
		});

		// update per-row status icons/highlight based on data-assigned-group attribute
		document.querySelectorAll('#teams-table tbody tr').forEach(tr => {
			const assigned = (tr.getAttribute('data-assigned-group') || '').toString().trim();
			setTeamRowAssigned(tr, assigned);
		});

		// update progress indicator
		try {
			const total = document.getElementById('assign-progress')?.getAttribute('data-total') || null;
			const assigned = Array.from(document.querySelectorAll('#teams-table tbody tr')).filter(tr => (tr.getAttribute('data-assigned-group') || '').trim() !== '').length;
			const progEl = document.getElementById('assign-progress');
			if (progEl) {
				if (total !== null && total !== '') {
					progEl.textContent = assigned + ' / ' + total + ' pasukan telah diagihkan.';
				} else {
					progEl.textContent = assigned + ' pasukan telah diagihkan.';
				}
			}
		} catch (e) { console.error('renderAssigned progress update', e); }

			// refresh kontinjen status table
			if (typeof updateKontinjenStatus === 'function') updateKontinjenStatus();
	}

	// search/filter (more robust)
	function getSelectedKontinjenId() {
		const container = document.getElementById('kontinjen-filters');
		if (!container) return '';
		const active = container.querySelector('button.active');
		return active ? (active.getAttribute('data-kontinjen-id') || '') : '';
	}

	function doTeamSearch() {
		const q = (searchInput.value || '').toString().trim().toLowerCase();
		const kfilter = getSelectedKontinjenId();
		let visible = 0;
		document.querySelectorAll('#teams-table tbody tr').forEach(tr => {
			const name = (tr.querySelector('.team-name')?.textContent || '').toLowerCase();
			const kont = (tr.querySelector('td:nth-child(3)')?.textContent || '').toLowerCase();
			const assigned = (tr.getAttribute('data-assigned-group') || '').toLowerCase();
			const matchesText = q === '' || name.indexOf(q) !== -1 || kont.indexOf(q) !== -1 || assigned.indexOf(q) !== -1;
			const matchesKont = (kfilter === '' || tr.getAttribute('data-kontinjen-id') === kfilter);
			const visibleRow = matchesText && matchesKont;
			tr.style.display = visibleRow ? '' : 'none';
			if (visibleRow) visible++;
		});
		const emptyMsg = document.getElementById('teams-empty-msg');
		if (emptyMsg) emptyMsg.classList.toggle('d-none', visible > 0);
	}

	if (searchInput) {
		searchInput.addEventListener('input', doTeamSearch);
		searchInput.addEventListener('keyup', doTeamSearch);
	}

		// observe changes to checkboxes and group select to toggle Save button
		document.addEventListener('change', function (ev) {
			if (ev.target && (ev.target.matches('.team-checkbox') || ev.target.id === 'assign-group-select')) {
				updateSaveButtonState();
			}
		});
	if (filterKont) {
		// delegated click: toggle active kontinjen button and re-run search
		filterKont.addEventListener('click', function (ev) {
			const btn = ev.target.closest('button[data-kontinjen-id]');
			if (!btn) return;
			// remove active from others
			filterKont.querySelectorAll('button').forEach(b => b.classList.remove('active'));
			btn.classList.add('active');
			doTeamSearch();
		});
	}

	if (selectAll) {
			selectAll.addEventListener('change', function () {
				const checked = this.checked;
				document.querySelectorAll('.team-checkbox').forEach(cb => { cb.checked = checked; });
				updateSaveButtonState();
			});
	}

	// assignBtn removed; assignments are performed via 'Simpan Agihan Pasukan' which
	// now supports assigning selected rows when a Kumpulan is chosen in the dropdown.

	if (saveBtn) {
		saveBtn.addEventListener('click', async function () {
			// collect assignments from staged rows
			const assignments = {};
			document.querySelectorAll('#teams-table tbody tr').forEach(tr => {
				const gid = tr.getAttribute('data-assigned-group');
				if (gid) assignments[tr.getAttribute('data-team-id')] = gid;
			});
			// Also include any selected rows combined with chosen group (if provided)
			const selRows = getCheckedTeamRows();
			const chosen = assignSelect ? (assignSelect.value || '') : '';
			if (selRows.length > 0 && chosen) {
				selRows.forEach(r => { assignments[r.getAttribute('data-team-id')] = chosen; });
			}
			const keys = Object.keys(assignments);
			if (keys.length === 0) { Swal.fire({ icon: 'warning', title: 'Tiada pasukan', text: 'Sila assign sekurang-kurangnya satu pasukan.' }); return; }

			const fd = new FormData();
			fd.append('action', 'save_tab3');
			fd.append('assignments', JSON.stringify(assignments));
			fd.append('event_id', '1');
			try {
				saveBtn.disabled = true; saveBtn.textContent = 'Menyimpan...';
				const res = await fetch('', { method: 'POST', body: fd });
				const json = await res.json();
				if (json.success) {
					// update DOM: set each affected row's data-assigned-group and refresh groups/statuses
					try {
						Object.keys(assignments).forEach(tid => {
							const tr = document.querySelector('#teams-table tbody tr[data-team-id="' + tid + '"]');
							if (tr) {
								const code = assignments[tid] || '';
								tr.setAttribute('data-assigned-group', code);
								setTeamRowAssigned(tr, code);
							}
						});
						// rebuild groups list from current rows
						renderAssigned();
						updateSaveButtonState();
					} catch (e) { console.error('post-save DOM update error', e); }
					Swal.fire({ icon: 'success', title: 'Berjaya', text: 'Agihan Pasukan disimpan.' });
				} else {
					const msg = (json.errors || ['Gagal menyimpan']).join('<br>');
					Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
				}
			} catch (e) {
				console.error(e);
				Swal.fire({ icon: 'error', title: 'Ralat', text: 'Ralat jaringan semasa menyimpan.' });
			} finally {
				saveBtn.disabled = false; saveBtn.textContent = 'Simpan Agihan Pasukan';
			}
		});
	}

	// initial render of any existing assignments (if teams have initial_group_code attribute rendered, we could map)
		// initial render of any existing assignments based on server-rendered attributes
		renderAssigned();
		// now fetch authoritative assignments from server and re-hydrate UI
		async function loadAssignmentsFromServer() {
			try {
				console.debug('[setup-pertandingan] loading assignments from server');
				const res = await fetch('?action=load_assignments', { credentials: 'same-origin' });
				console.debug('[setup-pertandingan] load_assignments HTTP status', res.status);
				let json = await res.json();
				console.debug('[setup-pertandingan] load_assignments response', json);
				// defensive: sometimes previous POST handlers respond; if response looks like save_tab2 (has 'mode' or groups is an array of codes), retry with cache-bust
				if (json && (json.mode || (Array.isArray(json.groups) && json.groups.length && typeof json.groups[0] === 'string'))) {
					console.warn('[setup-pertandingan] unexpected response for load_assignments, retrying with cache-bust');
					const res2 = await fetch('?action=load_assignments&t=' + Date.now(), { credentials: 'same-origin' });
					json = await res2.json();
					console.debug('[setup-pertandingan] load_assignments retry response', json);
				}
				if (!json || !json.success) return;
				const groups = json.groups || {};
				// clear existing group lists
				document.querySelectorAll('.group-list').forEach(ul => ul.innerHTML = '');
				// mark all rows as unassigned first
				document.querySelectorAll('#teams-table tbody tr').forEach(tr => {
					tr.setAttribute('data-assigned-group', '');
					const cb = tr.querySelector('.team-checkbox'); if (cb) cb.checked = false;
					setTeamRowAssigned(tr, '');
				});
				// helper to find group-list ul by matching trimmed/lowercased code
				function findGroupUl(code) {
					const wanted = (code || '').toString().trim().toLowerCase();
					const uls = Array.from(document.querySelectorAll('.group-list'));
					for (let ul of uls) {
						const val = (ul.getAttribute('data-group-code') || '').toString().trim().toLowerCase();
						if (val === wanted) return ul;
					}
					return null;
				}

				// Rebuild groups container entirely from server payload to ensure assignments display
				try {
					const groupsContainerEl = document.getElementById('groups-container');
					if (groupsContainerEl) {
						groupsContainerEl.innerHTML = '';
						const table = document.createElement('table');
						table.className = 'table table-sm table-bordered';
						table.id = 'groups-table';
						const thead = document.createElement('thead');
						thead.innerHTML = '<tr><th style="width:120px">Kumpulan</th><th>Anggota Pasukan</th></tr>';
						table.appendChild(thead);
						const tbody = document.createElement('tbody');
						Object.keys(groups).forEach(code => {
							const tr = document.createElement('tr'); tr.setAttribute('data-group-code', code);
							const td1 = document.createElement('td'); td1.className = 'align-top'; td1.textContent = 'Kumpulan ' + code;
							const td2 = document.createElement('td');
							const ul = document.createElement('ul'); ul.className = 'list-group list-group-flush group-list'; ul.setAttribute('data-group-code', code);
							// append members
							const members = groups[code] || [];
							members.forEach(m => {
								const li = document.createElement('li');
								li.className = 'list-group-item p-1';
								li.setAttribute('data-team-id', m.id);
								li.textContent = m.nama_pasukan;
								ul.appendChild(li);
								// mark left row if present
								const trLeft = document.querySelector('#teams-table tbody tr[data-team-id="' + m.id + '"]');
								if (trLeft) {
									trLeft.setAttribute('data-assigned-group', code);
									const cb = trLeft.querySelector('.team-checkbox'); if (cb) cb.checked = true;
									setTeamRowAssigned(trLeft, code);
								}
							});
							td2.appendChild(ul);
							tr.appendChild(td1); tr.appendChild(td2); tbody.appendChild(tr);
						});
						table.appendChild(tbody);
						groupsContainerEl.appendChild(table);
							// ensure container and list items are visible (defensive)
							groupsContainerEl.style.display = '';
							groupsContainerEl.style.overflow = 'visible';
							Array.from(groupsContainerEl.querySelectorAll('.group-list li')).forEach(li => {
								li.classList.remove('d-none');
								li.style.display = 'list-item';
								li.style.color = '#212529';
							});
							console.debug('[setup-pertandingan] groups rebuilt, groups count=', Object.keys(groups).length, 'list-items=', groupsContainerEl.querySelectorAll('.group-list li').length);
					}
				} catch (e) { console.error('rebuild groups container error', e); }

				// set progress total attribute and show notice
				const progEl = document.getElementById('assign-progress');
				if (progEl) {
					progEl.setAttribute('data-total', json.total_count || '');
					progEl.textContent = (json.assigned_count || 0) + ' / ' + (json.total_count || 0) + ' pasukan telah diagihkan.';
				}
				const notice = document.getElementById('assign-notice');
				if (notice) {
					if ((json.assigned_count || 0) > 0) {
						notice.classList.remove('d-none');
					} else {
						notice.classList.add('d-none');
					}
				}

				// update kontinjen status and save button state
				if (typeof updateKontinjenStatus === 'function') updateKontinjenStatus();
				if (typeof updateSaveButtonState === 'function') updateSaveButtonState();
				// refresh renderAssigned to update progress text
				renderAssigned();
			} catch (e) { console.error('loadAssignmentsFromServer error', e); }
		}
		loadAssignmentsFromServer();
		// also ensure kontinjen status matches initial state
		function updateKontinjenStatus() {
			try {
				const kontRows = document.querySelectorAll('#kontinjen-table tbody tr');
				kontRows.forEach(ktr => {
					const kid = ktr.getAttribute('data-kontinjen-id');
					const assigned = Array.from(document.querySelectorAll('#teams-table tbody tr')).some(tr => {
						return tr.getAttribute('data-kontinjen-id') === kid && (tr.getAttribute('data-assigned-group') || '').trim() !== '';
					});
					const action = ktr.querySelector('.kont-action');
					if (assigned) {
						ktr.classList.add('table-success');
						if (action) action.textContent = '✔️';
						ktr.querySelector('td:nth-child(2)').textContent = 'Assigned';
					} else {
						ktr.classList.remove('table-success');
						if (action) action.textContent = '❌';
						ktr.querySelector('td:nth-child(2)').textContent = 'Belum';
					}
				});
			} catch (e) { console.error('updateKontinjenStatus error', e); }
		}
		updateKontinjenStatus();
		// ensure Save button enabled state reflects initial data
		if (typeof updateSaveButtonState === 'function') updateSaveButtonState();
	})();

	(() => {
		// TAB1 JS: categories, check existing event, create/update submit
		const sukanSelect = document.getElementById('sukan_id');
		const kategoriSelect = document.getElementById('kategori_id');
		const namaInput = document.getElementById('nama_event');
		const form1 = document.getElementById('form-tab1');
		const saveBtn = document.getElementById('save-and-continue');
		let namaEdited = false;

		async function loadKategoriForSukan(sukanId) {
			kategoriSelect.innerHTML = '<option value="">-- Pilih Kategori --</option>';
			if (!sukanId) return;
			try {
				const endpoint = '/ajax/get_kategori.php?sukan_id=' + encodeURIComponent(sukanId);
				console.log('[setup-pertandingan] fetching kategori ->', endpoint);
				const res = await fetch(endpoint, { credentials: 'same-origin' });
				console.log('[setup-pertandingan] kategori HTTP status', res.status);
				const text = await res.text();
				console.log('[setup-pertandingan] kategori response text', text);
				let data = null;
				try {
					data = JSON.parse(text);
				} catch (e) {
					console.error('Invalid JSON from kategori endpoint', text);
					const help = document.getElementById('kategori-help');
					if (help) { help.classList.remove('d-none'); help.textContent = 'Gagal memuatkan kategori (respons tidak sah). Sila cuba semula.'; }
					return;
				}
				if (Array.isArray(data)) {
					const help = document.getElementById('kategori-help');
					if (help) { help.classList.add('d-none'); help.textContent = ''; }
					data.forEach(k => {
						const opt = document.createElement('option');
						opt.value = k.id;
						opt.textContent = k.nama_kategori;
						kategoriSelect.appendChild(opt);
					});
					if (data.length === 0) {
						const help = document.getElementById('kategori-help');
						if (help) { help.classList.remove('d-none'); help.textContent = 'Tiada kategori untuk sukan ini.'; }
					}
				}
			} catch (e) { console.error('Failed to load kategori', e); const help = document.getElementById('kategori-help'); if (help) { help.classList.remove('d-none'); help.textContent = 'Ralat sambungan ketika memuatkan kategori.'; } }
		}

		async function checkEvent(sukanId, kategoriId) {
			try {
				const res = await fetch('?action=check_event&sukan_id=' + encodeURIComponent(sukanId) + '&kategori_id=' + encodeURIComponent(kategoriId));
				const j = await res.json();
				return j;
			} catch (e) { console.error('check_event failed', e); return null; }
		}

		sukanSelect.addEventListener('change', async function () {
			const sukanId = this.value;
			await loadKategoriForSukan(sukanId);
			kategoriSelect.value = '';
			if (!namaEdited) namaInput.value = '';
		});

		kategoriSelect.addEventListener('change', async function () {
			if (!sukanSelect.value) return;
			const sukanText = sukanSelect.options[sukanSelect.selectedIndex]?.text || '';
			const kategoriText = kategoriSelect.options[kategoriSelect.selectedIndex]?.text || '';
			if (!namaEdited && sukanText && kategoriText) {
				namaInput.value = `${sukanText} – ${kategoriText} – SUKAN ASASI 2026`;
			}

			const sukanId = sukanSelect.value;
			const kategoriId = kategoriSelect.value;
			if (!sukanId || !kategoriId) return;
			const chk = await checkEvent(sukanId, kategoriId);
			if (chk && chk.success && chk.exists) {
				const ev = chk.event || {};
				if (!namaEdited) document.getElementById('nama_event').value = ev.nama_event || document.getElementById('nama_event').value;
				if (ev.tarikh_mula) document.querySelector('input[name="tarikh_mula"]').value = ev.tarikh_mula;
				if (ev.tarikh_tamat) document.querySelector('input[name="tarikh_tamat"]').value = ev.tarikh_tamat;
				if (ev.status) document.querySelector('select[name="status"]').value = ev.status;
				window.currentEventId = ev.id;
				if (saveBtn) saveBtn.textContent = 'Kemaskini & Teruskan';
				const tab2Btn = document.getElementById('tab-2-btn');
				if (tab2Btn) {
					tab2Btn.classList.remove('disabled');
					tab2Btn.removeAttribute('aria-disabled');
					tab2Btn.setAttribute('data-bs-toggle', 'pill');
					tab2Btn.setAttribute('data-bs-target', '#tab-2');
				}
			} else {
				window.currentEventId = null;
				if (saveBtn) saveBtn.textContent = 'Simpan & Teruskan';
			}
		});

		namaInput.addEventListener('input', function () { namaEdited = true; });

		if (form1) {
			form1.addEventListener('submit', async function (ev) {
				ev.preventDefault();
				const fd = new FormData(form1);
				try {
					if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Menyimpan...'; }
					const res = await fetch('', { method: 'POST', body: fd });
					const json = await res.json();
					if (json.success) {
						const eventId = json.event_id || json.event_id === 0 ? json.event_id : null;
						window.currentEventId = eventId;
						// ensure session set server-side by handlers
						Swal.fire({ icon: 'success', title: 'Berjaya', text: 'Event disimpan', timer: 1000, showConfirmButton: false }).then(() => {
							const tab2Btn = document.getElementById('tab-2-btn');
							if (tab2Btn) {
								tab2Btn.classList.remove('disabled');
								tab2Btn.removeAttribute('aria-disabled');
								tab2Btn.setAttribute('data-bs-toggle', 'pill');
								tab2Btn.setAttribute('data-bs-target', '#tab-2');
								var tabTrigger = new bootstrap.Tab(tab2Btn);
								tabTrigger.show();
							}
						});
					} else {
						const msg = (json.errors || ['Gagal menyimpan']).join('<br>');
						Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
					}
				} catch (e) {
					console.error(e);
					Swal.fire({ icon: 'error', title: 'Ralat', text: 'Ralat jaringan semasa menyimpan.' });
				} finally {
					if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Simpan & Teruskan'; }
				}
			});
		}
	})();

	(() => {
		// TAB2: Preview generation and submit
		const bilInput = document.getElementById('bilangan_kumpulan');
		const formatSelect = document.getElementById('format_kumpulan');
		const previewTbody = document.querySelector('#group-preview-table tbody');
		const form2 = document.getElementById('form-tab2');

		// existing rounds passed from server
		const existingRounds = [{"id":1,"group_code":"A","group_order":1,"qualification_rule":"{\"top_n\": 2, \"criteria\": \"mata\"}","nama_round":"Peringkat Kumpulan"},{"id":2,"group_code":"B","group_order":2,"qualification_rule":"{\"top_n\": 2, \"criteria\": \"mata\"}","nama_round":"Peringkat Kumpulan"},{"id":3,"group_code":"C","group_order":3,"qualification_rule":"{\"top_n\": 2, \"criteria\": \"mata\"}","nama_round":"Peringkat Kumpulan"}] || [];
		const editMode = true || (Array.isArray(existingRounds) && existingRounds.length > 0);
		const detectedFormat = "alphabetical" || 'alphabetical';
		const groupAssignmentsExist = true;
		const serverQualificationTopn = 2;
		const serverQualificationCriteria = "mata";

		function generateCodes(n, format) {
			const codes = [];
			for (let i = 0; i < n; i++) {
				if (format === 'numeric') codes.push(String(i + 1));
				else {
					codes.push(String.fromCharCode(65 + i));
				}
			}
			return codes;
		}

		function renderPreview() {
			previewTbody.innerHTML = '';
			// If editing, render existing rounds from server to reflect DB state
			if (editMode) {
				const desiredN = Math.max(1, parseInt(bilInput.value || existingRounds.length));
				const format = formatSelect ? formatSelect.value : detectedFormat;
				if (desiredN !== existingRounds.length) {
					// user changed the number in edit mode: generate new codes based on desired count
					const codes = generateCodes(desiredN, format);
					codes.forEach((c, idx) => {
						const tr = document.createElement('tr');
						const td1 = document.createElement('td'); td1.textContent = c;
						const td2 = document.createElement('td'); td2.textContent = 'Peringkat Kumpulan';
						const td3 = document.createElement('td'); td3.textContent = (idx + 1).toString();
						tr.appendChild(td1); tr.appendChild(td2); tr.appendChild(td3);
						previewTbody.appendChild(tr);
					});
					return codes;
				} else {
					const seen = new Set();
					existingRounds.forEach((r, idx) => {
						const code = String(r.group_code || '');
						if (seen.has(code)) return; // avoid duplicate preview rows
						seen.add(code);
						const tr = document.createElement('tr');
						const td1 = document.createElement('td'); td1.textContent = code;
						const td2 = document.createElement('td'); td2.textContent = 'Peringkat Kumpulan';
						const td3 = document.createElement('td'); td3.textContent = (r.group_order || (idx + 1)).toString();
						tr.appendChild(td1); tr.appendChild(td2); tr.appendChild(td3);
						previewTbody.appendChild(tr);
					});
					return existingRounds.map(r => r.group_code);
				}
			}

			const n = Math.max(1, parseInt(bilInput.value || '0'));
			const format = formatSelect.value;
			const codes = generateCodes(n, format);
			codes.forEach((c, idx) => {
				const tr = document.createElement('tr');
				const td1 = document.createElement('td'); td1.textContent = c;
				const td2 = document.createElement('td'); td2.textContent = 'Peringkat Kumpulan';
				const td3 = document.createElement('td'); td3.textContent = (idx + 1).toString();
				tr.appendChild(td1); tr.appendChild(td2); tr.appendChild(td3);
				previewTbody.appendChild(tr);
			});
			return codes;
		}

		// initial render if elements exist
		if (bilInput && formatSelect && previewTbody) {
			if (editMode) {
				// populate bilangan and set readonly only if assignments exist
				bilInput.value = existingRounds.length;
				if (groupAssignmentsExist) bilInput.setAttribute('readonly', 'readonly');
				// set format based on detected format from DB
				formatSelect.value = detectedFormat;
				formatSelect.disabled = true;
				// populate qualification inputs from server
				if (serverQualificationTopn) document.getElementById('qualification_topn').value = serverQualificationTopn;
				if (serverQualificationCriteria) document.getElementById('qualification_criteria').value = serverQualificationCriteria;
				// change button text
				const btn = document.getElementById('save-groups');
				if (btn) btn.textContent = 'Kemaskini Group';
				// enable TAB3 immediately
				const tab3Btn = document.getElementById('tab-3-btn');
				if (tab3Btn) {
					tab3Btn.classList.remove('disabled');
					tab3Btn.removeAttribute('aria-disabled');
					tab3Btn.setAttribute('data-bs-toggle', 'pill');
					tab3Btn.setAttribute('data-bs-target', '#tab-3');
				}
			}
			renderPreview();
			if (!editMode) {
				bilInput.addEventListener('input', renderPreview);
				formatSelect.addEventListener('change', renderPreview);
			}
		}

		if (form2) {
			form2.addEventListener('submit', async function (ev) {
				ev.preventDefault();
				const n = Math.max(1, parseInt(bilInput.value || '0'));
				if (!n || n < 1) {
					Swal.fire({ icon: 'error', title: 'Gagal', text: 'Sila masukkan bilangan kumpulan yang sah.' });
					return;
				}
				// ensure event id available
				const eventId = window.currentEventId || (typeof window !== 'undefined' && window.current_event_id) || null;
				if (!eventId && typeof window !== 'undefined' && !window.currentEventId) {
					Swal.fire({ icon: 'error', title: 'Gagal', text: 'Event ID tidak ditemui. Sila simpan Maklumat Kejohanan dahulu.' });
					return;
				}

				// if editing and group count changed, enforce checks
				if (editMode && existingRounds.length !== n) {
					if (groupAssignmentsExist) {
						Swal.fire({ icon: 'warning', title: 'Tidak dibenarkan', text: 'Bilangan kumpulan tidak boleh diubah kerana terdapat pasukan yang telah ditetapkan ke kumpulan.' });
						return;
					}
					// if reducing groups, ask for confirmation about deleting groups
					if (existingRounds.length > n) {
						const conf = await Swal.fire({
							title: 'Anda pasti?',
							html: 'Mengurangkan bilangan kumpulan akan <strong>memadam</strong> kumpulan berlebihan. Ini mungkin menyebabkan kehilangan struktur. Teruskan?',
							icon: 'warning',
							showCancelButton: true,
							confirmButtonText: 'Ya, padam',
							cancelButtonText: 'Batal'
						});
						if (!conf.isConfirmed) return;
					}
				}

				const codes = renderPreview();
				const fd = new FormData(form2);
				fd.append('group_codes', JSON.stringify(codes));
				fd.append('event_id', eventId);
				try {
					const btn = document.getElementById('save-groups');
					btn.disabled = true; btn.textContent = 'Menyimpan...';
					const res = await fetch('', { method: 'POST', body: fd });
					const json = await res.json();
					if (json.success) {
						const okText = json.mode === 'update' ? 'Struktur kumpulan dikemaskini' : 'Struktur kumpulan disimpan';
						Swal.fire({ icon: 'success', title: 'Berjaya', text: okText, timer: 1200, showConfirmButton: false }).then(() => {
							// enable and switch to TAB 3
							const tab3Btn = document.getElementById('tab-3-btn');
							if (tab3Btn) {
								tab3Btn.classList.remove('disabled');
								tab3Btn.removeAttribute('aria-disabled');
								tab3Btn.setAttribute('data-bs-toggle', 'pill');
								tab3Btn.setAttribute('data-bs-target', '#tab-3');
								var tabTrigger = new bootstrap.Tab(tab3Btn);
								tabTrigger.show();
							}
							// if created, reload page to refresh rounds list used in TAB3; if updated, update select/options in-page
							if (json.mode === 'create') {
								window.location.reload();
							} else {
								// update assign-group-select and groups container using returned groups if provided
								const assignSelect = document.getElementById('assign-group-select');
								if (assignSelect) {
									assignSelect.innerHTML = '<option value="">-- Pilih Kumpulan --</option>';
										const newGroups = json.groups || codes;
										newGroups.forEach(c => {
											const opt = document.createElement('option'); opt.value = c; opt.textContent = 'Kumpulan ' + c; assignSelect.appendChild(opt);
										});
								}
								// update preview
								if (typeof renderPreview === 'function') renderPreview();
								// rebuild Tab3 groups container as a full-width table so it reflects DB state immediately
								try {
									const gContainer = document.getElementById('groups-container');
									if (gContainer) {
										const groups = json.groups || codes;
										gContainer.innerHTML = '';
										const table = document.createElement('table');
										table.className = 'table table-sm table-bordered';
										table.id = 'groups-table';
										const thead = document.createElement('thead');
										thead.innerHTML = '<tr><th style="width:120px;">Group</th><th>Anggota Pasukan</th></tr>';
										table.appendChild(thead);
										const tbody = document.createElement('tbody');
										groups.forEach(gcode => {
											const tr = document.createElement('tr'); tr.setAttribute('data-group-code', gcode);
												const td1 = document.createElement('td'); td1.className = 'align-top'; td1.textContent = 'Kumpulan ' + gcode;
											const td2 = document.createElement('td');
											const ul = document.createElement('ul'); ul.className = 'list-group list-group-flush group-list'; ul.setAttribute('data-group-code', gcode);
											td2.appendChild(ul);
											tr.appendChild(td1); tr.appendChild(td2); tbody.appendChild(tr);
										});
										table.appendChild(tbody);
										gContainer.appendChild(table);
									}
								} catch (e) { console.error('Failed to rebuild groups container', e); }
							}
						});
					} else {
						const msg = (json.errors || ['Gagal menyimpan struktur kumpulan.']).join('<br>');
						Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
					}
				} catch (e) {
					console.error(e);
					Swal.fire({ icon: 'error', title: 'Ralat', text: 'Ralat jaringan semasa menyimpan.' });
				} finally {
					const btn = document.getElementById('save-groups');
					btn.disabled = false; btn.textContent = editMode ? 'Kemaskini Group' : 'Simpan Group';
				}
			});
		}
	})();
</script>

<script>
// server-side debug exported to console for quick inspection
window.__setup_debug = {
	event_id: 1,
	sukan_id: 5,
	rounds: 3,
	teams: 11,
	sample_team_ids: [138,53,122,65,43,99,149,110,202,77]};
console.log('[setup-pertandingan debug]', window.__setup_debug);
</script>

    </div>
</div>
<!-- Content Body End -->

<!-- Footer Section Start -->
<div class="footer-section">
    <div class="container-fluid">
        <div class="footer-copyright text-center">
            <p class="text-body-light">
                2026 &copy; Sukan Asasi Malaysia 2026. Dikuasakan oleh Universiti Pertahanan Nasional Malaysia (UPNM)
            </p>
        </div>
    </div>
</div>
<!-- Footer Section End -->

</div>

<!-- JS -->
<script src="/assets/light/js/vendor/modernizr-3.6.0.min.js"></script>
<script src="/assets/light/js/vendor/jquery-3.3.1.min.js"></script>
<script src="/assets/light/js/vendor/popper.min.js"></script>
<script src="/assets/light/js/vendor/bootstrap.min.js"></script>
<script src="/assets/light/js/plugins/perfect-scrollbar.min.js"></script>
<script src="/assets/light/js/plugins/tippy4.min.js.js"></script>
<script src="/assets/light/js/main.js"></script>
</body>
</html>
