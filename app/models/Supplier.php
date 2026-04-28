<?php
/**
 * Supplier Model Class
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Helper.php';

class Supplier {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO suppliers (name, phone, email, address, city, state, zip_code, 
                    contact_person, payment_terms, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')";

            $params = [
                $data['name'] ?? '',
                $data['phone'] ?? '',
                $data['email'] ?? '',
                $data['address'] ?? '',
                $data['city'] ?? '',
                $data['state'] ?? '',
                $data['zip_code'] ?? '',
                $data['contact_person'] ?? '',
                $data['payment_terms'] ?? ''
            ];

            $types = 'sssssssss';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                Helper::logActivity('CREATE', 'suppliers', $this->db->lastInsertId(), null, $data);
                return ['success' => true, 'id' => $this->db->lastInsertId()];
            }

            return ['success' => false, 'message' => 'Failed to create supplier'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM suppliers WHERE status = 'Active' ORDER BY name ASC";
        $params = [];
        $types = '';

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params = [$limit, $offset];
            $types = 'ii';
        }

        return $this->db->fetchAll($sql, $params, $types);
    }

    public function getById($id) {
        $sql = "SELECT * FROM suppliers WHERE id = ?";
        $params = [$id];
        $types = 'i';
        return $this->db->fetchOne($sql, $params, $types);
    }

    public function update($id, $data) {
        try {
            $oldData = $this->getById($id);
            $updates = [];
            $params = [];
            $types = '';

            $allowedFields = ['name', 'phone', 'email', 'address', 'city', 'state', 'zip_code', 'contact_person', 'payment_terms', 'status'];

            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $updates[] = "$key = ?";
                    $params[] = $value;
                    $types .= 's';
                }
            }

            if (empty($updates)) {
                return ['success' => false, 'message' => 'No valid fields to update'];
            }

            $params[] = $id;
            $types .= 'i';

            $sql = "UPDATE suppliers SET " . implode(', ', $updates) . " WHERE id = ?";
            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                Helper::logActivity('UPDATE', 'suppliers', $id, $oldData, $data);
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
            $sql = "UPDATE suppliers SET status = 'Inactive' WHERE id = ?";
            $params = [$id];
            $types = 'i';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                Helper::logActivity('DELETE', 'suppliers', $id, $oldData, null);
                return ['success' => true];
            }

            return ['success' => false];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as total FROM suppliers WHERE status = 'Active'";
        $result = $this->db->fetchOne($sql);
        return $result['total'] ?? 0;
    }

    public function getPurchaseHistory($supplierId) {
        $sql = "SELECT po.*, COUNT(poi.id) as item_count FROM purchase_orders po
                LEFT JOIN purchase_order_items poi ON po.id = poi.purchase_order_id
                WHERE po.supplier_id = ? AND po.status = 'Completed'
                GROUP BY po.id
                ORDER BY po.order_date DESC";
        $params = [$supplierId];
        $types = 'i';

        return $this->db->fetchAll($sql, $params, $types);
    }
}
?>
