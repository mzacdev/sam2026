<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

Session::start();
$auth = getAuth();
$auth->requireAuth();

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDB();
    $sql = "SELECT k.id, k.kod_universiti, COALESCE(u.nama_universiti, k.kod_universiti) AS nama_universiti
            FROM table_kontinjen k
            LEFT JOIN table_ref_universiti u ON k.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
            WHERE k.deleted_at IS NULL
            ORDER BY nama_universiti ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
}

?>