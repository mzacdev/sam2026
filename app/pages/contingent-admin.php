<?php
/**
 * Contingent Admin View
 * Roles: ADMIN, ORGANIZER, JUDGE
 * Shows all active contingents with Show/Hide inline participants
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/rbac.php';

Session::start();
$auth = getAuth();
$auth->requireAuth();
$rbac = getRBAC();
// Minimum JUDGE (includes ORGANIZER, ADMIN)
$rbac->requireMinimumRole('JUDGE');

$page_title = 'Kontinjen (Admin)';

// Fetch contingents (active only)
$contingents = [];
try {
    $pdo = getDB();
    $sql = "SELECT
        k.id,
        u.nama_universiti,
        k.kod_universiti,
        k.nama_pegawai_untuk_dihubungi,
        k.alamat,
        k.emel,
        k.no_telefon,
        COALESCE(SUM(a.cnt),0) AS jumlah_atlet,
        CASE WHEN u.status = 1 THEN 'Aktif' ELSE 'Tidak Aktif' END AS status_universiti,
        k.created_at
    FROM table_kontinjen k
    INNER JOIN table_ref_universiti u
        ON k.kod_universiti = u.kod_universiti
        AND u.status = 1
    LEFT JOIN table_pasukan p
        ON p.kontinjen_id = k.id
        AND p.deleted_at IS NULL
        AND p.status = 1
    LEFT JOIN (
        SELECT pasukan_id, COUNT(*) AS cnt
        FROM table_pasukan_atlet
        WHERE deleted_at IS NULL
        GROUP BY pasukan_id
    ) a ON a.pasukan_id = p.id
    WHERE k.deleted_at IS NULL
        AND k.status = 1
    GROUP BY
        k.id,
        u.nama_universiti,
        k.kod_universiti,
        k.nama_pegawai_untuk_dihubungi,
        k.alamat,
        k.emel,
        k.no_telefon,
        u.status,
        k.created_at
    ORDER BY k.created_at DESC
    LIMIT 1000";

    $stmt = $pdo->query($sql);
    $contingents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('[contingent-admin.php] DB error: ' . $e->getMessage());
}

ob_start();
?>
<div class="w-100 px-3">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light border-0 shadow-sm overflow-hidden">
                <div class="card-body py-4 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                    <div>
                        <h2 class="mb-1">Kontinjen (Admin/Organizer/Judge)</h2>
                        <p class="text-muted mb-0">Paparan semua kontinjen aktif. Klik Show untuk melihat peserta.</p>
                    </div>
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
                        <strong>Senarai Kontinjen Aktif</strong>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive" id="contingentTableWrap">
                        <table class="table table-hover table-striped align-middle table-fixed">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" style="width:3%;">#</th>
                                    <th scope="col" style="width:23%;">Nama Kontingen</th>
                                    <th scope="col" style="width:10%;">Kod</th>
                                    <th scope="col" style="width:30%;">Pegawai</th>
                                    <th scope="col" style="width:10%;">Telefon</th>
                                    <th scope="col" style="width:8%;">Jumlah Atlet</th>
                                    <th scope="col" style="width:8%;">Status</th>
                                    <th scope="col" style="width:8%;">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contingents as $idx => $c): ?>
                                    <tr data-kontinjen="<?php echo htmlspecialchars($c['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <td><?php echo $idx + 1; ?></td>
                                        <td><?php echo htmlspecialchars($c['nama_universiti'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($c['kod_universiti'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($c['nama_pegawai_untuk_dihubungi'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($c['no_telefon'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php $count = (int)($c['jumlah_atlet'] ?? 0); ?>
                                            <?php if ($count === 0): ?>
                                                <span class="badge badge-pill badge-danger"><?php echo $count; ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-pill badge-primary"><?php echo $count; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($c['status_universiti'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary contingent-view-btn" data-kid="<?php echo htmlspecialchars($c['id'], ENT_QUOTES, 'UTF-8'); ?>">Show</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.details-top-controls{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:6px}
.details-top-controls .left-controls{display:flex;align-items:center;gap:8px}
.details-top-controls .right-controls{display:flex;align-items:center;gap:8px}
.details-top-controls .right-controls .details-search{width:220px}
.details-top-controls .left-controls .details-select{max-width:60%}
.details-top-controls .left-controls .details-select.select-sukan{min-width:220px;width:220px}
.details-top-controls .left-controls .details-select.select-acara{min-width:280px;width:320px}
.details-top-controls .right-controls .details-pagesize{width:90px}
@media (max-width:768px){
    .details-top-controls{flex-wrap:wrap}
    .details-top-controls .right-controls .details-search{width:140px}
    .details-top-controls .left-controls .details-select{width:120px}
    .details-top-controls .left-controls .details-select.select-sukan{min-width:160px;width:160px}
    .details-top-controls .left-controls .details-select.select-acara{min-width:180px;width:180px}
}
.no-data-badge{display:inline-block;padding:0.18rem 0.45rem;border-radius:0.35rem;background:#fdecea;color:#842029;font-size:0.8rem}
.details-row .table{table-layout:fixed;width:100%}
.details-row .table th,.details-row .table td{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.details-row .table th:first-child,.details-row .table td:first-child{text-align:center;}
.details-row .p-3{overflow:auto}
.row-highlight{background:rgba(13,110,253,0.08);}
.role-pengurus td:first-child,.role-jurulatih td:first-child{border-left:4px solid #f0ad4e;}
.role-pengurus td,.role-jurulatih td{background:rgba(255,245,204,0.6);}
.editable-cell{cursor:pointer;position:relative;}
.editable-cell:hover{background-color:rgba(13,110,253,0.05)!important;}
.editable-cell.editing{background-color:rgba(255,193,7,0.15)!important;}
.editable-cell input{width:100%;border:1px solid #0d6efd;border-radius:3px;padding:2px 4px;font-size:inherit;font-family:inherit;}
.editable-cell .edit-indicator{position:absolute;right:4px;top:50%;transform:translateY(-50%);font-size:0.7em;color:#6c757d;opacity:0;}
.editable-cell:hover .edit-indicator{opacity:0.5;}
.row-editing{background-color:rgba(255,193,7,0.1)!important;}
</style>

<script>
(function(){
    function whenJQ(cb){ if(window.jQuery){ cb(window.jQuery); } else { setTimeout(function(){ whenJQ(cb); }, 50); } }

    whenJQ(function($){
        function fetchParticipants(kid, sukan_id, kategori_id){
            return $.ajax({
                url: '<?php echo url('ajax/contingent_participants.php'); ?>',
                data: { kontinjen_id: kid, sukan_id: sukan_id, kategori_id: kategori_id },
                dataType: 'json'
            });
        }

        function renderDetailsRow($tr, res){
            var colspan = $tr.find('td').length || 8;
            var kid = $tr.data('kontinjen') || $tr.find('td').eq(0).text();
            var $detail = $('<tr class="details-row" data-kid="'+kid+'"><td colspan="'+colspan+'"></td></tr>');
            var $container = $('<div class="p-3 bg-light border rounded">');
            var currentSport = '';
            var currentAcara = '';
            function parseId(val){
                var n = parseInt(val,10);
                return isNaN(n) ? '' : n;
            }
            function getFilters(){
                return {
                    sportId: parseId(currentSport || $select.val() || ''),
                    kategoriId: parseId(currentAcara || $selectAcara.val() || '')
                };
            }

            var $topBar = $('<div class="details-top-controls mb-2"></div>');
            var $select = $('<select class="form-select form-select-sm details-select select-sukan"><option value="">Semua Sukan</option></select>');
            var $selectAcara = $('<select class="form-select form-select-sm details-select select-acara"><option value="">Semua Acara</option></select>');
            $selectAcara.prop('disabled', true);
            var $search = $('<input type="search" class="form-control form-control-sm details-search" placeholder="Cari peserta...">');
            var $pageSizeSel = $('<select class="form-select form-select-sm details-pagesize"><option value="5">5</option><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select>');
            var $addBtn = $('<button class="btn btn-sm btn-success add-participant-btn" title="Tambah Peserta"><i class="fa fa-plus"></i> Tambah</button>');
            var $leftControls = $('<div class="left-controls"></div>').append($select).append($selectAcara);
            var $rightControls = $('<div class="right-controls"></div>').append($addBtn).append($search).append($pageSizeSel);
            $topBar.append($leftControls).append($rightControls);

            var $table = $('<table class="table table-sm table-striped mb-0"><colgroup>' +
                '<col style="width:3%"></col>' +
                '<col style="width:18%"></col>' +
                '<col style="width:10%"></col>' +
                '<col style="width:8%"></col>' +
                '<col style="width:8%"></col>' +
                '<col style="width:8%"></col>' +
                '<col style="width:8%"></col>' +
                '<col style="width:15%"></col>' +
                '<col style="width:12%"></col>' +
                '</colgroup><thead><tr>' +
                '<th>#</th>' +
                '<th>Nama</th>' +
                '<th>No Kad Pengenalan</th>' +
                '<th>No Matrik</th>' +
                '<th>Peranan</th>' +
                '<th>Sukan</th>' +
                '<th>Pasukan</th>' +
                '<th>Acara</th>' +
                '<th>Tindakan</th>' +
                '</tr></thead><tbody></tbody></table>');
            var $summary = $('<div class="text-muted small details-summary me-2"></div>');
            var $pager = $('<nav aria-label="Page navigation"><ul class="pagination pagination-sm mb-0"></ul></nav>');
            $rightControls.append($search).append($pageSizeSel);

            var $pagerWrap = $('<div class="d-flex justify-content-between align-items-center mt-2"></div>');
            $pagerWrap.append($summary).append($pager);

            $container.append($topBar).append($table).append($pagerWrap);
            $detail.find('td').append($container);
            $tr.after($detail);
            $tr.find('.contingent-view-btn').text('Hide');

            function buildRows(data){
                var list = [];
                (data.pengurus||[]).forEach(function(r){ r._role='Pengurus'; list.push(r); });
                (data.jurulatih||[]).forEach(function(r){ r._role='Jurulatih'; list.push(r); });
                (data.atlet||[]).forEach(function(r){ r._role='Atlet'; list.push(r); });
                return list;
            }

            var rows = buildRows(res);

            var map = {};
            var mapBySport = {};
            ['atlet','pengurus','jurulatih'].forEach(function(k){
                (res[k]||[]).forEach(function(r){
                    var id = r.sukan_id || '';
                    var name = r.nama_sukan || ('Sukan ' + id);
                    if (id && !map[id]) map[id]=name;

                    var aid = r.kategori_id || r.id_kategori || r.kategori || r.event_id || r.acara_id || '';
                    var aname = r.nama_kategori || r.nama_acara || r.acara || r.event_name || r.kategori || (aid?('Acara '+aid):'');

                    if (id){
                        if (!mapBySport[id]) mapBySport[id] = {};
                        var key = aid || aname || '';
                        if (key && !mapBySport[id][key]) mapBySport[id][key] = aname || key;
                    }
                });
            });
            Object.keys(map).forEach(function(id){ $select.append($('<option>').val(id).text(map[id])); });

            function populateAcaraOptionsForSport(sportId){
                $selectAcara.empty();
                $selectAcara.append($('<option>').val('').text('Semua Acara'));
                var added = 0;
                if(!sportId){
                    $selectAcara.prop('disabled', true);
                    return;
                }
                var m = mapBySport[sportId] || {};
                Object.keys(m).forEach(function(aid){ $selectAcara.append($('<option>').val(aid).text(m[aid])); added++; });
                $selectAcara.prop('disabled', added === 0);
            }

            $.ajax({ url: '<?php echo url('ajax/kategori_list.php'); ?>', dataType: 'json' }).done(function(kres){
                if (kres && kres.success && Array.isArray(kres.data)){
                    mapBySport = {};
                    kres.data.forEach(function(row){
                        var aid = row.id;
                        var sid = row.sukan_id;
                        var aname = row.nama_kategori || row.nama_acara || row.kod_kategori || ('Acara '+aid);
                        if (sid){ mapBySport[sid] = mapBySport[sid] || {}; mapBySport[sid][aid] = aname; }
                    });
                }
                populateAcaraOptionsForSport($select.val() || '');
                renderPage();
            }).fail(function(){
                populateAcaraOptionsForSport($select.val() || '');
                renderPage();
            });

            var $tbody = $table.find('tbody');
            var pageSize = parseInt($pageSizeSel.val(),10) || 10;
            var currentPage = 1;

            function applyFilters(){
                var filterIds = getFilters();
                var sportId = filterIds.sportId;
                var kategoriId = filterIds.kategoriId;
                var q = ($search.val()||'').trim().toLowerCase();

                function matchesSearch(r){
                    var name = (r.nama || r.nama_peserta || r.nama_atlet || r.nama_pengurus || r.nama_jurulatih || '').toString().toLowerCase();
                    if (q && name.indexOf(q) === -1) return false;
                    return true;
                }

                var filteredByIds = rows.filter(function(r){
                    if (sportId && String(r.sukan_id || '') !== String(sportId)) return false;
                    if (kategoriId){
                        var kidVal = r.kategori_id || r.id_kategori || r.acara_id || r.event_id;
                        if (String(kidVal || '') !== String(kategoriId)) return false;
                    }
                    return true;
                });

                var managers = filteredByIds.filter(function(r){
                    var role = (r._role||'').toString();
                    return (role === 'Pengurus' || role === 'Jurulatih') && matchesSearch(r);
                });

                var athletes = filteredByIds.filter(function(r){
                    return (r._role||'') === 'Atlet' && matchesSearch(r);
                });

                var ordered = [];
                managers.filter(function(m){ return (m._role||'') === 'Pengurus'; }).forEach(function(m){ ordered.push(m); });
                managers.filter(function(m){ return (m._role||'') === 'Jurulatih'; }).forEach(function(m){ ordered.push(m); });
                athletes.forEach(function(a){ ordered.push(a); });
                return ordered;
            }

            function genderFromMyKad(ic){
                var digits = (ic||'').toString().replace(/\\D+/g,'');
                if (!digits) return null;
                var last = digits.slice(-1);
                if (!last) return null;
                return (parseInt(last,10) % 2 === 0) ? 'F' : 'M';
            }

            function roleGenderIcon(role, ic){
                var g = genderFromMyKad(ic);
                var genderLabel = g === 'F' ? 'Wanita' : (g === 'M' ? 'Lelaki' : '');
                var iconClass = 'zmdi zmdi-account';
                if (role === 'pengurus') {
                    iconClass = (g === 'F') ? 'zmdi zmdi-female' : 'zmdi zmdi-male';
                } else if (role === 'jurulatih') {
                    iconClass = (g === 'F') ? 'zmdi zmdi-run' : 'zmdi zmdi-walk';
                }
                var title = (role.charAt(0).toUpperCase() + role.slice(1)) + (genderLabel ? ' - ' + genderLabel : '');
                return '<i class="'+iconClass+' text-muted" title="'+title+'"></i>';
            }

            // Inline editing functions
            function makeCellEditable($cell, participantId, participantType, fieldName, originalValue) {
                if ($cell.hasClass('editing')) return; // Already editing
                
                $cell.addClass('editing');
                $cell.closest('tr').addClass('row-editing');
                
                var currentValue = originalValue || '';
                var $input = $('<input type="text">')
                    .val(currentValue)
                    .css({
                        'width': '100%',
                        'border': '1px solid #0d6efd',
                        'border-radius': '3px',
                        'padding': '2px 4px',
                        'font-size': 'inherit',
                        'font-family': 'inherit'
                    });
                
                var $saveBtn = $('<button class="btn btn-sm btn-success ms-1" style="padding: 1px 6px; font-size: 0.75rem;">✓</button>');
                var $cancelBtn = $('<button class="btn btn-sm btn-secondary ms-1" style="padding: 1px 6px; font-size: 0.75rem;">✗</button>');
                var $btnContainer = $('<span class="ms-1"></span>').append($saveBtn).append($cancelBtn);
                
                $cell.empty().append($input).append($btnContainer);
                $input.focus().select();
                
                function finishEdit(save) {
                    if (!save) {
                        revertCell($cell, originalValue);
                        return;
                    }
                    
                    var newValue = $input.val().trim();
                    if (newValue === originalValue) {
                        revertCell($cell, originalValue);
                        return;
                    }
                    
                    saveParticipantField(participantId, participantType, fieldName, newValue, $cell, originalValue);
                }
                
                $input.on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        finishEdit(true);
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        finishEdit(false);
                    }
                });
                
                $saveBtn.on('click', function(e) {
                    e.stopPropagation();
                    finishEdit(true);
                });
                
                $cancelBtn.on('click', function(e) {
                    e.stopPropagation();
                    finishEdit(false);
                });
                
                // Prevent double-click from bubbling
                $cell.off('dblclick');
            }
            
            function saveParticipantField(participantId, participantType, fieldName, newValue, $cell, originalValue) {
                $cell.find('input').prop('disabled', true);
                $cell.find('button').prop('disabled', true);
                
                $.ajax({
                    url: '<?php echo url('ajax/participant_update.php'); ?>',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        participant_id: participantId,
                        participant_type: participantType,
                        field_name: fieldName,
                        field_value: newValue
                    }),
                    dataType: 'json'
                }).done(function(response) {
                    if (response.success) {
                        updateCellDisplay($cell, newValue, fieldName);
                        // Show success feedback
                        var $feedback = $('<span class="text-success ms-1" style="font-size: 0.75rem;">✓</span>');
                        $cell.append($feedback);
                        setTimeout(function() {
                            $feedback.fadeOut(function() { $(this).remove(); });
                        }, 2000);
                    } else {
                        alert('Ralat: ' + (response.message || 'Gagal menyimpan data.'));
                        revertCell($cell, originalValue);
                    }
                }).fail(function(xhr, status, error) {
                    alert('Ralat menyimpan data: ' + error);
                    revertCell($cell, originalValue);
                });
            }
            
            function revertCell($cell, originalValue) {
                $cell.removeClass('editing');
                $cell.closest('tr').removeClass('row-editing');
                
                if (!originalValue || originalValue.trim() === '') {
                    $cell.empty().append($('<span>').addClass('no-data-badge').text('Tiada'));
                    var fieldName = $cell.attr('data-field');
                    if (fieldName) {
                        $cell.addClass('editable-cell').attr('data-field', fieldName).attr('data-original-value', '');
                        $cell.append($('<span>').addClass('edit-indicator').html('✎'));
                    }
                } else {
                    $cell.empty().text(originalValue).attr('title', originalValue);
                    var fieldName = $cell.attr('data-field');
                    if (fieldName) {
                        $cell.addClass('editable-cell').attr('data-field', fieldName).attr('data-original-value', originalValue);
                        $cell.append($('<span>').addClass('edit-indicator').html('✎'));
                    }
                }
                
                // Re-attach double-click handler
                attachEditHandler($cell);
            }
            
            function updateCellDisplay($cell, newValue, fieldName) {
                $cell.removeClass('editing');
                $cell.closest('tr').removeClass('row-editing');
                
                if (!newValue || newValue.trim() === '') {
                    $cell.empty().append($('<span>').addClass('no-data-badge').text('Tiada'));
                    $cell.attr('data-original-value', '');
                } else {
                    $cell.empty().text(newValue).attr('title', newValue);
                    $cell.attr('data-original-value', newValue);
                }
                
                if (fieldName) {
                    $cell.addClass('editable-cell').attr('data-field', fieldName);
                    $cell.append($('<span>').addClass('edit-indicator').html('✎'));
                }
                
                // Re-attach double-click handler
                attachEditHandler($cell);
            }
            
            function attachEditHandler($cell) {
                $cell.off('dblclick').on('dblclick', function(e) {
                    e.stopPropagation();
                    var $this = $(this);
                    if ($this.hasClass('editing')) return;
                    
                    var $row = $this.closest('tr');
                    var participantId = parseInt($row.attr('data-participant-id'), 10);
                    var participantType = $row.attr('data-participant-type');
                    var fieldName = $this.attr('data-field');
                    var originalValue = $this.attr('data-original-value') || '';
                    
                    if (participantId && participantType && fieldName) {
                        makeCellEditable($this, participantId, participantType, fieldName, originalValue);
                    }
                });
            }

            function renderPage(){
                var filtered = applyFilters();
                var total = filtered.length;
                var totalPages = Math.max(1, Math.ceil(total / pageSize));
                if (currentPage > totalPages) currentPage = totalPages;
                var start = (currentPage - 1) * pageSize;
                var end = start + pageSize;
                $tbody.empty();
                if (total === 0) { $tbody.append('<tr><td colspan=\"9\">Tiada peserta untuk penapisan ini.</td></tr>'); }
                var pageRows = filtered.slice(start, end);
                var athleteBefore = filtered.slice(0, start).filter(function(r){ return (r._role||'').toLowerCase() === 'atlet'; }).length;
                var athleteCounter = athleteBefore;
                pageRows.forEach(function(r, idx){
                    // Helper to safely extract value, handling null/undefined
                    function safeValue(val, fallback) {
                        if (val === null || val === undefined) {
                            return (fallback !== undefined) ? fallback : '';
                        }
                        // Handle string representations of null/undefined
                        var strVal = String(val);
                        if (strVal === 'null' || strVal === 'undefined' || strVal.trim() === '') {
                            return (fallback !== undefined) ? fallback : '';
                        }
                        return strVal.trim();
                    }
                    
                    // Extract values with proper fallback chain
                    var name = r.nama || r.nama_peserta || r.nama_atlet || r.nama_pengurus || r.nama_jurulatih || '';
                    name = safeValue(name, '');
                    
                    var nic = r.no_kad_pengenalan || r.ic || r.no_ic || r.mykad || '';
                    nic = safeValue(nic, '');
                    
                    var matrik = r.no_matrik || r.matrik || '';
                    matrik = safeValue(matrik, '');
                    
                    var role = safeValue(r._role, '');
                    var sport = safeValue(r.nama_sukan, r.sukan_id ? ('Sukan ' + r.sukan_id) : '');
                    
                    // For acara, check multiple possible field names
                    var acara = r.nama_kategori || r.nama_acara || r.kategori || r.acara || r.event_name || r.nama_event || '';
                    acara = safeValue(acara, '');
                    if ((role||'').toString().toLowerCase() === 'pengurus' || (role||'').toString().toLowerCase() === 'jurulatih') {
                        acara = '';
                    }
                    
                    var team = safeValue(r.nama_pasukan, '');
                    var $r = $('<tr>');
                    var roleLower = (role||'').toString().toLowerCase();
                    var roleClass = 'role-' + roleLower;
                    $r.addClass(roleClass);
                    
                    // Store participant data in row attributes
                    var participantId = r.id || 0;
                    var participantType = (roleLower === 'atlet') ? 'atlet' : ((roleLower === 'pengurus') ? 'pengurus' : 'jurulatih');
                    var pasukanId = r.pasukan_id || 0;
                    var sukanId = r.sukan_id || 0;
                    $r.attr('data-participant-id', participantId);
                    $r.attr('data-participant-type', participantType);
                    $r.attr('data-participant-role', roleLower);
                    $r.attr('data-pasukan-id', pasukanId);
                    $r.attr('data-sukan-id', sukanId);
                    
                    function cell(val, isEditable, fieldName){
                        var $td = $('<td>').addClass('text-truncate');
                        // Handle null, undefined, empty string, or string "null"/"undefined"
                        var displayValue = (val === null || val === undefined || val === 'null' || val === 'undefined') ? '' : String(val).trim();
                        if (displayValue === '') {
                            $td.append($('<span>').addClass('no-data-badge').text('Tiada'));
                            if (isEditable) {
                                $td.addClass('editable-cell').attr('data-field', fieldName).attr('data-original-value', '');
                                $td.append($('<span>').addClass('edit-indicator').html('✎'));
                            }
                        } else {
                            $td.text(displayValue).attr('title', displayValue);
                            if (isEditable) {
                                $td.addClass('editable-cell').attr('data-field', fieldName).attr('data-original-value', displayValue);
                                $td.append($('<span>').addClass('edit-indicator').html('✎'));
                            }
                        }
                        return $td;
                    }
                    if (roleLower === 'atlet') {
                        athleteCounter++;
                        $r.append($('<td class="text-center">').text(athleteCounter));
                        // For athletes: nama, nic, matrik are editable
                        $r.append(cell(name, true, 'nama'));
                        $r.append(cell(nic, true, 'no_kad_pengenalan'));
                        $r.append(cell(matrik, true, 'no_matrik'));
                    } else {
                        var iconHtml = roleGenderIcon(roleLower, nic);
                        $r.append($('<td class="text-center">').html(iconHtml));
                        // For managers/coaches: only nama and nic are editable
                        $r.append(cell(name, true, 'nama'));
                        $r.append(cell(nic, true, 'no_kad_pengenalan'));
                        $r.append(cell(matrik, false, ''));
                    }
                    $r.append(cell(role, false, ''));
                    $r.append(cell(sport, false, ''));
                    $r.append(cell(team, false, ''));
                    $r.append(cell(acara, false, ''));
                    
                    // Actions column with delete button
                    var $actionsTd = $('<td>').addClass('text-center');
                    var $deleteBtn = $('<button class="btn btn-sm btn-outline-danger delete-participant-btn" title="Padam Peserta" style="padding: 2px 6px;"><i class="fa fa-trash"></i></button>');
                    $deleteBtn.data('participant-id', participantId);
                    $deleteBtn.data('participant-type', participantType);
                    $deleteBtn.data('participant-name', name);
                    $actionsTd.append($deleteBtn);
                    $r.append($actionsTd);
                    
                    $tbody.append($r);
                });

                var $ul = $pager.find('ul'); $ul.empty();
                var createPageItem = function(label, page, disabled, active){
                    var $li = $('<li class="page-item">'); if (disabled) $li.addClass('disabled'); if (active) $li.addClass('active');
                    var $a = $('<a class="page-link" href="#">').text(label).data('page', page);
                    $li.append($a); return $li;
                };
                $ul.append(createPageItem('<<', Math.max(1,currentPage-1), currentPage===1, false));
                var startPage = Math.max(1, currentPage - 2); var endPage = Math.min(totalPages, startPage + 4);
                for (var p = startPage; p <= endPage; p++){ $ul.append(createPageItem(p, p, false, p===currentPage)); }
                $ul.append(createPageItem('>>', Math.min(totalPages,currentPage+1), currentPage===totalPages, false));
                var showingStart = total === 0 ? 0 : start + 1;
                var showingEnd = Math.min(total, end);
                if (total === 0) {
                    $summary.text('Tiada data');
                } else {
                    $summary.text('Memaparkan ' + showingStart + ' - ' + showingEnd + ' daripada ' + total);
                }
                
                // Attach double-click handlers to editable cells after rendering
                $tbody.find('.editable-cell').each(function() {
                    attachEditHandler($(this));
                });
            }

            function reloadFromServer(){
                currentSport = $select.val() || '';
                currentAcara = $selectAcara.val() || '';
                var ids = getFilters();
                fetchParticipants(kid, ids.sportId || '', ids.kategoriId || '').done(function(newRes){
                    if (newRes.status !== 'ok') { alert('Tiada data'); return; }
                    rows = buildRows(newRes);
                    renderPage();
                }).fail(function(){ alert('Ralat memuatkan peserta'); });
            }

            $select.on('change', function(){
                var sv = $(this).val() || '';
                populateAcaraOptionsForSport(sv);
                $selectAcara.val('');
                currentPage = 1;
                reloadFromServer();
            });
            $selectAcara.on('change', function(){ currentPage = 1; reloadFromServer(); });
            $search.on('input', function(){ currentPage = 1; renderPage(); });
            $pageSizeSel.on('change', function(){ pageSize = parseInt($(this).val(),10) || 5; currentPage = 1; renderPage(); });
            $pager.on('click', 'a.page-link', function(e){ e.preventDefault(); var p = $(this).data('page'); if (!p) return; currentPage = parseInt(p,10)||1; renderPage(); });

            // Add participant modal
            var $addModal = $('<div class="modal fade" id="addParticipantModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Peserta</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><form id="addParticipantForm"><div class="mb-3"><label class="form-label">Jenis Peserta <span class="text-danger">*</span></label><select class="form-select" id="addParticipantType" required><option value="">Pilih jenis...</option><option value="atlet">Atlet</option><option value="pengurus">Pengurus</option><option value="jurulatih">Jurulatih</option></select></div><div class="mb-3"><label class="form-label">Pasukan <span class="text-danger">*</span></label><select class="form-select" id="addParticipantPasukan" required><option value="">Pilih pasukan...</option></select></div><div class="mb-3"><label class="form-label">Nama <span class="text-danger">*</span></label><input type="text" class="form-control" id="addParticipantNama" required></div><div class="mb-3"><label class="form-label">No Kad Pengenalan</label><input type="text" class="form-control" id="addParticipantIC" maxlength="12"></div><div class="mb-3" id="addParticipantMatrikGroup" style="display:none;"><label class="form-label">No Matrik</label><input type="text" class="form-control" id="addParticipantMatrik" maxlength="50"></div><div class="mb-3" id="addParticipantKategoriGroup" style="display:none;"><label class="form-label">Acara/Kategori</label><select class="form-select" id="addParticipantKategori"><option value="">Pilih acara...</option></select></div></form></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="button" class="btn btn-primary" id="addParticipantSubmit">Simpan</button></div></div></div></div>');
            $('body').append($addModal);
            
            // Get available pasukan for this kontinjen
            var availablePasukan = {};
            var availableKategori = {};
            
            function loadPasukanForKontinjen() {
                $.ajax({
                    url: '<?php echo url('ajax/pasukan_list.php'); ?>',
                    data: { kontinjen_id: kid },
                    dataType: 'json'
                }).done(function(res) {
                    if (res.success && Array.isArray(res.data)) {
                        var $select = $('#addParticipantPasukan');
                        $select.empty().append($('<option>').val('').text('Pilih pasukan...'));
                        res.data.forEach(function(p) {
                            if (p.status == 1 && !p.deleted_at) {
                                availablePasukan[p.id] = {
                                    id: p.id,
                                    nama_pasukan: p.nama_pasukan,
                                    sukan_id: p.sukan_id,
                                    nama_sukan: p.nama_sukan || ''
                                };
                                var displayText = p.nama_pasukan;
                                if (p.nama_sukan) {
                                    displayText += ' (' + p.nama_sukan + ')';
                                }
                                $select.append($('<option>').val(p.id).text(displayText).data('sukan-id', p.sukan_id));
                            }
                        });
                    }
                }).fail(function() {
                    alert('Ralat memuatkan senarai pasukan.');
                });
            }
            
            // Load pasukan when modal opens
            $addBtn.on('click', function() {
                loadPasukanForKontinjen();
                $('#addParticipantModal').modal('show');
            });
            
            // Handle participant type change
            $('#addParticipantType').on('change', function() {
                var type = $(this).val();
                $('#addParticipantMatrikGroup').toggle(type === 'atlet');
                $('#addParticipantKategoriGroup').toggle(type === 'atlet');
                if (type === 'atlet') {
                    var pasukanId = $('#addParticipantPasukan').val();
                    if (pasukanId) {
                        loadKategoriForPasukan(pasukanId);
                    }
                }
            });
            
            // Handle pasukan change - load kategori for athletes
            $(document).on('change', '#addParticipantPasukan', function() {
                var pasukanId = $(this).val();
                var type = $('#addParticipantType').val();
                if (type === 'atlet' && pasukanId) {
                    loadKategoriForPasukan(pasukanId);
                }
            });
            
            function loadKategoriForPasukan(pasukanId) {
                var pasukan = availablePasukan[pasukanId];
                if (!pasukan || !pasukan.sukan_id) return;
                
                $.ajax({
                    url: '<?php echo url('ajax/get_kategori_by_sukan.php'); ?>',
                    data: { sukan_id: pasukan.sukan_id },
                    dataType: 'json'
                }).done(function(res) {
                    if (res.success && Array.isArray(res.data)) {
                        var $select = $('#addParticipantKategori');
                        $select.empty().append($('<option>').val('').text('Pilih acara...'));
                        res.data.forEach(function(k) {
                            $select.append($('<option>').val(k.id).text(k.nama_kategori));
                        });
                    }
                });
            }
            
            // Handle add participant submit
            $('#addParticipantSubmit').on('click', function() {
                var type = $('#addParticipantType').val();
                var pasukanId = parseInt($('#addParticipantPasukan').val(), 10);
                var nama = $('#addParticipantNama').val().trim();
                var ic = $('#addParticipantIC').val().trim();
                var matrik = $('#addParticipantMatrik').val().trim();
                var kategoriId = $('#addParticipantKategori').val() ? parseInt($('#addParticipantKategori').val(), 10) : null;
                
                if (!type || !pasukanId || !nama) {
                    alert('Sila isi semua medan wajib.');
                    return;
                }
                
                var data = {
                    participant_type: type,
                    pasukan_id: pasukanId,
                    nama: nama,
                    no_kad_pengenalan: ic || null,
                    no_matrik: (type === 'atlet' ? (matrik || null) : null),
                    kategori_id: (type === 'atlet' ? kategoriId : null)
                };
                
                $.ajax({
                    url: '<?php echo url('ajax/participant_add.php'); ?>',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    dataType: 'json'
                }).done(function(response) {
                    if (response.success) {
                        $('#addParticipantModal').modal('hide');
                        $('#addParticipantForm')[0].reset();
                        $('#addParticipantMatrikGroup').hide();
                        $('#addParticipantKategoriGroup').hide();
                        reloadFromServer();
                    } else {
                        alert('Ralat: ' + (response.message || 'Gagal menambah peserta.'));
                    }
                }).fail(function(xhr, status, error) {
                    var errorMsg = 'Ralat menambah peserta';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += ': ' + xhr.responseJSON.message;
                    } else {
                        errorMsg += ': ' + error;
                    }
                    alert(errorMsg);
                });
            });
            
            // Handle delete participant
            $(document).on('click', '.delete-participant-btn', function(e) {
                e.stopPropagation();
                var $btn = $(this);
                var participantId = $btn.data('participant-id');
                var participantType = $btn.data('participant-type');
                var participantName = $btn.data('participant-name') || 'Peserta ini';
                
                if (!confirm('Adakah anda pasti ingin memadam ' + participantName + '?')) {
                    return;
                }
                
                $.ajax({
                    url: '<?php echo url('ajax/participant_delete.php'); ?>',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        participant_id: participantId,
                        participant_type: participantType
                    }),
                    dataType: 'json'
                }).done(function(response) {
                    if (response.success) {
                        reloadFromServer();
                    } else {
                        alert('Ralat: ' + (response.message || 'Gagal memadam peserta.'));
                    }
                }).fail(function(xhr, status, error) {
                    alert('Ralat memadam peserta: ' + error);
                });
            });

            renderPage();
        }

        $(document).on('click', '.contingent-view-btn', function(e){
            e.preventDefault();
            var $btn = $(this);
            var $tr = $btn.closest('tr');
            var kid = $btn.data('kid') || $tr.data('kontinjen');
            if (!kid) return;
            var $next = $tr.next();
            if ($next.hasClass('details-row') && String($next.data('kid')) === String(kid)) { $next.remove(); $btn.text('Show'); $tr.removeClass('row-highlight'); return; }
            $('.details-row').remove();
            $('.contingent-view-btn').text('Show');
            $('tr.row-highlight').removeClass('row-highlight');
            fetchParticipants(kid).done(function(res){
                if (res.status !== 'ok') { alert('Tiada data'); return; }
                renderDetailsRow($tr, res);
                $tr.find('.contingent-view-btn').text('Hide');
                $tr.addClass('row-highlight');
            }).fail(function(){ alert('Ralat memuatkan peserta'); });
        });
    });
})();
</script>

<?php
    $content = ob_get_clean();
    require_once __DIR__ . '/../includes/layout.php';
