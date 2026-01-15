<?php
// AJAX endpoint: list results with basic filters
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/results_list][exception] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ralat pelayan.']);
    exit;
});

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = getDB();
    $where = [];
    $params = [];
    if(!empty($_GET['sukan_id'])){ $where[] = 'r.sukan_id = :sukan_id'; $params[':sukan_id'] = (int)$_GET['sukan_id']; }
    if(!empty($_GET['acara_id'])){ $where[] = 'r.acara_id = :acara_id'; $params[':acara_id'] = (int)$_GET['acara_id']; }
    if(!empty($_GET['tarikh'])){ $where[] = 'DATE(r.tarikh) = :tarikh'; $params[':tarikh'] = $_GET['tarikh']; }
    if(!empty($_GET['status'])){ $where[] = 'r.status = :status'; $params[':status'] = $_GET['status']; }
    $where[] = 'r.deleted_at IS NULL';

    $sql = "SELECT r.id, s.nama_sukan AS sukan, COALESCE(e.nama_acara, '') AS acara, DATE_FORMAT(r.tarikh, '%Y-%m-%d') AS tarikh,
            r.tempat_pertama, r.tempat_kedua, r.tempat_ketiga, r.status
            FROM table_results r
            LEFT JOIN table_sukan s ON r.sukan_id = s.id
            LEFT JOIN table_event e ON r.acara_id = e.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY r.tarikh DESC
            LIMIT 500";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
} catch (Exception $e) {
    error_log('[ajax/results_list] ' . $e->getMessage());
    // return empty success so frontend still works
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}
