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
                <button class="btn btn-primary" onclick="showAddSport()">
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

