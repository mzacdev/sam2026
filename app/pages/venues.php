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

    <!-- Search only (removed dropdown filters) -->
    <div class="row mb-3">
        <div class="col-12">
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
                        <label for="venueSport" class="form-label">Sukan yang digunakan</label>
                        <select id="venueSport" name="venueSport" class="form-select">
                            <option value="">-- Pilih sukan --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="venueStatus" class="form-label">Status</label>
                        <select id="venueStatus" name="venueStatus" class="form-select">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
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

function showAddVenue(data = null) {
    const modalEl = document.getElementById('addVenueModal');
    if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);
    // If no data provided, reset form for new entry. If data is provided (edit), populate after showing.
    const form = document.getElementById('venueForm');
    if (form && data === null) {
        form.reset();
        const idField = document.getElementById('venueId');
        if (idField) {
            idField.remove();
        }
        // default status to active for new entries
        const statusEl = document.getElementById('venueStatus');
        if (statusEl) statusEl.value = '1';
    }

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

    // If editing (data provided), populate fields AFTER modal is prepared
    if (data && typeof data === 'object') {
        // ensure hidden id exists
        if (!document.getElementById('venueId')) {
            const input = document.createElement('input'); input.type = 'hidden'; input.id = 'venueId'; input.name = 'id';
            document.getElementById('venueForm').appendChild(input);
        }
        document.getElementById('venueId').value = data.id || '';
        document.getElementById('venueName').value = data.nama_venue || '';
        document.getElementById('venueLocation').value = data.lokasi || '';
        document.getElementById('venueCapacity').value = data.kapasiti || '';
        // if API returns sukan_id, set that, otherwise try sukan
        const sportVal = (typeof data.sukan_id !== 'undefined' && data.sukan_id !== null) ? String(data.sukan_id) : (data.sukan || '');
        const sportEl = document.getElementById('venueSport');
        if (sportEl) sportEl.value = sportVal;
        // populate status if available
        const statusEl2 = document.getElementById('venueStatus');
        if (statusEl2) statusEl2.value = (typeof data.status !== 'undefined' ? String(data.status) : '1');
    }
}

// Load sport options for dropdown
function loadSportOptions(callback) {
    const sel = document.getElementById('venueSport');
    if (!sel) { if (callback) callback(); return; }
    const cur = sel.value;
    fetch('<?php echo url("ajax/sport_list.php"); ?>', { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
    .then(res => res.json())
    .then(json => {
        if (json && json.success) {
            sel.innerHTML = '<option value="">-- Pilih sukan --</option>';
            (json.data || []).forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.nama_sukan + (s.kod_sukan ? (' ('+s.kod_sukan+')') : '');
                sel.appendChild(opt);
            });
            if (cur) sel.value = cur;
        }
        if (callback) callback();
    })
    .catch(err => { if (callback) callback(); });
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
    // clear form and id field after close
    const form = document.getElementById('venueForm');
    if (form) {
        form.reset();
        const idField = document.getElementById('venueId');
        if (idField) idField.remove();
    }
}

