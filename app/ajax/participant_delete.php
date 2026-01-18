<?php
/**
 * AJAX endpoint: Delete (soft delete) participant
 * Roles: ADMIN, ORGANIZER, JUDGE
 */
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/participant_delete][exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
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
    
    // Validation
    if (empty($participant_type) || !in_array($participant_type, ['atlet', 'pengurus', 'jurulatih'])) {
        throw new Exception('Jenis peserta tidak sah.');
    }
    
    if ($participant_id <= 0) {
        throw new Exception('ID peserta tidak sah.');
    }
    
    $db = getDB();
    
    // Map participant type to table name
    $tableMap = [
        'atlet' => 'table_pasukan_atlet',
        'pengurus' => 'table_pasukan_pengurus',
        'jurulatih' => 'table_pasukan_jurulatih'
    ];
    
    $tableName = $tableMap[$participant_type];
    
    // Check if participant exists and is not already deleted
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
    
    // Soft delete
    $deleteStmt = $db->prepare("
        UPDATE {$tableName} 
        SET deleted_at = CURRENT_TIMESTAMP
        WHERE id = :id AND deleted_at IS NULL
    ");
    
    $deleteStmt->execute([':id' => $participant_id]);
    
    if ($deleteStmt->rowCount() === 0) {
        throw new Exception('Gagal memadam peserta.');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Peserta berjaya dipadam.',
        'participant_id' => $participant_id,
        'participant_type' => $participant_type
    ]);
    
} catch (Exception $e) {
    error_log('[ajax/participant_delete] ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

