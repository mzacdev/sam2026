<?php
/**
 * Site Configuration
 * CoreUI Bootstrap Admin Template
 * SAM 2026 - Role-Based Access Control System
 */

// Site Settings
define('SITE_NAME', 'Sukan Asasi Malaysia 2026');
define('SITE_FULL_NAME', 'Sukan Asasi Malaysia');
define('SITE_DESCRIPTION', 'Sistem Pengurusan Kejohanan Sukan Asasi Malaysia');
define('SITE_TITLE', 'Papan Pemuka');
define('BASE_URL', '/sam2026/');

// Debug Mode (set to false in production)
define('DEBUG_MODE', true);

// Initialize session early (before any output)
// This ensures session is available for all subsequent operations
if (file_exists(__DIR__ . '/config/auth.php')) {
    require_once __DIR__ . '/config/auth.php';
    Session::start();
}

// Helper function to get asset URL (works from any directory)
function asset($path) {
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

// Helper function to get page URL (works from any directory)
function url($path) {
    return BASE_URL . ltrim($path, '/');
}

// Helper function to get logo URL
function logo($filename) {
    return asset('img/logos/' . $filename);
}

// Logo configuration
define('LOGO_HEADER', 'apple-icon-180x180.png'); // Main logo for header/navbar
define('LOGO_FAVICON', 'favicon.ico'); // Favicon
define('LOGO_APPLE_TOUCH', 'apple-icon-180x180.png'); // Apple touch icon
define('LOGO_ANDROID', 'android-icon-192x192.png'); // Android icon

// Navigation Menu Items
$nav_items = [
    [
        'title' => 'Papan Pemuka',
        'icon' => 'cil-speedometer',
        'url' => 'index.php',
        'active' => true
    ],
    [
        'title' => 'Kontinjen',
        'icon' => 'cil-people',
        'url' => 'pages/contingent.php',
        'active' => false
    ],
    [
        'title' => 'Sukan',
        'icon' => 'cil-gamepad',
        'url' => 'pages/sports.php',
        'active' => false
    ],
    [
        'title' => 'Atlet',
        'icon' => 'cil-user',
        'url' => 'pages/athletes.php',
        'active' => false
    ],
    [
        'title' => 'Venue',
        'icon' => 'cil-map',
        'url' => 'pages/venues.php',
        'active' => false
    ],
    [
        'title' => 'Keputusan',
        'icon' => 'cil-award',
        'url' => 'pages/results.php',
        'active' => false
    ],
    [
        'title' => 'Medal Tally',
        'icon' => 'cil-star',
        'url' => 'pages/medal-tally.php',
        'active' => false
    ],
    [
        'title' => 'Laporan',
        'icon' => 'cil-chart',
        'url' => 'pages/reports.php',
        'active' => false
    ],
    [
        'title' => 'Tetapan',
        'icon' => 'cil-settings',
        'url' => 'pages/settings.php',
        'active' => false
    ]
];

// Note: Navigation filtering is handled in includes/topbar.php using RBAC
// This ensures centralized access control based on pageAccessRules

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
$script_path = $_SERVER['SCRIPT_NAME'];
$script_path = str_replace('\\', '/', $script_path);
$base_path = str_replace('\\', '/', BASE_URL);
$relative_path = str_replace($base_path, '', $script_path);
$relative_path = ltrim($relative_path, '/');

foreach ($nav_items as &$item) {
    // Normalize paths for comparison
    $item_path = $item['url'];
    $item_basename = basename($item_path);
    
    // Check if current page matches the nav item
    if ($item_basename === $current_page || $item_path === $relative_path || $item_path === $script_path) {
        $item['active'] = true;
    } else {
        $item['active'] = false;
    }
}
unset($item);
?>

