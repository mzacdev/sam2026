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
    $row = $db->query("SELECT COUNT(*) AS c FROM table_kontinjen k JOIN table_ref_universiti r ON k.kod_universiti = r.kod_universiti WHERE k.deleted_at IS NULL AND r.status = 1")->fetch(PDO::FETCH_ASSOC);
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

    // Count distinct athletes (MyKad) only from active universities; ignore deleted/non-athlete roles
    $sqlAtlet = "
        SELECT COUNT(DISTINCT pa.no_kad_pengenalan) AS c
        FROM table_pasukan_atlet pa
        INNER JOIN table_pasukan p ON pa.pasukan_id = p.id AND p.deleted_at IS NULL AND p.status = 1
        INNER JOIN table_kontinjen k ON p.kontinjen_id = k.id AND k.deleted_at IS NULL AND k.status = 1
        INNER JOIN table_ref_universiti u ON k.kod_universiti = u.kod_universiti AND u.status = 1
        WHERE pa.deleted_at IS NULL
          AND pa.no_kad_pengenalan IS NOT NULL
          AND pa.no_kad_pengenalan <> ''
    ";
    $row = $db->query($sqlAtlet)->fetch(PDO::FETCH_ASSOC);
    $summary['atlet'] = (int)($row['c'] ?? 0);
    $row = $db->query("SELECT COUNT(*) AS c FROM table_pasukan_pengurus WHERE deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
    $summary['pengurus'] = (int)($row['c'] ?? 0);
    $row = $db->query("SELECT COUNT(*) AS c FROM table_pasukan_jurulatih WHERE deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
    $summary['jurulatih'] = (int)($row['c'] ?? 0);

    // recent contingents and recent teams
    $stmt = $db->prepare("SELECT k.id, k.kod_universiti, k.nama_pegawai_untuk_dihubungi, k.emel, k.created_at FROM table_kontinjen k JOIN table_ref_universiti r ON k.kod_universiti = r.kod_universiti WHERE k.deleted_at IS NULL AND r.status = 1 ORDER BY k.created_at DESC LIMIT 6");
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
                                "JOIN table_ref_universiti r ON k.kod_universiti = r.kod_universiti " .
                                "WHERE r.status = 1 " .
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
                    $stmt = $db->query("SELECT COALESCE(r.nama_pendek, k.kod_universiti) AS nama_pendek, k.kod_universiti FROM table_kontinjen k JOIN table_ref_universiti r ON k.kod_universiti = r.kod_universiti WHERE r.status = 1 GROUP BY k.kod_universiti ORDER BY k.kod_universiti ASC");
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
                    "FROM (SELECT DISTINCT k.kod_universiti, COALESCE(r.nama_pendek,k.kod_universiti) AS nama_pendek FROM table_kontinjen k JOIN table_ref_universiti r ON k.kod_universiti = r.kod_universiti WHERE k.deleted_at IS NULL AND r.status = 1) k \n" .
                    "LEFT JOIN (\n" .
                    "  SELECT k2.kod_universiti, \n" .
                    "    SUM(CASE WHEN rr.tempat_pertama IS NOT NULL AND rr.tempat_pertama != '' THEN 1 ELSE 0 END) AS emas,\n" .
                    "    SUM(CASE WHEN rr.tempat_kedua IS NOT NULL AND rr.tempat_kedua != '' THEN 1 ELSE 0 END) AS perak,\n" .
                    "    SUM(CASE WHEN rr.tempat_ketiga IS NOT NULL AND rr.tempat_ketiga != '' THEN 1 ELSE 0 END) AS gangsa\n" .
                    "  FROM table_results rr\n" .
                    "  JOIN table_pasukan p2 ON (rr.tempat_pertama = p2.id OR rr.tempat_kedua = p2.id OR rr.tempat_ketiga = p2.id)\n" .
                    "  JOIN table_kontinjen k2 ON p2.kontinjen_id = k2.id\n" .
                    "  JOIN table_ref_universiti r2 ON k2.kod_universiti = r2.kod_universiti\n" .
                    "  WHERE r2.status = 1\n" .
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
        /* Compact ranking table styles (reduced padding to avoid scrolling) */
        .medal-table-card { border-radius:8px; overflow:hidden; }
        .medal-table-card .card-header { 
            background: linear-gradient(180deg, #ffffff 0%, #f6f9ff 100%);
            border-bottom:1px solid rgba(14, 21, 47, 0.06);
            padding:.36rem .6rem;
            box-shadow: 0 6px 18px rgba(14,21,47,0.04);
            border-top-left-radius:8px;
            border-top-right-radius:8px;
        }
        /* Make the whole ranking box follow the neo-card appearance */
        .medal-table-card.neo-card { background: #f4f7fb; box-shadow: 6px 6px 14px rgba(16,24,40,0.06), -6px -6px 14px rgba(255,255,255,0.8); border: none; }
        .medal-table { width:100%; border-collapse:separate; border-spacing:0; }
        .medal-table thead th { background: #fbfdff; border-bottom: 1px solid #eef2f7; color:#172554; font-weight:600; font-size:0.82rem; padding:.35rem .45rem; }
        .medal-table tbody tr { transition: background .08s ease; }
        .medal-table tbody tr:hover { background: #fbfdff; }
        /* Subtle zebra and highlighted backgrounds for ranking rows */
        .medal-table tbody tr:nth-child(odd) { background: rgba(15,23,42,0.02); }
        .medal-table tbody tr.top-1 { background: linear-gradient(90deg, #fff7e6 0%, #fff3d1 100%); }
        .medal-table tbody tr.top-2 { background: linear-gradient(90deg, #f6f9ff 0%, #eef6ff 100%); }
        .medal-table tbody tr.top-3 { background: linear-gradient(90deg, #fff7f0 0%, #fff2ea 100%); }
        .medal-table tbody tr.top-1 .medal-name, .medal-table tbody tr.top-1 .medal-count { color:#7a4300; }
        .medal-table tbody tr.top-2 .medal-name, .medal-table tbody tr.top-2 .medal-count { color:#0b5ed7; }
        .medal-table tbody tr.top-3 .medal-name, .medal-table tbody tr.top-3 .medal-count { color:#8b5a2b; }
        .medal-table td, .medal-table th { padding: .28rem .4rem; vertical-align: middle; font-size: .82rem; }
        .medal-rank { font-weight:700; color:#0b5ed7; font-size:.9rem; }
        .medal-name { font-weight:600; color:#0f1724; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:11rem; }
        .medal-count { font-weight:700; color:#0b5ed7; }
        .medal-badge { display:inline-block; min-width:1.6rem; padding:.15rem .35rem; border-radius:.25rem; text-align:center; background:#f1f5f9; color:#0b5ed7; font-weight:700; font-size:.82rem; }
        .medal-table-small { font-size:.78rem; color:#6b7280; }
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
                        <div class="card-footer text-center"><span class="small text-muted"><i class="fa fa-flag me-1" aria-hidden="true"></i>Jumlah kontinjen</span></div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="card neo-card h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="fa fa-trophy stat-icon text-success mb-2"></i>
                            <div class="text-muted small">Sukan</div>
                            <div class="fs-4 fw-bold"><?php echo number_format($summary['sukan']); ?></div>
                        </div>
                        <div class="card-footer text-center"><span class="small text-muted"><i class="fa fa-futbol-o me-1" aria-hidden="true"></i>Jumlah sukan</span></div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="card neo-card h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="zmdi zmdi-accounts stat-icon text-info mb-2"></i>
                            <div class="text-muted small">Pasukan</div>
                            <div class="fs-4 fw-bold"><?php echo number_format($summary['pasukan']); ?></div>
                        </div>
                        <div class="card-footer text-center"><span class="small text-muted"><i class="fa fa-users me-1" aria-hidden="true"></i>Jumlah pasukan</span></div>
                    </div>
                </div>

                <div class="col-6 col-sm-6 col-lg-3">
                    <div class="card neo-card h-100">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                            <i class="zmdi zmdi-run stat-icon text-warning mb-2"></i>
                            <div class="text-muted small">Jumlah Atlet</div>
                            <div class="fs-4 fw-bold"><?php echo number_format($summary['atlet']); ?></div>
                        </div>
                        <div class="card-footer text-center"><span class="small text-muted"><i class="fa fa-id-card me-1" aria-hidden="true"></i>Jumlah atlet</span></div>
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
                <div class="card w-100 h-100 medal-table-card neo-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Kedudukan Kontinjen</strong>
                    <small class="text-muted">Ringkasan</small>
                </div>
                    <div class="card-body p-1">
                    <?php if (!empty($medalRows)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 medal-table">
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
                                         <?php $r = $rank++; $rowClass = ($r==1? 'top-1' : ($r==2? 'top-2' : ($r==3? 'top-3' : ''))); ?>
                                         <tr class="<?php echo $rowClass; ?>">
                                            <td class="align-middle"><span class="medal-rank"><?php echo $r; ?></span></td>
                                            <td class="align-middle"><div class="medal-name" title="<?php echo htmlspecialchars($mr['nama_pendek'] ?? ($mr['kod_universiti'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($mr['nama_pendek'] ?? ($mr['kod_universiti'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></div></td>
                                            <td class="text-center"><span class="medal-badge"><?php echo (int)($mr['emas'] ?? 0); ?></span></td>
                                            <td class="text-center"><span class="medal-badge"><?php echo (int)($mr['perak'] ?? 0); ?></span></td>
                                            <td class="text-center"><span class="medal-badge"><?php echo (int)($mr['gangsa'] ?? 0); ?></span></td>
                                            <td class="text-center"><span class="medal-count"><?php echo (int)($mr['jumlah'] ?? 0); ?></span></td>
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
