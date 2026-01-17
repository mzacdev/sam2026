<?php
/**
 * Ringkasan Laporan
 * Access: ADMIN, ORGANIZER only
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/rbac.php';

Session::start();
$auth = getAuth();
$auth->requireAuth();
$rbac = getRBAC();
// Require ORGANIZER minimum (ADMIN allowed by hierarchy)
$rbac->requireMinimumRole('ORGANIZER');

$page_title = 'Ringkasan Laporan';

// Fetch summary of athletes per university + gender
$rows = [];
try {
    $db = getDB();
    $sql = "WITH cleaned AS (
        SELECT DISTINCT
            REPLACE(pa.no_kad_pengenalan, '-', '') AS ic_clean,
            CASE WHEN MOD(CAST(RIGHT(REPLACE(pa.no_kad_pengenalan, '-', ''), 1) AS UNSIGNED), 2) = 0 THEN 'WANITA' ELSE 'LELAKI' END AS gender,
            u.kod_universiti,
            u.nama_universiti
        FROM table_pasukan_atlet pa
        JOIN table_pasukan p ON p.id = pa.pasukan_id AND p.deleted_at IS NULL AND p.status = 1
        JOIN table_kontinjen k ON k.id = p.kontinjen_id AND k.deleted_at IS NULL AND k.status = 1
        JOIN table_ref_universiti u ON u.kod_universiti = k.kod_universiti AND u.status = 1
        WHERE pa.deleted_at IS NULL
          AND pa.no_kad_pengenalan IS NOT NULL
          AND TRIM(pa.no_kad_pengenalan) <> ''
    )
    SELECT
        kod_universiti,
        nama_universiti,
        SUM(gender = 'LELAKI') AS lelaki,
        SUM(gender = 'WANITA') AS wanita
    FROM cleaned
    GROUP BY kod_universiti, nama_universiti
    ORDER BY nama_universiti";

    $stmt = $db->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('[ringkasan] DB error: ' . $e->getMessage());
    $rows = [];
}

// Compute totals
$totals = ['LELAKI' => 0, 'WANITA' => 0];
foreach ($rows as $r) {
    $totals['LELAKI'] += (int)($r['lelaki'] ?? 0);
    $totals['WANITA'] += (int)($r['wanita'] ?? 0);
}

// Tab 2 data: per-sport, per-gender counts by university
$unis = [];
$sportMatrix = [];
$colTotals = [];
try {
    $db = getDB();
    // Active universities list
    $stmt = $db->query("SELECT kod_universiti, nama_universiti FROM table_ref_universiti WHERE status = 1 ORDER BY nama_universiti");
    $unis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($unis as $u) {
        $colTotals[$u['kod_universiti']] = 0;
    }

    $sql = "WITH cleaned AS (
        SELECT DISTINCT
            REPLACE(pa.no_kad_pengenalan, '-', '') AS ic_clean,
            CASE WHEN MOD(CAST(RIGHT(REPLACE(pa.no_kad_pengenalan, '-', ''), 1) AS UNSIGNED), 2) = 0 THEN 'WANITA' ELSE 'LELAKI' END AS gender,
            u.kod_universiti,
            u.nama_universiti,
            s.nama_sukan AS sukan
        FROM table_pasukan_atlet pa
        JOIN table_pasukan p ON p.id = pa.pasukan_id AND p.deleted_at IS NULL AND p.status = 1
        JOIN table_kontinjen k ON k.id = p.kontinjen_id AND k.deleted_at IS NULL AND k.status = 1
        JOIN table_ref_universiti u ON u.kod_universiti = k.kod_universiti AND u.status = 1
        LEFT JOIN table_sukan s ON s.id = p.sukan_id
        WHERE pa.deleted_at IS NULL
          AND pa.no_kad_pengenalan IS NOT NULL
          AND TRIM(pa.no_kad_pengenalan) <> ''
    )
    SELECT
        sukan,
        gender,
        kod_universiti,
        COUNT(*) AS cnt
    FROM cleaned
    GROUP BY sukan, gender, kod_universiti
    ORDER BY sukan, gender, kod_universiti";

    $stmt = $db->query($sql);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($res as $r) {
        $sukan = $r['sukan'] ?: 'Tidak Berlabel';
        $gender = strtoupper($r['gender'] ?? 'LELAKI');
        $kod = $r['kod_universiti'];
        $cnt = (int)($r['cnt'] ?? 0);
        if (!isset($sportMatrix[$sukan])) {
            $sportMatrix[$sukan] = ['LELAKI' => [], 'WANITA' => []];
        }
        $sportMatrix[$sukan][$gender][$kod] = $cnt;
        if (isset($colTotals[$kod])) {
            $colTotals[$kod] += $cnt;
        } else {
            $colTotals[$kod] = $cnt;
        }
    }
    ksort($sportMatrix);
} catch (Exception $e) {
    error_log('[ringkasan tab2] DB error: ' . $e->getMessage());
    $sportMatrix = [];
}

ob_start();
?>
<div class="container-fluid px-3">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="mb-1">Ringkasan Laporan</h2>
            <p class="text-muted mb-0">Gambaran pantas atlet mengikut universiti dan jantina.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Tabs: Pill with Icon style (from light/elements-tabs.html) -->
            <ul class="nav nav-pills mb-3" id="pillTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-atlet" data-bs-toggle="pill" data-bs-target="#pane-atlet" type="button" role="tab" aria-controls="pane-atlet" aria-selected="true">
                        <i class="cil-people me-1"></i> Ringkasan Atlet
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-acara" data-bs-toggle="pill" data-bs-target="#pane-acara" type="button" role="tab" aria-controls="pane-acara" aria-selected="false">
                        <i class="cil-list-rich me-1"></i> Ringkasan Acara
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="pillTabContent">
                <!-- Tab 1: Ringkasan Atlet -->
                <div class="tab-pane fade show active" id="pane-atlet" role="tabpanel" aria-labelledby="tab-atlet">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:5%;">Bil</th>
                                    <th style="width:65%;">Universiti</th>
                                    <th style="width:10%;" class="text-end"><i class="cil-male me-1"></i>Lelaki</th>
                                    <th style="width:10%;" class="text-end"><i class="cil-child me-1"></i>Wanita</th>
                                    <th style="width:10%;" class="text-end"><i class="cil-calculator me-1"></i>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($rows)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">Tiada data untuk dipaparkan.</td></tr>
                                <?php else: ?>
                                    <?php $bil = 1; ?>
                                    <?php foreach ($rows as $row):
                                        $lelaki = (int)($row['lelaki'] ?? 0);
                                        $wanita = (int)($row['wanita'] ?? 0);
                                        $jumlah = $lelaki + $wanita;
                                    ?>
                                        <tr>
                                            <td><?php echo $bil++; ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_universiti'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-end text-primary fw-semibold"><?php echo number_format($lelaki); ?></td>
                                            <td class="text-end text-danger fw-semibold"><?php echo number_format($wanita); ?></td>
                                            <td class="text-end fw-bold"><?php echo number_format($jumlah); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th></th>
                                    <th class="text-end">Jumlah Keseluruhan</th>
                                    <th class="text-end text-primary fw-semibold"><?php echo number_format($totals['LELAKI']); ?></th>
                                    <th class="text-end text-danger fw-semibold"><?php echo number_format($totals['WANITA']); ?></th>
                                    <th class="text-end fw-bold"><?php echo number_format($totals['LELAKI'] + $totals['WANITA']); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Placeholder -->
                <div class="tab-pane fade" id="pane-acara" role="tabpanel" aria-labelledby="tab-acara">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:18%;">Acara</th>
                                    <th style="width:7%;">Jantina</th>
                                    <?php foreach ($unis as $uni): ?>
                                        <th class="text-end"><?php echo htmlspecialchars($uni['kod_universiti'] ?? $uni['nama_universiti'], ENT_QUOTES, 'UTF-8'); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sportMatrix)): ?>
                                    <tr><td colspan="<?php echo 2 + count($unis); ?>" class="text-center text-muted py-4">Tiada data untuk dipaparkan.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($sportMatrix as $sukan => $genders): ?>
                                        <?php foreach (['LELAKI','WANITA'] as $idxGender => $g): ?>
                                            <?php
                                                $counts = $genders[$g] ?? [];
                                                $hasData = array_sum(array_values($counts)) > 0;
                                                if (!$hasData) { continue; }
                                            ?>
                                            <tr>
                                                <td><?php echo $idxGender === 0 ? htmlspecialchars($sukan, ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td><?php echo ($g === 'LELAKI') ? '<i class="cil-male me-1"></i>Lelaki' : '<i class="cil-child me-1"></i>Wanita'; ?></td>
                                                <?php foreach ($unis as $uni): ?>
                                                    <?php $val = (int)($counts[$uni['kod_universiti']] ?? 0); ?>
                                                    <td class="text-end"><?php echo $val === 0 ? '&ndash;' : number_format($val); ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($sportMatrix) && !empty($unis)): ?>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-end">Jumlah</th>
                                    <?php foreach ($unis as $uni): ?>
                                        <?php $val = (int)($colTotals[$uni['kod_universiti']] ?? 0); ?>
                                        <th class="text-end fw-bold"><?php echo number_format($val); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
