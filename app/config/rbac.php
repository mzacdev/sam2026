<?php
/**
 * Role-Based Access Control (RBAC) Configuration
 * SAM 2026 - Page Access Control
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

/**
 * RBAC Class for Page Access Control
 */
class RBAC {
    private $auth;
    private $db;
    
    // Role hierarchy (higher number = more permissions)
    private $roleHierarchy = [
        'VIEWER' => 1,
        'CONTINGENT' => 2,
        'JUDGE' => 3,
        'ORGANIZER' => 4,
        'ADMIN' => 5
    ];
    
    // Public pages (accessible without authentication)
    // Only authentication-related pages are public
    private $publicPages = [
        'auth/login.php',
        'auth/logout.php',
        'auth/ajax-login.php',
        'pages/access-denied.php',
    ];
    
    // Page access rules
    // All pages require authentication except those in publicPages
    // Pages listed here require specific roles, others require any authenticated user
    private $pageAccessRules = [
        // Dashboard - all authenticated users
        'index.php' => ['ADMIN', 'ORGANIZER', 'JUDGE', 'CONTINGENT', 'VIEWER'],
        
        // Main pages - role-based defaults
        // ADMIN: all pages
        // ORGANIZER: all pages except settings-group pages (settings/university/users)
        // CONTINGENT: only allowed to access 'contingent-user' page (handled below)
        'pages/contingent.php' => ['ADMIN', 'ORGANIZER'],
        'pages/sports.php' => ['ADMIN', 'ORGANIZER'],
        'pages/pasukan.php' => ['ADMIN', 'ORGANIZER'],
        'pages/venues.php' => ['ADMIN', 'ORGANIZER'],
        'pages/keputusan.php' => ['ADMIN', 'ORGANIZER'],
        'pages/results.php' => ['ADMIN', 'ORGANIZER', 'JUDGE'],
        'pages/medal-tally.php' => ['ADMIN', 'ORGANIZER'],
        'pages/reports.php' => ['ADMIN', 'ORGANIZER'],
        'pages/ringkasan.php' => ['ADMIN', 'ORGANIZER', 'VIEWER'],
        'pages/contingent-admin.php' => ['ADMIN', 'ORGANIZER', 'JUDGE', 'VIEWER'],
        'pages/checklist.php' => ['ADMIN'],
        'pages/matrix-access.php' => ['ADMIN'],
        
        // Settings - ADMIN only
        'pages/settings.php' => ['ADMIN'],
        'pages/university.php' => ['ADMIN'],
        'pages/ic_audit.php' => ['ADMIN'],
        
        // Users management - ADMIN only
        'pages/users.php' => ['ADMIN'],
        
        // New Contingent User page - accessible to CONTINGENT only
        'pages/contingent-user.php' => ['CONTINGENT'],
        'pages/pengurusan-pengguna.php' => ['ADMIN'],
    ];
    
    // If present, menu_access.json will be loaded to override static rules
    private $ensureAdmin = true;
    
    private $useDatabase = false;
    
    public function __construct() {
        $this->auth = getAuth();
        $this->db = getDB();
        
        // Load JSON-based menu access if available
        $this->loadMenuAccessJson();

        // Check if dynamic RBAC tables exist
        $this->useDatabase = $this->checkDatabaseTables();
    }
    
    /**
     * Check if dynamic RBAC tables exist
     */
    private function checkDatabaseTables() {
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'page_access_rules'");
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Load menu access configuration from JSON file if present.
     * This allows maintaining RBAC in `app/config/menu_access.json`.
     */
    private function loadMenuAccessJson() {
        $path = __DIR__ . '/menu_access.json';
        if (!file_exists($path)) {
            return;
        }

        $json = @file_get_contents($path);
        if ($json === false) {
            return;
        }

        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return;
        }

        // Replace page access rules if provided
        if (!empty($data['pages']) && is_array($data['pages'])) {
            $this->pageAccessRules = $data['pages'];
        }

        // Replace public pages if provided
        if (isset($data['public_pages']) && is_array($data['public_pages'])) {
            $this->publicPages = $data['public_pages'];
        }

        // ensure_admin flag (optional)
        if (isset($data['ensure_admin'])) {
            $this->ensureAdmin = (bool)$data['ensure_admin'];
        }
    }
    
