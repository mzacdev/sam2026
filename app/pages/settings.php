<?php
/**
 * Settings Page - Comprehensive UI/UX Design
 * Tournament Management System Settings
 */
require_once __DIR__ . '/../config.php';

// Check access before rendering page
if (!defined('SKIP_AUTH_CHECK')) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/auth.php';
    require_once __DIR__ . '/../config/rbac.php';
    
    // Session should already be started in config.php, but ensure it's started
    if (session_status() === PHP_SESSION_NONE) {
        Session::start();
    }
    
    $auth = getAuth();
    $rbac = getRBAC();
    
    // Check access to settings page (requires ADMIN)
    $rbac->requirePageAccess('pages/settings.php');
    
    // If we reach here and user doesn't have access, stop execution
    if (!$rbac->hasPageAccess('pages/settings.php')) {
        // Access denied - redirect or show error
        if (!headers_sent()) {
            header('Location: ' . url('pages/access-denied.php'));
            exit;
        } else {
            // Headers already sent - output redirect and stop
            $deniedUrl = url('pages/access-denied.php');
            echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($deniedUrl) . '"></head><body><script>window.location.href="' . htmlspecialchars($deniedUrl) . '";</script></body></html>';
            exit;
        }
    }
}

$page_title = 'Tetapan';

