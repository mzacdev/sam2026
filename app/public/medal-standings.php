<?php
/**
 * Public Medal Standings - Reuses internal `medal-tally` logic but exposed publicly.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';

$page_title = 'Medal Standings';

$tally = [];
$totals = ['emas' => 0, 'perak' => 0, 'gangsa' => 0];

try {
    $db = getDB();

    $sql = "
        SELECT
            base.kod_universiti,
            base.nama_pendek,
            base.nama_universiti,
            COALESCE(mc.emas, 0)   AS emas,
            COALESCE(mc.perak, 0)  AS perak,
            COALESCE(mc.gangsa, 0) AS gangsa
        FROM (
            SELECT DISTINCT
                k.kod_universiti,
                COALESCE(r.nama_pendek, k.kod_universiti) AS nama_pendek,
                COALESCE(r.nama_universiti, r.nama_pendek, k.kod_universiti) AS nama_universiti
            FROM table_kontinjen k
            JOIN table_ref_universiti r ON r.kod_universiti = k.kod_universiti
            WHERE k.deleted_at IS NULL AND k.status = 1 AND r.status = 1
        ) base
        LEFT JOIN (
            SELECT
                k.kod_universiti,
                SUM(CASE WHEN jt.position = 1 THEN 1 ELSE 0 END) AS emas,
                SUM(CASE WHEN jt.position = 2 THEN 1 ELSE 0 END) AS perak,
                SUM(CASE WHEN jt.position = 3 THEN 1 ELSE 0 END) AS gangsa
            FROM table_results tr
            JOIN JSON_TABLE(tr.standings, '$[*]' COLUMNS(
                position INT PATH '$.position',
                participant_id VARCHAR(255) PATH '$.participant_id'
            )) jt ON jt.position IN (1,2,3)
            JOIN table_pasukan p ON p.id = jt.participant_id AND p.deleted_at IS NULL
            JOIN table_kontinjen k ON k.id = p.kontinjen_id AND k.deleted_at IS NULL AND k.status = 1
            JOIN table_ref_universiti r ON r.kod_universiti = k.kod_universiti AND r.status = 1
            WHERE tr.deleted_at IS NULL AND tr.status = 'completed'
            GROUP BY k.kod_universiti
        ) mc ON mc.kod_universiti = base.kod_universiti
        ORDER BY emas DESC, perak DESC, gangsa DESC, base.nama_pendek ASC
    ";

    $stmt = $db->query($sql);
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $ranked = [];
    $position = 0;
    foreach ($rows as $row) {
        $position++;
        $row['rank'] = $position;
        $row['jumlah'] = (int)$row['emas'] + (int)$row['perak'] + (int)$row['gangsa'];
        $ranked[] = $row;
        $totals['emas'] += (int)$row['emas'];
        $totals['perak'] += (int)$row['perak'];
        $totals['gangsa'] += (int)$row['gangsa'];
    }
    $tally = $ranked;
} catch (Exception $e) {
    error_log('[public/medal-standings] error: ' . $e->getMessage());
    $tally = [];
}

ob_start();
// Normalize backend keys to the template's expected keys
$standings = [];
foreach ($tally as $r) {
    $standings[] = [
        // Prefer full university name where available
        'kontinjen_nama' => $r['nama_universiti'] ?? $r['nama_pendek'] ?? ($r['kontinjen_nama'] ?? ''),
        'kod_universiti' => $r['kod_universiti'] ?? '',
        'gold' => isset($r['emas']) ? (int)$r['emas'] : (isset($r['gold']) ? (int)$r['gold'] : 0),
        'silver' => isset($r['perak']) ? (int)$r['perak'] : (isset($r['silver']) ? (int)$r['silver'] : 0),
        'bronze' => isset($r['gangsa']) ? (int)$r['gangsa'] : (isset($r['bronze']) ? (int)$r['bronze'] : 0),
        'total' => isset($r['jumlah']) ? (int)$r['jumlah'] : (isset($r['total']) ? (int)$r['total'] : ((int)($r['emas'] ?? $r['gold'] ?? 0) + (int)($r['perak'] ?? $r['silver'] ?? 0) + (int)($r['gangsa'] ?? $r['bronze'] ?? 0))),
        'rank' => isset($r['rank']) ? (int)$r['rank'] : 0,
    ];
}
?>
<!-- Page markup continues (styles and table header are above) -->
<div class="container mt-3 mb-4">
        <div class="row mb-2 align-items-center">
            <div class="col-md-8 col-12">
                <h2 class="mb-0">Contingent Rankings</h2>
                <div class="text-muted small">Latest medals won by each contingent</div>
            </div>
            <div class="col-md-4 col-12 mt-2 mt-md-0 text-md-right">
                <div class="medal-controls d-flex justify-content-end gap-2">
                    <div class="input-with-icon" style="max-width:340px;">
                        <svg class="icon-search" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path fill="#6B7280" d="M10 4a6 6 0 1 1 0 12 6 6 0 0 1 0-12zm8.707 15.293-4.387-4.387A8 8 0 1 0 18 19.586z"/></svg>
                        <input id="medalSearch" class="form-control form-control-sm medal-search" placeholder="Cari kontinjen..." />
                    </div>
                    <select id="medalSort" class="form-control form-control-sm medal-select" style="max-width:240px;">
                        <option value="rank_desc">Sort by Rank</option>
                        <option value="gold_desc">Gold (Most)</option>
                        <option value="total_desc">Total (Most)</option>
                        <option value="name_asc">Name (A→Z)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-medal" id="medalTable">
                        <thead>
                            <tr>
                                <th class="col-rank text-left">Rank</th>
                                <th class="col-kontinjen text-left">Contingent</th>
                                <th class="medal-col text-center" aria-label="gold">
                                    <svg class="icon-medal" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="34" height="34" aria-hidden="true">
                                        <defs>
                                            <linearGradient id="g-gold" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#FFE78A"/><stop offset="1" stop-color="#FFC107"/></linearGradient>
                                        </defs>
                                        <circle cx="24" cy="18" r="10" fill="url(#g-gold)" stroke="#D4A017" stroke-width="1"/>
                                        <path d="M14 6 L18 6 L24 18 L30 6 L34 6 L26 26 L22 26 Z" fill="#D4A017" opacity="0.9"/>
                                    </svg>
                                </th>
                                <th class="medal-col text-center" aria-label="silver">
                                    <svg class="icon-medal" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="34" height="34" aria-hidden="true">
                                        <defs>
                                            <linearGradient id="g-silver" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#F0F4F8"/><stop offset="1" stop-color="#C7CDD9"/></linearGradient>
                                        </defs>
                                        <circle cx="24" cy="18" r="10" fill="url(#g-silver)" stroke="#9AA5B2" stroke-width="1"/>
                                        <path d="M14 6 L18 6 L24 18 L30 6 L34 6 L26 26 L22 26 Z" fill="#9AA5B2" opacity="0.9"/>
                                    </svg>
                                </th>
                                <th class="medal-col text-center" aria-label="bronze">
                                    <svg class="icon-medal" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="34" height="34" aria-hidden="true">
                                        <defs>
                                            <linearGradient id="g-bronze" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#F3D6C0"/><stop offset="1" stop-color="#C0845A"/></linearGradient>
                                        </defs>
                                        <circle cx="24" cy="18" r="10" fill="url(#g-bronze)" stroke="#9A5C3A" stroke-width="1"/>
                                        <path d="M14 6 L18 6 L24 18 L30 6 L34 6 L26 26 L22 26 Z" fill="#9A5C3A" opacity="0.9"/>
                                    </svg>
                                </th>
                                <th class="medal-col text-center" aria-label="total"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($standings as $row): ?>
                            <tr data-kod="<?php echo htmlspecialchars($row['kod_universiti']); ?>" data-name="<?php echo htmlspecialchars(strtolower($row['kontinjen_nama'])); ?>" data-gold="<?php echo (int)$row['gold']; ?>" data-silver="<?php echo (int)$row['silver']; ?>" data-bronze="<?php echo (int)$row['bronze']; ?>" data-total="<?php echo (int)$row['total']; ?>" data-rank="<?php echo (int)$row['rank']; ?>">
                                <td class="rank-cell col-rank">
                                    <div class="rank-text"><?php echo $row['rank']; ?></div>
                                </td>
                                <td class="col-kontinjen">
                                    <div style="display:flex;align-items:center;">
                                        <img src="../assets/img/logos/UA/<?php echo htmlspecialchars($row['kod_universiti']); ?>.svg" alt="" class="kontinjen-logo" onerror="this.style.display='none'" />
                                        <div class="kontinjen-name"><?php echo htmlspecialchars($row['kontinjen_nama']); ?></div>
                                    </div>
                                </td>
                                <td class="medal-col text-center"><span class="medal-text medal-detail-btn-public" data-medal="emas" style="cursor:pointer;">
                                    <?php echo $row['gold']; ?></span></td>
                                <td class="medal-col text-center"><span class="medal-text medal-detail-btn-public" data-medal="perak" style="cursor:pointer;">
                                    <?php echo $row['silver']; ?></span></td>
                                <td class="medal-col text-center"><span class="medal-text medal-detail-btn-public" data-medal="gangsa" style="cursor:pointer;">
                                    <?php echo $row['bronze']; ?></span></td>
                                <td class="medal-col text-center"><strong class="total-count medal-detail-total-public" style="cursor:pointer;" data-medal="total"><?php echo $row['total']; ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Styles (kept near content for simplicity) -->
    <style>
    :root{--accent-50:#e8f0ff;--accent-100:#cfe0ff;--accent-300:#8fb8ff;--accent-600:#2563eb;--accent-700:#1e40af;--muted:#374151;--gold:#f59e0b;--silver:#94a3b8;--bronze:#c0845a;--card-bg:#ffffff;--card-shadow:0 6px 18px rgba(2,6,23,0.06)}
    .table-medal{width:100%;border-collapse:separate;border-spacing:0 8px}
    .table-medal thead th{background:transparent;border-bottom:0;padding:8px 12px 12px;color:var(--muted);font-weight:600;font-size:1.05rem;vertical-align:top}
    .table-medal tbody tr{background:var(--card-bg);border-radius:8px;box-shadow:var(--card-shadow);transition:transform .12s ease,box-shadow .12s ease}
    .table-medal tbody tr:hover{transform:translateY(-4px);box-shadow:0 12px 30px rgba(2,6,23,0.08)}
    .table-medal td{vertical-align:middle;padding:10px 12px;border:0}
    .rank-text{font-weight:400;font-size:1.05rem;color:var(--muted);display:inline-block;text-align:left}
    .medal-text{font-weight:400;color:var(--muted);display:inline-block;min-width:36px;font-size:1.05rem}
    .total-count{font-size:1.05rem;color:#111;font-weight:400}
    .kontinjen-name{font-weight:600;font-size:1.05rem}
    /* Column width and alignment enforcement */
    .table-medal thead th.col-rank, .table-medal tbody td.col-rank { width:5%; text-align:left }
    .table-medal thead th.col-kontinjen, .table-medal tbody td.col-kontinjen { width:55%; text-align:left }
    .table-medal thead th.medal-col, .table-medal tbody td.medal-col { width:10%; text-align:center }
    .input-with-icon{position:relative;display:inline-block}
    .input-with-icon .icon-search{position:absolute;left:14px;top:50%;transform:translateY(-50%);width:18px;height:18px;z-index:3;pointer-events:none}
    .medal-search{width:100%;padding-left:48px !important;border-radius:12px;border:1px solid #e6edf3;background:#fff;box-shadow:0 6px 18px rgba(15,23,42,0.06);transition:box-shadow .12s ease,border-color .12s ease;font-size:1.05rem;height:48px;box-sizing:border-box;z-index:1}
    .medal-select{border-radius:12px;border:1px solid #e6edf3;padding:8px 44px 8px 14px;appearance:none;background-color:#fff;background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="%236B7280" d="M7 10l5 5 5-5z"/></svg>');background-repeat:no-repeat;background-position:right 14px center;background-size:16px 16px;box-shadow:0 6px 18px rgba(15,23,42,0.06);transition:box-shadow .12s ease,border-color .12s ease;font-size:1rem;height:48px}
    .icon-medal{width:34px;height:34px;vertical-align:middle}
    .kontinjen-logo{width:36px;height:24px;object-fit:contain;border-radius:4px;margin-right:12px;flex:0 0 auto}
    </style>

    <!-- Medal detail modal (public) -->
    <style>
    /* Modal: fixed, professional layout for paginated table (vanilla CSS only) */
    /* Container positioning: horizontally centered, slightly from top */
    #medalDetailModalPublic .modal-dialog {
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
        top: 6vh; /* slightly from top center */
        margin: 0;
        width: min(900px, 95vw);
        max-width: 95vw;
        z-index: 1050;
    }

    /* Fixed modal size tuned for 10 rows (row ~44px); adjust as needed */
    #medalDetailModalPublic .modal-content {
        height: 640px; /* fixed height to avoid jumps */
        max-height: calc(100vh - 12vh);
        box-shadow: 0 18px 40px rgba(2,6,23,0.18);
        border-radius: 8px;
        overflow: hidden; /* enforce internal scrolling */
        display: flex;
        flex-direction: column;
        background: #fff;
        border: 1px solid rgba(15,23,42,0.06);
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }

    /* Modal header: standard height */
    #medalDetailModalPublic .modal-header {
        flex: 0 0 auto;
        padding: 14px 18px;
        border-bottom: 1px solid rgba(15,23,42,0.04);
        background: linear-gradient(180deg, rgba(245,248,255,0.6), rgba(255,255,255,0));
    }

    /* Modal body: column layout so table area can scroll while paginator stays visible */
    #medalDetailModalPublic .modal-body {
        flex: 1 1 auto;
        padding: 12px 18px;
        display: flex;
        flex-direction: column;
    }

    /* Keep paginator area visible at bottom */
    #medalDetailModalPublic .modal-footer {
        flex: 0 0 auto;
        padding: 10px 18px;
        border-top: 1px solid rgba(15,23,42,0.04);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Table layout: header sticky, table area scrolls inside the responsive wrapper */
    #medalDetailModalPublic .table-responsive { width: 100%; flex: 1 1 auto; overflow: auto; }
    #medalDetailModalPublic table { width: 100%; border-collapse: collapse; font-size: 0.95rem; min-width: 520px }
    #medalDetailModalPublic thead th {
        position: sticky;
        top: 0; /* stick to the top of modal body */
        background: #ffffff;
        z-index: 3;
        padding: 10px 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 1px solid rgba(15,23,42,0.06);
    }

    #medalDetailModalPublic tbody { }
    #medalDetailModalPublic tbody tr { border-bottom: 1px solid rgba(15,23,42,0.04); }
    #medalDetailModalPublic td, #medalDetailModalPublic th { padding: 10px 12px; vertical-align: middle }

    /* When table has many rows, modal-body provides the scroll; header remains visible */
    /* Empty state: reserve same space but show placeholder inside the scrolling area */
    #medalDetailModalPublic .empty-state {
        min-height: 360px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667085;
        font-size: 1rem;
        background: linear-gradient(180deg, rgba(250,251,255,0.5), rgba(255,255,255,0.5));
        border-radius: 6px;
        margin: 6px 0;
    }

    /* Responsive safety: ensure modal never overflows viewport */
    @media (max-height: 700px) {
        #medalDetailModalPublic .modal-content { height: calc(100vh - 12vh); }
    }

    @media (max-width: 520px) {
        #medalDetailModalPublic .modal-content { width: 95vw; height: calc(100vh - 10vh) }
        #medalDetailModalPublic thead th { font-size: 0.9rem }
    }
    </style>
    <div class="modal fade modal-top" id="medalDetailModalPublic" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Medal Recipients</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-semibold" id="medalDetailTitlePublic"></div>
                        <div class="badge bg-light text-dark" id="medalDetailMedalPublic"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Contingent</th>
                                    <th>Sport</th>
                                    <th>Event</th>
                                </tr>
                            </thead>
                            <tbody id="medalDetailBodyPublic">
                                <tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="text-muted small" id="medalDetailSummaryPublic"></div>
                        <div class="d-flex align-items-center gap-2">
                            <select class="form-select form-select-sm" id="medalDetailPageSizePublic" style="width:80px;">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <nav aria-label="Medal pagination">
                                <ul class="pagination pagination-sm mb-0" id="medalDetailPagerPublic"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Client-side filter and sort for medal table
    (function(){
        var table = document.getElementById('medalTable');
        var tbody = table.querySelector('tbody');
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));

        var searchInput = document.getElementById('medalSearch');
        var sortSelect = document.getElementById('medalSort');

        function renderRows(list){
            tbody.innerHTML = '';
            list.forEach(function(r){ tbody.appendChild(r); });
        }

        function filterRows(q){
            if(!q){ renderRows(rows.slice()); return; }
            q = q.trim().toLowerCase();
            var filtered = rows.filter(function(r){ return r.getAttribute('data-name').indexOf(q) !== -1; });
            renderRows(filtered);
        }

        function sortRows(mode){
            var sorted = rows.slice();
            if(mode === 'rank_desc') sorted.sort(function(a,b){ return parseInt(a.getAttribute('data-rank')) - parseInt(b.getAttribute('data-rank')); });
            else if(mode === 'gold_desc') sorted.sort(function(a,b){ return parseInt(b.getAttribute('data-gold')) - parseInt(a.getAttribute('data-gold')) || parseInt(a.getAttribute('data-rank')) - parseInt(b.getAttribute('data-rank')); });
            else if(mode === 'total_desc') sorted.sort(function(a,b){ return parseInt(b.getAttribute('data-total')) - parseInt(a.getAttribute('data-total')) || parseInt(a.getAttribute('data-rank')) - parseInt(b.getAttribute('data-rank')); });
            else if(mode === 'name_asc') sorted.sort(function(a,b){ return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name')); });
            rows = sorted;
            renderRows(rows.slice());
        }

        // events
        var searchTimer = null;
        if (searchInput) searchInput.addEventListener('input', function(){
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function(){ filterRows(searchInput.value); }, 160);
        });

        if (sortSelect) sortSelect.addEventListener('change', function(){ sortRows(sortSelect.value); });

        // initial sort
        sortRows((sortSelect && sortSelect.value) || 'rank_desc');
    })();
    </script>

    <script>
    (function(){
        // Public medal detail modal behavior (uses jQuery + bootstrap present in layout)
        function whenJQ(cb){ if (window.jQuery){ cb(window.jQuery); } else { setTimeout(function(){ whenJQ(cb); }, 50); } }
        whenJQ(function($){
            var cache = [];
            var pageSize = 10, currentPage = 1;

            function renderPager(total, $pager, summaryEl){
                var totalPages = Math.max(1, Math.ceil(total / pageSize));
                if (currentPage > totalPages) currentPage = totalPages;
                $pager.empty();
                var add = function(label, page, disabled, active){
                    var li = $('<li class="page-item">');
                    if (disabled) li.addClass('disabled');
                    if (active) li.addClass('active');
                    var a = $('<a class="page-link" href="#">').text(label).data('page', page);
                    li.append(a); $pager.append(li);
                };
                add('«', currentPage-1, currentPage===1, false);
                var start = Math.max(1, currentPage-2), end = Math.min(totalPages, start+4);
                for (var p=start; p<=end; p++){ add(p, p, false, p===currentPage); }
                add('»', currentPage+1, currentPage===totalPages, false);
                var startRow = total === 0 ? 0 : (currentPage-1)*pageSize + 1;
                var endRow = Math.min(total, currentPage*pageSize);
                summaryEl.text(total === 0 ? 'No records' : ('Showing '+startRow+' - '+endRow+' of '+total));
            }

            function renderPage($body, $pager, summaryEl){
                if (!cache.length){ $body.html('<tr><td colspan="5" class="text-center text-muted">No recipients.</td></tr>'); renderPager(0, $pager, summaryEl); return; }
                var start = (currentPage-1)*pageSize; var end = start + pageSize; var pageRows = cache.slice(start, end);
                var html = '';
                pageRows.forEach(function(r, idx){ html += '<tr>'+
                    '<td>'+(start+idx+1)+'</td>'+
                    '<td>'+(r.nama_pasukan || '-')+'</td>'+
                    '<td>'+(String(r.nama_kontinjen || r.kod_universiti || '-').toUpperCase())+'</td>'+
                    '<td>'+(r.nama_sukan || '-')+'</td>'+
                    '<td>'+(r.nama_kategori || '-')+'</td>'+
                '</tr>'; });
                $body.html(html);
                renderPager(cache.length, $pager, summaryEl);
            }

            $('#medalDetailPagerPublic').on('click', 'a.page-link', function(e){ e.preventDefault(); var p = $(this).data('page'); if (!p) return; currentPage = parseInt(p,10) || 1; renderPage($('#medalDetailBodyPublic'), $('#medalDetailPagerPublic'), $('#medalDetailSummaryPublic')); });
            $('#medalDetailPageSizePublic').on('change', function(){ pageSize = parseInt($(this).val(),10) || 10; currentPage = 1; renderPage($('#medalDetailBodyPublic'), $('#medalDetailPagerPublic'), $('#medalDetailSummaryPublic')); });

            $(document).on('click', '.medal-detail-btn-public', function(){
                var $tr = $(this).closest('tr');
                var kod = $tr.data('kod');
                var name = $tr.data('name') || kod;
                var medal = $(this).data('medal');
                if (!kod || !medal) return;
                $('#medalDetailTitlePublic').text(String(name).toUpperCase());
                $('#medalDetailMedalPublic').text(medal.toUpperCase());
                $('#medalDetailBodyPublic').html('<tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>');
                $('#medalDetailSummaryPublic').text(''); cache = []; currentPage = 1;
                var $modal = $('#medalDetailModalPublic');
                $modal.modal('show');
                $.ajax({ url: '<?php echo url('ajax/medal_recipients_public.php'); ?>', data: { kod_universiti: kod, medal: medal }, dataType: 'json' })
                .done(function(res){ if (!res || res.status !== 'ok'){ $('#medalDetailBodyPublic').html('<tr><td colspan="5" class="text-center text-danger">Failed to load data</td></tr>'); $('#medalDetailSummaryPublic').text('Failed to load data'); return; } cache = res.data || []; renderPage($('#medalDetailBodyPublic'), $('#medalDetailPagerPublic'), $('#medalDetailSummaryPublic')); })
                .fail(function(){ $('#medalDetailBodyPublic').html('<tr><td colspan="5" class="text-center text-danger">Network error</td></tr>'); $('#medalDetailSummaryPublic').text('Network error'); });
            });

            // Click handler for Total count: fetch all three medal types and merge results client-side
            $(document).on('click', '.medal-detail-total-public', function(){
                var $tr = $(this).closest('tr');
                var kod = $tr.data('kod');
                var name = $tr.data('name') || kod;
                if (!kod) return;
                $('#medalDetailTitlePublic').text(String(name).toUpperCase());
                $('#medalDetailMedalPublic').text('ALL');
                $('#medalDetailBodyPublic').html('<tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>');
                $('#medalDetailSummaryPublic').text(''); cache = []; currentPage = 1;
                var $modal = $('#medalDetailModalPublic'); $modal.modal('show');
                var endpoint = '<?php echo url('ajax/medal_recipients_public.php'); ?>';
                var types = ['emas','perak','gangsa'];
                var requests = types.map(function(m){ return $.ajax({ url: endpoint, data: { kod_universiti: kod, medal: m }, dataType: 'json' }); });
                $.when.apply($, requests).done(function(){
                    // arguments can be single or multiple depending on number of requests
                    var responses = Array.prototype.slice.call(arguments);
                    var merged = [];
                    var seen = {};
                    responses.forEach(function(resp){
                        // when single request, resp is [data, status, jqXHR]; when multiple, each arg is that tuple
                        var obj = Array.isArray(resp) && resp[0] ? resp[0] : resp;
                        if (!obj || obj.status !== 'ok' || !Array.isArray(obj.data)) return;
                        obj.data.forEach(function(r){
                            var id = r.participant_id || (r.nama_pasukan + '|' + r.nama_kategori + '|' + r.nama_sukan);
                            if (!seen[id]){ seen[id] = true; merged.push(r); }
                        });
                    });
                    cache = merged; renderPage($('#medalDetailBodyPublic'), $('#medalDetailPagerPublic'), $('#medalDetailSummaryPublic'));
                }).fail(function(){
                    $('#medalDetailBodyPublic').html('<tr><td colspan="5" class="text-center text-danger">Failed to load data</td></tr>'); $('#medalDetailSummaryPublic').text('Failed to load data');
                });
            });
        });
    })();
    </script>

<?php
$content = ob_get_clean();
?>
<!-- Sponsors and footer removed from public medal standings as requested -->
<?php
require_once __DIR__ . '/../includes/layout_public.php';
?>
