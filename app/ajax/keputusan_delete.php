<?php
// AJAX endpoint: delete keputusan (results)
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/keputusan_delete][exception] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ralat pelayan.']);
    exit;
});

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    Session::start();
}

$auth = getAuth();
if (!$auth->isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sesi tamat. Sila log masuk semula.']);
    exit;
}

$userRole = Session::get('user_role');
if (!in_array($userRole, ['ADMIN', 'ORGANIZER', 'JUDGE'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya ADMIN, ORGANIZER, dan JUDGE dibenarkan.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $id = isset($input['id']) ? (int)$input['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
    
    if (empty($id)) {
        throw new Exception('ID keputusan diperlukan');
    }
    
    $db = getDB();
    $userId = Session::get('user_id');
    
    // Soft delete
    $stmt = $db->prepare("
        UPDATE table_results 
        SET deleted_at = CURRENT_TIMESTAMP,
            updated_at = CURRENT_TIMESTAMP,
            updated_by = :updated_by
        WHERE id = :id AND deleted_at IS NULL
    ");
    $stmt->execute([
        ':id' => $id,
        ':updated_by' => $userId
    ]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Keputusan tidak dijumpai atau sudah dipadam');
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Keputusan berjaya dipadam'
    ]);
} catch (Exception $e) {
    error_log('[ajax/keputusan_delete] ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