ob_start();
?>
<div class="w-100 px-3">
    <!-- Page Header -->
    <div class="row mb-30">
        <div class="col-12">
            <div class="page-heading">
                <div class="row align-items-center">
                    <div class="col">
                        <h3>Tetapan Sistem <span>Konfigurasi dan urus tetapan sistem pengurusan kejohanan</span></h3>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="button button-outline button-secondary mr-10" onclick="resetAllSettings()">
                            <i class="zmdi zmdi-refresh mr-5"></i> Reset
                        </button>
                        <button type="button" class="button button-primary" onclick="saveAllSettings(this)">
                            <i class="zmdi zmdi-save mr-5"></i> Simpan Semua
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Navigation Tabs -->
    <div class="row mb-30">
        <div class="col-12">
            <div class="box">
                <div class="box-body">
                    <ul class="nav nav-tabs mb-15" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab">
                                <i class="zmdi zmdi-settings mr-5"></i> Umum
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="tournament-tab" data-bs-toggle="tab" href="#tournament" role="tab">
                                <i class="zmdi zmdi-trophy mr-5"></i> Kejohanan
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="registration-tab" data-bs-toggle="tab" href="#registration" role="tab">
                                <i class="zmdi zmdi-account-add mr-5"></i> Pendaftaran
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="notification-tab" data-bs-toggle="tab" href="#notification" role="tab">
                                <i class="zmdi zmdi-notifications mr-5"></i> Notifikasi
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="user-tab" data-bs-toggle="tab" href="#user" role="tab">
                                <i class="zmdi zmdi-accounts mr-5"></i> Pengguna & Akses
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="display-tab" data-bs-toggle="tab" href="#display" role="tab">
                                <i class="zmdi zmdi-palette mr-5"></i> Paparan
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="system-tab" data-bs-toggle="tab" href="#system" role="tab">
                                <i class="zmdi zmdi-devices mr-5"></i> Sistem
                            </a>
                        </li>
                    </ul>

                    <!-- Settings Content -->
                    <div class="tab-content" id="settingsTabContent">
        
        <!-- Tab 1: General Settings -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="row">
                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-info-outline me-2"></i> Maklumat Sistem</h4>
                        </div>
                        <div class="box-body">
                            <form id="generalSettingsForm">
                                <div class="mb-3">
                                    <label for="siteName" class="form-label">Nama Sistem <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="siteName" name="siteName" 
                                           value="<?php echo SITE_NAME; ?>" required>
                                    <div class="form-text">Nama yang dipaparkan di header dan tajuk halaman</div>
                                </div>

                                <div class="mb-3">
                                    <label for="siteFullName" class="form-label">Nama Penuh</label>
                                    <input type="text" class="form-control" id="siteFullName" name="siteFullName" 
                                           value="<?php echo SITE_FULL_NAME; ?>">
                                    <div class="form-text">Nama penuh sistem (cth: Sukan Asasi Malaysia)</div>
                                </div>

                                <div class="mb-3">
                                    <label for="siteDescription" class="form-label">Penerangan Sistem</label>
                                    <textarea class="form-control" id="siteDescription" name="siteDescription" rows="3"><?php echo SITE_DESCRIPTION; ?></textarea>
                                    <div class="form-text">Penerangan ringkas tentang sistem</div>
                                </div>

                                <div class="mb-3">
                                    <label for="siteEmail" class="form-label">E-mel Sistem <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="siteEmail" name="siteEmail" 
                                           placeholder="admin@sam2026.gov.my" required>
                                    <div class="form-text">E-mel utama untuk komunikasi rasmi</div>
                                </div>

                                <div class="mb-3">
                                    <label for="sitePhone" class="form-label">Telefon Sistem</label>
                                    <input type="tel" class="form-control" id="sitePhone" name="sitePhone" 
                                           placeholder="03-12345678">
                                </div>

                                <div class="mb-3">
                                    <label for="siteAddress" class="form-label">Alamat Sistem</label>
                                    <textarea class="form-control" id="siteAddress" name="siteAddress" rows="3" 
                                              placeholder="Masukkan alamat pejabat"></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-globe me-2"></i> Lokal & Zon Masa</h4>
                        </div>
                        <div class="box-body">
                            <form id="localeSettingsForm">
                                <div class="mb-3">
                                    <label for="timezone" class="form-label">Zon Masa <span class="text-danger">*</span></label>
                                    <select class="form-select" id="timezone" name="timezone" required>
                                        <option value="Asia/Kuala_Lumpur" selected>Asia/Kuala_Lumpur (GMT+8)</option>
                                        <option value="UTC">UTC (GMT+0)</option>
                                        <option value="Asia/Singapore">Asia/Singapore (GMT+8)</option>
                                        <option value="Asia/Jakarta">Asia/Jakarta (GMT+7)</option>
                                        <option value="Asia/Bangkok">Asia/Bangkok (GMT+7)</option>
                                    </select>
                                    <div class="form-text">Zon masa untuk semua tarikh dan masa dalam sistem</div>
                                </div>

                                <div class="mb-3">
                                    <label for="language" class="form-label">Bahasa Antaramuka</label>
                                    <select class="form-select" id="language" name="language">
                                        <option value="ms" selected>Bahasa Malaysia</option>
                                        <option value="en">English</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="dateFormat" class="form-label">Format Tarikh</label>
                                    <select class="form-select" id="dateFormat" name="dateFormat">
                                        <option value="d/m/Y" selected>DD/MM/YYYY (cth: 30/01/2026)</option>
                                        <option value="Y-m-d">YYYY-MM-DD (cth: 2026-01-30)</option>
                                        <option value="d M Y">DD MMM YYYY (cth: 30 Jan 2026)</option>
                                        <option value="j F Y">D MMMM YYYY (cth: 30 Januari 2026)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="timeFormat" class="form-label">Format Masa</label>
                                    <select class="form-select" id="timeFormat" name="timeFormat">
                                        <option value="H:i" selected>24 jam (cth: 14:30)</option>
                                        <option value="h:i A">12 jam (cth: 2:30 PM)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="currency" class="form-label">Mata Wang</label>
                                    <select class="form-select" id="currency" name="currency">
                                        <option value="MYR" selected>MYR (RM)</option>
                                        <option value="USD">USD ($)</option>
                                        <option value="SGD">SGD (S$)</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Tournament Settings -->
        <div class="tab-pane fade" id="tournament" role="tabpanel">
            <div class="row">
                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-trophy me-2"></i> Maklumat Kejohanan</h4>
                        </div>
                        <div class="box-body">
                            <form id="tournamentSettingsForm">
                                <div class="mb-3">
                                    <label for="tournamentName" class="form-label">Nama Kejohanan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="tournamentName" name="tournamentName" 
                                           value="Sukan Asasi Malaysia 2026" required>
                                </div>

                                <div class="mb-3">
                                    <label for="tournamentEdition" class="form-label">Edisi Kejohanan</label>
                                    <input type="text" class="form-control" id="tournamentEdition" name="tournamentEdition" 
                                           value="Kali Ke-9" placeholder="cth: Kali Ke-9">
                                </div>

                                <div class="mb-3">
                                    <label for="tournamentStartDate" class="form-label">Tarikh Mula <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tournamentStartDate" name="tournamentStartDate" 
                                           value="2026-01-30" required>
                                </div>

                                <div class="mb-3">
                                    <label for="tournamentEndDate" class="form-label">Tarikh Tamat <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tournamentEndDate" name="tournamentEndDate" 
                                           value="2026-02-01" required>
                                </div>

                                <div class="mb-3">
                                    <label for="tournamentStatus" class="form-label">Status Kejohanan</label>
                                    <select class="form-select" id="tournamentStatus" name="tournamentStatus">
                                        <option value="upcoming" selected>Akan Datang</option>
                                        <option value="ongoing">Sedang Berlangsung</option>
                                        <option value="completed">Selesai</option>
                                        <option value="cancelled">Dibatalkan</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-pin me-2"></i> Lokasi & Venue</h4>
                        </div>
                        <div class="box-body">
                            <form id="venueSettingsForm">
                                <div class="mb-3">
                                    <label for="mainVenue" class="form-label">Venue Utama <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="mainVenue" name="mainVenue" 
                                           value="UPNM Kem Sungai Besi" required>
                                </div>

                                <div class="mb-3">
                                    <label for="venueAddress" class="form-label">Alamat Venue</label>
                                    <textarea class="form-control" id="venueAddress" name="venueAddress" rows="3" 
                                              placeholder="Masukkan alamat lengkap venue"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="venueCity" class="form-label">Bandar</label>
                                    <input type="text" class="form-control" id="venueCity" name="venueCity" 
                                           value="Kuala Lumpur">
                                </div>

                                <div class="mb-3">
                                    <label for="venueState" class="form-label">Negeri</label>
                                    <select class="form-select" id="venueState" name="venueState">
                                        <option value="Kuala Lumpur" selected>Kuala Lumpur</option>
                                        <option value="Selangor">Selangor</option>
                                        <option value="Putrajaya">Putrajaya</option>
                                        <!-- Add more states -->
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="venueCapacity" class="form-label">Kapasiti Venue</label>
                                    <input type="number" class="form-control" id="venueCapacity" name="venueCapacity" 
                                           placeholder="cth: 5000" min="0">
                                    <div class="form-text">Jumlah kapasiti penonton (jika berkaitan)</div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Registration Settings -->
        <div class="tab-pane fade" id="registration" role="tabpanel">
            <div class="row">
                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-calendar me-2"></i> Tempoh Pendaftaran</h4>
                        </div>
                        <div class="box-body">
                            <form id="registrationPeriodForm">
                                <div class="mb-3">
                                    <label for="regOpenDate" class="form-label">Tarikh Buka Pendaftaran <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="regOpenDate" name="regOpenDate" required>
                                </div>

                                <div class="mb-3">
                                    <label for="regCloseDate" class="form-label">Tarikh Tutup Pendaftaran <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" id="regCloseDate" name="regCloseDate" required>
                                </div>

                                <div class="mb-3">
                                    <label class="adomx-switch">
                                        <input type="checkbox" id="regAutoClose" name="regAutoClose" checked>
                                        <i class="lever"></i>
                                        <span class="text">Tutup pendaftaran secara automatik selepas tarikh tutup</span>
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label class="adomx-switch">
                                        <input type="checkbox" id="regAllowLate" name="regAllowLate">
                                        <i class="lever"></i>
                                        <span class="text">Benarkan pendaftaran lewat (dengan kelulusan)</span>
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-money me-2"></i> Yuran & Bayaran</h4>
                        </div>
                        <div class="box-body">
                            <form id="feeSettingsForm">
                                <div class="mb-3">
                                    <label for="regFeePerContingent" class="form-label">Yuran Pendaftaran Kontinjen</label>
                                    <div class="input-group">
                                        <span class="input-group-text">RM</span>
                                        <input type="number" class="form-control" id="regFeePerContingent" name="regFeePerContingent" 
                                               placeholder="0.00" step="0.01" min="0">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="regFeePerAthlete" class="form-label">Yuran Pendaftaran Atlet</label>
                                    <div class="input-group">
                                        <span class="input-group-text">RM</span>
                                        <input type="number" class="form-control" id="regFeePerAthlete" name="regFeePerAthlete" 
                                               placeholder="0.00" step="0.01" min="0">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="adomx-switch">
                                        <input type="checkbox" id="regFeeRequired" name="regFeeRequired">
                                        <i class="lever"></i>
                                        <span class="text">Yuran pendaftaran wajib</span>
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-alert-triangle me-2"></i> Had & Sekatan</h4>
                        </div>
                        <div class="box-body">
                            <form id="limitSettingsForm">
                                <div class="mb-3">
                                    <label for="maxContingents" class="form-label">Bilangan Maksimum Kontinjen</label>
                                    <input type="number" class="form-control" id="maxContingents" name="maxContingents" 
                                           placeholder="cth: 50" min="1">
                                    <div class="form-text">Kosongkan untuk tiada had</div>
                                </div>

                                <div class="mb-3">
                                    <label for="maxAthletesPerContingent" class="form-label">Bilangan Maksimum Atlet per Kontinjen</label>
                                    <input type="number" class="form-control" id="maxAthletesPerContingent" name="maxAthletesPerContingent" 
                                           placeholder="cth: 100" min="1">
                                </div>

                                <div class="mb-3">
                                    <label for="maxSportsPerContingent" class="form-label">Bilangan Maksimum Sukan per Kontinjen</label>
                                    <input type="number" class="form-control" id="maxSportsPerContingent" name="maxSportsPerContingent" 
                                           placeholder="cth: 20" min="1">
                                </div>

                                <div class="mb-3">
                                    <label for="minAthletesPerContingent" class="form-label">Bilangan Minimum Atlet per Kontinjen</label>
                                    <input type="number" class="form-control" id="minAthletesPerContingent" name="minAthletesPerContingent" 
                                           placeholder="cth: 10" min="1">
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-file-text me-2"></i> Syarat & Dokumen</h4>
                        </div>
                        <div class="box-body">
                            <form id="requirementSettingsForm">
                                <div class="mb-3">
                                    <label for="requiredDocuments" class="form-label">Dokumen Wajib</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="reqDoc1" name="requiredDocuments[]" value="surat_rasmi" checked>
                                        <label class="form-check-label" for="reqDoc1">Surat Rasmi Institusi</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="reqDoc2" name="requiredDocuments[]" value="senarai_atlet">
                                        <label class="form-check-label" for="reqDoc2">Senarai Atlet</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="reqDoc3" name="requiredDocuments[]" value="sijil_kesihatan">
                                        <label class="form-check-label" for="reqDoc3">Sijil Kesihatan</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="reqDoc4" name="requiredDocuments[]" value="foto">
                                        <label class="form-check-label" for="reqDoc4">Foto Atlet</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="registrationTerms" class="form-label">Terma & Syarat Pendaftaran</label>
                                    <textarea class="form-control" id="registrationTerms" name="registrationTerms" rows="5" 
                                              placeholder="Masukkan terma dan syarat pendaftaran..."></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 4: Notification Settings -->
        <div class="tab-pane fade" id="notification" role="tabpanel">
            <div class="row">
                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-email me-2"></i> E-mel Notifikasi</h4>
                        </div>
                        <div class="box-body">
                            <form id="emailNotificationForm">
                                <div class="mb-3">
                                    <label class="adomx-switch">
                                        <input type="checkbox" id="emailEnabled" name="emailEnabled" checked>
                                        <i class="lever"></i>
                                        <span class="text">Aktifkan notifikasi e-mel</span>
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label for="smtpHost" class="form-label">SMTP Host</label>
                                    <input type="text" class="form-control" id="smtpHost" name="smtpHost" 
                                           placeholder="smtp.gmail.com">
                                </div>

                                <div class="mb-3">
                                    <label for="smtpPort" class="form-label">SMTP Port</label>
                                    <input type="number" class="form-control" id="smtpPort" name="smtpPort" 
                                           value="587" placeholder="587">
                                </div>

                                <div class="mb-3">
                                    <label for="smtpUsername" class="form-label">SMTP Username</label>
                                    <input type="text" class="form-control" id="smtpUsername" name="smtpUsername">
                                </div>

                                <div class="mb-3">
                                    <label for="smtpPassword" class="form-label">SMTP Password</label>
                                    <input type="password" class="form-control" id="smtpPassword" name="smtpPassword">
                                </div>

                                <div class="mb-3">
                                    <label for="emailFrom" class="form-label">E-mel Pengirim</label>
                                    <input type="email" class="form-control" id="emailFrom" name="emailFrom" 
                                           placeholder="noreply@sam2026.gov.my">
                                </div>

                                <div class="mb-3">
                                    <label for="emailFromName" class="form-label">Nama Pengirim</label>
                                    <input type="text" class="form-control" id="emailFromName" name="emailFromName" 
                                           value="SAM 2026">
                                </div>
                                <div class="mb-3">
                                    <button type="button" class="button button-outline button-info" onclick="testSmtp(this)">
                                        <i class="zmdi zmdi-email mr-5"></i> Test SMTP
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-phone me-2"></i> SMS Notifikasi</h4>
                        </div>
                        <div class="box-body">
                            <form id="smsNotificationForm">
                                <div class="mb-3">
                                    <label class="adomx-switch">
                                        <input type="checkbox" id="smsEnabled" name="smsEnabled">
                                        <i class="lever"></i>
                                        <span class="text">Aktifkan notifikasi SMS</span>
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label for="smsProvider" class="form-label">Penyedia SMS</label>
                                    <select class="form-select" id="smsProvider" name="smsProvider">
                                        <option value="">Pilih penyedia...</option>
                                        <option value="twilio">Twilio</option>
                                        <option value="nexmo">Vonage (Nexmo)</option>
                                        <option value="custom">Kustom</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="smsApiKey" class="form-label">API Key</label>
                                    <input type="text" class="form-control" id="smsApiKey" name="smsApiKey">
                                </div>

                                <div class="mb-3">
                                    <label for="smsSenderId" class="form-label">Sender ID</label>
                                    <input type="text" class="form-control" id="smsSenderId" name="smsSenderId" 
                                           placeholder="SAM2026">
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-notifications me-2"></i> Jenis Notifikasi</h4>
                        </div>
                        <div class="box-body">
                            <form id="notificationTypesForm">
                                <div class="mb-3">
                                    <label class="form-label">Aktifkan Notifikasi untuk:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notifRegistration" name="notificationTypes[]" value="registration" checked>
                                        <label class="form-check-label" for="notifRegistration">Pendaftaran Baru</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notifResults" name="notificationTypes[]" value="results" checked>
                                        <label class="form-check-label" for="notifResults">Keputusan Pertandingan</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notifSchedule" name="notificationTypes[]" value="schedule" checked>
                                        <label class="form-check-label" for="notifSchedule">Perubahan Jadual</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notifReminder" name="notificationTypes[]" value="reminder">
                                        <label class="form-check-label" for="notifReminder">Peringatan</label>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 5: User & Access Settings -->
        <div class="tab-pane fade" id="user" role="tabpanel">
            <?php require_once __DIR__ . '/../includes/rbac_management_ui.php'; ?>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-shield-security me-2"></i> Keselamatan</h4>
                        </div>
                        <div class="box-body">
                            <form id="securitySettingsForm">
                                <div class="mb-3">
                                    <label class="adomx-switch">
                                        <input type="checkbox" id="twoFactorAuth" name="twoFactorAuth">
                                        <i class="lever"></i>
                                        <span class="text">Aktifkan Pengesahan Dua Faktor (2FA)</span>
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label for="sessionTimeout" class="form-label">Masa Tamat Sesi (minit)</label>
                                    <input type="number" class="form-control" id="sessionTimeout" name="sessionTimeout" 
                                           value="30" min="5" max="480">
                                </div>

                                <div class="mb-3">
                                    <label for="passwordMinLength" class="form-label">Panjang Minimum Kata Laluan</label>
                                    <input type="number" class="form-control" id="passwordMinLength" name="passwordMinLength" 
                                           value="8" min="6" max="20">
                                </div>

                                <div class="mb-3">
                                    <label class="adomx-switch">
                                        <input type="checkbox" id="passwordRequireUppercase" name="passwordRequireUppercase" checked>
                                        <i class="lever"></i>
                                        <span class="text">Perlu huruf besar</span>
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label class="adomx-switch">
                                        <input type="checkbox" id="passwordRequireNumber" name="passwordRequireNumber" checked>
                                        <i class="lever"></i>
                                        <span class="text">Perlu nombor</span>
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label class="adomx-switch">
                                        <input type="checkbox" id="passwordRequireSpecial" name="passwordRequireSpecial">
                                        <i class="lever"></i>
                                        <span class="text">Perlu aksara khas</span>
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-lock me-2"></i> Kebenaran Akses</h4>
                        </div>
                        <div class="box-body">
                            <form id="permissionsForm">
                                <div class="mb-3">
                                    <label class="form-label">Kebenaran untuk Kontinjen:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="permViewResults" name="permissions[]" value="view_results" checked>
                                        <label class="form-check-label" for="permViewResults">Lihat Keputusan</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="permEditOwnData" name="permissions[]" value="edit_own_data" checked>
                                        <label class="form-check-label" for="permEditOwnData">Edit Data Sendiri</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="permUploadDocuments" name="permissions[]" value="upload_documents" checked>
                                        <label class="form-check-label" for="permUploadDocuments">Muat Naik Dokumen</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Kebenaran untuk Hakim:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="permEnterResults" name="permissions[]" value="enter_results" checked>
                                        <label class="form-check-label" for="permEnterResults">Masukkan Keputusan</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="permViewSchedule" name="permissions[]" value="view_schedule" checked>
                                        <label class="form-check-label" for="permViewSchedule">Lihat Jadual</label>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 6: Display Settings -->
        <div class="tab-pane fade" id="display" role="tabpanel">
            <div class="row">
                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-palette me-2"></i> Logo & Ikon</h4>
                        </div>
                        <div class="box-body">
                            <form id="logoSettingsForm">
                                <div class="mb-3">
                                    <label for="headerLogo" class="form-label">Logo Header</label>
                                    <input type="file" class="form-control" id="headerLogo" name="headerLogo" accept="image/*">
                                    <input type="hidden" id="headerLogoPath" name="headerLogoPath" value="<?php echo htmlspecialchars((string)app_setting('logoSettingsForm.headerLogoPath', LOGO_HEADER), ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="form-text">Logo yang dipaparkan di header (disyorkan: 180x180px)</div>
                                    <div class="mt-2">
                                        <img id="headerLogoPreview" src="<?php echo logo(LOGO_HEADER); ?>" alt="Current Logo" class="img-thumbnail" style="max-height: 60px;">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="favicon" class="form-label">Favicon</label>
                                    <input type="file" class="form-control" id="favicon" name="favicon" accept="image/*">
                                    <input type="hidden" id="faviconPath" name="faviconPath" value="<?php echo htmlspecialchars((string)app_setting('logoSettingsForm.faviconPath', LOGO_FAVICON), ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="form-text">Ikon untuk tab pelayar (disyorkan: 32x32px atau 16x16px)</div>
                                    <div class="mt-2">
                                        <img id="faviconPreview" src="<?php echo logo(LOGO_FAVICON); ?>" alt="Current Favicon" class="img-thumbnail" style="max-height: 40px;">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="backgroundImage" class="form-label">Gambar Latar Belakang</label>
                                    <input type="file" class="form-control" id="backgroundImage" name="backgroundImage" accept="image/*">
                                    <input type="hidden" id="backgroundImagePath" name="backgroundImagePath" value="<?php echo htmlspecialchars((string)app_setting('logoSettingsForm.backgroundImagePath', ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="form-text">Gambar latar belakang untuk semua halaman</div>
                                    <?php $bgPath = (string)app_setting('logoSettingsForm.backgroundImagePath', ''); ?>
                                    <?php if ($bgPath !== ''): ?>
                                    <div class="mt-2">
                                        <img id="backgroundImagePreview" src="<?php echo url('assets/img/backgrounds/' . $bgPath); ?>" alt="Current Background" class="img-thumbnail" style="max-height: 60px;">
                                    </div>
                                    <?php else: ?>
                                    <div class="mt-2">
                                        <img id="backgroundImagePreview" src="" alt="Current Background" class="img-thumbnail d-none" style="max-height: 60px;">
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-palette me-2"></i> Tema & Warna</h4>
                        </div>
                        <div class="box-body">
                            <form id="themeSettingsForm">
                                <div class="mb-3">
                                    <label for="primaryColor" class="form-label">Warna Utama</label>
                                    <input type="color" class="form-control form-control-color" id="primaryColor" name="primaryColor" 
                                           value="#0d6efd" title="Pilih warna utama">
                                </div>

                                <div class="mb-3">
                                    <label for="themeMode" class="form-label">Mod Tema</label>
                                    <select class="form-select" id="themeMode" name="themeMode">
                                        <option value="light" selected>Terang (Light)</option>
                                        <option value="dark">Gelap (Dark)</option>
                                        <option value="auto">Auto (Ikut Sistem)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="navbarStyle" class="form-label">Gaya Navbar</label>
                                    <select class="form-select" id="navbarStyle" name="navbarStyle">
                                        <option value="dark" selected>Gelap</option>
                                        <option value="light">Terang</option>
                                        <option value="primary">Warna Utama</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 7: System Settings -->
        <div class="tab-pane fade" id="system" role="tabpanel">
            <div class="row">
                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-download me-2"></i> Backup & Eksport</h4>
                        </div>
                        <div class="box-body">
                            <form id="backupSettingsForm">
                                <div class="mb-3">
                                    <label for="autoBackup" class="form-label">Backup Automatik</label>
                                    <select class="form-select" id="autoBackup" name="autoBackup">
                                        <option value="disabled">Tidak Aktif</option>
                                        <option value="daily" selected>Harian</option>
                                        <option value="weekly">Mingguan</option>
                                        <option value="monthly">Bulanan</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="backupRetention" class="form-label">Tempoh Simpanan Backup (hari)</label>
                                    <input type="number" class="form-control" id="backupRetention" name="backupRetention" 
                                           value="30" min="1" max="365">
                                </div>

                                <div class="mb-3">
                                    <button type="button" class="button button-outline button-primary mr-10" onclick="createBackup(this)">
                                        <i class="zmdi zmdi-save mr-5"></i> Buat Backup Sekarang
                                    </button>
                                    <button type="button" class="button button-outline button-success" onclick="exportData(this)">
                                        <i class="zmdi zmdi-download mr-5"></i> Eksport Data
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-wrench me-2"></i> Mod Penyelenggaraan</h4>
                        </div>
                        <div class="box-body">
                            <form id="maintenanceForm">
                                <div class="mb-3">
                                    <label class="adomx-switch">
                                        <input type="checkbox" id="maintenanceMode" name="maintenanceMode">
                                        <i class="lever"></i>
                                        <span class="text">Aktifkan Mod Penyelenggaraan</span>
                                    </label>
                                    <div class="form-text">Sistem akan tidak boleh diakses oleh pengguna biasa</div>
                                </div>

                                <div class="mb-3">
                                    <label for="maintenanceMessage" class="form-label">Mesej Penyelenggaraan</label>
                                    <textarea class="form-control" id="maintenanceMessage" name="maintenanceMessage" rows="3" 
                                              placeholder="Sistem sedang dalam penyelenggaraan. Sila cuba lagi kemudian."></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="box mb-30">
                        <div class="box-head">
                            <h4 class="title"><i class="zmdi zmdi-format-list-bulleted me-2"></i> Log & Audit</h4>
                        </div>
                        <div class="box-body">
                            <form id="logSettingsForm">
                                <div class="mb-3">
                                    <label class="adomx-switch">
                                        <input type="checkbox" id="enableAuditLog" name="enableAuditLog" checked>
                                        <i class="lever"></i>
                                        <span class="text">Aktifkan Log Audit</span>
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label for="logRetention" class="form-label">Tempoh Simpanan Log (hari)</label>
                                    <input type="number" class="form-control" id="logRetention" name="logRetention" 
                                           value="90" min="1" max="365">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Jenis Log yang Direkodkan:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="logLogin" name="logTypes[]" value="login" checked>
                                        <label class="form-check-label" for="logLogin">Log Masuk/Keluar</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="logDataChange" name="logTypes[]" value="data_change" checked>
                                        <label class="form-check-label" for="logDataChange">Perubahan Data</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="logSettings" name="logTypes[]" value="settings" checked>
                                        <label class="form-check-label" for="logSettings">Perubahan Tetapan</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <button type="button" class="button button-outline button-info mr-10" onclick="viewLogs(this)">
                                        <i class="zmdi zmdi-format-list-bulleted mr-5"></i> Lihat Log
                                    </button>
                                    <button type="button" class="button button-outline button-warning" onclick="clearLogs(this)">
                                        <i class="zmdi zmdi-delete mr-5"></i> Kosongkan Log
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
const SETTINGS_API_URL = <?php echo json_encode(url('api/settings.php')); ?>;
const HAS_SWAL = () => !!(window.Swal && typeof window.Swal.fire === 'function');

