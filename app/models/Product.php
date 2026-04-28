<?php
/**
 * Product Model Class
 * Handles all product-related database operations
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Helper.php';

class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create new product
     */
    public function create($data) {
        try {
            $sku = (!empty($data['sku'])) ? $data['sku'] : Helper::generateSKU();

            $sql = "INSERT INTO products (name, category_id, sku, description, purchase_price, 
                    selling_price, quantity, reorder_level, image_path, barcode, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')";

            $params = [
                $data['name'] ?? '',
                $data['category_id'] ?? 1,
                $sku,
                $data['description'] ?? '',
                $data['purchase_price'] ?? 0,
                $data['selling_price'] ?? 0,
                $data['quantity'] ?? 0,
                $data['reorder_level'] ?? 10,
                $data['image_path'] ?? null,
                $data['barcode'] ?? null
            ];

            $types = 'sissddiiss';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                $productId = $this->db->lastInsertId();
                Helper::logActivity('CREATE', 'products', $productId, null, $data);
                return ['success' => true, 'id' => $productId];
            }

            return ['success' => false, 'message' => 'Failed to create product'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get product by ID
     */
    public function getById($id) {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?";
        $params = [$id];
        $types = 'i';

        return $this->db->fetchOne($sql, $params, $types);
    }

    /**
     * Get all products
     */
    public function getAll($limit = null, $offset = 0, $filters = []) {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";

        $params = [];
        $types = '';

        // Filter by status
        if (isset($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        // Filter by category
        if (isset($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
            $types .= 'i';
        }

        // Search by name
        if (isset($filters['search'])) {
            $sql .= " AND p.name LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
            $types .= 's';
        }

        $sql .= " ORDER BY p.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
        }

        return $this->db->fetchAll($sql, $params, $types);
    }

    /**
     * Update product
     */
    public function update($id, $data) {
        try {
            $oldData = $this->getById($id);
            $updates = [];
            $params = [];
            $types = '';

            $allowedFields = ['name', 'category_id', 'description', 'purchase_price', 
                            'selling_price', 'reorder_level', 'image_path', 'barcode', 'status'];

            foreach ($data as $key => $value) {
                if (in_array($key, $allowedFields)) {
                    $updates[] = "$key = ?";
                    $params[] = $value;
                    $types .= (in_array($key, ['category_id', 'reorder_level']) ? 'i' : (in_array($key, ['purchase_price', 'selling_price']) ? 'd' : 's'));
                }
            }

            if (empty($updates)) {
                return ['success' => false, 'message' => 'No valid fields to update'];
            }

            $params[] = $id;
            $types .= 'i';

            $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?";
            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                Helper::logActivity('UPDATE', 'products', $id, $oldData, $data);
                return ['success' => true, 'message' => 'Product updated successfully'];
            }

            return ['success' => false, 'message' => 'Failed to update product'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete product
     */
    public function delete($id) {
        try {
            $oldData = $this->getById($id);

            $sql = "DELETE FROM products WHERE id = ?";
            $params = [$id];
            $types = 'i';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                Helper::logActivity('DELETE', 'products', $id, $oldData, null);
                return ['success' => true, 'message' => 'Product deleted successfully'];
            }

            return ['success' => false, 'message' => 'Failed to delete product'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update stock
     */
    public function updateStock($id, $quantity) {
        try {
            $sql = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
            $params = [$quantity, $id];
            $types = 'ii';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                Helper::logActivity('UPDATE_STOCK', 'products', $id, null, ['quantity_change' => $quantity]);
                return ['success' => true];
            }

            return ['success' => false, 'message' => 'Failed to update stock'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get low stock products
     */
    public function getLowStock() {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.quantity <= p.reorder_level AND p.status = 'Active'
                ORDER BY p.quantity ASC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Get total product count
     */
    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as total FROM products WHERE status = 'Active'";
        $result = $this->db->fetchOne($sql);
        return $result['total'] ?? 0;
    }

    /**
     * Get total inventory value
     */
    public function getTotalInventoryValue() {
        $sql = "SELECT SUM(quantity * purchase_price) as total FROM products WHERE status = 'Active'";
        $result = $this->db->fetchOne($sql);
        return $result['total'] ?? 0;
    }

    /**
     * Check if SKU exists
     */
    public function skuExists($sku, $excludeId = null) {
        $sql = "SELECT id FROM products WHERE sku = ?";
        $params = [$sku];
        $types = 's';

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
            $types .= 'i';
        }

        $result = $this->db->fetchOne($sql, $params, $types);
        return !empty($result);
    }

    /**
     * Get products by category
     */
    public function getByCategory($categoryId) {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = ? AND p.status = 'Active'
                ORDER BY p.name ASC";
        $params = [$categoryId];
        $types = 'i';

        return $this->db->fetchAll($sql, $params, $types);
    }

    /**
     * Search products
     */
    public function search($query) {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?) AND p.status = 'Active'
                ORDER BY p.name ASC";

        $searchTerm = '%' . $query . '%';
        $params = [$searchTerm, $searchTerm, $searchTerm];
        $types = 'sss';

        return $this->db->fetchAll($sql, $params, $types);
    }
}
?>
