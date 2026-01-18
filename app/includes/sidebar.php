<?php
$iconMap = [
    'cil-speedometer' => 'ti-home',
    'cil-people' => 'ti-user',
    'cil-gamepad' => 'zmdi zmdi-gamepad',
    'cil-user' => 'ti-id-badge',
    'cil-map' => 'ti-map',
    'cil-award' => 'ti-cup',
    'cil-star' => 'ti-star',
    'cil-chart' => 'ti-bar-chart',
    'cil-settings' => 'ti-settings',
    'cil-id-card' => 'ti-id-badge',
];

// Get current user and RBAC if authenticated (layout.php may already provide this)
$rbac = $rbac ?? null;
if (!defined('SKIP_AUTH_CHECK') && $rbac === null) {
    if (file_exists(__DIR__ . '/../config/database.php') &&
        file_exists(__DIR__ . '/../config/auth.php') &&
        file_exists(__DIR__ . '/../config/rbac.php')) {
        require_once __DIR__ . '/../config/database.php';
        require_once __DIR__ . '/../config/auth.php';
        require_once __DIR__ . '/../config/rbac.php';
        if (session_status() === PHP_SESSION_NONE) {
            Session::start();
        }
        $rbac = getRBAC();
    }
}

$contingentCode = Session::get('kod_universiti') ?? Session::get('kod_university') ?? '';
if (!$contingentCode) {
    try {
        $role = Session::get('user_role');
        $kontinjenId = Session::get('kontinjen_id');
        if ($role === 'CONTINGENT' && $kontinjenId) {
            $db = getDB();
            $stmt = $db->prepare("SELECT k.kod_universiti FROM table_kontinjen k WHERE k.id = :id AND k.deleted_at IS NULL");
            $stmt->execute([':id' => $kontinjenId]);
            $contingentCode = $stmt->fetchColumn() ?: '';
        }
    } catch (Exception $e) {
        // ignore
    }
}

$displayNavItems = $nav_items;
$useSections = isset($nav_sections) && is_array($nav_sections);

