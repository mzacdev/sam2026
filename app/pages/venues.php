<?php
/**
 * Venues Management Page
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Venue';

ob_start();
?>
<div class="w-100 px-3">
    <!-- Hero -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light border-0 shadow-sm overflow-hidden">
                <div class="card-body py-4 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                    <div>
                        <h2 class="mb-1">Venue</h2>
                        <p class="text-muted mb-0">Urus lokasi dan tempat pertandingan</p>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <div class="d-none d-md-flex">
                            <div class="me-3 text-center">
                                <div class="h5 mb-0">0</div>
                                <div class="small text-muted">Venue</div>
                            </div>
                            <div class="me-3 text-center">
                                <div class="h5 mb-0">0</div>
                                <div class="small text-muted">Kapasiti</div>
                            </div>
                            <div class="me-3 text-center">
                                <div class="h5 mb-0">0</div>
                                <div class="small text-muted">Sukan</div>
                            </div>
                        </div>

                        <div class="btn-group">
                            <button class="btn btn-outline-secondary">Laporan</button>
                            <button class="btn btn-primary" onclick="showAddVenue()">
                                <i class="cil cil-plus me-1"></i> Daftar Venue Baru
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="row mb-3">
        <div class="col-lg-4 mb-2 mb-lg-0">
            <select class="form-select" id="filterSport">
                <option value="">Semua Sukan</option>
            </select>
        </div>
        <div class="col-lg-4 mb-2 mb-lg-0">
            <select class="form-select" id="filterLocation">
                <option value="">Semua Lokasi</option>
            </select>
        </div>
        <div class="col-lg-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="cil cil-magnifying-glass"></i></span>
                <input type="text" class="form-control" id="venuesSearch" placeholder="Cari nama atau lokasi...">
            </div>
        </div>
    </div>

    <!-- Venues List -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Senarai Venue</strong>
                        <div class="small text-muted">Urus semua venue berdaftar</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width:70px;">#</th>
                                    <th scope="col">Nama Venue</th>
                                    <th scope="col">Lokasi</th>
                                    <th scope="col">Kapasiti</th>
                                    <th scope="col">Sukan</th>
                                    <th scope="col" style="width:120px;">Status</th>
                                    <th scope="col" style="width:160px;">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="venuesTableBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="cil cil-map" style="font-size: 2rem;"></i>
                                        <p class="mt-2">Tiada venue didaftarkan — klik "Daftar Venue Baru" untuk mula menambah.</p>
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

    // Simulate save; replace with AJAX to backend as needed
    setTimeout(() => {
        alert('Venue "' + name + '" berjaya disimpan (simulasi).');
        closeAddVenueModal();
        location.reload();
    }, 500);
}
</script>


