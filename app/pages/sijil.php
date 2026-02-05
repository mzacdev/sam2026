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

// DEV: enable error display to help diagnose blank-page issues during testing
@ini_set('display_errors', 1);
@ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
// Default to UPNM kontinjen unless user explicitly chooses another
if ($kod === '') {
    $kod = 'UPNM';
}

// ensure template image URL variable exists to avoid PHP notices
    if (!isset($img_url_versioned) || !$img_url_versioned) {
    $relPath = '/assets/img/sijil/sijil_atlet.jpeg';
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

if ($ajax === 'penyelaras') {
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
        $sql = "SELECT id, TRIM(nama_pegawai_untuk_dihubungi) AS nama, TRIM(emel) AS emel, TRIM(no_telefon) AS telefon
            FROM table_kontinjen
            WHERE UPPER(COALESCE(kod_universiti,'')) = :kod_val
              AND deleted_at IS NULL
            ORDER BY nama_pegawai_untuk_dihubungi";
        $st = $db->prepare($sql);
        $st->execute([':kod_val' => $k]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
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

// Provide a local AJAX endpoint for managers/coaches to avoid cross-page HTTP
// requests (which can return HTML Access Denied pages when RBAC blocks other
// pages). This uses the same DB helper above to return JSON.
if ($ajax === 'managers') {
    header('Content-Type: application/json; charset=utf-8');
    $out = ['ok' => false, 'rows' => [], 'elapsed_ms' => null, 'error' => null, 'count' => 0];
    $k = strtoupper(trim((string)($_GET['kod'] ?? '')));
    if ($k === '') {
        echo json_encode($out);
        exit;
    }
    $t0 = microtime(true);
    try {
        $rows = fetch_managers_from_ringkasan($k);
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

// AJAX endpoint for committee members (used by SIJIL JAWATANKUASA PELAKSANA tab)
if ($ajax === 'committee') {
    header('Content-Type: application/json; charset=utf-8');
    $out = ['ok' => false, 'rows' => [], 'elapsed_ms' => null, 'error' => null, 'count' => 0];
    $type = strtoupper(trim((string)($_GET['type'] ?? '')));
    if ($type === '') { echo json_encode($out); exit; }
    $t0 = microtime(true);
    try {
        $db = getDB();
        // Order differently depending on requested member_ref_type: STAF -> order by role_id then name; others -> order by name
        if (strtoupper($type) === 'STAF') {
            $sql = "SELECT cm.id AS id, TRIM(cm.member_name) AS member_name, TRIM(cm.member_email) AS member_email, COALESCE(cr.role_name, '') AS role_name, cm.member_ref_type, cm.role_id FROM committee_members cm LEFT JOIN committee_roles cr ON cr.id = cm.role_id WHERE cm.deleted_at IS NULL AND (cr.deleted_at IS NULL OR cr.deleted_at IS NULL) AND UPPER(COALESCE(cm.member_ref_type,'')) = :ref_type ORDER BY cm.role_id, cm.member_name";
        } else {
            $sql = "SELECT cm.id AS id, TRIM(cm.member_name) AS member_name, TRIM(cm.member_email) AS member_email, COALESCE(cr.role_name, '') AS role_name, cm.member_ref_type, cm.role_id FROM committee_members cm LEFT JOIN committee_roles cr ON cr.id = cm.role_id WHERE cm.deleted_at IS NULL AND (cr.deleted_at IS NULL OR cr.deleted_at IS NULL) AND UPPER(COALESCE(cm.member_ref_type,'')) = :ref_type ORDER BY cm.member_name";
        }
        $st = $db->prepare($sql);
        $st->execute([':ref_type' => $type]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $out['ok'] = true; $out['rows'] = $rows; $out['count'] = count($rows);
    } catch (Exception $e) {
        $out['ok'] = false; $out['error'] = $e->getMessage();
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
        // athletes -> use new athlete template
        $img_rel = '/assets/img/sijil/sijil_atlet.jpeg';
    }
    // Build absolute URL including BASE_URL so it works in subfolder deployments
    $absTemplateUrl = $scheme . '://' . $host . url(ltrim($img_rel, '/'));

    $multiHtml = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sijil Penyertaan - Semua</title>' .
        '<style>@page{size:A4;margin:0}html,body{height:100%;margin:0;padding:0}body{background:#fff;font-family:Arial,Helvetica,sans-serif}.page{position:relative;width:210mm;height:297mm;overflow:hidden}.bg-img{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover;z-index:0}.cert-name{position:absolute;left:50%;top:38%;transform:translate(-50%,-50%);width:78%;text-align:center;font-weight:700;color:#000;line-height:1.05;z-index:1;font-size:20px}.cert-sport{position:absolute;left:50%;top:48%;transform:translateX(-50%);width:78%;text-align:center;color:#000;font-weight:700;z-index:1;font-size:20px}.page,.bg-img{-webkit-print-color-adjust:exact;print-color-adjust:exact}</style></head><body>';

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
            // uppercase sport/event for printed certificates
            $sukan_combo = mb_strtoupper($sukan_combo, 'UTF-8');
        } else {
            // For non-athletes, show fixed role label in the same position
            if ($type === 'pengurus') {
                $sukan_combo = mb_strtoupper('PENGURUS', 'UTF-8');
            } else if ($type === 'jurulatih') {
                $sukan_combo = mb_strtoupper('JURULATIH', 'UTF-8');
            } else if ($type === 'penyelaras') {
                $sukan_combo = mb_strtoupper('KETUA KONTINJEN', 'UTF-8');
            } else {
                $sukan_combo = '';
            }
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
    // uppercase sport/event for printed certificate
    $sukan_combo = mb_strtoupper($sukan_combo, 'UTF-8');

    // Prepare HTML for certificate. For PDF generation we need absolute URLs.
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Use the athlete template explicitly to avoid mismatches when $img_url_versioned
    // may point elsewhere. Build a versioned absolute URL for the athlete background.
    $athRel = '/assets/img/sijil/sijil_atlet.jpeg';
    $athFull = realpath(__DIR__ . '/..') . $athRel;
    $athVer = @file_exists($athFull) ? @filemtime($athFull) : time();
    // include BASE_URL prefix so it resolves correctly in production subfolders
    $absTemplateUrl = $scheme . '://' . $host . url('assets/img/sijil/sijil_atlet.jpeg') . '?v=' . (int)$athVer;

    $certHtml = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sijil Penyertaan</title>' .
        '<style>@page{size:A4;margin:0}html,body{height:100%;margin:0;padding:0}body{background:#fff;font-family:Arial,Helvetica,sans-serif}.page{position:relative;width:210mm;height:297mm;overflow:hidden}.bg-img{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover;z-index:0}.cert-name{position:absolute;left:50%;top:38%;transform:translate(-50%,-50%);width:78%;text-align:center;font-weight:700;color:#000;line-height:1.05;z-index:1;font-size:20px}.cert-sport{position:absolute;left:50%;top:48%;transform:translateX(-50%);width:78%;text-align:center;color:#000;font-weight:700;z-index:1;font-size:20px}.page,.bg-img{-webkit-print-color-adjust:exact;print-color-adjust:exact}</style></head><body>' .
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
    // Instead of making an HTTP request (which may be blocked by RBAC/session),
    // query the database directly using the same SQL as pages/ringkasan.php ajax=managers.
    try {
        $db = getDB();
        $sql = "
            SELECT
                COALESCE(s.nama_sukan, '') AS acara,
                COALESCE(r.nama_pendek, r.nama_universiti, k.kod_universiti) AS kontinjen,
                TRIM(
                    COALESCE(
                        GROUP_CONCAT(DISTINCT CONCAT(pp.nama, IFNULL(CONCAT(' (', pp.no_telefon, ')'), ''), IF(pp.emel IS NOT NULL AND pp.emel <> '', CONCAT(' ', pp.emel), '')) SEPARATOR ' ||| '),
                        ''
                    )
                ) AS pengurus,
                TRIM(
                    COALESCE(
                        GROUP_CONCAT(DISTINCT CONCAT(j.nama, IFNULL(CONCAT(' (', j.no_telefon, ')'), ''), IF(j.emel IS NOT NULL AND j.emel <> '', CONCAT(' ', j.emel), '')) SEPARATOR ' ||| '),
                        ''
                    )
                ) AS jurulatih
            FROM table_pasukan p
            LEFT JOIN table_kontinjen k ON k.id = p.kontinjen_id
            LEFT JOIN table_ref_universiti r ON r.kod_universiti = k.kod_universiti AND r.status = 1
            LEFT JOIN table_sukan s ON s.id = p.sukan_id
            LEFT JOIN table_pasukan_pengurus pp ON pp.pasukan_id = p.id AND pp.deleted_at IS NULL
            LEFT JOIN table_pasukan_jurulatih j ON j.pasukan_id = p.id AND j.deleted_at IS NULL
            WHERE (:kod_empty = '' OR UPPER(COALESCE(k.kod_universiti, '')) = :kod_val)
              AND p.deleted_at IS NULL
            GROUP BY p.id, s.nama_sukan, k.kod_universiti, r.nama_pendek
            ORDER BY s.nama_sukan, p.id
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([':kod_empty' => $kod, ':kod_val' => $kod]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return $rows;
    } catch (Exception $e) {
        return [];
    }
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

    <!-- filter moved into SIJIL KONTINJEN tab -->

    <div class="row">
            <div class="col-12">
                <div id="tabsWrap">
                        <ul class="nav nav-tabs" id="sijilTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-kontinjen" data-bs-toggle="tab" data-bs-target="#pane-kontinjen" type="button" role="tab">SIJIL KONTINJEN</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-jawatankuasa" data-bs-toggle="tab" data-bs-target="#pane-jawatankuasa" type="button" role="tab">SIJIL JAWATANKUASA PELAKSANA</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-sukarelawan" data-bs-toggle="tab" data-bs-target="#pane-sukarelawan" type="button" role="tab">SIJIL SUKARELAWAN</button>
                            </li>
                        </ul>
                        <div class="tab-content border border-top-0 p-3" id="sijilTabContent">
                            <div class="tab-pane fade show active" id="pane-kontinjen" role="tabpanel">
                                <!-- Filter for kontinjen (moved here) -->
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
                                </div>
                                <!-- Existing kontingen content (penyelaras/pengurus/jurulatih/atlet) -->
                                <div id="kontinjen-inner">
                                    <ul class="nav nav-pills mb-3" id="kontinjenSubNav" role="tablist" style="display:none;">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" data-sub="0" type="button">Ketua Kontinjen</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" data-sub="1" type="button">Pengurus</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" data-sub="2" type="button">Jurulatih</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" data-sub="3" type="button">Atlet</button>
                                        </li>
                                    </ul>
                                    <div id="kontinjenSubtabs" style="display:none;">
                                    <div class="tab-pane-inner">
                                        <div class="d-flex mb-2">
                                            <div class="me-auto"></div>
                                            <button type="button" id="printAllPenyelaras" class="btn btn-sm btn-primary">Cetak Semua</button>
                                        </div>
                                        <div class="table-responsive"><table class="table table-sm table-hover"><thead class="table-light"><tr><th style="width:5%" class="text-center">No</th><th style="width:55%">Nama Ketua Kontinjen</th><th style="width:20%">Email</th><th style="width:10%" class="text-center">No Telefon</th><th style="width:10%" class="text-center">Tindakan</th></tr></thead><tbody id="penyelarasBody"></tbody></table></div>
                                        <div class="d-flex justify-content-end align-items-center mt-2">
                                            <button type="button" id="penyelarasPrev" class="btn btn-sm btn-outline-secondary me-2">Prev</button>
                                            <span id="penyelarasPageInfo" class="me-2">Page 1/1</span>
                                            <button type="button" id="penyelarasNext" class="btn btn-sm btn-outline-secondary">Next</button>
                                        </div>
                                    </div>

                                    <div class="tab-pane-inner mt-4">
                                        <div class="d-flex mb-2">
                                            <div class="me-auto"></div>
                                            <button type="button" id="printAllPengurus" class="btn btn-sm btn-primary">Cetak Semua</button>
                                        </div>
                                        <div class="table-responsive"><table class="table table-sm table-hover"><thead class="table-light"><tr><th style="width:5%" class="text-center">No</th><th style="width:75%">Nama Pengurus</th><th style="width:10%">No Telefon</th><th style="width:10%" class="text-center">Tindakan</th></tr></thead><tbody id="pengurusBody"></tbody></table></div>
                                        <div class="d-flex justify-content-end align-items-center mt-2">
                                            <button type="button" id="pengurusPrev" class="btn btn-sm btn-outline-secondary me-2">Prev</button>
                                            <span id="pengurusPageInfo" class="me-2">Page 1/1</span>
                                            <button type="button" id="pengurusNext" class="btn btn-sm btn-outline-secondary">Next</button>
                                        </div>
                                    </div>

                                    <div class="tab-pane-inner mt-4">
                                        <div class="d-flex mb-2">
                                            <div class="me-auto"></div>
                                            <button type="button" id="printAllJurulatih" class="btn btn-sm btn-primary">Cetak Semua</button>
                                        </div>
                                        <div class="table-responsive"><table class="table table-sm table-hover"><thead class="table-light"><tr><th style="width:5%" class="text-center">No</th><th style="width:75%">Nama Jurulatih</th><th style="width:10%">No Telefon</th><th style="width:10%" class="text-center">Tindakan</th></tr></thead><tbody id="jurulatihBody"></tbody></table></div>
                                        <div class="d-flex justify-content-end align-items-center mt-2">
                                            <button type="button" id="jurulatihPrev" class="btn btn-sm btn-outline-secondary me-2">Prev</button>
                                            <span id="jurulatihPageInfo" class="me-2">Page 1/1</span>
                                            <button type="button" id="jurulatihNext" class="btn btn-sm btn-outline-secondary">Next</button>
                                        </div>
                                    </div>

                                    <div class="tab-pane-inner mt-4">
                                        <div class="d-flex mb-2">
                                            <div class="me-auto"></div>
                                            <button type="button" id="printAllAtlet" class="btn btn-sm btn-primary">Cetak Semua</button>
                                        </div>
                                        <div class="table-responsive"><table class="table table-sm table-hover align-middle"><thead class="table-light"><tr><th style="width:5%" class="text-center">No</th><th style="width:55%">Nama Atlet</th><th style="width:30%">Sukan / Acara</th><th style="width:10%" class="text-center">Tindakan</th></tr></thead><tbody id="athleteBody"></tbody></table></div>
                                        <div class="d-flex justify-content-end align-items-center mt-2">
                                            <button type="button" id="athletePrev" class="btn btn-sm btn-outline-secondary me-2">Prev</button>
                                            <span id="athletePageInfo" class="me-2">Page 1/1</span>
                                            <button type="button" id="athleteNext" class="btn btn-sm btn-outline-secondary">Next</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pane-jawatankuasa" role="tabpanel">
                                <div class="card mb-3">
                                    <div class="card-body d-flex gap-3 align-items-end">
                                        <div>
                                            <label class="form-label small mb-1">Jenis Ahli</label>
                                            <div class="d-flex align-items-center">
                                                <select id="committeeType" class="form-select form-select-sm" style="min-width:220px;max-width:40%">
                                                    <option value="">-- Pilih Jenis --</option>
                                                    <option value="STAF">STAF</option>
                                                    <option value="PELAJAR">PELAJAR</option>
                                                </select>
                                                <button type="button" id="btnLoadCommittee" class="btn btn-sm btn-secondary ms-2" style="min-width:120px">Papar Data</button>
                                                <span id="committeeLoadStatus" class="ms-2 text-muted"></span>
                                                <div id="committeeLoader" class="ms-auto align-self-center" style="display:none;">
                                                    <div class="spinner-border text-primary" role="status" style="width:1.6rem;height:1.6rem;vertical-align:middle;"><span class="visually-hidden">Memuat...</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="committeeWrap" style="display:none;">
                                    <div class="d-flex mb-2">
                                        <div class="me-auto"></div>
                                        <button type="button" id="printAllCommittee" class="btn btn-sm btn-primary">Cetak Semua</button>
                                    </div>
                                        <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead class="table-light"><tr>
                                                <th style="width:5%" class="text-center">No</th>
                                                <th style="width:40%">Nama Jawatankuasa Pelaksana</th>
                                                <th style="width:30%">Nama Jawatankuasa</th>
                                                <th style="width:15%">Email</th>
                                                <th style="width:10%" class="text-center">Tindakan</th>
                                            </tr></thead>
                                            <tbody id="committeeBody"></tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end align-items-center mt-2">
                                        <button type="button" id="committeePrev" class="btn btn-sm btn-outline-secondary me-2">Prev</button>
                                        <span id="committeePageInfo" class="me-2">Page 1/1</span>
                                        <button type="button" id="committeeNext" class="btn btn-sm btn-outline-secondary">Next</button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pane-sukarelawan" role="tabpanel">
                                <div class="card mb-3">
                                    <div class="card-body d-flex gap-3 align-items-end">
                                        <div>
                                            <label class="form-label small mb-1">Jenis Ahli</label>
                                            <div class="d-flex align-items-center">
                                                <select id="volunteerType" class="form-select form-select-sm" style="min-width:220px;max-width:40%">
                                                    <option value="">-- Pilih Jenis --</option>
                                                    <option value="STAF">STAF</option>
                                                    <option value="PELAJAR">PELAJAR</option>
                                                </select>
                                                <button type="button" id="btnLoadVolunteer" class="btn btn-sm btn-secondary ms-2" style="min-width:120px">Papar Data</button>
                                                <span id="volunteerLoadStatus" class="ms-2 text-muted"></span>
                                                <div id="volunteerLoader" class="ms-auto align-self-center" style="display:none;">
                                                    <div class="spinner-border text-primary" role="status" style="width:1.6rem;height:1.6rem;vertical-align:middle;"><span class="visually-hidden">Memuat...</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="volunteerWrap" style="display:none;">
                                    <div class="d-flex mb-2">
                                        <div class="me-auto"></div>
                                        <button type="button" id="printAllVolunteer" class="btn btn-sm btn-primary">Cetak Semua</button>
                                    </div>
                                        <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead class="table-light"><tr>
                                                <th style="width:5%" class="text-center">No</th>
                                                <th style="width:40%">Nama Jawatankuasa Sukarelawan</th>
                                                <th style="width:30%">Nama Jawatankuasa</th>
                                                <th style="width:15%">Email</th>
                                                <th style="width:10%" class="text-center">Tindakan</th>
                                            </tr></thead>
                                            <tbody id="volunteerBody"></tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end align-items-center mt-2">
                                        <button type="button" id="volunteerPrev" class="btn btn-sm btn-outline-secondary me-2">Prev</button>
                                        <span id="volunteerPageInfo" class="me-2">Page 1/1</span>
                                        <button type="button" id="volunteerNext" class="btn btn-sm btn-outline-secondary">Next</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <script>
                    (function(){
                                var btn = document.getElementById('btnLoadList');
                                var status = document.getElementById('loadStatus');
                                var wrap = document.getElementById('tabsWrap');
                                var kontSubNav = document.getElementById('kontinjenSubNav');
                                var kontSub = document.getElementById('kontinjenSubtabs');
                                var athleteBody = document.getElementById('athleteBody');
                                var pengurusBody = document.getElementById('pengurusBody');
                                var jurulatihBody = document.getElementById('jurulatihBody');
                                var penyelarasBody = document.getElementById('penyelarasBody');
                                // committee elements
                                var committeeType = document.getElementById('committeeType');
                                var btnLoadCommittee = document.getElementById('btnLoadCommittee');
                                var committeeLoadStatus = document.getElementById('committeeLoadStatus');
                                var committeeLoader = document.getElementById('committeeLoader');
                                var committeeWrap = document.getElementById('committeeWrap');
                                var committeeBody = document.getElementById('committeeBody');
                                var printAllCommittee = document.getElementById('printAllCommittee');
                                var committeePrev = document.getElementById('committeePrev');
                                var committeeNext = document.getElementById('committeeNext');
                                var committeePageInfo = document.getElementById('committeePageInfo');
                                // volunteer elements
                                var volunteerType = document.getElementById('volunteerType');
                                var btnLoadVolunteer = document.getElementById('btnLoadVolunteer');
                                var volunteerLoadStatus = document.getElementById('volunteerLoadStatus');
                                var volunteerLoader = document.getElementById('volunteerLoader');
                                var volunteerWrap = document.getElementById('volunteerWrap');
                                var volunteerBody = document.getElementById('volunteerBody');
                                var printAllVolunteer = document.getElementById('printAllVolunteer');
                                var volunteerPrev = document.getElementById('volunteerPrev');
                                var volunteerNext = document.getElementById('volunteerNext');
                                var volunteerPageInfo = document.getElementById('volunteerPageInfo');
                                var lastRows = { athletes: [], pengurus: [], jurulatih: [], penyelaras: [], committee: [], volunteer: [] };
                                var committeeLoaded = false;
                                var volunteerLoaded = false;
                                var loader = document.getElementById('tableLoader');
                                var printAllPengurus = document.getElementById('printAllPengurus');
                                var printAllJurulatih = document.getElementById('printAllJurulatih');
                                var printAllAtlet = document.getElementById('printAllAtlet');
                                var printAllPenyelaras = document.getElementById('printAllPenyelaras');
                                var countPengurus = document.getElementById('countPengurus');
                                var countJurulatih = document.getElementById('countJurulatih');
                                var countAtlet = document.getElementById('countAtlet');
                                var countPenyelaras = document.getElementById('countPenyelaras');
                                var lastRows = { athletes: [], pengurus: [], jurulatih: [], penyelaras: [] };

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
                                    '<style>@page{size:A4;margin:0}html,body{height:100%;margin:0;padding:0}body{background:#fff;font-family:Arial,Helvetica,sans-serif}.page{position:relative;width:210mm;height:297mm;overflow:hidden}.bg-img{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover;z-index:0}.cert-name{position:absolute;left:50%;top:38%;transform:translate(-50%,-50%);width:78%;text-align:center;font-weight:700;color:#000;line-height:1.05;z-index:1;font-size:20px}.cert-sport{position:absolute;left:50%;top:48%;transform:translateX(-50%);width:78%;text-align:center;color:#000;font-weight:700;z-index:1;font-size:20px}.page,.bg-img{-webkit-print-color-adjust:exact;print-color-adjust:exact}</style></head><body>' +
                                    '<div class="page">' +
                                        '<img class="bg-img" src="'+templateUrl+'" alt="background">' +
                                        '<div class="cert-name">'+(name||'')+'</div>' +
                                        '<div class="cert-sport">' + ((sukanCombined||'').toString().toUpperCase()) + '</div>' +
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
                        var currentPenyelarasPage = 1, currentPengurusPage = 1, currentJurulatihPage = 1, currentAthletePage = 1;

                        // Render cell HTML with empty-value badge
                        function escHtml(s){ return (s===null||s===undefined)?'':String(s).replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
                        function cellHtml(val, center){
                            var v = (val===null||val===undefined)?'' : String(val).trim();
                            var cls = center ? 'text-center' : 'text-truncate';
                            if (v === '') return '<td class="'+cls+'"><span class="no-data-badge">Tiada</span></td>'; 
                            return '<td class="'+cls+'" title="'+escHtml(v)+'">'+escHtml(v)+'</td>';
                        }

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
                                    (function(){ return cellHtml(p.nama, false); })() +
                                    (function(){ return cellHtml(p.tel || '', false); })() +
                                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary do-print-pengurus">Cetak</button></td>';
                                pengurusBody.appendChild(tr);
                                tr.querySelector('.do-print-pengurus').addEventListener('click', function(){ printDirectHtml(buildCertHtml(p.nama, 'PENGURUS', <?php echo json_encode(url('assets/img/sijil/sijil_pengurus.jpeg')); ?>)); });
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
                                    (function(){ return cellHtml(p.nama, false); })() +
                                    (function(){ return cellHtml(p.tel || '', false); })() +
                                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary do-print-jurulatih">Cetak</button></td>';
                                jurulatihBody.appendChild(tr);
                                tr.querySelector('.do-print-jurulatih').addEventListener('click', function(){ printDirectHtml(buildCertHtml(p.nama, 'JURULATIH', <?php echo json_encode(url('assets/img/sijil/sijil_jurulatih.jpeg')); ?>)); });
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
                                    (function(){ return cellHtml(name, false); })() +
                                    (function(){ return cellHtml(info, false); })() +
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

                        function renderPenyelarasPage(){
                            var list = lastRows.penyelaras || [];
                            var total = list.length;
                            var pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
                            if (currentPenyelarasPage > pages) currentPenyelarasPage = pages;
                            var start = (currentPenyelarasPage - 1) * PAGE_SIZE;
                            var slice = list.slice(start, start + PAGE_SIZE);
                            penyelarasBody.innerHTML = '';
                            slice.forEach(function(r, idx){
                                var nIdx = start + idx + 1;
                                var tr = document.createElement('tr');
                                var nameSafe = (r.nama || '').replace(/</g,'&lt;');
                                var emailSafe = (r.emel || '').replace(/</g,'&lt;');
                                var telSafe = (r.telefon || '').replace(/</g,'&lt;');
                                tr.innerHTML = '<td class="text-center">'+nIdx+'</td>'+
                                    (function(){ return cellHtml(nameSafe, false); })() +
                                    (function(){ return cellHtml(emailSafe, false); })() +
                                    (function(){ return cellHtml(telSafe, true); })() +
                                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary do-print-penyelaras">Cetak</button></td>';
                                penyelarasBody.appendChild(tr);
                                var btn = tr.querySelector('.do-print-penyelaras');
                                if (btn) btn.addEventListener('click', function(){ printDirectHtml(buildCertHtml(r.nama || '', 'KETUA KONTINJEN', <?php echo json_encode(url('assets/img/sijil/sijil_penyelaras.jpeg')); ?>)); });
                            });
                            try{ document.getElementById('penyelarasPageInfo').textContent = 'Page ' + currentPenyelarasPage + '/' + pages; }catch(e){}
                            try{ document.getElementById('penyelarasPrev').disabled = currentPenyelarasPage <= 1; }catch(e){}
                            try{ document.getElementById('penyelarasNext').disabled = currentPenyelarasPage >= pages; }catch(e){}
                            if (countPenyelaras) countPenyelaras.textContent = total;
                            if (printAllPenyelaras) printAllPenyelaras.disabled = total === 0;
                        }

                        // Committee render (single table)
                        var currentCommitteePage = 1;
                        function renderCommitteePage(){
                            var list = lastRows.committee || [];
                            var total = list.length;
                            var pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
                            if (currentCommitteePage > pages) currentCommitteePage = pages;
                            var start = (currentCommitteePage - 1) * PAGE_SIZE;
                            var slice = list.slice(start, start + PAGE_SIZE);
                            if (!committeeBody) return;
                            committeeBody.innerHTML = '';
                            slice.forEach(function(r, idx){
                                var nIdx = start + idx + 1;
                                var tr = document.createElement('tr');
                                var name = (r.member_name||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                                var role = (r.role_name||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                                var email = (r.member_email||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                                tr.innerHTML = '<td class="text-center">'+nIdx+'</td>'+
                                    (function(){ return cellHtml(name, false); })() +
                                    (function(){ return cellHtml(role, false); })() +
                                    (function(){ return cellHtml(email, false); })() +
                                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary do-print-committee">Cetak</button></td>';
                                committeeBody.appendChild(tr);
                                var btn = tr.querySelector('.do-print-committee');
                                if (btn) btn.addEventListener('click', function(){ printDirectHtml(buildCertHtml(r.member_name || '', r.role_name || '', <?php echo json_encode(url('assets/img/sijil/sijil_penyelaras.jpeg')); ?>)); });
                            });
                            try{ if (committeePageInfo) committeePageInfo.textContent = 'Page ' + currentCommitteePage + '/' + pages; }catch(e){}
                            try{ if (committeePrev) committeePrev.disabled = currentCommitteePage <= 1; }catch(e){}
                            try{ if (committeeNext) committeeNext.disabled = currentCommitteePage >= pages; }catch(e){}
                            if (printAllCommittee) printAllCommittee.disabled = total === 0;
                        }

                        // Volunteer render (single table) - same as committee but separate storage
                        var currentVolunteerPage = 1;
                        function renderVolunteerPage(){
                            var list = lastRows.volunteer || [];
                            var total = list.length;
                            var pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
                            if (currentVolunteerPage > pages) currentVolunteerPage = pages;
                            var start = (currentVolunteerPage - 1) * PAGE_SIZE;
                            var slice = list.slice(start, start + PAGE_SIZE);
                            if (!volunteerBody) return;
                            volunteerBody.innerHTML = '';
                            slice.forEach(function(r, idx){
                                var nIdx = start + idx + 1;
                                var tr = document.createElement('tr');
                                var name = (r.member_name||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                                var role = (r.role_name||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                                var email = (r.member_email||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                                tr.innerHTML = '<td class="text-center">'+nIdx+'</td>'+
                                    (function(){ return cellHtml(name, false); })() +
                                    (function(){ return cellHtml(role, false); })() +
                                    (function(){ return cellHtml(email, false); })() +
                                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary do-print-volunteer">Cetak</button></td>';
                                volunteerBody.appendChild(tr);
                                var btn = tr.querySelector('.do-print-volunteer');
                                if (btn) btn.addEventListener('click', function(){ printDirectHtml(buildCertHtml(r.member_name || '', r.role_name || '', <?php echo json_encode(url('assets/img/sijil/sijil_penyelaras.jpeg')); ?>)); });
                            });
                            try{ if (volunteerPageInfo) volunteerPageInfo.textContent = 'Page ' + currentVolunteerPage + '/' + pages; }catch(e){}
                            try{ if (volunteerPrev) volunteerPrev.disabled = currentVolunteerPage <= 1; }catch(e){}
                            try{ if (volunteerNext) volunteerNext.disabled = currentVolunteerPage >= pages; }catch(e){}
                            if (printAllVolunteer) printAllVolunteer.disabled = total === 0;
                        }

                        // wire prev/next buttons
                        try{
                            document.getElementById('penyelarasPrev').addEventListener('click', function(){ if (currentPenyelarasPage>1){ currentPenyelarasPage--; renderPenyelarasPage(); } });
                            document.getElementById('penyelarasNext').addEventListener('click', function(){ currentPenyelarasPage++; renderPenyelarasPage(); });
                            document.getElementById('pengurusPrev').addEventListener('click', function(){ if (currentPengurusPage>1){ currentPengurusPage--; renderPengurusPage(); } });
                            document.getElementById('pengurusNext').addEventListener('click', function(){ currentPengurusPage++; renderPengurusPage(); });
                            document.getElementById('jurulatihPrev').addEventListener('click', function(){ if (currentJurulatihPage>1){ currentJurulatihPage--; renderJurulatihPage(); } });
                            document.getElementById('jurulatihNext').addEventListener('click', function(){ currentJurulatihPage++; renderJurulatihPage(); });
                            document.getElementById('athletePrev').addEventListener('click', function(){ if (currentAthletePage>1){ currentAthletePage--; renderAthletePage(); } });
                            document.getElementById('athleteNext').addEventListener('click', function(){ currentAthletePage++; renderAthletePage(); });
                            // committee paging
                            if (committeePrev) committeePrev.addEventListener('click', function(){ if (currentCommitteePage>1){ currentCommitteePage--; renderCommitteePage(); } });
                            if (committeeNext) committeeNext.addEventListener('click', function(){ currentCommitteePage++; renderCommitteePage(); });
                            if (volunteerPrev) volunteerPrev.addEventListener('click', function(){ if (currentVolunteerPage>1){ currentVolunteerPage--; renderVolunteerPage(); } });
                            if (volunteerNext) volunteerNext.addEventListener('click', function(){ currentVolunteerPage++; renderVolunteerPage(); });
                        }catch(e){}

                        // subtabs: show/hide the .tab-pane-inner sections inside #kontinjenSubtabs
                        function showSubIndex(i){
                            try{
                                var container = document.getElementById('kontinjenSubtabs');
                                if(!container) return;
                                var panes = container.querySelectorAll('.tab-pane-inner');
                                panes.forEach(function(p, idx){ p.style.display = (idx === i ? '' : 'none'); });
                                if (kontSubNav){
                                    var links = kontSubNav.querySelectorAll('.nav-link');
                                    links.forEach(function(l, idx){ if(idx===i) l.classList.add('active'); else l.classList.remove('active'); });
                                }
                            }catch(e){ console.error(e); }
                        }
                        if (kontSubNav){
                            kontSubNav.querySelectorAll('.nav-link').forEach(function(btn, idx){
                                btn.addEventListener('click', function(ev){ ev.preventDefault(); showSubIndex(idx); });
                            });
                        }

                        // Hide kontinjen subnav/subtabs when switching to other main tabs
                        try{
                            var sijilTabsEl = document.getElementById('sijilTabs');
                            if (sijilTabsEl){
                                sijilTabsEl.addEventListener('shown.bs.tab', function(e){
                                    try{
                                        var target = e.target || e.srcElement;
                                        var sel = '';
                                        if (target) sel = target.getAttribute('data-bs-target') || target.dataset.bsTarget || '';
                                        if (sel === '#pane-kontinjen'){
                                            if (kontSubNav) kontSubNav.style.display = '';
                                            if (kontSub) kontSub.style.display = '';
                                            showSubIndex(0);
                                        } else {
                                            if (kontSubNav) kontSubNav.style.display = 'none';
                                            if (kontSub) kontSub.style.display = 'none';
                                        }
                                        // when user opens jawatankuasa tab, lock to STAF and auto-load once
                                        if (sel === '#pane-jawatankuasa'){
                                            try{
                                                if (committeeType) { committeeType.value = 'STAF'; committeeType.disabled = true; }
                                                if (btnLoadCommittee && !committeeLoaded) { btnLoadCommittee.click(); }
                                            }catch(ex2){ console.error(ex2); }
                                        }
                                        // when user opens sukarelawan tab, lock to PELAJAR and auto-load once
                                        if (sel === '#pane-sukarelawan'){
                                            try{
                                                if (volunteerType) { volunteerType.value = 'PELAJAR'; volunteerType.disabled = true; }
                                                if (btnLoadVolunteer && !volunteerLoaded) { btnLoadVolunteer.click(); }
                                            }catch(ex3){ console.error(ex3); }
                                        }
                                    }catch(er){ console.error(er); }
                                });
                            }
                        }catch(e){ console.error(e); }

                        // wire print all buttons to client-side print (build multi-page HTML and print via hidden iframe)
                        try{
                            var tmplPengurus = <?php echo json_encode(url('assets/img/sijil/sijil_pengurus.jpeg')); ?>;
                            var tmplJurulatih = <?php echo json_encode(url('assets/img/sijil/sijil_jurulatih.jpeg')); ?>;
                            var tmplPenyelaras = <?php echo json_encode(url('assets/img/sijil/sijil_penyelaras.jpeg')); ?>;
                            var tmplAtlet = <?php
                                $athPath = __DIR__ . '/../assets/img/sijil/sijil_atlet.jpeg';
                                $ver = @file_exists($athPath) ? @filemtime($athPath) : time();
                                echo json_encode(url('assets/img/sijil/sijil_atlet.jpeg') . '?v=' . (int)$ver);
                            ?>;

                            function buildMultiHtml(rows, type){
                                var img = type === 'pengurus' ? tmplPengurus : (type === 'jurulatih' ? tmplJurulatih : (type === 'penyelaras' ? tmplPenyelaras : tmplAtlet));
                                var html = '<!doctype html><html><head><meta charset="utf-8"><title>Cetak Semua Sijil</title>' +
                                    '<style>@page{size:A4;margin:0}html,body{height:100%;margin:0;padding:0}body{background:#fff;font-family:Arial,Helvetica,sans-serif}.page{position:relative;width:210mm;height:297mm;overflow:hidden}.bg-img{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover;z-index:0}.cert-name{position:absolute;left:50%;top:38%;transform:translate(-50%,-50%);width:78%;text-align:center;font-weight:700;color:#000;line-height:1.05;z-index:1;font-size:20px}.cert-sport{position:absolute;left:50%;top:48%;transform:translateX(-50%);width:78%;text-align:center;color:#000;font-weight:700;z-index:1;font-size:20px}.page,.bg-img{-webkit-print-color-adjust:exact;print-color-adjust:exact}</style></head><body>';
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
                                        // uppercase for athletes
                                        sukan_combo = (sukan_combo||'').toString().toUpperCase();
                                    } else {
                                        // fixed labels for other types
                                        if (type === 'pengurus') sukan_combo = 'PENGURUS';
                                        else if (type === 'jurulatih') sukan_combo = 'JURULATIH';
                                        else if (type === 'penyelaras') sukan_combo = 'KETUA KONTINJEN';
                                        else sukan_combo = '';
                                    }
                                    html += '<div class="page">' +
                                        '<img class="bg-img" src="'+img+'" alt="bg">' +
                                        '<div class="cert-name">'+(name.replace(/</g,'&lt;'))+'</div>' +
                                        '<div class="cert-sport">'+((sukan_combo||'').toString().toUpperCase().replace(/</g,'&lt;'))+'</div>' +
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
                            if (printAllPenyelaras) printAllPenyelaras.addEventListener('click', function(){
                                if (!lastRows.penyelaras || lastRows.penyelaras.length===0){ alert('Tiada penyelaras untuk dicetak.'); return; }
                                var html = buildMultiHtml(lastRows.penyelaras, 'penyelaras');
                                printDirectHtml(html);
                            });
                            if (printAllCommittee) printAllCommittee.addEventListener('click', function(){
                                if (!lastRows.committee || lastRows.committee.length===0){ alert('Tiada rekod untuk dicetak.'); return; }
                                var rows = lastRows.committee.map(function(r){ return { nama: r.member_name || '', sukan: r.role_name || '', acara: '' }; });
                                var html = buildMultiHtml(rows, 'penyelaras');
                                printDirectHtml(html);
                            });
                            if (printAllVolunteer) printAllVolunteer.addEventListener('click', function(){
                                if (!lastRows.volunteer || lastRows.volunteer.length===0){ alert('Tiada rekod untuk dicetak.'); return; }
                                var rows = lastRows.volunteer.map(function(r){ return { nama: r.member_name || '', sukan: r.role_name || '', acara: '' }; });
                                var html = buildMultiHtml(rows, 'penyelaras');
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
                            var urlManagers = window.location.pathname + '?ajax=managers&kod=' + encodeURIComponent(kod);
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
                                        // fetch managers and penyelaras concurrently
                                        var urlPenyelaras = window.location.pathname + '?ajax=penyelaras&kod=' + encodeURIComponent(kod);
                                        return Promise.all([
                                            fetch(urlManagers, { credentials: 'same-origin' }).then(function(r2){ if(!r2.ok) throw new Error('HTTP ' + r2.status); return r2.text(); }),
                                            fetch(urlPenyelaras, { credentials: 'same-origin' }).then(function(r3){ if(!r3.ok) throw new Error('HTTP ' + r3.status); return r3.text(); })
                                        ]).then(function(texts){
                                            try {
                                                var mj = JSON.parse(texts[0]);
                                                var mgrRows = mj && mj.ok ? (mj.rows || []) : [];
                                            } catch(err2) {
                                                console.error('Managers endpoint returned non-JSON:', texts[0]);
                                                throw new Error('Managers endpoint returned non-JSON');
                                            }
                                            try {
                                                var pj = JSON.parse(texts[1]);
                                                var penyRows = pj && pj.ok ? (pj.rows || []) : [];
                                            } catch(err3) {
                                                console.error('Penyelaras endpoint returned non-JSON:', texts[1]);
                                                throw new Error('Penyelaras endpoint returned non-JSON');
                                            }
                                            return { athletes: athletesJson, managers: mgrRows, penyelaras: penyRows };
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
                                    lastRows.penyelaras = (all.penyelaras || []);
                                    currentPenyelarasPage = 1; currentPengurusPage = 1; currentJurulatihPage = 1; currentAthletePage = 1;
                                    renderPenyelarasPage();
                                    renderPengurusPage();
                                    renderJurulatihPage();
                                    renderAthletePage();
                                    // update subtab labels with kod and counts
                                    try{
                                        function badgeHtml(n, cls){ return '<span class="badge ' + (cls||'bg-secondary') + ' ms-2">' + (parseInt(n,10)||0) + '</span>'; }
                                        var kodLbl = (getSelectedKod() || '').toUpperCase();
                                        var nav = kontSubNav;
                                        if (nav){
                                            var links = nav.querySelectorAll('.nav-link');
                                            // Ketua Kontinjen
                                            if (links[0]) links[0].innerHTML = 'Ketua Kontinjen ' + (kodLbl ? ('['+kodLbl+']') : '') + badgeHtml(lastRows.penyelaras.length || 0, 'bg-info text-dark');
                                            // Pengurus
                                            if (links[1]) links[1].innerHTML = 'Pengurus Pasukan ' + (kodLbl ? ('['+kodLbl+']') : '') + badgeHtml(lastRows.pengurus.length || 0, 'bg-success');
                                            // Jurulatih
                                            if (links[2]) links[2].innerHTML = 'Jurulatih Acara Sukan ' + (kodLbl ? ('['+kodLbl+']') : '') + badgeHtml(lastRows.jurulatih.length || 0, 'bg-warning text-dark');
                                            // Atlet
                                            if (links[3]) links[3].innerHTML = 'Athlet Sukan ' + (kodLbl ? ('['+kodLbl+']') : '') + badgeHtml(lastRows.athletes.length || 0, 'bg-primary');
                                        }
                                        if (kontSub) kontSub.style.display = '';
                                        if (kontSubNav) kontSubNav.style.display = '';
                                        showSubIndex(0);
                                    }catch(e){ console.error(e); }
                                    hideLoader();
                                    btn.disabled = false;
                                    // mark kontinjen as loaded to avoid auto-triggering again
                                    try{ window.kontinjenLoaded = true; }catch(e){}
                                }).catch(function(e){ console.error('Fetch error', e); status.textContent = 'Ralat mengambil data.'; btn.disabled = false; hideLoader(); alert('Gagal mendapatkan data atlet/pengurus: '+ (e && e.message ? e.message : 'Unknown')); });
                        });

                        // Auto-load default kontinjen (UPNM) on page load if select present
                        try{
                            var selKont = document.getElementById('selectKont');
                            if (selKont && selKont.value && selKont.value.toUpperCase() === 'UPNM'){
                                // trigger click once after short delay so DOM settles
                                setTimeout(function(){ if (!window.kontinjenLoaded) btn.click(); }, 250);
                            }
                        }catch(e){ console.error(e); }

                                // Committee load handler for SIJIL JAWATANKUASA PELAKSANA tab
                                if (btnLoadCommittee){
                                    btnLoadCommittee.addEventListener('click', function(){
                                        var t = (committeeType && committeeType.value) ? committeeType.value : '';
                                        if (!t){ committeeLoadStatus.textContent = 'Sila pilih jenis ahli.'; return; }
                                        committeeLoadStatus.textContent = ''; btnLoadCommittee.disabled = true; if (committeeLoader) committeeLoader.style.display = 'inline-block';
                                        var url = window.location.pathname + '?ajax=committee&type=' + encodeURIComponent(t);
                                        fetch(url, { credentials: 'same-origin' }).then(function(r){ if(!r.ok) throw new Error('HTTP ' + r.status); return r.text(); }).then(function(text){
                                            try{
                                                var j = JSON.parse(text);
                                                if (!j || !j.ok){ throw new Error(j && j.error ? j.error : 'Empty response'); }
                                                lastRows.committee = j.rows || [];
                                                currentCommitteePage = 1;
                                                renderCommitteePage();
                                                if (committeeWrap) committeeWrap.style.display = '';
                                                committeeLoadStatus.textContent = '';
                                                committeeLoaded = true;
                                            } catch(err){ console.error('Committee parse error', err, text); committeeLoadStatus.textContent = 'Ralat memproses data.'; alert('Gagal mendapatkan data jawatankuasa.'); }
                                            btnLoadCommittee.disabled = false; if (committeeLoader) committeeLoader.style.display = 'none';
                                        }).catch(function(err){ console.error('Committee fetch error', err); committeeLoadStatus.textContent = 'Ralat mengambil data.'; btnLoadCommittee.disabled = false; if (committeeLoader) committeeLoader.style.display = 'none'; alert('Gagal mendapatkan data jawatankuasa: '+ (err && err.message ? err.message : 'Unknown')); });
                                    });
                                }

                                // Volunteer load handler (PELAJAR) - same as committee but stores into lastRows.volunteer
                                if (btnLoadVolunteer){
                                    btnLoadVolunteer.addEventListener('click', function(){
                                        var t = (volunteerType && volunteerType.value) ? volunteerType.value : '';
                                        if (!t){ volunteerLoadStatus.textContent = 'Sila pilih jenis ahli.'; return; }
                                        volunteerLoadStatus.textContent = ''; btnLoadVolunteer.disabled = true; if (volunteerLoader) volunteerLoader.style.display = 'inline-block';
                                        var url = window.location.pathname + '?ajax=committee&type=' + encodeURIComponent(t);
                                        fetch(url, { credentials: 'same-origin' }).then(function(r){ if(!r.ok) throw new Error('HTTP ' + r.status); return r.text(); }).then(function(text){
                                            try{
                                                var j = JSON.parse(text);
                                                if (!j || !j.ok){ throw new Error(j && j.error ? j.error : 'Empty response'); }
                                                lastRows.volunteer = j.rows || [];
                                                currentVolunteerPage = 1;
                                                renderVolunteerPage();
                                                if (volunteerWrap) volunteerWrap.style.display = '';
                                                volunteerLoadStatus.textContent = '';
                                                volunteerLoaded = true;
                                            } catch(err){ console.error('Volunteer parse error', err, text); volunteerLoadStatus.textContent = 'Ralat memproses data.'; alert('Gagal mendapatkan data sukarelawan.'); }
                                            btnLoadVolunteer.disabled = false; if (volunteerLoader) volunteerLoader.style.display = 'none';
                                        }).catch(function(err){ console.error('Volunteer fetch error', err); volunteerLoadStatus.textContent = 'Ralat mengambil data.'; btnLoadVolunteer.disabled = false; if (volunteerLoader) volunteerLoader.style.display = 'none'; alert('Gagal mendapatkan data sukarelawan: '+ (err && err.message ? err.message : 'Unknown')); });
                                    });
                                }

                        
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
            '<style>html,body{height:100%;margin:0;padding:0}body{font-family:Arial,Helvetica,sans-serif}.page{position:relative;width:210mm;height:297mm;overflow:hidden}.bg-img{position:absolute;left:0;top:0;width:100%;height:100%;object-fit:cover;z-index:0}.cert-name{position:absolute;left:50%;top:38%;transform:translate(-50%,-50%);font-size:20px;font-weight:700;color:#000;text-align:center;padding:0 30px;line-height:1.05;z-index:1}.cert-sport{position:absolute;left:50%;top:48%;transform:translateX(-50%);font-size:20px;font-weight:700;color:#000;text-align:center;padding:0 30px;z-index:1}@media print{.bg-img{display:block}}</style></head><body>'+
            '<div class="page">'+
                '<img class="bg-img" src="'+tUrl+'" alt="bg">'+
                '<div class="cert-name">'+(name||'')+'</div>'+
                '<div class="cert-sport">' + ((sukanCombined||'').toString().toUpperCase()) + '</div>'+
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
// Fallback for debugging: if content is empty, show a helpful message instead of blank page
if (is_string($content) && trim($content) === '') {
    $content = '<div class="container-fluid px-3"><div class="alert alert-warning">Halaman ini tidak memaparkan apa-apa — kandungan kosong (debug fallback).</div></div>';
}
require_once __DIR__ . '/../includes/layout.php';