function uiSuccess(title, text) {
    if (HAS_SWAL()) return Swal.fire({ icon: 'success', title, text });
    if (typeof Toast !== 'undefined' && typeof Toast.success === 'function') {
        Toast.success(text || title || 'Berjaya');
    } else {
        console.log('[SETTINGS][success] ' + (text || title || 'Berjaya'));
    }
    return Promise.resolve();
}

function uiError(title, text) {
    if (HAS_SWAL()) return Swal.fire({ icon: 'error', title, text });
    if (typeof Toast !== 'undefined' && typeof Toast.error === 'function') {
        Toast.error((title ? title + ': ' : '') + (text || 'Ralat.'));
    } else {
        console.error('[SETTINGS][error] ' + (title ? title + ': ' : '') + (text || 'Ralat.'));
    }
    return Promise.resolve();
}

function uiInfo(title, text) {
    if (HAS_SWAL()) return Swal.fire({ icon: 'info', title, text });
    if (typeof Toast !== 'undefined' && typeof Toast.info === 'function') {
        Toast.info(text || title || 'Makluman');
    } else {
        console.log('[SETTINGS][info] ' + (text || title || 'Makluman'));
    }
    return Promise.resolve();
}

async function uiConfirm(title, text, confirmText) {
    if (HAS_SWAL()) {
        const res = await Swal.fire({
            icon: 'warning',
            title: title || 'Pengesahan',
            text: text || '',
            showCancelButton: true,
            confirmButtonText: confirmText || 'Ya',
            cancelButtonText: 'Batal',
            reverseButtons: true
        });
        return !!(res && res.isConfirmed);
    }
    if (typeof Toast !== 'undefined' && typeof Toast.info === 'function') {
        Toast.info(text || title || 'Pengesahan diperlukan.');
    }
    return false;
}

