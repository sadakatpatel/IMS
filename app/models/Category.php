<?php
/**
 * Category Model Class
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Helper.php';

class Category {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($name, $description = '') {
        try {
            $sql = "INSERT INTO categories (name, description, status) VALUES (?, ?, 'Active')";
            $params = [$name, $description];
            $types = 'ss';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                Helper::logActivity('CREATE', 'categories', $this->db->lastInsertId(), null, ['name' => $name]);
                return ['success' => true, 'id' => $this->db->lastInsertId()];
            }

            return ['success' => false, 'message' => 'Failed to create category'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAll() {
        $sql = "SELECT * FROM categories WHERE status = 'Active' ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $params = [$id];
        $types = 'i';
        return $this->db->fetchOne($sql, $params, $types);
    }

    public function update($id, $name, $description) {
        try {
            $oldData = $this->getById($id);
            $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
            $params = [$name, $description, $id];
            $types = 'ssi';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                Helper::logActivity('UPDATE', 'categories', $id, $oldData, ['name' => $name]);
                return ['success' => true];
            }

            return ['success' => false];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function delete($id) {
        try {
            $oldData = $this->getById($id);
            $sql = "UPDATE categories SET status = 'Inactive' WHERE id = ?";
            $params = [$id];
            $types = 'i';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                Helper::logActivity('DELETE', 'categories', $id, $oldData, null);
                return ['success' => true];
            }

            return ['success' => false];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
