<?php
/**
 * Contingent Model
 * Handles all database operations for contingents (kontinjen)
 */

require_once __DIR__ . '/../../config/database.php';

class ContingentModel {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Create a new contingent
     * 
     * @param array $data Contingent data
     * @return array Result with success status and data
     */
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO table_kontinjen (
                    kod_universiti,
                    nama_pegawai_untuk_dihubungi,
                    alamat,
                    emel,
                    no_telefon,
                    status,
                    created_by
                ) VALUES (
                    :kod_universiti,
                    :nama_pegawai_untuk_dihubungi,
                    :alamat,
                    :emel,
                    :no_telefon,
                    :status,
                    :created_by
                )
            ");
            
            $result = $stmt->execute([
                ':kod_universiti' => $data['kod_universiti'],
                ':nama_pegawai_untuk_dihubungi' => $data['nama_pegawai_untuk_dihubungi'],
                ':alamat' => $data['alamat'],
                ':emel' => $data['emel'],
                ':no_telefon' => $data['no_telefon'],
                ':status' => isset($data['status']) ? (int)$data['status'] : 0,
                ':created_by' => $data['created_by'] ?? null
            ]);
            
            if ($result) {
                $id = $this->db->lastInsertId();
                return [
                    'success' => true,
                    'message' => 'Kontinjen berjaya didaftarkan',
                    'data' => ['id' => $id]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Gagal mendaftar kontinjen'
            ];
        } catch (PDOException $e) {
            error_log('[ContingentModel::create] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ralat sistem: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all contingents with pagination and filters
     * 
     * @param array $params Query parameters (limit, offset, search, status)
     * @return array Result with success status and data
     */
    public function getAll($params = []) {
        try {
            $limit = $params['limit'] ?? 50;
            $offset = $params['offset'] ?? 0;
            $search = $params['search'] ?? '';
            $status = $params['status'] ?? null;
            
            $where = ['k.deleted_at IS NULL'];
            $bindings = [];
            
            if (!empty($search)) {
                $where[] = "(k.nama_pegawai_untuk_dihubungi LIKE :search 
                            OR k.emel LIKE :search 
                            OR u.nama_universiti LIKE :search 
                            OR k.kod_universiti LIKE :search)";
                $bindings[':search'] = '%' . $search . '%';
            }
            
            if ($status !== null) {
                $where[] = "k.status = :status";
                $bindings[':status'] = $status;
            }
            
            $whereClause = implode(' AND ', $where);
            
            $sql = "
                SELECT 
                    k.id,
                    k.kod_universiti,
                    u.nama_universiti,
                    k.nama_pegawai_untuk_dihubungi,
                    k.alamat,
                    k.emel,
                    k.no_telefon,
                    k.status,
                    k.created_at,
                    k.updated_at,
                    creator.full_name AS created_by_name,
                    updater.full_name AS updated_by_name
                FROM table_kontinjen k
                LEFT JOIN table_ref_universiti u ON k.kod_universiti = u.kod_universiti
                LEFT JOIN users creator ON k.created_by = creator.id
                LEFT JOIN users updater ON k.updated_by = updater.id
                WHERE {$whereClause}
                ORDER BY k.created_at DESC
                LIMIT :limit OFFSET :offset
            ";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($bindings as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $contingents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $countSql = "
                SELECT COUNT(*) AS total
                FROM table_kontinjen k
                LEFT JOIN table_ref_universiti u ON k.kod_universiti = u.kod_universiti
                WHERE {$whereClause}
            ";
            
            $countStmt = $this->db->prepare($countSql);
            foreach ($bindings as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            return [
                'success' => true,
                'data' => $contingents,
                'total' => (int)$total,
                'limit' => (int)$limit,
                'offset' => (int)$offset
            ];
        } catch (PDOException $e) {
            error_log('[ContingentModel::getAll] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ralat sistem: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Get single contingent by ID
     * 
     * @param int $id Contingent ID
     * @return array Result with success status and data
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    k.id,
                    k.kod_universiti,
                    u.nama_universiti,
                    k.nama_pegawai_untuk_dihubungi,
                    k.alamat,
                    k.emel,
                    k.no_telefon,
                    k.status,
                    k.created_at,
                    k.updated_at,
                    k.created_by,
                    k.updated_by,
                    creator.full_name AS created_by_name,
                    updater.full_name AS updated_by_name
                FROM table_kontinjen k
                LEFT JOIN table_ref_universiti u ON k.kod_universiti = u.kod_universiti
                LEFT JOIN users creator ON k.created_by = creator.id
                LEFT JOIN users updater ON k.updated_by = updater.id
                WHERE k.id = :id 
                AND k.deleted_at IS NULL
            ");
            
            $stmt->execute([':id' => $id]);
            $contingent = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($contingent) {
                return [
                    'success' => true,
                    'data' => $contingent
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Kontinjen tidak dijumpai',
                'data' => null
            ];
        } catch (PDOException $e) {
            error_log('[ContingentModel::getById] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ralat sistem: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Get contingent by university code
     * 
     * @param string $kod_universiti University code
     * @return array Result with success status and data
     */
    public function getByUniversityCode($kod_universiti) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    k.id,
                    k.kod_universiti,
                    u.nama_universiti,
                    k.nama_pegawai_untuk_dihubungi,
                    k.alamat,
                    k.emel,
                    k.no_telefon,
                    k.status,
                    k.created_at,
                    k.updated_at
                FROM table_kontinjen k
                LEFT JOIN table_ref_universiti u ON k.kod_universiti = u.kod_universiti
                WHERE k.kod_universiti = :kod_universiti 
                AND k.deleted_at IS NULL
                ORDER BY k.created_at DESC
                LIMIT 1
            ");
            
            $stmt->execute([':kod_universiti' => $kod_universiti]);
            $contingent = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($contingent) {
                return [
                    'success' => true,
                    'data' => $contingent
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Kontinjen tidak dijumpai',
                'data' => null
            ];
        } catch (PDOException $e) {
            error_log('[ContingentModel::getByUniversityCode] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ralat sistem: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Update contingent
     * 
     * @param int $id Contingent ID
     * @param array $data Updated data
     * @return array Result with success status and message
     */
    public function update($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE table_kontinjen
                SET 
                    kod_universiti = :kod_universiti,
                    nama_pegawai_untuk_dihubungi = :nama_pegawai_untuk_dihubungi,
                    alamat = :alamat,
                    emel = :emel,
                    no_telefon = :no_telefon,
                    status = :status,
                    updated_by = :updated_by,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
                AND deleted_at IS NULL
            ");
            
            $result = $stmt->execute([
                ':id' => $id,
                ':kod_universiti' => $data['kod_universiti'],
                ':nama_pegawai_untuk_dihubungi' => $data['nama_pegawai_untuk_dihubungi'],
                ':alamat' => $data['alamat'],
                ':emel' => $data['emel'],
                ':no_telefon' => $data['no_telefon'],
                ':status' => isset($data['status']) ? (int)$data['status'] : 0,
                ':updated_by' => $data['updated_by'] ?? null
            ]);
            
            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Kontinjen berjaya dikemaskini'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Kontinjen tidak dijumpai atau tiada perubahan'
            ];
        } catch (PDOException $e) {
            error_log('[ContingentModel::update] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ralat sistem: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Soft delete contingent
     * 
     * @param int $id Contingent ID
     * @param int $deleted_by User ID who deleted
     * @return array Result with success status and message
     */
    public function delete($id, $deleted_by = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE table_kontinjen
                SET 
                    deleted_at = CURRENT_TIMESTAMP,
                    updated_by = :updated_by,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
                AND deleted_at IS NULL
            ");
            
            $result = $stmt->execute([
                ':id' => $id,
                ':updated_by' => $deleted_by
            ]);
            
            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Kontinjen berjaya dipadam'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Kontinjen tidak dijumpai'
            ];
        } catch (PDOException $e) {
            error_log('[ContingentModel::delete] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ralat sistem: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Hard delete contingent (permanent removal)
     * 
     * @param int $id Contingent ID
     * @return array Result with success status and message
     */
    public function hardDelete($id) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM table_kontinjen
                WHERE id = :id
            ");
            
            $result = $stmt->execute([':id' => $id]);
            
            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Kontinjen berjaya dipadam secara kekal'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Kontinjen tidak dijumpai'
            ];
        } catch (PDOException $e) {
            error_log('[ContingentModel::hardDelete] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ralat sistem: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if university already has an active contingent
     * 
     * @param string $kod_universiti University code
     * @return array Result with success status and data
     */
    public function checkUniversityExists($kod_universiti) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS count
                FROM table_kontinjen
                WHERE kod_universiti = :kod_universiti
                AND status = 1
                AND deleted_at IS NULL
            ");
            
            $stmt->execute([':kod_universiti' => $kod_universiti]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'exists' => (int)$result['count'] > 0,
                'count' => (int)$result['count']
            ];
        } catch (PDOException $e) {
            error_log('[ContingentModel::checkUniversityExists] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'exists' => false,
                'message' => 'Ralat sistem: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get contingent statistics
     * 
     * @return array Result with success status and statistics
     */
    public function getStatistics() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS inactive
                FROM table_kontinjen
                WHERE deleted_at IS NULL
            ");
            
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => [
                    'total' => (int)$stats['total'],
                    'active' => (int)$stats['active'],
                    'inactive' => (int)$stats['inactive']
                ]
            ];
        } catch (PDOException $e) {
            error_log('[ContingentModel::getStatistics] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Ralat sistem: ' . $e->getMessage(),
                'data' => [
                    'total' => 0,
                    'active' => 0,
                    'pending' => 0,
                    'inactive' => 0,
                    'suspended' => 0
                ]
            ];
        }
    }
    
    /**
     * Count total contingents
     * 
     * @return int Total count
     */
    public function count() {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) AS total
                FROM table_kontinjen
                WHERE deleted_at IS NULL
            ");
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log('[ContingentModel::count] Error: ' . $e->getMessage());
            return 0;
        }
    }
}

