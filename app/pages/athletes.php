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
                <button class="btn btn-primary" onclick="showAddAthlete()">
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

<!-- Add Athlete Modal -->
<div class="modal fade" id="addAthleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Atlet Baru</h5>
                <button type="button" class="btn-close" aria-label="Close" onclick="closeAddAthleteModal()"></button>
            </div>
            <div class="modal-body">
                <form id="athleteForm">
                    <div class="mb-3">
                        <label for="athleteName" class="form-label">Nama Penuh</label>
                        <input type="text" id="athleteName" name="athleteName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="athleteIdNo" class="form-label">No. Kad Pengenalan</label>
                        <input type="text" id="athleteIdNo" name="athleteIdNo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="athleteContingent" class="form-label">Kontinjen</label>
                        <select id="athleteContingent" class="form-select"></select>
                    </div>
                    <div class="mb-3">
                        <label for="athleteSport" class="form-label">Sukan</label>
                        <select id="athleteSport" class="form-select"></select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddAthleteModal()">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitAthlete()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
let addAthleteModalInstance = null;

function showAddAthlete() {
    const modalEl = document.getElementById('addAthleteModal');
    if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);

    if (typeof coreui !== 'undefined' && coreui.Modal) {
        addAthleteModalInstance = new coreui.Modal(modalEl, {backdrop:true,keyboard:true,focus:true});
        addAthleteModalInstance.show();
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        addAthleteModalInstance = new bootstrap.Modal(modalEl, {backdrop:true,keyboard:true,focus:true});
        addAthleteModalInstance.show();
    } else {
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        document.body.classList.add('modal-open');
    }
}

function closeAddAthleteModal() {
    const modalEl = document.getElementById('addAthleteModal');
    if (addAthleteModalInstance && typeof addAthleteModalInstance.hide === 'function') {
        addAthleteModalInstance.hide();
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

function submitAthlete() {
    const form = document.getElementById('athleteForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const name = document.getElementById('athleteName').value;

    // Simulate save; replace with AJAX to backend as needed
    setTimeout(() => {
        alert('Atlet "' + name + '" berjaya disimpan (simulasi).');
        closeAddAthleteModal();
        location.reload();
    }, 500);
}
</script>

