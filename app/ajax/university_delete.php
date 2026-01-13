<?php
// AJAX endpoint: delete university (currently disabled) — returns JSON
// Ensure JSON is returned even on fatal errors
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/university_delete][exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ralat pelayan.']);
    }
    exit;
});

register_shutdown_function(function(){
    $err = error_get_last();
    if ($err) {
        http_response_code(500);
        error_log('[ajax/university_delete][shutdown] ' . $err['message'] . ' in ' . $err['file'] . ':' . $err['line']);
        $msg = (defined('DEBUG_MODE') && DEBUG_MODE) ? $err['message'] . ' in ' . $err['file'] . ':' . $err['line'] : 'Ralat pelayan.';
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }
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

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID tidak sah.']);
    exit;
}

// Log attempt for auditing
error_log('[ajax/university_delete] user=' . Session::get('user_id') . ' attempted delete id=' . $id);

// Deletion is intentionally disabled. Inform the caller.
echo json_encode(['success' => false, 'message' => 'Fungsi padam belum diaktifkan. Sila hubungi pentadbir.']);
exit;
