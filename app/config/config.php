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

// Debug logging: emit which nav items/sections were marked active when DEBUG_MODE is enabled
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    try {
        foreach ($nav_items as $ni) {
            if (!empty($ni['active'])) {
                error_log('[nav_active] nav_item active -> ' . ($ni['title'] ?? $ni['url'] ?? json_encode($ni)));
            }
        }
        if (isset($nav_sections) && is_array($nav_sections)) {
            foreach ($nav_sections as $sec) {
                $secTitle = $sec['title'] ?? '(section)';
                if (isset($sec['children']) && is_array($sec['children'])) {
                    foreach ($sec['children'] as $ch) {
                        if (!empty($ch['active'])) {
                            error_log('[nav_active] nav_section child active -> ' . $secTitle . ' :: ' . ($ch['title'] ?? $ch['url'] ?? json_encode($ch)));
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log('[nav_active] debug logging failed: ' . $e->getMessage());
    }
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
define('DEBUG_MODE', false);

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
    [ 'title' => 'Papan Pemuka', 'icon' => 'cil-speedometer', 'url' => 'index.php', 'active' => false ],
    [ 'title' => 'Kontinjen', 'icon' => 'cil-people', 'url' => 'pages/contingent.php', 'active' => false ],
    // removed top-level Pengurusan Pengguna; moved under Tetapan as 'Pengguna'
    [ 'title' => 'Sukan', 'icon' => 'cil-gamepad', 'url' => 'pages/sports.php', 'active' => false ],
    [ 'title' => 'Pasukan', 'icon' => 'cil-people', 'url' => 'pages/pasukan.php', 'active' => false ],
    [ 'title' => 'Venue', 'icon' => 'cil-map', 'url' => 'pages/venues.php', 'active' => false ],
    [ 'title' => 'Keputusan', 'icon' => 'cil-award', 'url' => 'pages/results.php', 'active' => false ],
    [ 'title' => 'Medal Tally', 'icon' => 'cil-star', 'url' => 'pages/medal-tally.php', 'active' => false ],
    [ 'title' => 'Laporan', 'icon' => 'cil-chart', 'url' => 'pages/reports.php', 'active' => false ],
    [ 'title' => 'Tetapan', 'icon' => 'cil-settings', 'url' => 'pages/settings.php', 'active' => false ],
];

/* ============================================================
 * NAVIGATION SECTIONS (grouped)
 * Rendered by sidebar as collapsible groups. Individual child
 * visibility is controlled by RBAC via `isNavItemVisible()`.
 */
$nav_sections = [
    [
        'title' => 'Pengurusan',
        'children' => [
            ['title' => 'Kontinjen', 'icon' => 'cil-people', 'url' => 'pages/contingent.php'],
            ['title' => 'Sukan', 'icon' => 'cil-gamepad', 'url' => 'pages/sports.php'],
            ['title' => 'Pasukan', 'icon' => 'cil-people', 'url' => 'pages/pasukan.php'],
            ['title' => 'Venue', 'icon' => 'cil-map', 'url' => 'pages/venues.php'],
            ['title' => 'Keputusan', 'icon' => 'cil-award', 'url' => 'pages/keputusan.php'],
            ['title' => 'Kontinjen User', 'icon' => 'cil-people', 'url' => 'pages/contingent-user.php'],
        ],
    ],
    [
        'title' => 'Laporan',
        'children' => [
            ['title' => 'Ringkasan', 'icon' => 'cil-chart', 'url' => 'pages/ringkasan.php'],
            ['title' => 'Keputusan', 'icon' => 'cil-award', 'url' => 'pages/results.php'],
            ['title' => 'Kontingen', 'icon' => 'cil-people', 'url' => 'pages/contingent-admin.php'],
            ['title' => 'Checklist', 'icon' => 'cil-list', 'url' => 'pages/checklist.php'],
        ],
    ],
    [
        'title' => 'Tetapan',
        'children' => [
            ['title' => 'General', 'icon' => 'cil-settings', 'url' => 'pages/settings.php'],
            ['title' => 'Universiti', 'icon' => 'cil-building', 'url' => 'pages/university.php'],
            ['title' => 'Pengguna', 'icon' => 'cil-user', 'url' => 'pages/pengurusan-pengguna.php'],
            ['title' => 'Audit MyKad', 'icon' => 'cil-id-card', 'url' => 'pages/ic_audit.php'],
            ['title' => 'Akses Matrix', 'icon' => 'cil-lock-unlocked', 'url' => 'pages/matrix-access.php'],
        ],
    ],
];

/* ============================================================
 * ACTIVE MENU DETECTION
 * ============================================================ */
$current_page = basename($_SERVER['PHP_SELF']);
$script_path  = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$relative_path = ltrim(str_replace(BASE_URL, '', $script_path), '/');

// normalize helpers
function _nav_normalize($p) {
    $p = trim((string)$p);
    $p = preg_replace('#^/+#', '/', $p);
    $p = rtrim($p, '/');
    return strtolower($p === '' ? '/' : $p);
}

$current_page_base = strtolower(basename($_SERVER['PHP_SELF']));
$script_base = strtolower(basename($script_path));
$rel_norm = _nav_normalize('/' . ltrim($relative_path, '/'));

foreach ($nav_items as &$item) {
    $item_path = '/' . ltrim($item['url'], '/');
    $item_norm = _nav_normalize($item_path);
    $item_base = strtolower(basename($item_path));

    // Mark active if any reasonable match: basename, exact normalized path, or containment
    if (
        $item_base === $current_page_base ||
        $item_base === $script_base ||
        $item_norm === $rel_norm ||
        stripos($rel_norm, $item_norm) !== false
    ) {
        $item['active'] = true;
    }
}
unset($item);

// Also mark children in nav_sections active so markup that uses "active" flag works
if (isset($nav_sections) && is_array($nav_sections)) {
    foreach ($nav_sections as $sidx => $section) {
        if (empty($section['children']) || !is_array($section['children'])) continue;
        foreach ($section['children'] as $cidx => $child) {
            $child_path = '/' . ltrim($child['url'], '/');
            $child_norm = _nav_normalize($child_path);
            $child_base = strtolower(basename($child_path));

            if (
                $child_base === $current_page_base ||
                $child_base === $script_base ||
                $child_norm === $rel_norm ||
                stripos($rel_norm, $child_norm) !== false
            ) {
                $nav_sections[$sidx]['children'][$cidx]['active'] = true;
            }
        }
    }
}
