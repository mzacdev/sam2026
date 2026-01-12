<?php
/**
 * Reports Page
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Laporan';

ob_start();
?>
<div class="w-100 px-3">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">Laporan</h2>
            <p class="text-muted">Lihat dan jana laporan</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Penapis Laporan</strong>
                </div>
                <div class="card-body">
                    <form class="row g-3">
                        <div class="col-md-3">
                            <label for="reportType" class="form-label">Jenis Laporan</label>
                            <select class="form-select" id="reportType">
                                <option selected>Pilih jenis laporan...</option>
                                <option>Laporan Kontinjen</option>
                                <option>Laporan Atlet</option>
                                <option>Laporan Keputusan</option>
                                <option>Laporan Medal Tally</option>
                                <option>Laporan Venue</option>
                                <option>Laporan Acara</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="startDate" class="form-label">Tarikh Mula</label>
                            <input type="date" class="form-control" id="startDate">
                        </div>
                        <div class="col-md-3">
                            <label for="endDate" class="form-label">Tarikh Akhir</label>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary w-100">Jana Laporan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Data Laporan</strong>
                </div>
                <div class="card-body">
                    <p class="text-muted">Pilih penapis di atas dan klik "Jana Laporan" untuk melihat data.</p>
                    <div class="text-center py-5">
                        <i class="icon icon-4xl text-muted cil cil-chart" style="font-size: 4rem;"></i>
                        <p class="mt-3">Tiada data laporan tersedia</p>
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

