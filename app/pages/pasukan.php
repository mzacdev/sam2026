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

// Compute participant totals per kontinjen (used to highlight contingents with zero participants)
$participantCounts = [];
try {
    $db = getDB();
    $sql = "SELECT k.id AS kontinjen_id, 
        COALESCE(SUM(a.cnt),0) AS total_atlet,
        COALESCE(SUM(m.cnt),0) AS total_pengurus,
        COALESCE(SUM(co.cnt),0) AS total_jurulatih,
        (COALESCE(SUM(a.cnt),0) + COALESCE(SUM(m.cnt),0) + COALESCE(SUM(co.cnt),0)) AS jumlah_keseluruhan
    FROM table_kontinjen k
    LEFT JOIN table_pasukan p ON p.kontinjen_id = k.id AND p.deleted_at IS NULL AND p.status = 1
    LEFT JOIN (SELECT pasukan_id, COUNT(*) AS cnt FROM table_pasukan_atlet WHERE deleted_at IS NULL GROUP BY pasukan_id) a ON a.pasukan_id = p.id
    LEFT JOIN (SELECT pasukan_id, COUNT(*) AS cnt FROM table_pasukan_pengurus WHERE deleted_at IS NULL GROUP BY pasukan_id) m ON m.pasukan_id = p.id
    LEFT JOIN (SELECT pasukan_id, COUNT(*) AS cnt FROM table_pasukan_jurulatih WHERE deleted_at IS NULL GROUP BY pasukan_id) co ON co.pasukan_id = p.id
    WHERE k.deleted_at IS NULL
    GROUP BY k.id";

    $stmt = $db->query($sql);
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    foreach ($rows as $r) {
        // Store athlete-only counts (exclude pengurus and jurulatih)
        $participantCounts[(int)$r['kontinjen_id']] = (int)$r['total_atlet'];
    }
} catch (Exception $e) {
    error_log('[pasukan.php] participantCounts error: ' . $e->getMessage());
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
                                <div class="h5 mb-0" id="statTotalPasukan"><?php echo (int)$teamStats['total']; ?></div>
                                <div class="small text-muted">Pasukan</div>
                            </div>
                            <div class="me-3 text-center">
                                <div class="h5 mb-0" id="statActivePasukan"><?php echo (int)$teamStats['active']; ?></div>
                                <div class="small text-muted">Aktif</div>
                            </div>
                            <div class="me-3 text-center">
                                <div class="h5 mb-0" id="statInactivePasukan"><?php echo (int)$teamStats['inactive']; ?></div>
                                <div class="small text-muted">Tidak Aktif</div>
                            </div>
                            <div class="me-3 text-center">
                                <div class="h5 mb-0" id="statTotalAtlet"><?php 
                                    $totalAtlet = 0;
                                    foreach ($teams as $t) {
                                        $totalAtlet += (int)($t['atlet_count'] ?? 0);
                                    }
                                    echo $totalAtlet;
                                ?></div>
                                <div class="small text-muted">Jumlah Atlet</div>
                            </div>
                        </div>

                        <div class="btn-group">
                            <button class="btn btn-outline-secondary" id="btnCetak">Cetak</button>
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

    <!-- Filters (moved to header) -->
    <div class="row mb-3">
        <div class="col-12">
            <!-- Left empty: filters are shown in the card header for compact layout -->
        </div>
    </div>

    <!-- Teams List -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-2" style="min-width:200px;">
                            <select class="form-select form-select-sm" id="filterContingent" data-custom="true">
                                <option value="">Semua Kontinjen</option>
                                <?php foreach ($contingents as $c): 
                                    $cid = (int)$c['id'];
                                    $count = isset($participantCounts[$cid]) ? (int)$participantCounts[$cid] : 0;
                                    $label = htmlspecialchars($c['nama_universiti'] ?? '-', ENT_QUOTES, 'UTF-8');
                                    if ($count === 0) {
                                        $attr = ' data-empty="1"';
                                    } else {
                                        $attr = '';
                                    }
                                ?>
                                    <option value="<?php echo $cid; ?>"<?php echo $attr; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="me-2" style="min-width:160px;">
                            <select class="form-select form-select-sm" id="filterSport" data-custom="true">
                                <option value="">Semua Sukan</option>
                                <?php foreach ($sports as $s): ?>
                                    <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['nama_sukan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <label class="small text-muted me-2 mb-0">Tunjuk</label>
                        <select id="pasukanPageSize" class="form-select form-select-sm pasukan-header-control custom-like" style="width:110px">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <input type="search" id="pasukanSearchTop" class="form-control form-control-sm ms-2 pasukan-header-control custom-like" placeholder="Cari pasukan..." style="width:220px">
                    </div>
                </div>
                    <div class="card-body">
                    <div class="table-responsive" id="pasukanTableWrap">
                        <table class="table table-hover table-striped align-middle table-fixed">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width:3%;">#</th>
                                    <th scope="col" style="width:20%;">Nama Pasukan</th>
                                    <th scope="col" style="width:15%;">Kontinjen</th>
                                    <th scope="col" style="width:10%;">Sukan</th>
                                    <th scope="col" style="width:18%;">Pengurus</th>
                                    <th scope="col" style="width:18%;">Jurulatih</th>
                                    <th scope="col" style="width:8%;">Bil. Atlet</th>
                                    <th scope="col" style="width:8%;">Status</th>
                                    <th scope="col">Tindakan</th>
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
                                            <td style="width:3%;"><div class="cell-inner"><?php echo $i + 1; ?></div></td>
                                            <td style="width:20%;"><div class="cell-inner fw-semibold"><?php echo htmlspecialchars($t['nama_pasukan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div></td>
                                            <td style="width:15%;"><div class="cell-inner"><?php echo htmlspecialchars($t['nama_universiti'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div></td>
                                            <td style="width:10%;"><div class="cell-inner"><?php echo htmlspecialchars($t['nama_sukan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div></td>
                                            <td style="width:18%;"><div class="cell-inner small">
                                                    <?php 
                                                    $pengurusList = !empty($t['pengurus_list']) ? explode(', ', $t['pengurus_list']) : [];
                                                    echo !empty($pengurusList) ? htmlspecialchars(implode(', ', array_slice($pengurusList, 0, 2)), ENT_QUOTES, 'UTF-8') : '-';
                                                    if (count($pengurusList) > 2) echo '...';
                                                    ?>
                                                </div></td>
                                            <td style="width:18%;"><div class="cell-inner small">
                                                    <?php 
                                                    $jurulatihList = !empty($t['jurulatih_list']) ? explode(', ', $t['jurulatih_list']) : [];
                                                    echo !empty($jurulatihList) ? htmlspecialchars(implode(', ', array_slice($jurulatihList, 0, 2)), ENT_QUOTES, 'UTF-8') : '-';
                                                    if (count($jurulatihList) > 2) echo '...';
                                                    ?>
                                                </div></td>
                                            <td style="width:8%;" class="text-center"><div class="cell-inner"><span class="badge bg-info"><?php echo (int)($t['atlet_count'] ?? 0); ?></span></div></td>
                                            <td style="width:8%;"><div class="cell-inner">
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
                                            </div></td>
                                            <td><div class="cell-inner">
                                                <a class="btn btn-sm btn-outline-primary edit-pasukan" title="Edit" href="#"
                                                   data-id="<?php echo (int)$t['id']; ?>">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a class="btn btn-sm btn-outline-danger delete-pasukan" title="Padam" href="#" data-id="<?php echo (int)$t['id']; ?>">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </div></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end align-items-center mt-2" id="pasukanPaginationWrap">
                            <nav aria-label="Pasukan pagination">
                                <ul class="pagination pagination-sm mb-0" id="pasukanPagination"></ul>
                            </nav>
                        </div>
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

<style>
/* Custom lightweight dropdown to allow option row highlighting */
.custom-select-wrap{position:relative;display:inline-block;width:100%;}
.custom-select-toggle{width:100%;text-align:left;padding:.375rem .75rem;border:1px solid #ced4da;border-radius:.375rem;background:#fff;cursor:pointer}
.custom-select-toggle:after{content:'▾';float:right;margin-left:.5rem;color:#6c757d}
.custom-select-menu{position:absolute;z-index:1100;width:100%;background:#fff;border:1px solid rgba(0,0,0,0.08);box-shadow:0 6px 18px rgba(16,24,40,0.08);max-height:240px;overflow:auto;border-radius:.375rem;margin-top:.25rem}
.custom-select-item{padding:.4rem .75rem;cursor:pointer}
.custom-select-item:hover{background:#f1f5f9}
.custom-select-item.empty-item{background:#fff3f3;color:#b71c1c}
.custom-select-item.empty-item:hover{background:#ffe6e6}
.custom-select-placeholder{color:#6c757d}
/* Make header controls visually match the custom select toggle */
.pasukan-header-control, .form-select.form-select-sm {
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: .375rem;
    box-shadow: none;
    padding: .375rem .75rem;
    height: 40px !important;
    display: inline-flex !important;
    align-items: center !important;
    font-size: .95rem !important;
}
.pasukan-header-control { min-width: 110px; }

/* Make pagination links and header search visually match the custom select toggle */
#pasukanPaginationWrap .pagination .page-link,
#pasukanPaginationWrap .pagination .page-item > .page-link,
#pasukanSearchTop,
#pasukanPageSize {
    /* match custom-select-toggle exactly */
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: .375rem;
    box-shadow: none;
    padding: .375rem .75rem;
    height: 40px !important;
    line-height: normal !important;
    display: inline-flex !important;
    align-items: center !important;
    font-size: .95rem !important;
    color: #495057;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    background-image: linear-gradient(45deg, transparent 50%, #495057 50%), linear-gradient(135deg, #495057 50%, transparent 50%);
    background-position: calc(100% - 18px) 50%, calc(100% - 12px) 50%;
    background-size: 6px 6px,6px 6px;
    background-repeat: no-repeat;
    padding-right: 2.8rem !important;
}
#pasukanSearchTop {
    /* match toggle visual (no arrow) */
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: .375rem;
    box-shadow: none;
    padding: .375rem .75rem;
    height: 40px !important;
    line-height: normal !important;
    display: inline-flex !important;
    align-items: center !important;
    font-size: .95rem !important;
    color: #495057;
}
#pasukanPageSize.custom-like, #pasukanSearchTop.custom-like {
    box-sizing: border-box !important;
    font-family: inherit !important;
    font-weight: 400 !important;
    text-align: left !important;
    padding: .375rem .75rem !important;
    height: 40px !important;
    line-height: 1.2 !important;
    border: 1px solid #ced4da !important;
    border-radius: .375rem !important;
    background: #fff !important;
    color: #495057 !important;
    display: inline-flex !important;
    align-items: center !important;
}

/* Add toggle-like arrow for page-size to match select */
#pasukanPageSize.custom-like {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    background-image: linear-gradient(45deg, transparent 50%, #495057 50%), linear-gradient(135deg, #495057 50%, transparent 50%);
    background-position: calc(100% - 18px) 50%, calc(100% - 12px) 50%;
    background-size: 6px 6px,6px 6px;
    background-repeat: no-repeat;
    padding-right: 2.8rem !important;
}

/* Force pagination buttons to use same box-sizing and border so they visually match */
#pasukanPaginationWrap .pagination .page-link,
#pasukanPaginationWrap .pagination .page-item > .page-link {
    box-sizing: border-box !important;
    border: 1px solid #ced4da !important;
    border-radius: .375rem !important;
    background: #fff !important;
    height: 40px !important;
    display: inline-flex !important;
    align-items: center !important;
    padding: 0 .75rem !important;
}
#pasukanPaginationWrap .pagination .page-link,
#pasukanPaginationWrap .pagination .page-item > .page-link {
    padding: 0 .75rem;
    height: 40px !important;
    line-height: normal;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #ced4da;
    border-radius: .375rem;
    background: #fff;
}
#pasukanPaginationWrap .pagination .page-link { margin-left: .25rem; }
#pasukanPaginationWrap .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}
/* Table fixed layout and single-line rows */
.table-fixed{table-layout:fixed;width:100%}
.table-fixed th,.table-fixed td{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle}
.table-fixed td .cell-inner{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.table-responsive{overflow-x:auto;overflow-y:visible}

/* Print only the table content when user clicks Cetak */
@media print {
    /* hide everything by default */
    body * { visibility: hidden !important; }
    /* show only the table wrapper and its contents */
    #pasukanTableWrap, #pasukanTableWrap * { visibility: visible !important; }
    /* position table at the top-left for printing */
    #pasukanTableWrap { position: absolute !important; left: 0; top: 0; width: 100% !important; }
}

/* Make page-size select and pagination styling */
#pasukanPaginationWrap {
    gap: .5rem;
    --pasukan-control-height: 40px; /* unified control height */
    --pasukan-pager-height: var(--pasukan-control-height);
}
#pasukanPaginationWrap .form-select-sm {
    box-sizing: border-box;
    padding: 0 .6rem;
    font-size: .95rem;
    height: var(--pasukan-control-height);
    line-height: normal;
    border-radius: .375rem;
    border: 1px solid #dee2e6;
    background-color: #fff;
    display: inline-flex;
    align-items: center;
    min-width: 110px;
}
#pasukanPaginationWrap .pagination .page-link {
    padding: 0 .6rem;
    font-size: .95rem;
    height: var(--pasukan-pager-height);
    line-height: normal;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
/* header controls (select + search) */
#pasukanPageSize, .pasukan-header-control, #pasukanSearchTop {
    /* Force select to use select-height */
    height: var(--pasukan-control-height) !important;
    line-height: var(--pasukan-control-height) !important;
    padding: 0 .75rem !important;
    font-size: .95rem !important;
    display: inline-flex !important;
    align-items: center !important;
    box-sizing: border-box !important;
    border-radius: .375rem !important;
    /* Remove native appearance and add custom arrow so sizing is consistent */
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    background-image: linear-gradient(45deg, transparent 50%, #495057 50%), linear-gradient(135deg, #495057 50%, transparent 50%);
    background-position: calc(100% - 14px) calc(50% - 2px), calc(100% - 9px) calc(50% - 2px);
    background-size: 6px 6px,6px 6px;
    background-repeat: no-repeat;
    padding-right: 2.5rem !important;
}
#pasukanSearchTop {
    /* Match the header page-size select */
    height: var(--pasukan-select-height) !important;
    line-height: var(--pasukan-select-height) !important;
    padding: 0 .75rem !important;
    font-size: .95rem !important;
    display: inline-flex !important;
    align-items: center !important;
    box-sizing: border-box !important;
    border-radius: .375rem !important;
    border: 1px solid #dee2e6 !important;
    background-color: #fff !important;
    width: 220px !important;
}
#pasukanPaginationWrap .pagination .page-link {
    padding: 0 .75rem;
    font-size: .95rem;
    height: var(--pasukan-pager-height);
    line-height: var(--pasukan-pager-height);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
#pasukanPaginationWrap .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}
#pasukanPaginationWrap .page-link {
    color: #495057;
}

/* Force explicit matching box sizing/padding for Chrome */
@supports (-webkit-appearance:none) {
    #pasukanPaginationWrap .form-select-sm,
    #pasukanPaginationWrap .pagination .page-link,
    #pasukanPageSize,
    .pasukan-header-control,
    #pasukanSearchTop {
        height: var(--pasukan-control-height) !important;
        min-height: var(--pasukan-control-height) !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        box-sizing: border-box !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: .95rem !important;
    }
    /* adjust select arrow position for Chrome */
    #pasukanPageSize {
        background-position: calc(100% - 18px) 50%, calc(100% - 12px) 50% !important;
        padding-right: 2.8rem !important;
    }
}

/* Ensure page-link box sizing aligns with select */
#pasukanPaginationWrap .pagination .page-link,
#pasukanPaginationWrap .pagination .page-item > .page-link {
    box-sizing: border-box;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
    // Remove duplicate selects with same ID (if any) to avoid double-hidden originals
    ['filterContingent','filterSport'].forEach(function(id){
        var nodes = Array.from(document.querySelectorAll('select#' + id));
        if (nodes.length > 1) {
            // keep the first, remove the rest
            nodes.slice(1).forEach(function(n){ n.parentNode && n.parentNode.removeChild(n); });
        }
    });
    function buildCustom(select){
        if (!select || select.dataset._customInit) return;
        select.dataset._customInit = '1';
        // hide original select but keep it in DOM for forms
        select.style.display = 'none';

        var wrap = document.createElement('div'); wrap.className = 'custom-select-wrap';
        var toggle = document.createElement('button'); toggle.type='button'; toggle.className='custom-select-toggle';
        var placeholder = select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : '';
        toggle.innerHTML = '<span class="custom-select-placeholder">' + (placeholder || select.getAttribute('data-placeholder') || 'Pilih...') + '</span>';

        var menu = document.createElement('div'); menu.className='custom-select-menu'; menu.style.display='none';

        Array.from(select.options).forEach(function(opt, idx){
            if (!opt.value && opt.value !== '0') {
                // include even empty value (like default)
            }
            var item = document.createElement('div'); item.className='custom-select-item';
            item.textContent = opt.text;
            item.dataset.value = opt.value;
            if (opt.getAttribute('data-empty') === '1') item.classList.add('empty-item');
            if (opt.disabled) item.classList.add('disabled');
            item.addEventListener('click', function(e){
                // set underlying hidden input/select value
                try {
                    select.value = this.dataset.value;
                } catch (err) {
                    console.warn('set value error', err);
                }
                // trigger change event on the input/select
                var ev = new Event('change', {bubbles:true});
                try { select.dispatchEvent(ev); } catch (err) { console.warn('dispatch change error', err); }
                // update toggle label
                toggle.querySelector('.custom-select-placeholder').textContent = this.textContent;
                closeMenu();
            });
            menu.appendChild(item);
        });

        function closeMenu(){ menu.style.display='none'; document.removeEventListener('click', outsideClick); }
        function openMenu(){ menu.style.display='block'; setTimeout(function(){ document.addEventListener('click', outsideClick);}, 10); }
        function outsideClick(e){ if (!wrap.contains(e.target)) closeMenu(); }

        toggle.addEventListener('click', function(e){ e.preventDefault(); if (menu.style.display === 'block') closeMenu(); else openMenu(); });

        wrap.appendChild(toggle); wrap.appendChild(menu);
        select.parentNode.insertBefore(wrap, select.nextSibling);


        // Auto-size the custom select wrapper to fit the longest option text
        try {
            var measurer = document.createElement('span');
            measurer.style.position = 'absolute';
            measurer.style.visibility = 'hidden';
            measurer.style.whiteSpace = 'nowrap';
            measurer.style.fontSize = window.getComputedStyle(toggle).fontSize || '14px';
            measurer.style.fontFamily = window.getComputedStyle(toggle).fontFamily || 'inherit';
            document.body.appendChild(measurer);
            var maxW = 0;
            Array.from(select.options).forEach(function(o){
                measurer.textContent = o.text || '';
                var w = measurer.offsetWidth;
                if (w > maxW) maxW = w;
            });
            document.body.removeChild(measurer);
            // add padding for arrow and internal padding — increase to better fit long names
            var extra = 140; // more generous padding to accommodate fonts and arrows
            var desired = Math.max(140, maxW + extra);
            wrap.style.minWidth = desired + 'px';
            // ensure the toggle and menu also expand to match
            try { toggle.style.minWidth = '100%'; menu.style.minWidth = desired + 'px'; } catch(e){}
        } catch (e) {
            // ignore measurement errors
            console.warn('custom select autosize error', e);
        }

        // Sync initial selected label
        select.addEventListener('change', function(){
            var opt = select.options[select.selectedIndex];
            if (opt) toggle.querySelector('.custom-select-placeholder').textContent = opt.text;
        });

        // Now replace original <select> with a hidden input (single DOM element) to avoid seeing duplicates
        try {
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.id = select.id || '';
            if (select.name) hidden.name = select.name;
            hidden.value = select.value || '';
            select.parentNode.insertBefore(hidden, select);
            select.parentNode.removeChild(select);
            // point `select` variable to the hidden input for later handlers
            select = hidden;
        } catch (e) {
            console.warn('custom select replace error', e);
        }
    }

    var targets = document.querySelectorAll('select[data-custom="true"]');
    targets.forEach(function(s){ buildCustom(s); });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function(){
    // Sync header search with main search input and trigger load
    var top = document.getElementById('pasukanSearchTop');
    var main = document.getElementById('pasukanSearch');
    if (top) {
        top.addEventListener('input', function(){
            if (main) main.value = this.value;
            if (window.pasukanDebounced) {
                window.pasukanDebounced();
            } else {
                loadPasukanList();
            }
        });
    }
    if (main && top) {
        main.addEventListener('input', function(){
            top.value = this.value;
        });
    }
});
</script>

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
                                                    <select id="pasukanKontinjen" name="kontinjen_id" class="form-select" required data-custom="true">
                                    <option value="">Sila Pilih</option>
                                    <?php foreach ($contingents as $c): 
                                        $cid = (int)$c['id'];
                                        $count = isset($participantCounts[$cid]) ? (int)$participantCounts[$cid] : 0;
                                        $label = htmlspecialchars($c['nama_universiti'] ?? '-', ENT_QUOTES, 'UTF-8');
                                        if ($count === 0) {
                                            $attr = ' data-empty="1"';
                                        } else {
                                            $attr = '';
                                        }
                                    ?>
                                        <option value="<?php echo $cid; ?>"<?php echo $attr; ?>><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pasukanSukan" class="form-label">Sukan <span class="text-danger">*</span></label>
                                <select id="pasukanSukan" name="sukan_id" class="form-select" required data-custom="true">
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
    // create a shared debounced loader so top and main search share the same timer
    window.pasukanDebounced = debounce(loadPasukanList, 300);
    document.getElementById('pasukanSearch')?.addEventListener('input', window.pasukanDebounced);
    
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
    
        // Print handler: print only the table when Cetak button clicked
        document.addEventListener('DOMContentLoaded', function(){
            var btn = document.getElementById('btnCetak');
            if (btn) {
                btn.addEventListener('click', function(e){
                    e.preventDefault();
                    window.print();
                });
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
            const statTotalPasukan = document.getElementById('statTotalPasukan');
            const statActivePasukan = document.getElementById('statActivePasukan');
            const statInactivePasukan = document.getElementById('statInactivePasukan');
            const statTotalAtlet = document.getElementById('statTotalAtlet');
            
            if (statTotalPasukan) statTotalPasukan.textContent = stats.total || 0;
            if (statActivePasukan) statActivePasukan.textContent = stats.active || 0;
            if (statInactivePasukan) statInactivePasukan.textContent = stats.inactive || 0;
            
            // Calculate total atlet from visible teams (filtered/search results)
            let totalAtlet = 0;
            teams.forEach(t => {
                totalAtlet += parseInt(t.atlet_count || 0);
            });
            if (statTotalAtlet) statTotalAtlet.textContent = totalAtlet;
            
            if (teams.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-5"><i class="cil cil-people" style="font-size: 2rem;"></i><p class="mt-2">Tiada pasukan didaftarkan — klik "Daftar Pasukan Baru" untuk mula menambah.</p></td></tr>';
                // Clear any pagination
                window.pasukanTeams = [];
                buildPasukanPagination(1);
            } else {
                // Store teams and render first page with pagination
                window.pasukanTeams = teams;
                window.pasukanCurrentPage = 1;
                window.pasukanPageSize = parseInt(document.getElementById('pasukanPageSize')?.value) || 10;
                renderPasukanPage(1);
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

// --- Client-side pagination for Pasukan list ---
window.pasukanTeams = window.pasukanTeams || [];
window.pasukanCurrentPage = window.pasukanCurrentPage || 1;
window.pasukanPageSize = window.pasukanPageSize || 10;

function renderPasukanPage(page) {
    const tbody = document.getElementById('pasukanTableBody');
    if (!tbody) return;
    const teams = window.pasukanTeams || [];
    const pageSize = parseInt(window.pasukanPageSize) || 10;
    const total = teams.length;
    const totalPages = Math.max(1, Math.ceil(total / pageSize));
    page = Math.min(Math.max(1, page), totalPages);
    window.pasukanCurrentPage = page;

    const start = (page - 1) * pageSize;
    const slice = teams.slice(start, start + pageSize);

    if (slice.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-5"><i class="cil cil-people" style="font-size: 2rem;"></i><p class="mt-2">Tiada pasukan didaftarkan — klik "Daftar Pasukan Baru" untuk mula menambah.</p></td></tr>';
    } else {
        let html = '';
        slice.forEach((t, i) => {
            const idx = start + i + 1;
            const status = parseInt(t.status || 0);
            const badgeClass = status == 1 ? 'bg-success' : 'bg-secondary';
            const statusText = status == 1 ? 'Aktif' : 'Tidak Aktif';
            const pengurusList = (t.pengurus_list || '').split(', ').filter(x => x);
            const jurulatihList = (t.jurulatih_list || '').split(', ').filter(x => x);

            html += '<tr>';
            html += '<td>' + idx + '</td>';
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

    buildPasukanPagination(totalPages);
}

function buildPasukanPagination(totalPages) {
    const ul = document.getElementById('pasukanPagination');
    if (!ul) return;
    const current = window.pasukanCurrentPage || 1;
    ul.innerHTML = '';

    function createLi(label, page, disabled, active) {
        const li = document.createElement('li');
        li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.dataset.page = page;
        a.innerHTML = label;
        a.addEventListener('click', function(e){
            e.preventDefault();
            if (disabled) return;
            renderPasukanPage(parseInt(this.dataset.page));
        });
        li.appendChild(a);
        return li;
    }

    // Prev
    ul.appendChild(createLi('&laquo;', Math.max(1, current - 1), current <= 1, false));

    // page window
    let start = Math.max(1, current - 3);
    let end = Math.min(totalPages, current + 3);
    if (current <= 4) start = 1;
    if (current + 3 >= totalPages) end = totalPages;

    for (let p = start; p <= end; p++) {
        ul.appendChild(createLi(p, p, false, p === current));
    }

    // Next
    ul.appendChild(createLi('&raquo;', Math.min(totalPages, current + 1), current >= totalPages, false));

    // page size change handler
    const sizeSelect = document.getElementById('pasukanPageSize');
    if (sizeSelect && !sizeSelect.dataset._init) {
        sizeSelect.dataset._init = '1';
        sizeSelect.value = String(window.pasukanPageSize || 10);
        sizeSelect.addEventListener('change', function(){
            window.pasukanPageSize = parseInt(this.value) || 10;
            renderPasukanPage(1);
        });
    }
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
