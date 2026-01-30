<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';

$sukan_id = isset($_GET['sukan_id']) ? (int)$_GET['sukan_id'] : 0;
if ($sukan_id <= 0) {
    echo json_encode([]);
    exit;
}
try {
    $db = getDB();
    $stmt = $db->prepare('SELECT id, nama_kategori FROM table_kategori WHERE status = 1 AND sukan_id = :sukan_id ORDER BY nama_kategori');
    $stmt->execute([':sukan_id' => $sukan_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} catch (Exception $e) {
    error_log('[get_kategori] ' . $e->getMessage());
    echo json_encode([]);
}
