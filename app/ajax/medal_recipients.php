<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

Session::start();
$auth = getAuth();
$auth->requireAuth();

header('Content-Type: application/json; charset=utf-8');

$kod = isset($_GET['kod_universiti']) ? trim($_GET['kod_universiti']) : '';
$medal = isset($_GET['medal']) ? strtolower(trim($_GET['medal'])) : '';
$allowed = ['emas','perak','gangsa'];

if ($kod === '' || !in_array($medal, $allowed, true)) {
    echo json_encode(['status' => 'error', 'message' => 'Parameter tidak sah']);
    exit;
}

try {
    $db = getDB();
    $sql = "
        SELECT
            w.medal,
            p.id AS pasukan_id,
            p.nama_pasukan,
            COALESCE(r.nama_pendek, k.kod_universiti) AS nama_kontinjen,
            k.kod_universiti,
            s.nama_sukan,
            kat.nama_kategori
        FROM (
            SELECT tempat_pertama AS pasukan_id, kategori_id, 'emas' AS medal FROM table_results WHERE tempat_pertama IS NOT NULL AND tempat_pertama <> '' AND deleted_at IS NULL
            UNION ALL
            SELECT tempat_kedua AS pasukan_id, kategori_id, 'perak' AS medal FROM table_results WHERE tempat_kedua IS NOT NULL AND tempat_kedua <> '' AND deleted_at IS NULL
            UNION ALL
            SELECT tempat_ketiga AS pasukan_id, kategori_id, 'gangsa' AS medal FROM table_results WHERE tempat_ketiga IS NOT NULL AND tempat_ketiga <> '' AND deleted_at IS NULL
        ) w
        JOIN table_pasukan p ON p.id = w.pasukan_id AND p.deleted_at IS NULL
        JOIN table_kontinjen k ON k.id = p.kontinjen_id AND k.deleted_at IS NULL AND k.status = 1
        JOIN table_ref_universiti r ON r.kod_universiti = k.kod_universiti AND r.status = 1
        LEFT JOIN table_sukan s ON s.id = p.sukan_id
        LEFT JOIN table_kategori kat ON kat.id = w.kategori_id
        WHERE k.kod_universiti = :kod AND w.medal = :medal
        ORDER BY s.nama_sukan ASC, kat.nama_kategori ASC, p.nama_pasukan ASC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':kod' => $kod, ':medal' => $medal]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'ok', 'data' => $rows]);
} catch (Exception $e) {
    error_log('[medal_recipients] ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Gagal memuatkan penerima pingat']);
}
