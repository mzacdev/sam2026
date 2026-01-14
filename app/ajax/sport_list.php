<?php
// AJAX endpoint: list active sports
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/sport_list][exception] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ralat pelayan.']);
    exit;
});

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, nama_sukan, kod_sukan FROM table_sukan WHERE deleted_at IS NULL AND status = 1 ORDER BY nama_sukan ASC");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
} catch (Exception $e) {
    error_log('[ajax/sport_list] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ralat memuatkan sukan.']);
    exit;
}
