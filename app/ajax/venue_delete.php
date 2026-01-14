<?php
// AJAX endpoint: delete (soft) venue
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/venue_delete][exception] ' . $e->getMessage());
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
if (!in_array($userRole, ['ADMIN', 'ORGANIZER'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Anda tidak mempunyai kebenaran.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Kaedah tidak dibenarkan.']);
    exit;
}

$input = filter_input_array(INPUT_POST, [
    'id' => FILTER_SANITIZE_NUMBER_INT
]);

$id = isset($input['id']) ? (int)$input['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID tidak sah.']);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("UPDATE table_ref_venues SET deleted_at = NOW(), deleted_by = :deleted_by WHERE id = :id");
    $stmt->execute([':deleted_by' => Session::get('user_id'), ':id' => $id]);
    echo json_encode(['success' => true, 'message' => 'Venue dipadam.']);
    exit;
} catch (Exception $e) {
    error_log('[ajax/venue_delete] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ralat memadam rekod.']);
    exit;
}
