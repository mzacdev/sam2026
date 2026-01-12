<?php
/**
 * Base Layout Template (Light theme)
 */

// Initialize auth and RBAC for all pages BEFORE any output
if (!defined('SKIP_AUTH_CHECK')) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/auth.php';
    require_once __DIR__ . '/../config/rbac.php';

    if (session_status() === PHP_SESSION_NONE) {
        Session::start();
    }
    $auth = getAuth();
    $rbac = getRBAC();

    $currentPage = $_SERVER['PHP_SELF'] ?? 'index.php';
    $scriptPath = str_replace('\\', '/', $currentPage);
    $basePath = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] . BASE_URL);
    $relativePath = str_replace($basePath, '', $scriptPath);
    $relativePath = ltrim($relativePath, '/');

    if (empty($relativePath) || $relativePath === 'index.php') {
        $relativePath = 'index.php';
    }

    $rbac->requirePageAccess($relativePath);
}

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';
?>

<!-- Content Body Start -->
<div class="content-body">
    <div class="container-fluid">
        <?php echo $content; ?>
    </div>
</div>
<!-- Content Body End -->

<?php require_once __DIR__ . '/footer.php'; ?>
