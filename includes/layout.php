<?php
/**
 * Base Layout Template
 * This file provides the main layout structure
 */

// Initialize auth and RBAC for all pages BEFORE any output
// This must happen before header.php is included to allow redirects
if (!defined('SKIP_AUTH_CHECK')) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/auth.php';
    require_once __DIR__ . '/../config/rbac.php';
    
    // Session should already be started in config.php, but ensure it's started
    if (session_status() === PHP_SESSION_NONE) {
        Session::start();
    }
    $auth = getAuth();
    $rbac = getRBAC();
    
    // Get current page path
    $currentPage = $_SERVER['PHP_SELF'] ?? 'index.php';
    $scriptPath = str_replace('\\', '/', $currentPage);
    $basePath = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] . BASE_URL);
    $relativePath = str_replace($basePath, '', $scriptPath);
    $relativePath = ltrim($relativePath, '/');
    
    // Normalize path
    if (empty($relativePath) || $relativePath === 'index.php') {
        $relativePath = 'index.php';
    }
    
    // Always check page access (for all pages, including public ones)
    // This ensures all pages require authentication except public pages
    // If user is not logged in, they will be redirected to login page
    // This MUST happen before header.php is included to allow redirects
    $rbac->requirePageAccess($relativePath);
    
    // Login modal is no longer used - users are redirected to login page
    $showLoginModal = false;
} else {
    $showLoginModal = false;
    $auth = null;
    $rbac = null;
}

// Now include header.php after authentication check
require_once __DIR__ . '/header.php';
?>
<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Memuatkan...</span>
        </div>
        <div class="mt-3">
            <p class="text-white mb-0">Memuatkan sistem...</p>
            <small class="text-white-50">Sila tunggu sebentar</small>
        </div>
    </div>
</div>

<div class="wrapper d-flex flex-column min-vh-100">
    <?php require_once __DIR__ . '/topbar.php'; ?>
    
    <div class="body flex-grow-1 content-wrapper">
        <?php echo $content; ?>
    </div>
    
    <?php require_once __DIR__ . '/footer.php'; ?>
</div>

<!-- Login Modal -->
<?php if (!defined('SKIP_AUTH_CHECK')): ?>
    <?php require_once __DIR__ . '/login-modal.php'; ?>
<?php endif; ?>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container-custom"></div>

<script>
// Check for login success and show toast
document.addEventListener('DOMContentLoaded', function() {
    // Check URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('login') === 'success') {
        // Show loading spinner first
        if (typeof LoadingOverlay !== 'undefined') {
            LoadingOverlay.show('Memproses log masuk...');
        }
        
        // Wait a moment, then show success toast
        setTimeout(function() {
            if (typeof Toast !== 'undefined') {
                Toast.success('Log Masuk Berjaya! Selamat datang kembali.', 3000);
            }
            
            // Hide loading spinner
            if (typeof LoadingOverlay !== 'undefined') {
                setTimeout(function() {
                    LoadingOverlay.hide();
                }, 500);
            }
            
            // Clean URL (remove ?login=success)
            if (window.history && window.history.replaceState) {
                const cleanUrl = window.location.pathname + window.location.search.replace(/[?&]login=success/, '').replace(/^&/, '?');
                window.history.replaceState({}, document.title, cleanUrl || window.location.pathname);
            }
        }, 300);
    }
    
    // Show login modal if needed
    <?php if (isset($showLoginModal) && $showLoginModal): ?>
    if (typeof LoginModal !== 'undefined') {
        const returnUrl = '<?php echo htmlspecialchars(Session::get('login_return_url', ''), ENT_QUOTES, 'UTF-8'); ?>';
        LoginModal.show(returnUrl || window.location.href);
        <?php Session::remove('login_return_url'); ?>
    }
    <?php endif; ?>
    
    // Handle unauthorized access
    <?php 
    $unauthorizedAccess = Session::get('unauthorized_access', false);
    $isLoggedIn = isset($auth) && $auth->isLoggedIn();
    $accessDeniedReason = Session::get('access_denied_reason', '');
    
    // Show appropriate message based on user status
    if ($unauthorizedAccess): 
        if ($isLoggedIn && $accessDeniedReason === 'insufficient_permissions'):
            // Authenticated user without permission - show access denied
    ?>
    if (typeof Toast !== 'undefined') {
        Toast.error('Anda tidak mempunyai kebenaran untuk mengakses halaman ini.', 5000);
    }
    // Redirect to access denied page or dashboard
    setTimeout(function() {
        window.location.href = '<?php echo url('pages/access-denied.php'); ?>';
    }, 2000);
    <?php 
        else:
            // User not logged in - login modal will handle this
            // Just clear the flag
    ?>
    // User not logged in - login modal will handle authentication
    <?php 
        endif;
        Session::remove('unauthorized_access');
        Session::remove('unauthorized_page');
        Session::remove('access_denied_reason');
    ?>
    <?php endif; ?>
});
</script>

