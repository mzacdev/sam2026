<?php
/**
 * Athletes Management Page
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Atlet';

ob_start();
?>
<div class="w-100 px-3">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">Atlet</h2>
                    <p class="text-muted">Urus pendaftaran atlet</p>
                </div>
                <button class="btn btn-primary">
                    <i class="cil cil-plus me-1"></i> Daftar Atlet Baru
                </button>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <select class="form-select" id="filterContingent">
                <option value="">Semua Kontinjen</option>
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-select" id="filterSport">
                <option value="">Semua Sukan</option>
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Cari atlet...">
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Senarai Atlet</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nama</th>
                                    <th scope="col">No. Kad Pengenalan</th>
                                    <th scope="col">Kontinjen</th>
                                    <th scope="col">Sukan</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="cil cil-user" style="font-size: 2rem;"></i>
                                        <p class="mt-2">Tiada atlet didaftarkan</p>
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

