<?php
// AJAX endpoint: Generate CSV template for bulk pasukan upload
ini_set('display_errors', '0');

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e){
    http_response_code(500);
    error_log('[ajax/pasukan_template][exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Ralat: ' . $e->getMessage();
    exit;
});

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../api/models/ContingentModel.php';
require_once __DIR__ . '/../api/models/SportModel.php';

if (session_status() === PHP_SESSION_NONE) {
    Session::start();
}

$auth = getAuth();
if (!$auth->isLoggedIn()) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Sesi tamat. Sila log masuk semula.';
    exit;
}

$userRole = Session::get('user_role');
if (!in_array($userRole, ['ADMIN', 'ORGANIZER', 'CONTINGENT'])) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Anda tidak mempunyai kebenaran.';
    exit;
}

try {
    // Fetch contingents and sports for template
    $contingentModel = new ContingentModel();
    $contingentsResult = $contingentModel->getAll(['limit' => 1000, 'status' => 1]);
    $contingents = $contingentsResult['success'] ? $contingentsResult['data'] : [];
    
    $sportModel = new SportModel();
    $sportsResult = $sportModel->getAll(['limit' => 1000, 'status' => 1]);
    $sports = $sportsResult['success'] ? $sportsResult['data'] : [];
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="template_pasukan_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output UTF-8 BOM for Excel compatibility
    echo "\xEF\xBB\xBF";
    
    // Instructions/Comments rows
    echo "# Template Muat Naik Pukal Pasukan\n";
    echo "# Format: Setiap pasukan menggunakan beberapa baris\n";
    echo "# Baris TEAM: nama_pasukan,kontinjen_id,sukan_id,status\n";
    echo "# Baris PENGURUS: nama,no_kad_pengenalan,no_telefon,emel\n";
    echo "# Baris JURULATIH: nama,no_kad_pengenalan,no_telefon,emel\n";
    echo "# Baris ATLET: nama,no_kad_pengenalan,no_matrik,kategori_id\n";
    echo "# Kosongkan baris untuk memisahkan pasukan\n";
    echo "#\n";
    
    // Contingent reference
    if (!empty($contingents)) {
        echo "# Rujukan Kontinjen:\n";
        foreach ($contingents as $c) {
            echo "#   " . (int)$c['id'] . " = " . htmlspecialchars($c['nama_universiti'] ?? '', ENT_QUOTES, 'UTF-8') . "\n";
        }
        echo "#\n";
    }
    
    // Sports reference
    if (!empty($sports)) {
        echo "# Rujukan Sukan:\n";
        foreach ($sports as $s) {
            echo "#   " . (int)$s['id'] . " = " . htmlspecialchars($s['nama_sukan'] ?? '', ENT_QUOTES, 'UTF-8') . "\n";
        }
        echo "#\n";
    }
    
    // Sample data - Team 1
    $sampleContingentId = !empty($contingents) ? (int)$contingents[0]['id'] : 1;
    $sampleSportId = !empty($sports) ? (int)$sports[0]['id'] : 1;
    
    echo "TEAM,Pasukan Contoh A," . $sampleContingentId . "," . $sampleSportId . ",1\n";
    echo "PENGURUS,Nama Pengurus,IC Pengurus,0123456789,pengurus@example.com\n";
    echo "JURULATIH,Nama Jurulatih,IC Jurulatih,0123456788,jurulatih@example.com\n";
    echo "ATLET,Nama Atlet 1,IC Atlet 1,MAT123456,1\n";
    echo "ATLET,Nama Atlet 2,IC Atlet 2,MAT123457,1\n";
    echo "\n";
    
    // Sample data - Team 2
    echo "TEAM,Pasukan Contoh B," . $sampleContingentId . "," . $sampleSportId . ",1\n";
    echo "PENGURUS,Nama Pengurus B,IC Pengurus B,0123456787,pengurusb@example.com\n";
    echo "JURULATIH,Nama Jurulatih B,IC Jurulatih B,0123456786,jurulatihb@example.com\n";
    echo "ATLET,Nama Atlet B1,IC Atlet B1,MAT123458,1\n";
    echo "\n";
    
} catch (Exception $e) {
    error_log('[ajax/pasukan_template] ' . $e->getMessage() . " in " . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    $msg = (defined('DEBUG_MODE') && DEBUG_MODE) ? $e->getMessage() : 'Ralat menjana template.';
    echo $msg;
    exit;
}