function submitVenue() {
    const form = document.getElementById('venueForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const id = document.getElementById('venueId') ? document.getElementById('venueId').value : '';
    const name = document.getElementById('venueName').value.trim();
    const lokasi = document.getElementById('venueLocation').value.trim();
    const kapasiti = document.getElementById('venueCapacity').value;
    const sukan_id = document.getElementById('venueSport') ? document.getElementById('venueSport').value : '';
    const status = document.getElementById('venueStatus') ? document.getElementById('venueStatus').value : '1';

    const btn = document.querySelector('#addVenueModal .btn-primary');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    const payload = new URLSearchParams();
    if (id) payload.append('id', id);
    payload.append('nama_venue', name);
    payload.append('lokasi', lokasi);
    if (kapasiti !== '') payload.append('kapasiti', kapasiti);
    if (sukan_id !== '') payload.append('sukan_id', sukan_id);
    if (typeof status !== 'undefined' && status !== '') payload.append('status', status);

    fetch('<?php echo url("ajax/venue_save.php"); ?>', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
        body: payload
    })
    .then(res => res.json())
    .then(json => {
        btn.disabled = false;
        btn.innerHTML = origText;
        if (json && json.success) {
            closeAddVenueModal();
            // reload list
            reloadVenuesTable();
        } else {
            alert((json && json.message) || 'Ralat menyimpan venue');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origText;
        alert('Ralat sambungan. Sila cuba lagi.');
    });
}

// Load venues via AJAX and render table
function reloadVenuesTable(callback) {
    const tbody = document.getElementById('venuesTableBody');
    if (!tbody) { if (callback) callback(); return; }
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Memuatkan data...</td></tr>';

    fetch('<?php echo url("ajax/venue_list.php"); ?>', { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
    .then(res => res.json())
    .then(json => {
        if (json && json.success) {
            const data = json.data || [];
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-5"><i class="cil cil-map" style="font-size: 2rem;"></i><p class="mt-2">Tiada venue didaftarkan — klik "Daftar Venue Baru" untuk mula menambah.</p></td></tr>';
            } else {
                let html = '';
                data.forEach((v, i) => {
                    const status = (parseInt(v.status) === 1) ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Tidak Aktif</span>';
                    html += '<tr>';
                    html += '<td>' + (i+1) + '</td>';
                    html += '<td>' + escapeHtml(v.nama_venue || '-') + '</td>';
                    html += '<td>' + escapeHtml(v.lokasi || '-') + '</td>';
                    html += '<td class="text-center">' + (v.kapasiti !== null ? escapeHtml(String(v.kapasiti)) : '-') + '</td>';
                    html += '<td>' + escapeHtml(v.sukan_name || '-') + '</td>';
                    html += '<td>' + status + '</td>';
                    html += '<td>';
                    html += '<a href="#" class="btn btn-sm btn-outline-primary edit-venue me-1" data-id="' + (v.id||0) + '"> <i class="fa fa-edit"></i></a>';
                    html += '<a href="#" class="btn btn-sm btn-outline-danger delete-venue" data-id="' + (v.id||0) + '"> <i class="fa fa-trash"></i></a>';
                    html += '</td>';
                    html += '</tr>';
                });
                tbody.innerHTML = html;
            }
            // update hero stats if provided
            if (json.stats) {
                const hero = document.querySelector('.card.bg-light .d-none.d-md-flex');
                if (hero) {
                    const statDivs = hero.querySelectorAll('.me-3 .h5.mb-0');
                    if (statDivs.length >= 3) {
                        statDivs[0].textContent = json.stats.total || 0;
                        statDivs[1].textContent = json.stats.active || 0;
                        statDivs[2].textContent = json.stats.inactive || 0;
                    }
                }
            }
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-3">Ralat memuatkan data.</td></tr>';
        }
        if (callback) callback();
    })
    .catch(err => {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-3">Ralat sambungan.</td></tr>';
        if (callback) callback();
    });
}

// Escape helper
function escapeHtml(text) {
    const d = document.createElement('div'); d.textContent = text; return d.innerHTML;
}

// Edit / Delete handlers
document.addEventListener('click', function(e) {
    const editBtn = e.target.closest && e.target.closest('.edit-venue');
    if (editBtn) {
        e.preventDefault();
        const id = editBtn.getAttribute('data-id');
        // fetch single venue via list and populate form
        fetch('<?php echo url("ajax/venue_list.php"); ?>', { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(json => {
            if (json && json.success) {
                const item = (json.data || []).find(x => String(x.id) === String(id));
                if (item) {
                    // ensure sport options loaded then show modal with data
                    loadSportOptions(function(){ showAddVenue(item); });
                }
            }
        });
    }

    const delBtn = e.target.closest && e.target.closest('.delete-venue');
    if (delBtn) {
        e.preventDefault();
        const id = delBtn.getAttribute('data-id');
        if (!id) return;

        const doDelete = function() {
            fetch('<?php echo url("ajax/venue_delete.php"); ?>', { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + encodeURIComponent(id) })
            .then(res => res.json())
            .then(json => {
                if (json && json.success) {
                    reloadVenuesTable();
                } else {
                    alert((json && json.message) || 'Ralat memadam.');
                }
            })
            .catch(err => { alert('Ralat sambungan.'); });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Padam venue ini?',
                text: 'Tindakan ini tidak boleh diundur.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, padam',
                cancelButtonText: 'Batal'
            }).then(function(result){
                if (result && result.isConfirmed) doDelete();
            });
        } else {
            if (!confirm('Padam venue ini?')) return;
            doDelete();
        }
    }
});

// Initial load
document.addEventListener('DOMContentLoaded', function() { loadSportOptions(function(){ reloadVenuesTable(); }); });
</script>


