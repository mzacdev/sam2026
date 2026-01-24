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
                r.standings,
                r.status
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
                r.standings,
                r.status
            FROM table_results r
            LEFT JOIN table_sukan s ON r.sukan_id = s.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY r.tarikh DESC, s.nama_sukan ASC
            LIMIT 500";
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse standings JSON and get participant names
    $checkColStmt = $db->query("SHOW COLUMNS FROM table_pasukan_atlet LIKE 'kategori_id'");
    $hasKategoriIdInAtlet = $checkColStmt && $checkColStmt->rowCount() > 0;
    
    foreach ($rows as &$row) {
        $standings = [];
        if (!empty($row['standings'])) {
            $standingsData = json_decode($row['standings'], true);
            if (is_array($standingsData)) {
                $penilaian = $row['penilaian'] ?? 'berkumpulan';
                if (empty($penilaian)) {
                    $penilaian = 'berkumpulan';
                }
                
                foreach ($standingsData as $standing) {
                    $position = isset($standing['position']) ? (int)$standing['position'] : 0;
                    $participantId = isset($standing['participant_id']) ? trim($standing['participant_id']) : '';
                    
                    if (empty($participantId)) {
                        continue;
                    }
                    
                    $participantName = '';
                    
                        if ($penilaian === 'individu') {
                        // Get individual player name
                        if ($hasKategoriIdInAtlet) {
                            $nameStmt = $db->prepare("
                                SELECT CONCAT(pa.nama, ' - ', COALESCE(u.nama_universiti, k2.kod_universiti, '')) AS nama,
                                       COALESCE(u.nama_pendek, k2.kod_universiti, '') AS kontingen_short_name,
                                       k2.kod_universiti
                                FROM table_pasukan_atlet pa 
                                JOIN table_pasukan p ON pa.pasukan_id = p.id
                                JOIN table_kontinjen k2 ON p.kontinjen_id = k2.id
                                LEFT JOIN table_ref_universiti u ON k2.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
                                WHERE pa.id = :id AND pa.deleted_at IS NULL
                            ");
                        } else {
                            $nameStmt = $db->prepare("
                                SELECT CONCAT(pa.nama, ' - ', COALESCE(u.nama_universiti, k2.kod_universiti, '')) AS nama,
                                       COALESCE(u.nama_pendek, k2.kod_universiti, '') AS kontingen_short_name,
                                       k2.kod_universiti
                                FROM table_pasukan_atlet pa 
                                JOIN table_pasukan p ON pa.pasukan_id = p.id
                                JOIN table_kontinjen k2 ON p.kontinjen_id = k2.id
                                LEFT JOIN table_ref_universiti u ON k2.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
                                WHERE pa.id = :id AND pa.deleted_at IS NULL AND p.deleted_at IS NULL
                            ");
                        }
                        $nameStmt->execute([':id' => (int)$participantId]);
                        $nameResult = $nameStmt->fetch(PDO::FETCH_ASSOC);
                        $participantName = $nameResult['nama'] ?? '';
                        $kontingenShort = $nameResult['kontingen_short_name'] ?? ($nameResult['kod_universiti'] ?? '');

                        // Build a cleaned display name: base name + ', ' + nama_pendek (if present) or fallback to suffix after ' - '
                        $participantDisplay = $participantName;
                        $displayBase = $participantDisplay;
                        $displaySuffix = '';
                        if (strpos($participantDisplay, ' - ') !== false) {
                            $parts = explode(' - ', $participantDisplay);
                            if (count($parts) > 1) {
                                $displayBase = implode(' - ', array_slice($parts, 0, count($parts)-1));
                                $displaySuffix = $parts[count($parts)-1];
                            }
                        }
                        if (!empty($kontingenShort)) {
                            $participant_display_name = trim($displayBase) . ', ' . trim($kontingenShort);
                        } elseif (!empty($displaySuffix)) {
                            $participant_display_name = trim($displayBase) . ', ' . trim($displaySuffix);
                        } else {
                            $participant_display_name = trim($displayBase);
                        }
                    } else {
                        // Get team name
                        if ($hasKategoriIdInAtlet) {
                            $nameStmt = $db->prepare("
                                SELECT CONCAT(p.nama_pasukan, ' - ', COALESCE(u.nama_universiti, k2.kod_universiti, '')) AS nama,
                                       COALESCE(u.nama_pendek, k2.kod_universiti, '') AS kontingen_short_name,
                                       k2.kod_universiti
                                FROM table_pasukan p
                                JOIN table_kontinjen k2 ON p.kontinjen_id = k2.id
                                LEFT JOIN table_ref_universiti u ON k2.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
                                WHERE p.id = :id AND p.deleted_at IS NULL AND p.status = 1
                            ");
                        } else {
                            $nameStmt = $db->prepare("
                                SELECT CONCAT(p.nama_pasukan, ' - ', COALESCE(u.nama_universiti, k2.kod_universiti, '')) AS nama,
                                       COALESCE(u.nama_pendek, k2.kod_universiti, '') AS kontingen_short_name,
                                       k2.kod_universiti
                                FROM table_pasukan p
                                JOIN table_kontinjen k2 ON p.kontinjen_id = k2.id
                                LEFT JOIN table_ref_universiti u ON k2.kod_universiti = u.kod_universiti AND u.deleted_at IS NULL
                                WHERE p.id = :id AND p.deleted_at IS NULL AND p.status = 1
                            ");
                        }
                        $nameStmt->execute([':id' => (int)$participantId]);
                        $nameResult = $nameStmt->fetch(PDO::FETCH_ASSOC);
                        $participantName = $nameResult['nama'] ?? '';
                        $kontingenShort = $nameResult['kontingen_short_name'] ?? ($nameResult['kod_universiti'] ?? '');

                        // Build cleaned display name as above for teams
                        $participantDisplay = $participantName;
                        $displayBase = $participantDisplay;
                        $displaySuffix = '';
                        if (strpos($participantDisplay, ' - ') !== false) {
                            $parts = explode(' - ', $participantDisplay);
                            if (count($parts) > 1) {
                                $displayBase = implode(' - ', array_slice($parts, 0, count($parts)-1));
                                $displaySuffix = $parts[count($parts)-1];
                            }
                        }
                        if (!empty($kontingenShort)) {
                            $participant_display_name = trim($displayBase) . ', ' . trim($kontingenShort);
                        } elseif (!empty($displaySuffix)) {
                            $participant_display_name = trim($displayBase) . ', ' . trim($displaySuffix);
                        } else {
                            $participant_display_name = trim($displayBase);
                        }
                    }
                    
                    $standings[] = [
                        'position' => $position,
                        'participant_id' => $participantId,
                        'participant_name' => $participantName,
                        'participant_display_name' => $participant_display_name ?? ($participantName ?? ''),
                        'kontingen_short_name' => $kontingenShort ?? ''
                    ];
                }
                
                // Sort by position
                usort($standings, function($a, $b) {
                    return $a['position'] <=> $b['position'];
                });
            }
        }
        $row['standings'] = $standings;
    }
    unset($row);
    
    // Debug logging
    error_log('[ajax/keputusan_list] Query executed. Found ' . count($rows) . ' rows.');
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

