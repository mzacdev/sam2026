<?php
/**
 * Contingent Management Page
 */
require_once __DIR__ . '/../config.php';

$page_title = 'Kontinjen';

ob_start();
?>
<div class="w-100 px-3">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">Kontinjen</h2>
                    <p class="text-muted">Urus pendaftaran kontinjen</p>
                </div>
                <button class="btn btn-primary" onclick="showRegistrationForm()">
                    <i class="cil cil-plus me-1"></i> Daftar Kontinjen Baru
                </button>
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
                    <!-- Progress Indicator -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Langkah <span id="currentStep">1</span> daripada <span id="totalSteps">5</span></span>
                            <span class="text-muted small"><span id="progressPercent">20</span>% siap</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" id="progressBar" style="width: 20%"></div>
                        </div>
                    </div>

                    <!-- Step 1: Institution Selection -->
                    <div class="registration-step" id="step1" data-step="1">
                        <h5 class="mb-4">Langkah 1: Pilih Institusi</h5>
                        
                        <div class="mb-3">
                            <label for="institution" class="form-label">INSTITUTION/ INSTITUSI <span class="text-danger">*</span></label>
                            <select class="form-select" id="institution" name="institution" autocomplete="organization" required>
                                <option value="" selected disabled>Sila pilih institusi...</option>
                                <option value="upnm">Universiti Pertahanan Nasional Malaysia (UPNM)</option>
                                <option value="utm">Universiti Teknologi Malaysia (UTM)</option>
                                <option value="usm">Universiti Sains Malaysia (USM)</option>
                                <option value="ukm">Universiti Kebangsaan Malaysia (UKM)</option>
                                <option value="um">Universiti Malaya (UM)</option>
                                <option value="uitm">Universiti Teknologi MARA (UiTM)</option>
                                <option value="upsi">Universiti Pendidikan Sultan Idris (UPSI)</option>
                                <option value="unimas">Universiti Malaysia Sarawak (UNIMAS)</option>
                                <option value="ums">Universiti Malaysia Sabah (UMS)</option>
                                <option value="ump">Universiti Malaysia Pahang (UMP)</option>
                                <option value="umt">Universiti Malaysia Terengganu (UMT)</option>
                                <option value="umk">Universiti Malaysia Kelantan (UMK)</option>
                                <option value="unimap">Universiti Malaysia Perlis (UNIMAP)</option>
                                <option value="uthm">Universiti Tun Hussein Onn Malaysia (UTHM)</option>
                                <option value="utem">Universiti Teknikal Malaysia Melaka (UTeM)</option>
                            </select>
                            <div class="invalid-feedback">Sila pilih institusi</div>
                        </div>

                        <div class="alert alert-danger mb-3">
                            <strong><i class="cil cil-info me-1"></i> Perhatian:</strong><br>
                            Jika nama pasukan/IPT/pasukan anda tidak tersenarai, sila hubungi:<br>
                            <strong>Nama:</strong> Mr. Ahmad Fadhil Bin Mohamad Locman<br>
                            <strong>Tel:</strong> <a href="tel:0388706455">03-88706455</a> / <a href="tel:0133236874">013-3236874</a><br>
                            <strong>Email:</strong> <a href="mailto:fadhil.locman@mohe.gov.my">fadhil.locman@mohe.gov.my</a>
                        </div>
                    </div>

                    <!-- Step 2: Basic Information -->
                    <div class="registration-step d-none" id="step2" data-step="2">
                        <h5 class="mb-4">Langkah 2: Maklumat Asas</h5>
                        
                        <div class="mb-3">
                            <label for="shortName" class="form-label">SHORT NAME/ NAMA SINGKATAN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="shortName" name="shortName" 
                                   placeholder="cth: UPNM, UTM, USM" maxlength="50" autocomplete="organization" required>
                            <div class="invalid-feedback">Nama singkatan diperlukan (2-50 aksara)</div>
                            <div class="form-text">Masukkan nama singkatan kontinjen (2-50 aksara)</div>
                        </div>

                        <div class="mb-3">
                            <label for="headName" class="form-label">NAME (HEAD OF DELEGATION) / NAMA (KETUA KONTINJEN) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="headName" name="headName" 
                                   placeholder="Masukkan nama penuh ketua kontinjen" maxlength="100" autocomplete="name" required>
                            <div class="invalid-feedback">Nama ketua kontinjen diperlukan (minimum 3 aksara, nama penuh)</div>
                        </div>

                        <div class="mb-3">
                            <label for="headPosition" class="form-label">POSITION/ JAWATAN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="headPosition" name="headPosition" 
                                   placeholder="cth: Dekan, Pengarah, Ketua Jabatan" maxlength="100" autocomplete="organization-title" required>
                            <div class="invalid-feedback">Jawatan ketua kontinjen diperlukan (minimum 2 aksara)</div>
                        </div>
                    </div>

                    <!-- Step 3: Officer Information -->
                    <div class="registration-step d-none" id="step3" data-step="3">
                        <h5 class="mb-4">Langkah 3: Maklumat Pegawai</h5>
                        <p class="text-muted mb-4">Sila isi maklumat untuk dua (2) pegawai</p>

                        <!-- Officer 1 -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <strong>Pegawai 1</strong>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="officer1Name" class="form-label">NAME OFFICER 1/ NAMA PEGAWAI 1 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="officer1Name" name="officer1Name" 
                                           placeholder="Masukkan nama penuh pegawai 1" maxlength="100" autocomplete="name" required>
                                    <div class="invalid-feedback">Nama pegawai 1 diperlukan (minimum 3 aksara)</div>
                                </div>

                                <div class="mb-3">
                                    <label for="officer1Position" class="form-label">POSITION OFFICER 1/ JAWATAN PEGAWAI 1 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="officer1Position" name="officer1Position" 
                                           placeholder="cth: Penolong Pendaftar, Setiausaha" maxlength="100" autocomplete="organization-title" required>
                                    <div class="invalid-feedback">Jawatan pegawai 1 diperlukan</div>
                                </div>

                                <div class="mb-3">
                                    <label for="officer1Phone" class="form-label">MOBILE PHONE OFFICER 1/ TEL. BIMBIT PEGAWAI 1 <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="officer1Phone" name="officer1Phone" 
                                           placeholder="cth: 012-3456789" pattern="01[0-9]-?[0-9]{7,8}" autocomplete="tel" required>
                                    <div class="invalid-feedback">Format telefon tidak sah. Contoh: 012-3456789</div>
                                </div>

                                <div class="mb-3">
                                    <label for="officer1Email" class="form-label">EMAIL OFFICER 1/ EMEL PEGAWAI 1 <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="officer1Email" name="officer1Email" 
                                           placeholder="pegawai1@example.com" autocomplete="email" required>
                                    <div class="invalid-feedback">Format e-mel tidak sah</div>
                                </div>
                            </div>
                        </div>

                        <!-- Officer 2 -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <strong>Pegawai 2</strong>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="officer2Name" class="form-label">NAME OFFICER 2/ NAMA PEGAWAI 2 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="officer2Name" name="officer2Name" 
                                           placeholder="Masukkan nama penuh pegawai 2" maxlength="100" autocomplete="name" required>
                                    <div class="invalid-feedback">Nama pegawai 2 diperlukan (minimum 3 aksara)</div>
                                </div>

                                <div class="mb-3">
                                    <label for="officer2Position" class="form-label">POSITION OFFICER 2/ JAWATAN PEGAWAI 2 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="officer2Position" name="officer2Position" 
                                           placeholder="cth: Penolong Pendaftar, Setiausaha" maxlength="100" autocomplete="organization-title" required>
                                    <div class="invalid-feedback">Jawatan pegawai 2 diperlukan</div>
                                </div>

                                <div class="mb-3">
                                    <label for="officer2Phone" class="form-label">MOBILE PHONE OFFICER 2/ TEL. BIMBIT PEGAWAI 2 <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="officer2Phone" name="officer2Phone" 
                                           placeholder="cth: 012-3456789" pattern="01[0-9]-?[0-9]{7,8}" autocomplete="tel" required>
                                    <div class="invalid-feedback">Format telefon tidak sah. Contoh: 012-3456789</div>
                                </div>

                                <div class="mb-3">
                                    <label for="officer2Email" class="form-label">EMAIL OFFICER 2/ EMEL PEGAWAI 2 <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="officer2Email" name="officer2Email" 
                                           placeholder="pegawai2@example.com" autocomplete="email" required>
                                    <div class="invalid-feedback">Format e-mel tidak sah atau sama dengan pegawai 1</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Contact Details -->
                    <div class="registration-step d-none" id="step4" data-step="4">
                        <h5 class="mb-4">Langkah 4: Maklumat Hubungan</h5>
                        
                        <div class="mb-3">
                            <label for="officePhone" class="form-label">OFFICE PHONE/ TEL. PEJABAT <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="officePhone" name="officePhone" 
                                   placeholder="cth: 03-12345678" pattern="0[1-9]-?[0-9]{7,9}" autocomplete="tel" required>
                            <div class="invalid-feedback">Format telefon pejabat tidak sah. Contoh: 03-12345678</div>
                        </div>

                        <div class="mb-3">
                            <label for="fax" class="form-label">FAX/ FAKS <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="fax" name="fax" 
                                   placeholder="cth: 03-12345679" pattern="0[1-9]-?[0-9]{7,9}" autocomplete="tel-national" required>
                            <div class="invalid-feedback">Format faks tidak sah. Contoh: 03-12345679</div>
                        </div>

                        <div class="mb-3">
                            <label for="officeAddress" class="form-label">OFFICE ADDRESS/ ALAMAT PEJABAT <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="officeAddress" name="officeAddress" rows="4" 
                                      placeholder="Masukkan alamat pejabat lengkap" maxlength="500" autocomplete="street-address" required></textarea>
                            <div class="invalid-feedback">Alamat pejabat diperlukan (minimum 10 aksara)</div>
                            <div class="form-text">
                                <span id="addressCharCount">0</span> / 500 aksara
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Review & Confirm -->
                    <div class="registration-step d-none" id="step5" data-step="5">
                        <h5 class="mb-4">Langkah 5: Semak & Sahkan</h5>
                        <p class="text-muted mb-4">Sila semak semua maklumat sebelum menghantar</p>

                        <!-- Review Sections -->
                        <div class="review-section mb-4">
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>Maklumat Institusi</strong>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="goToStep(1)">
                                        <i class="cil cil-pencil me-1"></i> Edit
                                    </button>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>Institusi:</strong> <span id="reviewInstitution">-</span></p>
                                    <p class="mb-0"><strong>Nama Singkatan:</strong> <span id="reviewShortName">-</span></p>
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>Ketua Kontinjen</strong>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="goToStep(2)">
                                        <i class="cil cil-pencil me-1"></i> Edit
                                    </button>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>Nama:</strong> <span id="reviewHeadName">-</span></p>
                                    <p class="mb-0"><strong>Jawatan:</strong> <span id="reviewHeadPosition">-</span></p>
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>Pegawai 1</strong>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="goToStep(3)">
                                        <i class="cil cil-pencil me-1"></i> Edit
                                    </button>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>Nama:</strong> <span id="reviewOfficer1Name">-</span></p>
                                    <p class="mb-1"><strong>Jawatan:</strong> <span id="reviewOfficer1Position">-</span></p>
                                    <p class="mb-1"><strong>Telefon:</strong> <span id="reviewOfficer1Phone">-</span></p>
                                    <p class="mb-0"><strong>E-mel:</strong> <span id="reviewOfficer1Email">-</span></p>
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>Pegawai 2</strong>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="goToStep(3)">
                                        <i class="cil cil-pencil me-1"></i> Edit
                                    </button>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>Nama:</strong> <span id="reviewOfficer2Name">-</span></p>
                                    <p class="mb-1"><strong>Jawatan:</strong> <span id="reviewOfficer2Position">-</span></p>
                                    <p class="mb-1"><strong>Telefon:</strong> <span id="reviewOfficer2Phone">-</span></p>
                                    <p class="mb-0"><strong>E-mel:</strong> <span id="reviewOfficer2Email">-</span></p>
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>Maklumat Hubungan</strong>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="goToStep(4)">
                                        <i class="cil cil-pencil me-1"></i> Edit
                                    </button>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>Telefon Pejabat:</strong> <span id="reviewOfficePhone">-</span></p>
                                    <p class="mb-1"><strong>Faks:</strong> <span id="reviewFax">-</span></p>
                                    <p class="mb-0"><strong>Alamat:</strong> <span id="reviewOfficeAddress">-</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Confirmation Checkbox -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmationCheck" required>
                                <label class="form-check-label" for="confirmationCheck">
                                    Saya mengesahkan bahawa semua maklumat yang diberikan adalah benar dan tepat <span class="text-danger">*</span>
                                </label>
                                <div class="invalid-feedback">Sila sahkan maklumat sebelum menghantar</div>
                            </div>
                        </div>
                    </div>

                    <!-- Error Summary -->
                    <div class="alert alert-danger d-none" id="errorSummary">
                        <strong><i class="cil cil-warning me-1"></i> Sila betulkan ralat berikut:</strong>
                        <ul class="mb-0 mt-2" id="errorList"></ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="backButton" onclick="previousStep()" style="display: none;">
                        <i class="cil cil-arrow-left me-1"></i> Kembali
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="confirmCancel()">
                        Batal
                    </button>
                    <button type="button" class="btn btn-primary" id="nextButton" onclick="nextStep()">
                        Seterusnya <i class="cil cil-arrow-right ms-1"></i>
                    </button>
                    <button type="button" class="btn btn-success d-none" id="submitButton" onclick="submitRegistration()">
                        <i class="cil cil-check me-1"></i> Hantar Pendaftaran
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contingent List -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Senarai Kontinjen</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nama Kontinjen</th>
                                    <th scope="col">Kod</th>
                                    <th scope="col">Jumlah Atlet</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="cil cil-info" style="font-size: 2rem;"></i>
                                        <p class="mt-2">Tiada kontinjen didaftarkan</p>
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
const totalSteps = 5;
let formData = {};

