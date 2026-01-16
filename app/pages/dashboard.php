<?php
/**
 * Dashboard Page (improved layout)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';

$page_title = 'Papan Pemuka';
// gather summary statistics
$summary = [
    'kontinjen' => 0,
    'sukan' => 0,
    'pasukan' => 0,
    'acara_selesai' => 0,
    'universiti' => 0,
    'venues' => 0,
    'atlet' => 0,
    'pengurus' => 0,
    'jurulatih' => 0,
];
$recent = ['contingents' => [], 'teams' => []];
try {
    $db = getDB();
    // counts
    $row = $db->query("SELECT COUNT(*) AS c FROM table_kontinjen WHERE deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
    $summary['kontinjen'] = (int)($row['c'] ?? 0);

    $row = $db->query("SELECT COUNT(*) AS c FROM table_sukan WHERE deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
    $summary['sukan'] = (int)($row['c'] ?? 0);

    $row = $db->query("SELECT COUNT(*) AS c FROM table_pasukan WHERE deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
    $summary['pasukan'] = (int)($row['c'] ?? 0);

    // acara_selesai placeholder — count events if table exists
    try {
        $r = $db->query("SELECT COUNT(*) AS c FROM table_event WHERE deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
        $summary['acara_selesai'] = (int)($r['c'] ?? 0);
    } catch (Exception $e) { /* ignore if no table_event */ }

    // table_ref_universiti does not have `deleted_at`; use `status` to count active records
    try {
        $row = $db->query("SELECT COUNT(*) AS c FROM table_ref_universiti WHERE status = 1")->fetch(PDO::FETCH_ASSOC);
        $summary['universiti'] = (int)($row['c'] ?? 0);
    } catch (Exception $e) {
        $summary['universiti'] = 0;
    }

    $row = $db->query("SELECT COUNT(*) AS c FROM table_ref_venues WHERE deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
    $summary['venues'] = (int)($row['c'] ?? 0);

    $row = $db->query("SELECT COUNT(*) AS c FROM table_pasukan_atlet WHERE deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
    $summary['atlet'] = (int)($row['c'] ?? 0);
    $row = $db->query("SELECT COUNT(*) AS c FROM table_pasukan_pengurus WHERE deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
    $summary['pengurus'] = (int)($row['c'] ?? 0);
    $row = $db->query("SELECT COUNT(*) AS c FROM table_pasukan_jurulatih WHERE deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
    $summary['jurulatih'] = (int)($row['c'] ?? 0);

    // recent contingents and recent teams
    $stmt = $db->prepare("SELECT id, kod_universiti, nama_pegawai_untuk_dihubungi, emel, created_at FROM table_kontinjen WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 6");
    $stmt->execute(); $recent['contingents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT p.id, p.nama_pasukan, p.kontinjen_id, k.kod_universiti, p.created_at FROM table_pasukan p LEFT JOIN table_kontinjen k ON p.kontinjen_id = k.id WHERE p.deleted_at IS NULL ORDER BY p.created_at DESC LIMIT 6");
    $stmt->execute(); $recent['teams'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // medal tally: return all universities from reference table and left-join aggregated counts (show zeros)
        // If main query returned no rows, build rows from reference table (show zeros) and then merge any actual counts
        if (empty($medalRows)) {
            // try to load all universities from reference table
            try {
                $refStmt = $db->query("SELECT kod_universiti, nama_pendek FROM table_ref_universiti WHERE status = 1 ORDER BY kod_universiti ASC");
                $refs = $refStmt ? $refStmt->fetchAll(PDO::FETCH_ASSOC) : [];
                if (!empty($refs)) {
                    // initialize medalRows with zeros
                    $medalRows = [];
                    foreach ($refs as $r) {
                        $medalRows[$r['kod_universiti']] = [
                            'nama_pendek' => $r['nama_pendek'] ?: $r['kod_universiti'],
                            'kod_universiti' => $r['kod_universiti'],
                            'emas' => 0,
                            'perak' => 0,
                            'gangsa' => 0,
                            'jumlah' => 0
                        ];
                    }
                    // fetch aggregated counts and merge
                    $countSql = "SELECT k.kod_universiti, " .
                                "SUM(CASE WHEN rr.tempat_pertama IS NOT NULL AND rr.tempat_pertama != '' THEN 1 ELSE 0 END) AS emas, " .
                                "SUM(CASE WHEN rr.tempat_kedua IS NOT NULL AND rr.tempat_kedua != '' THEN 1 ELSE 0 END) AS perak, " .
                                "SUM(CASE WHEN rr.tempat_ketiga IS NOT NULL AND rr.tempat_ketiga != '' THEN 1 ELSE 0 END) AS gangsa " .
                                "FROM table_results rr " .
                                "JOIN table_pasukan p ON (rr.tempat_pertama = p.id OR rr.tempat_kedua = p.id OR rr.tempat_ketiga = p.id) " .
                                "JOIN table_kontinjen k ON p.kontinjen_id = k.id " .
                                "GROUP BY k.kod_universiti";
                    $cntStmt = $db->query($countSql);
                    $counts = $cntStmt ? $cntStmt->fetchAll(PDO::FETCH_ASSOC) : [];
                    foreach ($counts as $c) {
                        $kod = $c['kod_universiti'];
                        if (!isset($medalRows[$kod])) {
                            $medalRows[$kod] = [
                                'nama_pendek' => $kod,
                                'kod_universiti' => $kod,
                                'emas' => (int)$c['emas'],
                                'perak' => (int)$c['perak'],
                                'gangsa' => (int)$c['gangsa'],
                                'jumlah' => (int)$c['emas'] + (int)$c['perak'] + (int)$c['gangsa']
                            ];
                        } else {
                            $medalRows[$kod]['emas'] = (int)$c['emas'];
                            $medalRows[$kod]['perak'] = (int)$c['perak'];
                            $medalRows[$kod]['gangsa'] = (int)$c['gangsa'];
                            $medalRows[$kod]['jumlah'] = (int)$c['emas'] + (int)$c['perak'] + (int)$c['gangsa'];
                        }
                    }
                    // convert associative by kod back to indexed array preserving order
                    $medalRows = array_values($medalRows);
                } else {
                    // fallback: try to use table_kontinjen kod_universiti list
                    $stmt = $db->query("SELECT COALESCE(nama_pendek, kod_universiti) AS nama_pendek, kod_universiti FROM table_kontinjen GROUP BY kod_universiti ORDER BY kod_universiti ASC");
                    $list = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
                    $medalRows = [];
                    foreach ($list as $l) {
                        $kod = $l['kod_universiti'] ?? null;
                        $medalRows[] = [
                            'nama_pendek' => $l['nama_pendek'] ?: $kod,
                            'kod_universiti' => $kod,
                            'emas' => 0,
                            'perak' => 0,
                            'gangsa' => 0,
                            'jumlah' => 0
                        ];
                    }
                }
            } catch (Exception $e) {
                // keep medalRows empty on error
            }
        }

    // If ref table is empty or query returned no rows, fallback to listing kod_universiti from table_kontinjen
    if (empty($medalRows) || empty($summary['universiti'])) {
        try {
            $sql2 = "SELECT COALESCE(k.nama_pendek, k.kod_universiti) AS nama_pendek, k.kod_universiti AS kod_universiti, " .
                    "COALESCE(m.emas,0) AS emas, COALESCE(m.perak,0) AS perak, COALESCE(m.gangsa,0) AS gangsa, " .
                    "(COALESCE(m.emas,0) + COALESCE(m.perak,0) + COALESCE(m.gangsa,0)) AS jumlah \n" .
                    "FROM (SELECT DISTINCT kod_universiti, nama_pendek FROM table_kontinjen WHERE deleted_at IS NULL) k \n" .
                    "LEFT JOIN (\n" .
                    "  SELECT k2.kod_universiti, \n" .
                    "    SUM(CASE WHEN rr.tempat_pertama IS NOT NULL AND rr.tempat_pertama != '' THEN 1 ELSE 0 END) AS emas,\n" .
                    "    SUM(CASE WHEN rr.tempat_kedua IS NOT NULL AND rr.tempat_kedua != '' THEN 1 ELSE 0 END) AS perak,\n" .
                    "    SUM(CASE WHEN rr.tempat_ketiga IS NOT NULL AND rr.tempat_ketiga != '' THEN 1 ELSE 0 END) AS gangsa\n" .
                    "  FROM table_results rr\n" .
                    "  JOIN table_pasukan p2 ON (rr.tempat_pertama = p2.id OR rr.tempat_kedua = p2.id OR rr.tempat_ketiga = p2.id)\n" .
                    "  JOIN table_kontinjen k2 ON p2.kontinjen_id = k2.id\n" .
                    "  GROUP BY k2.kod_universiti\n" .
                    ") m ON k.kod_universiti = m.kod_universiti\n" .
                    "ORDER BY emas DESC, perak DESC, gangsa DESC, k.kod_universiti ASC";
            $mStmt2 = $db->query($sql2);
            $medalRows = $mStmt2 ? $mStmt2->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            // keep medalRows as empty array
        }
    }

} catch (Exception $e) {
    error_log('[dashboard] summary fetch error: ' . $e->getMessage());
}

ob_start();
?>
<div class="w-100 px-3">
    <style>
        /* Neumorphic statistic cards */
        .neo-card{
            background: #f4f7fb;
            border: none;
            border-radius: 12px;
            box-shadow: 6px 6px 14px rgba(16,24,40,0.06), -6px -6px 14px rgba(255,255,255,0.8);
        }
        .neo-card .card-body{ background: transparent; }
        .neo-card .rounded-circle{ box-shadow: inset 2px 2px 6px rgba(0,0,0,0.06); }
        .neo-card .card-footer .text-muted.small{ font-size:0.85rem; }
        .stat-icon{ font-size:1.6rem; line-height:1; }
    </style>
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Papan Pemuka</h1>
            <p class="text-muted small mb-0">Ringkasan pantas sistem dan aktiviti terkini</p>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <button class="btn btn-outline-secondary">Laporan</button>
                <button class="btn btn-primary">Tindakan Cepat</button>
            </div>
        </div>
    </div>

    <div class="row g-3 align-items-stretch">
        <div class="col-lg-8 d-flex flex-column">
            <div class="row g-3 mb-3">
                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="card neo-card h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="zmdi zmdi-flag stat-icon text-primary mb-2"></i>
                            <div class="text-muted small">Kontinjen</div>
                            <div class="fs-4 fw-bold"><?php echo number_format($summary['kontinjen']); ?></div>
                        </div>
                        <div class="card-footer"><span class="text-muted small">Jumlah kontinjen: <?php echo number_format($summary['kontinjen']); ?></span></div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="card neo-card h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="zmdi zmdi-trophy stat-icon text-success mb-2"></i>
                            <div class="text-muted small">Sukan</div>
                            <div class="fs-4 fw-bold"><?php echo number_format($summary['sukan']); ?></div>
                        </div>
                        <div class="card-footer"><span class="text-muted small">Jumlah sukan: <?php echo number_format($summary['sukan']); ?></span></div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="card neo-card h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="zmdi zmdi-accounts stat-icon text-info mb-2"></i>
                            <div class="text-muted small">Pasukan</div>
                            <div class="fs-4 fw-bold"><?php echo number_format($summary['pasukan']); ?></div>
                        </div>
                        <div class="card-footer"><span class="text-muted small">Jumlah pasukan: <?php echo number_format($summary['pasukan']); ?></span></div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="card neo-card h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="zmdi zmdi-calendar stat-icon text-warning mb-2"></i>
                            <div class="text-muted small">Acara Selesai</div>
                            <div class="fs-4 fw-bold"><?php echo number_format($summary['acara_selesai']); ?></div>
                        </div>
                        <div class="card-footer"><span class="text-muted small">Acara selesai: <?php echo number_format($summary['acara_selesai']); ?></span></div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Aktiviti Terkini</strong>
                    <small class="text-muted">Rekod terbaru</small>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent['contingents'])): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recent['contingents'] as $rc): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($rc['kod_universiti'] . ' — ' . ($rc['nama_pegawai_untuk_dihubungi'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($rc['emel'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <div class="small text-muted"><?php echo htmlspecialchars($rc['created_at'] ?? ''); ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                        <div class="text-muted">Tiada aktiviti lagi — sistem kosong.</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="col-lg-4 d-flex">
            <div class="card shadow-sm w-100 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Kedudukan pasukan</strong>
                    <small class="text-muted">Ringkasan</small>
                </div>
                <div class="card-body p-2" style="overflow:auto;">
                    <?php if (!empty($medalRows)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Kontinjen</th>
                                        <th class="text-center"><span class="me-1">🥇</span> Emas</th>
                                        <th class="text-center"><span class="me-1">🥈</span> Perak</th>
                                        <th class="text-center" style="width:5rem;"><span class="me-1">🥉</span> Gangsa</th>
                                        <th class="text-center" style="width:5rem;">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $rank = 1; foreach ($medalRows as $mr): ?>
                                        <tr>
                                            <td class="align-middle small"><?php echo $rank++; ?></td>
                                            <td class="align-middle small"><?php echo htmlspecialchars($mr['nama_pendek'] ?? ($mr['kod_universiti'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-center small"><?php echo (int)($mr['emas'] ?? 0); ?></td>
                                            <td class="text-center small"><?php echo (int)($mr['perak'] ?? 0); ?></td>
                                            <td class="text-center small"><?php echo (int)($mr['gangsa'] ?? 0); ?></td>
                                            <td class="text-center small"><strong><?php echo (int)($mr['jumlah'] ?? 0); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-muted small p-3">Tiada data pingat.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>
