<?php
/**
 * Contingent User Management (view-only)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../api/models/ContingentModel.php';

$page_title = 'Kontinjen User';

// Start session & auth
Session::start();
$auth = getAuth();
$auth->requireAuth();
$currentUserRole = Session::get('user_role') ?? '';
$restrictToOwnContingent = ($currentUserRole === 'CONTINGENT');

// Restrict this page to CONTINGENT role only
if ($currentUserRole !== 'CONTINGENT') {
    header('Location: ' . url('pages/access-denied.php'));
    exit;
}
$currentKontinjenId = Session::get('kontinjen_id') ?? null;

if ($restrictToOwnContingent && empty($currentKontinjenId)) {
    header('Location: ' . url('pages/access-denied.php'));
    exit;
}

// Fetch contingents and participant counts (reuse existing queries)
$contingents = [];
try {
    $pdo = getDB();
    $where = "k.deleted_at IS NULL AND k.status = 1";
    if ($restrictToOwnContingent && $currentKontinjenId) {
        $where .= " AND k.id = :kontinjen_id";
    }

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
    WHERE " . $where . "
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

    if ($restrictToOwnContingent && $currentKontinjenId) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':kontinjen_id' => (int)$currentKontinjenId]);
    } else {
        $stmt = $pdo->query($sql);
    }
    $contingents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('[contingent-user.php] DB error: ' . $e->getMessage());
}
// Determine current contingent name (if page is restricted to own contingent)
$contingentName = '';
if (!empty($contingents) && isset($contingents[0]['nama_universiti'])) {
    $contingentName = $contingents[0]['nama_universiti'];
}

ob_start();
?>
<div class="w-100 px-3">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light border-0 shadow-sm overflow-hidden">
                <div class="card-body py-4 d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                    <div>
                        <h2 class="mb-1">Kontinjen User</h2>
                        <p class="text-muted mb-0">Halaman hanya untuk paparan data — tiada borang pendaftaran di sini.</p>
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
                        <strong>Senarai Kontinjen<?php if(!empty(
                            $contingentName
                        )): ?>, <?php echo htmlspecialchars($contingentName, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?></strong>
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
                                            <span class="badge badge-pill badge-primary"><?php echo $count; ?></span>
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

/* Highlight rows for Pengurus and Jurulatih */
.details-row tr.role-pengurus td, .details-row tr.role-jurulatih td {
    background-color: rgba(255, 245, 204, 0.9); /* light yellow */
}
.details-row tr.role-pengurus td:first-child, .details-row tr.role-jurulatih td:first-child {
    border-left: 4px solid #f0ad4e; /* accent */
}
/* Soft red badge for empty values */
.no-data-badge{display:inline-block;padding:0.18rem 0.45rem;border-radius:0.35rem;background:#fdecea;color:#842029;font-size:0.8rem}

/* Force single-line rows inside details table and enable truncation */
.details-row .table{table-layout:fixed;width:100%}
.details-row .table th,.details-row .table td{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.details-row .p-3{overflow:auto}
</style>

<script>
// Inline details expansion (view-only)
(function(){
    function whenJQ(cb){ if(window.jQuery){ cb(window.jQuery); } else { setTimeout(function(){ whenJQ(cb); }, 50); } }

    whenJQ(function($){
        function fetchParticipants(kid, sukan_id){
            return $.ajax({ url: '<?php echo url('ajax/contingent_participants.php'); ?>', data: { kontinjen_id: kid, sukan_id: sukan_id }, dataType: 'json' });
        }

        function renderDetailsRow($tr, res){
            var colspan = $tr.find('td').length || 8;
            var kid = $tr.data('kontinjen') || $tr.find('td').eq(0).text();
            var $detail = $('<tr class="details-row" data-kid="'+kid+'"><td colspan="'+colspan+'"></td></tr>');
            var $container = $('<div class="p-3 bg-light border rounded">');

            // Top bar: left-aligned selects (sport + acara) and right-aligned search + pager controls
            var $topBar = $('<div class="details-top-controls mb-2"></div>');
            var $select = $('<select class="form-select form-select-sm details-select select-sukan"><option value="">Semua Sukan</option></select>');
            var $selectAcara = $('<select class="form-select form-select-sm details-select select-acara"><option value="">Semua Acara</option></select>');
            // disable acara until categories are loaded and populated for chosen sport
            $selectAcara.prop('disabled', true);
            var $search = $('<input type="search" class="form-control form-control-sm details-search" placeholder="Cari peserta...">');
            var $pageSizeSel = $('<select class="form-select form-select-sm details-pagesize"><option value="5">5</option><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select>');
            // left and right control groups
            var $leftControls = $('<div class="left-controls"></div>').append($select).append($selectAcara);
            var $rightControls = $('<div class="right-controls"></div>').append($search).append($pageSizeSel);
            $topBar.append($leftControls).append($rightControls);

            var $table = $('<table class="table table-sm table-striped mb-0"><colgroup>' +
                '<col style="width:3%"></col>' +
                '<col style="width:22%"></col>' +
                '<col style="width:12%"></col>' +
                '<col style="width:10%"></col>' +
                '<col style="width:10%"></col>' +
                '<col style="width:10%"></col>' +
                '<col style="width:10%"></col>' +
                '<col style="width:20%"></col>' +
                '</colgroup><thead><tr>' +
                '<th>#</th>' +
                '<th>Nama</th>' +
                '<th>No Kad Pengenalan</th>' +
                '<th>No Matrik</th>' +
                '<th>Peranan</th>' +
                '<th>Sukan</th>' +
                '<th>Pasukan</th>' +
                '<th>Acara</th>' +
                '</tr></thead><tbody></tbody></table>');
            var $summary = $('<div class="text-muted small details-summary me-2"></div>');
            var $pager = $('<nav aria-label="Page navigation"><ul class="pagination pagination-sm mb-0"></ul></nav>');
            // keep search + pagesize in top-right; summary + pager sit below the table
            $rightControls.append($search).append($pageSizeSel);

            var $pagerWrap = $('<div class="d-flex justify-content-between align-items-center mt-2"></div>');
            $pagerWrap.append($summary).append($pager);

            $container.append($topBar).append($table).append($pagerWrap);
            $detail.find('td').append($container);
            $tr.after($detail);
            // mark originating button as Hide
            $tr.find('.contingent-view-btn').text('Hide');

            // Build row list: show Pengurus first, then Jurulatih, then Atlet
            var rows = [];
            (res.pengurus||[]).forEach(function(r){ r._role='Pengurus'; rows.push(r); });
            (res.jurulatih||[]).forEach(function(r){ r._role='Jurulatih'; rows.push(r); });
            (res.atlet||[]).forEach(function(r){ r._role='Atlet'; rows.push(r); });

            // Populate sport select and acara select
            var map = {};
            var mapAcara = {};
            // sport -> acara mapping for dependent dropdown
            var mapBySport = {};
            ['atlet','pengurus','jurulatih'].forEach(function(k){
                (res[k]||[]).forEach(function(r){
                    var id = r.sukan_id || '';
                    var name = r.nama_sukan || ('Sukan ' + id);
                    if (id && !map[id]) map[id]=name;

                    var aid = r.kategori_id || r.id_kategori || r.kategori || r.event_id || r.acara_id || '';
                    var aname = r.nama_kategori || r.nama_acara || r.acara || r.event_name || r.kategori || (aid?('Acara '+aid):'');
                    if (aid){ if(!mapAcara[aid]) mapAcara[aid]=aname; }
                    else { if(aname && !mapAcara[aname]) mapAcara[aname]=aname; }

                    // populate mapBySport
                    if (id){
                        if (!mapBySport[id]) mapBySport[id] = {};
                        var key = aid || aname || '';
                        if (key && !mapBySport[id][key]) mapBySport[id][key] = aname || key;
                    }
                });
            });
            Object.keys(map).forEach(function(id){ $select.append($('<option>').val(id).text(map[id])); });

            // function to populate acara options filtered by sport id (or all when empty)
            function populateAcaraOptionsForSport(sportId){
                $selectAcara.empty();
                $selectAcara.append($('<option>').val('').text('Semua Acara'));
                var added = 0;
                if(!sportId){
                    // no sport selected: keep disabled and do not list all acara
                    $selectAcara.prop('disabled', true);
                    return;
                }
                var m = mapBySport[sportId] || {};
                Object.keys(m).forEach(function(aid){ $selectAcara.append($('<option>').val(aid).text(m[aid])); added++; });
                if(added === 0){
                    // no acara for this sport
                    $selectAcara.prop('disabled', true);
                } else {
                    $selectAcara.prop('disabled', false);
                }
            }

            // Load canonical acara list from server (table_kategori) and build mapBySport
            $.ajax({ url: '<?php echo url('ajax/kategori_list.php'); ?>', dataType: 'json' }).done(function(kres){
                if (kres && kres.success && Array.isArray(kres.data)){
                    mapAcara = {};
                    mapBySport = {};
                    kres.data.forEach(function(row){
                        var aid = row.id;
                        var sid = row.sukan_id;
                        var aname = row.nama_kategori || row.nama_acara || row.kod_kategori || ('Acara '+aid);
                        if (aid) mapAcara[aid] = aname;
                        if (sid){ mapBySport[sid] = mapBySport[sid] || {}; mapBySport[sid][aid] = aname; }
                    });
                } else {
                    // if server returns no categories, try to build mapBySport from participant-derived mapBySport
                    // mapBySport may already be populated from participants above
                }
                // initialize acara options filtered by currently selected sport (if any)
                populateAcaraOptionsForSport($select.val() || '');
                // now render page
                renderPage();
            }).fail(function(){
                // fallback: use participant-derived mapBySport
                populateAcaraOptionsForSport($select.val() || '');
                renderPage();
            });

            var $tbody = $table.find('tbody');
            var pageSize = parseInt($pageSizeSel.val(),10) || 10;
            var currentPage = 1;

            function applyFilters(){
                var sportFilter = $select.val() || '';
                var acaraFilter = $selectAcara.val() || '';
                var q = ($search.val()||'').trim().toLowerCase();

                // Helper to match name/search
                function matchesSearch(r){
                    var name = (r.nama || r.nama_peserta || r.nama_atlet || r.nama_pengurus || r.nama_jurulatih || '').toString().toLowerCase();
                    if (q && name.indexOf(q) === -1) return false;
                    return true;
                }

                // Managers and coaches (always appear first when acara is selected)
                var managers = rows.filter(function(r){
                    var role = (r._role||'').toString();
                    if (role !== 'Pengurus' && role !== 'Jurulatih') return false;
                    // Apply sport filter if present
                    if (sportFilter && String(r.sukan_id) !== String(sportFilter)) return false;
                    // Always include managers/jurulatih even when acaraFilter is set (but respect search)
                    return matchesSearch(r);
                });

                // Athletes: apply sport + acara + search filters
                var athletes = rows.filter(function(r){
                    var role = (r._role||'').toString();
                    if (role !== 'Atlet') return false;
                    if (sportFilter && String(r.sukan_id) !== String(sportFilter)) return false;
                    if (acaraFilter){
                        var rid = r.kategori_id || r.id_kategori || r.kategori || r.event_id || r.acara_id || '';
                        var rname = r.nama_kategori || r.nama_acara || r.acara || r.event_name || r.kategori || '';
                        if (String(rid) !== String(acaraFilter) && String(rname) !== String(acaraFilter)) return false;
                    }
                    return matchesSearch(r);
                });

                // If no acara selected, keep original intended order: Pengurus, Jurulatih, Atlet
                if (!acaraFilter){
                    var ordered = [];
                    // push Pengurus then Jurulatih from managers (managers contains both)
                    managers.filter(function(m){ return (m._role||'') === 'Pengurus'; }).forEach(function(m){ ordered.push(m); });
                    managers.filter(function(m){ return (m._role||'') === 'Jurulatih'; }).forEach(function(m){ ordered.push(m); });
                    // then athletes (already filtered)
                    athletes.forEach(function(a){ ordered.push(a); });
                    return ordered;
                }

                // When acara is selected, ensure Pengurus & Jurulatih (matching search/sport) display first, then athletes for that acara
                var orderedWhenAcara = [];
                managers.filter(function(m){ return (m._role||'') === 'Pengurus'; }).forEach(function(m){ orderedWhenAcara.push(m); });
                managers.filter(function(m){ return (m._role||'') === 'Jurulatih'; }).forEach(function(m){ orderedWhenAcara.push(m); });
                athletes.forEach(function(a){ orderedWhenAcara.push(a); });
                return orderedWhenAcara;
            }

            function renderPage(){
                var filtered = applyFilters();
                var total = filtered.length;
                var totalPages = Math.max(1, Math.ceil(total / pageSize));
                if (currentPage > totalPages) currentPage = totalPages;
                var start = (currentPage - 1) * pageSize;
                var end = start + pageSize;
                $tbody.empty();
                if (total === 0) { $tbody.append('<tr><td colspan="8">Tiada peserta untuk penapisan ini.</td></tr>'); }
                var pageRows = filtered.slice(start, end);
                pageRows.forEach(function(r, idx){
                    var name = r.nama || r.nama_peserta || r.nama_atlet || r.nama_pengurus || r.nama_jurulatih || '';
                    var nic = r.no_kad_pengenalan || r.ic || r.no_ic || r.mykad || '';
                    var matrik = r.no_matrik || r.matrik || r.no_matrik || '';
                    var role = r._role || '';
                    var sport = r.nama_sukan || ('Sukan ' + (r.sukan_id || ''));
                    // For Pengurus and Jurulatih, always show 'Tiada' for Acara (use empty value so badge is rendered)
                    var acara = r.nama_acara || r.nama_kategori || r.kategori || r.acara || r.event_name || r.nama_event || '';
                    if ((role||'').toString().toLowerCase() === 'pengurus' || (role||'').toString().toLowerCase() === 'jurulatih') {
                        acara = '';
                    }
                    var team = r.nama_pasukan || '';
                    var $r = $('<tr>');
                    var roleClass = 'role-' + (role||'').toString().toLowerCase();
                    $r.addClass(roleClass);
                    function cell(val){
                        if (val === null || val === undefined || String(val).trim() === '') {
                            return $('<td>').append($('<span>').addClass('no-data-badge').text('Tiada'));
                        }
                        var display = String(val);
                        return $('<td>').addClass('text-truncate').text(display).attr('title', display);
                    }
                    $r.append(cell(start + idx + 1));
                    $r.append(cell(name));
                    $r.append(cell(nic));
                    $r.append(cell(matrik));
                    $r.append(cell(role));
                    $r.append(cell(sport));
                    $r.append(cell(team));
                    $r.append(cell(acara));
                    $tbody.append($r);
                });

                // Render pager (simple prev/next + page numbers upto 5 pages)
                var $ul = $pager.find('ul'); $ul.empty();
                var createPageItem = function(label, page, disabled, active){
                    var $li = $('<li class="page-item">'); if (disabled) $li.addClass('disabled'); if (active) $li.addClass('active');
                    var $a = $('<a class="page-link" href="#">').text(label).data('page', page);
                    $li.append($a); return $li;
                };
                $ul.append(createPageItem('«', Math.max(1,currentPage-1), currentPage===1, false));
                var startPage = Math.max(1, currentPage - 2); var endPage = Math.min(totalPages, startPage + 4);
                for (var p = startPage; p <= endPage; p++){ $ul.append(createPageItem(p, p, false, p===currentPage)); }
                $ul.append(createPageItem('»', Math.min(totalPages,currentPage+1), currentPage===totalPages, false));
                // Update summary
                var showingStart = total === 0 ? 0 : start + 1;
                var showingEnd = Math.min(total, end);
                if (total === 0) {
                    $summary.text('Tiada data');
                } else {
                    $summary.text('Memaparkan ' + showingStart + '–' + showingEnd + ' daripada ' + total);
                }
            }

            // Handlers
            $select.on('change', function(){
                // repopulate acara options for the selected sport and reset acara selection
                var sv = $(this).val() || '';
                populateAcaraOptionsForSport(sv);
                $selectAcara.val('');
                currentPage = 1; renderPage();
            });
            $selectAcara.on('change', function(){ currentPage = 1; renderPage(); });
            $search.on('input', function(){ currentPage = 1; renderPage(); });
            $pageSizeSel.on('change', function(){ pageSize = parseInt($(this).val(),10) || 5; currentPage = 1; renderPage(); });
            $pager.on('click', 'a.page-link', function(e){ e.preventDefault(); var p = $(this).data('page'); if (!p) return; currentPage = parseInt(p,10)||1; renderPage(); });

            // initial render
            renderPage();
        }

        $(document).on('click', '.contingent-view-btn', function(e){
            e.preventDefault();
            var $btn = $(this);
            var $tr = $btn.closest('tr');
            var kid = $btn.data('kid') || $tr.data('kontinjen');
            if (!kid) return;
            var $next = $tr.next();
            // if its own details row is open, close it and reset label
            if ($next.hasClass('details-row') && String($next.data('kid')) === String(kid)) { $next.remove(); $btn.text('Show'); return; }
            // close any other open details and reset other buttons
            $('.details-row').remove();
            $('.contingent-view-btn').text('Show');
            fetchParticipants(kid).done(function(res){
                if (res.status !== 'ok') { alert('Tiada data'); return; }
                renderDetailsRow($tr, res);
                // set this button to Hide after rendering
                $tr.find('.contingent-view-btn').text('Hide');
            }).fail(function(){ alert('Ralat memuatkan peserta'); });
        });
    });
})();
</script>

<?php
    $content = ob_get_clean();
    require_once __DIR__ . '/../includes/layout.php';