function uiLoading(title, text) {
    if (!HAS_SWAL()) return;
    Swal.fire({
        title: title || 'Sila tunggu',
        text: text || 'Sedang diproses...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    });
}

function uiCloseLoading() {
    if (HAS_SWAL()) Swal.close();
}

function setBtnLoading(btn, loadingText) {
    if (!btn) return () => {};
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ' + (loadingText || 'Memproses...');
    return () => {
        btn.disabled = false;
        btn.innerHTML = original;
    };
}

function collectFormValues(form) {
    const data = {};
    const fields = form.querySelectorAll('input[name], select[name], textarea[name]');
    fields.forEach(el => {
        if (el.disabled) return;
        const name = el.name;
        if (!name) return;
        if (el.type === 'file') return;

        if (el.type === 'checkbox') {
            if (name.endsWith('[]')) {
                if (!Array.isArray(data[name])) data[name] = [];
                if (el.checked) data[name].push(el.value);
            } else {
                data[name] = !!el.checked;
            }
            return;
        }

        if (el.type === 'radio') {
            if (el.checked) data[name] = el.value;
            return;
        }

        data[name] = el.value;
    });
    return data;
}

function applyFormValues(form, values) {
    if (!form || !values || typeof values !== 'object') return;
    const fields = form.querySelectorAll('input[name], select[name], textarea[name]');
    fields.forEach(el => {
        const name = el.name;
        if (!name || !(name in values)) return;
        if (el.type === 'file') return;

        const incoming = values[name];
        if (el.type === 'checkbox') {
            if (name.endsWith('[]')) {
                const arr = Array.isArray(incoming) ? incoming.map(String) : [];
                el.checked = arr.includes(String(el.value));
            } else {
                el.checked = !!incoming;
            }
            return;
        }

        if (el.type === 'radio') {
            el.checked = (String(el.value) === String(incoming));
            return;
        }

        el.value = incoming ?? '';
    });
}

async function loadAllSettings() {
    try {
        const res = await fetch(SETTINGS_API_URL, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) return;
        const json = await res.json();
        if (!json || !json.ok || !json.data || typeof json.data !== 'object') return;

        Object.keys(json.data).forEach(formId => {
            const form = document.getElementById(formId);
            if (!form) return;
            applyFormValues(form, json.data[formId]);
        });
    } catch (e) {
        console.error('Load settings error:', e);
    }
}

// Save all settings
async function saveAllSettings(saveBtn) {
    // Collect all form data
    const allForms = document.querySelectorAll('#settingsTabContent form');
    const allData = {};
    
    allForms.forEach(form => {
        const formId = form.id;
        allData[formId] = collectFormValues(form);
    });
    
    // Show loading
    if (!saveBtn) return;
    const doneBtn = setBtnLoading(saveBtn, 'Menyimpan...');
    uiLoading('Simpan Tetapan', 'Sedang menyimpan semua tetapan...');

    try {
        const res = await fetch(SETTINGS_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ data: allData })
        });
        const json = await res.json();
        if (!res.ok || !json || !json.ok) {
            let msg = (json && json.error) ? json.error : 'Gagal menyimpan tetapan.';
            if (json && Array.isArray(json.errors) && json.errors.length) {
                msg += ' ' + json.errors.slice(0, 3).join(' | ');
            }
            throw new Error(msg);
        }
        uiCloseLoading();
        await uiSuccess('Berjaya', json.message || 'Tetapan telah disimpan.');
    } catch (err) {
        console.error('Save settings error:', err);
        uiCloseLoading();
        await uiError('Ralat', (err && err.message) ? err.message : 'Gagal menyimpan tetapan. Sila cuba lagi.');
    } finally {
        doneBtn();
    }
}

