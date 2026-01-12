<?php
/**
 * Sports Management Page
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Sukan';

ob_start();
?>
<div class="w-100 px-3">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">Sukan</h2>
                    <p class="text-muted">Urus sukan dan acara pertandingan</p>
                </div>
                <button class="btn btn-primary">
                    <i class="cil cil-plus me-1"></i> Daftar Sukan Baru
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Senarai Sukan</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nama Sukan</th>
                                    <th scope="col">Kategori</th>
                                    <th scope="col">Jumlah Acara</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="cil cil-gamepad" style="font-size: 2rem;"></i>
                                        <p class="mt-2">Tiada sukan didaftarkan</p>
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

