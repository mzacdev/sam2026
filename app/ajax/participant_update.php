<?php
/**
 * AJAX endpoint: Update individual participant field
 * Roles: ADMIN, ORGANIZER, JUDGE
 * Updates nama, no_kad_pengenalan, and no_matrik (for athletes only) fields
 */
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/participant_update][exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
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
// Only ADMIN and ORGANIZER can edit participants
$userRole = Session::get('user_role');
if (!in_array($userRole, ['ADMIN', 'ORGANIZER'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya ADMIN dan ORGANIZER dibenarkan untuk mengedit peserta.']);
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
    $participant_id = isset($input['participant_id']) ? (int)$input['participant_id'] : 0;
    $field_name = isset($input['field_name']) ? trim($input['field_name']) : '';
    $field_value = isset($input['field_value']) ? trim($input['field_value']) : '';
    
    // Validation
    if (empty($participant_type) || !in_array($participant_type, ['atlet', 'pengurus', 'jurulatih'])) {
        throw new Exception('Jenis peserta tidak sah.');
    }
    
    if ($participant_id <= 0) {
        throw new Exception('ID peserta tidak sah.');
    }
    
    // Define allowed fields for each participant type
    $allowedFields = [
        'atlet' => ['nama', 'no_kad_pengenalan', 'no_matrik'],
        'pengurus' => ['nama', 'no_kad_pengenalan', 'no_telefon', 'emel'],
        'jurulatih' => ['nama', 'no_kad_pengenalan', 'no_telefon', 'emel']
    ];
    
    if (!in_array($field_name, $allowedFields[$participant_type])) {
        throw new Exception('Medan tidak boleh diedit atau tidak sah untuk jenis peserta ini.');
    }
    
    $db = getDB();
    
    // Map participant type to table name
    $tableMap = [
        'atlet' => 'table_pasukan_atlet',
        'pengurus' => 'table_pasukan_pengurus',
        'jurulatih' => 'table_pasukan_jurulatih'
    ];
    
    $tableName = $tableMap[$participant_type];
    
    // Check if participant exists and is not deleted
    $checkStmt = $db->prepare("
        SELECT id 
        FROM {$tableName} 
        WHERE id = :id AND deleted_at IS NULL
    ");
    $checkStmt->execute([':id' => $participant_id]);
    $participant = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$participant) {
        throw new Exception('Peserta tidak dijumpai atau telah dipadam.');
    }
    
    // Prepare update query
    $fieldMap = [
        'nama' => 'nama',
        'no_kad_pengenalan' => 'no_kad_pengenalan',
        'no_matrik' => 'no_matrik',
        'no_telefon' => 'no_telefon',
        'emel' => 'emel'
    ];
    
    if (!isset($fieldMap[$field_name])) {
        throw new Exception('Medan tidak sah.');
    }
    
    $dbFieldName = $fieldMap[$field_name];
    
    // Validate field value based on field type
    if ($field_name === 'nama' && empty($field_value)) {
        throw new Exception('Nama tidak boleh kosong.');
    }
    
    if ($field_name === 'no_kad_pengenalan' && !empty($field_value)) {
        // Basic validation: remove non-digits and check length (Malaysian IC format)
        $cleanIc = preg_replace('/\D+/', '', $field_value);
        if (strlen($cleanIc) !== 12) {
            throw new Exception('No Kad Pengenalan mesti 12 digit.');
        }
    }
    
    if ($field_name === 'no_matrik' && !empty($field_value)) {
        // Basic validation: max length
        if (mb_strlen($field_value) > 50) {
            throw new Exception('No Matrik tidak boleh melebihi 50 aksara.');
        }
    }
    
    if ($field_name === 'no_telefon' && !empty($field_value)) {
        // Basic validation: max length
        if (mb_strlen($field_value) > 20) {
            throw new Exception('No Telefon tidak boleh melebihi 20 aksara.');
        }
    }
    
    if ($field_name === 'emel' && !empty($field_value)) {
        // Basic validation: email format and max length
        if (mb_strlen($field_value) > 100) {
            throw new Exception('Emel tidak boleh melebihi 100 aksara.');
        }
        if (!filter_var($field_value, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format emel tidak sah.');
        }
    }
    
    // Update the field
    $updateStmt = $db->prepare("
        UPDATE {$tableName} 
        SET {$dbFieldName} = :value, updated_at = CURRENT_TIMESTAMP
        WHERE id = :id AND deleted_at IS NULL
    ");
    
    // For nullable fields, allow empty string to be stored as NULL
    $valueToStore = ($field_value === '') ? null : $field_value;
    
    $updateStmt->execute([
        ':value' => $valueToStore,
        ':id' => $participant_id
    ]);
    
    if ($updateStmt->rowCount() === 0) {
        throw new Exception('Gagal mengemaskini data peserta.');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Data peserta berjaya dikemaskini.',
        'participant_id' => $participant_id,
        'field_name' => $field_name,
        'field_value' => $field_value
    ]);
    
} catch (Exception $e) {
    error_log('[ajax/participant_update] ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

