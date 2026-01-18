<?php
// AJAX endpoint: list keputusan (results) with category and participant details
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/keputusan_list][exception] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ralat pelayan.']);
    exit;
});

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $db = getDB();
    $where = [];
    $params = [];
    
    // Check if requesting a single record by ID
    if(!empty($_GET['id'])){
        $where[] = 'r.id = :id';
        $params[':id'] = (int)$_GET['id'];
    }
    
    if(!empty($_GET['sukan_id'])){
        $where[] = 'r.sukan_id = :sukan_id';
        $params[':sukan_id'] = (int)$_GET['sukan_id'];
    }
    if(!empty($_GET['kategori_id'])){
        $where[] = 'r.kategori_id = :kategori_id';
        $params[':kategori_id'] = (int)$_GET['kategori_id'];
    }
    if(!empty($_GET['tarikh'])){
        $where[] = 'DATE(r.tarikh) = :tarikh';
        $params[':tarikh'] = $_GET['tarikh'];
    }
    if(!empty($_GET['status'])){
        $where[] = 'r.status = :status';
        $params[':status'] = $_GET['status'];
    }
    $where[] = 'r.deleted_at IS NULL';
    
    // Check if kategori_id column exists
    $checkColStmt = $db->query("SHOW COLUMNS FROM table_results LIKE 'kategori_id'");
    $hasKategoriId = $checkColStmt && $checkColStmt->rowCount() > 0;
    
    if ($hasKategoriId) {
        $sql = "SELECT 
                r.id, 
                r.sukan_id,
                s.nama_sukan AS sukan, 
                r.kategori_id,
                k.nama_kategori AS kategori,
                k.penilaian,
                DATE_FORMAT(r.tarikh, '%Y-%m-%d') AS tarikh,
                r.tempat_pertama, 
                r.tempat_kedua, 
                r.tempat_ketiga, 
                r.status,
                -- Get participant names for tempat pertama
                CASE 
                    WHEN k.penilaian = 'individu' THEN 
                        (SELECT CONCAT(pa.nama, ' - ', COALESCE(u.nama_universiti, k2.kod_universiti, '')) 
                         FROM table_pasukan_atlet pa 
                         JOIN table_pasukan p ON pa.pasukan_id = p.id
                         JOIN table_kontinjen k2 ON p.kontinjen_id = k2.id
                         LEFT JOIN table_ref_universiti u ON k2.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
                         WHERE pa.id = r.tempat_pertama AND pa.deleted_at IS NULL)
                    ELSE 
                        (SELECT CONCAT(p.nama_pasukan, ' - ', COALESCE(u.nama_universiti, k2.kod_universiti, '')) 
                         FROM table_pasukan p
                         JOIN table_kontinjen k2 ON p.kontinjen_id = k2.id
                         LEFT JOIN table_ref_universiti u ON k2.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
                         WHERE p.id = r.tempat_pertama AND p.deleted_at IS NULL)
                END AS tempat_pertama_nama,
                -- Get participant names for tempat kedua
                CASE 
                    WHEN k.penilaian = 'individu' THEN 
                        (SELECT CONCAT(pa.nama, ' - ', COALESCE(u.nama_universiti, k2.kod_universiti, '')) 
                         FROM table_pasukan_atlet pa 
                         JOIN table_pasukan p ON pa.pasukan_id = p.id
                         JOIN table_kontinjen k2 ON p.kontinjen_id = k2.id
                         LEFT JOIN table_ref_universiti u ON k2.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
                         WHERE pa.id = r.tempat_kedua AND pa.deleted_at IS NULL)
                    ELSE 
                        (SELECT CONCAT(p.nama_pasukan, ' - ', COALESCE(u.nama_universiti, k2.kod_universiti, '')) 
                         FROM table_pasukan p
                         JOIN table_kontinjen k2 ON p.kontinjen_id = k2.id
                         LEFT JOIN table_ref_universiti u ON k2.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
                         WHERE p.id = r.tempat_kedua AND p.deleted_at IS NULL)
                END AS tempat_kedua_nama,
                -- Get participant names for tempat ketiga
                CASE 
                    WHEN k.penilaian = 'individu' THEN 
                        (SELECT CONCAT(pa.nama, ' - ', COALESCE(u.nama_universiti, k2.kod_universiti, '')) 
                         FROM table_pasukan_atlet pa 
                         JOIN table_pasukan p ON pa.pasukan_id = p.id
                         JOIN table_kontinjen k2 ON p.kontinjen_id = k2.id
                         LEFT JOIN table_ref_universiti u ON k2.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
                         WHERE pa.id = r.tempat_ketiga AND pa.deleted_at IS NULL)
                    ELSE 
                        (SELECT CONCAT(p.nama_pasukan, ' - ', COALESCE(u.nama_universiti, k2.kod_universiti, '')) 
                         FROM table_pasukan p
                         JOIN table_kontinjen k2 ON p.kontinjen_id = k2.id
                         LEFT JOIN table_ref_universiti u ON k2.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
                         WHERE p.id = r.tempat_ketiga AND p.deleted_at IS NULL)
                END AS tempat_ketiga_nama
            FROM table_results r
            LEFT JOIN table_sukan s ON r.sukan_id = s.id
            LEFT JOIN table_kategori k ON r.kategori_id = k.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY r.tarikh DESC, s.nama_sukan ASC, k.nama_kategori ASC
            LIMIT 500";
    } else {
        // Fallback if kategori_id doesn't exist
        $sql = "SELECT 
                r.id, 
                r.sukan_id,
                s.nama_sukan AS sukan, 
                NULL AS kategori_id,
                '' AS kategori,
                NULL AS penilaian,
                DATE_FORMAT(r.tarikh, '%Y-%m-%d') AS tarikh,
                r.tempat_pertama, 
                r.tempat_kedua, 
                r.tempat_ketiga, 
                r.status,
                (SELECT CONCAT(p.nama_pasukan, ' - ', COALESCE(u.nama_universiti, k2.kod_universiti, '')) 
                 FROM table_pasukan p
                 JOIN table_kontinjen k2 ON p.kontinjen_id = k2.id
                 LEFT JOIN table_ref_universiti u ON k2.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
                 WHERE p.id = r.tempat_pertama AND p.deleted_at IS NULL) AS tempat_pertama_nama,
                (SELECT CONCAT(p.nama_pasukan, ' - ', COALESCE(u.nama_universiti, k2.kod_universiti, '')) 
                 FROM table_pasukan p
                 JOIN table_kontinjen k2 ON p.kontinjen_id = k2.id
                 LEFT JOIN table_ref_universiti u ON k2.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
                 WHERE p.id = r.tempat_kedua AND p.deleted_at IS NULL) AS tempat_kedua_nama,
                (SELECT CONCAT(p.nama_pasukan, ' - ', COALESCE(u.nama_universiti, k2.kod_universiti, '')) 
                 FROM table_pasukan p
                 JOIN table_kontinjen k2 ON p.kontinjen_id = k2.id
                 LEFT JOIN table_ref_universiti u ON k2.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
                 WHERE p.id = r.tempat_ketiga AND p.deleted_at IS NULL) AS tempat_ketiga_nama
            FROM table_results r
            LEFT JOIN table_sukan s ON r.sukan_id = s.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY r.tarikh DESC, s.nama_sukan ASC
            LIMIT 500";
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug logging
    error_log('[ajax/keputusan_list] Query executed. Found ' . count($rows) . ' rows. SQL: ' . $sql);
    if (!empty($params)) {
        error_log('[ajax/keputusan_list] Params: ' . json_encode($params));
    }
    
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
} catch (Exception $e) {
    error_log('[ajax/keputusan_list] ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ralat memuatkan keputusan.', 'data' => []]);
    exit;
}