// Reset all settings
function resetAllSettings() {
    uiConfirm('Reset Tetapan', 'Adakah anda pasti mahu reset semua tetapan kepada nilai lalai?', 'Reset').then(ok => {
        if (ok) location.reload();
    });
}

async function testSmtp(btn) {
    const smtpHost = (document.getElementById('smtpHost')?.value || '').trim();
    const smtpPort = parseInt(document.getElementById('smtpPort')?.value || '0', 10);
    if (!smtpHost || !smtpPort) {
        uiInfo('Maklumat Tidak Lengkap', 'Sila isi SMTP Host dan SMTP Port dahulu.');
        return;
    }
    const doneBtn = setBtnLoading(btn, 'Testing...');
    uiLoading('Ujian SMTP', 'Sedang menguji sambungan SMTP...');
    try {
        const res = await fetch(SETTINGS_API_URL + '?action=test_smtp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ smtpHost, smtpPort })
        });
        const json = await res.json();
        if (!res.ok || !json.ok) throw new Error(json.error || 'Test SMTP gagal.');
        uiCloseLoading();
        await uiSuccess('Berjaya', json.message || 'SMTP berjaya diuji.');
    } catch (e) {
        uiCloseLoading();
        await uiError('Test SMTP Gagal', e.message || 'Unknown error');
    } finally {
        doneBtn();
    }
}

