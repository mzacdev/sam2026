<?php
/**
 * Sijil Penyertaan
 * Access: ADMIN, CONTINGENT, VIEWER
 *
 * This page reuses the managers/coaches AJAX endpoint from pages/ringkasan.php
 * to avoid duplicating SQL/business logic. It also queries athletes directly
 * for athlete certificates. Access controlled via RBAC same as other pages.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/rbac.php';

Session::start();
$auth = getAuth();
$auth->requireAuth();
$rbac = getRBAC();
$rbac->requirePageAccess('pages/sijil.php');

$page_title = 'Sijil Penyertaan';

// load list of universities (same approach as ringkasan)
$unis = [];
try {
    $db = getDB();
    $sqlUnis = "SELECT kod_universiti, nama_pendek, nama_universiti FROM table_ref_universiti WHERE status = 1 ORDER BY COALESCE(NULLIF(nama_pendek,''), nama_universiti)";
    $stUn = $db->query($sqlUnis);
    $rowsUn = $stUn->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($rowsUn as $u) {
        $kod = trim((string)($u['kod_universiti'] ?? ''));
        $short = trim((string)($u['nama_pendek'] ?? ''));
        $full = trim((string)($u['nama_universiti'] ?? ''));
        $display = $short !== '' ? $short : ($full !== '' ? $full : $kod);
        if ($kod === '') continue;
        $unis[] = ['kod_universiti' => $kod, 'nama_universiti' => $display];
    }
} catch (Exception $e) {
    $unis = [];
}

// selected contingent code (uppercase)
$kod = strtoupper(trim((string)($_GET['kod'] ?? '')));

// Cache-busting version for the certificate image (use file modification time)
$img_rel_path = __DIR__ . '/../assets/img/sijil/sam2026_sijil_penyertaan.jpg';
$img_ver = file_exists($img_rel_path) ? filemtime($img_rel_path) : time();
$img_url_versioned = url('assets/img/sijil/sam2026_sijil_penyertaan.jpg') . '?v=' . $img_ver;

// Lightweight AJAX endpoint: return athletes for a kontinjen when requested
$ajax = trim((string)($_GET['ajax'] ?? ''));
if ($ajax === 'athletes') {
    header('Content-Type: application/json; charset=utf-8');
    $out = ['ok' => false, 'rows' => [], 'elapsed_ms' => null, 'error' => null, 'count' => 0];
    $k = strtoupper(trim((string)($_GET['kod'] ?? '')));
    if ($k === '') {
        echo json_encode($out);
        exit;
    }
    $t0 = microtime(true);
    try {
        $db = getDB();
        $sqlA = "SELECT pa.id AS id, TRIM(pa.nama) AS nama,
                        COALESCE(s.nama_sukan, '') AS sukan,
                        COALESCE(kt.nama_kategori, '') AS acara
            FROM table_pasukan_atlet pa
            JOIN table_pasukan p ON p.id = pa.pasukan_id
            LEFT JOIN table_sukan s ON s.id = p.sukan_id
            LEFT JOIN table_kategori kt ON kt.id = pa.kategori_id
            LEFT JOIN table_kontinjen k ON k.id = p.kontinjen_id
            WHERE UPPER(COALESCE(k.kod_universiti,'')) = :kod_val
              AND pa.deleted_at IS NULL
              AND p.deleted_at IS NULL
            ORDER BY s.nama_sukan, kt.nama_kategori, pa.nama";
        $stA = $db->prepare($sqlA);
        $stA->execute([':kod_val' => $k]);
        $rows = $stA->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $out['ok'] = true;
        $out['rows'] = $rows;
        $out['count'] = count($rows);
    } catch (Exception $e) {
        $out['ok'] = false;
        $out['error'] = $e->getMessage();
    }
    $out['elapsed_ms'] = (int)( (microtime(true) - $t0) * 1000 );
    echo json_encode($out);
    exit;
}

// Handle printing all athletes for a kontinjen (multi-page HTML or combined PDF)
$printAll = trim((string)($_GET['print_all'] ?? ''));
if ($printAll !== '') {
    $kodAll = strtoupper(trim((string)($_GET['kod'] ?? '')));
    if ($kodAll === '') {
        http_response_code(400);
        echo "Kod kontinjen diperlukan.";
        exit;
    }
    try {
        $db = getDB();
        $sqlAll = "SELECT TRIM(pa.nama) AS nama, pa.id AS id, COALESCE(s.nama_sukan,'') AS sukan, COALESCE(kt.nama_kategori,'') AS acara
            FROM table_pasukan_atlet pa
            JOIN table_pasukan p ON p.id = pa.pasukan_id
            LEFT JOIN table_sukan s ON s.id = p.sukan_id
            LEFT JOIN table_kategori kt ON kt.id = pa.kategori_id
            LEFT JOIN table_kontinjen k ON k.id = p.kontinjen_id
            WHERE UPPER(COALESCE(k.kod_universiti,'')) = :kod_val
              AND pa.deleted_at IS NULL
              AND p.deleted_at IS NULL
            ORDER BY s.nama_sukan, kt.nama_kategori, pa.nama";
        $stAll = $db->prepare($sqlAll);
        $stAll->execute([':kod_val' => $kodAll]);
        $rowsAll = $stAll->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        $rowsAll = [];
    }

    if (empty($rowsAll)) {
        http_response_code(404);
        echo "Tiada atlet ditemui untuk kontinjen ini.";
        exit;
    }

    // Build combined HTML with one .page per athlete
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $absTemplateUrl = $scheme . '://' . $host . $img_url_versioned;

    $multiHtml = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sijil Penyertaan - Semua</title>' .
        '<style>@page{size:A4;margin:0}html,body{height:100%;margin:0;padding:0}body{background:#fff}.page{position:relative;width:210mm;height:297mm;overflow:hidden}.bg-img{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover;z-index:0}.cert-name{position:absolute;left:50%;top:38%;transform:translate(-50%,-50%);width:78%;text-align:center;font-weight:700;color:#000;line-height:1.05;z-index:1;font-size:20px}.cert-sport{position:absolute;left:50%;top:48%;transform:translateX(-50%);width:78%;text-align:center;color:#000;z-index:1;font-size:20px}.page,.bg-img{-webkit-print-color-adjust:exact;print-color-adjust:exact}</style></head><body>';

    foreach ($rowsAll as $ra) {
        $rname = trim((string)($ra['nama'] ?? ''));
        $rsukan = trim((string)($ra['sukan'] ?? ''));
        $racara = trim((string)($ra['acara'] ?? ''));
        $sukan_combo = $rsukan;
        if ($racara !== '') {
            $sukan_combo = $sukan_combo !== '' ? $sukan_combo . ' (' . $racara . ')' : $racara;
        }
        $multiHtml .= '<div class="page">' .
            '<img class="bg-img" src="' . htmlspecialchars($absTemplateUrl, ENT_QUOTES, 'UTF-8') . '" alt="background">' .
            '<div class="cert-name">' . htmlspecialchars($rname, ENT_QUOTES, 'UTF-8') . '</div>' .
            '<div class="cert-sport">' . htmlspecialchars($sukan_combo, ENT_QUOTES, 'UTF-8') . '</div>' .
        '</div>';
    }

    $multiHtml .= '<script> (function(){ if(window.top===window.self){ setTimeout(function(){ window.print(); },180); } })();</script></body></html>';

    // If download requested, attempt wkhtmltopdf generation
    $downloadAll = trim((string)($_GET['download'] ?? $_GET['dl'] ?? ''));
    if ($downloadAll === '1' || strtolower($downloadAll) === 'true') {
        $tmpDir = sys_get_temp_dir();
        $tmpHtml = tempnam($tmpDir, 'sijil_all_') . '.html';
        $tmpPdf = tempnam($tmpDir, 'sijil_all_') . '.pdf';
        file_put_contents($tmpHtml, $multiHtml);

        $wk = 'wkhtmltopdf';
        $whichCmd = (stripos(PHP_OS, 'WIN') === 0) ? 'where' : 'command -v';
        $binary = null;
        $rawPath = @shell_exec($whichCmd . ' ' . $wk);
        $pathCheck = is_null($rawPath) ? '' : trim($rawPath);
        if ($pathCheck) $binary = trim(explode("\n", $pathCheck)[0]);
        if (!$binary) $binary = $wk;

        $cmd = escapeshellcmd($binary) . ' --page-size A4 --margin-top 0 --margin-bottom 0 --margin-left 0 --margin-right 0 --enable-local-file-access ' . escapeshellarg($tmpHtml) . ' ' . escapeshellarg($tmpPdf) . ' 2>&1';
        $output = @shell_exec($cmd);

        if (file_exists($tmpPdf) && filesize($tmpPdf) > 0) {
            $dlname = 'sijil_semua_' . $kodAll . '_' . date('Ymd_His') . '.pdf';
            $dlNameEnc = rawurlencode($dlname);
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $dlname . '"; filename*=UTF-8\'\'' . $dlNameEnc);
            header('Content-Length: ' . filesize($tmpPdf));
            readfile($tmpPdf);
            @unlink($tmpHtml);
            @unlink($tmpPdf);
            exit;
        } else {
            @unlink($tmpHtml);
            @unlink($tmpPdf);
            // fallback to HTML output when PDF generation fails
        }
    }

    echo $multiHtml;
    exit;
}

// If a single print request is made (from the list), render one full-page A4 certificate
$printId = trim((string)($_GET['print_id'] ?? ''));
$download = trim((string)($_GET['download'] ?? $_GET['dl'] ?? ''));
if ($printId !== '') {
    // ensure numeric id to avoid injection (adjust if your IDs are UUIDs)
    $pid = is_numeric($printId) ? (int)$printId : 0;
    if ($pid <= 0) {
        http_response_code(400);
        echo "Invalid ID";
        exit;
    }
    try {
        $db = getDB();
        $sqlSingle = "SELECT TRIM(pa.nama) AS nama, pa.id AS id, COALESCE(s.nama_sukan,'') AS sukan, COALESCE(kt.nama_kategori,'') AS acara
            FROM table_pasukan_atlet pa
            JOIN table_pasukan p ON p.id = pa.pasukan_id
            LEFT JOIN table_sukan s ON s.id = p.sukan_id
            LEFT JOIN table_kategori kt ON kt.id = pa.kategori_id
            WHERE pa.id = :id AND pa.deleted_at IS NULL AND p.deleted_at IS NULL
            LIMIT 1";
        $st = $db->prepare($sqlSingle);
        $st->execute([':id' => $pid]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $row = false;
    }

    if (!$row) {
        http_response_code(404);
        echo "Rekod tidak ditemui.";
        exit;
    }

    $name = trim((string)($row['nama'] ?? ''));
    $sukan = trim((string)($row['sukan'] ?? ''));
    $acara = trim((string)($row['acara'] ?? ''));
    $sukan_combo = $sukan;
    if ($acara !== '') {
        $sukan_combo = $sukan_combo !== '' ? $sukan_combo . ' (' . $acara . ')' : $acara;
    }

    // Prepare HTML for certificate. For PDF generation we need absolute URLs.
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $absTemplateUrl = $scheme . '://' . $host . $img_url_versioned;

    $certHtml = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sijil Penyertaan</title>' .
        '<style>@page{size:A4;margin:0}html,body{height:100%;margin:0;padding:0}body{background:#fff}.page{position:relative;width:210mm;height:297mm;overflow:hidden}.bg-img{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover;z-index:0}.cert-name{position:absolute;left:50%;top:38%;transform:translate(-50%,-50%);width:78%;text-align:center;font-weight:700;color:#000;line-height:1.05;z-index:1;font-size:20px}.cert-sport{position:absolute;left:50%;top:48%;transform:translateX(-50%);width:78%;text-align:center;color:#000;z-index:1;font-size:20px}.page,.bg-img{-webkit-print-color-adjust:exact;print-color-adjust:exact}</style></head><body>' .
        '<div class="page">' .
            '<img class="bg-img" src="' . htmlspecialchars($absTemplateUrl, ENT_QUOTES, 'UTF-8') . '" alt="background">' .
            '<div class="cert-name">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</div>' .
            '<div class="cert-sport">' . htmlspecialchars($sukan_combo, ENT_QUOTES, 'UTF-8') . '</div>' .
        '</div>' .
        '<script> (function(){ var bg=document.querySelector(".bg-img"); function p(){ try{ if(window.top===window.self){ window.print(); } }catch(e){} } if(bg){ if(bg.complete) setTimeout(p,120); else { bg.addEventListener("load", p); bg.addEventListener("error", p); } } else setTimeout(p,200); })(); </script></body></html>';

    // If user requested download=1, try to generate PDF server-side using wkhtmltopdf
    if ($download === '1' || strtolower($download) === 'true') {
        // create temp files
        $tmpDir = sys_get_temp_dir();
        $tmpHtml = tempnam($tmpDir, 'sijil_') . '.html';
        $tmpPdf = tempnam($tmpDir, 'sijil_') . '.pdf';
        file_put_contents($tmpHtml, $certHtml);

        // command
        $wk = 'wkhtmltopdf';
        // try to find wkhtmltopdf full path on Windows or Unix
        $whichCmd = (stripos(PHP_OS, 'WIN') === 0) ? 'where' : 'command -v';
        $binary = null;
        $rawPath = @shell_exec($whichCmd . ' ' . $wk);
        $pathCheck = is_null($rawPath) ? '' : trim($rawPath);
        if ($pathCheck) $binary = trim(explode("\n", $pathCheck)[0]);
        if (!$binary) $binary = $wk; // rely on PATH

        $cmd = escapeshellcmd($binary) . ' --page-size A4 --margin-top 0 --margin-bottom 0 --margin-left 0 --margin-right 0 --enable-local-file-access ' . escapeshellarg($tmpHtml) . ' ' . escapeshellarg($tmpPdf) . ' 2>&1';
        $output = null;
        $ret = null;
        $output = shell_exec($cmd);

        if (file_exists($tmpPdf) && filesize($tmpPdf) > 0) {
            // create a safe filename using recipient name
            $raw = trim((string)$name);
            $slug = '';
            if ($raw !== '') {
                $trans = @iconv('UTF-8', 'ASCII//TRANSLIT', $raw);
                $trans = $trans !== false ? $trans : $raw;
                $slug = preg_replace('/[^A-Za-z0-9 _-]/', '', $trans);
                $slug = preg_replace('/[\s\t\r\n]+/', '_', $slug);
                $slug = preg_replace('/_+/', '_', $slug);
                $slug = trim($slug, '_');
                if ($slug === '') $slug = 'sijil';
                $slug = substr($slug, 0, 60);
            } else {
                $slug = 'sijil';
            }
            $downloadName = 'sijil_' . $pid . '_' . $slug . '.pdf';
            $downloadNameUtf = rawurlencode($downloadName);
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $downloadName . '"; filename*=UTF-8\'\'' . $downloadNameUtf);
            header('Content-Length: ' . filesize($tmpPdf));
            readfile($tmpPdf);
            @unlink($tmpHtml);
            @unlink($tmpPdf);
            exit;
        } else {
            // cleanup and fall back to HTML page if PDF generation failed
            @unlink($tmpHtml);
            @unlink($tmpPdf);
            // optionally log $output for debugging
        }
    }
    // If PDF not requested or generation failed, output the HTML certificate
    echo $certHtml;
    exit;
}

// Helper: try to fetch managers/coaches JSON from ringkasan AJAX endpoint
function fetch_managers_from_ringkasan($kod) {
    // Build absolute URL to ringkasan AJAX endpoint
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = url('pages/ringkasan.php');
    $query = http_build_query(['ajax' => 'managers', 'kod' => $kod]);
    $full = $scheme . '://' . $host . $path . '?' . $query;

    // Try file_get_contents first
    $opts = [
        'http' => [ 'method' => 'GET', 'header' => "X-Requested-With: XMLHttpRequest\r\n" ]
    ];
    $context = stream_context_create($opts);
    $json = @file_get_contents($full, false, $context);
    if ($json === false) {
        // fallback to cURL if available
        if (function_exists('curl_init')) {
            $ch = curl_init($full);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $json = curl_exec($ch);
            curl_close($ch);
        }
    }
    if (!$json) return [];
    $data = json_decode($json, true);
    if (!is_array($data) || empty($data['ok'])) return [];
    return $data['rows'] ?? [];
}

// Do not load data until a kontinjen is selected to avoid heavy initial queries
$managersRows = [];
$athletes = [];
if ($kod !== '') {
    // Fetch managers & coaches via ringkasan AJAX (reuse existing logic)
    try {
        $managersRows = fetch_managers_from_ringkasan($kod);
    } catch (Exception $e) {
        $managersRows = [];
    }

    // Do not fetch athletes here to keep page load fast.
    $athletes = [];
}

ob_start();
?>
<div class="container-fluid px-3">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="mb-1">Sijil Penyertaan</h2>
            <p class="text-muted mb-0">Cetak sijil untuk Atlet, Pengurus dan Jurulatih.</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body d-flex gap-3 align-items-end">
            <div>
                <label class="form-label small mb-1">Pilih Kontinjen</label>
                <form method="get" id="frmKont">
                    <div class="d-flex align-items-center">
                        <select id="selectKont" name="kod" class="form-select form-select-sm" style="min-width:360px;max-width:60%">
                            <option value="">-- Semua Kontinjen --</option>
                            <?php foreach ($unis as $u): ?>
                                <option value="<?php echo htmlspecialchars(strtoupper($u['kod_universiti']), ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($kod !== '' && strtoupper($u['kod_universiti']) === $kod) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u['nama_universiti'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="btnLoadList" class="btn btn-sm btn-secondary ms-2" style="min-width:120px">Papar Data</button>
                        <span id="loadStatus" class="ms-2 text-muted"></span>
                        <button type="button" id="btnPrintAll" class="btn btn-sm btn-primary ms-2" style="min-width:140px" disabled>Cetak Semua</button>
                        <div id="tableLoader" class="ms-auto align-self-center" style="display:none;">
                            <div class="spinner-border text-primary" role="status" style="width:1.6rem;height:1.6rem;vertical-align:middle;">
                                <span class="visually-hidden">Memuat...</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h5>Atlet</h5>
                    <div class="table-responsive" id="listWrap" style="display:none;position:relative;">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%;">No</th>
                                <th style="width:60%;">Nama Atlet</th>
                                <th style="width:10%;">Sukan</th>
                                <th style="width:15%;">Acara</th>
                                <th style="width:10%;">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody id="athleteBody"></tbody>
                    </table>
                </div>
                <script>
                    (function(){
                        var btn = document.getElementById('btnLoadList');
                        var btnAll = document.getElementById('btnPrintAll');
                        var status = document.getElementById('loadStatus');
                        var wrap = document.getElementById('listWrap');
                        var body = document.getElementById('athleteBody');
                        var loader = document.getElementById('tableLoader');
                        var lastRows = null;

                        function showLoader(){ try{ if(loader) loader.style.display = 'inline-block'; }catch(e){} }
                        function hideLoader(){ try{ if(loader) loader.style.display = 'none'; }catch(e){} }
                        function printDirect(pid){
                            try{
                                showLoader();
                                var iframe = document.createElement('iframe');
                                iframe.style.display = 'none';
                                iframe.src = window.location.pathname + '?print_id=' + encodeURIComponent(pid);
                                iframe.onload = function(){
                                    try { iframe.contentWindow.focus(); iframe.contentWindow.print(); } catch(e) { console.error('printDirect error', e); alert('Gagal memulakan cetakan.'); }
                                    setTimeout(function(){ try{ document.body.removeChild(iframe); }catch(e){}; hideLoader(); }, 1500);
                                };
                                document.body.appendChild(iframe);
                            }catch(e){ console.error(e); hideLoader(); alert('Gagal memulakan cetakan.'); }
                        }

                        function renderRows(rows){
                            body.innerHTML = '';
                            var i=1;
                            lastRows = rows || [];
                            rows.forEach(function(r){
                                var pid = r.id || '';
                                var name = (r.nama || '').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                                var sukan = (r.sukan || '').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                                var acara = (r.acara || '').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                                var tr = document.createElement('tr');
                                tr.innerHTML = '<td class="text-center">'+(i++)+'</td>'+
                                    '<td>'+name+'</td>'+
                                    '<td>'+sukan+'</td>'+
                                    '<td>'+acara+'</td>'+
                                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary do-print">Cetak</button></td>';
                                body.appendChild(tr);
                                var btnEl = tr.querySelector('.do-print');
                                if (btnEl) {
                                    btnEl.addEventListener('click', function(e){ e.preventDefault(); printDirect(pid); });
                                }
                            });
                            // enable the Cetak Semua button when we have rows
                            if (btnAll) {
                                try { btnAll.disabled = !(lastRows && lastRows.length > 0); } catch(e){}
                            }
                            hideLoader();
                        }
                        btn.addEventListener('click', function(){
                            var kod = (document.getElementById('selectKont') && document.getElementById('selectKont').value) ? document.getElementById('selectKont').value : '';
                            if (!kod) { status.textContent = 'Sila pilih kontinjen dahulu.'; return; }
                            var url = window.location.pathname + '?ajax=athletes&kod=' + encodeURIComponent(kod);
                            status.textContent = '';
                            btn.disabled = true;
                            showLoader();
                            console.log('Fetching athletes from', url);
                            fetch(url, { credentials: 'same-origin' })
                                .then(function(r){
                                    if (!r.ok) throw new Error('HTTP ' + r.status);
                                    return r.json();
                                })
                                .then(function(j){
                                    console.log('Athletes response', j);
                                    if (!j || !j.ok) { status.textContent = 'Tiada data.'; btn.disabled = false; hideLoader(); return; }
                                    renderRows(j.rows || []);
                                    wrap.style.display = '';
                                    status.textContent = '';
                                    btn.disabled = false;
                                }).catch(function(e){ console.error('Fetch error', e); status.textContent = 'Ralat mengambil data.'; btn.disabled = false; hideLoader(); alert('Gagal mendapatkan data atlet: '+ (e && e.message ? e.message : 'Unknown')); });
                        });

                        // Cetak Semua handler (server-side combined PDF or multi-page HTML fallback)
                        if (btnAll) {
                            btnAll.addEventListener('click', function(){
                                var kod = (document.getElementById('selectKont') && document.getElementById('selectKont').value) ? document.getElementById('selectKont').value : '';
                                if (!kod) { status.textContent = 'Sila pilih kontinjen dahulu.'; return; }
                                
                                btnAll.disabled = true;
                                showLoader();
                                try {
                                    var iframe = document.createElement('iframe');
                                    iframe.style.display = 'none';
                                    iframe.src = window.location.pathname + '?print_all=1&kod=' + encodeURIComponent(kod);
                                    iframe.onload = function(){
                                        try {
                                            iframe.contentWindow.focus();
                                            iframe.contentWindow.print();
                                        } catch(e) {
                                            console.error('printAll error', e);
                                            // fallback: open in new tab
                                            window.open(window.location.pathname + '?print_all=1&kod=' + encodeURIComponent(kod));
                                        }
                                        setTimeout(function(){ try{ document.body.removeChild(iframe); }catch(e){}; btnAll.disabled = false; status.textContent = ''; hideLoader(); }, 1500);
                                    };
                                    document.body.appendChild(iframe);
                                } catch(e) {
                                    console.error(e);
                                    btnAll.disabled = false;
                                    status.textContent = '';
                                    hideLoader();
                                    window.open(window.location.pathname + '?print_all=1&kod=' + encodeURIComponent(kod));
                                }
                            });
                        }
                    })();
                </script>
        </div>
    </div>

    <hr class="my-4">

    <div class="row">
        <div class="col-12">
            <h5>Pengurus &amp; Jurulatih</h5>
            <?php if (empty($managersRows)): ?>
                <div class="text-muted">Tiada data pengurus/jurulatih ditemui.</div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    <?php foreach ($managersRows as $r): ?>
                        <?php
                            $acara = trim((string)($r['acara'] ?? 'Tidak Berlabel'));
                            // pengurus and jurulatih are concatenated strings separated by ' ||| '
                            $pengurusList = [];
                            if (!empty($r['pengurus'])) {
                                $parts = explode(' ||| ', $r['pengurus']);
                                foreach ($parts as $p) { $p = trim($p); if ($p !== '') $pengurusList[] = $p; }
                            }
                            $jurulatihList = [];
                            if (!empty($r['jurulatih'])) {
                                $parts = explode(' ||| ', $r['jurulatih']);
                                foreach ($parts as $p) { $p = trim($p); if ($p !== '') $jurulatihList[] = $p; }
                            }
                        ?>
                        <?php foreach ($pengurusList as $p): ?>
                            <div class="col">
                                <div class="p-3 border rounded bg-white">
                                    <div class="fw-bold"><?php echo htmlspecialchars($p, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($acara, ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach ($jurulatihList as $j): ?>
                            <div class="col">
                                <div class="p-3 border rounded bg-white">
                                    <div class="fw-bold"><?php echo htmlspecialchars($j, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($acara, ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<style>
@media print {
    body { background: #fff; color: #000; }
    .container-fluid { padding: 0; }
    .card { box-shadow: none; border: none; }
    .btn { display: none !important; }
    .row-cols-1 .col { page-break-inside: avoid; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var templateUrl = '<?php echo htmlspecialchars($img_url_versioned, ENT_QUOTES, "UTF-8"); ?>';
    function buildCertHtml(name, sukanCombined){
        var html = '<!doctype html><html><head><meta charset="utf-8"><title>Sijil Penyertaan</title>' +
            '<style>html,body{height:100%;margin:0;padding:0}body{font-family:Arial,Helvetica,sans-serif}.page{position:relative;width:210mm;height:297mm;overflow:hidden}.bg-img{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover;z-index:0}.cert-name{position:absolute;left:50%;top:38%;transform:translate(-50%,-50%);font-size:20px;font-weight:700;color:#000;text-align:center;padding:0 30px;line-height:1.05;z-index:1}.cert-sport{position:absolute;left:50%;top:48%;transform:translateX(-50%);font-size:20px;color:#000;text-align:center;padding:0 30px;z-index:1}@media print{.bg-img{display:block}}</style></head><body>'+
            '<div class="page">'+
                '<img class="bg-img" src="'+templateUrl+'" alt="bg">'+
                '<div class="cert-name">'+(name||'')+'</div>'+
                '<div class="cert-sport">'+(sukanCombined||'')+'</div>'+
            '</div></body></html>';
        return html;
    }

    window.printCertificate = function(name, sukanCombined){
        try{
            var w = window.open('about:blank','_blank');
            if(!w) { alert('Sila benarkan pop-up untuk cetakan sijil.'); return; }
            var doc = w.document;
            doc.open();
            doc.write(buildCertHtml(name, sukanCombined));
            doc.close();
            setTimeout(function(){ try { w.focus(); w.print(); } catch(e) { console.error(e); } }, 700);
        }catch(e){ console.error(e); alert('Gagal membuka tetingkap cetak.'); }
    };

    document.querySelectorAll('.btn-print-cert').forEach(function(btn){
        btn.addEventListener('click', function(e){
            var name = this.getAttribute('data-name') || '';
            var sukanCombined = this.getAttribute('data-sukan') || '';
            printCertificate(name, sukanCombined);
        });
    });
});
</script>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
