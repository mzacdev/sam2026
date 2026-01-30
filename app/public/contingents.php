<?php
/**
 * Public Contingents page - grid of contingent logos
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';

$page_title = 'Contingents';
$contingents = [];
try {
    $db = getDB();
    $sql = "
        SELECT DISTINCT
            k.kod_universiti,
            COALESCE(r.nama_pendek, r.nama_universiti, k.kod_universiti) AS nama
        FROM table_kontinjen k
        JOIN table_ref_universiti r ON r.kod_universiti = k.kod_universiti
        WHERE k.deleted_at IS NULL AND k.status = 1 AND r.status = 1
        ORDER BY nama ASC
    ";
    $stmt = $db->query($sql);
    $contingents = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Exception $e) {
    error_log('[public/contingents] ' . $e->getMessage());
    $contingents = [];
}

ob_start();
?>
<div class="container mt-3 mb-4">
    <div class="row align-items-center mb-3">
        <div class="col-md-8 col-12">
            <h2 class="mb-0">Contingents</h2>
            <div class="text-muted small">Browse participating contingents</div>
        </div>
    </div>

    <div class="contingent-grid">
        <?php if (empty($contingents)): ?>
            <div class="empty-state">No contingents available.</div>
        <?php else: ?>
            <?php foreach ($contingents as $c):
                $kod = htmlspecialchars($c['kod_universiti'] ?? '', ENT_QUOTES, 'UTF-8');
                $name = htmlspecialchars($c['nama'] ?? $kod, ENT_QUOTES, 'UTF-8');
                $logo = asset('img/logos/UA/' . $kod . '.svg');
                $link = url('public/medal-standings.php') . '?kod_universiti=' . urlencode($kod);
            ?>
                <a class="contingent-item" href="<?php echo $link; ?>" title="<?php echo $name; ?>">
                    <div class="contingent-logo-wrap">
                        <img src="<?php echo $logo; ?>" alt="<?php echo $name; ?>" onerror="this.onerror=null;this.style.opacity=.15;this.style.filter='grayscale(1)';" />
                    </div>
                    <div class="contingent-caption"><?php echo $name; ?></div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.contingent-grid{ display:grid; grid-template-columns: repeat(auto-fill, minmax(140px,1fr)); gap:16px; align-items:start }
.contingent-item{ display:flex; flex-direction:column; align-items:center; text-align:center; text-decoration:none; color:inherit; padding:12px; border-radius:8px; transition:transform .12s ease,box-shadow .12s ease; background:transparent }
.contingent-item:hover{ transform:translateY(-6px); box-shadow:0 12px 30px rgba(2,6,23,0.06); }
.contingent-logo-wrap{ width:100%; display:flex; align-items:center; justify-content:center; padding:8px 6px; min-height:72px }
.contingent-logo-wrap img{ max-height:64px; max-width:100%; object-fit:contain }
.contingent-caption{ margin-top:8px; font-weight:600; font-size:0.95rem }
.empty-state{ padding:40px; text-align:center; color:#6b7280 }
@media(max-width:576px){ .contingent-caption{ font-size:0.9rem } }
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout_public.php';
?>