// Create backup
async function createBackup(btn) {
    const doneBtn = setBtnLoading(btn, 'Membuat backup...');
    uiLoading('Backup Sistem', 'Sedang menjana fail backup...');
    try {
        const res = await fetch(SETTINGS_API_URL + '?action=create_backup', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        if (!res.ok || !json.ok) throw new Error(json.error || 'Gagal buat backup.');
        if (json.download_url) {
            window.open(json.download_url, '_blank');
        }
        uiCloseLoading();
        await uiSuccess('Berjaya', json.message || 'Backup berjaya dibuat.');
    } catch (e) {
        uiCloseLoading();
        await uiError('Backup Gagal', e.message || 'Unknown error');
    } finally {
        doneBtn();
    }
}

// Export data
async function exportData(btn) {
    const doneBtn = setBtnLoading(btn, 'Mengeksport...');
    uiLoading('Eksport Data', 'Sedang menjana fail eksport...');
    try {
        const res = await fetch(SETTINGS_API_URL + '?action=export_data', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        if (!res.ok || !json.ok) throw new Error(json.error || 'Gagal eksport data.');
        if (json.download_url) {
            window.open(json.download_url, '_blank');
        }
        uiCloseLoading();
        await uiSuccess('Berjaya', json.message || 'Eksport data berjaya.');
    } catch (e) {
        uiCloseLoading();
        await uiError('Eksport Gagal', e.message || 'Unknown error');
    } finally {
        doneBtn();
    }
}

// View logs
async function viewLogs(btn) {
    const doneBtn = setBtnLoading(btn, 'Memuat log...');
    uiLoading('Log Audit', 'Sedang memuatkan log terkini...');
    try {
        const res = await fetch(SETTINGS_API_URL + '?action=view_logs', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        if (!res.ok || !json.ok) throw new Error(json.error || 'Gagal dapatkan log.');
        const rows = Array.isArray(json.rows) ? json.rows : [];
        if (!rows.length) {
            uiCloseLoading();
            await uiInfo('Log Audit', 'Tiada log untuk dipaparkan.');
            return;
        }
        const safe = (v) => String(v == null ? '' : v)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
        const body = rows.slice(0, 100).map(r => (
            `<tr>
                <td>${safe(r.id)}</td>
                <td>${safe(r.created_at)}</td>
                <td>${safe(r.action)}</td>
                <td>${safe(r.description || '')}</td>
            </tr>`
        )).join('');
        uiCloseLoading();
        if (HAS_SWAL()) {
            await Swal.fire({
                title: 'Log Audit (100 terkini)',
                width: '900px',
                html: `<div style="max-height:55vh;overflow:auto;text-align:left">
                    <table class="table table-sm table-bordered mb-0">
                        <thead><tr><th style="width:70px">ID</th><th style="width:170px">Tarikh</th><th style="width:140px">Aksi</th><th>Keterangan</th></tr></thead>
                        <tbody>${body}</tbody>
                    </table>
                </div>`,
                showConfirmButton: true,
                confirmButtonText: 'Tutup'
            });
        } else {
            const preview = rows.slice(0, 20).map(r => `#${r.id} [${r.created_at}] ${r.action} - ${r.description || ''}`).join('\n');
            console.log('[SETTINGS][logs]\\n' + preview);
            await uiInfo('Log Audit', 'Log telah dipaparkan di console browser.');
        }
    } catch (e) {
        uiCloseLoading();
        await uiError('Lihat Log Gagal', e.message || 'Unknown error');
    } finally {
        doneBtn();
    }
}

// Clear logs
async function clearLogs(btn) {
    const ok = await uiConfirm('Kosongkan Log', 'Adakah anda pasti mahu kosongkan log lama berdasarkan tempoh retention?', 'Teruskan');
    if (!ok) return;

    const doneBtn = setBtnLoading(btn, 'Membersihkan...');
    uiLoading('Kosongkan Log', 'Sedang memadam log lama...');
    try {
        const retention = parseInt(document.getElementById('logRetention')?.value || '90', 10);
        const res = await fetch(SETTINGS_API_URL + '?action=clear_logs', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ days: retention })
        });
        const json = await res.json();
        if (!res.ok || !json.ok) throw new Error(json.error || 'Gagal kosongkan log.');
        uiCloseLoading();
        await uiSuccess('Berjaya', (json.message || 'Log berjaya dibersihkan.') + ` (deleted: ${json.deleted || 0})`);
    } catch (e) {
        uiCloseLoading();
        await uiError('Kosongkan Log Gagal', e.message || 'Unknown error');
    } finally {
        doneBtn();
    }
}

