<?php
/**
 * Login Page
 * SAM 2026 - Administrator Login Only (Phase 1)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Start session
Session::start();

// Redirect if already logged in
$auth = getAuth();
if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Sila isi semua medan yang diperlukan';
    } else {
        $result = $auth->login($username, $password);
        
        if ($result['success']) {
            // Determine redirect URL based on role
            $returnUrl = $_GET['return'] ?? null;
            
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
            
            // Validate return URL to prevent open redirect
            $returnUrl = filter_var($returnUrl, FILTER_SANITIZE_URL);
            if (strpos($returnUrl, BASE_URL) === 0 || strpos($returnUrl, '/') === 0) {
                // Redirect with success flag
                header('Location: ' . $returnUrl . '?login=success');
            } else {
                header('Location: ' . BASE_URL . 'index.php?login=success');
            }
            exit;
        } else {
            $error = $result['message'];
            // Show detailed error in development mode
            if (isset($result['error']) && (defined('DEBUG_MODE') && DEBUG_MODE)) {
                $error .= '<br><small class="text-muted">Debug: ' . htmlspecialchars($result['error']) . '</small>';
            }
        }
    }
}

$page_title = 'Log Masuk';
ob_start();
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- CoreUI CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.3.0/dist/css/coreui.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@coreui/icons@3.0.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/custom.css'); ?>">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        .login-card {
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .login-header {
            text-align: center;
            padding: 2rem 1rem 1rem;
        }
        .login-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .login-logo img {
            width: 60px;
            height: 60px;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Memuatkan...</span>
            </div>
            <div class="mt-3">
                <p class="text-white mb-0">Memproses log masuk...</p>
                <small class="text-white-50">Sila tunggu sebentar</small>
            </div>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container-custom"></div>
    
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <div class="login-logo">
                    <img src="<?php echo logo(LOGO_HEADER); ?>" alt="<?php echo SITE_NAME; ?>">
                </div>
                <h3 class="mb-1"><?php echo SITE_NAME; ?></h3>
                <p class="text-muted mb-0">Sistem Pengurusan Kejohanan</p>
            </div>
            
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="cil cil-warning me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="cil cil-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nama Pengguna atau E-mel <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="cil cil-user"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Masukkan nama pengguna atau e-mel" 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                   required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Kata Laluan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="cil cil-lock-locked"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Masukkan kata laluan" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="cil cil-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe" name="remember_me">
                        <label class="form-check-label" for="rememberMe">
                            Ingat saya
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" id="loginSubmitBtn">
                            <i class="cil cil-account-logout me-2"></i>
                            <span id="loginBtnText">Log Masuk</span>
                        </button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <small class="text-muted">
                        <i class="cil cil-info me-1"></i>
                        Sila log masuk dengan akaun anda
                    </small>
                </div>
            </div>
            
            <div class="card-footer text-center bg-light">
                <small class="text-muted">
                    &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Hak cipta terpelihara.
                </small>
            </div>
        </div>
    </div>
    
    <!-- CoreUI JS -->
    <script src="https://cdn.jsdelivr.net/npm/@coreui/coreui@4.3.0/dist/js/coreui.bundle.min.js"></script>
    
    <!-- Custom JS (for LoadingOverlay and Toast) -->
    <script src="<?php echo asset('js/custom.js'); ?>"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('cil-eye');
                eyeIcon.classList.add('cil-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('cil-eye-slash');
                eyeIcon.classList.add('cil-eye');
            }
        });
        
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('loginSubmitBtn');
            const btnText = document.getElementById('loginBtnText');
            
            if (!username || !password) {
                e.preventDefault();
                alert('Sila isi semua medan yang diperlukan');
                return false;
            }
            
            // Show loading state on button
            submitBtn.disabled = true;
            btnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengesahkan...';
            
            // Show loading overlay
            if (typeof LoadingOverlay !== 'undefined') {
                LoadingOverlay.show('Mengesahkan log masuk...');
            }
        });
    </script>
</body>
</html>
<?php
$content = ob_get_clean();
echo $content;
?>

