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

            var $topBar = $('<div class="details-top-controls mb-2"></div>');
            var $select = $('<select class="form-select form-select-sm details-select select-sukan"><option value="">Semua Sukan</option></select>');
            var $selectAcara = $('<select class="form-select form-select-sm details-select select-acara"><option value="">Semua Acara</option></select>');
            $selectAcara.prop('disabled', true);
            var $search = $('<input type="search" class="form-control form-control-sm details-search" placeholder="Cari peserta...">');
            var $pageSizeSel = $('<select class="form-select form-select-sm details-pagesize"><option value="5">5</option><option value="10" selected>10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select>');
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
                var q = ($search.val()||'').trim().toLowerCase();

                function matchesSearch(r){
                    var name = (r.nama || r.nama_peserta || r.nama_atlet || r.nama_pengurus || r.nama_jurulatih || '').toString().toLowerCase();
                    if (q && name.indexOf(q) === -1) return false;
                    return true;
                }

                var managers = rows.filter(function(r){
                    var role = (r._role||'').toString();
                    return (role === 'Pengurus' || role === 'Jurulatih') && matchesSearch(r);
                });

                var athletes = rows.filter(function(r){
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

            function renderPage(){
                var filtered = applyFilters();
                var total = filtered.length;
                var totalPages = Math.max(1, Math.ceil(total / pageSize));
                if (currentPage > totalPages) currentPage = totalPages;
                var start = (currentPage - 1) * pageSize;
                var end = start + pageSize;
                $tbody.empty();
                if (total === 0) { $tbody.append('<tr><td colspan=\"8\">Tiada peserta untuk penapisan ini.</td></tr>'); }
                var pageRows = filtered.slice(start, end);
                var athleteBefore = filtered.slice(0, start).filter(function(r){ return (r._role||'').toLowerCase() === 'atlet'; }).length;
                var athleteCounter = athleteBefore;
                pageRows.forEach(function(r, idx){
                    var name = r.nama || r.nama_peserta || r.nama_atlet || r.nama_pengurus || r.nama_jurulatih || '';
                    var nic = r.no_kad_pengenalan || r.ic || r.no_ic || r.mykad || '';
                    var matrik = r.no_matrik || r.matrik || r.no_matrik || '';
                    var role = r._role || '';
                    var sport = r.nama_sukan || ('Sukan ' + (r.sukan_id || ''));
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
                    var roleLower = (role||'').toString().toLowerCase();
                    if (roleLower === 'atlet') {
                        athleteCounter++;
                        $r.append($('<td class="text-center">').text(athleteCounter));
                    } else {
                        var iconHtml = roleGenderIcon(roleLower, nic);
                        $r.append($('<td class="text-center">').html(iconHtml));
                    }
                    $r.append(cell(name));
                    $r.append(cell(nic));
                    $r.append(cell(matrik));
                    $r.append(cell(role));
                    $r.append(cell(sport));
                    $r.append(cell(team));
                    $r.append(cell(acara));
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
            }

            function reloadFromServer(){
                currentSport = $select.val() || '';
                currentAcara = $selectAcara.val() || '';
                fetchParticipants(kid, currentSport, currentAcara).done(function(newRes){
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