function uploadSettingsAssetXhr(assetType, file) {
    return new Promise((resolve, reject) => {
        const fd = new FormData();
        fd.append('asset_type', assetType);
        fd.append('asset_file', file);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', SETTINGS_API_URL + '?action=upload_asset');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.responseType = 'json';

        xhr.upload.onprogress = function(ev) {
            if (!ev.lengthComputable || !HAS_SWAL()) return;
            const pct = Math.max(0, Math.min(100, Math.round((ev.loaded / ev.total) * 100)));
            const container = Swal.getHtmlContainer();
            if (!container) return;
            const bar = container.querySelector('#settingsUploadBar');
            const text = container.querySelector('#settingsUploadPct');
            if (bar) bar.style.width = pct + '%';
            if (text) text.textContent = pct + '%';
        };

        xhr.onload = function() {
            const status = xhr.status || 0;
            const res = xhr.response || {};
            if (status >= 200 && status < 300 && res && res.ok) {
                resolve(res);
                return;
            }
            reject(new Error((res && res.error) ? res.error : 'Upload gagal.'));
        };
        xhr.onerror = function() { reject(new Error('Ralat rangkaian semasa upload.')); };
        xhr.send(fd);
    });
}

async function uploadSettingsAsset(assetType, fileInputId, hiddenFieldId, previewId) {
    const input = document.getElementById(fileInputId);
    if (!input || !input.files || !input.files[0]) return;
    const file = input.files[0];

    if (HAS_SWAL()) {
        Swal.fire({
            title: 'Muat Naik Aset',
            html: `<div class="text-start small mb-2">Sedang memuat naik fail...</div>
                   <div class="progress" style="height:14px">
                     <div id="settingsUploadBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%"></div>
                   </div>
                   <div class="small mt-2">Progress: <span id="settingsUploadPct">0%</span></div>`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });
    }

    try {
        const json = await uploadSettingsAssetXhr(assetType, file);
        const hidden = document.getElementById(hiddenFieldId);
        if (hidden) hidden.value = json.filename || '';
        const preview = document.getElementById(previewId);
        if (preview && json.url) {
            preview.src = json.url;
            preview.classList.remove('d-none');
        }
        uiCloseLoading();
        await uiSuccess('Berjaya', json.message || 'Fail berjaya dimuat naik.');
    } catch (e) {
        uiCloseLoading();
        await uiError('Upload Gagal', e.message || 'Unknown error');
    } finally {
        input.value = '';
    }
}

// Initialize tabs
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching is handled by CoreUI
    loadAllSettings();
    const headerLogo = document.getElementById('headerLogo');
    const favicon = document.getElementById('favicon');
    const bg = document.getElementById('backgroundImage');
    if (headerLogo) headerLogo.addEventListener('change', function(){ uploadSettingsAsset('headerLogo', 'headerLogo', 'headerLogoPath', 'headerLogoPreview'); });
    if (favicon) favicon.addEventListener('change', function(){ uploadSettingsAsset('favicon', 'favicon', 'faviconPath', 'faviconPreview'); });
    if (bg) bg.addEventListener('change', function(){ uploadSettingsAsset('backgroundImage', 'backgroundImage', 'backgroundImagePath', 'backgroundImagePreview'); });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>
