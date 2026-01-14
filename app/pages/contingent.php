<?php
/**
 * Contingent Management Page
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../api/models/ContingentModel.php';

$page_title = 'Kontinjen';

// Get current user role for status control
Session::start();
$auth = getAuth();
$currentUserRole = Session::get('user_role') ?? '';
$canChangeStatus = in_array($currentUserRole, ['ADMIN', 'ORGANIZER']);

// Fetch universities from database
$universities = [];
try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT kod_universiti, nama_universiti FROM table_ref_universiti where status = 1 ORDER BY nama_universiti ASC");
    $universities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('[contingent.php] DB error fetching universities: ' . $e->getMessage());
    // Continue with empty array if database query fails
}

// Fetch contingents from database
$contingents = [];
$contingentStats = ['total' => 0, 'active' => 0, 'inactive' => 0];
try {
    $contingentModel = new ContingentModel();
    $result = $contingentModel->getAll(['limit' => 1000]);
    if ($result['success']) {
        $contingents = $result['data'];
    }
    
    $statsResult = $contingentModel->getStatistics();
    if ($statsResult['success']) {
        $contingentStats = $statsResult['data'];
    }
} catch (Exception $e) {
    error_log('[contingent.php] DB error fetching contingents: ' . $e->getMessage());
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
                        <h2 class="mb-1">Kontinjen</h2>
                        <p class="text-muted mb-0">Urus pendaftaran kontinjen — ringkasan dan tindakan pantas</p>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <div class="d-none d-md-flex">
                            <div class="me-3 text-center">
                                <div class="h5 mb-0"><?php echo (int)$contingentStats['total']; ?></div>
                                <div class="small text-muted">Kontinjen</div>
                            </div>
                            <div class="me-3 text-center">
                                <div class="h5 mb-0"><?php echo (int)$contingentStats['active']; ?></div>
                                <div class="small text-muted">Aktif</div>
                            </div>
                            <div class="me-3 text-center">
                                <div class="h5 mb-0"><?php echo (int)$contingentStats['inactive']; ?></div>
                                <div class="small text-muted">Tidak Aktif</div>
                            </div>
                        </div>

                        <div class="btn-group">
                            <button class="btn btn-outline-secondary">Laporan</button>
                            <button class="btn btn-primary" onclick="showRegistrationForm()">
                                <i class="cil cil-plus me-1"></i> Daftar Kontinjen Baru
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Form Modal/Wizard -->
    <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true" data-coreui-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registrationModalLabel">Pendaftaran Kontinjen Baru</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close" onclick="confirmCancel()"></button>
                </div>
                <div class="modal-body">
                    <!-- Registration Form -->
                    <form id="contingentForm">
                        <input type="hidden" id="contingentId" name="id" value="">
                        <div class="registration-step" id="step1" data-step="1">
                            <div class="mb-3">
                                <label for="institution" class="form-label">INSTITUSI <span class="text-danger">*</span></label>
                                <select class="form-select" id="institution" name="institution" required>
                                    <option value="" disabled selected>Sila pilih institusi...</option>
                                <?php foreach ($universities as $university): ?>
                                    <option value="<?php echo htmlspecialchars($university['kod_universiti'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($university['nama_universiti'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Sila pilih institusi</div>
                        </div>

                        <div class="mb-3">
                            <label for="contactOfficerName" class="form-label">NAMA PEGAWAI UNTUK DIHUBUNGI <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contactOfficerName" name="contactOfficerName" 
                                   placeholder="Masukkan nama pegawai untuk dihubungi" maxlength="100" autocomplete="name" required>
                            <div class="invalid-feedback">Nama pegawai diperlukan (minimum 3 aksara)</div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">ALAMAT <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address" name="address" rows="4" 
                                      placeholder="Masukkan alamat lengkap" maxlength="500" autocomplete="street-address" required></textarea>
                            <div class="invalid-feedback">Alamat diperlukan (minimum 10 aksara)</div>
                            <div class="form-text">
                                <span id="addressCharCount">0</span> / 500 aksara
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">EMEL <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="cth: contoh@email.com" autocomplete="email" required>
                            <div class="invalid-feedback">Format e-mel tidak sah</div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">NO TELEFON</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   placeholder="cth: 012-3456789 atau 03-12345678" autocomplete="tel">
                            <div class="invalid-feedback">Format telefon tidak sah</div>
                        </div>

                        <?php if ($canChangeStatus): ?>
                        <div class="mb-3">
                            <label for="status" class="form-label">STATUS <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="1">Aktif</option>
                                <option value="0" selected>Tidak Aktif</option>
                            </select>
                            <div class="invalid-feedback">Sila pilih status</div>
                        </div>
                        <?php endif; ?>
                        </div>
                    </form>

                    <!-- Error Summary -->
                    <div class="alert alert-danger d-none" id="errorSummary">
                        <strong><i class="cil cil-warning me-1"></i> Sila betulkan ralat berikut:</strong>
                        <ul class="mb-0 mt-2" id="errorList"></ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="confirmCancel()">
                        Batal
                    </button>
                    <button type="button" class="btn btn-success" id="submitButton" onclick="submitRegistration()">
                        <i class="cil cil-check me-1"></i> Hantar Pendaftaran
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contingent List -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Senarai Kontinjen</strong>
                        <div class="small text-muted">Urus semua kontinjen yang didaftarkan</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm me-2" style="min-width:220px;">
                            <span class="input-group-text"><i class="cil cil-magnifying-glass"></i></span>
                            <input type="search" class="form-control" id="contingentSearch" placeholder="Cari nama atau kod...">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width:70px;">#</th>
                                    <th scope="col">Nama Universiti</th>
                                    <th scope="col">Kod</th>
                                    <th scope="col">Pegawai Untuk Dihubungi</th>
                                    <th scope="col" style="width:140px;">Jumlah Atlet</th>
                                    <th scope="col" style="width:120px;">Status</th>
                                    <th scope="col" style="width:160px;">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="contingentTableBody">
                                <?php if (empty($contingents)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="cil cil-info" style="font-size: 2rem;"></i>
                                            <p class="mt-2">Tiada kontinjen didaftarkan — klik "Daftar Kontinjen Baru" untuk mula mendaftar.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($contingents as $i => $c): ?>
                                        <tr>
                                            <td><?php echo $i + 1; ?></td>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($c['nama_universiti'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($c['kod_universiti'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div class="small">
                                                    <div class="fw-semibold"><?php echo htmlspecialchars($c['nama_pegawai_untuk_dihubungi'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <?php if (!empty($c['emel'])): ?>
                                                    <div class="text-muted small">
                                                        <a href="mailto:<?php echo htmlspecialchars($c['emel'], ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?php echo htmlspecialchars($c['emel'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center">-</td>
                                            <td>
                                                <?php
                                                $status = isset($c['status']) ? (int)$c['status'] : 0;
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
                                                <a class="btn btn-sm btn-outline-primary edit-contingent" title="Edit" href="#"
                                                   data-id="<?php echo (int)$c['id']; ?>"
                                                   data-kod="<?php echo htmlspecialchars($c['kod_universiti'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-nama="<?php echo htmlspecialchars($c['nama_pegawai_untuk_dihubungi'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-alamat="<?php echo htmlspecialchars($c['alamat'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-emel="<?php echo htmlspecialchars($c['emel'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-phone="<?php echo htmlspecialchars($c['no_telefon'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-status="<?php echo (int)$status; ?>"
                                                >
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a class="btn btn-sm btn-outline-danger delete-contingent" title="Padam" href="#" data-id="<?php echo (int)$c['id']; ?>">
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

<script>
// Initialize modal fixes on page load
document.addEventListener('DOMContentLoaded', function() {
    // Ensure fixModalZIndex is available globally for this page
    if (typeof fixModalZIndex === 'undefined') {
        // Create a local version if global doesn't exist
            window.fixModalZIndex = function() {
            const modals = document.querySelectorAll('.modal.show');
            modals.forEach(modal => {
                // INCREASED: modal z-index 1060 (MUST be above navbar/header 1000)
                modal.style.zIndex = '1060';
                modal.style.position = 'fixed';
                
                // CRITICAL: Ensure modal is always above navbar/header
                const navbar = document.querySelector('.navbar');
                const header = document.querySelector('.header, .header-sticky');
                if (navbar) {
                    navbar.style.zIndex = '1000';
                }
                if (header) {
                    header.style.zIndex = '1000';
                }
                
                // Modal-dialog and modal-content inherit from modal container
                // No separate z-index needed (Bootstrap standard)
                const dialog = modal.querySelector('.modal-dialog');
                if (dialog) {
                    dialog.style.pointerEvents = 'auto';
                    dialog.style.zIndex = ''; // Remove non-standard z-index
                }
                
                const content = modal.querySelector('.modal-content');
                if (content) {
                    content.style.pointerEvents = 'auto';
                    content.style.zIndex = ''; // Remove non-standard z-index
                }
            });
            
            // Ensure all backdrops follow Bootstrap standard z-index
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach((backdrop, index) => {
                // Standard Bootstrap: backdrop z-index 1040
                backdrop.style.zIndex = '1040';
                backdrop.style.position = 'fixed';
                
                // CRITICAL: Remove duplicate backdrops (only first one should exist)
                if (index > 0) {
                    backdrop.remove();
                }
            });
            // Remove duplicate backdrops
            if (backdrops.length > 1) {
                for (let i = 1; i < backdrops.length; i++) {
                    backdrops[i].remove();
                }
            }
        };
    }
    
    // Monitor for modal backdrop and fix z-index continuously
    const modalCheckInterval = setInterval(function() {
        const visibleModals = document.querySelectorAll('.modal.show');
        if (visibleModals.length > 0) {
            if (typeof fixModalZIndex === 'function') {
                fixModalZIndex();
            }
        }
    }, 500);
    
    // Stop monitoring when page unloads
    window.addEventListener('beforeunload', function() {
        clearInterval(modalCheckInterval);
    });
    
    // Also listen for modal show events
    document.addEventListener('show.coreui.modal', function() {
        setTimeout(function() {
            if (typeof fixModalZIndex === 'function') {
                fixModalZIndex();
            }
        }, 10);
    });
    
    document.addEventListener('shown.coreui.modal', function() {
        if (typeof fixModalZIndex === 'function') {
            fixModalZIndex();
        }
        setTimeout(function() {
            if (typeof fixModalZIndex === 'function') {
                fixModalZIndex();
            }
        }, 50);
    });
});

// Registration Form State
let currentStep = 1;
const totalSteps = 1;
let formData = {};

// Reload datatable function
function reloadContingentTable(callback) {
    const tbody = document.getElementById('contingentTableBody');
    if (!tbody) {
        if (callback) callback();
        return;
    }
    
    // Show loading state
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-3"><span class="spinner-border spinner-border-sm me-2"></span>Memuatkan data...</td></tr>';
    
    fetch('<?php echo url("ajax/contingent_list.php"); ?>', {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    })
    .then(function(res) { return res.json(); })
    .then(function(json) {
        if (json && json.success) {
            const contingents = json.data || [];
            const stats = json.stats || {total: 0, active: 0, inactive: 0};
            
            // Update statistics in hero section
            const heroSection = document.querySelector('.card.bg-light .d-none.d-md-flex');
            if (heroSection) {
                const statDivs = heroSection.querySelectorAll('.me-3 .h5.mb-0');
                if (statDivs.length >= 3) {
                    statDivs[0].textContent = stats.total || 0;
                    statDivs[1].textContent = stats.active || 0;
                    statDivs[2].textContent = stats.inactive || 0;
                }
            }
            
            // Update table
            if (contingents.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-5"><i class="cil cil-info" style="font-size: 2rem;"></i><p class="mt-2">Tiada kontinjen didaftarkan — klik "Daftar Kontinjen Baru" untuk mula mendaftar.</p></td></tr>';
            } else {
                let html = '';
                contingents.forEach(function(c, i) {
                    const status = c.status !== undefined ? parseInt(c.status) : 0;
                    const badgeClass = status == 1 ? 'bg-success' : 'bg-secondary';
                    const statusText = status == 1 ? 'Aktif' : 'Tidak Aktif';
                    
                    html += '<tr>';
                    html += '<td>' + (i + 1) + '</td>';
                    html += '<td><div class="fw-semibold">' + escapeHtml(c.nama_universiti || '-') + '</div></td>';
                    html += '<td>' + escapeHtml(c.kod_universiti || '') + '</td>';
                    html += '<td><div class="small">';
                    html += '<div class="fw-semibold">' + escapeHtml(c.nama_pegawai_untuk_dihubungi || '-') + '</div>';
                    if (c.emel) {
                        html += '<div class="text-muted small"><a href="mailto:' + escapeHtml(c.emel) + '">' + escapeHtml(c.emel) + '</a></div>';
                    }
                    html += '</div></td>';
                    html += '<td class="text-center">-</td>';
                    html += '<td><span class="badge ' + badgeClass + '">' + statusText + '</span></td>';
                    html += '<td>';
                    html += '<a class="btn btn-sm btn-outline-primary edit-contingent" title="Edit" href="#" ';
                    html += 'data-id="' + (c.id || 0) + '" ';
                    html += 'data-kod="' + escapeHtml(c.kod_universiti || '') + '" ';
                    html += 'data-nama="' + escapeHtml(c.nama_pegawai_untuk_dihubungi || '') + '" ';
                    html += 'data-alamat="' + escapeHtml(c.alamat || '') + '" ';
                    html += 'data-emel="' + escapeHtml(c.emel || '') + '" ';
                    html += 'data-phone="' + escapeHtml(c.no_telefon || '') + '" ';
                    html += 'data-status="' + status + '">';
                    html += '<i class="fa fa-edit"></i></a> ';
                    html += '<a class="btn btn-sm btn-outline-danger delete-contingent" title="Padam" href="#" data-id="' + (c.id || 0) + '">';
                    html += '<i class="fa fa-trash"></i></a>';
                    html += '</td>';
                    html += '</tr>';
                });
                tbody.innerHTML = html;
            }
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-3">Ralat memuatkan data. Sila muat semula halaman.</td></tr>';
        }
        
        // Execute callback after reload completes
        if (callback) callback();
    })
    .catch(function(err) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-3">Ralat sambungan. Sila muat semula halaman.</td></tr>';
        if (callback) callback();
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Global modal instance for registration modal
let registrationModalInstance = null;

// Local cleanupModalBackdrops function if not available globally
if (typeof cleanupModalBackdrops !== 'function') {
    function cleanupModalBackdrops() {
        // Remove all but one backdrop (CoreUI should manage this, but we'll ensure)
        const backdrops = document.querySelectorAll('.modal-backdrop');
        
        // If multiple backdrops exist, remove extras
        if (backdrops.length > 1) {
            for (let i = 1; i < backdrops.length; i++) {
                backdrops[i].remove();
            }
        }
        
        // Wait a bit for CoreUI to finish its animation
        setTimeout(() => {
            // Check if any modals are still showing
            const visibleModals = document.querySelectorAll('.modal.show');
            
            if (visibleModals.length === 0) {
                // No modals are showing, remove all backdrops
                backdrops.forEach(backdrop => {
                    backdrop.remove();
                });
                
                // Remove modal-open class and restore body styles
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            } else {
                // Modals are showing, ensure only one backdrop exists
                const remainingBackdrops = document.querySelectorAll('.modal-backdrop');
                if (remainingBackdrops.length > 1) {
                    for (let i = 1; i < remainingBackdrops.length; i++) {
                        remainingBackdrops[i].remove();
                    }
                }
            }
        }, 150); // Small delay to allow CoreUI animations to complete
    }
}

// Show registration form with optional data for editing
function showRegistrationForm(data = null) {
    // Reset form
    document.getElementById('contingentForm').reset();
    document.getElementById('contingentId').value = '';
    document.getElementById('registrationModalLabel').textContent = 'Pendaftaran Kontinjen Baru';
    
    // If data provided, populate form for editing
    if (data && data.id) {
        document.getElementById('contingentId').value = data.id || '';
        document.getElementById('institution').value = data.kod || '';
        document.getElementById('contactOfficerName').value = data.nama || '';
        document.getElementById('address').value = data.alamat || '';
        document.getElementById('email').value = data.emel || '';
        document.getElementById('phone').value = data.phone || '';
        if (document.getElementById('status')) {
            document.getElementById('status').value = data.status !== undefined ? data.status : '0';
        }
        document.getElementById('registrationModalLabel').textContent = 'Sunting Kontinjen';
        
        // Remove disabled/selected from placeholder option
        const institutionSelect = document.getElementById('institution');
        const placeholderOption = institutionSelect.querySelector('option[value=""]');
        if (placeholderOption) {
            placeholderOption.removeAttribute('disabled');
            placeholderOption.removeAttribute('selected');
        }
    }
    // CRITICAL: Close any existing modals first to prevent stacking
    if (typeof closeAllModals === 'function') {
        closeAllModals();
    } else {
        // Manual cleanup
        const visibleModals = document.querySelectorAll('.modal.show');
        visibleModals.forEach(modal => {
            const instance = coreui?.Modal?.getInstance(modal) || bootstrap?.Modal?.getInstance(modal);
            if (instance) {
                instance.hide();
            } else {
                modal.classList.remove('show');
                modal.style.display = 'none';
            }
        });
        if (typeof cleanupModalBackdrops === 'function') {
            cleanupModalBackdrops();
        } else {
            // Local cleanup if function not available
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(b => b.remove());
            document.body.classList.remove('modal-open');
        }
    }
    
    // Hide loading overlay before showing modal
    if (typeof hideLoadingOverlayForModal === 'function') {
        hideLoadingOverlayForModal();
    }
    
    // Load saved data if exists
    loadFormData();
    
    // Reset to step 1
    currentStep = 1;
    updateStepDisplay();
    
    // Get or create modal instance
    const modalElement = document.getElementById('registrationModal');
    
    // Always get or create fresh instance
    if (registrationModalInstance) {
        // Dispose old instance if exists
        try {
            registrationModalInstance.dispose();
        } catch (e) {
            // Ignore if already disposed
        }
    }
    
    // CRITICAL: Move modal to body level to avoid stacking context issues
    if (modalElement.parentElement !== document.body) {
        document.body.appendChild(modalElement);
    }
    
    // Create modal instance: prefer CoreUI, fallback to Bootstrap, final manual fallback
    if (typeof coreui !== 'undefined' && coreui.Modal) {
        registrationModalInstance = new coreui.Modal(modalElement, {
            backdrop: true,
            keyboard: true,
            focus: true
        });

        // Hide loading overlay when modal is shown (CoreUI event)
        modalElement.addEventListener('show.coreui.modal', function() {
            if (typeof hideLoadingOverlayForModal === 'function') {
                hideLoadingOverlayForModal();
            }
            if (typeof fixModalZIndex === 'function') {
                fixModalZIndex();
            }
        });

        // Clean up backdrop on hidden (CoreUI event)
        modalElement.addEventListener('hidden.coreui.modal', function() {
            if (typeof cleanupModalBackdrops === 'function') {
                cleanupModalBackdrops();
            }
        });

    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        registrationModalInstance = new bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true,
            focus: true
        });

        // Bootstrap events
        modalElement.addEventListener('show.bs.modal', function() {
            if (typeof hideLoadingOverlayForModal === 'function') {
                hideLoadingOverlayForModal();
            }
            if (typeof fixModalZIndex === 'function') {
                fixModalZIndex();
            }
        });

        modalElement.addEventListener('hidden.bs.modal', function() {
            if (typeof cleanupModalBackdrops === 'function') {
                cleanupModalBackdrops();
            }
        });

    } else {
        // Final fallback: simple class toggle if no modal library available
        modalElement.classList.add('show');
        modalElement.style.display = 'block';
        document.body.classList.add('modal-open');
        if (typeof hideLoadingOverlayForModal === 'function') {
            hideLoadingOverlayForModal();
        }
        if (typeof fixModalZIndex === 'function') {
            fixModalZIndex();
        }
    }
    
    // CRITICAL: Ensure modal is at body level before showing
    if (modalElement.parentElement !== document.body) {
        document.body.appendChild(modalElement);
    }
    
    // Show modal (if library instance exists), otherwise fallback already showed it
    if (registrationModalInstance && typeof registrationModalInstance.show === 'function') {
        registrationModalInstance.show();
    }
    
    // CRITICAL: Force z-index immediately and continuously
    modalElement.style.zIndex = '1060';
    modalElement.style.position = 'fixed';
    
    // Ensure navbar/header are below modal
    const navbar = document.querySelector('.navbar');
    const header = document.querySelector('.header, .header-sticky');
    if (navbar) navbar.style.zIndex = '1000';
    if (header) header.style.zIndex = '1000';
    
    // Fix z-index after showing
    setTimeout(function() {
        modalElement.style.zIndex = '1060';
        modalElement.style.position = 'fixed';
        if (navbar) navbar.style.zIndex = '1000';
        if (header) header.style.zIndex = '1000';
        if (typeof fixModalZIndex === 'function') {
            fixModalZIndex();
        }
    }, 50);
    
    // Continuous monitoring to ensure z-index is maintained
    const zIndexInterval = setInterval(function() {
        if (modalElement.classList.contains('show')) {
            modalElement.style.zIndex = '1060';
            modalElement.style.position = 'fixed';
            if (navbar) navbar.style.zIndex = '1000';
            if (header) header.style.zIndex = '1000';
        } else {
            clearInterval(zIndexInterval);
        }
    }, 100);
}

// Load form data from localStorage
function loadFormData() {
    const saved = localStorage.getItem('contingentRegistrationData');
    if (saved) {
        formData = JSON.parse(saved);
        // Populate form fields
        Object.keys(formData).forEach(key => {
            const field = document.getElementById(key);
            if (field) {
                field.value = formData[key];
            }
        });
    }
}

// Save form data to localStorage
function saveFormData() {
    const fields = [
        'institution', 'contactOfficerName', 'address', 'email', 'phone'
    ];
    
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            formData[fieldId] = field.value;
        }
    });
    
    localStorage.setItem('contingentRegistrationData', JSON.stringify(formData));
}

// Update step display
function updateStepDisplay() {
    // Hide all steps
    document.querySelectorAll('.registration-step').forEach(step => {
        step.classList.add('d-none');
    });
    
    // Show current step
    const currentStepEl = document.getElementById('step' + currentStep);
    if (currentStepEl) {
        currentStepEl.classList.remove('d-none');
    }
    
    // Update buttons - single step form, so show submit button
    const backButton = document.getElementById('backButton');
    const nextButton = document.getElementById('nextButton');
    const submitButton = document.getElementById('submitButton');
    
    if (backButton) {
        backButton.style.display = 'none';
    }
    if (nextButton) {
        nextButton.style.display = 'none';
    }
    if (submitButton) {
        submitButton.style.display = 'inline-block';
    }
}

// Validate current step
function validateStep(step) {
    let isValid = true;
    const errors = [];
    
    if (step === 1) {
        const institution = document.getElementById('institution');
        if (!institution.value) {
            isValid = false;
            institution.classList.add('is-invalid');
            errors.push('Sila pilih institusi');
        } else {
            institution.classList.remove('is-invalid');
            institution.classList.add('is-valid');
        }
        
        const contactOfficerName = document.getElementById('contactOfficerName');
        if (!contactOfficerName.value || contactOfficerName.value.length < 3) {
            isValid = false;
            contactOfficerName.classList.add('is-invalid');
            errors.push('Nama pegawai untuk dihubungi diperlukan (minimum 3 aksara)');
        } else {
            contactOfficerName.classList.remove('is-invalid');
            contactOfficerName.classList.add('is-valid');
        }
        
        const address = document.getElementById('address');
        if (!address.value || address.value.length < 10 || address.value.length > 500) {
            isValid = false;
            address.classList.add('is-invalid');
            errors.push('Alamat mesti antara 10-500 aksara');
        } else {
            address.classList.remove('is-invalid');
            address.classList.add('is-valid');
        }
        
        const email = document.getElementById('email');
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value || !emailPattern.test(email.value)) {
            isValid = false;
            email.classList.add('is-invalid');
            errors.push('Format e-mel tidak sah');
        } else {
            email.classList.remove('is-invalid');
            email.classList.add('is-valid');
        }
        
        const phone = document.getElementById('phone');
        // Phone is optional, but if provided, validate format
        // Mobile: 01X-XXXXXXX (e.g., 010-1234567, 012-3456789)
        // Landline: 0X-XXXXXXX (e.g., 03-12345678, 04-1234567)
        if (phone.value) {
            const cleanedPhone = phone.value.replace(/\s/g, '');
            // Mobile pattern: 01[0-9] followed by optional dash and 7-8 digits
            const mobilePattern = /^01[0-9]-?[0-9]{7,8}$/;
            // Landline pattern: 0[1-9] followed by optional dash and 7-9 digits
            const landlinePattern = /^0[1-9]-?[0-9]{7,9}$/;
            
            if (!mobilePattern.test(cleanedPhone) && !landlinePattern.test(cleanedPhone)) {
                isValid = false;
                phone.classList.add('is-invalid');
                errors.push('Format telefon tidak sah. Contoh: 012-3456789 (bimbit) atau 03-12345678 (talian tetap)');
            } else {
                phone.classList.remove('is-invalid');
                phone.classList.add('is-valid');
            }
        } else {
            phone.classList.remove('is-invalid', 'is-valid');
        }
    }
    
    // Show error summary
    const errorSummary = document.getElementById('errorSummary');
    const errorList = document.getElementById('errorList');
    if (!isValid && errors.length > 0) {
        errorSummary.classList.remove('d-none');
        errorList.innerHTML = errors.map(err => `<li>${err}</li>`).join('');
        // Scroll to top
        document.querySelector('.modal-body').scrollTop = 0;
    } else {
        errorSummary.classList.add('d-none');
    }
    
    return isValid;
}

// Next step (not needed for single step form, but kept for compatibility)
function nextStep() {
    if (validateStep(currentStep)) {
        saveFormData();
        submitRegistration();
    }
}

// Previous step (not needed for single step form)
function previousStep() {
    // No previous step in single step form
}

// Go to specific step (not needed for single step form)
function goToStep(step) {
    currentStep = step;
    updateStepDisplay();
}

// Submit registration
function submitRegistration() {
    if (validateStep(1)) {
        // Show loading state
        const submitBtn = document.getElementById('submitButton');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghantar...';
        
        // Collect all form data
        saveFormData();
        
        // Prepare form data
        const formData = new FormData();
        const contingentId = document.getElementById('contingentId').value;
        if (contingentId) {
            formData.append('id', contingentId);
        }
        formData.append('kod_universiti', document.getElementById('institution').value);
        formData.append('nama_pegawai_untuk_dihubungi', document.getElementById('contactOfficerName').value);
        formData.append('alamat', document.getElementById('address').value);
        formData.append('emel', document.getElementById('email').value);
        formData.append('phone', document.getElementById('phone').value);
        // Status: only ADMIN/ORGANIZER can set it, CONTINGENT always gets 0
        const statusField = document.getElementById('status');
        if (statusField) {
            formData.append('status', statusField.value);
        } else {
            formData.append('status', '0'); // CONTINGENT users always get 0
        }
        
        // Show loading with SweetAlert
        if (window.Swal) {
            Swal.showLoading();
        }
        
        // Submit via AJAX
        fetch('<?php echo url("ajax/contingent_save.php"); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData,
            headers: { 'Accept': 'application/json' }
        })
        .then(function(res) { return res.json(); })
        .then(function(json) {
            if (window.Swal) Swal.close();
            
            // Reset button state immediately
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            
            if (json && json.success) {
                // Clear saved data
                localStorage.removeItem('contingentRegistrationData');
                
                // Close modal first
                closeModal();
                
                // Reset form completely
                resetModalForm();
                
                // Reload datatable, then show success message after reload completes
                reloadContingentTable(function() {
                    // Show success message after datatable is reloaded
                    if (window.Swal) {
                        Swal.fire({
                            text: json.message || 'Kontinjen berjaya disimpan',
                            icon: 'success'
                        });
                    } else {
                        alert(json.message || 'Kontinjen berjaya disimpan');
                    }
                });
            } else {
                // Show error message
                if (window.Swal) {
                    Swal.fire({
                        text: (json && json.message) || 'Ralat menyimpan kontinjen',
                        icon: 'error'
                    });
                } else {
                    alert((json && json.message) || 'Ralat menyimpan kontinjen');
                }
            }
        })
        .catch(function(err) {
            if (window.Swal) {
                Swal.close();
                Swal.fire({
                    text: 'Ralat sambungan. Sila cuba lagi.',
                    icon: 'error'
                });
            } else {
                alert('Ralat sambungan. Sila cuba lagi.');
            }
            // Reset button state on error
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }
}

// Reset modal form completely
function resetModalForm() {
    const form = document.getElementById('contingentForm');
    if (form) {
        form.reset();
        document.getElementById('contingentId').value = '';
        document.getElementById('registrationModalLabel').textContent = 'Pendaftaran Kontinjen Baru';
        
        // Clear all validation states
        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.classList.remove('is-valid', 'is-invalid');
        });
        
        // Reset institution select placeholder
        const institutionSelect = document.getElementById('institution');
        if (institutionSelect) {
            const placeholderOption = institutionSelect.querySelector('option[value=""]');
            if (placeholderOption) {
                placeholderOption.setAttribute('disabled', 'disabled');
                placeholderOption.setAttribute('selected', 'selected');
            }
        }
        
        // Reset submit button state
        const submitBtn = document.getElementById('submitButton');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="cil cil-check me-1"></i> Hantar Pendaftaran';
        }
        
        // Reset step display
        currentStep = 1;
        updateStepDisplay();
        
        // Clear error summary
        const errorSummary = document.getElementById('errorSummary');
        if (errorSummary) {
            errorSummary.classList.add('d-none');
        }
    }
}

// Close modal helper
function closeModal() {
    const modalElement = document.getElementById('registrationModal');
    if (registrationModalInstance) {
        registrationModalInstance.hide();
    } else if (typeof coreui !== 'undefined' && coreui.Modal) {
        const modal = coreui.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
    } else {
        // Fallback: hide using class
        modalElement.classList.remove('show');
        modalElement.style.display = 'none';
        document.body.classList.remove('modal-open');
    }
    
    // Clean up any lingering backdrops
    if (typeof cleanupModalBackdrops === 'function') {
        cleanupModalBackdrops();
    }
    
    // Reset form after modal is closed
    setTimeout(function() {
        resetModalForm();
    }, 300); // Small delay to ensure modal is fully closed
}

// Confirm cancel
function confirmCancel() {
    // Immediate cancel: clear saved data and close without confirmation
    localStorage.removeItem('contingentRegistrationData');
    closeModal();
    // Form will be reset by closeModal's setTimeout
}

// Real-time validation
document.addEventListener('DOMContentLoaded', function() {
    // Address character counter
    const addressField = document.getElementById('address');
    if (addressField) {
        addressField.addEventListener('input', function() {
            const count = this.value.length;
            const charCountEl = document.getElementById('addressCharCount');
            if (charCountEl) {
                charCountEl.textContent = count;
            }
            if (count > 500) {
                this.value = this.value.substring(0, 500);
                if (charCountEl) {
                    charCountEl.textContent = 500;
                }
            }
        });
    }
    
    // Phone number formatting
    const phoneField = document.getElementById('phone');
    if (phoneField) {
        phoneField.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0) {
                // Check if mobile (starts with 01) or landline
                if (value.startsWith('01')) {
                    // Mobile: 01X-XXXXXXX
                    if (value.length <= 3) {
                        this.value = value;
                    } else if (value.length <= 10) {
                        this.value = value.substring(0, 3) + '-' + value.substring(3);
                    } else {
                        this.value = value.substring(0, 3) + '-' + value.substring(3, 10);
                    }
                } else {
                    // Landline: 0X-XXXXXXX (can be 7-9 digits after prefix)
                    if (value.length <= 2) {
                        this.value = value;
                    } else if (value.length <= 10) {
                        this.value = value.substring(0, 2) + '-' + value.substring(2);
                    } else {
                        this.value = value.substring(0, 2) + '-' + value.substring(2, 10);
                    }
                }
            }
        });
    }
    
    // Real-time validation on blur
    const allFields = document.querySelectorAll('#registrationModal input, #registrationModal select, #registrationModal textarea');
    allFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.value) {
                // Validate based on current step
                const step = currentStep;
                validateStep(step);
            }
        });
    });
    
    // Also ensure modal z-index is fixed when modal is interacted with
    const registrationModal = document.getElementById('registrationModal');
    if (registrationModal) {
        // CRITICAL: Move modal to body level on page load (Bootstrap best practice)
        // This prevents stacking context issues with parent containers
        if (registrationModal.parentElement !== document.body) {
            document.body.appendChild(registrationModal);
        }
        
        // Monitor for any changes to modal - AGGRESSIVE
        const modalObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const target = mutation.target;
                    if (target.classList.contains('modal') && target.classList.contains('show')) {
                        // Fix z-index immediately - no delay
                        target.style.zIndex = '1060';
                        target.style.position = 'fixed';
                        const dialog = target.querySelector('.modal-dialog');
                        if (dialog) {
                            dialog.style.pointerEvents = 'auto';
                            dialog.style.zIndex = ''; // Remove non-standard z-index
                        }
                        const content = target.querySelector('.modal-content');
                        if (content) {
                            content.style.pointerEvents = 'auto';
                            content.style.zIndex = ''; // Remove non-standard z-index
                        }
                        // Fix all backdrops
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        backdrops.forEach(b => {
                            b.style.zIndex = '1040';
                            b.style.position = 'fixed';
                        });
                        if (typeof fixModalZIndex === 'function') {
                            fixModalZIndex();
                        }
                    }
                }
            });
        });
        
        modalObserver.observe(registrationModal, {
            attributes: true,
            attributeFilter: ['class', 'style']
        });
    }
    
    // CRITICAL: Monitor body for backdrop creation and fix immediately
    const backdropObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('modal-backdrop')) {
                        // Backdrop was just added - ensure only one exists
                        const allBackdrops = document.querySelectorAll('.modal-backdrop');
                        
                        // If more than one backdrop exists, remove extras
                        if (allBackdrops.length > 1) {
                            // Keep the first one, remove all others
                            for (let i = 1; i < allBackdrops.length; i++) {
                                allBackdrops[i].remove();
                            }
                        }
                        
                        // Fix z-index for remaining backdrop(s)
                        const remainingBackdrops = document.querySelectorAll('.modal-backdrop');
                        remainingBackdrops.forEach(b => {
                            b.style.zIndex = '1040';
                            b.style.position = 'fixed';
                            b.style.opacity = '0.5';
                        });
                        
                        // Ensure modal is above backdrop AND navbar/header
                        const visibleModals = document.querySelectorAll('.modal.show');
                        visibleModals.forEach(modal => {
                            modal.style.zIndex = '1060';
                            modal.style.position = 'fixed';
                            
                            // CRITICAL: Ensure modal is always above navbar/header
                            const navbar = document.querySelector('.navbar');
                            const header = document.querySelector('.header, .header-sticky');
                            if (navbar) navbar.style.zIndex = '1000';
                            if (header) header.style.zIndex = '1000';
                            const dialog = modal.querySelector('.modal-dialog');
                            if (dialog) {
                                dialog.style.pointerEvents = 'auto';
                                dialog.style.zIndex = ''; // Remove non-standard z-index
                            }
                            const content = modal.querySelector('.modal-content');
                            if (content) {
                                content.style.pointerEvents = 'auto';
                                content.style.zIndex = ''; // Remove non-standard z-index
                            }
                        });
                    }
                });
            }
        });
    });
    
    // Observe body for backdrop additions
    backdropObserver.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Also continuously monitor for duplicate backdrops AND loading overlay
    setInterval(function() {
        // Check for duplicate backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        if (backdrops.length > 1) {
            // Keep only the first one
            for (let i = 1; i < backdrops.length; i++) {
                backdrops[i].remove();
            }
        }
        // Ensure backdrop z-index is correct
        backdrops.forEach(b => {
            b.style.zIndex = '1040';
            b.style.position = 'fixed';
        });
        // Ensure modal is above backdrop AND navbar/header
        const visibleModals = document.querySelectorAll('.modal.show');
        visibleModals.forEach(modal => {
            modal.style.zIndex = '1060';
            modal.style.position = 'fixed';
            
            // CRITICAL: Ensure modal is always above navbar/header
            const navbar = document.querySelector('.navbar');
            const header = document.querySelector('.header, .header-sticky');
            if (navbar) navbar.style.zIndex = '1000';
            if (header) header.style.zIndex = '1000';
        });
        
        // CRITICAL: Force hide loading overlay if any modal is open
        const hasOpenModal = visibleModals.length > 0 || backdrops.length > 0 || document.body.classList.contains('modal-open');
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (hasOpenModal && loadingOverlay) {
            // Force hide loading overlay immediately
            loadingOverlay.style.display = 'none';
            loadingOverlay.style.visibility = 'hidden';
            loadingOverlay.style.opacity = '0';
            loadingOverlay.style.zIndex = '-1';
            loadingOverlay.style.pointerEvents = 'none';
            loadingOverlay.classList.remove('show', 'hide');
            loadingOverlay.style.position = 'fixed';
            loadingOverlay.style.top = '-9999px';
            loadingOverlay.style.left = '-9999px';
        }
    }, 100); // Check every 100ms
    
    // Handle edit and delete buttons
    document.addEventListener('click', function(e) {
        // Edit button
        var editBtn = e.target.closest && e.target.closest('.edit-contingent');
        if (editBtn) {
            e.preventDefault();
            var data = {
                id: editBtn.getAttribute('data-id'),
                kod: editBtn.getAttribute('data-kod'),
                nama: editBtn.getAttribute('data-nama'),
                alamat: editBtn.getAttribute('data-alamat'),
                emel: editBtn.getAttribute('data-emel'),
                phone: editBtn.getAttribute('data-phone'),
                status: editBtn.getAttribute('data-status')
            };
            showRegistrationForm(data);
        }
        
        // Delete button
        var delBtn = e.target.closest && e.target.closest('.delete-contingent');
        if (delBtn) {
            e.preventDefault();
            var id = delBtn.getAttribute('data-id');
            if (!id) return;
            
            if (window.Swal) {
                Swal.fire({
                    title: 'Padam kontinjen?',
                    text: 'Kontinjen akan dipadam dan tidak boleh dipulihkan',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Padam',
                    cancelButtonText: 'Batal'
                }).then(function(r) {
                    if (r.isConfirmed) {
                        // Show loading
                        Swal.showLoading();
                        
                        // Call AJAX delete endpoint
                        fetch('<?php echo url("ajax/contingent_delete.php"); ?>', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: { 'Accept': 'application/json' },
                            body: new URLSearchParams({ id: id })
                        })
                        .then(function(res) { return res.json(); })
                        .then(function(json) {
                            if (json && json.success) {
                                // Reload datatable, then show success message after reload completes
                                reloadContingentTable(function() {
                                    Swal.fire({
                                        text: json.message || 'Kontinjen dipadam',
                                        icon: 'success'
                                    });
                                });
                            } else {
                                Swal.fire({
                                    text: (json && json.message) || 'Operasi tidak dibenarkan',
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(function(err) {
                            Swal.fire({
                                text: 'Ralat pelayan. Sila cuba lagi.',
                                icon: 'error'
                            });
                        });
                    }
                });
            } else {
                if (confirm('Padam kontinjen ini?')) {
                    alert('Fungsi Padam belum diaktifkan. Sila hubungi pentadbir untuk bantuan.');
                }
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>
