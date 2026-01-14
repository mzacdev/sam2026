<?php
// AJAX endpoint: get categories for a sport
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/get_categories][exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
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
require_once __DIR__ . '/../api/models/SportModel.php';

if (session_status() === PHP_SESSION_NONE) {
    Session::start();
}

$auth = getAuth();
if (!$auth->isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sesi tamat. Sila log masuk semula.']);
    exit;
}

$sukanId = isset($_GET['sukan_id']) ? (int)$_GET['sukan_id'] : 0;

if ($sukanId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Sukan ID tidak sah.', 'data' => []]);
    exit;
}

try {
    $sportModel = new SportModel();
    $result = $sportModel->getById($sukanId);
    
    if ($result['success'] && $result['data']) {
        $categories = $result['data']['categories'] ?? [];
        echo json_encode([
            'success' => true,
            'data' => $categories
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Sukan tidak dijumpai',
            'data' => []
        ]);
    }
} catch (Exception $e) {
    error_log('[ajax/get_categories] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ralat memuatkan kategori.',
        'data' => []
    ]);
}

