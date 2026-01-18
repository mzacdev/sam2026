<?php
// AJAX endpoint: save (insert/update) keputusan (results)
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/keputusan_save][exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
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
if (!in_array($userRole, ['ADMIN', 'ORGANIZER', 'JUDGE'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya ADMIN, ORGANIZER, dan JUDGE dibenarkan.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $id = isset($input['id']) ? (int)$input['id'] : null;
    $sukan_id = isset($input['sukan_id']) ? (int)$input['sukan_id'] : 0;
    $kategori_id = isset($input['kategori_id']) ? (int)$input['kategori_id'] : 0;
    $tarikh = isset($input['tarikh']) ? trim($input['tarikh']) : '';
    $tempat_pertama = isset($input['tempat_pertama']) ? trim($input['tempat_pertama']) : null;
    $tempat_kedua = isset($input['tempat_kedua']) ? trim($input['tempat_kedua']) : null;
    $tempat_ketiga = isset($input['tempat_ketiga']) ? trim($input['tempat_ketiga']) : null;
    $status = isset($input['status']) ? trim($input['status']) : 'completed';
    
    // Validation
    if (empty($sukan_id)) {
        throw new Exception('Sukan diperlukan');
    }
    if (empty($kategori_id)) {
        throw new Exception('Kategori diperlukan');
    }
    if (empty($tarikh)) {
        throw new Exception('Tarikh diperlukan');
    }
    
    // Validate date format
    $dateObj = DateTime::createFromFormat('Y-m-d', $tarikh);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $tarikh) {
        throw new Exception('Format tarikh tidak sah');
    }
    
    $db = getDB();
    $userId = Session::get('user_id');
    
    // Get category to determine type
    $catStmt = $db->prepare("
        SELECT id, nama_kategori, penilaian, sukan_id 
        FROM table_kategori 
        WHERE id = :kategori_id 
        AND deleted_at IS NULL 
        AND status = 1
    ");
    $catStmt->execute([':kategori_id' => $kategori_id]);
    $category = $catStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        throw new Exception('Kategori tidak dijumpai');
    }
    
    if ($category['sukan_id'] != $sukan_id) {
        throw new Exception('Kategori tidak sesuai dengan sukan yang dipilih');
    }
    
    $penilaian = $category['penilaian'];
    
    // Validate participants are registered to the category
    if ($penilaian === 'individu') {
        // Validate atlet IDs
        if ($tempat_pertama) {
            $checkStmt = $db->prepare("
                SELECT id FROM table_pasukan_atlet 
                WHERE id = :id AND kategori_id = :kategori_id AND deleted_at IS NULL
            ");
            $checkStmt->execute([':id' => (int)$tempat_pertama, ':kategori_id' => $kategori_id]);
            if (!$checkStmt->fetch()) {
                throw new Exception('Peserta tempat pertama tidak didaftarkan dalam kategori ini');
            }
        }
        if ($tempat_kedua) {
            $checkStmt = $db->prepare("
                SELECT id FROM table_pasukan_atlet 
                WHERE id = :id AND kategori_id = :kategori_id AND deleted_at IS NULL
            ");
            $checkStmt->execute([':id' => (int)$tempat_kedua, ':kategori_id' => $kategori_id]);
            if (!$checkStmt->fetch()) {
                throw new Exception('Peserta tempat kedua tidak didaftarkan dalam kategori ini');
            }
        }
        if ($tempat_ketiga) {
            $checkStmt = $db->prepare("
                SELECT id FROM table_pasukan_atlet 
                WHERE id = :id AND kategori_id = :kategori_id AND deleted_at IS NULL
            ");
            $checkStmt->execute([':id' => (int)$tempat_ketiga, ':kategori_id' => $kategori_id]);
            if (!$checkStmt->fetch()) {
                throw new Exception('Peserta tempat ketiga tidak didaftarkan dalam kategori ini');
            }
        }
    } else {
        // Validate pasukan IDs
        if ($tempat_pertama) {
            $checkStmt = $db->prepare("
                SELECT p.id FROM table_pasukan p
                JOIN table_pasukan_atlet pa ON p.id = pa.pasukan_id
                WHERE p.id = :id AND pa.kategori_id = :kategori_id 
                AND pa.deleted_at IS NULL AND p.deleted_at IS NULL AND p.status = 1
            ");
            $checkStmt->execute([':id' => (int)$tempat_pertama, ':kategori_id' => $kategori_id]);
            if (!$checkStmt->fetch()) {
                throw new Exception('Pasukan tempat pertama tidak didaftarkan dalam kategori ini');
            }
        }
        if ($tempat_kedua) {
            $checkStmt = $db->prepare("
                SELECT p.id FROM table_pasukan p
                JOIN table_pasukan_atlet pa ON p.id = pa.pasukan_id
                WHERE p.id = :id AND pa.kategori_id = :kategori_id 
                AND pa.deleted_at IS NULL AND p.deleted_at IS NULL AND p.status = 1
            ");
            $checkStmt->execute([':id' => (int)$tempat_kedua, ':kategori_id' => $kategori_id]);
            if (!$checkStmt->fetch()) {
                throw new Exception('Pasukan tempat kedua tidak didaftarkan dalam kategori ini');
            }
        }
        if ($tempat_ketiga) {
            $checkStmt = $db->prepare("
                SELECT p.id FROM table_pasukan p
                JOIN table_pasukan_atlet pa ON p.id = pa.pasukan_id
                WHERE p.id = :id AND pa.kategori_id = :kategori_id 
                AND pa.deleted_at IS NULL AND p.deleted_at IS NULL AND p.status = 1
            ");
            $checkStmt->execute([':id' => (int)$tempat_ketiga, ':kategori_id' => $kategori_id]);
            if (!$checkStmt->fetch()) {
                throw new Exception('Pasukan tempat ketiga tidak didaftarkan dalam kategori ini');
            }
        }
    }
    
    // Validate no duplicate participants in different positions
    $participants = array_filter([$tempat_pertama, $tempat_kedua, $tempat_ketiga], function($p) {
        return !empty($p);
    });
    
    if (count($participants) !== count(array_unique($participants))) {
        throw new Exception('Pasukan/peserta yang sama tidak boleh dipilih untuk lebih daripada satu tempat');
    }
    
    // Check if table_results has kategori_id column
    $checkColStmt = $db->query("SHOW COLUMNS FROM table_results LIKE 'kategori_id'");
    $hasKategoriId = $checkColStmt && $checkColStmt->rowCount() > 0;
    
    // Check for duplicate results (same kategori, regardless of date)
    if ($hasKategoriId) {
        $dupStmt = $db->prepare("
            SELECT id FROM table_results 
            WHERE kategori_id = :kategori_id 
            AND deleted_at IS NULL
            " . ($id ? "AND id != :id" : "") . "
        ");
        $dupParams = [':kategori_id' => $kategori_id];
        if ($id) {
            $dupParams[':id'] = $id;
        }
        $dupStmt->execute($dupParams);
        if ($dupStmt->fetch()) {
            throw new Exception('Keputusan untuk kategori ini sudah wujud');
        }
    } else {
        // Fallback: check by sukan_id if kategori_id doesn't exist
        // Note: This is less precise but prevents duplicates at sport level
        $dupStmt = $db->prepare("
            SELECT id FROM table_results 
            WHERE sukan_id = :sukan_id 
            AND deleted_at IS NULL
            " . ($id ? "AND id != :id" : "") . "
        ");
        $dupParams = [':sukan_id' => $sukan_id];
        if ($id) {
            $dupParams[':id'] = $id;
        }
        $dupStmt->execute($dupParams);
        if ($dupStmt->fetch()) {
            throw new Exception('Keputusan untuk sukan ini sudah wujud. Sila tambah kolum kategori_id untuk penapisan yang lebih tepat.');
        }
    }
    
    
    if ($id) {
        // Update existing result
        if ($hasKategoriId) {
            $stmt = $db->prepare("
                UPDATE table_results 
                SET sukan_id = :sukan_id,
                    kategori_id = :kategori_id,
                    tarikh = :tarikh,
                    tempat_pertama = :tempat_pertama,
                    tempat_kedua = :tempat_kedua,
                    tempat_ketiga = :tempat_ketiga,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP,
                    updated_by = :updated_by
                WHERE id = :id AND deleted_at IS NULL
            ");
            $stmt->execute([
                ':id' => $id,
                ':sukan_id' => $sukan_id,
                ':kategori_id' => $kategori_id,
                ':tarikh' => $tarikh,
                ':tempat_pertama' => $tempat_pertama ?: null,
                ':tempat_kedua' => $tempat_kedua ?: null,
                ':tempat_ketiga' => $tempat_ketiga ?: null,
                ':status' => $status,
                ':updated_by' => $userId
            ]);
        } else {
            // Fallback if kategori_id doesn't exist yet
            $stmt = $db->prepare("
                UPDATE table_results 
                SET sukan_id = :sukan_id,
                    tarikh = :tarikh,
                    tempat_pertama = :tempat_pertama,
                    tempat_kedua = :tempat_kedua,
                    tempat_ketiga = :tempat_ketiga,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP,
                    updated_by = :updated_by
                WHERE id = :id AND deleted_at IS NULL
            ");
            $stmt->execute([
                ':id' => $id,
                ':sukan_id' => $sukan_id,
                ':tarikh' => $tarikh,
                ':tempat_pertama' => $tempat_pertama ?: null,
                ':tempat_kedua' => $tempat_kedua ?: null,
                ':tempat_ketiga' => $tempat_ketiga ?: null,
                ':status' => $status,
                ':updated_by' => $userId
            ]);
        }
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Keputusan tidak dijumpai atau tidak boleh dikemaskini');
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Keputusan berjaya dikemaskini',
            'id' => $id
        ]);
    } else {
        // Insert new result
        if ($hasKategoriId) {
            $stmt = $db->prepare("
                INSERT INTO table_results (
                    sukan_id, kategori_id, tarikh, 
                    tempat_pertama, tempat_kedua, tempat_ketiga, 
                    status, created_by
                ) VALUES (
                    :sukan_id, :kategori_id, :tarikh,
                    :tempat_pertama, :tempat_kedua, :tempat_ketiga,
                    :status, :created_by
                )
            ");
            $stmt->execute([
                ':sukan_id' => $sukan_id,
                ':kategori_id' => $kategori_id,
                ':tarikh' => $tarikh,
                ':tempat_pertama' => $tempat_pertama ?: null,
                ':tempat_kedua' => $tempat_kedua ?: null,
                ':tempat_ketiga' => $tempat_ketiga ?: null,
                ':status' => $status,
                ':created_by' => $userId
            ]);
        } else {
            // Fallback if kategori_id doesn't exist yet
            $stmt = $db->prepare("
                INSERT INTO table_results (
                    sukan_id, tarikh, 
                    tempat_pertama, tempat_kedua, tempat_ketiga, 
                    status, created_by
                ) VALUES (
                    :sukan_id, :tarikh,
                    :tempat_pertama, :tempat_kedua, :tempat_ketiga,
                    :status, :created_by
                )
            ");
            $stmt->execute([
                ':sukan_id' => $sukan_id,
                ':tarikh' => $tarikh,
                ':tempat_pertama' => $tempat_pertama ?: null,
                ':tempat_kedua' => $tempat_kedua ?: null,
                ':tempat_ketiga' => $tempat_ketiga ?: null,
                ':status' => $status,
                ':created_by' => $userId
            ]);
        }
        
        $newId = $db->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Keputusan berjaya direkodkan',
            'id' => $newId
        ]);
    }
} catch (Exception $e) {
    error_log('[ajax/keputusan_save] ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

