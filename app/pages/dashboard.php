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

    $row = $db->query("SELECT COUNT(*) AS c FROM table_ref_universiti WHERE deleted_at IS NULL")->fetch(PDO::FETCH_ASSOC);
    $summary['universiti'] = (int)($row['c'] ?? 0);

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

} catch (Exception $e) {
    error_log('[dashboard] summary fetch error: ' . $e->getMessage());
}

ob_start();
?>
<div class="w-100 px-3">
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

    <!-- KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <span class="bg-primary text-white rounded-circle p-3 d-inline-block">
                            <i class="cil cil-people" style="font-size:1.2rem;"></i>
                        </span>
                    </div>
                    <div>
                            <div class="text-muted small">Kontinjen</div>
                            <div class="fs-4 fw-bold"><?php echo number_format($summary['kontinjen']); ?></div>
                    </div>
                </div>
                <div class="card-footer small text-muted">Kemas kini: Tiada data</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <span class="bg-success text-white rounded-circle p-3 d-inline-block">
                            <i class="cil cil-gamepad" style="font-size:1.2rem;"></i>
                        </span>
                    </div>
                    <div>
                        <div class="text-muted small">Sukan</div>
                        <div class="fs-4 fw-bold"><?php echo number_format($summary['sukan']); ?></div>
                    </div>
                </div>
                <div class="card-footer small text-muted">Kemas kini: Tiada data</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <span class="bg-info text-white rounded-circle p-3 d-inline-block">
                            <i class="cil cil-user" style="font-size:1.2rem;"></i>
                        </span>
                    </div>
                    <div>
                        <div class="text-muted small">Pasukan</div>
                        <div class="fs-4 fw-bold"><?php echo number_format($summary['pasukan']); ?></div>
                    </div>
                </div>
                <div class="card-footer small text-muted">Kemas kini: Tiada data</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3">
                        <span class="bg-warning text-white rounded-circle p-3 d-inline-block">
                            <i class="cil cil-check-circle" style="font-size:1.2rem;"></i>
                        </span>
                    </div>
                    <div>
                        <div class="text-muted small">Acara Selesai</div>
                        <div class="fs-4 fw-bold"><?php echo number_format($summary['acara_selesai']); ?></div>
                    </div>
                </div>
                <div class="card-footer small text-muted">Kemas kini: Tiada data</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm">
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

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Akan Datang</strong>
                    <small class="text-muted">Jadual</small>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent['teams'])): ?>
                        <ul class="list-group mb-3">
                            <?php foreach ($recent['teams'] as $t): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="small">
                                        <div class="fw-semibold"><?php echo htmlspecialchars($t['nama_pasukan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="text-muted small"><?php echo htmlspecialchars($t['kod_universiti'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <span class="badge bg-secondary small"><?php echo htmlspecialchars($t['created_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="mb-2">Tiada acara akan datang.</p>
                    <?php endif; ?>
                    <div class="d-grid gap-2">
                        <a href="<?php echo url('pages/tournament.php'); ?>" class="btn btn-outline-primary btn-sm">Urus Kejohanan</a>
                        <a href="<?php echo url('pages/registration.php'); ?>" class="btn btn-outline-secondary btn-sm">Urus Pendaftaran</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>