// Institution options mapping
const institutionNames = {
    'upnm': 'Universiti Pertahanan Nasional Malaysia (UPNM)',
    'utm': 'Universiti Teknologi Malaysia (UTM)',
    'usm': 'Universiti Sains Malaysia (USM)',
    'ukm': 'Universiti Kebangsaan Malaysia (UKM)',
    'um': 'Universiti Malaya (UM)',
    'uitm': 'Universiti Teknologi MARA (UiTM)',
    'upsi': 'Universiti Pendidikan Sultan Idris (UPSI)',
    'unimas': 'Universiti Malaysia Sarawak (UNIMAS)',
    'ums': 'Universiti Malaysia Sabah (UMS)',
    'ump': 'Universiti Malaysia Pahang (UMP)',
    'umt': 'Universiti Malaysia Terengganu (UMT)',
    'umk': 'Universiti Malaysia Kelantan (UMK)',
    'unimap': 'Universiti Malaysia Perlis (UNIMAP)',
    'uthm': 'Universiti Tun Hussein Onn Malaysia (UTHM)',
    'utem': 'Universiti Teknikal Malaysia Melaka (UTeM)'
};

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

// Show registration form
function showRegistrationForm() {
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
        'institution', 'shortName', 'headName', 'headPosition',
        'officer1Name', 'officer1Position', 'officer1Phone', 'officer1Email',
        'officer2Name', 'officer2Position', 'officer2Phone', 'officer2Email',
        'officePhone', 'fax', 'officeAddress'
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
    
    // Update progress
    const progress = (currentStep / totalSteps) * 100;
    document.getElementById('currentStep').textContent = currentStep;
    document.getElementById('totalSteps').textContent = totalSteps;
    document.getElementById('progressPercent').textContent = Math.round(progress);
    document.getElementById('progressBar').style.width = progress + '%';
    
    // Update buttons
    document.getElementById('backButton').style.display = currentStep > 1 ? 'inline-block' : 'none';
    document.getElementById('nextButton').style.display = currentStep < 5 ? 'inline-block' : 'none';
    document.getElementById('submitButton').style.display = currentStep === 5 ? 'inline-block' : 'none';
    
    if (currentStep === 5) {
        populateReview();
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
    }
    
    if (step === 2) {
        const shortName = document.getElementById('shortName');
        const headName = document.getElementById('headName');
        const headPosition = document.getElementById('headPosition');
        
        if (!shortName.value || shortName.value.length < 2 || shortName.value.length > 50) {
            isValid = false;
            shortName.classList.add('is-invalid');
            errors.push('Nama singkatan mesti antara 2-50 aksara');
        } else {
            shortName.classList.remove('is-invalid');
            shortName.classList.add('is-valid');
        }
        
        if (!headName.value || headName.value.length < 3 || !headName.value.includes(' ')) {
            isValid = false;
            headName.classList.add('is-invalid');
            errors.push('Nama ketua kontinjen mesti nama penuh (minimum 3 aksara)');
        } else {
            headName.classList.remove('is-invalid');
            headName.classList.add('is-valid');
        }
        
        if (!headPosition.value || headPosition.value.length < 2) {
            isValid = false;
            headPosition.classList.add('is-invalid');
            errors.push('Jawatan ketua kontinjen diperlukan (minimum 2 aksara)');
        } else {
            headPosition.classList.remove('is-invalid');
            headPosition.classList.add('is-valid');
        }
    }
    
    if (step === 3) {
        const officer1Name = document.getElementById('officer1Name');
        const officer1Position = document.getElementById('officer1Position');
        const officer1Phone = document.getElementById('officer1Phone');
        const officer1Email = document.getElementById('officer1Email');
        const officer2Name = document.getElementById('officer2Name');
        const officer2Position = document.getElementById('officer2Position');
        const officer2Phone = document.getElementById('officer2Phone');
        const officer2Email = document.getElementById('officer2Email');
        
        // Officer 1 validation
        if (!officer1Name.value || officer1Name.value.length < 3) {
            isValid = false;
            officer1Name.classList.add('is-invalid');
            errors.push('Nama pegawai 1 diperlukan (minimum 3 aksara)');
        } else {
            officer1Name.classList.remove('is-invalid');
            officer1Name.classList.add('is-valid');
        }
        
        if (!officer1Position.value || officer1Position.value.length < 2) {
            isValid = false;
            officer1Position.classList.add('is-invalid');
            errors.push('Jawatan pegawai 1 diperlukan');
        } else {
            officer1Position.classList.remove('is-invalid');
            officer1Position.classList.add('is-valid');
        }
        
        const phone1Pattern = /^01[0-9]-?[0-9]{7,8}$/;
        if (!officer1Phone.value || !phone1Pattern.test(officer1Phone.value.replace(/\s/g, ''))) {
            isValid = false;
            officer1Phone.classList.add('is-invalid');
            errors.push('Format telefon pegawai 1 tidak sah');
        } else {
            officer1Phone.classList.remove('is-invalid');
            officer1Phone.classList.add('is-valid');
        }
        
        const email1Pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!officer1Email.value || !email1Pattern.test(officer1Email.value)) {
            isValid = false;
            officer1Email.classList.add('is-invalid');
            errors.push('Format e-mel pegawai 1 tidak sah');
        } else {
            officer1Email.classList.remove('is-invalid');
            officer1Email.classList.add('is-valid');
        }
        
        // Officer 2 validation
        if (!officer2Name.value || officer2Name.value.length < 3) {
            isValid = false;
            officer2Name.classList.add('is-invalid');
            errors.push('Nama pegawai 2 diperlukan (minimum 3 aksara)');
        } else {
            officer2Name.classList.remove('is-invalid');
            officer2Name.classList.add('is-valid');
        }
        
        if (!officer2Position.value || officer2Position.value.length < 2) {
            isValid = false;
            officer2Position.classList.add('is-invalid');
            errors.push('Jawatan pegawai 2 diperlukan');
        } else {
            officer2Position.classList.remove('is-invalid');
            officer2Position.classList.add('is-valid');
        }
        
        const phone2Pattern = /^01[0-9]-?[0-9]{7,8}$/;
        if (!officer2Phone.value || !phone2Pattern.test(officer2Phone.value.replace(/\s/g, ''))) {
            isValid = false;
            officer2Phone.classList.add('is-invalid');
            errors.push('Format telefon pegawai 2 tidak sah');
        } else {
            officer2Phone.classList.remove('is-invalid');
            officer2Phone.classList.add('is-valid');
        }
        
        const email2Pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!officer2Email.value || !email2Pattern.test(officer2Email.value)) {
            isValid = false;
            officer2Email.classList.add('is-invalid');
            errors.push('Format e-mel pegawai 2 tidak sah');
        } else if (officer1Email.value && officer2Email.value === officer1Email.value) {
            isValid = false;
            officer2Email.classList.add('is-invalid');
            errors.push('E-mel pegawai 2 mesti berbeza dengan pegawai 1');
        } else {
            officer2Email.classList.remove('is-invalid');
            officer2Email.classList.add('is-valid');
        }
    }
    
    if (step === 4) {
        const officePhone = document.getElementById('officePhone');
        const fax = document.getElementById('fax');
        const officeAddress = document.getElementById('officeAddress');
        
        const phonePattern = /^0[1-9]-?[0-9]{7,9}$/;
        if (!officePhone.value || !phonePattern.test(officePhone.value.replace(/\s/g, ''))) {
            isValid = false;
            officePhone.classList.add('is-invalid');
            errors.push('Format telefon pejabat tidak sah');
        } else {
            officePhone.classList.remove('is-invalid');
            officePhone.classList.add('is-valid');
        }
        
        if (!fax.value || !phonePattern.test(fax.value.replace(/\s/g, ''))) {
            isValid = false;
            fax.classList.add('is-invalid');
            errors.push('Format faks tidak sah');
        } else {
            fax.classList.remove('is-invalid');
            fax.classList.add('is-valid');
        }
        
        if (!officeAddress.value || officeAddress.value.length < 10 || officeAddress.value.length > 500) {
            isValid = false;
            officeAddress.classList.add('is-invalid');
            errors.push('Alamat pejabat mesti antara 10-500 aksara');
        } else {
            officeAddress.classList.remove('is-invalid');
            officeAddress.classList.add('is-valid');
        }
    }
    
    if (step === 5) {
        const confirmation = document.getElementById('confirmationCheck');
        if (!confirmation.checked) {
            isValid = false;
            confirmation.classList.add('is-invalid');
            errors.push('Sila sahkan maklumat sebelum menghantar');
        } else {
            confirmation.classList.remove('is-invalid');
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

// Next step
function nextStep() {
    if (validateStep(currentStep)) {
        saveFormData();
        if (currentStep < totalSteps) {
            currentStep++;
            updateStepDisplay();
        }
    }
}

// Previous step
function previousStep() {
    if (currentStep > 1) {
        currentStep--;
        updateStepDisplay();
    }
}

// Go to specific step
function goToStep(step) {
    currentStep = step;
    updateStepDisplay();
}

// Populate review section
function populateReview() {
    const institution = document.getElementById('institution');
    document.getElementById('reviewInstitution').textContent = institutionNames[institution.value] || institution.value;
    document.getElementById('reviewShortName').textContent = document.getElementById('shortName').value || '-';
    document.getElementById('reviewHeadName').textContent = document.getElementById('headName').value || '-';
    document.getElementById('reviewHeadPosition').textContent = document.getElementById('headPosition').value || '-';
    document.getElementById('reviewOfficer1Name').textContent = document.getElementById('officer1Name').value || '-';
    document.getElementById('reviewOfficer1Position').textContent = document.getElementById('officer1Position').value || '-';
    document.getElementById('reviewOfficer1Phone').textContent = document.getElementById('officer1Phone').value || '-';
    document.getElementById('reviewOfficer1Email').textContent = document.getElementById('officer1Email').value || '-';
    document.getElementById('reviewOfficer2Name').textContent = document.getElementById('officer2Name').value || '-';
    document.getElementById('reviewOfficer2Position').textContent = document.getElementById('officer2Position').value || '-';
    document.getElementById('reviewOfficer2Phone').textContent = document.getElementById('officer2Phone').value || '-';
    document.getElementById('reviewOfficer2Email').textContent = document.getElementById('officer2Email').value || '-';
    document.getElementById('reviewOfficePhone').textContent = document.getElementById('officePhone').value || '-';
    document.getElementById('reviewFax').textContent = document.getElementById('fax').value || '-';
    document.getElementById('reviewOfficeAddress').textContent = document.getElementById('officeAddress').value || '-';
}

// Submit registration
function submitRegistration() {
    if (validateStep(5)) {
        // Show loading state
        const submitBtn = document.getElementById('submitButton');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghantar...';
        
        // Collect all form data
        saveFormData();
        
        // Simulate submission (replace with actual AJAX call)
        setTimeout(() => {
            // Clear saved data
            localStorage.removeItem('contingentRegistrationData');
            
            // Show success message
            alert('Pendaftaran kontinjen berjaya dihantar!');
            
            // Close modal
            closeModal();
            
            // Reset form
            document.getElementById('registrationForm')?.reset();
            currentStep = 1;
            updateStepDisplay();
            
            // Reload page or update list
            location.reload();
        }, 1500);
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
}

// Confirm cancel
function confirmCancel() {
    // Immediate cancel: clear saved data and close without confirmation
    localStorage.removeItem('contingentRegistrationData');
    closeModal();
    // Reset form and step state
    document.getElementById('registrationForm')?.reset();
    currentStep = 1;
    updateStepDisplay();
}

// Real-time validation
document.addEventListener('DOMContentLoaded', function() {
    // Address character counter
    const addressField = document.getElementById('officeAddress');
    if (addressField) {
        addressField.addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('addressCharCount').textContent = count;
            if (count > 500) {
                this.value = this.value.substring(0, 500);
                document.getElementById('addressCharCount').textContent = 500;
            }
        });
    }
    
    // Phone number formatting
    const phoneFields = ['officer1Phone', 'officer2Phone', 'officePhone', 'fax'];
    phoneFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.length > 0) {
                    if (fieldId.includes('officer')) {
                        // Mobile: 01X-XXXXXXX
                        if (value.length <= 3) {
                            this.value = value;
                        } else if (value.length <= 10) {
                            this.value = value.substring(0, 3) + '-' + value.substring(3);
                        } else {
                            this.value = value.substring(0, 3) + '-' + value.substring(3, 10);
                        }
                    } else {
                        // Landline: 0X-XXXXXXX
                        if (value.length <= 2) {
                            this.value = value;
                        } else if (value.length <= 9) {
                            this.value = value.substring(0, 2) + '-' + value.substring(2);
                        } else {
                            this.value = value.substring(0, 2) + '-' + value.substring(2, 9);
                        }
                    }
                }
            });
        }
    });
    
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
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>
