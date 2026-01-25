<?php
/**
 * Setup Pertandingan - Multi-step (TAB 1: Maklumat Kejohanan)
 * Access: ADMIN, ORGANIZER, JUDGE
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';

Session::start();

// Ensure application logs directory exists and route PHP error_log there
$__app_log_dir = __DIR__ . '/../logs';
if (!is_dir($__app_log_dir)) { @mkdir($__app_log_dir, 0777, true); }
$__app_error_log = $__app_log_dir . '/setup-pertandingan.error.log';
@ini_set('error_log', $__app_error_log);
error_log('[setup-pertandingan] PHP error_log redirected to ' . $__app_error_log);

$auth = getAuth();
$auth->requireAuth();

// Debug helper: allow forcing current event via query param ?force_event=1
if (isset($_GET['force_event']) && is_numeric($_GET['force_event'])) {
	$fe = (int)$_GET['force_event'];
	if ($fe > 0) {
		$_SESSION['current_event_id'] = $fe;
		error_log('[setup-pertandingan] debug: forced current_event_id=' . $fe);
	}
}

// Try to include RBAC from several likely locations to tolerate different deployments
$rbacCandidates = [
	__DIR__ . '/../config/rbac.php',
	__DIR__ . '/../../config/rbac.php',
	__DIR__ . '/../../app/config/rbac.php',
	__DIR__ . '/../config/rbac.php'
];
$rbacIncluded = false;
foreach ($rbacCandidates as $p) {
	if (file_exists($p)) {
		require_once $p;
		error_log('[setup-pertandingan] included RBAC from: ' . $p);
		$rbacIncluded = true;
		break;
	}
}

// Defensive check and clearer error message when RBAC not available
if (!function_exists('getRBAC')) {
	error_log('[setup-pertandingan] getRBAC() not found after attempted includes: ' . implode(', ', $rbacCandidates));
	header('Content-Type: text/plain; charset=utf-8');
	echo "Server configuration error: RBAC not available.\n";
	echo "Attempted paths:\n" . implode("\n", $rbacCandidates) . "\n";
	if (!$rbacIncluded) echo "Note: none of the candidate files were present.\n";
	exit;
}
$rbac = getRBAC();
// Enforce page-specific access (ADMIN, ORGANIZER, JUDGE allowed via RBAC rules)
$rbac->requirePageAccess('pages/setup-pertandingan.php');

$page_title = 'Setup Pertandingan';

// --- Server-side: AJAX check for existing event (sukan + kategori) ---
if (isset($_GET['action']) && $_GET['action'] === 'check_event' && isset($_GET['sukan_id']) && isset($_GET['kategori_id'])) {
	header('Content-Type: application/json; charset=utf-8');
	$sukan_id = (int)$_GET['sukan_id'];
	$kategori_id = (int)$_GET['kategori_id'];
	if ($sukan_id <= 0 || $kategori_id <= 0) {
		echo json_encode(['success' => false]);
		exit;
	}
	try {
		$db = getDB();
		$stmt = $db->prepare('SELECT * FROM table_event WHERE sukan_id = :sukan_id AND kategori_id = :kategori_id AND deleted_at IS NULL LIMIT 1');
		$stmt->execute([':sukan_id' => $sukan_id, ':kategori_id' => $kategori_id]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($row) {
			// store in session
			$_SESSION['current_event_id'] = (int)$row['id'];
			echo json_encode(['success' => true, 'exists' => true, 'event' => $row]);
			exit;
		} else {
			echo json_encode(['success' => true, 'exists' => false]);
			exit;
		}
	} catch (Exception $e) {
		error_log('[setup-pertandingan check_event] ' . $e->getMessage());
		echo json_encode(['success' => false, 'error' => 'server_error']);
		exit;
	}
}

// --- Server-side: handle AJAX save for TAB1 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_tab1') {
	header('Content-Type: application/json; charset=utf-8');
	$errors = [];
	$sukan_id = isset($_POST['sukan_id']) ? (int)$_POST['sukan_id'] : 0;
	$kategori_id = isset($_POST['kategori_id']) ? (int)$_POST['kategori_id'] : 0;
	$nama_event = isset($_POST['nama_event']) ? trim($_POST['nama_event']) : '';
	$tarikh_mula = !empty($_POST['tarikh_mula']) ? trim($_POST['tarikh_mula']) : null;
	$tarikh_tamat = !empty($_POST['tarikh_tamat']) ? trim($_POST['tarikh_tamat']) : null;
	// table_event.status enum: ('ongoing','completed','cancelled')
	$allowed_status = ['ongoing','completed','cancelled'];
	$status = isset($_POST['status']) && in_array($_POST['status'], $allowed_status) ? $_POST['status'] : 'ongoing';
	// determine if updating existing event
	$event_id = isset($_SESSION['current_event_id']) ? (int)$_SESSION['current_event_id'] : 0;
	if (!$event_id && isset($_POST['event_id'])) { $event_id = (int)$_POST['event_id']; }

	if ($sukan_id <= 0) { $errors[] = 'Sukan diperlukan.'; }
	if ($kategori_id <= 0) { $errors[] = 'Kategori/Acara diperlukan.'; }
	if ($nama_event === '') { $errors[] = 'Nama Event diperlukan.'; }

	if (!empty($errors)) {
		echo json_encode(['success' => false, 'errors' => $errors]);
		exit;
	}

	try {
		$db = getDB();
		// enforce uniqueness: check if another event exists with this sukan+kategori
		$uniqStmt = $db->prepare('SELECT id FROM table_event WHERE sukan_id = :sukan_id AND kategori_id = :kategori_id AND deleted_at IS NULL LIMIT 1');
		$uniqStmt->execute([':sukan_id' => $sukan_id, ':kategori_id' => $kategori_id]);
		$existing = $uniqStmt->fetch(PDO::FETCH_ASSOC);

		if ($event_id > 0) {
			// UPDATE mode: ensure existing event is either same id or not present
			if ($existing && (int)$existing['id'] !== $event_id) {
				echo json_encode(['success' => false, 'errors' => ['Satu event dengan kombinasi Sukan + Kategori sudah wujud.']]);
				exit;
			}
			$upd = $db->prepare('UPDATE table_event SET sukan_id = :sukan_id, kategori_id = :kategori_id, nama_event = :nama_event, tarikh_mula = :tarikh_mula, tarikh_tamat = :tarikh_tamat, status = :status, updated_at = NOW() WHERE id = :id');
			$upd->execute([
				':sukan_id' => $sukan_id,
				':kategori_id' => $kategori_id,
				':nama_event' => $nama_event,
				':tarikh_mula' => $tarikh_mula,
				':tarikh_tamat' => $tarikh_tamat,
				':status' => $status,
				':id' => $event_id,
			]);
			$_SESSION['current_event_id'] = $event_id;
			echo json_encode(['success' => true, 'event_id' => $event_id, 'mode' => 'update']);
			exit;
		} else {
			// CREATE mode: prevent duplicate
			if ($existing) {
				echo json_encode(['success' => false, 'errors' => ['Satu event dengan kombinasi Sukan + Kategori sudah wujud.'], 'event_id' => (int)$existing['id']]);
				exit;
			}
			$sql = "INSERT INTO table_event (sukan_id, kategori_id, nama_event, tarikh_mula, tarikh_tamat, status, created_at)
					VALUES (:sukan_id, :kategori_id, :nama_event, :tarikh_mula, :tarikh_tamat, :status, NOW())";
			$stmt = $db->prepare($sql);
			$stmt->execute([
				':sukan_id' => $sukan_id,
				':kategori_id' => $kategori_id,
				':nama_event' => $nama_event,
				':tarikh_mula' => $tarikh_mula,
				':tarikh_tamat' => $tarikh_tamat,
				':status' => $status,
			]);
			$event_id = (int)$db->lastInsertId();
			// store in PHP session
			$_SESSION['current_event_id'] = $event_id;

			echo json_encode(['success' => true, 'event_id' => $event_id, 'mode' => 'create']);
			exit;
		}
	} catch (Exception $e) {
		error_log('[setup-pertandingan save_tab1] ' . $e->getMessage());
		echo json_encode(['success' => false, 'errors' => ['Gagal menyimpan rekod.'], 'debug' => $e->getMessage()]);
		exit;
	}
}

// --- End server-side save handler ---

// --- Server-side: handle AJAX save for TAB2 (Struktur Kumpulan) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_tab2') {
	header('Content-Type: application/json; charset=utf-8');
 	$errors = [];
 	// event_id should be in session
 	$event_id = isset($_SESSION['current_event_id']) ? (int)$_SESSION['current_event_id'] : 0;
 	if ($event_id <= 0 && isset($_POST['event_id'])) {
 		$event_id = (int)$_POST['event_id'];
 	}

 	$bilangan = isset($_POST['bilangan_kumpulan']) ? (int)$_POST['bilangan_kumpulan'] : 0;
 	$format = isset($_POST['format_kumpulan']) ? $_POST['format_kumpulan'] : 'alphabetical';
 	$group_codes_json = isset($_POST['group_codes']) ? $_POST['group_codes'] : null;
 	$qualification_topn = isset($_POST['qualification_topn']) && $_POST['qualification_topn'] !== '' ? (int)$_POST['qualification_topn'] : null;
 	$qualification_criteria = isset($_POST['qualification_criteria']) && $_POST['qualification_criteria'] !== '' ? $_POST['qualification_criteria'] : null;

 	if ($event_id <= 0) { $errors[] = 'Event tidak ditemui.'; }
 	if ($bilangan <= 0) { $errors[] = 'Bilangan Kumpulan mesti lebih besar dari 0.'; }

 	// parse group codes from client if provided
 	$group_codes = [];
 	if ($group_codes_json) {
 		$decoded = json_decode($group_codes_json, true);
 		if (is_array($decoded)) {
 			$group_codes = $decoded;
 		}
 	}

 	if (count($group_codes) > 0 && count($group_codes) !== $bilangan) {
 		$errors[] = 'Bilangan kod kumpulan tidak sepadan.';
 	}

 	if (!empty($errors)) {
 		echo json_encode(['success' => false, 'errors' => $errors]);
 		exit;
 	}

 	try {
 		$db = getDB();

 		// fetch existing rounds for this event (Peringkat Kumpulan)
 		$existingStmt = $db->prepare("SELECT id, group_code, group_order FROM table_round WHERE event_id = :event_id AND nama_round = 'Peringkat Kumpulan' AND deleted_at IS NULL ORDER BY group_order ASC");
 		$existingStmt->execute([':event_id' => $event_id]);
 		$existingRounds = $existingStmt->fetchAll(PDO::FETCH_ASSOC);

 		// build qualification_rule JSON if provided
 		$qualification_rule = null;
 		if ($qualification_topn !== null && $qualification_topn > 0 && in_array($qualification_criteria, ['mata','score','masa'])) {
 			$qualification_rule = json_encode([
 				'type' => 'TOP_N',
 				'value' => $qualification_topn,
 				'criteria' => $qualification_criteria
 			]);
 		}

 		// if client didn't send codes, generate here (fallback)
 		if (empty($group_codes)) {
 			for ($i = 0; $i < $bilangan; $i++) {
 				if ($format === 'numeric') {
 					$group_codes[] = (string)($i + 1);
 				} else {
 					$group_codes[] = chr(65 + $i);
 				}
 			}
 		}

 		// ensure provided codes are unique among themselves
 		if (count($group_codes) !== count(array_unique($group_codes))) {
 			echo json_encode(['success' => false, 'errors' => ['Kod kumpulan mengandungi duplikasi.']]);
 			exit;
 		}

 		// If existing rounds exist -> EDIT MODE: update existing rows (do NOT insert new rows)
 		if (!empty($existingRounds)) {
 			$existingCount = count($existingRounds);
 			// require bilangan to match existing count to avoid accidental inserts/deletes
 			if ($existingCount !== $bilangan) {
 				echo json_encode(['success' => false, 'errors' => ['Bilangan Kumpulan mesti sama seperti struktur sedia ada (' . $existingCount . '). Untuk mengubah bilangan, hapus struktur sedia ada terlebih dahulu.']]);
 				exit;
 			}

 			$db->beginTransaction();
 			$upd = $db->prepare('UPDATE table_round SET group_code = :group_code, group_order = :group_order, qualification_rule = :qualification_rule, updated_at = NOW() WHERE id = :id');
 			foreach ($existingRounds as $i => $row) {
 				$code = $group_codes[$i];
 				$group_order = $i + 1;
 				$upd->execute([
 					':group_code' => $code,
 					':group_order' => $group_order,
 					':qualification_rule' => $qualification_rule,
 					':id' => (int)$row['id']
 				]);
 			}
 			$db->commit();

 			echo json_encode(['success' => true, 'mode' => 'update']);
 			exit;
 		} else {
 			// CREATE MODE: ensure no rounds exist and insert
 			// ensure no existing group_code conflict for same event (should be none)
 			$chkCodes = $db->prepare("SELECT group_code FROM table_round WHERE event_id = :event_id AND nama_round = 'Peringkat Kumpulan' AND deleted_at IS NULL");
 			$chkCodes->execute([':event_id' => $event_id]);
 			$existingCodes = $chkCodes->fetchAll(PDO::FETCH_COLUMN, 0);
 			if (!empty($existingCodes)) {
 				echo json_encode(['success' => false, 'errors' => ['Struktur kumpulan telah wujud untuk event ini.']]);
 				exit;
 			}

 			$db->beginTransaction();
 			$insert = $db->prepare("INSERT INTO table_round (event_id, nama_round, group_code, group_order, round_order, qualification_rule, status, created_at) VALUES (:event_id, :nama_round, :group_code, :group_order, :round_order, :qualification_rule, :status, NOW())");
 			$nama_round = 'Peringkat Kumpulan';
 			$round_order = 1;
 			$status = 'pending';
 			for ($i = 0; $i < $bilangan; $i++) {
 				$code = isset($group_codes[$i]) ? $group_codes[$i] : ($format === 'numeric' ? (string)($i + 1) : chr(65 + $i));
 				$group_order = $i + 1;
 				$insert->execute([
 					':event_id' => $event_id,
 					':nama_round' => $nama_round,
 					':group_code' => $code,
 					':group_order' => $group_order,
 					':round_order' => $round_order,
 					':qualification_rule' => $qualification_rule,
 					':status' => $status
 				]);
 			}
 			$db->commit();

 			echo json_encode(['success' => true, 'mode' => 'create']);
 			exit;
 		}

 	} catch (Exception $e) {
 		if ($db && $db->inTransaction()) { $db->rollBack(); }
 		error_log('[setup-pertandingan save_tab2] ' . $e->getMessage());
 		echo json_encode(['success' => false, 'errors' => ['Gagal menyimpan struktur kumpulan.'], 'debug' => $e->getMessage()]);
 		exit;
 	}
}

// --- End server-side save handler for TAB2 ---
// --- Server-side: handle AJAX save for TAB3 (Agihan Pasukan) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_tab3') {
	header('Content-Type: application/json; charset=utf-8');
	$errors = [];
	$event_id = isset($_SESSION['current_event_id']) ? (int)$_SESSION['current_event_id'] : 0;
	if ($event_id <= 0 && isset($_POST['event_id'])) { $event_id = (int)$_POST['event_id']; }

	$assignments_json = isset($_POST['assignments']) ? $_POST['assignments'] : null;
	$assignments = [];
	if ($assignments_json) {
		$decoded = json_decode($assignments_json, true);
		if (is_array($decoded)) $assignments = $decoded;
	}

	if ($event_id <= 0) { $errors[] = 'Event tidak ditemui.'; }
	if (empty($assignments)) { $errors[] = 'Tiada pasukan ditetapkan ke kumpulan.'; }

	// ensure assignments keys are unique team ids
	$teamIds = array_keys($assignments);
	if (count($teamIds) !== count(array_unique($teamIds))) {
		$errors[] = 'Duplikasi pasukan dalam senarai penetapan.';
	}

	if (!empty($errors)) {
		echo json_encode(['success' => false, 'errors' => $errors]);
		exit;
	}

	try {
		$db = getDB();
		// fetch event sukan_id for validation
		$stmt = $db->prepare('SELECT sukan_id FROM table_event WHERE id = :id AND deleted_at IS NULL');
		$stmt->execute([':id' => $event_id]);
		$ev = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$ev) {
			echo json_encode(['success' => false, 'errors' => ['Event tidak wujud.']]);
			exit;
		}
		$sukan_id = (int)$ev['sukan_id'];

		// verify rounds exist for event
		$rchk = $db->prepare("SELECT COUNT(*) AS c FROM table_round WHERE event_id = :event_id AND nama_round = 'Peringkat Kumpulan' AND deleted_at IS NULL");
		$rchk->execute([':event_id' => $event_id]);
		$rres = $rchk->fetch(PDO::FETCH_ASSOC);
		if (!$rres || (int)$rres['c'] === 0) {
			echo json_encode(['success' => false, 'errors' => ['Struktur kumpulan tidak dijumpai untuk event ini.']]);
			exit;
		}

		// validate teams belong to this sukan and are active
		$placeholders = implode(',', array_fill(0, count($teamIds), '?'));
		$vstmt = $db->prepare("SELECT id FROM table_pasukan WHERE id IN ($placeholders) AND sukan_id = ? AND status = 1 AND deleted_at IS NULL");
		$params = $teamIds; $params[] = $sukan_id;
		$vstmt->execute($params);
		$valid = $vstmt->fetchAll(PDO::FETCH_COLUMN, 0);
		if (count($valid) !== count($teamIds)) {
			echo json_encode(['success' => false, 'errors' => ['Beberapa pasukan tidak sah untuk event ini.']]);
			exit;
		}

		// perform updates in transaction
		$db->beginTransaction();
		$ustmt = $db->prepare('UPDATE table_pasukan SET initial_group_code = :code WHERE id = :id AND sukan_id = :sukan_id AND deleted_at IS NULL');
		foreach ($assignments as $tid => $code) {
			$ustmt->execute([':code' => $code, ':id' => (int)$tid, ':sukan_id' => $sukan_id]);
		}
		$db->commit();

		echo json_encode(['success' => true]);
		exit;
	} catch (Exception $e) {
		if ($db && $db->inTransaction()) $db->rollBack();
		error_log('[setup-pertandingan save_tab3] ' . $e->getMessage());
		echo json_encode(['success' => false, 'errors' => ['Gagal menyimpan agihan pasukan.'], 'debug' => $e->getMessage()]);
		exit;
	}
}

// --- End server-side save handler for TAB3 ---

// Fetch sukan list for the Sukan select
$sukan_list = [];
try {
	$db = getDB();
	$stmt = $db->prepare('SELECT id, nama_sukan FROM table_sukan WHERE status = 1 ORDER BY nama_sukan');
	$stmt->execute();
	$sukan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
	error_log('[setup-pertandingan] fetch sukan error: ' . $e->getMessage());
	$sukan_list = [];
}

// Fetch current event and related data if available
$event_id = isset($_SESSION['current_event_id']) ? (int)$_SESSION['current_event_id'] : 0;
$event_sukan_id = null;
$rounds = [];
$teams = [];
$kontinjen_list = [];
try {
	if ($event_id > 0) {
		$stmt = $db->prepare('SELECT id, sukan_id FROM table_event WHERE id = :id AND deleted_at IS NULL');
		$stmt->execute([':id' => $event_id]);
		$ev = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($ev) {
			$event_sukan_id = (int)$ev['sukan_id'];
			// fetch rounds (Peringkat Kumpulan)
			$rstmt = $db->prepare("SELECT id, group_code, group_order FROM table_round WHERE event_id = :event_id AND nama_round = 'Peringkat Kumpulan' AND deleted_at IS NULL ORDER BY group_order ASC");
			$rstmt->execute([':event_id' => $event_id]);
			$rounds = $rstmt->fetchAll(PDO::FETCH_ASSOC);

			// fetch teams for this sukan (include initial_group_code and kontingen + university short name)
			try {
				$tstmt = $db->prepare("SELECT p.id, p.nama_pasukan, p.kontinjen_id, p.initial_group_code, k.nama_kontinjen, k.kod_universiti, r.nama_pendek AS universiti_pendek FROM table_pasukan p LEFT JOIN table_kontinjen k ON p.kontinjen_id = k.id LEFT JOIN table_ref_universiti r ON k.kod_universiti = r.kod_universiti WHERE p.sukan_id = :sukan_id AND p.status = 1 AND p.deleted_at IS NULL ORDER BY p.nama_pasukan ASC");
				$tstmt->execute([':sukan_id' => $event_sukan_id]);
				$teams = $tstmt->fetchAll(PDO::FETCH_ASSOC);
			} catch (Exception $e) {
				// If the join fails due to column name differences in table_kontinjen, attempt to detect the correct column and retry
				error_log('[setup-pertandingan] teams query failed, attempting fallback: ' . $e->getMessage());
				try {
					$cols = [];
					$cstmt = $db->query("SHOW COLUMNS FROM table_kontinjen");
					$cols = $cstmt->fetchAll(PDO::FETCH_COLUMN, 0);
					$kontinjenNameCol = null;
					$candidates = ['nama_kontinjen', 'nama_kontingen', 'nama', 'nama_kontinjen_full'];
					foreach ($candidates as $cand) {
						if (in_array($cand, $cols)) { $kontinjenNameCol = $cand; break; }
					}
					if (!$kontinjenNameCol && count($cols) > 0) {
						// fallback to first non-id column
						foreach ($cols as $col) {
							if (!in_array($col, ['id','kod_universiti','created_at','updated_at','deleted_at'])) { $kontinjenNameCol = $col; break; }
						}
					}
					if ($kontinjenNameCol) {
						$sql = "SELECT p.id, p.nama_pasukan, p.kontinjen_id, p.initial_group_code, k." . $kontinjenNameCol . " AS nama_kontinjen, k.kod_universiti, r.nama_pendek AS universiti_pendek FROM table_pasukan p LEFT JOIN table_kontinjen k ON p.kontinjen_id = k.id LEFT JOIN table_ref_universiti r ON k.kod_universiti = r.kod_universiti WHERE p.sukan_id = :sukan_id AND p.status = 1 AND p.deleted_at IS NULL ORDER BY p.nama_pasukan ASC";
						$tstmt = $db->prepare($sql);
						$tstmt->execute([':sukan_id' => $event_sukan_id]);
						$teams = $tstmt->fetchAll(PDO::FETCH_ASSOC);
						error_log('[setup-pertandingan] teams fallback used column: ' . $kontinjenNameCol . ' returned=' . count($teams));
					} else {
						error_log('[setup-pertandingan] cannot determine kontingen name column; aborting teams fetch.');
						$teams = [];
					}
				} catch (Exception $e2) {
					error_log('[setup-pertandingan] teams fallback failed: ' . $e2->getMessage());
					$teams = [];
				}
			}
			// server-side debug logs when debug=1 or force_event present
			if ((isset($_GET['debug']) && $_GET['debug'] === '1') || isset($_GET['force_event'])) {
				error_log('[setup-pertandingan debug] fetched teams count=' . count($teams) . ' for sukan_id=' . $event_sukan_id);
				// also check total rows ignoring status/deleted filters to detect data-state issues
				$ck = $db->prepare('SELECT COUNT(*) AS c FROM table_pasukan WHERE sukan_id = :sukan_id');
				$ck->execute([':sukan_id' => $event_sukan_id]);
				$cc = $ck->fetch(PDO::FETCH_ASSOC);
				error_log('[setup-pertandingan debug] total pasukan for sukan_id=' . $event_sukan_id . ' => ' . ($cc['c'] ?? '0'));
				error_log('[setup-pertandingan debug] sample teams: ' . substr(var_export(array_slice($teams,0,10), true),0,1000));
			}

			// fetch kontingen list for optional filter
			$kstmt = $db->prepare('SELECT id, nama_kontinjen FROM table_kontinjen WHERE deleted_at IS NULL ORDER BY nama_kontinjen ASC');
			$kstmt->execute();
			$kontinjen_list = $kstmt->fetchAll(PDO::FETCH_ASSOC);
		}
	}
} catch (Exception $e) {
	error_log('[setup-pertandingan] fetch event/rounds/teams error: ' . $e->getMessage());
}

ob_start();
?>
<div class="container-fluid px-3">
	<?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
		<?php
			$dbg_db = null; $dbg_user = null;
			try {
				$tmpdb = getDB();
				$r1 = $tmpdb->query('SELECT DATABASE() AS db')->fetch(PDO::FETCH_ASSOC);
				$r2 = $tmpdb->query('SELECT CURRENT_USER() AS user')->fetch(PDO::FETCH_ASSOC);
				$dbg_db = $r1['db'] ?? null;
				$dbg_user = $r2['user'] ?? null;
			} catch (Exception $e) {
				error_log('[setup-pertandingan debug] cannot fetch DB name: ' . $e->getMessage());
			}
		?>
		<div class="alert alert-info small">
			<strong>Debug</strong>: event_id=<?php echo (int)$event_id; ?>, sukan_id=<?php echo htmlspecialchars($event_sukan_id); ?>, rounds=<?php echo (int)count($rounds); ?>, teams=<?php echo (int)count($teams); ?>, db=<?php echo htmlspecialchars($dbg_db); ?>, db_user=<?php echo htmlspecialchars($dbg_user); ?>
			<br>
			<?php if (!empty($teams)): ?>Sample team ids: <?php echo implode(',', array_map(function($t){return (int)$t['id'];}, array_slice($teams,0,10))); ?><?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="row mb-3">
		<div class="col-12">
			<h2 class="mb-1">Setup Pertandingan</h2>
			<p class="text-muted mb-0">Langkah 1: Maklumat Pertandingan</p>
		</div>
	</div>

	<div class="card">
		<div class="card-body">
			<ul class="nav nav-pills mb-3" id="setupTabs" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="tab-1-btn" data-bs-toggle="pill" data-bs-target="#tab-1" type="button" role="tab">1. Maklumat Pertandingan</button>
				</li>
				<?php $has_event = !empty($_SESSION['current_event_id']); ?>
				<li class="nav-item" role="presentation">
					<button class="nav-link <?php echo $has_event ? '' : 'disabled'; ?>" id="tab-2-btn" <?php echo $has_event ? 'data-bs-toggle="pill" data-bs-target="#tab-2"' : 'aria-disabled="true"'; ?> type="button">2. Struktur Kumpulan</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link disabled" id="tab-3-btn" aria-disabled="true" type="button">3. Agihan Pasukan</button>
				</li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane show active" id="tab-1" role="tabpanel">
					<form id="form-tab1" method="post">
						<input type="hidden" name="action" value="save_tab1">
						<div class="row">
							<div class="col-md-6">
								<h5>Maklumat Asas</h5>
								<div class="mb-3">
									<label class="form-label">Sukan <span class="text-danger">*</span></label>
									<select id="sukan_id" name="sukan_id" class="form-select" required>
										<option value="">-- Pilih Sukan --</option>
										<?php foreach ($sukan_list as $s): ?>
											<option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['nama_sukan'], ENT_QUOTES, 'UTF-8'); ?></option>
										<?php endforeach; ?>
									</select>
								</div>

								<div class="mb-3">
									<label class="form-label">Kategori / Acara <span class="text-danger">*</span></label>
									<select id="kategori_id" name="kategori_id" class="form-select" required>
										<option value="">-- Pilih Kategori --</option>
									</select>
									<div id="kategori-help" class="form-text text-danger small d-none"></div>
								</div>

								<div class="mb-3">
									<label class="form-label">Nama Event <span class="text-danger">*</span></label>
									<input id="nama_event" name="nama_event" type="text" class="form-control" required>
								</div>

								<div class="mb-3">
									<label class="form-label">Tarikh Mula</label>
									<input name="tarikh_mula" type="date" class="form-control">
								</div>

								<div class="mb-3">
									<label class="form-label">Tarikh Tamat</label>
									<input name="tarikh_tamat" type="date" class="form-control">
								</div>

									<div class="mb-3">
										<label class="form-label">Status</label>
										<select name="status" class="form-select">
											<option value="ongoing" selected>ongoing</option>
											<option value="completed">completed</option>
											<option value="cancelled">cancelled</option>
										</select>
									</div>

								<div class="mb-3">
									<button id="save-and-continue" type="submit" class="btn btn-primary">Simpan & Teruskan</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			<div class="tab-pane" id="tab-2" role="tabpanel">
					<form id="form-tab2" method="post">
						<input type="hidden" name="action" value="save_tab2">
						<div class="row">
							<div class="col-md-6">
								<h5>Struktur Kumpulan</h5>
								<div class="mb-3">
									<label class="form-label">Nama Round</label>
									<select class="form-select" id="nama_round" name="nama_round" disabled>
										<option>Peringkat Kumpulan</option>
									</select>
								</div>

								<div class="mb-3">
									<label class="form-label">Bilangan Kumpulan <span class="text-danger">*</span></label>
									<input id="bilangan_kumpulan" name="bilangan_kumpulan" type="number" min="1" class="form-control" required value="4">
								</div>

								<div class="mb-3">
									<label class="form-label">Format Kumpulan</label>
									<select id="format_kumpulan" name="format_kumpulan" class="form-select">
										<option value="alphabetical">Alphabetical (A, B, C)</option>
										<option value="numeric">Numeric (1, 2, 3)</option>
									</select>
								</div>

								<h6>Peraturan Kelayakan (optional)</h6>
								<div class="mb-3">
									<label class="form-label">Top N Lulus</label>
									<input id="qualification_topn" name="qualification_topn" type="number" min="1" class="form-control">
								</div>
								<div class="mb-3">
									<label class="form-label">Kriteria</label>
									<select id="qualification_criteria" name="qualification_criteria" class="form-select">
										<option value="">-- Tiada --</option>
										<option value="mata">mata</option>
										<option value="score">score</option>
										<option value="masa">masa</option>
									</select>
								</div>

								<div class="mb-3">
									<button id="save-groups" type="submit" class="btn btn-primary">Simpan Group</button>
								</div>
							</div>

							<div class="col-md-6">
								<h5>Preview Kumpulan</h5>
								<div id="group-preview-area">
									<table class="table table-sm table-bordered" id="group-preview-table">
										<thead>
											<tr><th>Group</th><th>Nama Round</th><th>Order</th></tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
							</div>
						</div>
					</form>
				</div>

			<div class="tab-pane" id="tab-3" role="tabpanel">
					<div class="row">
						<div class="col-md-6">
							<h5>Senarai Pasukan</h5>
							<div class="small text-muted mb-2">Debug: event_id=<?php echo (int)$event_id; ?> sukan_id=<?php echo htmlspecialchars($event_sukan_id); ?> | teams=<?php echo (int)count($teams); ?> <?php if (!empty($teams)) echo ' sample_ids=' . implode(',', array_map(function($t){return (int)$t['id'];}, array_slice($teams,0,5))); ?></div>
							<div class="mb-2">
								<div class="d-flex gap-2 mb-2">
									<input id="search-team" class="form-control" placeholder="Cari nama pasukan">
								</div>
								<div id="kontinjen-filters" class="d-flex flex-wrap gap-2">
									<button type="button" class="btn btn-sm btn-outline-secondary active" data-kontinjen-id="">Semua Kontinjen</button>
									<?php foreach ($kontinjen_list as $k): ?>
										<button type="button" class="btn btn-sm btn-outline-secondary" data-kontinjen-id="<?php echo (int)$k['id']; ?>"><?php echo htmlspecialchars($k['nama_kontinjen'], ENT_QUOTES, 'UTF-8'); ?></button>
									<?php endforeach; ?>
								</div>
							</div>
							<div style="max-height:420px;overflow:auto;">
								<table class="table table-sm" id="teams-table">
									<thead><tr><th><input type="checkbox" id="select-all-teams"></th><th>Nama Pasukan</th><th>Kontinjen</th></tr></thead>
									<tbody>
										<?php foreach ($teams as $t): ?>
											<?php $assigned = isset($t['initial_group_code']) ? $t['initial_group_code'] : ''; ?>
											<tr data-team-id="<?php echo (int)$t['id']; ?>" data-kontinjen-id="<?php echo (int)$t['kontinjen_id']; ?>" data-assigned-group="<?php echo htmlspecialchars($assigned, ENT_QUOTES, 'UTF-8'); ?>">
												<td><input type="checkbox" class="team-checkbox"></td>
												<td class="team-name"><?php echo htmlspecialchars($t['nama_pasukan'], ENT_QUOTES, 'UTF-8'); ?></td>
												<td><?php echo htmlspecialchars($t['universiti_pendek'] ?? $t['kod_universiti'] ?? $t['nama_kontinjen'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
								<div id="teams-empty-msg" class="small text-muted text-center mt-2 d-none">Tiada pasukan ditemui.</div>
							</div>
							<div class="mt-2 d-flex gap-2 align-items-center">
								<label class="form-label mb-0 pe-2">Assign ke Kumpulan</label>
								<select id="assign-group-select" class="form-select" style="max-width:200px;">
									<option value="">-- Pilih Kumpulan --</option>
									<?php foreach ($rounds as $r): ?>
										<option value="<?php echo htmlspecialchars($r['group_code'], ENT_QUOTES, 'UTF-8'); ?>">Group <?php echo htmlspecialchars($r['group_code'], ENT_QUOTES, 'UTF-8'); ?></option>
									<?php endforeach; ?>
								</select>
								<button id="assign-btn" class="btn btn-secondary" <?php echo empty($rounds) ? 'disabled' : ''; ?>>Assign</button>
							</div>
						</div>

						<div class="col-md-6">
							<h5>Kumpulan</h5>
							<div id="groups-container" class="d-flex flex-wrap gap-2">
								<?php
									// build lookup of teams by initial_group_code
									$teamsByGroup = [];
									foreach ($teams as $t) {
										$g = trim((string)($t['initial_group_code'] ?? ''));
										if ($g === '') continue;
										if (!isset($teamsByGroup[$g])) $teamsByGroup[$g] = [];
										$teamsByGroup[$g][] = $t;
									}
								?>
								<?php foreach ($rounds as $r): ?>
									<?php $gcode = htmlspecialchars($r['group_code'], ENT_QUOTES, 'UTF-8'); ?>
									<div class="card" style="width:200px;">
										<div class="card-body p-2">
											<h6 class="card-title mb-2">Group <?php echo $gcode; ?></h6>
											<ul class="list-group list-group-flush group-list" data-group-code="<?php echo $gcode; ?>">
												<?php if (isset($teamsByGroup[$r['group_code']])): foreach ($teamsByGroup[$r['group_code']] as $pt): ?>
													<li class="list-group-item p-1" data-team-id="<?php echo (int)$pt['id']; ?>"><?php echo htmlspecialchars($pt['nama_pasukan'], ENT_QUOTES, 'UTF-8'); ?></li>
												<?php endforeach; endif; ?>
											</ul>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
							<div class="mt-3">
								<div id="tab3-warning" class="text-danger small mb-2 <?php echo empty($rounds) ? '' : 'd-none'; ?>">
									<?php if (empty($rounds)): ?>Tiada kumpulan dicipta untuk event ini. Sila lengkapkan Struktur Kumpulan (Tab 2) terlebih dahulu.<?php endif; ?>
								</div>
								<button id="save-assignment" class="btn btn-primary" <?php echo empty($rounds) ? 'disabled' : ''; ?>>Simpan Agihan Pasukan</button>
							</div>
						</div>
					</div>
				</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(() => {
	// TAB3: Assign teams to groups
	const teamsTable = document.getElementById('teams-table');
	const searchInput = document.getElementById('search-team');
	const filterKont = document.getElementById('filter-kontinjen');
	const selectAll = document.getElementById('select-all-teams');
	const assignSelect = document.getElementById('assign-group-select');
	const assignBtn = document.getElementById('assign-btn');
	const groupsContainer = document.getElementById('groups-container');
	const saveBtn = document.getElementById('save-assignment');

	function getCheckedTeamRows() {
		return Array.from(document.querySelectorAll('.team-checkbox')).filter(c => c.checked).map(c => c.closest('tr'));
	}

	function renderAssigned() {
		// clear groups
		document.querySelectorAll('.group-list').forEach(ul => ul.innerHTML = '');
		// find all rows and check for assigned data attribute
		document.querySelectorAll('#teams-table tbody tr').forEach(tr => {
			const assigned = tr.getAttribute('data-assigned-group');
			const tid = tr.getAttribute('data-team-id');
			const name = tr.querySelector('.team-name')?.textContent || '';
			if (assigned) {
				const ul = document.querySelector('.group-list[data-group-code="' + assigned + '"]');
				if (ul) {
					const li = document.createElement('li');
					li.className = 'list-group-item p-1';
					li.textContent = name;
					li.setAttribute('data-team-id', tid);
					ul.appendChild(li);
				}
			}
		});
	}

	// search/filter (more robust)
	function getSelectedKontinjenId() {
		const container = document.getElementById('kontinjen-filters');
		if (!container) return '';
		const active = container.querySelector('button.active');
		return active ? (active.getAttribute('data-kontinjen-id') || '') : '';
	}

	function doTeamSearch() {
		const q = (searchInput.value || '').toString().trim().toLowerCase();
		const kfilter = getSelectedKontinjenId();
		let visible = 0;
		document.querySelectorAll('#teams-table tbody tr').forEach(tr => {
			const name = (tr.querySelector('.team-name')?.textContent || '').toLowerCase();
			const kont = (tr.querySelector('td:nth-child(3)')?.textContent || '').toLowerCase();
			const assigned = (tr.getAttribute('data-assigned-group') || '').toLowerCase();
			const matchesText = q === '' || name.indexOf(q) !== -1 || kont.indexOf(q) !== -1 || assigned.indexOf(q) !== -1;
			const matchesKont = (kfilter === '' || tr.getAttribute('data-kontinjen-id') === kfilter);
			const visibleRow = matchesText && matchesKont;
			tr.style.display = visibleRow ? '' : 'none';
			if (visibleRow) visible++;
		});
		const emptyMsg = document.getElementById('teams-empty-msg');
		if (emptyMsg) emptyMsg.classList.toggle('d-none', visible > 0);
	}

	if (searchInput) {
		searchInput.addEventListener('input', doTeamSearch);
		searchInput.addEventListener('keyup', doTeamSearch);
	}
	if (filterKont) {
		// delegated click: toggle active kontinjen button and re-run search
		filterKont.addEventListener('click', function (ev) {
			const btn = ev.target.closest('button[data-kontinjen-id]');
			if (!btn) return;
			// remove active from others
			filterKont.querySelectorAll('button').forEach(b => b.classList.remove('active'));
			btn.classList.add('active');
			doTeamSearch();
		});
	}

	if (selectAll) {
		selectAll.addEventListener('change', function () {
			const checked = this.checked;
			document.querySelectorAll('.team-checkbox').forEach(cb => { cb.checked = checked; });
		});
	}

	if (assignBtn) {
		assignBtn.addEventListener('click', function () {
			const group = assignSelect.value;
			if (!group) { Swal.fire({ icon: 'warning', title: 'Pilih kumpulan', text: 'Sila pilih kumpulan untuk assign.' }); return; }
			const rows = getCheckedTeamRows();
			if (rows.length === 0) { Swal.fire({ icon: 'warning', title: 'Tiada pasukan', text: 'Sila pilih sekurang-kurangnya satu pasukan.' }); return; }
			rows.forEach(tr => {
				tr.setAttribute('data-assigned-group', group);
				tr.querySelector('.team-checkbox').checked = false; // uncheck after assigning
			});
			if (selectAll) selectAll.checked = false;
			renderAssigned();
		});
	}

	if (saveBtn) {
		saveBtn.addEventListener('click', async function () {
			// collect assignments
			const assignments = {};
			document.querySelectorAll('#teams-table tbody tr').forEach(tr => {
				const gid = tr.getAttribute('data-assigned-group');
				if (gid) assignments[tr.getAttribute('data-team-id')] = gid;
			});
			const keys = Object.keys(assignments);
			if (keys.length === 0) { Swal.fire({ icon: 'warning', title: 'Tiada pasukan', text: 'Sila assign sekurang-kurangnya satu pasukan.' }); return; }

			const fd = new FormData();
			fd.append('action', 'save_tab3');
			fd.append('assignments', JSON.stringify(assignments));
			fd.append('event_id', '<?php echo $event_id; ?>');
			try {
				saveBtn.disabled = true; saveBtn.textContent = 'Menyimpan...';
				const res = await fetch('', { method: 'POST', body: fd });
				const json = await res.json();
				if (json.success) {
					Swal.fire({ icon: 'success', title: 'Berjaya', text: 'Agihan Pasukan disimpan.' });
				} else {
					const msg = (json.errors || ['Gagal menyimpan']).join('<br>');
					Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
				}
			} catch (e) {
				console.error(e);
				Swal.fire({ icon: 'error', title: 'Ralat', text: 'Ralat jaringan semasa menyimpan.' });
			} finally {
				saveBtn.disabled = false; saveBtn.textContent = 'Simpan Agihan Pasukan';
			}
		});
	}

	// initial render of any existing assignments (if teams have initial_group_code attribute rendered, we could map)
	renderAssigned();
	})();

	(() => {
		// TAB1 JS: categories, check existing event, create/update submit
		const sukanSelect = document.getElementById('sukan_id');
		const kategoriSelect = document.getElementById('kategori_id');
		const namaInput = document.getElementById('nama_event');
		const form1 = document.getElementById('form-tab1');
		const saveBtn = document.getElementById('save-and-continue');
		let namaEdited = false;

		async function loadKategoriForSukan(sukanId) {
			kategoriSelect.innerHTML = '<option value="">-- Pilih Kategori --</option>';
			if (!sukanId) return;
			try {
				const endpoint = '<?php echo url("ajax/get_kategori.php"); ?>?sukan_id=' + encodeURIComponent(sukanId);
				console.log('[setup-pertandingan] fetching kategori ->', endpoint);
				const res = await fetch(endpoint, { credentials: 'same-origin' });
				console.log('[setup-pertandingan] kategori HTTP status', res.status);
				const text = await res.text();
				console.log('[setup-pertandingan] kategori response text', text);
				let data = null;
				try {
					data = JSON.parse(text);
				} catch (e) {
					console.error('Invalid JSON from kategori endpoint', text);
					const help = document.getElementById('kategori-help');
					if (help) { help.classList.remove('d-none'); help.textContent = 'Gagal memuatkan kategori (respons tidak sah). Sila cuba semula.'; }
					return;
				}
				if (Array.isArray(data)) {
					const help = document.getElementById('kategori-help');
					if (help) { help.classList.add('d-none'); help.textContent = ''; }
					data.forEach(k => {
						const opt = document.createElement('option');
						opt.value = k.id;
						opt.textContent = k.nama_kategori;
						kategoriSelect.appendChild(opt);
					});
					if (data.length === 0) {
						const help = document.getElementById('kategori-help');
						if (help) { help.classList.remove('d-none'); help.textContent = 'Tiada kategori untuk sukan ini.'; }
					}
				}
			} catch (e) { console.error('Failed to load kategori', e); const help = document.getElementById('kategori-help'); if (help) { help.classList.remove('d-none'); help.textContent = 'Ralat sambungan ketika memuatkan kategori.'; } }
		}

		async function checkEvent(sukanId, kategoriId) {
			try {
				const res = await fetch('?action=check_event&sukan_id=' + encodeURIComponent(sukanId) + '&kategori_id=' + encodeURIComponent(kategoriId));
				const j = await res.json();
				return j;
			} catch (e) { console.error('check_event failed', e); return null; }
		}

		sukanSelect.addEventListener('change', async function () {
			const sukanId = this.value;
			await loadKategoriForSukan(sukanId);
			kategoriSelect.value = '';
			if (!namaEdited) namaInput.value = '';
		});

		kategoriSelect.addEventListener('change', async function () {
			if (!sukanSelect.value) return;
			const sukanText = sukanSelect.options[sukanSelect.selectedIndex]?.text || '';
			const kategoriText = kategoriSelect.options[kategoriSelect.selectedIndex]?.text || '';
			if (!namaEdited && sukanText && kategoriText) {
				namaInput.value = `${sukanText} – ${kategoriText} – SUKAN ASASI 2026`;
			}

			const sukanId = sukanSelect.value;
			const kategoriId = kategoriSelect.value;
			if (!sukanId || !kategoriId) return;
			const chk = await checkEvent(sukanId, kategoriId);
			if (chk && chk.success && chk.exists) {
				const ev = chk.event || {};
				if (!namaEdited) document.getElementById('nama_event').value = ev.nama_event || document.getElementById('nama_event').value;
				if (ev.tarikh_mula) document.querySelector('input[name="tarikh_mula"]').value = ev.tarikh_mula;
				if (ev.tarikh_tamat) document.querySelector('input[name="tarikh_tamat"]').value = ev.tarikh_tamat;
				if (ev.status) document.querySelector('select[name="status"]').value = ev.status;
				window.currentEventId = ev.id;
				if (saveBtn) saveBtn.textContent = 'Kemaskini & Teruskan';
				const tab2Btn = document.getElementById('tab-2-btn');
				if (tab2Btn) {
					tab2Btn.classList.remove('disabled');
					tab2Btn.removeAttribute('aria-disabled');
					tab2Btn.setAttribute('data-bs-toggle', 'pill');
					tab2Btn.setAttribute('data-bs-target', '#tab-2');
				}
			} else {
				window.currentEventId = null;
				if (saveBtn) saveBtn.textContent = 'Simpan & Teruskan';
			}
		});

		namaInput.addEventListener('input', function () { namaEdited = true; });

		if (form1) {
			form1.addEventListener('submit', async function (ev) {
				ev.preventDefault();
				const fd = new FormData(form1);
				try {
					if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Menyimpan...'; }
					const res = await fetch('', { method: 'POST', body: fd });
					const json = await res.json();
					if (json.success) {
						const eventId = json.event_id || json.event_id === 0 ? json.event_id : null;
						window.currentEventId = eventId;
						// ensure session set server-side by handlers
						Swal.fire({ icon: 'success', title: 'Berjaya', text: 'Event disimpan', timer: 1000, showConfirmButton: false }).then(() => {
							const tab2Btn = document.getElementById('tab-2-btn');
							if (tab2Btn) {
								tab2Btn.classList.remove('disabled');
								tab2Btn.removeAttribute('aria-disabled');
								tab2Btn.setAttribute('data-bs-toggle', 'pill');
								tab2Btn.setAttribute('data-bs-target', '#tab-2');
								var tabTrigger = new bootstrap.Tab(tab2Btn);
								tabTrigger.show();
							}
						});
					} else {
						const msg = (json.errors || ['Gagal menyimpan']).join('<br>');
						Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
					}
				} catch (e) {
					console.error(e);
					Swal.fire({ icon: 'error', title: 'Ralat', text: 'Ralat jaringan semasa menyimpan.' });
				} finally {
					if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Simpan & Teruskan'; }
				}
			});
		}
	})();

	(() => {
		// TAB2: Preview generation and submit
		const bilInput = document.getElementById('bilangan_kumpulan');
		const formatSelect = document.getElementById('format_kumpulan');
		const previewTbody = document.querySelector('#group-preview-table tbody');
		const form2 = document.getElementById('form-tab2');

		// existing rounds passed from server
		const existingRounds = <?php echo json_encode($rounds ?: []); ?> || [];
		const editMode = Array.isArray(existingRounds) && existingRounds.length > 0;

		function generateCodes(n, format) {
			const codes = [];
			for (let i = 0; i < n; i++) {
				if (format === 'numeric') codes.push(String(i + 1));
				else {
					codes.push(String.fromCharCode(65 + i));
				}
			}
			return codes;
		}

		function renderPreview() {
			previewTbody.innerHTML = '';
			// If editing, render existing rounds from server to reflect DB state
			if (editMode) {
				const seen = new Set();
				existingRounds.forEach((r, idx) => {
					const code = String(r.group_code || '');
					if (seen.has(code)) return; // avoid duplicate preview rows
					seen.add(code);
					const tr = document.createElement('tr');
					const td1 = document.createElement('td'); td1.textContent = code;
					const td2 = document.createElement('td'); td2.textContent = 'Peringkat Kumpulan';
					const td3 = document.createElement('td'); td3.textContent = (r.group_order || (idx + 1)).toString();
					tr.appendChild(td1); tr.appendChild(td2); tr.appendChild(td3);
					previewTbody.appendChild(tr);
				});
				return existingRounds.map(r => r.group_code);
			}

			const n = Math.max(1, parseInt(bilInput.value || '0'));
			const format = formatSelect.value;
			const codes = generateCodes(n, format);
			codes.forEach((c, idx) => {
				const tr = document.createElement('tr');
				const td1 = document.createElement('td'); td1.textContent = c;
				const td2 = document.createElement('td'); td2.textContent = 'Peringkat Kumpulan';
				const td3 = document.createElement('td'); td3.textContent = (idx + 1).toString();
				tr.appendChild(td1); tr.appendChild(td2); tr.appendChild(td3);
				previewTbody.appendChild(tr);
			});
			return codes;
		}

		// initial render if elements exist
		if (bilInput && formatSelect && previewTbody) {
			if (editMode) {
				// populate bilangan and disable changes that would imply insert/delete
				bilInput.value = existingRounds.length;
				bilInput.setAttribute('readonly', 'readonly');
				formatSelect.disabled = true;
				// change button text
				const btn = document.getElementById('save-groups');
				if (btn) btn.textContent = 'Kemaskini Group';
				// enable TAB3 immediately
				const tab3Btn = document.getElementById('tab-3-btn');
				if (tab3Btn) {
					tab3Btn.classList.remove('disabled');
					tab3Btn.removeAttribute('aria-disabled');
					tab3Btn.setAttribute('data-bs-toggle', 'pill');
					tab3Btn.setAttribute('data-bs-target', '#tab-3');
				}
			}
			renderPreview();
			if (!editMode) {
				bilInput.addEventListener('input', renderPreview);
				formatSelect.addEventListener('change', renderPreview);
			}
		}

		if (form2) {
			form2.addEventListener('submit', async function (ev) {
				ev.preventDefault();
				const n = Math.max(1, parseInt(bilInput.value || '0'));
				if (!n || n < 1) {
					Swal.fire({ icon: 'error', title: 'Gagal', text: 'Sila masukkan bilangan kumpulan yang sah.' });
					return;
				}
				// ensure event id available
				const eventId = window.currentEventId || (typeof window !== 'undefined' && window.current_event_id) || null;
				if (!eventId && typeof window !== 'undefined' && !window.currentEventId) {
					Swal.fire({ icon: 'error', title: 'Gagal', text: 'Event ID tidak ditemui. Sila simpan Maklumat Kejohanan dahulu.' });
					return;
				}

				const codes = renderPreview();
				const fd = new FormData(form2);
				fd.append('group_codes', JSON.stringify(codes));
				fd.append('event_id', eventId);
				try {
					const btn = document.getElementById('save-groups');
					btn.disabled = true; btn.textContent = 'Menyimpan...';
					const res = await fetch('', { method: 'POST', body: fd });
					const json = await res.json();
					if (json.success) {
						const okText = json.mode === 'update' ? 'Struktur kumpulan dikemaskini' : 'Struktur kumpulan disimpan';
						Swal.fire({ icon: 'success', title: 'Berjaya', text: okText, timer: 1200, showConfirmButton: false }).then(() => {
							// enable and switch to TAB 3
							const tab3Btn = document.getElementById('tab-3-btn');
							if (tab3Btn) {
								tab3Btn.classList.remove('disabled');
								tab3Btn.removeAttribute('aria-disabled');
								tab3Btn.setAttribute('data-bs-toggle', 'pill');
								tab3Btn.setAttribute('data-bs-target', '#tab-3');
								var tabTrigger = new bootstrap.Tab(tab3Btn);
								tabTrigger.show();
							}
							// if created, reload page to refresh rounds list used in TAB3; if updated, update select/options in-page
							if (json.mode === 'create') {
								window.location.reload();
							} else {
								// update assign-group-select and groups container
								const assignSelect = document.getElementById('assign-group-select');
								const groupsContainer = document.getElementById('groups-container');
								if (assignSelect) {
									assignSelect.innerHTML = '<option value="">-- Assign to Group --</option>';
									(json.groups || codes).forEach(c => {
										const opt = document.createElement('option'); opt.value = c; opt.textContent = c; assignSelect.appendChild(opt);
									});
								}
							}
						});
					} else {
						const msg = (json.errors || ['Gagal menyimpan struktur kumpulan.']).join('<br>');
						Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
					}
				} catch (e) {
					console.error(e);
					Swal.fire({ icon: 'error', title: 'Ralat', text: 'Ralat jaringan semasa menyimpan.' });
				} finally {
					const btn = document.getElementById('save-groups');
					btn.disabled = false; btn.textContent = editMode ? 'Kemaskini Group' : 'Simpan Group';
				}
			});
		}
	})();
</script>

<script>
// server-side debug exported to console for quick inspection
window.__setup_debug = {
	event_id: <?php echo (int)$event_id; ?>,
	sukan_id: <?php echo json_encode($event_sukan_id); ?>,
	rounds: <?php echo (int)count($rounds); ?>,
	teams: <?php echo (int)count($teams); ?>,
	sample_team_ids: <?php echo json_encode(array_values(array_slice(array_map(function($t){return (int)$t['id'];}, $teams),0,10))); ?>
};
console.log('[setup-pertandingan debug]', window.__setup_debug);
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';