    /**
     * Check if a page is public (no authentication required)
     */
    public function isPublicPage($pagePath) {
        $pagePath = $this->normalizePagePath($pagePath);
        
        // Only pages explicitly in publicPages list are public
        // All other pages require authentication
        return in_array($pagePath, $this->publicPages);
    }
    
    /**
     * Check if user has access to a specific page
     */
    public function hasPageAccess($pagePath) {
        // Normalize page path
        $pagePath = $this->normalizePagePath($pagePath);
        
        // Check if page is public
        if ($this->isPublicPage($pagePath)) {
            return true;
        }
        
        // Page requires authentication - check if user is logged in
        if (!$this->auth->isLoggedIn()) {
            return false;
        }
        
        // Use database if available
        if ($this->useDatabase) {
            try {
                $userId = Session::get('user_id');
                
                // Check if user_roles table exists
                $checkTable = $this->db->query("SHOW TABLES LIKE 'user_roles'");
                if ($checkTable->rowCount() > 0) {
                    // Get user's active roles from user_roles table
                    $stmt = $this->db->prepare("
                        SELECT role_id 
                        FROM user_roles 
                        WHERE user_id = :user_id 
                        AND is_active = TRUE
                        AND (expires_at IS NULL OR expires_at > NOW())
                    ");
                    $stmt->execute([':user_id' => $userId]);
                    $userRoleIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    // If user has roles in user_roles table, use database-based access control
                    if (!empty($userRoleIds)) {
                        // Check if any of user's roles have access to this page
                        $placeholders = implode(',', array_fill(0, count($userRoleIds), '?'));
                        $stmt = $this->db->prepare("
                            SELECT COUNT(*) as count
                            FROM page_role_access pra
                            INNER JOIN page_access_rules par ON pra.page_rule_id = par.id
                            WHERE par.page_path = :page_path
                            AND pra.role_id IN ($placeholders)
                        ");
                        
                        $params = [':page_path' => $pagePath];
                        foreach ($userRoleIds as $roleId) {
                            $params[] = $roleId;
                        }
                        
                        $stmt->execute($params);
                        $result = $stmt->fetch();
                        
                        return $result && $result['count'] > 0;
                    }
                    // If user_roles table exists but user has no roles, fall through to static config
                }
                // If user_roles table doesn't exist, fall through to static config
            } catch (PDOException $e) {
                error_log("RBAC Error (hasPageAccess): " . $e->getMessage());
                // Fallback to static config
            }
        }
        
        // Fallback to static configuration
        $userRole = Session::get('user_role');
        
        // If page is in pageAccessRules, check if user's role is allowed
        if (isset($this->pageAccessRules[$pagePath])) {
            return in_array($userRole, $this->pageAccessRules[$pagePath]);
        }
        
        // If page is not in pageAccessRules but is not public, require any authenticated user
        // This ensures all pages require authentication
        return true;
    }
    
    /**
     * Require access to a page (trigger modal or redirect if no access)
     * This is the central method for blocking direct URL access
     */
    public function requirePageAccess($pagePath) {
        // Normalize page path
        $pagePath = $this->normalizePagePath($pagePath);
        
        // Get access status
        $accessStatus = $this->getPageAccessStatus($pagePath);
        
        // Handle based on access status
        switch ($accessStatus) {
            case 'public':
                // Public page - allow access
                return;
                
            case 'requires_auth':
                // User not logged in - redirect to login page
                $this->redirectToLogin($pagePath);
                return;
                
            case 'allowed':
                // User has access - clear any unauthorized flags
                Session::remove('unauthorized_access');
                Session::remove('unauthorized_page');
                return;
                
            case 'denied':
                // User is logged in but doesn't have permission
                // Show access denied state
                $this->handleAccessDenied($pagePath);
                return;
        }
    }
    
    /**
     * Handle access denied for authenticated users without permission
     */
    private function handleAccessDenied($pagePath) {
        // Set unauthorized flag
        Session::set('unauthorized_access', true);
        Session::set('unauthorized_page', $pagePath);
        Session::set('access_denied_reason', 'insufficient_permissions');
        
        // Try to redirect if headers not sent
        if (!headers_sent()) {
            $this->redirectToUnauthorized();
            exit; // Stop execution after redirect
        }
        // If headers already sent, stop page rendering and let JavaScript handle redirect
        // Output minimal content and exit
        $deniedUrl = url('pages/access-denied.php');
        echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($deniedUrl) . '"></head><body><script>window.location.href="' . htmlspecialchars($deniedUrl) . '";</script></body></html>';
        exit;
    }
    
    /**
     * Check if user has minimum role level
     */
    public function hasMinimumRole($requiredRole) {
        if (!$this->auth->isLoggedIn()) {
            return false;
        }
        
        $userRole = Session::get('user_role');
        $userLevel = $this->roleHierarchy[$userRole] ?? 0;
        $requiredLevel = $this->roleHierarchy[$requiredRole] ?? 0;
        
        return $userLevel >= $requiredLevel;
    }
    
    /**
     * Require minimum role level
     */
    public function requireMinimumRole($requiredRole) {
        $this->auth->requireAuth();
        
        if (!$this->hasMinimumRole($requiredRole)) {
            $this->redirectToUnauthorized();
        }
    }
    
    /**
     * Normalize page path for comparison
     */
    private function normalizePagePath($path) {
        // If path is already normalized (starts with 'pages/', 'auth/', or 'index.php'), return as is
        if (strpos($path, 'pages/') === 0 || strpos($path, 'auth/') === 0 || $path === 'index.php') {
            // Remove query string
            $path = strtok($path, '?');
            return $path;
        }
        
        // Remove base URL
        $path = str_replace(BASE_URL, '', $path);
        $path = ltrim($path, '/');
        
        // Remove query string
        $path = strtok($path, '?');
        
        // Handle Windows paths (convert backslashes to forward slashes)
        $path = str_replace('\\', '/', $path);
        
        // Remove document root if present
        $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
        if (strpos($path, $docRoot) === 0) {
            $path = str_replace($docRoot, '', $path);
            $path = ltrim($path, '/');
        }
        
        // Remove BASE_URL if still present
        $baseUrl = ltrim(BASE_URL, '/');
        if (strpos($path, $baseUrl) === 0) {
            $path = substr($path, strlen($baseUrl));
            $path = ltrim($path, '/');
        }
        
        // Handle index.php
        if (empty($path) || $path === 'index.php' || $path === '/') {
            return 'index.php';
        }
        
        return $path;
    }
    
    /**
     * Trigger login modal (for modal-based auth)
     * Store return URL in session and set flag for JavaScript to show modal
     * NOTE: This is kept for backward compatibility but redirectToLogin is now used
     */
    private function triggerLoginModal($returnUrl = null) {
        // Redirect to login page instead of showing modal
        $this->redirectToLogin($returnUrl);
    }
    
    /**
     * Redirect to login page with return URL (fallback for non-modal flow)
     */
    private function redirectToLogin($returnUrl = null) {
        $loginUrl = url('auth/login.php');
        if ($returnUrl) {
            $loginUrl .= '?return=' . urlencode($returnUrl);
        }
        
        // Check if headers have already been sent
        if (!headers_sent()) {
            header('Location: ' . $loginUrl);
            exit;
        } else {
            // Headers already sent - use JavaScript redirect as fallback
            echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($loginUrl) . '"></head><body><script>window.location.href="' . htmlspecialchars($loginUrl) . '";</script><p>Redirecting to <a href="' . htmlspecialchars($loginUrl) . '">login page</a>...</p></body></html>';
            exit;
        }
    }
    
    /**
     * Redirect to unauthorized/access denied page
     */
    private function redirectToUnauthorized() {
        // Check if headers have already been sent
        if (!headers_sent()) {
            // Check if user is logged in
            if ($this->auth->isLoggedIn()) {
                // Authenticated user without permission - show access denied page
                header('Location: ' . url('pages/access-denied.php'));
            } else {
                // Unauthenticated user - redirect to login
                header('Location: ' . url('auth/login.php'));
            }
            exit; // Stop execution after redirect
        } else {
            // Headers already sent - use JavaScript redirect as fallback
            $redirectUrl = $this->auth->isLoggedIn() 
                ? url('pages/access-denied.php')
                : url('auth/login.php');
            echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($redirectUrl) . '"></head><body><script>window.location.href="' . htmlspecialchars($redirectUrl) . '";</script><p>Redirecting...</p></body></html>';
            exit;
        }
    }
    
    /**
     * Get allowed pages for current user
     */
    public function getAllowedPages() {
        if (!$this->auth->isLoggedIn()) {
            return [];
        }
        
        $userRole = Session::get('user_role');
        $allowedPages = [];
        
        foreach ($this->pageAccessRules as $page => $allowedRoles) {
            if (in_array($userRole, $allowedRoles)) {
                $allowedPages[] = $page;
            }
        }
        
        return $allowedPages;
    }
    
    /**
     * Check if navigation item should be visible
     * This determines whether a page appears in the navigation bar
     * Based on user's authentication status and role
     */
    public function isNavItemVisible($pagePath) {
        // Normalize page path
        $pagePath = $this->normalizePagePath($pagePath);
        
        // User must be logged in to see navigation items
        if (!$this->auth->isLoggedIn()) {
            return false;
        }
        
        // Check if user has access to the page
        return $this->hasPageAccess($pagePath);
    }
    
    /**
     * Check if a page can be accessed via direct URL
     * This is used to block direct URL access
     */
    public function canAccessPage($pagePath) {
        // Normalize page path
        $pagePath = $this->normalizePagePath($pagePath);
        
        // Public pages can always be accessed
        if ($this->isPublicPage($pagePath)) {
            return true;
        }
        
        // For protected pages, user must have access
        return $this->hasPageAccess($pagePath);
    }
    
    /**
     * Get page access status for current user
     * Returns: 'public', 'allowed', 'requires_auth', 'denied'
     */
    public function getPageAccessStatus($pagePath) {
        $pagePath = $this->normalizePagePath($pagePath);
        
        // Check if public
        if ($this->isPublicPage($pagePath)) {
            return 'public';
        }
        
        // Check if user is logged in
        if (!$this->auth->isLoggedIn()) {
            return 'requires_auth';
        }
        
        // Check if user has access
        if ($this->hasPageAccess($pagePath)) {
            return 'allowed';
        }
        
        // User is logged in but doesn't have access
        return 'denied';
    }
    
    /**
     * Clear RBAC cache
     */
    public function clearCache() {
        if ($this->useDatabase) {
            try {
                $this->db->exec("DELETE FROM rbac_cache WHERE expires_at < NOW()");
            } catch (PDOException $e) {
                error_log("RBAC Error (clearCache): " . $e->getMessage());
            }
        }
    }
    
    /**
     * Check if at least one admin exists (prevent lockout)
     */
    public function hasAtLeastOneAdmin() {
        if (!$this->useDatabase) {
            // Fallback: check users table directly
            try {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as count
                    FROM users
                    WHERE role = 'ADMIN' AND status = 'active'
                ");
                $stmt->execute();
                $result = $stmt->fetch();
                return $result && $result['count'] > 0;
            } catch (PDOException $e) {
                return true; // Assume admin exists on error
            }
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM users u
                INNER JOIN user_roles ur ON u.id = ur.user_id
                INNER JOIN roles r ON ur.role_id = r.id
                WHERE r.role_code = 'ADMIN'
                AND u.status = 'active'
                AND ur.is_active = TRUE
                AND (ur.expires_at IS NULL OR ur.expires_at > NOW())
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            
            return $result && $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("RBAC Error (hasAtLeastOneAdmin): " . $e->getMessage());
            return true; // Assume admin exists on error to prevent lockout
        }
    }
}

// Global RBAC instance
function getRBAC() {
    static $rbac = null;
    if ($rbac === null) {
        $rbac = new RBAC();
    }
    return $rbac;
}
