<?php
// AJAX endpoint: get contingent list
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/contingent_list][exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
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
require_once __DIR__ . '/../api/models/ContingentModel.php';

if (session_status() === PHP_SESSION_NONE) {
    Session::start();
}

$auth = getAuth();
if (!$auth->isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sesi tamat. Sila log masuk semula.']);
    exit;
}

try {
    $contingentModel = new ContingentModel();
    
    // Get contingents
    $result = $contingentModel->getAll(['limit' => 1000]);
    
    // Get statistics
    $statsResult = $contingentModel->getStatistics();
    
    if ($result['success'] && $statsResult['success']) {
        echo json_encode([
            'success' => true,
            'data' => $result['data'],
            'stats' => $statsResult['data']
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal memuatkan data kontinjen',
            'data' => [],
            'stats' => ['total' => 0, 'active' => 0, 'inactive' => 0]
        ]);
    }
} catch (Exception $e) {
    error_log('[ajax/contingent_list] ' . $e->getMessage() . " in " . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    $msg = (defined('DEBUG_MODE') && DEBUG_MODE) ? $e->getMessage() : 'Ralat memuatkan senarai kontinjen.';
    echo json_encode([
        'success' => false,
        'message' => $msg,
        'data' => [],
        'stats' => ['total' => 0, 'active' => 0, 'inactive' => 0]
    ]);
    exit;
}

