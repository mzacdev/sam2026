<?php
/**
 * Sports Management Page
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Sukan';

ob_start();
?>
<div class="w-100 px-3">
    <!-- Hero -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light border-0 shadow-sm overflow-hidden">
                <div class="card-body py-4 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                    <div>
                        <h2 class="mb-1">Sukan</h2>
                        <p class="text-muted mb-0">Urus sukan dan acara pertandingan — ringkasan dan tindakan pantas</p>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <div class="d-none d-md-flex">
                            <div class="me-3 text-center">
                                <div class="h5 mb-0">0</div>
                                <div class="small text-muted">Sukan</div>
                            </div>
                            <div class="me-3 text-center">
                                <div class="h5 mb-0">0</div>
                                <div class="small text-muted">Acara</div>
                            </div>
                            <div class="me-3 text-center">
                                <div class="h5 mb-0">0</div>
                                <div class="small text-muted">Kategori</div>
                            </div>
                        </div>

                        <div class="btn-group">
                            <button class="btn btn-outline-secondary">Laporan</button>
                            <button class="btn btn-primary" onclick="showAddSport()">
                                <i class="cil cil-plus me-1"></i> Daftar Sukan Baru
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sports List -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Senarai Sukan</strong>
                        <div class="small text-muted">Urus semua sukan dan kategori</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="min-width:220px;">
                            <span class="input-group-text"><i class="cil cil-magnifying-glass"></i></span>
                            <input type="search" class="form-control" id="sportsSearch" placeholder="Cari nama atau kategori...">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width:70px;">#</th>
                                    <th scope="col">Nama Sukan</th>
                                    <th scope="col">Kategori</th>
                                    <th scope="col" style="width:140px;">Jumlah Acara</th>
                                    <th scope="col" style="width:120px;">Status</th>
                                    <th scope="col" style="width:160px;">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="sportsTableBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="cil cil-gamepad" style="font-size: 2rem;"></i>
                                        <p class="mt-2">Tiada sukan didaftarkan — klik "Daftar Sukan Baru" untuk mula menambah.</p>
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

<!-- Add Sport Modal -->
<div class="modal fade" id="addSportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Sukan Baru</h5>
                <button type="button" class="btn-close" aria-label="Close" onclick="closeAddSportModal()"></button>
            </div>
            <div class="modal-body">
                <form id="sportForm">
                    <div class="mb-3">
                        <label for="sportName" class="form-label">Nama Sukan</label>
                        <input type="text" id="sportName" name="sportName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="sportCategory" class="form-label">Kategori</label>
                        <input type="text" id="sportCategory" name="sportCategory" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddSportModal()">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitSport()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
// Modal instance
let addSportModalInstance = null;

function showAddSport() {
    const modalEl = document.getElementById('addSportModal');

    // Move to body to avoid stacking context
    if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);

    if (typeof coreui !== 'undefined' && coreui.Modal) {
        addSportModalInstance = new coreui.Modal(modalEl, {backdrop:true,keyboard:true,focus:true});
        addSportModalInstance.show();
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        addSportModalInstance = new bootstrap.Modal(modalEl, {backdrop:true,keyboard:true,focus:true});
        addSportModalInstance.show();
    } else {
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        document.body.classList.add('modal-open');
    }
}

function closeAddSportModal() {
    const modalEl = document.getElementById('addSportModal');
    if (addSportModalInstance && typeof addSportModalInstance.hide === 'function') {
        addSportModalInstance.hide();
    } else if (typeof coreui !== 'undefined' && coreui.Modal) {
        const inst = coreui.Modal.getInstance(modalEl);
        if (inst) inst.hide();
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const inst = bootstrap.Modal.getInstance(modalEl);
        if (inst) inst.hide();
    } else {
        modalEl.classList.remove('show');
        modalEl.style.display = 'none';
        document.body.classList.remove('modal-open');
    }
}

function submitSport() {
    const form = document.getElementById('sportForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    const name = document.getElementById('sportName').value;
    const category = document.getElementById('sportCategory').value;

    // Simulate save (replace with AJAX to backend)
    setTimeout(() => {
        alert('Sukan "' + name + '" berjaya disimpan (simulasi).');
        closeAddSportModal();
        // Optionally refresh page/list
        location.reload();
    }, 500);
}
</script>

