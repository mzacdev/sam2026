<?php
/**
 * Access Denied Page
 * Shown when authenticated user tries to access a page they don't have permission for
 */
define('SKIP_AUTH_CHECK', true); // Allow access to this page

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

Session::start();
$auth = getAuth();

// If not logged in, redirect to login
if (!$auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$user = $auth->getUser();
$page_title = 'Akses Ditolak';

ob_start();
?>
<div class="w-100 px-3">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card text-center">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="cil cil-lock-locked text-danger" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="card-title mb-3">Akses Ditolak</h2>
                    <p class="card-text text-muted mb-4">
                        Anda tidak mempunyai kebenaran untuk mengakses halaman ini.
                    </p>
                    
                    <?php
                    require_once __DIR__ . '/../config/database.php';
                    require_once __DIR__ . '/../config/auth.php';
                    Session::start();
                    $auth = getAuth();
                    $user = $auth->getUser();
                    if ($user):
                    ?>
                        <div class="alert alert-info">
                            <strong>Peranan anda:</strong> 
                            <span class="badge bg-primary"><?php echo htmlspecialchars($user['role']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="<?php echo url('index.php'); ?>" class="btn btn-primary me-2">
                            <i class="cil cil-home me-1"></i> Kembali ke Papan Pemuka
                        </a>
                        <a href="<?php echo url('auth/logout.php'); ?>" class="btn btn-outline-secondary">
                            <i class="cil cil-account-logout me-1"></i> Log Keluar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../includes/layout.php';
?>

