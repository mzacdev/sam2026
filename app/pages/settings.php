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
            header('Location: ' . BASE_URL . 'pages/access-denied.php');
            exit;
        } else {
            // Headers already sent - output redirect and stop
            echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=' . BASE_URL . 'pages/access-denied.php"></head><body><script>window.location.href="' . BASE_URL . 'pages/access-denied.php";</script></body></html>';
            exit;
        }
    }
}

$page_title = 'Tetapan';

ob_start();
?>
<div class="w-100 px-3">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">Tetapan Sistem</h2>
                    <p class="text-muted">Konfigurasi dan urus tetapan sistem pengurusan kejohanan</p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-secondary me-2" onclick="resetAllSettings()">
                        <i class="cil cil-reload me-1"></i> Reset
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveAllSettings()">
                        <i class="cil cil-save me-1"></i> Simpan Semua
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Navigation Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-coreui-toggle="tab" data-coreui-target="#general" type="button" role="tab">
                        <i class="cil cil-cog me-1"></i> Umum
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tournament-tab" data-coreui-toggle="tab" data-coreui-target="#tournament" type="button" role="tab">
                        <i class="cil cil-trophy me-1"></i> Kejohanan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="registration-tab" data-coreui-toggle="tab" data-coreui-target="#registration" type="button" role="tab">
                        <i class="cil cil-user-plus me-1"></i> Pendaftaran
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="notification-tab" data-coreui-toggle="tab" data-coreui-target="#notification" type="button" role="tab">
                        <i class="cil cil-bell me-1"></i> Notifikasi
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="user-tab" data-coreui-toggle="tab" data-coreui-target="#user" type="button" role="tab">
                        <i class="cil cil-people me-1"></i> Pengguna & Akses
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="display-tab" data-coreui-toggle="tab" data-coreui-target="#display" type="button" role="tab">
                        <i class="cil cil-paint me-1"></i> Paparan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="system-tab" data-coreui-toggle="tab" data-coreui-target="#system" type="button" role="tab">
                        <i class="cil cil-devices me-1"></i> Sistem
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="tab-content" id="settingsTabContent">
        
        <!-- Tab 1: General Settings -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="cil cil-info me-2"></i><strong>Maklumat Sistem</strong>
                        </div>
                        <div class="card-body">
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
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="cil cil-globe-alt me-2"></i><strong>Lokal & Zon Masa</strong>
                        </div>
                        <div class="card-body">
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
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <i class="cil cil-trophy me-2"></i><strong>Maklumat Kejohanan</strong>
                        </div>
                        <div class="card-body">
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
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <i class="cil cil-map me-2"></i><strong>Lokasi & Venue</strong>
                        </div>
                        <div class="card-body">
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
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <i class="cil cil-calendar me-2"></i><strong>Tempoh Pendaftaran</strong>
                        </div>
                        <div class="card-body">
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
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="regAutoClose" name="regAutoClose" checked>
                                        <label class="form-check-label" for="regAutoClose">
                                            Tutup pendaftaran secara automatik selepas tarikh tutup
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="regAllowLate" name="regAllowLate">
                                        <label class="form-check-label" for="regAllowLate">
                                            Benarkan pendaftaran lewat (dengan kelulusan)
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <i class="cil cil-dollar me-2"></i><strong>Yuran & Bayaran</strong>
                        </div>
                        <div class="card-body">
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
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="regFeeRequired" name="regFeeRequired">
                                        <label class="form-check-label" for="regFeeRequired">
                                            Yuran pendaftaran wajib
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <i class="cil cil-limit-e875 me-2"></i><strong>Had & Sekatan</strong>
                        </div>
                        <div class="card-body">
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

                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <i class="cil cil-file me-2"></i><strong>Syarat & Dokumen</strong>
                        </div>
                        <div class="card-body">
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
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <i class="cil cil-envelope-letter me-2"></i><strong>E-mel Notifikasi</strong>
                        </div>
                        <div class="card-body">
                            <form id="emailNotificationForm">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emailEnabled" name="emailEnabled" checked>
                                        <label class="form-check-label" for="emailEnabled">
                                            Aktifkan notifikasi e-mel
                                        </label>
                                    </div>
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
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <i class="cil cil-phone me-2"></i><strong>SMS Notifikasi</strong>
                        </div>
                        <div class="card-body">
                            <form id="smsNotificationForm">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="smsEnabled" name="smsEnabled">
                                        <label class="form-check-label" for="smsEnabled">
                                            Aktifkan notifikasi SMS
                                        </label>
                                    </div>
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

                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <i class="cil cil-bell me-2"></i><strong>Jenis Notifikasi</strong>
                        </div>
                        <div class="card-body">
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
                    <div class="card mb-4">

                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <i class="cil cil-shield-alt me-2"></i><strong>Keselamatan</strong>
                        </div>
                        <div class="card-body">
                            <form id="securitySettingsForm">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="twoFactorAuth" name="twoFactorAuth">
                                        <label class="form-check-label" for="twoFactorAuth">
                                            Aktifkan Pengesahan Dua Faktor (2FA)
                                        </label>
                                    </div>
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
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="passwordRequireUppercase" name="passwordRequireUppercase" checked>
                                        <label class="form-check-label" for="passwordRequireUppercase">
                                            Perlu huruf besar
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="passwordRequireNumber" name="passwordRequireNumber" checked>
                                        <label class="form-check-label" for="passwordRequireNumber">
                                            Perlu nombor
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="passwordRequireSpecial" name="passwordRequireSpecial">
                                        <label class="form-check-label" for="passwordRequireSpecial">
                                            Perlu aksara khas
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <i class="cil cil-lock-locked me-2"></i><strong>Kebenaran Akses</strong>
                        </div>
                        <div class="card-body">
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
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <i class="cil cil-paint me-2"></i><strong>Logo & Ikon</strong>
                        </div>
                        <div class="card-body">
                            <form id="logoSettingsForm">
                                <div class="mb-3">
                                    <label for="headerLogo" class="form-label">Logo Header</label>
                                    <input type="file" class="form-control" id="headerLogo" name="headerLogo" accept="image/*">
                                    <div class="form-text">Logo yang dipaparkan di header (disyorkan: 180x180px)</div>
                                    <div class="mt-2">
                                        <img src="<?php echo logo(LOGO_HEADER); ?>" alt="Current Logo" class="img-thumbnail" style="max-height: 60px;">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="favicon" class="form-label">Favicon</label>
                                    <input type="file" class="form-control" id="favicon" name="favicon" accept="image/*">
                                    <div class="form-text">Ikon untuk tab pelayar (disyorkan: 32x32px atau 16x16px)</div>
                                </div>

                                <div class="mb-3">
                                    <label for="backgroundImage" class="form-label">Gambar Latar Belakang</label>
                                    <input type="file" class="form-control" id="backgroundImage" name="backgroundImage" accept="image/*">
                                    <div class="form-text">Gambar latar belakang untuk semua halaman</div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <i class="cil cil-palette me-2"></i><strong>Tema & Warna</strong>
                        </div>
                        <div class="card-body">
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
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <i class="cil cil-data-transfer-down me-2"></i><strong>Backup & Eksport</strong>
                        </div>
                        <div class="card-body">
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
                                    <button type="button" class="btn btn-outline-primary" onclick="createBackup()">
                                        <i class="cil cil-save me-1"></i> Buat Backup Sekarang
                                    </button>
                                    <button type="button" class="btn btn-outline-success" onclick="exportData()">
                                        <i class="cil cil-arrow-bottom me-1"></i> Eksport Data
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <i class="cil cil-wrench me-2"></i><strong>Mod Penyelenggaraan</strong>
                        </div>
                        <div class="card-body">
                            <form id="maintenanceForm">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="maintenanceMode" name="maintenanceMode">
                                        <label class="form-check-label" for="maintenanceMode">
                                            Aktifkan Mod Penyelenggaraan
                                        </label>
                                    </div>
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
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <i class="cil cil-list me-2"></i><strong>Log & Audit</strong>
                        </div>
                        <div class="card-body">
                            <form id="logSettingsForm">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="enableAuditLog" name="enableAuditLog" checked>
                                        <label class="form-check-label" for="enableAuditLog">
                                            Aktifkan Log Audit
                                        </label>
                                    </div>
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
                                    <button type="button" class="btn btn-outline-info" onclick="viewLogs()">
                                        <i class="cil cil-list me-1"></i> Lihat Log
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" onclick="clearLogs()">
                                        <i class="cil cil-trash me-1"></i> Kosongkan Log
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Save Success Alert -->
    <div class="alert alert-success alert-dismissible fade d-none" id="saveSuccessAlert" role="alert">
        <i class="cil cil-check-circle me-2"></i>
        <strong>Berjaya!</strong> Tetapan telah disimpan.
        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
    </div>

    <!-- Save Error Alert -->
    <div class="alert alert-danger alert-dismissible fade d-none" id="saveErrorAlert" role="alert">
        <i class="cil cil-warning me-2"></i>
        <strong>Ralat!</strong> Gagal menyimpan tetapan. Sila cuba lagi.
        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
    </div>
