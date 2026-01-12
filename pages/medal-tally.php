<?php
/**
 * Medal Tally Page
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Medal Tally';

ob_start();
?>
<div class="w-100 px-3">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">Medal Tally</h2>
            <p class="text-muted">Jadual pingat dan kedudukan kontinjen</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="cil cil-star text-warning" style="font-size: 3rem;"></i>
                    <h3 class="mt-3 mb-0">0</h3>
                    <p class="text-muted mb-0">Emas</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="cil cil-star text-secondary" style="font-size: 3rem;"></i>
                    <h3 class="mt-3 mb-0">0</h3>
                    <p class="text-muted mb-0">Perak</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="cil cil-star text-danger" style="font-size: 3rem;"></i>
                    <h3 class="mt-3 mb-0">0</h3>
                    <p class="text-muted mb-0">Gangsa</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Jadual Pingat Mengikut Kontinjen</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Kedudukan</th>
                                    <th scope="col">Kontinjen</th>
                                    <th scope="col" class="text-center">
                                        <i class="cil cil-star text-warning"></i> Emas
                                    </th>
                                    <th scope="col" class="text-center">
                                        <i class="cil cil-star text-secondary"></i> Perak
                                    </th>
                                    <th scope="col" class="text-center">
                                        <i class="cil cil-star text-danger"></i> Gangsa
                                    </th>
                                    <th scope="col" class="text-center"><strong>Jumlah</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="cil cil-star" style="font-size: 2rem;"></i>
                                        <p class="mt-2">Tiada data pingat</p>
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

