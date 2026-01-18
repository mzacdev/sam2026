<?php
/**
 * AJAX endpoint: Add new participant (atlet, pengurus, or jurulatih)
 * Roles: ADMIN, ORGANIZER, JUDGE
 */
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/participant_add][exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ralat pelayan.']);
    }
    exit;
});

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/rbac.php';

if (session_status() === PHP_SESSION_NONE) {
    Session::start();
}

$auth = getAuth();
if (!$auth->isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sesi tamat. Sila log masuk semula.']);
    exit;
}

$rbac = getRBAC();
// Only ADMIN and ORGANIZER can add participants
$userRole = Session::get('user_role');
if (!in_array($userRole, ['ADMIN', 'ORGANIZER'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya ADMIN dan ORGANIZER dibenarkan untuk menambah peserta.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Kaedah tidak dibenarkan.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $participant_type = isset($input['participant_type']) ? trim($input['participant_type']) : '';
    $pasukan_id = isset($input['pasukan_id']) ? (int)$input['pasukan_id'] : 0;
    $nama = isset($input['nama']) ? trim($input['nama']) : '';
    $no_kad_pengenalan = isset($input['no_kad_pengenalan']) ? trim($input['no_kad_pengenalan']) : null;
    $no_matrik = isset($input['no_matrik']) ? trim($input['no_matrik']) : null;
    $no_telefon = isset($input['no_telefon']) ? trim($input['no_telefon']) : null;
    $emel = isset($input['emel']) ? trim($input['emel']) : null;
    $kategori_id = isset($input['kategori_id']) && !empty($input['kategori_id']) ? (int)$input['kategori_id'] : null;
    
    // Validation
    if (empty($participant_type) || !in_array($participant_type, ['atlet', 'pengurus', 'jurulatih'])) {
        throw new Exception('Jenis peserta tidak sah.');
    }
    
    if ($pasukan_id <= 0) {
        throw new Exception('ID pasukan tidak sah.');
    }
    
    if (empty($nama)) {
        throw new Exception('Nama peserta diperlukan.');
    }
    
    // Validate IC format if provided
    if (!empty($no_kad_pengenalan)) {
        $cleanIc = preg_replace('/\D+/', '', $no_kad_pengenalan);
        if (strlen($cleanIc) !== 12) {
            throw new Exception('No Kad Pengenalan mesti 12 digit.');
        }
        $no_kad_pengenalan = $cleanIc;
    }
    
    // Validate matrik length if provided
    if (!empty($no_matrik) && mb_strlen($no_matrik) > 50) {
        throw new Exception('No Matrik tidak boleh melebihi 50 aksara.');
    }
    
    // Validate phone length if provided
    if (!empty($no_telefon) && mb_strlen($no_telefon) > 20) {
        throw new Exception('No Telefon tidak boleh melebihi 20 aksara.');
    }
    
    // Validate email format if provided
    if (!empty($emel)) {
        if (mb_strlen($emel) > 100) {
            throw new Exception('Emel tidak boleh melebihi 100 aksara.');
        }
        if (!filter_var($emel, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format emel tidak sah.');
        }
    }
    
    $db = getDB();
    
    // Verify pasukan exists and is active
    $checkPasukan = $db->prepare("
        SELECT id, sukan_id 
        FROM table_pasukan 
        WHERE id = :id AND deleted_at IS NULL AND status = 1
    ");
    $checkPasukan->execute([':id' => $pasukan_id]);
    $pasukan = $checkPasukan->fetch(PDO::FETCH_ASSOC);
    
    if (!$pasukan) {
        throw new Exception('Pasukan tidak dijumpai atau tidak aktif.');
    }
    
    // For athletes, validate kategori_id if provided
    if ($participant_type === 'atlet' && !empty($kategori_id)) {
        $checkKategori = $db->prepare("
            SELECT id, sukan_id 
            FROM table_kategori 
            WHERE id = :id AND deleted_at IS NULL AND status = 1
        ");
        $checkKategori->execute([':id' => $kategori_id]);
        $kategori = $checkKategori->fetch(PDO::FETCH_ASSOC);
        
        if (!$kategori) {
            throw new Exception('Kategori tidak dijumpai.');
        }
        
        if ($kategori['sukan_id'] != $pasukan['sukan_id']) {
            throw new Exception('Kategori tidak sesuai dengan sukan pasukan.');
        }
    }
    
    // Map participant type to table name and fields
    $tableMap = [
        'atlet' => [
            'table' => 'table_pasukan_atlet',
            'fields' => ['pasukan_id', 'nama', 'no_kad_pengenalan', 'no_matrik', 'kategori_id']
        ],
        'pengurus' => [
            'table' => 'table_pasukan_pengurus',
            'fields' => ['pasukan_id', 'nama', 'no_kad_pengenalan']
        ],
        'jurulatih' => [
            'table' => 'table_pasukan_jurulatih',
            'fields' => ['pasukan_id', 'nama', 'no_kad_pengenalan']
        ]
    ];
    
    $tableInfo = $tableMap[$participant_type];
    $tableName = $tableInfo['table'];
    $fields = $tableInfo['fields'];
    
    // Build insert query
    $fieldList = implode(', ', $fields);
    $placeholders = ':' . implode(', :', $fields);
    
    $insertSql = "INSERT INTO {$tableName} ({$fieldList}) VALUES ({$placeholders})";
    $insertStmt = $db->prepare($insertSql);
    
    // Prepare parameters
    $params = [
        ':pasukan_id' => $pasukan_id,
        ':nama' => $nama
    ];
    
    if (in_array('no_kad_pengenalan', $fields)) {
        $params[':no_kad_pengenalan'] = $no_kad_pengenalan;
    }
    
    if (in_array('no_matrik', $fields)) {
        $params[':no_matrik'] = $no_matrik;
    }
    
    if (in_array('no_telefon', $fields)) {
        $params[':no_telefon'] = $no_telefon;
    }
    
    if (in_array('emel', $fields)) {
        $params[':emel'] = $emel;
    }
    
    if (in_array('kategori_id', $fields)) {
        $params[':kategori_id'] = $kategori_id;
    }
    
    $insertStmt->execute($params);
    $newId = $db->lastInsertId();
    
    // Fetch the newly created record to return
    $fetchStmt = $db->prepare("SELECT * FROM {$tableName} WHERE id = :id");
    $fetchStmt->execute([':id' => $newId]);
    $newParticipant = $fetchStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Peserta berjaya ditambah.',
        'participant' => $newParticipant,
        'participant_id' => $newId,
        'participant_type' => $participant_type
    ]);
    
} catch (Exception $e) {
    error_log('[ajax/participant_add] ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

