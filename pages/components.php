<?php
/**
 * Components Page
 * Showcase CoreUI components
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Komponen';

ob_start();
?>
<div class="w-100 px-3">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">Komponen</h2>
            <p class="text-muted">Contoh komponen CoreUI</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Butang</strong>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary me-2">Utama</button>
                    <button class="btn btn-secondary me-2">Sekunder</button>
                    <button class="btn btn-success me-2">Berjaya</button>
                    <button class="btn btn-danger me-2">Bahaya</button>
                    <button class="btn btn-warning me-2">Amaran</button>
                    <button class="btn btn-info me-2">Maklumat</button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Amaran</strong>
                </div>
                <div class="card-body">
                    <div class="alert alert-primary" role="alert">Amaran utama</div>
                    <div class="alert alert-success" role="alert">Amaran berjaya</div>
                    <div class="alert alert-warning" role="alert">Amaran amaran</div>
                    <div class="alert alert-danger" role="alert">Amaran bahaya</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Elemen Borang</strong>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label for="exampleInput" class="form-label">Input Teks</label>
                            <input type="text" class="form-control" id="exampleInput" placeholder="Masukkan teks">
                        </div>
                        <div class="mb-3">
                            <label for="exampleSelect" class="form-label">Pilih</label>
                            <select class="form-select" id="exampleSelect">
                                <option>Pilihan 1</option>
                                <option>Pilihan 2</option>
                                <option>Pilihan 3</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="exampleCheck">
                                <label class="form-check-label" for="exampleCheck">Kotak semak</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Hantar</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Lencana & Pil</strong>
                </div>
                <div class="card-body">
                    <h5>Lencana</h5>
                    <span class="badge bg-primary me-2">Utama</span>
                    <span class="badge bg-secondary me-2">Sekunder</span>
                    <span class="badge bg-success me-2">Berjaya</span>
                    <span class="badge bg-danger me-2">Bahaya</span>
                    <span class="badge bg-warning me-2">Amaran</span>
                    
                    <h5 class="mt-4">Pil</h5>
                    <span class="badge rounded-pill bg-primary me-2">Utama</span>
                    <span class="badge rounded-pill bg-secondary me-2">Sekunder</span>
                    <span class="badge rounded-pill bg-success me-2">Berjaya</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>

