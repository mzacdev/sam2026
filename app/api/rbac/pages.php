<?php
/**
 * RBAC Page Access Rules API
 * Handles page access rule configuration
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/rbac.php';

Session::start();
header('Content-Type: application/json');

$auth = getAuth();
$rbac = getRBAC();
$db = getDB();

// Check if user is admin
if (!$auth->isLoggedIn() || !$rbac->hasPageAccess('pages/settings.php')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                // Get all page access rules with their roles
                $stmt = $db->query("
                    SELECT par.*,
                           GROUP_CONCAT(DISTINCT r.role_code ORDER BY r.role_code SEPARATOR ', ') as allowed_roles,
                           GROUP_CONCAT(DISTINCT r.id ORDER BY r.id SEPARATOR ',') as role_ids
                    FROM page_access_rules par
                    LEFT JOIN page_role_access pra ON par.id = pra.page_rule_id
                    LEFT JOIN roles r ON pra.role_id = r.id
                    GROUP BY par.id
                    ORDER BY par.page_path
                ");
                $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'data' => $rules]);
            } elseif ($action === 'get' && isset($_GET['id'])) {
                // Get single page rule
                $stmt = $db->prepare("SELECT * FROM page_access_rules WHERE id = :id");
                $stmt->execute([':id' => $_GET['id']]);
                $rule = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($rule) {
                    // Get roles for this page
                    $stmt = $db->prepare("
                        SELECT r.* 
                        FROM roles r
                        INNER JOIN page_role_access pra ON r.id = pra.role_id
                        WHERE pra.page_rule_id = :page_rule_id
                    ");
                    $stmt->execute([':page_rule_id' => $_GET['id']]);
                    $rule['roles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo json_encode(['success' => true, 'data' => $rule]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Peraturan tidak dijumpai']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Tindakan tidak sah']);
            }
            break;
            
        case 'POST':
            if ($action === 'create') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $pagePath = trim($data['page_path'] ?? '');
                $isPublic = isset($data['is_public']) ? (bool)$data['is_public'] : false;
                $requiresAuth = isset($data['requires_auth']) ? (bool)$data['requires_auth'] : true;
                $roleIds = $data['role_ids'] ?? [];
                
                if (empty($pagePath)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Laluan halaman diperlukan']);
                    exit;
                }
                
                // Check if rule already exists
                $stmt = $db->prepare("SELECT id FROM page_access_rules WHERE page_path = :page_path");
                $stmt->execute([':page_path' => $pagePath]);
                if ($existing = $stmt->fetch()) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Peraturan untuk halaman ini sudah wujud']);
                    exit;
                }
                
                $currentUserId = Session::get('user_id');
                
                // Create page rule
                $stmt = $db->prepare("
                    INSERT INTO page_access_rules (page_path, is_public, requires_auth, created_by)
                    VALUES (:page_path, :is_public, :requires_auth, :created_by)
                ");
                $stmt->execute([
                    ':page_path' => $pagePath,
                    ':is_public' => $isPublic,
                    ':requires_auth' => $requiresAuth,
                    ':created_by' => $currentUserId
                ]);
                
                $pageRuleId = $db->lastInsertId();
                
                // Assign roles if provided and not public
                if (!$isPublic && !empty($roleIds)) {
                    $stmt = $db->prepare("
                        INSERT INTO page_role_access (page_rule_id, role_id, created_by)
                        VALUES (:page_rule_id, :role_id, :created_by)
                    ");
                    foreach ($roleIds as $roleId) {
                        $stmt->execute([
                            ':page_rule_id' => $pageRuleId,
                            ':role_id' => $roleId,
                            ':created_by' => $currentUserId
                        ]);
                    }
                }
                
                // Clear RBAC cache
                if (method_exists($rbac, 'clearCache')) {
                    $rbac->clearCache();
                }
                
                echo json_encode(['success' => true, 'message' => 'Peraturan berjaya dicipta', 'id' => $pageRuleId]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Tindakan tidak sah']);
            }
            break;
            
        case 'PUT':
            if ($action === 'update' && isset($_GET['id'])) {
                $data = json_decode(file_get_contents('php://input'), true);
                $pageRuleId = $_GET['id'];
                
                $updates = [];
                $params = [':id' => $pageRuleId];
                
                if (isset($data['is_public'])) {
                    $updates[] = "is_public = :is_public";
                    $params[':is_public'] = (bool)$data['is_public'];
                }
                
                if (isset($data['requires_auth'])) {
                    $updates[] = "requires_auth = :requires_auth";
                    $params[':requires_auth'] = (bool)$data['requires_auth'];
                }
                
                if (!empty($updates)) {
                    $currentUserId = Session::get('user_id');
                    $updates[] = "updated_by = :updated_by";
                    $params[':updated_by'] = $currentUserId;
                    
                    $sql = "UPDATE page_access_rules SET " . implode(', ', $updates) . " WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                }
                
                // Update roles if provided
                if (isset($data['role_ids']) && is_array($data['role_ids'])) {
                    // Remove existing role assignments
                    $stmt = $db->prepare("DELETE FROM page_role_access WHERE page_rule_id = :page_rule_id");
                    $stmt->execute([':page_rule_id' => $pageRuleId]);
                    
                    // Add new role assignments
                    if (!empty($data['role_ids'])) {
                        $currentUserId = Session::get('user_id');
                        $stmt = $db->prepare("
                            INSERT INTO page_role_access (page_rule_id, role_id, created_by)
                            VALUES (:page_rule_id, :role_id, :created_by)
                        ");
                        foreach ($data['role_ids'] as $roleId) {
                            $stmt->execute([
                                ':page_rule_id' => $pageRuleId,
                                ':role_id' => $roleId,
                                ':created_by' => $currentUserId
                            ]);
                        }
                    }
                }
                
                // Clear RBAC cache
                if (method_exists($rbac, 'clearCache')) {
                    $rbac->clearCache();
                }
                
                echo json_encode(['success' => true, 'message' => 'Peraturan berjaya dikemaskini']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Tindakan tidak sah']);
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete' && isset($_GET['id'])) {
                $pageRuleId = $_GET['id'];
                
                // Delete page rule (cascade will handle page_role_access)
                $stmt = $db->prepare("DELETE FROM page_access_rules WHERE id = :id");
                $stmt->execute([':id' => $pageRuleId]);
                
                // Clear RBAC cache
                if (method_exists($rbac, 'clearCache')) {
                    $rbac->clearCache();
                }
                
                echo json_encode(['success' => true, 'message' => 'Peraturan berjaya dipadam']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Tindakan tidak sah']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Kaedah tidak dibenarkan']);
    }
} catch (PDOException $e) {
    error_log("RBAC Page API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ralat sistem: ' . $e->getMessage()]);
}

