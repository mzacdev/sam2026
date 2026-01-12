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

$displayNavItems = $nav_items;
if ($rbac) {
    $displayNavItems = [];
    foreach ($nav_items as $item) {
        if ($rbac->isNavItemVisible($item['url'])) {
            $displayNavItems[] = $item;
        }
    }
}
?>
<!-- Side Header Start -->
<div class="side-header show">
    <button class="side-header-close"><i class="zmdi zmdi-close"></i></button>
    <div class="side-header-inner custom-scroll">
        <nav class="side-header-menu" id="side-header-menu">
            <ul>
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
            </ul>
        </nav>
    </div>
</div>
<!-- Side Header End -->
