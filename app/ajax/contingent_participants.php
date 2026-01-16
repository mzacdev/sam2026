<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

Session::start();
$auth = getAuth();
$auth->requireAuth();

$currentRole = Session::get('user_role') ?? '';
$sessionKontinjen = Session::get('kontinjen_id') ?? null;

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDB();

    // Receive params
    $kontinjen_id = isset($_GET['kontinjen_id']) ? (int)$_GET['kontinjen_id'] : null;
    $sukan_id = isset($_GET['sukan_id']) ? (int)$_GET['sukan_id'] : null;

    // Enforce session scoping for CONTINGENT users
    if ($currentRole === 'CONTINGENT') {
        if (empty($sessionKontinjen)) {
            throw new Exception('Access denied: no session kontinjen');
        }
        $kontinjen_id = (int)$sessionKontinjen;
    }

    if (empty($kontinjen_id)) {
        throw new Exception('Missing kontinjen_id');
    }

    // Build base team filter
    $teamWhere = 'p.kontinjen_id = :kontinjen_id AND p.deleted_at IS NULL AND p.status = 1';
    $params = [':kontinjen_id' => $kontinjen_id];
    if (!empty($sukan_id)) {
        $teamWhere .= ' AND p.sukan_id = :sukan_id';
        $params[':sukan_id'] = $sukan_id;
    }

    // Fetch participants: athletes
    // Join `table_sukan` to include readable sport name where available.
    $sqlAtlet = "SELECT pa.*, p.nama_pasukan, p.sukan_id, s.nama_sukan FROM table_pasukan_atlet pa
        JOIN table_pasukan p ON p.id = pa.pasukan_id
        LEFT JOIN table_sukan s ON s.id = p.sukan_id
        WHERE " . $teamWhere . " AND pa.deleted_at IS NULL";
    $stmt = $pdo->prepare($sqlAtlet);
    $stmt->execute($params);
    $atlets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch managers
    $sqlPengurus = "SELECT pg.*, p.nama_pasukan, p.sukan_id, s.nama_sukan FROM table_pasukan_pengurus pg
        JOIN table_pasukan p ON p.id = pg.pasukan_id
        LEFT JOIN table_sukan s ON s.id = p.sukan_id
        WHERE " . $teamWhere . " AND pg.deleted_at IS NULL";
    $stmt = $pdo->prepare($sqlPengurus);
    $stmt->execute($params);
    $pengurus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch coaches
    $sqlJurulatih = "SELECT jr.*, p.nama_pasukan, p.sukan_id, s.nama_sukan FROM table_pasukan_jurulatih jr
        JOIN table_pasukan p ON p.id = jr.pasukan_id
        LEFT JOIN table_sukan s ON s.id = p.sukan_id
        WHERE " . $teamWhere . " AND jr.deleted_at IS NULL";
    $stmt = $pdo->prepare($sqlJurulatih);
    $stmt->execute($params);
    $jurulatih = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return combined result
    echo json_encode([
        'status' => 'ok',
        'kontinjen_id' => $kontinjen_id,
        'sukan_id' => $sukan_id,
        'atlet' => $atlets,
        'pengurus' => $pengurus,
        'jurulatih' => $jurulatih,
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>
