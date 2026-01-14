<?php
/**
 * Pasukan (Team) Management Page
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../api/models/PasukanModel.php';
require_once __DIR__ . '/../api/models/ContingentModel.php';
require_once __DIR__ . '/../api/models/SportModel.php';

$page_title = 'Pasukan';

// Get current user role for status control
Session::start();
$auth = getAuth();
$currentUserRole = Session::get('user_role') ?? '';
$canChangeStatus = in_array($currentUserRole, ['ADMIN', 'ORGANIZER']);

// Fetch contingents from database
$contingents = [];
try {
    $contingentModel = new ContingentModel();
    $result = $contingentModel->getAll(['limit' => 1000, 'status' => 1]);
    if ($result['success']) {
        $contingents = $result['data'];
    }
} catch (Exception $e) {
    error_log('[pasukan.php] DB error fetching contingents: ' . $e->getMessage());
}

// Fetch sports from database
$sports = [];
try {
    $sportModel = new SportModel();
    $result = $sportModel->getAll(['limit' => 1000, 'status' => 1]);
    if ($result['success']) {
        $sports = $result['data'];
    }
} catch (Exception $e) {
    error_log('[pasukan.php] DB error fetching sports: ' . $e->getMessage());
}

// Fetch teams from database
$teams = [];
$teamStats = ['total' => 0, 'active' => 0, 'inactive' => 0];
try {
    $pasukanModel = new PasukanModel();
    $result = $pasukanModel->getAll(['limit' => 1000]);
    if ($result['success']) {
        $teams = $result['data'];
    }
    
    $statsResult = $pasukanModel->getStatistics();
    if ($statsResult['success']) {
        $teamStats = $statsResult['data'];
    }
} catch (Exception $e) {
    error_log('[pasukan.php] DB error fetching teams: ' . $e->getMessage());
}

ob_start();
?>
<div class="w-100 px-3">
    <!-- Hero -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light border-0 shadow-sm overflow-hidden">
                <div class="card-body py-4 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                    <div>
                        <h2 class="mb-1">Pasukan</h2>
                        <p class="text-muted mb-0">Urus pendaftaran pasukan — ringkasan dan tindakan pantas</p>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <div class="d-none d-md-flex">
                            <div class="me-3 text-center">
                                <div class="h5 mb-0"><?php echo (int)$teamStats['total']; ?></div>
                                <div class="small text-muted">Pasukan</div>
                            </div>
                            <div class="me-3 text-center">
                                <div class="h5 mb-0"><?php echo (int)$teamStats['active']; ?></div>
                                <div class="small text-muted">Aktif</div>
                            </div>
                            <div class="me-3 text-center">
                                <div class="h5 mb-0"><?php echo (int)$teamStats['inactive']; ?></div>
                                <div class="small text-muted">Tidak Aktif</div>
                            </div>
                        </div>

                        <div class="btn-group">
                            <button class="btn btn-outline-secondary">Laporan</button>
                            <button class="btn btn-outline-primary" onclick="showBulkUploadPasukan()">
                                <i class="cil cil-cloud-upload me-1"></i> Muat Naik Pukal
                            </button>
                            <button class="btn btn-primary" onclick="showAddPasukan()">
                                <i class="cil cil-plus me-1"></i> Daftar Pasukan Baru
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
            <select class="form-select" id="filterContingent">
                <option value="">Semua Kontinjen</option>
                <?php foreach ($contingents as $c): ?>
                    <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars($c['nama_universiti'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-4 mb-2 mb-lg-0">
            <select class="form-select" id="filterSport">
                <option value="">Semua Sukan</option>
                <?php foreach ($sports as $s): ?>
                    <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['nama_sukan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="cil cil-magnifying-glass"></i></span>
                <input type="text" class="form-control" id="pasukanSearch" placeholder="Cari nama pasukan, pengurus, jurulatih atau atlet...">
            </div>
        </div>
    </div>

    <!-- Teams List -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Senarai Pasukan</strong>
                        <div class="small text-muted">Urus semua pasukan berdaftar</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width:70px;">#</th>
                                    <th scope="col">Nama Pasukan</th>
                                    <th scope="col">Kontinjen</th>
                                    <th scope="col">Sukan</th>
                                    <th scope="col">Pengurus</th>
                                    <th scope="col">Jurulatih</th>
                                    <th scope="col" style="width:100px;">Bil. Atlet</th>
                                    <th scope="col" style="width:120px;">Status</th>
                                    <th scope="col" style="width:160px;">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="pasukanTableBody">
                                <?php if (empty($teams)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-5">
                                            <i class="cil cil-people" style="font-size: 2rem;"></i>
                                            <p class="mt-2">Tiada pasukan didaftarkan — klik "Daftar Pasukan Baru" untuk mula menambah.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($teams as $i => $t): ?>
                                        <tr>
                                            <td><?php echo $i + 1; ?></td>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($t['nama_pasukan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($t['nama_universiti'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($t['nama_sukan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div class="small">
                                                    <?php 
                                                    $pengurusList = !empty($t['pengurus_list']) ? explode(', ', $t['pengurus_list']) : [];
                                                    echo !empty($pengurusList) ? htmlspecialchars(implode(', ', array_slice($pengurusList, 0, 2)), ENT_QUOTES, 'UTF-8') : '-';
                                                    if (count($pengurusList) > 2) echo '...';
                                                    ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <?php 
                                                    $jurulatihList = !empty($t['jurulatih_list']) ? explode(', ', $t['jurulatih_list']) : [];
                                                    echo !empty($jurulatihList) ? htmlspecialchars(implode(', ', array_slice($jurulatihList, 0, 2)), ENT_QUOTES, 'UTF-8') : '-';
                                                    if (count($jurulatihList) > 2) echo '...';
                                                    ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info"><?php echo (int)($t['atlet_count'] ?? 0); ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $status = isset($t['status']) ? (int)$t['status'] : 0;
                                                if ($status == 1) {
                                                    $badgeClass = 'bg-success';
                                                    $statusText = 'Aktif';
                                                } else {
                                                    $badgeClass = 'bg-secondary';
                                                    $statusText = 'Tidak Aktif';
                                                }
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                                            </td>
                                            <td>
                                                <a class="btn btn-sm btn-outline-primary edit-pasukan" title="Edit" href="#"
                                                   data-id="<?php echo (int)$t['id']; ?>">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a class="btn btn-sm btn-outline-danger delete-pasukan" title="Padam" href="#" data-id="<?php echo (int)$t['id']; ?>">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                    </td>
                                </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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

<!-- Add/Edit Pasukan Modal -->
<div class="modal fade" id="addPasukanModal" tabindex="-1" aria-labelledby="addPasukanModalLabel" aria-hidden="true" data-coreui-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPasukanModalLabel">Daftar Pasukan Baru</h5>
                <button type="button" class="btn-close" aria-label="Close" onclick="closeAddPasukanModal()"></button>
            </div>
            <div class="modal-body">
                <form id="pasukanForm">
                    <input type="hidden" id="pasukanId" name="id" value="">
                    
                    <!-- Basic Information -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">Maklumat Asas</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pasukanNama" class="form-label">Nama Pasukan <span class="text-danger">*</span></label>
                                <input type="text" id="pasukanNama" name="nama_pasukan" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pasukanKontinjen" class="form-label">Kontinjen <span class="text-danger">*</span></label>
                                <select id="pasukanKontinjen" name="kontinjen_id" class="form-select" required>
                                    <option value="">Sila Pilih</option>
                                    <?php foreach ($contingents as $c): ?>
                                        <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars($c['nama_universiti'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pasukanSukan" class="form-label">Sukan <span class="text-danger">*</span></label>
                                <select id="pasukanSukan" name="sukan_id" class="form-select" required>
                                    <option value="">Sila Pilih</option>
                                    <?php foreach ($sports as $s): ?>
                                        <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['nama_sukan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if ($canChangeStatus): ?>
                            <div class="col-md-6 mb-3">
                                <label for="pasukanStatus" class="form-label">Status</label>
                                <select id="pasukanStatus" name="status" class="form-select">
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Pengurus (Manager) -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">Pengurus</h6>
                        <div id="pengurusContainer">
                            <div class="pengurus-item border rounded p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control pengurus-nama" placeholder="Nama penuh">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">No. Kad Pengenalan</label>
                                        <input type="text" class="form-control pengurus-ic" placeholder="Contoh: 123456789012">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">No. Telefon</label>
                                        <input type="text" class="form-control pengurus-phone" placeholder="Contoh: 012-3456789">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">E-mel</label>
                                        <input type="email" class="form-control pengurus-email" placeholder="Contoh: email@example.com">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addPengurus()">
                            <i class="cil cil-plus"></i> Tambah Pengurus
                        </button>
                    </div>

                    <!-- Jurulatih (Coach) -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">Jurulatih</h6>
                        <div id="jurulatihContainer">
                            <div class="jurulatih-item border rounded p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control jurulatih-nama" placeholder="Nama penuh">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">No. Kad Pengenalan</label>
                                        <input type="text" class="form-control jurulatih-ic" placeholder="Contoh: 123456789012">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">No. Telefon</label>
                                        <input type="text" class="form-control jurulatih-phone" placeholder="Contoh: 012-3456789">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">E-mel</label>
                                        <input type="email" class="form-control jurulatih-email" placeholder="Contoh: email@example.com">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addJurulatih()">
                            <i class="cil cil-plus"></i> Tambah Jurulatih
                        </button>
                    </div>

                    <!-- Atlet (Athletes) -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">Senarai Atlet mengikut Kategori</h6>
                        <div id="kategoriContainer">
                            <!-- Category sections will be added here -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addKategoriSection()">
                            <i class="cil cil-plus"></i> Tambah Kategori
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddPasukanModal()">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitPasukan()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Upload Pasukan Modal -->
<div class="modal fade" id="bulkUploadPasukanModal" tabindex="-1" aria-labelledby="bulkUploadPasukanModalLabel" aria-hidden="true" data-coreui-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkUploadPasukanModalLabel">Muat Naik Pukal Pasukan</h5>
                <button type="button" class="btn-close" aria-label="Close" onclick="closeBulkUploadPasukanModal()"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <p class="text-muted">Muat naik fail CSV untuk mendaftarkan berbilang pasukan sekaligus.</p>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="downloadTemplate()">
                        <i class="cil cil-download me-1"></i> Muat Turun Template
                    </button>
                </div>
                
                <div class="mb-3">
                    <label for="csvFileInput" class="form-label">Pilih Fail CSV <span class="text-danger">*</span></label>
                    <input type="file" id="csvFileInput" class="form-control" accept=".csv" onchange="handleFileSelect(event)">
                    <div class="form-text">Saiz maksimum: 5MB. Format: CSV sahaja.</div>
                </div>
                
                <div id="filePreview" class="d-none mb-3">
                    <div class="card">
                        <div class="card-header">
                            <strong>Pratonton Fail</strong>
                        </div>
                        <div class="card-body">
                            <div id="previewContent" class="small"></div>
                        </div>
                    </div>
                </div>
                
                <div id="uploadProgress" class="d-none mb-3">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%">
                            Memproses fail...
                        </div>
                    </div>
                </div>
                
                <div id="uploadResults" class="d-none">
                    <div class="alert" id="resultsAlert">
                        <h6 class="alert-heading">Keputusan Muat Naik</h6>
                        <div id="resultsContent"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeBulkUploadPasukanModal()">Tutup</button>
                <button type="button" class="btn btn-primary" id="uploadBtn" onclick="uploadBulkPasukan()" disabled>
                    <i class="cil cil-cloud-upload me-1"></i> Muat Naik
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let addPasukanModalInstance = null;
let editingPasukanId = null;

// Load teams on page load
document.addEventListener('DOMContentLoaded', function() {
    loadPasukanList();
    
    // Setup filter handlers
    document.getElementById('filterContingent')?.addEventListener('change', loadPasukanList);
    document.getElementById('filterSport')?.addEventListener('change', loadPasukanList);
    document.getElementById('pasukanSearch')?.addEventListener('input', debounce(loadPasukanList, 300));
    
    // Load categories when sport is selected in modal
    document.getElementById('pasukanSukan')?.addEventListener('change', function() {
        const sukanId = this.value;
        loadCategoriesForSport(sukanId);
    });
    
    // Setup edit/delete handlers
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-pasukan')) {
            e.preventDefault();
            const id = e.target.closest('.edit-pasukan').dataset.id;
            editPasukan(id);
        }
        if (e.target.closest('.delete-pasukan')) {
            e.preventDefault();
            const id = e.target.closest('.delete-pasukan').dataset.id;
            deletePasukan(id);
        }
    });
});

function showAddPasukan() {
    editingPasukanId = null;
    document.getElementById('addPasukanModalLabel').textContent = 'Daftar Pasukan Baru';
    document.getElementById('pasukanForm').reset();
    document.getElementById('pasukanId').value = '';
    
    // Reset containers
    resetPengurusContainer();
    resetJurulatihContainer();
    resetKategoriContainer();
    
    const modalEl = document.getElementById('addPasukanModal');
    if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);

    if (typeof coreui !== 'undefined' && coreui.Modal) {
        addPasukanModalInstance = new coreui.Modal(modalEl, {backdrop:true,keyboard:true,focus:true});
        addPasukanModalInstance.show();
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        addPasukanModalInstance = new bootstrap.Modal(modalEl, {backdrop:true,keyboard:true,focus:true});
        addPasukanModalInstance.show();
    } else {
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        document.body.classList.add('modal-open');
    }
}

function closeAddPasukanModal() {
    const modalEl = document.getElementById('addPasukanModal');
    if (addPasukanModalInstance && typeof addPasukanModalInstance.hide === 'function') {
        addPasukanModalInstance.hide();
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

function resetPengurusContainer() {
    const container = document.getElementById('pengurusContainer');
    container.innerHTML = `
        <div class="pengurus-item border rounded p-3 mb-3">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control pengurus-nama" placeholder="Nama penuh">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">No. Kad Pengenalan</label>
                    <input type="text" class="form-control pengurus-ic" placeholder="Contoh: 123456789012">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">No. Telefon</label>
                    <input type="text" class="form-control pengurus-phone" placeholder="Contoh: 012-3456789">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">E-mel</label>
                    <input type="email" class="form-control pengurus-email" placeholder="Contoh: email@example.com">
                </div>
            </div>
        </div>
    `;
}

function resetJurulatihContainer() {
    const container = document.getElementById('jurulatihContainer');
    container.innerHTML = `
        <div class="jurulatih-item border rounded p-3 mb-3">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control jurulatih-nama" placeholder="Nama penuh">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">No. Kad Pengenalan</label>
                    <input type="text" class="form-control jurulatih-ic" placeholder="Contoh: 123456789012">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">No. Telefon</label>
                    <input type="text" class="form-control jurulatih-phone" placeholder="Contoh: 012-3456789">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">E-mel</label>
                    <input type="email" class="form-control jurulatih-email" placeholder="Contoh: email@example.com">
                </div>
            </div>
        </div>
    `;
}

function resetKategoriContainer() {
    const container = document.getElementById('kategoriContainer');
    container.innerHTML = '';
}

function addPengurus() {
    const container = document.getElementById('pengurusContainer');
    const newItem = document.createElement('div');
    newItem.className = 'pengurus-item border rounded p-3 mb-3';
    newItem.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Pengurus Tambahan</strong>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePengurus(this)">
                <i class="cil cil-trash"></i> Buang
            </button>
        </div>
        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label">Nama <span class="text-danger">*</span></label>
                <input type="text" class="form-control pengurus-nama" placeholder="Nama penuh">
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">No. Kad Pengenalan</label>
                <input type="text" class="form-control pengurus-ic" placeholder="Contoh: 123456789012">
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">No. Telefon</label>
                <input type="text" class="form-control pengurus-phone" placeholder="Contoh: 012-3456789">
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">E-mel</label>
                <input type="email" class="form-control pengurus-email" placeholder="Contoh: email@example.com">
            </div>
        </div>
    `;
    container.appendChild(newItem);
}

function removePengurus(btn) {
    btn.closest('.pengurus-item').remove();
}

function addJurulatih() {
    const container = document.getElementById('jurulatihContainer');
    const newItem = document.createElement('div');
    newItem.className = 'jurulatih-item border rounded p-3 mb-3';
    newItem.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Jurulatih Tambahan</strong>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeJurulatih(this)">
                <i class="cil cil-trash"></i> Buang
            </button>
        </div>
        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label">Nama <span class="text-danger">*</span></label>
                <input type="text" class="form-control jurulatih-nama" placeholder="Nama penuh">
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">No. Kad Pengenalan</label>
                <input type="text" class="form-control jurulatih-ic" placeholder="Contoh: 123456789012">
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">No. Telefon</label>
                <input type="text" class="form-control jurulatih-phone" placeholder="Contoh: 012-3456789">
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">E-mel</label>
                <input type="email" class="form-control jurulatih-email" placeholder="Contoh: email@example.com">
            </div>
        </div>
    `;
    container.appendChild(newItem);
}

function removeJurulatih(btn) {
    btn.closest('.jurulatih-item').remove();
}

function addKategoriSection() {
    const container = document.getElementById('kategoriContainer');
    const sukanId = document.getElementById('pasukanSukan')?.value || '';
    
    if (!sukanId || sukanId === '') {
        if (window.Swal) {
            Swal.fire({
                text: 'Sila pilih sukan dahulu sebelum menambah kategori.',
                icon: 'warning'
            });
        } else {
            alert('Sila pilih sukan dahulu sebelum menambah kategori.');
        }
        return;
    }
    
    const kategoriSection = document.createElement('div');
    kategoriSection.className = 'kategori-section border rounded p-3 mb-3';
    kategoriSection.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Pilih Kategori <span class="text-danger">*</span></label>
                <select class="form-select kategori-select" required onchange="loadCategoryOptions(this)">
                    <option value="">Sila Pilih Kategori</option>
                </select>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeKategoriSection(this)">
                <i class="cil cil-trash"></i> Buang Kategori
            </button>
        </div>
        <div class="atlet-list" data-kategori-id="">
            <div class="small text-muted mb-2">Senarai Atlet untuk kategori ini:</div>
            <div class="atlet-items"></div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addAtletToKategori(this)">
                <i class="cil cil-plus"></i> Tambah Atlet
            </button>
        </div>
    `;
    container.appendChild(kategoriSection);
    
    // Load categories for this dropdown
    loadCategoriesForSport(sukanId).then(() => {
        const select = kategoriSection.querySelector('.kategori-select');
        if (select) {
            // Categories are already loaded by loadCategoriesForSport
            // Just need to populate this specific select
            populateCategorySelect(select);
        }
    });
}

function populateCategorySelect(selectElement) {
    // Get categories from the first available select or reload
    const sukanId = document.getElementById('pasukanSukan')?.value || '';
    if (sukanId && selectElement) {
        return fetch('<?php echo url("ajax/get_categories.php"); ?>?sukan_id=' + sukanId)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data) {
                let html = '<option value="">Sila Pilih Kategori</option>';
                data.data.forEach(cat => {
                    if (cat.status == 1) {
                        html += `<option value="${cat.id}">${escapeHtml(cat.nama_kategori || '')}</option>`;
                    }
                });
                selectElement.innerHTML = html;
            }
        });
    }
    return Promise.resolve();
}

function loadCategoryOptions(selectElement) {
    const kategoriId = selectElement.value;
    const kategoriSection = selectElement.closest('.kategori-section');
    const atletList = kategoriSection.querySelector('.atlet-list');
    if (atletList) {
        atletList.setAttribute('data-kategori-id', kategoriId);
    }
}

function removeKategoriSection(btn) {
    btn.closest('.kategori-section').remove();
}

function addAtletToKategori(btn) {
    const kategoriSection = btn.closest('.kategori-section');
    const kategoriSelect = kategoriSection.querySelector('.kategori-select');
    const kategoriId = kategoriSelect?.value || '';
    
    if (!kategoriId || kategoriId === '') {
        if (window.Swal) {
            Swal.fire({
                text: 'Sila pilih kategori dahulu sebelum menambah atlet.',
                icon: 'warning'
            });
        } else {
            alert('Sila pilih kategori dahulu sebelum menambah atlet.');
        }
        return;
    }
    
    const atletItems = kategoriSection.querySelector('.atlet-items');
    if (!atletItems) return;
    
    const atletItem = document.createElement('div');
    atletItem.className = 'atlet-item border rounded p-2 mb-2 bg-light';
    atletItem.innerHTML = `
        <div class="row">
            <div class="col-md-4 mb-2">
                <label class="form-label small">Nama <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm atlet-nama" placeholder="Nama penuh" required>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label small">No. Kad Pengenalan</label>
                <input type="text" class="form-control form-control-sm atlet-ic" placeholder="Contoh: 123456789012">
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label small">No. Matrik</label>
                <input type="text" class="form-control form-control-sm atlet-matrik" placeholder="Contoh: ABC123456">
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAtlet(this)">
            <i class="cil cil-trash"></i> Buang
        </button>
    `;
    atletItems.appendChild(atletItem);
}

function removeAtlet(btn) {
    btn.closest('.atlet-item').remove();
}

function submitPasukan() {
    const form = document.getElementById('pasukanForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Collect form data
    const formData = {
        id: document.getElementById('pasukanId').value || 0,
        nama_pasukan: document.getElementById('pasukanNama').value.trim(),
        kontinjen_id: document.getElementById('pasukanKontinjen').value,
        sukan_id: document.getElementById('pasukanSukan').value,
        status: document.getElementById('pasukanStatus')?.value || 1
    };
    
    // Collect pengurus
    const pengurus = [];
    document.querySelectorAll('.pengurus-item').forEach(item => {
        const nama = item.querySelector('.pengurus-nama')?.value.trim();
        if (nama) {
            pengurus.push({
                nama: nama,
                no_kad_pengenalan: item.querySelector('.pengurus-ic')?.value.trim() || '',
                no_telefon: item.querySelector('.pengurus-phone')?.value.trim() || '',
                emel: item.querySelector('.pengurus-email')?.value.trim() || ''
            });
        }
    });
    formData.pengurus = pengurus;
    
    // Collect jurulatih
    const jurulatih = [];
    document.querySelectorAll('.jurulatih-item').forEach(item => {
        const nama = item.querySelector('.jurulatih-nama')?.value.trim();
        if (nama) {
            jurulatih.push({
                nama: nama,
                no_kad_pengenalan: item.querySelector('.jurulatih-ic')?.value.trim() || '',
                no_telefon: item.querySelector('.jurulatih-phone')?.value.trim() || '',
                emel: item.querySelector('.jurulatih-email')?.value.trim() || ''
            });
        }
    });
    formData.jurulatih = jurulatih;
    
    // Collect atlet from category sections
    const atlet = [];
    document.querySelectorAll('.kategori-section').forEach(kategoriSection => {
        const kategoriSelect = kategoriSection.querySelector('.kategori-select');
        const kategoriId = kategoriSelect?.value || '';
        
        if (!kategoriId) {
            // Skip sections without selected category
            return;
        }
        
        // Get all athletes in this category section
        const atletItems = kategoriSection.querySelectorAll('.atlet-item');
        atletItems.forEach(item => {
            const nama = item.querySelector('.atlet-nama')?.value.trim();
            if (nama) {
                atlet.push({
                    nama: nama,
                    no_kad_pengenalan: item.querySelector('.atlet-ic')?.value.trim() || '',
                    no_matrik: item.querySelector('.atlet-matrik')?.value.trim() || '',
                    kategori_id: parseInt(kategoriId)
                });
            }
        });
    });
    formData.atlet = atlet;
    
    // Validate at least one category section with athletes
    const kategoriSections = document.querySelectorAll('.kategori-section');
    if (kategoriSections.length === 0) {
        if (window.Swal) {
            Swal.fire({
                text: 'Sila tambah sekurang-kurangnya satu kategori.',
                icon: 'warning'
            });
        } else {
            alert('Sila tambah sekurang-kurangnya satu kategori.');
        }
        return;
    }
    
    // Validate all category sections have selected category
    let hasInvalidCategory = false;
    kategoriSections.forEach(section => {
        const kategoriSelect = section.querySelector('.kategori-select');
        const kategoriId = kategoriSelect?.value || '';
        const atletCount = section.querySelectorAll('.atlet-item').length;
        
        if (!kategoriId && atletCount > 0) {
            hasInvalidCategory = true;
        }
    });
    
    if (hasInvalidCategory) {
        if (window.Swal) {
            Swal.fire({
                text: 'Sila pilih kategori untuk semua bahagian yang mempunyai atlet.',
                icon: 'warning'
            });
        } else {
            alert('Sila pilih kategori untuk semua bahagian yang mempunyai atlet.');
        }
        return;
    }
    
    // Validate at least one athlete
    if (atlet.length === 0) {
        if (window.Swal) {
            Swal.fire({
                text: 'Sila tambah sekurang-kurangnya satu atlet.',
                icon: 'warning'
            });
        } else {
            alert('Sila tambah sekurang-kurangnya satu atlet.');
        }
        return;
    }
    
    // Show loading
    if (window.Swal) {
        Swal.showLoading();
    }
    
    // Submit via AJAX
    fetch('<?php echo url("ajax/pasukan_save.php"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(res => res.json())
    .then(data => {
        if (window.Swal) Swal.close();
        
        if (data.success) {
            // Close modal first
            closeAddPasukanModal();
            
            // Reload list, then show success message
            loadPasukanList();
            
            if (window.Swal) {
                Swal.fire({
                    text: data.message || 'Pasukan berjaya disimpan.',
                    icon: 'success'
                });
            } else {
                alert(data.message || 'Pasukan berjaya disimpan.');
            }
        } else {
            if (window.Swal) {
                Swal.fire({
                    text: data.message || 'Ralat menyimpan pasukan.',
                    icon: 'error'
                });
            } else {
                alert(data.message || 'Ralat menyimpan pasukan.');
            }
        }
    })
    .catch(err => {
        if (window.Swal) Swal.close();
        console.error('Error:', err);
        if (window.Swal) {
            Swal.fire({
                text: 'Ralat menyimpan pasukan. Sila cuba lagi.',
                icon: 'error'
            });
        } else {
            alert('Ralat menyimpan pasukan. Sila cuba lagi.');
        }
    });
}

// Load categories when sport is selected
function loadCategoriesForSport(sukanId) {
    if (!sukanId || sukanId === '') {
        // Clear all category dropdowns
        document.querySelectorAll('.kategori-select').forEach(select => {
            select.innerHTML = '<option value="">Sila pilih sukan dahulu</option>';
        });
        return Promise.resolve();
    }
    
    return fetch('<?php echo url("ajax/get_categories.php"); ?>?sukan_id=' + sukanId)
    .then(res => res.json())
    .then(data => {
        if (data.success && data.data) {
            const categories = data.data;
            // Update all category dropdowns
            document.querySelectorAll('.kategori-select').forEach(select => {
                let html = '<option value="">Sila Pilih Kategori</option>';
                categories.forEach(cat => {
                    if (cat.status == 1) { // Only show active categories
                        html += `<option value="${cat.id}">${escapeHtml(cat.nama_kategori || '')}</option>`;
                    }
                });
                select.innerHTML = html;
            });
        } else {
            // Clear dropdowns on error
            document.querySelectorAll('.kategori-select').forEach(select => {
                select.innerHTML = '<option value="">Tiada kategori tersedia</option>';
            });
        }
    })
    .catch(err => {
        console.error('Error loading categories:', err);
        document.querySelectorAll('.kategori-select').forEach(select => {
            select.innerHTML = '<option value="">Ralat memuatkan kategori</option>';
        });
    });
}

function editPasukan(id) {
    editingPasukanId = id;
    document.getElementById('addPasukanModalLabel').textContent = 'Kemaskini Pasukan';
    
    // Show modal first
    showAddPasukan();
    
    // Show loading
    if (window.Swal) {
        Swal.showLoading();
    }
    
    // Fetch team details
    fetch('<?php echo url("ajax/pasukan_list.php"); ?>?id=' + id)
    .then(res => res.json())
    .then(data => {
        if (window.Swal) Swal.close();
        
        if (data.success && data.data) {
            loadTeamIntoForm(data.data);
        } else {
            if (window.Swal) {
                Swal.fire({
                    text: 'Ralat memuatkan data pasukan.',
                    icon: 'error'
                });
            } else {
                alert('Ralat memuatkan data pasukan.');
            }
            closeAddPasukanModal();
        }
    })
    .catch(err => {
        if (window.Swal) Swal.close();
        console.error('Error:', err);
        if (window.Swal) {
            Swal.fire({
                text: 'Ralat memuatkan data pasukan.',
                icon: 'error'
            });
        } else {
            alert('Ralat memuatkan data pasukan.');
        }
        closeAddPasukanModal();
    });
}

function loadTeamIntoForm(team) {
    document.getElementById('pasukanId').value = team.id;
    document.getElementById('pasukanNama').value = team.nama_pasukan || '';
    document.getElementById('pasukanKontinjen').value = team.kontinjen_id || '';
    document.getElementById('pasukanSukan').value = team.sukan_id || '';
    if (document.getElementById('pasukanStatus')) {
        document.getElementById('pasukanStatus').value = team.status || 1;
    }
    
    // Load pengurus
    resetPengurusContainer();
    const pengurusContainer = document.getElementById('pengurusContainer');
    if (team.pengurus && team.pengurus.length > 0) {
        pengurusContainer.innerHTML = '';
        team.pengurus.forEach((p, index) => {
            const item = document.createElement('div');
            item.className = 'pengurus-item border rounded p-3 mb-3';
            item.innerHTML = `
                ${index > 0 ? '<div class="d-flex justify-content-between align-items-center mb-2"><strong>Pengurus Tambahan</strong><button type="button" class="btn btn-sm btn-outline-danger" onclick="removePengurus(this)"><i class="cil cil-trash"></i> Buang</button></div>' : ''}
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control pengurus-nama" value="${escapeHtml(p.nama || '')}" placeholder="Nama penuh">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">No. Kad Pengenalan</label>
                        <input type="text" class="form-control pengurus-ic" value="${escapeHtml(p.no_kad_pengenalan || '')}" placeholder="Contoh: 123456789012">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">No. Telefon</label>
                        <input type="text" class="form-control pengurus-phone" value="${escapeHtml(p.no_telefon || '')}" placeholder="Contoh: 012-3456789">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">E-mel</label>
                        <input type="email" class="form-control pengurus-email" value="${escapeHtml(p.emel || '')}" placeholder="Contoh: email@example.com">
                    </div>
                </div>
            `;
            pengurusContainer.appendChild(item);
        });
    }
    
    // Load jurulatih
    resetJurulatihContainer();
    const jurulatihContainer = document.getElementById('jurulatihContainer');
    if (team.jurulatih && team.jurulatih.length > 0) {
        jurulatihContainer.innerHTML = '';
        team.jurulatih.forEach((j, index) => {
            const item = document.createElement('div');
            item.className = 'jurulatih-item border rounded p-3 mb-3';
            item.innerHTML = `
                ${index > 0 ? '<div class="d-flex justify-content-between align-items-center mb-2"><strong>Jurulatih Tambahan</strong><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeJurulatih(this)"><i class="cil cil-trash"></i> Buang</button></div>' : ''}
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control jurulatih-nama" value="${escapeHtml(j.nama || '')}" placeholder="Nama penuh">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">No. Kad Pengenalan</label>
                        <input type="text" class="form-control jurulatih-ic" value="${escapeHtml(j.no_kad_pengenalan || '')}" placeholder="Contoh: 123456789012">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">No. Telefon</label>
                        <input type="text" class="form-control jurulatih-phone" value="${escapeHtml(j.no_telefon || '')}" placeholder="Contoh: 012-3456789">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">E-mel</label>
                        <input type="email" class="form-control jurulatih-email" value="${escapeHtml(j.emel || '')}" placeholder="Contoh: email@example.com">
                    </div>
                </div>
            `;
            jurulatihContainer.appendChild(item);
        });
    }
    
    // Load atlet grouped by category
    resetKategoriContainer();
    const kategoriContainer = document.getElementById('kategoriContainer');
    
    if (team.atlet && team.atlet.length > 0) {
        // First load categories for the sport
        loadCategoriesForSport(team.sukan_id).then(() => {
            // Group athletes by kategori_id
            const athletesByCategory = {};
            team.atlet.forEach(a => {
                const kategoriId = a.kategori_id || 'uncategorized';
                if (!athletesByCategory[kategoriId]) {
                    athletesByCategory[kategoriId] = [];
                }
                athletesByCategory[kategoriId].push(a);
            });
            
            // Create a category section for each unique kategori_id
            Object.keys(athletesByCategory).forEach(kategoriId => {
                if (kategoriId === 'uncategorized') return; // Skip uncategorized
                
                const kategoriSection = document.createElement('div');
                kategoriSection.className = 'kategori-section border rounded p-3 mb-3';
                
                // Get category name
                let categoryName = '';
                const categorySelect = document.querySelector('.kategori-select');
                if (categorySelect) {
                    const option = categorySelect.querySelector(`option[value="${kategoriId}"]`);
                    if (option) {
                        categoryName = option.textContent;
                    }
                }
                
                kategoriSection.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pilih Kategori <span class="text-danger">*</span></label>
                            <select class="form-select kategori-select" required onchange="loadCategoryOptions(this)">
                                <option value="">Sila Pilih Kategori</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeKategoriSection(this)">
                            <i class="cil cil-trash"></i> Buang Kategori
                        </button>
                    </div>
                    <div class="atlet-list" data-kategori-id="${kategoriId}">
                        <div class="small text-muted mb-2">Senarai Atlet untuk kategori ini:</div>
                        <div class="atlet-items"></div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addAtletToKategori(this)">
                            <i class="cil cil-plus"></i> Tambah Atlet
                        </button>
                    </div>
                `;
                
                kategoriContainer.appendChild(kategoriSection);
                
                // Populate category select
                const select = kategoriSection.querySelector('.kategori-select');
                populateCategorySelect(select).then(() => {
                    // Set selected category
                    if (select) {
                        select.value = kategoriId;
                        loadCategoryOptions(select);
                    }
                    
                    // Add athletes to this category section
                    const atletItems = kategoriSection.querySelector('.atlet-items');
                    athletesByCategory[kategoriId].forEach(a => {
                        const atletItem = document.createElement('div');
                        atletItem.className = 'atlet-item border rounded p-2 mb-2 bg-light';
                        atletItem.innerHTML = `
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label small">Nama <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm atlet-nama" value="${escapeHtml(a.nama || '')}" placeholder="Nama penuh" required>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label small">No. Kad Pengenalan</label>
                                    <input type="text" class="form-control form-control-sm atlet-ic" value="${escapeHtml(a.no_kad_pengenalan || '')}" placeholder="Contoh: 123456789012">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label small">No. Matrik</label>
                                    <input type="text" class="form-control form-control-sm atlet-matrik" value="${escapeHtml(a.no_matrik || '')}" placeholder="Contoh: ABC123456">
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAtlet(this)">
                                <i class="cil cil-trash"></i> Buang
                            </button>
                        `;
                        atletItems.appendChild(atletItem);
                    });
                });
            });
        });
    }
}

function deletePasukan(id) {
    if (window.Swal) {
        Swal.fire({
            title: 'Padam pasukan?',
            text: 'Pasukan akan dipadam dan tidak boleh dipulihkan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Padam',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.showLoading();
                
                const formData = new FormData();
                formData.append('id', id);
                
                fetch('<?php echo url("ajax/pasukan_delete.php"); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Reload list, then show success message
                        loadPasukanList();
                        Swal.fire({
                            text: data.message || 'Pasukan berjaya dipadam.',
                            icon: 'success'
                        });
                    } else {
                        Swal.fire({
                            text: data.message || 'Ralat memadam pasukan.',
                            icon: 'error'
                        });
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    Swal.fire({
                        text: 'Ralat memadam pasukan. Sila cuba lagi.',
                        icon: 'error'
                    });
                });
            }
        });
    } else {
        // Fallback to confirm if SweetAlert not available
        if (!confirm('Adakah anda pasti mahu memadam pasukan ini?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('id', id);
        
        fetch('<?php echo url("ajax/pasukan_delete.php"); ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Pasukan berjaya dipadam.');
                loadPasukanList();
            } else {
                alert(data.message || 'Ralat memadam pasukan.');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Ralat memadam pasukan. Sila cuba lagi.');
        });
    }
}

function loadPasukanList() {
    const tbody = document.getElementById('pasukanTableBody');
    if (!tbody) return;
    
    const filterContingent = document.getElementById('filterContingent')?.value || '';
    const filterSport = document.getElementById('filterSport')?.value || '';
    const search = document.getElementById('pasukanSearch')?.value || '';
    
    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Memuatkan data...</td></tr>';
    
    let url = '<?php echo url("ajax/pasukan_list.php"); ?>?';
    if (filterContingent) url += 'kontinjen_id=' + encodeURIComponent(filterContingent) + '&';
    if (filterSport) url += 'sukan_id=' + encodeURIComponent(filterSport) + '&';
    if (search) url += 'search=' + encodeURIComponent(search) + '&';
    
    fetch(url)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const teams = data.data || [];
            const stats = data.stats || {total: 0, active: 0, inactive: 0};
            
            // Update statistics
            const heroSection = document.querySelector('.card.bg-light .d-none.d-md-flex');
            if (heroSection) {
                const statDivs = heroSection.querySelectorAll('.me-3 .h5.mb-0');
                if (statDivs.length >= 3) {
                    statDivs[0].textContent = stats.total || 0;
                    statDivs[1].textContent = stats.active || 0;
                    statDivs[2].textContent = stats.inactive || 0;
                }
            }
            
            if (teams.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-5"><i class="cil cil-people" style="font-size: 2rem;"></i><p class="mt-2">Tiada pasukan didaftarkan — klik "Daftar Pasukan Baru" untuk mula menambah.</p></td></tr>';
            } else {
                let html = '';
                teams.forEach((t, i) => {
                    const status = parseInt(t.status || 0);
                    const badgeClass = status == 1 ? 'bg-success' : 'bg-secondary';
                    const statusText = status == 1 ? 'Aktif' : 'Tidak Aktif';
                    
                    const pengurusList = (t.pengurus_list || '').split(', ').filter(x => x);
                    const jurulatihList = (t.jurulatih_list || '').split(', ').filter(x => x);
                    
                    html += '<tr>';
                    html += '<td>' + (i + 1) + '</td>';
                    html += '<td><div class="fw-semibold">' + escapeHtml(t.nama_pasukan || '-') + '</div></td>';
                    html += '<td>' + escapeHtml(t.nama_universiti || '-') + '</td>';
                    html += '<td>' + escapeHtml(t.nama_sukan || '-') + '</td>';
                    html += '<td><div class="small">' + escapeHtml(pengurusList.slice(0, 2).join(', ')) + (pengurusList.length > 2 ? '...' : '') + '</div></td>';
                    html += '<td><div class="small">' + escapeHtml(jurulatihList.slice(0, 2).join(', ')) + (jurulatihList.length > 2 ? '...' : '') + '</div></td>';
                    html += '<td class="text-center"><span class="badge bg-info">' + (parseInt(t.atlet_count || 0)) + '</span></td>';
                    html += '<td><span class="badge ' + badgeClass + '">' + statusText + '</span></td>';
                    html += '<td>';
                    html += '<a class="btn btn-sm btn-outline-primary edit-pasukan" title="Edit" href="#" data-id="' + (t.id || 0) + '"><i class="fa fa-edit"></i></a> ';
                    html += '<a class="btn btn-sm btn-outline-danger delete-pasukan" title="Padam" href="#" data-id="' + (t.id || 0) + '"><i class="fa fa-trash"></i></a>';
                    html += '</td>';
                    html += '</tr>';
                });
                tbody.innerHTML = html;
            }
        } else {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-3">Ralat memuatkan data. Sila muat semula halaman.</td></tr>';
            if (window.Swal) {
                Swal.fire({
                    text: 'Ralat memuatkan data pasukan.',
                    icon: 'error'
                });
            }
        }
    })
    .catch(err => {
        console.error('Error:', err);
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-3">Ralat sambungan. Sila muat semula halaman.</td></tr>';
        if (window.Swal) {
            Swal.fire({
                text: 'Ralat sambungan. Sila muat semula halaman.',
                icon: 'error'
            });
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Bulk Upload Functions
let bulkUploadModalInstance = null;
let selectedFile = null;

function showBulkUploadPasukan() {
    const modalEl = document.getElementById('bulkUploadPasukanModal');
    if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);

    if (typeof coreui !== 'undefined' && coreui.Modal) {
        bulkUploadModalInstance = new coreui.Modal(modalEl, {backdrop:true,keyboard:true,focus:true});
        bulkUploadModalInstance.show();
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        bulkUploadModalInstance = new bootstrap.Modal(modalEl, {backdrop:true,keyboard:true,focus:true});
        bulkUploadModalInstance.show();
    } else {
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        document.body.classList.add('modal-open');
    }
    
    // Reset form
    resetBulkUploadForm();
}

function closeBulkUploadPasukanModal() {
    const modalEl = document.getElementById('bulkUploadPasukanModal');
    if (bulkUploadModalInstance && typeof bulkUploadModalInstance.hide === 'function') {
        bulkUploadModalInstance.hide();
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
    
    resetBulkUploadForm();
}

function resetBulkUploadForm() {
    document.getElementById('csvFileInput').value = '';
    document.getElementById('filePreview').classList.add('d-none');
    document.getElementById('uploadProgress').classList.add('d-none');
    document.getElementById('uploadResults').classList.add('d-none');
    document.getElementById('uploadBtn').disabled = true;
    selectedFile = null;
}

function downloadTemplate() {
    window.location.href = '<?php echo url("ajax/pasukan_template.php"); ?>';
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) {
        resetBulkUploadForm();
        return;
    }
    
    // Validate file type
    if (!file.name.toLowerCase().endsWith('.csv')) {
        if (window.Swal) {
            Swal.fire({
                text: 'Hanya fail CSV dibenarkan.',
                icon: 'error'
            });
        } else {
            alert('Hanya fail CSV dibenarkan.');
        }
        resetBulkUploadForm();
        return;
    }
    
    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        if (window.Swal) {
            Swal.fire({
                text: 'Saiz fail melebihi 5MB. Sila pilih fail yang lebih kecil.',
                icon: 'error'
            });
        } else {
            alert('Saiz fail melebihi 5MB. Sila pilih fail yang lebih kecil.');
        }
        resetBulkUploadForm();
        return;
    }
    
    selectedFile = file;
    
    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        const text = e.target.result;
        const lines = text.split('\n').slice(0, 20); // Show first 20 lines
        const previewContent = document.getElementById('previewContent');
        previewContent.innerHTML = '<pre class="mb-0">' + escapeHtml(lines.join('\n')) + (text.split('\n').length > 20 ? '\n...' : '') + '</pre>';
        document.getElementById('filePreview').classList.remove('d-none');
        document.getElementById('uploadBtn').disabled = false;
    };
    reader.readAsText(file);
}

function uploadBulkPasukan() {
    if (!selectedFile) {
        if (window.Swal) {
            Swal.fire({
                text: 'Sila pilih fail CSV terlebih dahulu.',
                icon: 'warning'
            });
        } else {
            alert('Sila pilih fail CSV terlebih dahulu.');
        }
        return;
    }
    
    // Show progress
    document.getElementById('uploadProgress').classList.remove('d-none');
    document.getElementById('uploadResults').classList.add('d-none');
    document.getElementById('uploadBtn').disabled = true;
    
    // Create FormData
    const formData = new FormData();
    formData.append('csv_file', selectedFile);
    
    // Upload file
    fetch('<?php echo url("ajax/pasukan_bulk_upload.php"); ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('uploadProgress').classList.add('d-none');
        document.getElementById('uploadBtn').disabled = false;
        
        // Show results
        const resultsDiv = document.getElementById('uploadResults');
        const resultsAlert = document.getElementById('resultsAlert');
        const resultsContent = document.getElementById('resultsContent');
        
        resultsDiv.classList.remove('d-none');
        
        if (data.success) {
            resultsAlert.className = 'alert alert-success';
            let html = `<p><strong>Berjaya:</strong> ${data.success_count} daripada ${data.total} pasukan berjaya dimuat naik.</p>`;
            
            if (data.failed_count > 0) {
                html += `<p><strong>Gagal:</strong> ${data.failed_count} pasukan gagal dimuat naik.</p>`;
                if (data.errors && data.errors.length > 0) {
                    html += '<ul class="mb-0">';
                    data.errors.forEach(error => {
                        html += `<li><strong>Pasukan "${escapeHtml(error.team_name)}"</strong> (Baris ${error.team_index}): ${escapeHtml(error.error)}`;
                        if (error.team_data && error.team_data.atlet_data && error.team_data.atlet_data.length > 0) {
                            html += '<br><small class="text-muted">Data atlet yang gagal:</small><ul class="small">';
                            error.team_data.atlet_data.forEach((atlet, idx) => {
                                html += `<li>Atlet ${idx + 1}: nama="${escapeHtml(atlet.nama)}", IC="${escapeHtml(atlet.ic)}" (panjang: ${atlet.ic_length}), matrik="${escapeHtml(atlet.matrik)}", kategori_id=${atlet.kategori_id || 'null'}</li>`;
                            });
                            html += '</ul>';
                        }
                        html += '</li>';
                    });
                    html += '</ul>';
                }
            }
            
            resultsContent.innerHTML = html;
            
            // Reload list after short delay
            setTimeout(() => {
                loadPasukanList();
            }, 1000);
        } else {
            resultsAlert.className = 'alert alert-danger';
            let html = `<p><strong>Gagal:</strong> ${data.message || 'Ralat memuat naik fail.'}</p>`;
            
            if (data.errors && data.errors.length > 0) {
                html += '<ul class="mb-0">';
                data.errors.forEach(error => {
                    html += `<li>Pasukan "${escapeHtml(error.team_name)}" (Baris ${error.team_index}): ${escapeHtml(error.error)}</li>`;
                });
                html += '</ul>';
            }
            
            resultsContent.innerHTML = html;
        }
    })
    .catch(err => {
        document.getElementById('uploadProgress').classList.add('d-none');
        document.getElementById('uploadBtn').disabled = false;
        console.error('Error:', err);
        
        const resultsDiv = document.getElementById('uploadResults');
        const resultsAlert = document.getElementById('resultsAlert');
        const resultsContent = document.getElementById('resultsContent');
        
        resultsDiv.classList.remove('d-none');
        resultsAlert.className = 'alert alert-danger';
        resultsContent.innerHTML = '<p>Ralat menyambung ke pelayan. Sila cuba lagi.</p>';
        
        if (window.Swal) {
            Swal.fire({
                text: 'Ralat memuat naik fail. Sila cuba lagi.',
                icon: 'error'
            });
        } else {
            alert('Ralat memuat naik fail. Sila cuba lagi.');
        }
    });
}
</script>
