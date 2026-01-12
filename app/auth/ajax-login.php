<?php
/**
 * AJAX Login Endpoint
 * Handles login requests via AJAX for modal-based authentication
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Start session
Session::start();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Kaedah tidak dibenarkan'
    ]);
    exit;
}

// Get input
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] === 'on';

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Sila isi semua medan yang diperlukan'
    ]);
    exit;
}

// Attempt login
$auth = getAuth();
$result = $auth->login($email, $password);

if ($result['success']) {
    // Login successful
    // Determine redirect URL based on role
    $returnUrl = $_POST['return_url'] ?? null;
    
    // If no return URL, redirect based on role
    if (!$returnUrl) {
        switch ($result['user']['role']) {
            case 'ADMIN':
                $returnUrl = url('index.php');
                break;
            case 'ORGANIZER':
                $returnUrl = url('index.php');
                break;
            case 'JUDGE':
                $returnUrl = url('pages/results.php');
                break;
            case 'CONTINGENT':
                $returnUrl = url('pages/contingent.php');
                break;
            default:
                $returnUrl = url('index.php');
        }
    }
    
    // Normalize return URL (handle relative paths like "index.php")
    if ($returnUrl && !preg_match('#^https?://#i', $returnUrl)) {
        $returnUrl = '/' . ltrim($returnUrl, '/');
        if (BASE_URL !== '' && strpos($returnUrl, BASE_URL . '/') !== 0 && $returnUrl !== BASE_URL) {
            $returnUrl = BASE_URL . $returnUrl;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Log masuk berjaya',
        'user' => [
            'id' => $result['user']['id'],
            'username' => $result['user']['username'],
            'full_name' => $result['user']['full_name'],
            'role' => $result['user']['role']
        ],
        'redirect_url' => $returnUrl
    ]);
} else {
    // Login failed
    echo json_encode([
        'success' => false,
        'message' => $result['message'] ?? 'Log masuk gagal. Sila cuba lagi.'
    ]);
}
