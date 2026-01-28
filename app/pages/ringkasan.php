<?php
/**
 * Ringkasan Laporan
 * Access: ADMIN, ORGANIZER, VIEWER
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/rbac.php';

Session::start();
$auth = getAuth();
$auth->requireAuth();
$rbac = getRBAC();
// Enforce page-specific access (VIEWER allowed via RBAC rules)
$rbac->requirePageAccess('pages/ringkasan.php');

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

$unis = [
    ['kod_universiti' => 'KMS',   'nama_universiti' => 'KMS'],
    ['kod_universiti' => 'APM',   'nama_universiti' => 'APM'],
    ['kod_universiti' => 'UIAM',  'nama_universiti' => 'UIAM'],
    ['kod_universiti' => 'UKM',   'nama_universiti' => 'UKM'],
    ['kod_universiti' => 'UM',    'nama_universiti' => 'UM'],
    ['kod_universiti' => 'UMK',   'nama_universiti' => 'UMK'],
    ['kod_universiti' => 'UMS',   'nama_universiti' => 'UMS'],
    ['kod_universiti' => 'UNIMAS','nama_universiti' => 'UNIMAS'],
    ['kod_universiti' => 'UMT',   'nama_universiti' => 'UMT'],
    ['kod_universiti' => 'UPNM',  'nama_universiti' => 'UPNM'],
    ['kod_universiti' => 'UPM',   'nama_universiti' => 'UPM'],
    ['kod_universiti' => 'USIM',  'nama_universiti' => 'USIM'],
    ['kod_universiti' => 'UniSZA','nama_universiti' => 'UniSZA'],
    ['kod_universiti' => 'UiTM',  'nama_universiti' => 'UiTM'],
    ['kod_universiti' => 'UUM',   'nama_universiti' => 'UUM'],
];
// Tab 2 data: per-sport, per-gender counts by university (fixed order)
$sportMatrix = [];
$colTotals = [];
// build a lookup map for university codes using uppercase keys to avoid case mismatches
$unis_map = [];
foreach ($unis as $u) {
    $k = strtoupper($u['kod_universiti']);
    $unis_map[$k] = $u['kod_universiti'];
    $colTotals[$k] = 0;
}
try {
    $db = getDB();

    // Include team-only entries (teams without athlete rows) so team sports appear
    // Also collect participant names per group using GROUP_CONCAT
    $sql = "WITH cleaned AS (
        SELECT DISTINCT
            REPLACE(pa.no_kad_pengenalan, '-', '') AS ic_clean,
            CASE WHEN RIGHT(REPLACE(pa.no_kad_pengenalan, '-', ''),1) REGEXP '[02468]' THEN 'WANITA' ELSE 'LELAKI' END AS gender,
            u.kod_universiti,
            u.nama_universiti,
            s.nama_sukan AS sukan,
            pa.nama AS peserta_nama
        FROM table_pasukan_atlet pa
        JOIN table_pasukan p ON p.id = pa.pasukan_id AND p.deleted_at IS NULL AND p.status = 1
        JOIN table_kontinjen k ON k.id = p.kontinjen_id AND k.deleted_at IS NULL AND k.status = 1
        JOIN table_ref_universiti u ON u.kod_universiti = k.kod_universiti AND u.status = 1
        LEFT JOIN table_sukan s ON s.id = p.sukan_id
        WHERE pa.deleted_at IS NULL
          AND pa.no_kad_pengenalan IS NOT NULL
          AND TRIM(pa.no_kad_pengenalan) <> ''
    ), teams_only AS (
        SELECT
            NULL AS ic_clean,
            'PASUKAN' AS gender,
            u.kod_universiti,
            u.nama_universiti,
            s.nama_sukan AS sukan,
            NULL AS peserta_nama
        FROM table_pasukan p
        LEFT JOIN table_pasukan_atlet pa ON pa.pasukan_id = p.id AND pa.deleted_at IS NULL
        JOIN table_kontinjen k ON k.id = p.kontinjen_id AND k.deleted_at IS NULL AND k.status = 1
        JOIN table_ref_universiti u ON u.kod_universiti = k.kod_universiti AND u.status = 1
        LEFT JOIN table_sukan s ON s.id = p.sukan_id
        WHERE p.deleted_at IS NULL
          AND pa.id IS NULL
          AND p.status = 1
    ), combined AS (
        SELECT * FROM cleaned
        UNION ALL
        SELECT * FROM teams_only
    )
    SELECT
        sukan,
        gender,
        kod_universiti,
        COUNT(*) AS cnt,
        GROUP_CONCAT(DISTINCT COALESCE(peserta_nama,'') SEPARATOR '|||') AS nama_list
    FROM combined
    GROUP BY sukan, gender, kod_universiti
    ORDER BY sukan, gender, kod_universiti";

    $stmt = $db->query($sql);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $namesMatrix = [];
    foreach ($res as $r) {
        // Normalize sport name: remove control/formatting characters, collapse whitespace
        $raw_sukan = (string)($r['sukan'] ?? '');
        $sukan = preg_replace('/[\x00-\x1F\x7F]+/u', '', $raw_sukan); // remove control chars
        $sukan = preg_replace('/\p{Cf}/u', '', $sukan); // remove format chars (zero-width etc)
        $sukan = preg_replace('/\s+/u', ' ', trim($sukan));
        if ($sukan === '') { $sukan = 'Tidak Berlabel'; }
        $gender = strtoupper($r['gender'] ?? 'LELAKI');
        $kod = strtoupper(trim((string)($r['kod_universiti'] ?? '')));
        $cnt = (int)($r['cnt'] ?? 0);
        $nama_list = $r['nama_list'] ?? '';
        $names = [];
        if ($nama_list !== '') {
            $parts = explode('|||', $nama_list);
            foreach ($parts as $p) {
                $pn = trim($p);
                if ($pn !== '') $names[] = $pn;
            }
        }
        if (!isset($sportMatrix[$sukan])) {
            $sportMatrix[$sukan] = ['LELAKI' => [], 'WANITA' => [], 'PASUKAN' => []];
        }
        $sportMatrix[$sukan][$gender][$kod] = $cnt;
        $namesMatrix[$sukan][$gender][$kod] = $names;
        if (isset($colTotals[$kod])) {
            $colTotals[$kod] += $cnt;
        } else {
            // if kod not in predefined list, still keep tally under its uppercase key
            $colTotals[$kod] = ($colTotals[$kod] ?? 0) + $cnt;
        }
    }
    ksort($sportMatrix);
} catch (Exception $e) {
    error_log('[ringkasan tab2] DB error: ' . $e->getMessage());
    $sportMatrix = [];
}
// Compute athlete-only and team-only column totals (keys are uppercase)
$colTotalsAthletes = [];
$colTotalsTeams = [];
foreach ($colTotals as $k => $v) { $colTotalsAthletes[$k] = 0; $colTotalsTeams[$k] = 0; }
foreach ($sportMatrix as $sname => $genders) {
    foreach (['LELAKI','WANITA'] as $g) {
        $counts = $genders[$g] ?? [];
        foreach ($counts as $kod => $c) {
            $kod = strtoupper($kod);
            $colTotalsAthletes[$kod] = ($colTotalsAthletes[$kod] ?? 0) + (int)$c;
        }
    }
    // teams
    $counts = $genders['PASUKAN'] ?? [];
    foreach ($counts as $kod => $c) {
        $kod = strtoupper($kod);
        $colTotalsTeams[$kod] = ($colTotalsTeams[$kod] ?? 0) + (int)$c;
    }
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

                <!-- Tab 2: Ringkasan Acara -->
                <div class="tab-pane fade" id="pane-acara" role="tabpanel" aria-labelledby="tab-acara">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:3%;">Bil</th>
                                    <th style="width:12%;">Acara</th>
                                    <th style="width:7%;">Jantina</th>
                                    <?php foreach ($unis as $uni): ?>
                                        <th style="width:5%;" class="text-end"><?php echo htmlspecialchars($uni['kod_universiti'] ?? $uni['nama_universiti'], ENT_QUOTES, 'UTF-8'); ?></th>
                                    <?php endforeach; ?>
                                    <th style="width:3%;" class="text-end">∑</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sportMatrix)): ?>
                                    <tr><td colspan="<?php echo 4 + count($unis); ?>" class="text-center py-4"><span class="badge badge-outline badge-info">Tiada</span></td></tr>
                                <?php else: ?>
                                    <?php $bilAcara = 1; ?>
                                    <?php foreach ($sportMatrix as $sukan => $genders): ?>
                                        <?php
                                            $genderOrder = ['LELAKI','WANITA'];
                                            $firstNonEmptyIndex = null;
                                            foreach ($genderOrder as $gi => $gg) {
                                                $tmp = $genders[$gg] ?? [];
                                                if (array_sum(array_values($tmp)) > 0) { $firstNonEmptyIndex = $gi; break; }
                                            }
                                        ?>
                                        <?php foreach ($genderOrder as $idxGender => $g): ?>
                                            <?php
                                                $counts = $genders[$g] ?? [];
                                                $hasData = array_sum(array_values($counts)) > 0;
                                                if (!$hasData) { continue; }
                                            ?>
                                            <tr>
                                                <td><?php echo $idxGender === $firstNonEmptyIndex ? $bilAcara : ''; ?></td>
                                                <td><?php echo $idxGender === $firstNonEmptyIndex ? htmlspecialchars($sukan, ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td><?php echo ($g === 'LELAKI') ? '<i class="cil-male me-1"></i>Lelaki' : '<i class="cil-child me-1"></i>Wanita'; ?></td>
                                                <?php $rowSum = 0; ?>
                                                <?php foreach ($unis as $uni): ?>
                                                    <?php
                                                        $kodKey = strtoupper($uni['kod_universiti']);
                                                        $val = (int)($counts[$kodKey] ?? $counts[$uni['kod_universiti']] ?? 0);
                                                        $rowSum += $val;
                                                        $cellNames = $namesMatrix[$sukan][$g][$kodKey] ?? [];
                                                        if (!empty($cellNames)) {
                                                            $escaped = array_map(function($n){ return htmlspecialchars($n, ENT_QUOTES, 'UTF-8'); }, $cellNames);
                                                            $namesHtml = '<div style="text-align:left">' . implode('<br>', $escaped) . '</div>';
                                                            $tooltipAttr = ' data-names-html="' . htmlspecialchars($namesHtml, ENT_QUOTES, 'UTF-8') . '" data-bs-toggle="names-tooltip" data-bs-html="true"';
                                                        } else {
                                                            $tooltipAttr = '';
                                                        }
                                                    ?>
                                                    <td class="text-end"<?php echo $tooltipAttr; ?>><?php echo $val === 0 ? '<span class="badge badge-outline badge-info">Tiada</span>' : number_format($val); ?></td>
                                                <?php endforeach; ?>
                                                <td class="text-end fw-bold"><?php echo number_format($rowSum); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php $bilAcara++; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($sportMatrix) && !empty($unis)): ?>
                                <?php
                                    // compute per-column totals for displayed genders
                                    $colTotalsDisplayed = [];
                                    foreach ($unis as $u) { $colTotalsDisplayed[strtoupper($u['kod_universiti'])] = 0; }
                                    foreach ($sportMatrix as $sname => $genders) {
                                        foreach (['LELAKI','WANITA'] as $g) {
                                            $counts = $genders[$g] ?? [];
                                            foreach ($counts as $kod => $c) {
                                                $kod = strtoupper($kod);
                                                $colTotalsDisplayed[$kod] = ($colTotalsDisplayed[$kod] ?? 0) + (int)$c;
                                            }
                                        }
                                    }
                                ?>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Jumlah</th>
                                    <?php foreach ($unis as $uni): ?>
                                        <?php $kodKey = strtoupper($uni['kod_universiti']); $val = (int)($colTotalsDisplayed[$kodKey] ?? 0); ?>
                                        <th class="text-end fw-bold"><?php echo number_format($val); ?></th>
                                    <?php endforeach; ?>
                                    <th></th>
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
        </div>
    </div>
</div>
<style>
/* Wider, left-aligned tooltips for participant name lists */
.tooltip .tooltip-inner {
    max-width: 640px; /* increase as needed */
    white-space: normal;
    text-align: left;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Initialize custom name tooltips (left-justified, multi-line)
    var els = document.querySelectorAll('[data-bs-toggle="names-tooltip"]');
    els.forEach(function(el){
        var html = el.getAttribute('data-names-html') || '';
        // Use Bootstrap Tooltip API
        try {
            var tip = new bootstrap.Tooltip(el, {title: html, html: true, sanitize: false, placement: 'top'});
        } catch (e) {
            console.error('Tooltip init failed', e);
        }
    });
});
</script>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
