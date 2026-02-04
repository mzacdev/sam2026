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
// Populate list of universities for the select (reuse ringkasan logic)
$unis = [];
try {
    $db = getDB();
    $sqlUnis = "SELECT kod_universiti, nama_pendek, nama_universiti FROM table_ref_universiti WHERE status = 1 ORDER BY COALESCE(NULLIF(nama_pendek,''), nama_universiti)";
    $stUn = $db->query($sqlUnis);
    $rowsUn = $stUn->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($rowsUn as $u) {
        $kodu = trim((string)($u['kod_universiti'] ?? ''));
        $short = trim((string)($u['nama_pendek'] ?? ''));
        $full = trim((string)($u['nama_universiti'] ?? ''));
        $display = $short !== '' ? $short : ($full !== '' ? $full : $kodu);
        if ($kodu === '') continue;
        $unis[] = ['kod_universiti' => $kodu, 'nama_universiti' => $display];
    }
} catch (Exception $e) {
    $unis = [];
}
// ensure $kod is defined (may be provided via GET when returning from form)
$kod = strtoupper(trim((string)($_GET['kod'] ?? '')));

// ensure template image URL variable exists to avoid PHP notices
if (!isset($img_url_versioned) || !$img_url_versioned) {
    $relPath = '/assets/img/sijil/sam2026_sijil_penyertaan.jpg';
    $fullPath = realpath(__DIR__ . '/..') . $relPath;
    $ver = @file_exists($fullPath) ? @filemtime($fullPath) : time();
    $img_url_versioned = $relPath . '?v=' . $ver;
}

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
    $type = strtolower(trim((string)($_GET['type'] ?? 'athletes')));
    try {
        if ($type === 'athletes') {
            $db = getDB();
            $sqlAll = "SELECT pa.id AS id, TRIM(pa.nama) AS nama, COALESCE(s.nama_sukan,'') AS sukan, COALESCE(kt.nama_kategori,'') AS acara
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
        } else {
            // build rows from managers endpoint
            $rowsAll = [];
            $mgrs = fetch_managers_from_ringkasan($kodAll);
            foreach ($mgrs as $m) {
                $acara = trim((string)($m['acara'] ?? ''));
                if ($type === 'pengurus') {
                    if (!empty($m['pengurus'])) {
                        $parts = explode(' ||| ', $m['pengurus']);
                        foreach ($parts as $p) {
                            $p = trim($p);
                            if ($p !== '') $rowsAll[] = ['nama' => $p, 'sukan' => '', 'acara' => $acara];
                        }
                    }
                } else if ($type === 'jurulatih') {
                    if (!empty($m['jurulatih'])) {
                        $parts = explode(' ||| ', $m['jurulatih']);
                        foreach ($parts as $p) {
                            $p = trim($p);
                            if ($p !== '') $rowsAll[] = ['nama' => $p, 'sukan' => '', 'acara' => $acara];
                        }
                    }
                }
            }
        }
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
    // choose template based on type
    if ($type === 'pengurus') {
        $img_rel = '/assets/img/sijil/sijil_pengurus.jpeg';
    } else if ($type === 'jurulatih') {
        $img_rel = '/assets/img/sijil/sijil_jurulatih.jpeg';
    } else {
        $img_rel = $img_url_versioned;
    }
    // Build absolute URL including BASE_URL so it works in subfolder deployments
    $absTemplateUrl = $scheme . '://' . $host . url(ltrim($img_rel, '/'));

    $multiHtml = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sijil Penyertaan - Semua</title>' .
        '<style>@page{size:A4;margin:0}html,body{height:100%;margin:0;padding:0}body{background:#fff}.page{position:relative;width:210mm;height:297mm;overflow:hidden}.bg-img{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover;z-index:0}.cert-name{position:absolute;left:50%;top:38%;transform:translate(-50%,-50%);width:78%;text-align:center;font-weight:700;color:#000;line-height:1.05;z-index:1;font-size:20px}.cert-sport{position:absolute;left:50%;top:48%;transform:translateX(-50%);width:78%;text-align:center;color:#000;z-index:1;font-size:20px}.page,.bg-img{-webkit-print-color-adjust:exact;print-color-adjust:exact}</style></head><body>';

    foreach ($rowsAll as $ra) {
        $rname = trim((string)($ra['nama'] ?? ''));
        // strip phone/email in parentheses or trailing emails for cleaner name
        $rname = preg_replace('/\s*\([^)]*\)/', '', $rname);
        $rname = preg_replace('/\s+\S+@\S+$/', '', $rname);
        $rname = trim($rname);
        $rsukan = trim((string)($ra['sukan'] ?? ''));
        $racara = trim((string)($ra['acara'] ?? ''));
        // only show sport/event for athletes; pengurus/jurulatih show name only
        if ($type === 'athletes') {
            $sukan_combo = $rsukan;
            if ($racara !== '') {
                $sukan_combo = $sukan_combo !== '' ? $sukan_combo . ' (' . $racara . ')' : $racara;
            }
        } else {
            $sukan_combo = '';
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
    // Use the athlete template explicitly to avoid mismatches when $img_url_versioned
    // may point elsewhere. Build a versioned absolute URL for the athlete background.
    $athRel = '/assets/img/sijil/sam2026_sijil_penyertaan.jpg';
    $athFull = realpath(__DIR__ . '/..') . $athRel;
    $athVer = @file_exists($athFull) ? @filemtime($athFull) : time();
    // include BASE_URL prefix so it resolves correctly in production subfolders
    $absTemplateUrl = $scheme . '://' . $host . url('assets/img/sijil/sam2026_sijil_penyertaan.jpg') . '?v=' . (int)$athVer;

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
            <p class="text-muted mb-0">Cetak sijil untuk Pengurus dan Jurulatih.</p>
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
                <div id="tabsWrap" style="display:none;">
                        <ul class="nav nav-tabs" id="sijilTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-pengurus" data-bs-toggle="tab" data-bs-target="#pane-pengurus" type="button" role="tab">Pengurus <span id="countPengurus" class="badge bg-secondary ms-1">0</span></button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-jurulatih" data-bs-toggle="tab" data-bs-target="#pane-jurulatih" type="button" role="tab">Jurulatih <span id="countJurulatih" class="badge bg-secondary ms-1">0</span></button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-atlet" data-bs-toggle="tab" data-bs-target="#pane-atlet" type="button" role="tab">Atlet <span id="countAtlet" class="badge bg-secondary ms-1">0</span></button>
                            </li>
                        </ul>
                        <div class="tab-content border border-top-0 p-3" id="sijilTabContent">
                            <div class="tab-pane fade show active" id="pane-pengurus" role="tabpanel">
                                <div class="d-flex mb-2">
                                    <div class="me-auto"></div>
                                    <button type="button" id="printAllPengurus" class="btn btn-sm btn-primary">Cetak Semua</button>
                                </div>
                                <div class="table-responsive"><table class="table table-sm table-hover"><thead class="table-light"><tr><th style="width:5%">No</th><th style="width:80%">Nama Pengurus</th><th style="width:15%">No Telefon</th><th style="width:12%">Tindakan</th></tr></thead><tbody id="pengurusBody"></tbody></table></div>
                                <div class="d-flex justify-content-end align-items-center mt-2">
                                    <button type="button" id="pengurusPrev" class="btn btn-sm btn-outline-secondary me-2">Prev</button>
                                    <span id="pengurusPageInfo" class="me-2">Page 1/1</span>
                                    <button type="button" id="pengurusNext" class="btn btn-sm btn-outline-secondary">Next</button>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pane-jurulatih" role="tabpanel">
                                <div class="d-flex mb-2">
                                    <div class="me-auto"></div>
                                    <button type="button" id="printAllJurulatih" class="btn btn-sm btn-primary">Cetak Semua</button>
                                </div>
                                <div class="table-responsive"><table class="table table-sm table-hover"><thead class="table-light"><tr><th style="width:5%">No</th><th style="width:80%">Nama Jurulatih</th><th style="width:15%">No Telefon</th><th style="width:12%">Tindakan</th></tr></thead><tbody id="jurulatihBody"></tbody></table></div>
                                <div class="d-flex justify-content-end align-items-center mt-2">
                                    <button type="button" id="jurulatihPrev" class="btn btn-sm btn-outline-secondary me-2">Prev</button>
                                    <span id="jurulatihPageInfo" class="me-2">Page 1/1</span>
                                    <button type="button" id="jurulatihNext" class="btn btn-sm btn-outline-secondary">Next</button>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pane-atlet" role="tabpanel">
                                <div class="d-flex mb-2">
                                    <div class="me-auto"></div>
                                    <button type="button" id="printAllAtlet" class="btn btn-sm btn-primary">Cetak Semua</button>
                                </div>
                                <div class="table-responsive"><table class="table table-sm table-hover align-middle"><thead class="table-light"><tr><th style="width:5%">No</th><th style="width:75%">Nama Atlet</th><th style="width:13%">Sukan / Acara</th><th style="width:7%">Tindakan</th></tr></thead><tbody id="athleteBody"></tbody></table></div>
                                <div class="d-flex justify-content-end align-items-center mt-2">
                                    <button type="button" id="athletePrev" class="btn btn-sm btn-outline-secondary me-2">Prev</button>
                                    <span id="athletePageInfo" class="me-2">Page 1/1</span>
                                    <button type="button" id="athleteNext" class="btn btn-sm btn-outline-secondary">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <script>
                    (function(){
                                var btn = document.getElementById('btnLoadList');
                                var status = document.getElementById('loadStatus');
                                var wrap = document.getElementById('tabsWrap');
                                var athleteBody = document.getElementById('athleteBody');
                                var pengurusBody = document.getElementById('pengurusBody');
                                var jurulatihBody = document.getElementById('jurulatihBody');
                                var loader = document.getElementById('tableLoader');
                                var printAllPengurus = document.getElementById('printAllPengurus');
                                var printAllJurulatih = document.getElementById('printAllJurulatih');
                                var printAllAtlet = document.getElementById('printAllAtlet');
                                var countPengurus = document.getElementById('countPengurus');
                                var countJurulatih = document.getElementById('countJurulatih');
                                var countAtlet = document.getElementById('countAtlet');
                                var lastRows = { athletes: [], pengurus: [], jurulatih: [] };

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

                                function buildCertHtml(name, sukanCombined, templateOverride){
                                var templateUrl = templateOverride || <?php echo json_encode($img_url_versioned); ?>;
                                var html = '<!doctype html><html><head><meta charset="utf-8"><title>Sijil Penyertaan</title>' +
                                    '<style>@page{size:A4;margin:0}html,body{height:100%;margin:0;padding:0}body{background:#fff}.page{position:relative;width:210mm;height:297mm;overflow:hidden}.bg-img{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover;z-index:0}.cert-name{position:absolute;left:50%;top:38%;transform:translate(-50%,-50%);width:78%;text-align:center;font-weight:700;color:#000;line-height:1.05;z-index:1;font-size:20px}.cert-sport{position:absolute;left:50%;top:48%;transform:translateX(-50%);width:78%;text-align:center;color:#000;z-index:1;font-size:20px}.page,.bg-img{-webkit-print-color-adjust:exact;print-color-adjust:exact}</style></head><body>' +
                                    '<div class="page">' +
                                        '<img class="bg-img" src="'+templateUrl+'" alt="background">' +
                                        '<div class="cert-name">'+(name||'')+'</div>' +
                                        '<div class="cert-sport">'+(sukanCombined||'')+'</div>' +
                                    '</div>' +
                                    '<script> (function(){ if(window.top===window.self){ setTimeout(function(){ window.print(); },120); } })();<\/script></body></html>';
                                return html;
                            }

                            function printDirectHtml(html){
                                try{
                                    showLoader();
                                    var iframe = document.createElement('iframe');
                                    iframe.style.display = 'none';
                                    document.body.appendChild(iframe);
                                    var doc = iframe.contentWindow.document;
                                    doc.open(); doc.write(html); doc.close();
                                    iframe.onload = function(){ try{ iframe.contentWindow.focus(); iframe.contentWindow.print(); }catch(e){ console.error(e); } setTimeout(function(){ try{ document.body.removeChild(iframe); }catch(e){}; hideLoader(); },1500); };
                                }catch(e){ console.error(e); hideLoader(); }
                            }

                        // Pagination and rendering helpers
                        var PAGE_SIZE = 10;
                        var currentPengurusPage = 1, currentJurulatihPage = 1, currentAthletePage = 1;

                        function getSelectedKod(){ var sel = document.getElementById('selectKont'); return sel && sel.value ? sel.value : ''; }

                        function renderPengurusPage(){
                            var list = lastRows.pengurus || [];
                            var total = list.length;
                            var pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
                            if (currentPengurusPage > pages) currentPengurusPage = pages;
                            var start = (currentPengurusPage - 1) * PAGE_SIZE;
                            var slice = list.slice(start, start + PAGE_SIZE);
                            pengurusBody.innerHTML = '';
                            slice.forEach(function(p, idx){
                                var nIdx = start + idx + 1;
                                var tr = document.createElement('tr');
                                var telSafe = (p.tel || '').replace(/</g,'&lt;');
                                tr.innerHTML = '<td class="text-center">'+nIdx+'</td>' +
                                    '<td>'+ (p.nama.replace(/</g,'&lt;')) +'</td>' +
                                    '<td>'+ telSafe +'</td>' +
                                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary do-print-pengurus">Cetak</button></td>';
                                pengurusBody.appendChild(tr);
                                tr.querySelector('.do-print-pengurus').addEventListener('click', function(){ printDirectHtml(buildCertHtml(p.nama, '', <?php echo json_encode(url('assets/img/sijil/sijil_pengurus.jpeg')); ?>)); });
                            });
                            try{ document.getElementById('pengurusPageInfo').textContent = 'Page ' + currentPengurusPage + '/' + pages; }catch(e){}
                            try{ document.getElementById('pengurusPrev').disabled = currentPengurusPage <= 1; }catch(e){}
                            try{ document.getElementById('pengurusNext').disabled = currentPengurusPage >= pages; }catch(e){}
                            if (countPengurus) countPengurus.textContent = total;
                            if (printAllPengurus) printAllPengurus.disabled = total === 0;
                        }

                        function renderJurulatihPage(){
                            var list = lastRows.jurulatih || [];
                            var total = list.length;
                            var pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
                            if (currentJurulatihPage > pages) currentJurulatihPage = pages;
                            var start = (currentJurulatihPage - 1) * PAGE_SIZE;
                            var slice = list.slice(start, start + PAGE_SIZE);
                            jurulatihBody.innerHTML = '';
                            slice.forEach(function(p, idx){
                                var nIdx = start + idx + 1;
                                var tr = document.createElement('tr');
                                var telSafe = (p.tel || '').replace(/</g,'&lt;');
                                tr.innerHTML = '<td class="text-center">'+nIdx+'</td>' +
                                    '<td>'+ (p.nama.replace(/</g,'&lt;')) +'</td>' +
                                    '<td>'+ telSafe +'</td>' +
                                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary do-print-jurulatih">Cetak</button></td>';
                                jurulatihBody.appendChild(tr);
                                tr.querySelector('.do-print-jurulatih').addEventListener('click', function(){ printDirectHtml(buildCertHtml(p.nama, '', <?php echo json_encode(url('assets/img/sijil/sijil_jurulatih.jpeg')); ?>)); });
                            });
                            try{ document.getElementById('jurulatihPageInfo').textContent = 'Page ' + currentJurulatihPage + '/' + pages; }catch(e){}
                            try{ document.getElementById('jurulatihPrev').disabled = currentJurulatihPage <= 1; }catch(e){}
                            try{ document.getElementById('jurulatihNext').disabled = currentJurulatihPage >= pages; }catch(e){}
                            if (countJurulatih) countJurulatih.textContent = total;
                            if (printAllJurulatih) printAllJurulatih.disabled = total === 0;
                        }

                        function renderAthletePage(){
                            var list = lastRows.athletes || [];
                            var total = list.length;
                            var pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
                            if (currentAthletePage > pages) currentAthletePage = pages;
                            var start = (currentAthletePage - 1) * PAGE_SIZE;
                            var slice = list.slice(start, start + PAGE_SIZE);
                            athleteBody.innerHTML = '';
                            slice.forEach(function(r, idx){
                                var pid = r.id || '';
                                var name = (r.nama || '').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                                var sukan = (r.sukan || '').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                                var acara = (r.acara || '').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                                var info = sukan;
                                if (acara !== '') info = info !== '' ? (info + ' (' + acara + ')') : acara;
                                var nIdx = start + idx + 1;
                                var tr = document.createElement('tr');
                                tr.innerHTML = '<td class="text-center">'+nIdx+'</td>'+
                                    '<td>'+name+'</td>'+
                                    '<td>'+info+'</td>'+
                                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary do-print">Cetak</button></td>';
                                athleteBody.appendChild(tr);
                                var btnEl = tr.querySelector('.do-print');
                                if (btnEl) {
                                    btnEl.addEventListener('click', function(e){ e.preventDefault(); printDirect(pid); });
                                }
                            });
                            try{ document.getElementById('athletePageInfo').textContent = 'Page ' + currentAthletePage + '/' + pages; }catch(e){}
                            try{ document.getElementById('athletePrev').disabled = currentAthletePage <= 1; }catch(e){}
                            try{ document.getElementById('athleteNext').disabled = currentAthletePage >= pages; }catch(e){}
                            if (countAtlet) countAtlet.textContent = total;
                            if (printAllAtlet) printAllAtlet.disabled = total === 0;
                        }

                        // wire prev/next buttons
                        try{
                            document.getElementById('pengurusPrev').addEventListener('click', function(){ if (currentPengurusPage>1){ currentPengurusPage--; renderPengurusPage(); } });
                            document.getElementById('pengurusNext').addEventListener('click', function(){ currentPengurusPage++; renderPengurusPage(); });
                            document.getElementById('jurulatihPrev').addEventListener('click', function(){ if (currentJurulatihPage>1){ currentJurulatihPage--; renderJurulatihPage(); } });
                            document.getElementById('jurulatihNext').addEventListener('click', function(){ currentJurulatihPage++; renderJurulatihPage(); });
                            document.getElementById('athletePrev').addEventListener('click', function(){ if (currentAthletePage>1){ currentAthletePage--; renderAthletePage(); } });
                            document.getElementById('athleteNext').addEventListener('click', function(){ currentAthletePage++; renderAthletePage(); });
                        }catch(e){}

                        // wire print all buttons to client-side print (build multi-page HTML and print via hidden iframe)
                        try{
                            var tmplPengurus = <?php echo json_encode(url('assets/img/sijil/sijil_pengurus.jpeg')); ?>;
                            var tmplJurulatih = <?php echo json_encode(url('assets/img/sijil/sijil_jurulatih.jpeg')); ?>;
                            var tmplAtlet = <?php
                                $athPath = __DIR__ . '/../assets/img/sijil/sam2026_sijil_penyertaan.jpg';
                                $ver = @file_exists($athPath) ? @filemtime($athPath) : time();
                                echo json_encode(url('assets/img/sijil/sam2026_sijil_penyertaan.jpg') . '?v=' . (int)$ver);
                            ?>;

                            function buildMultiHtml(rows, type){
                                var img = type === 'pengurus' ? tmplPengurus : (type === 'jurulatih' ? tmplJurulatih : tmplAtlet);
                                var html = '<!doctype html><html><head><meta charset="utf-8"><title>Cetak Semua Sijil</title>' +
                                    '<style>@page{size:A4;margin:0}html,body{height:100%;margin:0;padding:0}body{background:#fff}.page{position:relative;width:210mm;height:297mm;overflow:hidden}.bg-img{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover;z-index:0}.cert-name{position:absolute;left:50%;top:38%;transform:translate(-50%,-50%);width:78%;text-align:center;font-weight:700;color:#000;line-height:1.05;z-index:1;font-size:20px}.cert-sport{position:absolute;left:50%;top:48%;transform:translateX(-50%);width:78%;text-align:center;color:#000;z-index:1;font-size:20px}.page,.bg-img{-webkit-print-color-adjust:exact;print-color-adjust:exact}</style></head><body>';
                                rows.forEach(function(r){
                                    var raw = (r.nama || '').toString();
                                    // strip phone/email
                                    var clean = raw.replace(/\s*\([^)]*\)/g,'').replace(/\s+\S+@\S+$/,'').trim();
                                    var name = clean || raw;
                                    var sukan_combo = '';
                                    if (type === 'athletes'){
                                        var s = (r.sukan||'').toString();
                                        var a = (r.acara||'').toString();
                                        sukan_combo = s;
                                        if (a !== '') sukan_combo = sukan_combo !== '' ? (sukan_combo + ' (' + a + ')') : a;
                                    }
                                    html += '<div class="page">' +
                                        '<img class="bg-img" src="'+img+'" alt="bg">' +
                                        '<div class="cert-name">'+(name.replace(/</g,'&lt;'))+'</div>' +
                                        '<div class="cert-sport">'+(sukan_combo.replace(/</g,'&lt;'))+'</div>' +
                                    '</div>';
                                });
                                html += '<script> (function(){ if(window.top===window.self){ setTimeout(function(){ window.print(); },120); } })();<\/script></body></html>';
                                return html;
                            }

                            if (printAllPengurus) printAllPengurus.addEventListener('click', function(){
                                if (!lastRows.pengurus || lastRows.pengurus.length===0){ alert('Tiada pengurus untuk dicetak.'); return; }
                                var html = buildMultiHtml(lastRows.pengurus, 'pengurus');
                                printDirectHtml(html);
                            });

                            if (printAllJurulatih) printAllJurulatih.addEventListener('click', function(){
                                if (!lastRows.jurulatih || lastRows.jurulatih.length===0){ alert('Tiada jurulatih untuk dicetak.'); return; }
                                var html = buildMultiHtml(lastRows.jurulatih, 'jurulatih');
                                printDirectHtml(html);
                            });

                            if (printAllAtlet) printAllAtlet.addEventListener('click', function(){
                                if (!lastRows.athletes || lastRows.athletes.length===0){ alert('Tiada atlet untuk dicetak.'); return; }
                                var html = buildMultiHtml(lastRows.athletes, 'athletes');
                                printDirectHtml(html);
                            });
                        }catch(e){}
                        btn.addEventListener('click', function(){
                            var kod = (document.getElementById('selectKont') && document.getElementById('selectKont').value) ? document.getElementById('selectKont').value : '';
                            if (!kod) { status.textContent = 'Sila pilih kontinjen dahulu.'; return; }
                            var url = window.location.pathname + '?ajax=athletes&kod=' + encodeURIComponent(kod);
                            status.textContent = '';
                            btn.disabled = true;
                            showLoader();
                            console.log('Fetching athletes from', url);
                            // fetch athletes and managers concurrently
                            var urlManagers = <?php echo json_encode(url("pages/ringkasan.php")); ?> + '?ajax=managers&kod=' + encodeURIComponent(kod);
                            fetch(url, { credentials: 'same-origin' })
                                .then(function(r){
                                    if (!r.ok) throw new Error('HTTP ' + r.status);
                                    return r.text();
                                })
                                .then(function(text){
                                    try {
                                        var j = JSON.parse(text);
                                        console.log('Athletes JSON response', j);
                                        var athletesJson = j && j.ok ? (j.rows || []) : [];
                                        // fetch managers from ringkasan
                                        return fetch(urlManagers, { credentials: 'same-origin' }).then(function(r2){ if(!r2.ok) throw new Error('HTTP ' + r2.status); return r2.text(); }).then(function(text2){
                                            try {
                                                var mj = JSON.parse(text2);
                                                var mgrRows = mj && mj.ok ? (mj.rows || []) : [];
                                                return { athletes: athletesJson, managers: mgrRows };
                                            } catch(err2) {
                                                console.error('Managers endpoint returned non-JSON:', text2);
                                                throw new Error('Managers endpoint returned non-JSON');
                                            }
                                        });
                                    } catch(err) {
                                        console.error('Athletes endpoint returned non-JSON:', text);
                                        throw new Error('Athletes endpoint returned non-JSON');
                                    }
                                }).then(function(all){
                                    // process managers into pengurus and jurulatih
                                    // Build deduplicated pengurus/jurulatih lists (dedupe by name only)
                                    var pengurusMap = Object.create(null);
                                    var jurulatihMap = Object.create(null);
                                    (all.managers || []).forEach(function(r){
                                        // each r may contain pengurus and jurulatih strings like: "Name (0123456789) email@example.com"
                                        if (r.pengurus) {
                                            r.pengurus.split(' ||| ').forEach(function(praw){
                                                var p = (praw||'').trim(); if(!p) return;
                                                var m = p.match(/\(([^)]*)\)/);
                                                var tel = m ? m[1].trim() : '';
                                                // remove phone parentheses and trailing email
                                                var clean = p.replace(/\s*\([^)]*\)/g,'').replace(/\s+\S+@\S+$/,'').trim();
                                                var key = (clean || p).toLowerCase();
                                                if (!pengurusMap[key]) pengurusMap[key] = { nama: clean || p, tel: tel };
                                                else if (!pengurusMap[key].tel && tel) pengurusMap[key].tel = tel;
                                            });
                                        }
                                        if (r.jurulatih) {
                                            r.jurulatih.split(' ||| ').forEach(function(jraw){
                                                var j = (jraw||'').trim(); if(!j) return;
                                                var mj = j.match(/\(([^)]*)\)/);
                                                var jtTel = mj ? mj[1].trim() : '';
                                                var cleanj = j.replace(/\s*\([^)]*\)/g,'').replace(/\s+\S+@\S+$/,'').trim();
                                                var keyj = (cleanj || j).toLowerCase();
                                                if (!jurulatihMap[keyj]) jurulatihMap[keyj] = { nama: cleanj || j, tel: jtTel };
                                                else if (!jurulatihMap[keyj].tel && jtTel) jurulatihMap[keyj].tel = jtTel;
                                            });
                                        }
                                    });
                                    var pengurusList = Object.keys(pengurusMap).map(function(k){ return pengurusMap[k]; });
                                    var jurulatihList = Object.keys(jurulatihMap).map(function(k){ return jurulatihMap[k]; });

                                    // store lists and render paged views
                                    lastRows.pengurus = pengurusList;
                                    lastRows.jurulatih = jurulatihList;
                                    lastRows.athletes = (all.athletes || []);
                                    currentPengurusPage = 1; currentJurulatihPage = 1; currentAthletePage = 1;
                                    renderPengurusPage();
                                    renderJurulatihPage();
                                    renderAthletePage();
                                    wrap.style.display = '';
                                    hideLoader();
                                    btn.disabled = false;
                                }).catch(function(e){ console.error('Fetch error', e); status.textContent = 'Ralat mengambil data.'; btn.disabled = false; hideLoader(); alert('Gagal mendapatkan data atlet/pengurus: '+ (e && e.message ? e.message : 'Unknown')); });
                        });

                        
                    })();
                </script>
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
    var templateUrl = <?php echo json_encode($img_url_versioned); ?>;
    function buildCertHtml(name, sukanCombined, templateOverride){
        var tUrl = templateOverride || templateUrl;
        var html = '<!doctype html><html><head><meta charset="utf-8"><title>Sijil Penyertaan</title>' +
            '<style>html,body{height:100%;margin:0;padding:0}body{font-family:Arial,Helvetica,sans-serif}.page{position:relative;width:210mm;height:297mm;overflow:hidden}.bg-img{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover;z-index:0}.cert-name{position:absolute;left:50%;top:38%;transform:translate(-50%,-50%);font-size:20px;font-weight:700;color:#000;text-align:center;padding:0 30px;line-height:1.05;z-index:1}.cert-sport{position:absolute;left:50%;top:48%;transform:translateX(-50%);font-size:20px;color:#000;text-align:center;padding:0 30px;z-index:1}@media print{.bg-img{display:block}}</style></head><body>'+
            '<div class="page">'+
                '<img class="bg-img" src="'+tUrl+'" alt="bg">'+
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