if ($rbac) {
    if ($useSections) {
        foreach ($nav_sections as $sidx => $section) {
            $visibleChildren = [];
            foreach ($section['children'] as $child) {
                if ($rbac->isNavItemVisible($child['url'])) {
                    $visibleChildren[] = $child;
                }
            }
            $nav_sections[$sidx]['children'] = $visibleChildren;
        }
    } else {
        $displayNavItems = [];
        foreach ($nav_items as $item) {
            if ($rbac->isNavItemVisible($item['url'])) {
                $displayNavItems[] = $item;
            }
        }
    }
}
// auth state for logout
$auth = function_exists('getAuth') ? getAuth() : null;
$isLoggedIn = ($auth && $auth->isLoggedIn());
?>
<!-- Side Header Start -->
<div class="side-header show">
    <button class="side-header-close"><i class="zmdi zmdi-close"></i></button>
    <div class="side-header-inner custom-scroll">
        <nav class="side-header-menu" id="side-header-menu">
            <ul>
                <?php // Dashboard first ?>
                <?php if ($rbac && $rbac->isNavItemVisible('index.php')): ?>
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : ''; ?>">
                        <a href="<?php echo url('index.php'); ?>"><i class="ti-home"></i> <span>Dashboard</span></a>
                    </li>
                <?php endif; ?>

                <?php if ($useSections && isset($nav_sections) && is_array($nav_sections)): ?>
                    <?php foreach ($nav_sections as $section): ?>
                        <?php if (empty($section['children'])) continue; ?>
                        <?php
                            $visibleChildren = $section['children'];
                            if (empty($visibleChildren)) continue;

                            // determine current request path to robustly match submenu URLs
                            $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
                            $queryString = $_SERVER['QUERY_STRING'] ?? '';
                            $sectionActive = false;
                            foreach ($visibleChildren as $childCheck) {
                                // resolve the child href the same way the link is rendered
                                $childHref = url($childCheck['url']);
                                $childPathCheck = parse_url($childHref, PHP_URL_PATH) ?: '/' . ltrim($childCheck['url'], '/');

                                // strip BASE_URL prefix if present so comparisons work when app is in subfolder
                                if (defined('BASE_URL') && BASE_URL) {
                                    $prefix = rtrim(BASE_URL, '/');
                                    if ($prefix !== '' && stripos($childPathCheck, $prefix) === 0) {
                                        $childPathCheck = substr($childPathCheck, strlen($prefix));
                                    }
                                    if ($prefix !== '' && stripos($requestPath ?? '', $prefix) === 0) {
                                        $requestPath = substr($requestPath, strlen($prefix));
                                    }
                                }

                                // normalize for safe comparisons
                                $reqNorm = rtrim(strtolower($requestPath ?? ''), '/');
                                $childNorm = rtrim(strtolower($childPathCheck), '/');
                                $phpSelfBase = strtolower(basename($_SERVER['PHP_SELF']));
                                $scriptBase = strtolower(basename($scriptName));
                                $childBase = strtolower(basename($childPathCheck));

                                // checks: basename match, exact path match, containment, or query-string reference
                                if (
                                    $phpSelfBase === $childBase
                                    || $scriptBase === $childBase
                                    || $reqNorm === $childNorm
                                    || stripos($requestPath, $childPathCheck) !== false
                                    || stripos($queryString, ltrim($childPathCheck, '/')) !== false
                                ) {
                                    $sectionActive = true; break;
                                }
                            }
                            $parentClasses = 'has-sub-menu' . ($sectionActive ? ' active open' : '');
                        ?>
                        <li class="<?php echo $parentClasses; ?>">
                            <a href="#"><i class="ti-list"></i> <span><?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?></span></a>
                            <ul class="side-header-sub-menu" <?php echo $sectionActive ? 'style="display:block;"' : ''; ?>>
                    <?php foreach ($visibleChildren as $child): ?>
                        <?php
                        $icon = $iconMap[$child['icon']] ?? 'ti-angle-right';
                        // dynamic title override for contingent-user (show Kod Universiti)
                        if (($child['url'] ?? '') === 'pages/contingent-user.php' && !empty($contingentCode)) {
                            $child['title'] = 'Kontinjen ' . $contingentCode;
                        }
                        $childHref = url($child['url']);
                        $childPath = parse_url($childHref, PHP_URL_PATH) ?: '/' . ltrim($child['url'], '/');

                                    // remove BASE_URL prefix if present
                                    if (defined('BASE_URL') && BASE_URL) {
                                        $prefix = rtrim(BASE_URL, '/');
                                        if ($prefix !== '' && stripos($childPath, $prefix) === 0) {
                                            $childPath = substr($childPath, strlen($prefix));
                                        }
                                    }

                                    $reqNorm = rtrim(strtolower($requestPath ?? ''), '/');
                                    $childNorm = rtrim(strtolower($childPath), '/');
                                    $phpSelfBase = strtolower(basename($_SERVER['PHP_SELF']));
                                    $scriptBase = strtolower(basename($scriptName));
                                    $childBase = strtolower(basename($childPath));

                                    $active = (
                                        $phpSelfBase === $childBase
                                        || $scriptBase === $childBase
                                        || $reqNorm === $childNorm
                                        || stripos($requestPath, $childPath) !== false
                                        || stripos($_SERVER['QUERY_STRING'] ?? '', ltrim($childPath, '/')) !== false
                                    ) ? 'active' : '';
                                    ?>
                                    <li class="<?php echo $active; ?>">
                                        <a href="<?php echo url($child['url']); ?>"><i class="<?php echo $icon; ?>"></i> <span><?php echo htmlspecialchars($child['title'], ENT_QUOTES, 'UTF-8'); ?></span></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ($displayNavItems as $item): ?>
                        <?php
                        $icon = $iconMap[$item['icon']] ?? 'ti-angle-right';
                        $active = $item['active'] ? 'active' : '';
                        ?>
                        <li class="<?php echo $active; ?>">
                            <a href="<?php echo url($item['url']); ?>">
                                <i class="<?php echo $icon; ?>"></i>
                                <span><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if ($isLoggedIn): ?>
                    <li class="logout-link">
                        <a class="confirm-logout" href="<?php echo url('auth/logout.php'); ?>">
                            <i class="ti-power-off"></i> <span>Log Keluar</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>
<!-- Side Header End -->
