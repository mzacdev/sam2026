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
// Minimum JUDGE (includes ORGANIZER, ADMIN)
if (!$rbac->hasMinimumRole('JUDGE')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya ADMIN, ORGANIZER, dan JUDGE dibenarkan.']);
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
        'pengurus' => ['nama', 'no_kad_pengenalan'],
        'jurulatih' => ['nama', 'no_kad_pengenalan']
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
        'no_matrik' => 'no_matrik'
    ];
    
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

