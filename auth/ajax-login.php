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
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] === 'on';

// Validate input
if (empty($username) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Sila isi semua medan yang diperlukan'
    ]);
    exit;
}

// Attempt login
$auth = getAuth();
$result = $auth->login($username, $password);

if ($result['success']) {
    // Login successful
    // Determine redirect URL based on role
    $returnUrl = $_POST['return_url'] ?? null;
    
    // If no return URL, redirect based on role
    if (!$returnUrl) {
        switch ($result['user']['role']) {
            case 'ADMIN':
                $returnUrl = BASE_URL . 'index.php';
                break;
            case 'ORGANIZER':
                $returnUrl = BASE_URL . 'index.php';
                break;
            case 'JUDGE':
                $returnUrl = BASE_URL . 'pages/results.php';
                break;
            case 'CONTINGENT':
                $returnUrl = BASE_URL . 'pages/contingent.php';
                break;
            default:
                $returnUrl = BASE_URL . 'index.php';
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

