<?php
/**
 * Site Configuration
 * CoreUI Bootstrap Admin Template
 * SAM 2026 - Role-Based Access Control System
 */

/* ============================================================
 * BASE URL (AUTO-DETECT, FUTURE-PROOF)
 * ============================================================
 * - Root domain      → BASE_URL = ''
 * - Subfolder app    → BASE_URL = '/folder'
 * - Works for HTTP / HTTPS / any port
 */
$docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$appRoot = realpath(__DIR__ . '/..');
$baseUrl = '';

if ($docRoot && $appRoot && strpos($appRoot, $docRoot) === 0) {
    $baseUrl = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
}

$baseUrl = rtrim($baseUrl, '/');
define('BASE_URL', $baseUrl);

/* ============================================================
 * SITE SETTINGS
 * ============================================================ */
define('SITE_NAME', 'Sukan Asasi Malaysia 2026');
define('SITE_FULL_NAME', 'Sukan Asasi Malaysia');
define('SITE_DESCRIPTION', 'Sistem Pengurusan Kejohanan Sukan Asasi Malaysia');
define('SITE_TITLE', 'Papan Pemuka');

/* ============================================================
 * DEBUG MODE
 * ============================================================ */
define('DEBUG_MODE', true);

/* ============================================================
 * SESSION & AUTH INITIALIZATION
 * ============================================================ */
if (file_exists(__DIR__ . '/auth.php')) {
    require_once __DIR__ . '/auth.php';
    Session::start();
}

/* ============================================================
 * HELPER FUNCTIONS
 * ============================================================ */

/**
 * Asset URL helper
 * Example: asset('css/custom.css')
 * Output : /assets/css/custom.css
 */
function asset($path) {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Page URL helper
 * Example: url('auth/login.php')
 * Output : /auth/login.php
 */
function url($path) {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Logo helper
 */
function logo($filename) {
    return asset('img/logos/' . $filename);
}

/* ============================================================
 * LOGO CONFIGURATION
 * ============================================================ */
define('LOGO_HEADER', 'apple-icon-180x180.png');
define('LOGO_FAVICON', 'favicon.ico');
define('LOGO_APPLE_TOUCH', 'apple-icon-180x180.png');
define('LOGO_ANDROID', 'android-icon-192x192.png');

/* ============================================================
 * NAVIGATION MENU
 * ============================================================ */
$nav_items = [
    [
        'title' => 'Papan Pemuka',
        'icon'  => 'cil-speedometer',
        'url'   => 'index.php',
        'active'=> false
    ],
    [
        'title' => 'Kontinjen',
        'icon'  => 'cil-people',
        'url'   => 'pages/contingent.php',
        'active'=> false
    ],
    [
        'title' => 'Sukan',
        'icon'  => 'cil-gamepad',
        'url'   => 'pages/sports.php',
        'active'=> false
    ],
    [
        'title' => 'Atlet',
        'icon'  => 'cil-user',
        'url'   => 'pages/athletes.php',
        'active'=> false
    ],
    [
        'title' => 'Venue',
        'icon'  => 'cil-map',
        'url'   => 'pages/venues.php',
        'active'=> false
    ],
    [
        'title' => 'Keputusan',
        'icon'  => 'cil-award',
        'url'   => 'pages/results.php',
        'active'=> false
    ],
    [
        'title' => 'Medal Tally',
        'icon'  => 'cil-star',
        'url'   => 'pages/medal-tally.php',
        'active'=> false
    ],
    [
        'title' => 'Laporan',
        'icon'  => 'cil-chart',
        'url'   => 'pages/reports.php',
        'active'=> false
    ],
    [
        'title' => 'Tetapan',
        'icon'  => 'cil-settings',
        'url'   => 'pages/settings.php',
        'active'=> false
    ]
];

/* ============================================================
 * ACTIVE MENU DETECTION
 * ============================================================ */
$current_page = basename($_SERVER['PHP_SELF']);
$script_path  = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$relative_path = ltrim(str_replace(BASE_URL, '', $script_path), '/');

foreach ($nav_items as &$item) {
    $item_path = ltrim($item['url'], '/');
    $item_base = basename($item_path);

    if (
        $item_base === $current_page ||
        $item_path === $relative_path
    ) {
        $item['active'] = true;
    }
}
unset($item);
