<?php
/**
 * Dashboard Page (improved layout)
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Papan Pemuka';
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
                        <div class="fs-4 fw-bold">0</div>
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
                        <div class="fs-4 fw-bold">0</div>
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
                        <div class="fs-4 fw-bold">0</div>
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
                        <div class="fs-4 fw-bold">0</div>
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
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Tiada aktiviti lagi — sistem kosong.</li>
                    </ul>
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
                    <p class="mb-2">Tiada acara akan datang.</p>
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
