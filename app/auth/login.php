<?php
/**
 * Login Page (Light theme)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

Session::start();
$auth = getAuth();

    if ($auth->isLoggedIn()) {
    header('Location: ' . url('pages/dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Sila isi semua medan yang diperlukan';
    } else {
        $result = $auth->login($email, $password);
        if ($result['success']) {
            $returnUrl = $_GET['return'] ?? null;

            if (!$returnUrl) {
                // Default landing for all roles is dashboard
                $returnUrl = url('pages/dashboard.php');
            }

            if ($returnUrl && !preg_match('#^https?://#i', $returnUrl)) {
                $returnUrl = '/' . ltrim($returnUrl, '/');
                if (BASE_URL !== '' && strpos($returnUrl, BASE_URL . '/') !== 0 && $returnUrl !== BASE_URL) {
                    $returnUrl = BASE_URL . $returnUrl;
                }
            }

            $returnUrl = filter_var($returnUrl, FILTER_SANITIZE_URL);
            if ($returnUrl && (strpos($returnUrl, BASE_URL) === 0 || strpos($returnUrl, '/') === 0)) {
                header('Location: ' . $returnUrl . '?login=success');
            } else {
                header('Location: ' . url('pages/dashboard.php?login=success'));
            }
            exit;
        }
        $error = $result['message'] ?? 'Log masuk gagal. Sila cuba lagi.';
    }
}

$page_title = 'Log Masuk';
?>
<!doctype html>
<html class="no-js" lang="ms">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?php echo defined('SITE_DESCRIPTION') ? SITE_DESCRIPTION : SITE_NAME; ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo asset('img/favicon.ico'); ?>">

    <link rel="stylesheet" href="<?php echo asset('light/css/vendor/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/vendor/material-design-iconic-font.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/vendor/font-awesome.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/vendor/themify-icons.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/vendor/cryptocurrency-icons.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/plugins/plugins.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/helper.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/style-primary.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('light/css/custom.css'); ?>">
</head>
<body>

<div class="main-wrapper">
    <div class="content-body m-0 p-0">
        <div class="login-register-wrap">
            <div class="row">

                <div class="d-flex align-self-center justify-content-center order-2 order-lg-1 col-lg-5 col-12">
                    <div class="login-register-form-wrap">

                        <div class="content">
                            <h1>Log Masuk</h1>
                            <p>Gunakan akaun anda untuk akses sistem e‑Sukan.</p>
                        </div>

                        <div class="login-register-form">
                            <?php if ($error): ?>
                                <div class="alert alert-danger mb-20"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-12 mb-20">
                                        <input class="form-control" type="email" name="email" placeholder="E-mel" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" autocomplete="email" required>
                                    </div>
                                    <div class="col-12 mb-20">
                                        <input class="form-control" type="password" name="password" placeholder="Kata laluan" required>
                                    </div>
                                    <div class="col-12 mb-20">
                                        <label for="remember" class="adomx-checkbox-2">
                                            <input id="remember" type="checkbox" name="remember_me">
                                            <i class="icon"></i> Ingat saya
                                        </label>
                                    </div>
                                    <div class="col-12 mt-10">
                                        <button class="button button-primary button-outline" type="submit">Log Masuk</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="login-register-bg order-1 order-lg-2 col-lg-7 col-12" style="background-image: url('<?php echo asset('img/banners/sam2026-banner.jpg'); ?>');">
                    <div class="content">
                        <h1><?php echo SITE_NAME; ?></h1>
                        <p>Sistem Pengurusan Kejohanan e‑Sukan Asasi Malaysia.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="<?php echo asset('light/js/vendor/modernizr-3.6.0.min.js'); ?>"></script>
<script src="<?php echo asset('light/js/vendor/jquery-3.3.1.min.js'); ?>"></script>
<script src="<?php echo asset('light/js/vendor/popper.min.js'); ?>"></script>
<script src="<?php echo asset('light/js/vendor/bootstrap.min.js'); ?>"></script>
<script src="<?php echo asset('light/js/plugins/perfect-scrollbar.min.js'); ?>"></script>
<script src="<?php echo asset('light/js/plugins/tippy4.min.js.js'); ?>"></script>
<script src="<?php echo asset('light/js/main.js'); ?>"></script>
</body>
</html>
