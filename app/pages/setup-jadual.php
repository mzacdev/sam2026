<?php
/**
 * Setup Jadual - placeholder page for schedule management
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/rbac.php';

Session::start();
$auth = getAuth();
$auth->requireAuth();

// Enforce RBAC for this page
$rbac = getRBAC();
$rbac->requirePageAccess('pages/setup-jadual.php');

$page_title = 'Setup Jadual Perlawanan';

// Utility: find round rows for event + nama_round
function _get_round_rows($db, $event_id, $nama_round) {
    $stmt = $db->prepare("SELECT id, group_code FROM table_round WHERE event_id = :event_id AND nama_round = :nama_round AND deleted_at IS NULL ORDER BY group_order ASC");
    $stmt->execute([':event_id' => $event_id, ':nama_round' => $nama_round]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- AJAX endpoints ---
if (isset($_GET['action']) && $_GET['action'] === 'get_round_names' && isset($_GET['event_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    $event_id = (int)$_GET['event_id'];
    if ($event_id <= 0) { echo json_encode(['success' => false]); exit; }
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT DISTINCT nama_round FROM table_round WHERE event_id = :event_id AND deleted_at IS NULL ORDER BY nama_round ASC");
        $stmt->execute([':event_id' => $event_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        echo json_encode(['success' => true, 'rounds' => $rows]);
    } catch (Exception $e) {
        error_log('[setup-jadual get_round_names] ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'server_error']);
    }
    exit;
}

// load all rounds + groups + teams for an event in one request
if (isset($_GET['action']) && $_GET['action'] === 'load_event_all' && isset($_GET['event_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    $event_id = (int)$_GET['event_id'];
    if ($event_id <= 0) { echo json_encode(['success' => false]); exit; }
    try {
        $db = getDB();
        $ev = $db->prepare('SELECT id, sukan_id FROM table_event WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $ev->execute([':id' => $event_id]);
        $evr = $ev->fetch(PDO::FETCH_ASSOC);
        if (!$evr) { echo json_encode(['success' => false, 'error' => 'event_not_found']); exit; }
        $sukan_id = (int)$evr['sukan_id'];

        // get distinct round names
        $stmt = $db->prepare("SELECT DISTINCT nama_round FROM table_round WHERE event_id = :event_id AND deleted_at IS NULL ORDER BY nama_round ASC");
        $stmt->execute([':event_id' => $event_id]);
        $rounds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        $groups_by_round = [];
        foreach ($rounds as $rname) {
            $rrows = _get_round_rows($db, $event_id, $rname);
            $codes = [];
            foreach ($rrows as $rr) { $c = trim((string)$rr['group_code']); if ($c !== '') $codes[] = $c; }
            $groups = [];
            if (!empty($codes)) {
                $q = $db->prepare('SELECT id, nama_pasukan, initial_group_code FROM table_pasukan WHERE sukan_id = :sukan_id AND initial_group_code IS NOT NULL AND initial_group_code != "" AND deleted_at IS NULL');
                $q->execute([':sukan_id' => $sukan_id]);
                $rows = $q->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $code = trim((string)$row['initial_group_code']);
                    if ($code === '') continue;
                    if (!in_array($code, $codes, true)) continue;
                    if (!isset($groups[$code])) $groups[$code] = [];
                    $groups[$code][] = ['id' => (int)$row['id'], 'nama_pasukan' => $row['nama_pasukan']];
                }
            }
            $groups_by_round[$rname] = $groups;
        }

        echo json_encode(['success' => true, 'rounds' => $rounds, 'groups_by_round' => $groups_by_round]);
    } catch (Exception $e) {
        error_log('[setup-jadual load_event_all] ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'server_error']);
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'load_groups' && isset($_GET['event_id']) && isset($_GET['nama_round'])) {
    header('Content-Type: application/json; charset=utf-8');
    $event_id = (int)$_GET['event_id'];
    $nama_round = trim((string)$_GET['nama_round']);
    if ($event_id <= 0 || $nama_round === '') { echo json_encode(['success' => false]); exit; }
    try {
        $db = getDB();
        $ev = $db->prepare('SELECT id, sukan_id FROM table_event WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $ev->execute([':id' => $event_id]);
        $evr = $ev->fetch(PDO::FETCH_ASSOC);
        if (!$evr) { echo json_encode(['success' => false, 'error' => 'event_not_found']); exit; }
        $sukan_id = (int)$evr['sukan_id'];

        // Load round rows ordered by group_order so groups are returned in defined order
        $rstmt = $db->prepare("SELECT id, group_code FROM table_round WHERE event_id = :event_id AND nama_round = :nama_round AND deleted_at IS NULL ORDER BY group_order ASC");
        $rstmt->execute([':event_id' => $event_id, ':nama_round' => $nama_round]);
        $roundRows = $rstmt->fetchAll(PDO::FETCH_ASSOC);

        $groups = [];
        // For each group_code in order, fetch teams ordered by nama_pasukan
        $teamStmt = $db->prepare('SELECT id, nama_pasukan, kontinjen_id, initial_group_code FROM table_pasukan WHERE sukan_id = :sukan_id AND initial_group_code = :code AND deleted_at IS NULL ORDER BY nama_pasukan ASC');
        foreach ($roundRows as $rr) {
            $code = trim((string)$rr['group_code']);
            if ($code === '') continue;
            $teamStmt->execute([':sukan_id' => $sukan_id, ':code' => $code]);
            $rows = $teamStmt->fetchAll(PDO::FETCH_ASSOC);
            $teams = [];
            foreach ($rows as $r) {
                $teams[] = ['id' => (int)$r['id'], 'nama_pasukan' => $r['nama_pasukan'], 'kontinjen_id' => isset($r['kontinjen_id']) ? (int)$r['kontinjen_id'] : null, 'initial_group_code' => $r['initial_group_code']];
            }
            $groups[] = ['group_code' => $code, 'teams' => $teams];
        }

        echo json_encode(['success' => true, 'groups' => $groups]);
    } catch (Exception $e) {
        error_log('[setup-jadual load_groups] ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'server_error']);
    }
    exit;
}

// load venues for modal selection
if (isset($_GET['action']) && $_GET['action'] === 'load_venues') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $db = getDB();
        $stmt = $db->prepare('SELECT id, nama_venue, lokasi FROM table_ref_venues WHERE status = 1 AND deleted_at IS NULL ORDER BY nama_venue ASC');
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $disp = trim($r['nama_venue'] . (isset($r['lokasi']) && trim($r['lokasi']) !== '' ? ' (' . $r['lokasi'] . ')' : ''));
            $out[] = ['id' => (int)$r['id'], 'display' => $disp];
        }
        echo json_encode(['success' => true, 'venues' => $out]);
    } catch (Exception $e) {
        error_log('[setup-jadual load_venues] ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'server_error']);
    }
    exit;
}

// list matches for selected round (grouped)
if (isset($_GET['action']) && $_GET['action'] === 'list_matches' && isset($_GET['event_id']) && isset($_GET['nama_round'])) {
    header('Content-Type: application/json; charset=utf-8');
    $event_id = (int)$_GET['event_id']; $nama_round = trim((string)$_GET['nama_round']);
    if ($event_id <= 0 || $nama_round === '') { echo json_encode(['success' => false]); exit; }
    try {
        $db = getDB();
        $roundRows = _get_round_rows($db, $event_id, $nama_round);
        if (empty($roundRows)) { echo json_encode(['success' => true, 'matches' => []]); exit; }
        $roundIds = array_map('intval', array_column($roundRows, 'id'));
        $in = implode(',', array_fill(0, count($roundIds), '?'));
        $sql = "SELECT m.id, m.round_id, m.group_code, m.match_no, m.status, m.tarikh FROM table_match m WHERE m.round_id IN ($in) AND m.deleted_at IS NULL ORDER BY m.group_code ASC, m.match_no ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($roundIds);
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($matches as $m) {
            $mid = (int)$m['id'];
            $pstmt = $db->prepare('SELECT p.team_id, t.nama_pasukan FROM table_match_participant p JOIN table_pasukan t ON t.id = p.team_id WHERE p.match_id = :mid AND p.deleted_at IS NULL');
            $pstmt->execute([':mid' => $mid]);
            $parts = $pstmt->fetchAll(PDO::FETCH_ASSOC);
            $teams = [];
            foreach ($parts as $p) $teams[] = ['id' => (int)$p['team_id'], 'nama_pasukan' => $p['nama_pasukan']];
            $out[$m['group_code']][] = ['id'=>$mid, 'match_no'=>(int)$m['match_no'], 'status'=>$m['status'], 'tarikh'=>$m['tarikh'], 'teams'=>$teams];
        }
        echo json_encode(['success' => true, 'matches' => $out]);
    } catch (Exception $e) { error_log('[setup-jadual list_matches] '.$e->getMessage()); echo json_encode(['success'=>false, 'error'=>'server_error']); }
    exit;
}

// Add manual match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_manual_match') {
    header('Content-Type: application/json; charset=utf-8');
    $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    $nama_round = isset($_POST['nama_round']) ? trim((string)$_POST['nama_round']) : '';
    $group_code = isset($_POST['group_code']) ? trim((string)$_POST['group_code']) : '';
    $team_a = isset($_POST['team_a']) ? (int)$_POST['team_a'] : 0;
    $team_b = isset($_POST['team_b']) ? (int)$_POST['team_b'] : 0;
    $match_no = isset($_POST['match_no']) && $_POST['match_no'] !== '' ? (int)$_POST['match_no'] : null;
    $tarikh = isset($_POST['tarikh']) && $_POST['tarikh'] !== '' ? trim((string)$_POST['tarikh']) : null;
    $venue_id = isset($_POST['venue_id']) && $_POST['venue_id'] !== '' ? (int)$_POST['venue_id'] : null;
    $venue_detail = isset($_POST['venue_detail']) ? trim((string)$_POST['venue_detail']) : null;

    $errors = [];
    if ($event_id <= 0) $errors[] = 'Event tidak sah';
    if ($nama_round === '') $errors[] = 'Round tidak dipilih';
    if ($group_code === '') $errors[] = 'Kumpulan tidak dipilih';
    if ($team_a <= 0 || $team_b <= 0) $errors[] = 'Sila pilih kedua-dua pasukan';
    if ($team_a === $team_b) $errors[] = 'Pasukan A mesti berbeza daripada Pasukan B';
    if ($venue_detail !== null && strlen($venue_detail) > 100) $errors[] = 'Venue detail terlalu panjang';
    if (!empty($errors)) { echo json_encode(['success'=>false,'errors'=>$errors]); exit; }

    try {
        $db = getDB();
        // ensure round exists and map to a round_id for this group
        $roundRows = _get_round_rows($db, $event_id, $nama_round);
        $groupMap = [];
        foreach ($roundRows as $r) { $c = trim((string)$r['group_code']); if ($c !== '') $groupMap[$c] = (int)$r['id']; }
        if (!isset($groupMap[$group_code])) { echo json_encode(['success'=>false,'errors'=>['Kumpulan tidak ditemui untuk round ini']]); exit; }
        $round_id = $groupMap[$group_code];

        // ensure both teams belong to this group (initial_group_code)
        $q = $db->prepare('SELECT id, initial_group_code FROM table_pasukan WHERE id IN (:a, :b) AND deleted_at IS NULL');
        $q->execute([':a'=>$team_a, ':b'=>$team_b]);
        $found = $q->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($found as $f) $map[(int)$f['id']] = $f['initial_group_code'];
        if (!isset($map[$team_a]) || !isset($map[$team_b])) { echo json_encode(['success'=>false,'errors'=>['Salah satu pasukan tidak ditemui']]); exit; }
        if (trim((string)$map[$team_a]) !== $group_code || trim((string)$map[$team_b]) !== $group_code) { echo json_encode(['success'=>false,'errors'=>['Kedua-dua pasukan mesti milik kumpulan yang sama']]); exit; }

        // ensure required columns exist (migration applied)
        $colCheck = $db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'table_match' AND COLUMN_NAME IN ('venue_id','venue_detail','created_by')");
        $colCheck->execute();
        $colCount = (int)$colCheck->fetchColumn();
        if ($colCount < 3) {
            echo json_encode(['success'=>false,'errors'=>['Database migration not applied: missing venue or created_by columns in table_match. Please run the migration: db/migrations/20260126_add_venue_to_table_match.sql']]);
            exit;
        }

        // prevent duplicate pairing within same round
        $dupSql = 'SELECT m.id FROM table_match m JOIN table_match_participant p1 ON p1.match_id=m.id AND p1.team_id=:a JOIN table_match_participant p2 ON p2.match_id=m.id AND p2.team_id=:b WHERE m.round_id = :rid AND m.deleted_at IS NULL LIMIT 1';
        $dup = $db->prepare($dupSql);
        $dup->execute([':a'=>$team_a, ':b'=>$team_b, ':rid'=>$round_id]);
        if ($dup->fetch()) { echo json_encode(['success'=>false,'errors'=>['Perlawanan antara pasukan ini telah wujud dalam round ini']]); exit; }

        // insert
        $db->beginTransaction();
        // validate venue if provided
        $created_by = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        if ($venue_id) {
            $vs = $db->prepare('SELECT id FROM table_ref_venues WHERE id = :id AND status = 1 AND deleted_at IS NULL LIMIT 1');
            $vs->execute([':id' => $venue_id]);
            if (!$vs->fetch()) { echo json_encode(['success'=>false,'errors'=>['Venue tidak sah']]); exit; }
        }

        $insSql = 'INSERT INTO table_match (event_id, round_id, group_code, match_no, status, created_by, tarikh, venue_id, venue_detail, created_at) VALUES (:event_id, :round_id, :group_code, :match_no, :status, :created_by, :tarikh, :venue_id, :venue_detail, NOW())';
        $ins = $db->prepare($insSql);
        $ins->execute([
            ':event_id'=>$event_id,
            ':round_id'=>$round_id,
            ':group_code'=>$group_code,
            ':match_no'=>$match_no,
            ':status'=>'scheduled',
            ':created_by'=>$created_by,
            ':tarikh'=>$tarikh,
            ':venue_id'=>$venue_id,
            ':venue_detail'=>$venue_detail
        ]);
        $mid = (int)$db->lastInsertId();
        $pins = $db->prepare('INSERT INTO table_match_participant (match_id, team_id, created_at) VALUES (:mid, :tid, NOW())');
        $pins->execute([':mid'=>$mid, ':tid'=>$team_a]);
        $pins->execute([':mid'=>$mid, ':tid'=>$team_b]);
        $db->commit();

        echo json_encode(['success'=>true,'match'=>['id'=>$mid,'match_no'=>$match_no,'group'=>$group_code]]);
    } catch (Exception $e) {
        if (isset($db) && $db && $db->inTransaction()) $db->rollBack();
        error_log('[setup-jadual add_manual] '.$e->getMessage());
        echo json_encode(['success'=>false,'errors'=>['Server error']]);
    }
    exit;
}

// delete match (soft-delete) — only allowed when all matches for the round are in 'scheduled' status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_match') {
    header('Content-Type: application/json; charset=utf-8');
    $match_id = isset($_POST['match_id']) ? (int)$_POST['match_id'] : 0;
    if ($match_id <= 0) { echo json_encode(['success'=>false,'errors'=>['Invalid match id']]); exit; }
    try {
        $db = getDB();
        $m = $db->prepare('SELECT id, round_id FROM table_match WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $m->execute([':id'=>$match_id]);
        $mr = $m->fetch(PDO::FETCH_ASSOC);
        if (!$mr) { echo json_encode(['success'=>false,'errors'=>['Match not found']]); exit; }
        $rid = (int)$mr['round_id'];
        // check statuses for all matches in this round
        $s = $db->prepare('SELECT COUNT(*) FROM table_match WHERE round_id = :rid AND deleted_at IS NULL AND status != :sched');
        $s->execute([':rid'=>$rid, ':sched'=>'scheduled']);
        $cnt = (int)$s->fetchColumn();
        if ($cnt > 0) { echo json_encode(['success'=>false,'errors'=>['Round is locked — some matches are not in scheduled status']]); exit; }

        $db->beginTransaction();
        $db->prepare('UPDATE table_match_participant SET deleted_at = NOW() WHERE match_id = :mid')->execute([':mid'=>$match_id]);
        $db->prepare('UPDATE table_match SET deleted_at = NOW() WHERE id = :mid')->execute([':mid'=>$match_id]);
        $db->commit();
        echo json_encode(['success'=>true]);
    } catch (Exception $e) {
        if (isset($db) && $db && $db->inTransaction()) $db->rollBack();
        error_log('[setup-jadual delete_match] '.$e->getMessage());
        echo json_encode(['success'=>false,'errors'=>['Server error']]);
    }
    exit;
}

// POST: generate schedule (auto) — keep previous behavior but ensure no matches exist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_schedule') {
    header('Content-Type: application/json; charset=utf-8');
    $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    $nama_round = isset($_POST['nama_round']) ? trim((string)$_POST['nama_round']) : '';
    if ($event_id <= 0 || $nama_round === '') { echo json_encode(['success' => false, 'errors' => ['Parameter tidak sah']]); exit; }

    try {
        $db = getDB();
        $ev = $db->prepare('SELECT id, sukan_id FROM table_event WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $ev->execute([':id' => $event_id]);
        $evr = $ev->fetch(PDO::FETCH_ASSOC);
        if (!$evr) { echo json_encode(['success' => false, 'errors' => ['Event tidak dijumpai']]); exit; }
        $sukan_id = (int)$evr['sukan_id'];

        $roundRows = _get_round_rows($db, $event_id, $nama_round);
        if (empty($roundRows)) { echo json_encode(['success' => false, 'errors' => ['Tiada round ditemui untuk event ini']]); exit; }
        $groupCodes = [];
        $roundIds = [];
        foreach ($roundRows as $rr) { $gc = trim((string)$rr['group_code']); if ($gc === '') continue; $groupCodes[$gc] = (int)$rr['id']; $roundIds[] = (int)$rr['id']; }
        if (empty($groupCodes)) { echo json_encode(['success' => false, 'errors' => ['Tiada kumpulan ditetapkan untuk round ini']]); exit; }

        $in = implode(',', array_fill(0, count($roundIds), '?'));
        $checkSql = "SELECT COUNT(*) AS c FROM table_match WHERE round_id IN ($in) AND deleted_at IS NULL";
        $chk = $db->prepare($checkSql);
        $chk->execute($roundIds);
        $cnt = (int)$chk->fetchColumn();
        if ($cnt > 0) { echo json_encode(['success' => false, 'errors' => ['Jadual perlawanan telah dijana atau wujud perlawanan manual sebelum ini.']]); exit; }

        // For each group, fetch teams
        $groups = [];
        $teamStmt = $db->prepare('SELECT id, nama_pasukan FROM table_pasukan WHERE sukan_id = :sukan_id AND initial_group_code = :code AND deleted_at IS NULL ORDER BY id ASC');
        foreach ($groupCodes as $code => $rid) {
            $teamStmt->execute([':sukan_id' => $sukan_id, ':code' => $code]);
            $rows = $teamStmt->fetchAll(PDO::FETCH_ASSOC);
            $teams = array_map(function($r){ return ['id' => (int)$r['id'], 'nama_pasukan' => $r['nama_pasukan']]; }, $rows);
            if (count($teams) < 2) {
                echo json_encode(['success' => false, 'errors' => ["Kumpulan {$code} mesti mempunyai sekurang-kurangnya 2 pasukan."]]);
                exit;
            }
            $groups[$code] = $teams;
        }

        // All validations passed — generate matches
        $db->beginTransaction();
        $insertMatch = $db->prepare('INSERT INTO table_match (event_id, round_id, group_code, match_no, status, tarikh, created_at) VALUES (:event_id, :round_id, :group_code, :match_no, :status, NULL, NOW())');
        $insertPart = $db->prepare('INSERT INTO table_match_participant (match_id, team_id, created_at) VALUES (:match_id, :team_id, NOW())');

        $output = [];
        foreach ($groups as $code => $teams) {
            $matchNo = 1;
            $teamCount = count($teams);
            for ($i = 0; $i < $teamCount; $i++) {
                for ($j = $i + 1; $j < $teamCount; $j++) {
                    $t1 = $teams[$i]['id'];
                    $t2 = $teams[$j]['id'];
                    $round_id = $groupCodes[$code] ?? $roundIds[0];
                    $insertMatch->execute([
                        ':event_id' => $event_id,
                        ':round_id' => $round_id,
                        ':group_code' => $code,
                        ':match_no' => $matchNo,
                        ':status' => 'scheduled'
                    ]);
                    $matchId = (int)$db->lastInsertId();
                    $insertPart->execute([':match_id' => $matchId, ':team_id' => $t1]);
                    $insertPart->execute([':match_id' => $matchId, ':team_id' => $t2]);
                    $output[$code][] = ['match_no' => $matchNo, 'teams' => [ ['id'=>$t1], ['id'=>$t2] ]];
                    $matchNo++;
                }
            }
        }
        $db->commit();

        echo json_encode(['success' => true, 'matches' => $output]);
        exit;
    } catch (Exception $e) {
        if (isset($db) && $db && $db->inTransaction()) $db->rollBack();
        error_log('[setup-jadual generate] ' . $e->getMessage());
        echo json_encode(['success' => false, 'errors' => ['Gagal menjana jadual: ' . $e->getMessage()]]);
        exit;
    }
}

// Render page — load events
$events = [];
try {
    $db = getDB();
    // Load non-deleted events (exclude cancelled), show most recent first
    $stmt = $db->prepare("SELECT id, nama_event FROM table_event WHERE deleted_at IS NULL AND status != 'cancelled' ORDER BY tarikh_mula DESC, created_at DESC");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { error_log('[setup-jadual] load events: ' . $e->getMessage()); }

ob_start();
?>
<div class="w-100 px-3">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="mb-1">Setup Jadual Perlawanan</h2>
            <p class="text-muted">Jana atau tambah perlawanan untuk peringkat kumpulan.</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Pilih Event</label>
                    <select id="sj-event" class="form-select">
                        <option value="">-- Pilih Event --</option>
                        <?php foreach ($events as $ev): ?>
                            <option value="<?php echo (int)$ev['id']; ?>"><?php echo htmlspecialchars($ev['nama_event'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pilih Round</label>
                    <select id="sj-round" class="form-select" disabled>
                        <option value="">-- Pilih Round --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mode</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="sj-mode" id="mode-manual" value="manual" checked>
                            <label class="form-check-label" for="mode-manual">Manual Setup Jadual</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="sj-mode" id="mode-auto" value="auto">
                            <label class="form-check-label" for="mode-auto">Auto Jana Jadual</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="manual-section" class="mb-3">
        <div class="card">
            <div class="card-header">Manual Setup</div>
            <div class="card-body">
                <div class="mb-3">
                    <?php if (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['ADMIN','ORGANIZER'])): ?>
                        <button id="sj-add-btn" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahPerlawanan">Tambah Perlawanan</button>
                    <?php endif; ?>
                </div>

                <!-- Modal Tambah Perlawanan -->
                <div class="modal fade" id="modalTambahPerlawanan" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Tambah Perlawanan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Round / Peringkat</label>
                                    <select id="modal-round" class="form-select"></select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kumpulan</label>
                                    <select id="modal-group" class="form-select"></select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pasukan A</label>
                                    <select id="modal-team-a" class="form-select"></select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pasukan B</label>
                                    <select id="modal-team-b" class="form-select"></select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tarikh & Masa (optional)</label>
                                    <input id="modal-tarikh" class="form-control" type="datetime-local">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Venue (optional)</label>
                                    <select id="modal-venue" class="form-select">
                                        <option value="">-- Pilih Venue (optional) --</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Venue Detail (optional)</label>
                                    <input id="modal-venue-detail" class="form-control" type="text" placeholder="Contoh: Padang A, Court 2, Lane 4" maxlength="100" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nombor Perlawanan</label>
                                    <input id="modal-match-no" class="form-control" type="number" min="1">
                                    <div id="modal-match-no-hint" class="form-text">Cadangan: akan mengesyorkan nombor perlawanan seterusnya.</div>
                                </div>
                                <div id="modal-msg" class="text-danger"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="button" id="modal-save-btn" class="btn btn-primary">Simpan Perlawanan</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="manual-table" class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Kumpulan</th><th>Match No</th><th>Pasukan A</th><th>Pasukan B</th><th>Tarikh</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody id="manual-table-body"><tr><td colspan="7" class="text-muted">Pilih event dan round untuk memuatkan perlawanan.</td></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="auto-section" style="display:none;" class="mb-3">
        <div class="card">
            <div class="card-header">Auto Jana Jadual</div>
            <div class="card-body">
                <div class="card mb-3">
                    <div class="card-header">Ringkasan Kumpulan</div>
                    <div class="card-body" id="sj-groups">
                        <p class="text-muted">Pilih event dan round untuk melihat ringkasan kumpulan.</p>
                    </div>
                </div>
                <div>
                    <button id="sj-generate" class="btn btn-primary" disabled>Jana Jadual Perlawanan</button>
                    <div id="sj-result" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
(function(){
    const evt = document.getElementById('sj-event');
    const rnd = document.getElementById('sj-round');
    const modeManual = document.getElementById('mode-manual');
    const modeAuto = document.getElementById('mode-auto');
    const manualSection = document.getElementById('manual-section');
    const autoSection = document.getElementById('auto-section');

    const groupsEl = document.getElementById('sj-groups');
    const genBtn = document.getElementById('sj-generate');
    const resultEl = document.getElementById('sj-result');

    const addBtn = document.getElementById('sj-add-btn');
    const manualGroup = document.getElementById('manual-group') || document.getElementById('modal-group');
    const manualA = document.getElementById('manual-team-a') || document.getElementById('modal-team-a');
    const manualB = document.getElementById('manual-team-b') || document.getElementById('modal-team-b');
    const manualMatchNo = document.getElementById('manual-match-no') || document.getElementById('modal-match-no');
    const manualTarikh = document.getElementById('manual-tarikh') || document.getElementById('modal-tarikh');
    const manualSave = document.getElementById('manual-save') || document.getElementById('modal-save-btn');
    const manualMsg = document.getElementById('manual-msg') || document.getElementById('modal-msg');
    const modalRound = document.getElementById('modal-round');
    const modalGroup = document.getElementById('modal-group');
    const modalA = document.getElementById('modal-team-a');
    const modalB = document.getElementById('modal-team-b');
    const modalMatchNoHint = document.getElementById('modal-match-no-hint');
    const modalMsg = document.getElementById('modal-msg');
    const modalVenue = document.getElementById('modal-venue');
    const modalVenueDetail = document.getElementById('modal-venue-detail');
    const manualTableBody = document.getElementById('manual-table-body');

    let currentGroups = {}; // code => [{id,name}]

    function setMode() {
        if (modeManual.checked) { manualSection.style.display = ''; autoSection.style.display = 'none'; }
        else { manualSection.style.display = 'none'; autoSection.style.display = ''; }
    }
    setMode();
    modeManual.addEventListener('change', setMode);
    modeAuto.addEventListener('change', setMode);

    evt && evt.addEventListener('change', async function(){
        const id = this.value || '';
        rnd.innerHTML = '<option value="">-- Pilih Round --</option>';
        rnd.disabled = true; groupsEl.innerHTML = '<p class="text-muted">Pilih event dan round untuk melihat ringkasan kumpulan.</p>';
        genBtn.disabled = true; resultEl.innerHTML = '';
        manualTableBody.innerHTML = '<tr><td colspan="7" class="text-muted">Pilih event dan round untuk memuatkan perlawanan.</td></tr>';
        window.loadedEventData = null; currentGroups = {};
        if (!id) return;
        try{
            const res = await fetch('?action=load_event_all&event_id=' + encodeURIComponent(id));
            const j = await res.json();
            if (j && j.success){
                window.loadedEventData = j;
                // populate rounds
                if (Array.isArray(j.rounds)){
                    j.rounds.forEach(r => { const opt = document.createElement('option'); opt.value = r; opt.textContent = r; rnd.appendChild(opt); });
                    rnd.disabled = false;
                    // auto-select first round if available
                    if (j.rounds.length === 1) { rnd.value = j.rounds[0]; loadGroupsAndMatches(); }
                }
            }
        }catch(e){ console.error(e); }
    });

    async function loadGroupsAndMatches() {
        const eventId = evt.value || '';
        const nama = rnd.value || '';
        groupsEl.innerHTML = '<p class="text-muted">Memuat kumpulan...</p>';
        genBtn.disabled = true; resultEl.innerHTML = '';
        manualTableBody.innerHTML = '<tr><td colspan="7" class="text-muted">Memuat perlawanan...</td></tr>';
        if (addBtn) addBtn.disabled = false;
        manualMsg.innerHTML = '';
        if (!eventId || !nama) return;
        try{
            let j = null;
            if (window.loadedEventData && window.loadedEventData.groups_by_round && window.loadedEventData.groups_by_round[nama]){
                j = { success: true, groups: window.loadedEventData.groups_by_round[nama] };
            } else {
                const res = await fetch('?action=load_groups&event_id=' + encodeURIComponent(eventId) + '&nama_round=' + encodeURIComponent(nama));
                j = await res.json();
            }
                if (j && j.success){
                    // j.groups is an ordered array: [{group_code, teams:[]}, ...]
                    let groupsArr = j.groups || [];
                    if (!Array.isArray(groupsArr) && groupsArr && typeof groupsArr === 'object') {
                        groupsArr = Object.keys(groupsArr).map(function(code){ return { group_code: code, teams: groupsArr[code] || [] }; });
                    }
                    if (groupsArr.length === 0) { groupsEl.innerHTML = '<p class="text-muted">Tiada kumpulan ditemui.</p>'; manualTableBody.innerHTML = '<tr><td colspan="7" class="text-muted">Tiada perlawanan.</td></tr>'; return; }
                    // build mapping for quick lookup and maintain order
                    currentGroups = {}; currentGroupsOrder = [];
                    let html = '';
                    manualGroup.innerHTML = '';
                    groupsArr.forEach(g => {
                        const code = g.group_code;
                        currentGroupsOrder.push(code);
                        currentGroups[code] = g.teams || [];
                        html += '<h6>Group ' + code + ':</h6><ul>';
                        (g.teams || []).forEach(t => { html += '<li>' + (t.nama_pasukan || ('Pasukan ' + t.id)) + '</li>'; });
                        html += '</ul>';
                        const opt = document.createElement('option'); opt.value = code; opt.textContent = code; manualGroup.appendChild(opt);
                    });
                    groupsEl.innerHTML = html;
                    // populate manual team selects for first group
                    _populateTeamsForGroup(currentGroupsOrder[0]);
            }
            // now load matches
            const lm = await fetch('?action=list_matches&event_id=' + encodeURIComponent(eventId) + '&nama_round=' + encodeURIComponent(nama));
            const lmj = await lm.json();
            if (lmj && lmj.success){
                const matches = lmj.matches || {};
                _renderMatchTable(matches);
                // If any matches exist, disable auto generation
                const hasAny = Object.keys(matches).some(k => (matches[k]||[]).length>0);
                genBtn.disabled = hasAny; // server will also protect
                if (hasAny) resultEl.innerHTML = '<div class="alert alert-info">Terdapat perlawanan sedia ada untuk round ini — auto-generation dikunci.</div>';
            }
        }catch(e){ console.error(e); groupsEl.innerHTML = '<p class="text-danger">Gagal memuat kumpulan.</p>'; }
    }

    rnd && rnd.addEventListener('change', loadGroupsAndMatches);

    function _populateTeamsForGroup(code, targetA = manualA, targetB = manualB){
        if (!targetA || !targetB) return;
        targetA.innerHTML = '';
        targetB.innerHTML = '';
        const arr = currentGroups[code] || [];
        arr.forEach(t => { const o = document.createElement('option'); o.value = t.id; o.textContent = t.nama_pasukan; targetA.appendChild(o); });
        arr.forEach(t => { const o = document.createElement('option'); o.value = t.id; o.textContent = t.nama_pasukan; targetB.appendChild(o); });
    }

    manualGroup && manualGroup.addEventListener('change', function(){ _populateTeamsForGroup(this.value); });

    // modal-specific: when modal opens, populate modal round/group/team selects
    document.getElementById('modalTambahPerlawanan') && document.getElementById('modalTambahPerlawanan').addEventListener('show.bs.modal', function(){
        // populate modal rounds
        modalRound.innerHTML = '';
        if (window.loadedEventData && Array.isArray(window.loadedEventData.rounds)){
            window.loadedEventData.rounds.forEach(r => { const opt = document.createElement('option'); opt.value = r; opt.textContent = r; modalRound.appendChild(opt); });
        }
        // default select current page round if any
        if (rnd && rnd.value) modalRound.value = rnd.value;
        // trigger change to load groups
        const ev = new Event('change'); modalRound.dispatchEvent(ev);
        // load venues for modal
        if (modalVenue) {
            modalVenue.innerHTML = '<option value="">-- Pilih Venue (optional) --</option>';
            fetch('?action=load_venues')
                .then(r=>r.json())
                .then(j=>{
                    if (j && j.success) {
                        (j.venues||[]).forEach(v=>{
                            const o = document.createElement('option');
                            o.value = v.id; o.textContent = v.display;
                            modalVenue.appendChild(o);
                        });
                    }
                }).catch(()=>{});
        }
    });

    modalRound && modalRound.addEventListener('change', async function(){
        const rname = this.value || '';
        modalGroup.innerHTML = '';
        if (!rname) return;
        // Try to use preloaded data first
        if (window.loadedEventData && window.loadedEventData.groups_by_round && window.loadedEventData.groups_by_round[rname]){
            let groupsArr = window.loadedEventData.groups_by_round[rname];
            if (!Array.isArray(groupsArr) && typeof groupsArr === 'object') {
                groupsArr = Object.keys(groupsArr).map(function(code){ return { group_code: code, teams: groupsArr[code] || [] }; });
            }
            groupsArr.forEach(g => { const o = document.createElement('option'); o.value = g.group_code; o.textContent = g.group_code; modalGroup.appendChild(o); });
            // auto populate teams for first group
            if (modalGroup.options.length) {
                modalGroup.value = modalGroup.options[0].value;
                // populate using the preloaded teams
                const first = groupsArr[0];
                if (first && Array.isArray(first.teams)) {
                    // update modal team selects directly
                    modalA.innerHTML = '';
                    modalB.innerHTML = '';
                    first.teams.forEach(t => { const oa = document.createElement('option'); oa.value = t.id; oa.textContent = t.nama_pasukan; modalA.appendChild(oa); const ob = document.createElement('option'); ob.value = t.id; ob.textContent = t.nama_pasukan; modalB.appendChild(ob); });
                }
                _suggestNextMatchNo(rname, modalGroup.value);
            }
            return;
        }
        // Fallback: fetch groups for this round from server
        if (!evt.value) return; // need event selected
        try {
            const res = await fetch('?action=load_groups&event_id=' + encodeURIComponent(evt.value) + '&nama_round=' + encodeURIComponent(rname));
            const j = await res.json();
            if (j && j.success) {
                const groupsArr = j.groups || [];
                groupsArr.forEach(g => { const o = document.createElement('option'); o.value = g.group_code; o.textContent = g.group_code; modalGroup.appendChild(o); });
                if (modalGroup.options.length) {
                    modalGroup.value = modalGroup.options[0].value;
                    const first = groupsArr[0];
                    if (first && Array.isArray(first.teams)) {
                        modalA.innerHTML = '';
                        modalB.innerHTML = '';
                        first.teams.forEach(t => { const oa = document.createElement('option'); oa.value = t.id; oa.textContent = t.nama_pasukan; modalA.appendChild(oa); const ob = document.createElement('option'); ob.value = t.id; ob.textContent = t.nama_pasukan; modalB.appendChild(ob); });
                    }
                    _suggestNextMatchNo(rname, modalGroup.value);
                }
            }
        } catch (e) { console.error('Failed to load groups for modal:', e); }
    });

    modalGroup && modalGroup.addEventListener('change', async function(){
        const code = this.value || '';
        if (!code) return;
        // If preloaded groups exist for current round, use them
        const rname = modalRound.value || '';
        if (window.loadedEventData && window.loadedEventData.groups_by_round && window.loadedEventData.groups_by_round[rname]){
            let groupsArr = window.loadedEventData.groups_by_round[rname];
            if (!Array.isArray(groupsArr) && typeof groupsArr === 'object') {
                groupsArr = Object.keys(groupsArr).map(function(codeKey){ return { group_code: codeKey, teams: groupsArr[codeKey] || [] }; });
            }
            const found = groupsArr.find(g => g.group_code === code);
            if (found) {
                // populate modal team selects from found.teams
                modalA.innerHTML = '';
                modalB.innerHTML = '';
                (found.teams || []).forEach(t => { const oa = document.createElement('option'); oa.value = t.id; oa.textContent = t.nama_pasukan; modalA.appendChild(oa); const ob = document.createElement('option'); ob.value = t.id; ob.textContent = t.nama_pasukan; modalB.appendChild(ob); });
                _suggestNextMatchNo(rname, code);
                return;
            }
        }
        // Otherwise fetch groups (which include teams) and populate
        if (!evt.value || !rname) return;
        try {
            const res = await fetch('?action=load_groups&event_id=' + encodeURIComponent(evt.value) + '&nama_round=' + encodeURIComponent(rname));
            const j = await res.json();
            if (j && j.success) {
                const groupsArr = j.groups || [];
                // update window.loadedEventData cache if present
                if (window.loadedEventData) {
                    window.loadedEventData.groups_by_round = window.loadedEventData.groups_by_round || {};
                    window.loadedEventData.groups_by_round[rname] = groupsArr;
                }
                const found = groupsArr.find(g => g.group_code === code);
                if (found) {
                    modalA.innerHTML = '';
                    modalB.innerHTML = '';
                    (found.teams || []).forEach(t => { const oa = document.createElement('option'); oa.value = t.id; oa.textContent = t.nama_pasukan; modalA.appendChild(oa); const ob = document.createElement('option'); ob.value = t.id; ob.textContent = t.nama_pasukan; modalB.appendChild(ob); });
                }
                _suggestNextMatchNo(rname, code);
            }
        } catch (e) { console.error('Failed to load teams for modal group:', e); }
    });

        // enable/disable venue detail input based on venue selection
        modalVenue && modalVenue.addEventListener('change', function(){
            if (this.value) { modalVenueDetail.disabled = false; }
            else { modalVenueDetail.disabled = true; modalVenueDetail.value = ''; }
        });

    function _suggestNextMatchNo(roundName, groupCode){
        // use list_matches if available
        if (!evt.value || !roundName) return;
        fetch('?action=list_matches&event_id=' + encodeURIComponent(evt.value) + '&nama_round=' + encodeURIComponent(roundName))
            .then(r=>r.json()).then(j=>{
                if (!j || !j.success) return;
                const matches = j.matches || {};
                const arr = matches[groupCode] || [];
                let max = 0; arr.forEach(m => { if (m.match_no && m.match_no > max) max = m.match_no; });
                const next = max + 1;
                if (manualMatchNo) manualMatchNo.value = next;
                if (modalMatchNoHint) modalMatchNoHint.textContent = 'Cadangan nombor perlawanan: ' + next;
            }).catch(()=>{});
    }

    function _renderMatchTable(matches){
        manualTableBody.innerHTML = '';
        const allGroups = Object.keys(matches || {});
        if (allGroups.length === 0) { manualTableBody.innerHTML = '<tr><td colspan="7" class="text-muted">Tiada perlawanan.</td></tr>'; return; }
        let locked = false;
        for (const code of allGroups){
            const arr = matches[code] || [];
            arr.forEach(m => {
                const tr = document.createElement('tr');
                const teams = (m.teams||[]).map(t=>t.nama_pasukan||t.id).join(' vs ');
                tr.innerHTML = '<td>'+code+'</td><td>'+ (m.match_no||'') +'</td><td>'+ (m.teams[0]?m.teams[0].nama_pasukan:'') +'</td><td>'+ (m.teams[1]?m.teams[1].nama_pasukan:'') +'</td><td>'+ (m.tarikh||'') +'</td><td>'+m.status+'</td><td>'+(m.status==='scheduled'?'<button class="btn btn-sm btn-danger manual-delete" data-id="'+m.id+'">Padam</button>':'')+'</td>';
                manualTableBody.appendChild(tr);
                if (m.status !== 'scheduled') locked = true;
            });
        }
        // apply locking rules
        const disableActions = locked;
        document.querySelectorAll('.manual-delete').forEach(b=>b.disabled = disableActions);
        addBtn.disabled = disableActions;
        // attach delete handlers
        document.querySelectorAll('.manual-delete').forEach(b=> b.addEventListener('click', async function(){
            if (!confirm('Padam perlawanan ini?')) return; const mid = this.getAttribute('data-id');
            const fd = new FormData(); fd.append('action','delete_match'); fd.append('match_id', mid);
            const res = await fetch('', { method: 'POST', body: fd }); const j = await res.json();
            if (j && j.success){ loadGroupsAndMatches(); } else { alert((j.errors||['Gagal']).join('\n')); }
        }));
    }

    // open modal handled by bootstrap data attributes; handle modal save
    manualSave && manualSave.addEventListener('click', async function(){
        modalMsg.innerHTML = '';
        const eventId = evt.value || '';
        const nama = modalRound.value || '';
        const group = modalGroup.value || '';
        const a = modalA.value || '';
        const b = modalB.value || '';
        const no = manualMatchNo.value || '';
        const tar = manualTarikh.value || '';
        const venueId = modalVenue ? modalVenue.value || '' : '';
        const venueDetail = modalVenueDetail ? modalVenueDetail.value || '' : '';
        if (!eventId || !nama || !group || !a || !b) { modalMsg.innerHTML = 'Sila lengkapkan semua medan.'; return; }
        if (a === b) { modalMsg.innerHTML = 'Pasukan A mesti berbeza daripada Pasukan B.'; return; }
        // client-side duplicate check
        try {
            const lm = await fetch('?action=list_matches&event_id=' + encodeURIComponent(eventId) + '&nama_round=' + encodeURIComponent(nama));
            const lmj = await lm.json();
            if (lmj && lmj.success) {
                const arr = (lmj.matches && lmj.matches[group]) || [];
                const dup = arr.some(m => {
                    if (!m.teams) return false;
                    const ids = m.teams.map(t=>String(t.id));
                    return (ids.includes(String(a)) && ids.includes(String(b)));
                });
                if (dup) { modalMsg.innerHTML = 'Perlawanan antara pasukan ini telah wujud dalam round ini.'; return; }
            }
        } catch(e) { /* ignore, server will validate */ }

        try{
            const fd = new FormData(); fd.append('action','add_manual_match'); fd.append('event_id', eventId); fd.append('nama_round', nama);
            fd.append('group_code', group); fd.append('team_a', a); fd.append('team_b', b);
            if (no) fd.append('match_no', no); if (tar) fd.append('tarikh', tar);
            if (venueId) fd.append('venue_id', venueId);
            if (venueDetail) fd.append('venue_detail', venueDetail);
            manualSave.disabled = true; manualSave.textContent = 'Menyimpan...';
            const res = await fetch('', { method: 'POST', body: fd }); const j = await res.json();
            manualSave.disabled = false; manualSave.textContent = 'Simpan Perlawanan';
            if (j && j.success){
                // close modal
                const modalEl = document.getElementById('modalTambahPerlawanan');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal && modal.hide();
                // show success message and refresh list
                resultEl.innerHTML = '<div class="alert alert-success">Perlawanan berjaya ditambah</div>';
                loadGroupsAndMatches();
            } else {
                modalMsg.innerHTML = (j.errors || ['Gagal menyimpan']).join('<br>');
            }
        }catch(e){ modalMsg.innerHTML = 'Ralat jaringan.'; manualSave.disabled = false; manualSave.textContent = 'Simpan Perlawanan'; }
    });

    genBtn && genBtn.addEventListener('click', async function(){
        const eventId = evt.value || '';
        const nama = rnd.value || '';
        if (!eventId || !nama) return;
        if (!confirm('Anda pasti mahu menjana jadual perlawanan untuk round ini?')) return;
        genBtn.disabled = true; genBtn.textContent = 'Menjana...'; resultEl.innerHTML = '';
        try{
            const fd = new FormData(); fd.append('action','generate_schedule'); fd.append('event_id', eventId); fd.append('nama_round', nama);
            const res = await fetch('', { method: 'POST', body: fd });
            const j = await res.json();
            if (j && j.success){
                const matches = j.matches || {};
                let html = '<div class="alert alert-success">Jadual berjaya dijana.</div>';
                for (const code of Object.keys(matches)){
                    html += '<h6>Group ' + code + '</h6><ul>';
                    matches[code].forEach(m => { html += '<li>Match ' + m.match_no + '</li>'; });
                    html += '</ul>';
                }
                resultEl.innerHTML = html;
                genBtn.disabled = true; genBtn.textContent = 'Jana Jadual Perlawanan';
                // refresh table
                loadGroupsAndMatches();
            } else {
                const msg = (j.errors || ['Gagal menjana jadual']).join('<br>');
                resultEl.innerHTML = '<div class="alert alert-danger">' + msg + '</div>';
                genBtn.disabled = false; genBtn.textContent = 'Jana Jadual Perlawanan';
            }
        }catch(e){ console.error(e); resultEl.innerHTML = '<div class="alert alert-danger">Ralat jaringan.</div>'; genBtn.disabled = false; genBtn.textContent = 'Jana Jadual Perlawanan'; }
    });

})();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
