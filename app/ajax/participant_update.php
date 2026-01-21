<?php
/**
 * AJAX endpoint: Update individual participant field
 * Roles: ADMIN, ORGANIZER, VIEWER
 * Updates participant fields including pasukan_id/kategori_id (athletes)
 */
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/participant_update][exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
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
require_once __DIR__ . '/../config/rbac.php';

if (session_status() === PHP_SESSION_NONE) {
    Session::start();
}

$auth = getAuth();
if (!$auth->isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sesi tamat. Sila log masuk semula.']);
    exit;
}

$rbac = getRBAC();
// Allow ADMIN, ORGANIZER, and VIEWER to edit participants from contingent-admin.php
$userRole = Session::get('user_role');
if (!in_array($userRole, ['ADMIN', 'ORGANIZER', 'VIEWER'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya ADMIN, ORGANIZER, atau VIEWER dibenarkan untuk mengedit peserta.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Kaedah tidak dibenarkan.']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $participant_type = isset($input['participant_type']) ? trim($input['participant_type']) : '';
    $participant_id = isset($input['participant_id']) ? (int)$input['participant_id'] : 0;
    $field_name = isset($input['field_name']) ? trim($input['field_name']) : '';
    $field_value = isset($input['field_value']) ? trim($input['field_value']) : '';
    
    // Validation
    if (empty($participant_type) || !in_array($participant_type, ['atlet', 'pengurus', 'jurulatih'])) {
        throw new Exception('Jenis peserta tidak sah.');
    }
    
    if ($participant_id <= 0) {
        throw new Exception('ID peserta tidak sah.');
    }
    
    // Define allowed fields for each participant type
    $allowedFields = [
        'atlet' => ['nama', 'no_kad_pengenalan', 'no_matrik', 'pasukan_id', 'kategori_id'],
        'pengurus' => ['nama', 'no_kad_pengenalan', 'no_telefon', 'emel', 'pasukan_id'],
        'jurulatih' => ['nama', 'no_kad_pengenalan', 'no_telefon', 'emel', 'pasukan_id']
    ];
    
    if (!in_array($field_name, $allowedFields[$participant_type])) {
        throw new Exception('Medan tidak boleh diedit atau tidak sah untuk jenis peserta ini.');
    }
    
    $db = getDB();
    
    // Map participant type to table name
    $tableMap = [
        'atlet' => 'table_pasukan_atlet',
        'pengurus' => 'table_pasukan_pengurus',
        'jurulatih' => 'table_pasukan_jurulatih'
    ];
    
    $tableName = $tableMap[$participant_type];
    
    // Check if participant exists and is not deleted
    $checkStmt = $db->prepare("
        SELECT * 
        FROM {$tableName} 
        WHERE id = :id AND deleted_at IS NULL
    ");
    $checkStmt->execute([':id' => $participant_id]);
    $participant = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$participant) {
        throw new Exception('Peserta tidak dijumpai atau telah dipadam.');
    }
    
    // Validate pasukan/kategori changes
    $targetPasukanId = $participant['pasukan_id'] ?? null;
    $targetPasukanSukanId = null;
    
    if ($field_name === 'pasukan_id') {
        $newPasukanId = (int)$field_value;
        if ($newPasukanId <= 0) {
            throw new Exception('Pasukan tidak sah.');
        }
        $pasukanStmt = $db->prepare("SELECT id, sukan_id, status, deleted_at FROM table_pasukan WHERE id = :id");
        $pasukanStmt->execute([':id' => $newPasukanId]);
        $pasukan = $pasukanStmt->fetch(PDO::FETCH_ASSOC);
        if (!$pasukan || $pasukan['deleted_at'] !== null || (isset($pasukan['status']) && (int)$pasukan['status'] !== 1)) {
            throw new Exception('Pasukan tidak dijumpai atau tidak aktif.');
        }
        $targetPasukanId = $newPasukanId;
        $targetPasukanSukanId = $pasukan['sukan_id'] ?? null;

        // For atlet, clear kategori if mismatched sukan
        if ($participant_type === 'atlet' && !empty($participant['kategori_id'])) {
            $katStmt = $db->prepare("SELECT sukan_id FROM table_kategori WHERE id = :id AND deleted_at IS NULL AND status = 1");
            $katStmt->execute([':id' => $participant['kategori_id']]);
            $kat = $katStmt->fetch(PDO::FETCH_ASSOC);
            if (!$kat || (int)$kat['sukan_id'] !== (int)$targetPasukanSukanId) {
                $participant['kategori_id'] = null; // clear later when updating pasukan_id
            }
        }
    }
    
    if ($field_name === 'kategori_id') {
        if ($participant_type !== 'atlet') {
            throw new Exception('Kategori hanya terpakai untuk atlet.');
        }
        $newKategoriId = (int)$field_value;
        if ($newKategoriId <= 0) {
            throw new Exception('Kategori tidak sah.');
        }
        $katStmt = $db->prepare("SELECT id, sukan_id FROM table_kategori WHERE id = :id AND deleted_at IS NULL AND status = 1");
        $katStmt->execute([':id' => $newKategoriId]);
        $kat = $katStmt->fetch(PDO::FETCH_ASSOC);
        if (!$kat) {
            throw new Exception('Kategori tidak dijumpai atau tidak aktif.');
        }
        if (!$targetPasukanId) {
            $targetPasukanId = $participant['pasukan_id'] ?? null;
        }
        if (!$targetPasukanId) {
            throw new Exception('Pasukan belum ditetapkan untuk peserta ini.');
        }
        $pasukanStmt = $db->prepare("SELECT sukan_id, status, deleted_at FROM table_pasukan WHERE id = :id");
        $pasukanStmt->execute([':id' => $targetPasukanId]);
        $pasukan = $pasukanStmt->fetch(PDO::FETCH_ASSOC);
        if (!$pasukan || $pasukan['deleted_at'] !== null || (int)$pasukan['status'] !== 1) {
            throw new Exception('Pasukan tidak dijumpai atau tidak aktif.');
        }
        if ((int)$pasukan['sukan_id'] !== (int)$kat['sukan_id']) {
            throw new Exception('Kategori tidak sepadan dengan sukan pasukan.');
        }
    }
    
    // Prepare update query
    $fieldMap = [
        'nama' => 'nama',
        'no_kad_pengenalan' => 'no_kad_pengenalan',
        'no_matrik' => 'no_matrik',
        'no_telefon' => 'no_telefon',
        'emel' => 'emel',
        'pasukan_id' => 'pasukan_id',
        'kategori_id' => 'kategori_id'
    ];
    
    if (!isset($fieldMap[$field_name])) {
        throw new Exception('Medan tidak sah.');
    }
    
    $dbFieldName = $fieldMap[$field_name];
    
    // Validate field value based on field type
    if ($field_name === 'nama' && empty($field_value)) {
        throw new Exception('Nama tidak boleh kosong.');
    }
    
    if ($field_name === 'no_kad_pengenalan' && !empty($field_value)) {
        // Basic validation: remove non-digits and check length (Malaysian IC format)
        $cleanIc = preg_replace('/\D+/', '', $field_value);
        if (strlen($cleanIc) !== 12) {
            throw new Exception('No Kad Pengenalan mesti 12 digit.');
        }
        $field_value = $cleanIc;
    }
    
    if ($field_name === 'pasukan_id') {
        if (!ctype_digit((string)$field_value) || (int)$field_value <= 0) {
            throw new Exception('Pasukan tidak sah.');
        }
        $field_value = (int)$field_value;
    }
    
    if ($field_name === 'kategori_id') {
        if (!ctype_digit((string)$field_value) || (int)$field_value <= 0) {
            throw new Exception('Kategori tidak sah.');
        }
        $field_value = (int)$field_value;
    }
    
    if ($field_name === 'no_matrik' && !empty($field_value)) {
        // Basic validation: max length
        if (mb_strlen($field_value) > 50) {
            throw new Exception('No Matrik tidak boleh melebihi 50 aksara.');
        }
    }
    
    if ($field_name === 'no_telefon' && !empty($field_value)) {
        // Basic validation: max length
        if (mb_strlen($field_value) > 20) {
            throw new Exception('No Telefon tidak boleh melebihi 20 aksara.');
        }
    }
    
    if ($field_name === 'emel' && !empty($field_value)) {
        // Basic validation: email format and max length
        if (mb_strlen($field_value) > 100) {
            throw new Exception('Emel tidak boleh melebihi 100 aksara.');
        }
        if (!filter_var($field_value, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format emel tidak sah.');
        }
    }
    
    // Update the field (handle clearing kategori when pasukan changes)
    if ($field_name === 'pasukan_id' && $participant_type === 'atlet' && empty($participant['kategori_id'])) {
        $updateStmt = $db->prepare("
            UPDATE {$tableName} 
            SET {$dbFieldName} = :value, kategori_id = NULL, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND deleted_at IS NULL
        ");
        $updateStmt->execute([
            ':value' => $field_value,
            ':id' => $participant_id
        ]);
    } else {
        $updateStmt = $db->prepare("
            UPDATE {$tableName} 
            SET {$dbFieldName} = :value, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND deleted_at IS NULL
        ");
        
        // For nullable fields, allow empty string to be stored as NULL
        $valueToStore = ($field_value === '') ? null : $field_value;
        
        $updateStmt->execute([
            ':value' => $valueToStore,
            ':id' => $participant_id
        ]);
    }
    
    if ($updateStmt->rowCount() === 0) {
        throw new Exception('Gagal mengemaskini data peserta.');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Data peserta berjaya dikemaskini.',
        'participant_id' => $participant_id,
        'field_name' => $field_name,
        'field_value' => $field_value
    ]);
    
} catch (Exception $e) {
    error_log('[ajax/participant_update] ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

