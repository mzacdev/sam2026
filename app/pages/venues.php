<?php
/**
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
                <button class="btn btn-primary" onclick="showAddVenue()">
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

<!-- Add Venue Modal -->
<div class="modal fade" id="addVenueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Venue Baru</h5>
                <button type="button" class="btn-close" aria-label="Close" onclick="closeAddVenueModal()"></button>
            </div>
            <div class="modal-body">
                <form id="venueForm">
                    <div class="mb-3">
                        <label for="venueName" class="form-label">Nama Venue</label>
                        <input type="text" id="venueName" name="venueName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="venueLocation" class="form-label">Lokasi</label>
                        <input type="text" id="venueLocation" name="venueLocation" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="venueCapacity" class="form-label">Kapasiti</label>
                        <input type="number" id="venueCapacity" name="venueCapacity" class="form-control" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="venueSports" class="form-label">Sukan yang digunakan</label>
                        <input type="text" id="venueSports" name="venueSports" class="form-control" placeholder="cth: Bola Sepak, Badminton">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddVenueModal()">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitVenue()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
let addVenueModalInstance = null;

function showAddVenue() {
    const modalEl = document.getElementById('addVenueModal');
    if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);

    if (typeof coreui !== 'undefined' && coreui.Modal) {
        addVenueModalInstance = new coreui.Modal(modalEl, {backdrop:true,keyboard:true,focus:true});
        addVenueModalInstance.show();
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        addVenueModalInstance = new bootstrap.Modal(modalEl, {backdrop:true,keyboard:true,focus:true});
        addVenueModalInstance.show();
    } else {
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        document.body.classList.add('modal-open');
    }
}

function closeAddVenueModal() {
    const modalEl = document.getElementById('addVenueModal');
    if (addVenueModalInstance && typeof addVenueModalInstance.hide === 'function') {
        addVenueModalInstance.hide();
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

function submitVenue() {
    const form = document.getElementById('venueForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const name = document.getElementById('venueName').value;

    // Simulate save; replace with AJAX to backend when ready
    setTimeout(() => {
        alert('Venue "' + name + '" berjaya disimpan (simulasi).');
        closeAddVenueModal();
        location.reload();
    }, 500);
}
</script>
}

function closeAddVenueModal() {
    const modalEl = document.getElementById('addVenueModal');
    if (addVenueModalInstance && typeof addVenueModalInstance.hide === 'function') {
        addVenueModalInstance.hide();
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

function submitVenue() {
    const form = document.getElementById('venueForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const name = document.getElementById('venueName').value;

    // Simulate save; replace with AJAX to backend when ready
    setTimeout(() => {
        alert('Venue "' + name + '" berjaya disimpan (simulasi).');
        closeAddVenueModal();
        location.reload();
    }, 500);
}
</script>

