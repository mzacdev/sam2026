<?php
/**
 * Dashboard Page
 */
require_once 'config.php';
// Authentication is handled in layout.php

$page_title = 'Papan Pemuka';

// Start output buffering to capture content
ob_start();
?>
<div class="w-100 px-3">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">Papan Pemuka</h2>
            <p class="text-muted">Selamat datang ke papan pemuka anda</p>
        </div>
    </div>

    <!-- Event Banner -->
    <div class="row mb-4" id="eventBannerContainer" style="display: block;">
        <div class="col-12">
            <div class="event-banner position-relative">
                <img src="<?php echo asset('img/banners/sam2026-banner.jpg'); ?>" 
                     alt="SAM 2026 - Sukan Asasi Malaysia Kali Ke-9 | 30 Jan - 1 Feb 2026 | UPNM Kem Sungai Besi, Kuala Lumpur" 
                     class="img-fluid event-banner-image"
                     onload="if(document.getElementById('eventBannerContainer')) { document.getElementById('eventBannerContainer').style.display='block'; }">
                <button type="button" class="btn-close event-banner-close" 
                        aria-label="Tutup banner" 
                        onclick="if(confirm('Tutup banner ini?')) { document.getElementById('eventBannerContainer').style.display='none'; localStorage.setItem('eventBannerDismissed', 'true'); localStorage.setItem('eventBannerDismissedDate', new Date().getTime()); }">
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-4 fw-semibold">0</div>
                            <div class="text-medium-emphasis small">Jumlah Kontinjen</div>
                        </div>
                        <div>
                            <i class="icon cil cil-people text-primary" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-4 fw-semibold">0</div>
                            <div class="text-medium-emphasis small">Jumlah Sukan</div>
                        </div>
                        <div>
                            <i class="icon cil cil-gamepad text-success" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-4 fw-semibold">0</div>
                            <div class="text-medium-emphasis small">Jumlah Atlet</div>
                        </div>
                        <div>
                            <i class="icon cil cil-user text-info" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-4 fw-semibold">0</div>
                            <div class="text-medium-emphasis small">Acara Selesai</div>
                        </div>
                        <div>
                            <i class="icon cil cil-check-circle text-warning" style="font-size: 2rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Gambaran Keseluruhan Kejohanan</strong>
                </div>
                <div class="card-body">
                    <p>Selamat datang ke Sistem Pengurusan Kejohanan <?php echo SITE_FULL_NAME; ?> (<?php echo SITE_NAME; ?>).</p>
                    <p>Sistem ini membolehkan anda menguruskan pendaftaran kontinjen, sukan, atlet, venue, keputusan pertandingan, dan jadual pingat.</p>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Aktiviti Terkini</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="cil cil-check text-success me-2"></i> Tiada aktiviti terkini</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Notis Penting</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="cil cil-info text-info me-2"></i> Sila lengkapkan pendaftaran kontinjen</li>
                                <li class="mb-2"><i class="cil cil-info text-info me-2"></i> Daftarkan sukan dan acara</li>
                                <li class="mb-2"><i class="cil cil-info text-info me-2"></i> Daftarkan atlet untuk setiap kontinjen</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

// Include the layout
require_once 'includes/layout.php';
?>

