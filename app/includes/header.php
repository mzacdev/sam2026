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
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo asset('light/images/favicon.ico'); ?>">

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
                    var allowed = ['style-primary.css','style-red.css','style-green.css','style-brown.css','style-indigo.css','style-orange.css','style-pink.css','style-purple.css'];
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
                                <!-- Language & Theme Selector -->
                                <li class="adomx-dropdown col-auto">
                                    <a class="toggle" href="#" id="headerLocaleToggle">
                                        <i class="zmdi zmdi-globe"></i>
                                    </a>
                                    <div class="adomx-dropdown-menu dropdown-menu-user">
                                        <div class="head">
                                            <h5 class="name">Paparan</h5>
                                        </div>
                                        <div class="body">
                                            <ul>
                                                <li class="mb-2 px-3">
                                                    <div class="small text-muted">Bahasa</div>
                                                    <div class="mt-1">
                                                        <button class="btn btn-sm btn-outline-secondary me-1" onclick="setLanguage('ms')">MS</button>
                                                        <button class="btn btn-sm btn-outline-secondary" onclick="setLanguage('en')">EN</button>
                                                    </div>
                                                </li>
                                                <li class="px-3">
                                                    <div class="small text-muted">Warna Tema</div>
                                                    <div class="mt-2 d-flex gap-2 flex-wrap">
                                                        <button class="btn btn-sm btn-light border" onclick="setTheme('style-primary.css')">Default</button>
                                                        <button class="btn btn-sm btn-danger" onclick="setTheme('style-red.css')">Merah</button>
                                                        <button class="btn btn-sm btn-success" onclick="setTheme('style-green.css')">Hijau</button>
                                                        <button class="btn btn-sm btn-warning" onclick="setTheme('style-orange.css')">Oren</button>
                                                        <button class="btn btn-sm btn-info" onclick="setTheme('style-indigo.css')">Indigo</button>
                                                        <button class="btn btn-sm btn-dark" onclick="setTheme('style-brown.css')">Coklat</button>
                                                        <button class="btn btn-sm btn-pink" style="background:#ff6fa3;color:#fff;border:none;" onclick="setTheme('style-pink.css')">Pink</button>
                                                        <button class="btn btn-sm btn-purple" style="background:#6f42c1;color:#fff;border:none;" onclick="setTheme('style-purple.css')">Purple</button>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                                <?php if ($currentUser): ?>
                                    <li class="adomx-dropdown col-auto">
                                        <a class="toggle" href="#">
                                            <span class="user">
                                                <span class="avatar">
                                                    <img src="<?php echo asset('light/images/avatar/avatar-1.jpg'); ?>" alt="">
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
                                                    <li><a href="<?php echo url('pages/settings.php'); ?>"><i class="zmdi zmdi-settings"></i>Tetapan</a></li>
                                                    <li><a href="<?php echo url('auth/logout.php'); ?>"><i class="zmdi zmdi-lock-open"></i>Log keluar</a></li>
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

    <script>
        // Base URL for theme CSS files
        var __themeBase = '<?php echo asset("light/css/"); ?>';
        function setTheme(filename) {
            try {
                var allowed = ['style-primary.css','style-red.css','style-green.css','style-brown.css','style-indigo.css','style-orange.css','style-pink.css','style-purple.css'];
                if (allowed.indexOf(filename) === -1) return;
                localStorage.setItem('sam_theme', filename);
                var link = document.getElementById('themeStylesheet');
                if (link) link.href = __themeBase + filename;
            } catch(e) { console && console.warn && console.warn(e); }
        }

        function setLanguage(lang) {
            try {
                if (!lang) return;
                // set cookie (server-side will read this on next request)
                var d = new Date(); d.setTime(d.getTime() + (365*24*60*60*1000));
                document.cookie = 'sam_lang=' + encodeURIComponent(lang) + '; path=/; expires=' + d.toUTCString();
                localStorage.setItem('sam_lang', lang);
                // update html lang immediately
                document.documentElement.lang = lang;
                // reload so server-side translations (if any) can apply
                location.reload();
            } catch(e) { console && console.warn && console.warn(e); }
        }
    </script>