</div>

<script>
// Save all settings
function saveAllSettings() {
    // Collect all form data
    const allForms = document.querySelectorAll('#settingsTabContent form');
    const allData = {};
    
    allForms.forEach(form => {
        const formData = new FormData(form);
        const formId = form.id;
        allData[formId] = {};
        
        for (let [key, value] of formData.entries()) {
            if (allData[formId][key]) {
                // Handle multiple values (checkboxes, etc.)
                if (Array.isArray(allData[formId][key])) {
                    allData[formId][key].push(value);
                } else {
                    allData[formId][key] = [allData[formId][key], value];
                }
            } else {
                allData[formId][key] = value;
            }
        }
    });
    
    // Show loading
    const saveBtn = event.target;
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
    
    // Simulate save (replace with actual AJAX call)
    setTimeout(() => {
        // Show success message
        const successAlert = document.getElementById('saveSuccessAlert');
        successAlert.classList.remove('d-none');
        successAlert.classList.add('show');
        
        // Reset button
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
        
        // Hide alert after 3 seconds
        setTimeout(() => {
            successAlert.classList.remove('show');
            setTimeout(() => successAlert.classList.add('d-none'), 150);
        }, 3000);
    }, 1000);
}

// Reset all settings
function resetAllSettings() {
    if (confirm('Adakah anda pasti mahu reset semua tetapan kepada nilai lalai?')) {
        // Reload page or reset forms
        location.reload();
    }
}

// Create backup
function createBackup() {
    alert('Fungsi backup akan dilaksanakan kemudian.');
}

// Export data
function exportData() {
    alert('Fungsi eksport data akan dilaksanakan kemudian.');
}

// View logs
function viewLogs() {
    alert('Fungsi lihat log akan dilaksanakan kemudian.');
}

// Clear logs
function clearLogs() {
    if (confirm('Adakah anda pasti mahu kosongkan semua log?')) {
        alert('Fungsi kosongkan log akan dilaksanakan kemudian.');
    }
}

// Initialize tabs
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching is handled by CoreUI
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>
