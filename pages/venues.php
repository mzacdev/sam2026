<?php
/**
 * Venues Management Page
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Venue';

ob_start();
?>
<div class="w-100 px-3">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">Venue</h2>
                    <p class="text-muted">Urus lokasi dan tempat pertandingan</p>
                </div>
                <button class="btn btn-primary">
                    <i class="cil cil-plus me-1"></i> Daftar Venue Baru
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Senarai Venue</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nama Venue</th>
                                    <th scope="col">Lokasi</th>
                                    <th scope="col">Kapasiti</th>
                                    <th scope="col">Sukan</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="cil cil-map" style="font-size: 2rem;"></i>
                                        <p class="mt-2">Tiada venue didaftarkan</p>
                                    </td>
                                </tr>
                            </tbody>
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
?>

