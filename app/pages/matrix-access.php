<?php
/**
 * Akses Matrix
 * Role: ADMIN only
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/rbac.php';

Session::start();
$auth = getAuth();
$auth->requireAuth();
$rbac = getRBAC();
$rbac->requireMinimumRole('ADMIN');

$page_title = 'Akses Matrix';

// Define roles (fixed order)
$roles = ['ADMIN', 'ORGANIZER', 'JUDGE', 'CONTINGENT', 'VIEWER'];

// Load menu access rules (prefer menu_access.json, fallback to rbac static)
$pageAccess = [];
$jsonPath = __DIR__ . '/../config/menu_access.json';
if (file_exists($jsonPath)) {
    $json = @file_get_contents($jsonPath);
    $data = json_decode($json, true);
    if (isset($data['pages']) && is_array($data['pages'])) {
        $pageAccess = $data['pages'];
    }
}
if (empty($pageAccess)) {
    // fallback: minimal set from RBAC defaults
    $pageAccess = [
        'index.php' => ['ADMIN','ORGANIZER','JUDGE','CONTINGENT','VIEWER'],
    ];
}

// Explicit menu ordering (ADMIN view)
$menus = [];
$add = function($url, $title, $group = '') use (&$menus, $pageAccess){
    $norm = ltrim($url, '/');
    $allowed = $pageAccess[$norm] ?? $pageAccess['pages/'.$norm] ?? [];
    $menus[] = [
        'url' => $url,
        'title' => $title,
        'group' => $group,
        'allowed' => $allowed,
    ];
};

// Dashboard (shown as group header)
$add('', 'Dashboard', 'Dashboard');

// Pengurusan group
$add('', 'Pengurusan', 'Pengurusan');
$add('pages/contingent.php', 'Kontinjen', 'Pengurusan');
$add('pages/sports.php', 'Sukan', 'Pengurusan');
$add('pages/pasukan.php', 'Pasukan', 'Pengurusan');
$add('pages/venues.php', 'Venue', 'Pengurusan');
$add('pages/results.php', 'Keputusan', 'Pengurusan');
$add('pages/contingent-user.php', 'Kontinjen - User', 'Pengurusan');

// Laporan group
$add('', 'Laporan', 'Laporan');
$add('pages/ringkasan.php', 'Ringkasan', 'Laporan');
$add('pages/results.php', 'Keputusan', 'Laporan');
$add('pages/contingent-admin.php', 'Kontinjen - Admin', 'Laporan');

// Tetapan group
$add('', 'Tetapan', 'Tetapan');
$add('pages/settings.php', 'General', 'Tetapan');
$add('pages/university.php', 'Universiti', 'Tetapan');
$add('pages/pengurusan-pengguna.php', 'Pengguna', 'Tetapan');
$add('pages/ic_audit.php', 'Audit MyKad', 'Tetapan');
$add('pages/matrix-access.php', 'Akses Matrix', 'Tetapan');

// Log Keluar (as its own group)
$add('', 'Log Keluar', 'Log Keluar');

// Normalize helper
$norm = function($p){
    $p = ltrim((string)$p, '/');
    return $p;
};

ob_start();
?>
<div class="w-100 px-3">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="mb-1">Akses Matrix</h2>
            <p class="text-muted mb-0">Matriks akses menu mengikut peranan sistem.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:3%;">Bil</th>
                            <th style="width:10%;">Group</th>
                            <th style="width:17%;">Menu</th>
                            <th style="width:20%;">Path</th>
                            <?php foreach ($roles as $r): ?>
                                <th class="text-center" style="width:10%;"><?php echo htmlspecialchars($r, ENT_QUOTES, 'UTF-8'); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($menus)): ?>
                            <tr><td colspan="<?php echo 4 + count($roles); ?>" class="text-center text-muted py-4">Tiada menu ditemui.</td></tr>
                        <?php else: ?>
                            <?php $bil = 1; ?>
                            <?php foreach ($menus as $menu): ?>
                                <?php
                                    $url = $menu['url'] ?? '';
                                    $title = $menu['title'] ?? $url;
                                    $group = $menu['group'] ?? '';
                                    $isGroup = !empty($menu['is_group']);
                                    $path = $url ? $norm($url) : '-';
                                    $allowed = $menu['allowed'] ?? [];
                                    $displayTitle = $title;
                                    $isHeaderRow = ($url === '');
                                ?>
                                <tr class="<?php echo $isHeaderRow ? 'table-warning' : ''; ?>">
                                    <td><?php echo $bil++; ?></td>
                                    <td><?php echo $group ? htmlspecialchars($group, ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                    <td><?php echo htmlspecialchars($displayTitle, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo $isHeaderRow ? '' : htmlspecialchars($path, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <?php foreach ($roles as $role): ?>
                                        <?php $ok = in_array($role, $allowed, true); ?>
                                        <td class="text-center">
                                            <?php if ($isHeaderRow): ?>
                                                &nbsp;
                                            <?php elseif ($ok): ?>
                                                <span class="badge bg-success">Dibenarkan</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Tiada Akses</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
