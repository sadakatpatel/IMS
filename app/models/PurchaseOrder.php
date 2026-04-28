<?php
/**
 * Purchase Order Model Class
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Helper.php';

class PurchaseOrder {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($supplierId, $orderDate, $notes = '') {
        try {
            $orderNumber = Helper::generateOrderNumber('PO');

            $sql = "INSERT INTO purchase_orders (order_number, supplier_id, order_date, notes, status, created_by) 
                    VALUES (?, ?, ?, ?, 'Pending', ?)";

            $params = [$orderNumber, $supplierId, $orderDate, $notes, Helper::getCurrentUserId()];
            $types = 'sissi';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                $orderId = $this->db->lastInsertId();
                Helper::logActivity('CREATE', 'purchase_orders', $orderId, null, ['order_number' => $orderNumber]);
                return ['success' => true, 'id' => $orderId, 'order_number' => $orderNumber];
            }

            return ['success' => false, 'message' => 'Failed to create purchase order'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAll($limit = null, $offset = 0, $filters = []) {
        $sql = "SELECT po.*, s.name as supplier_name, COUNT(poi.id) as item_count 
                FROM purchase_orders po
                LEFT JOIN suppliers s ON po.supplier_id = s.id
                LEFT JOIN purchase_order_items poi ON po.id = poi.purchase_order_id
                WHERE 1=1";

        $params = [];
        $types = '';

        if (isset($filters['status'])) {
            $sql .= " AND po.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (isset($filters['supplier_id'])) {
            $sql .= " AND po.supplier_id = ?";
            $params[] = $filters['supplier_id'];
            $types .= 'i';
        }

        $sql .= " GROUP BY po.id ORDER BY po.order_date DESC";

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
        }

        return $this->db->fetchAll($sql, $params, $types);
    }

    public function getById($id) {
        $sql = "SELECT po.*, s.name as supplier_name, s.phone, s.email 
                FROM purchase_orders po
                LEFT JOIN suppliers s ON po.supplier_id = s.id
                WHERE po.id = ?";
        $params = [$id];
        $types = 'i';

        return $this->db->fetchOne($sql, $params, $types);
    }

    public function addItem($orderId, $productId, $quantity, $unitPrice) {
        try {
            $subtotal = $quantity * $unitPrice;

            $sql = "INSERT INTO purchase_order_items (purchase_order_id, product_id, quantity, unit_price, subtotal) 
                    VALUES (?, ?, ?, ?, ?)";

            $params = [$orderId, $productId, $quantity, $unitPrice, $subtotal];
            $types = 'iiidd';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                // Update total amount
                $this->updateTotalAmount($orderId);
                return ['success' => true, 'id' => $this->db->lastInsertId()];
            }

            return ['success' => false];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getItems($orderId) {
        $sql = "SELECT poi.*, p.name as product_name, p.sku, c.name as category_name
                FROM purchase_order_items poi
                LEFT JOIN products p ON poi.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE poi.purchase_order_id = ?";

        $params = [$orderId];
        $types = 'i';

        return $this->db->fetchAll($sql, $params, $types);
    }

    public function removeItem($itemId) {
        try {
            // Get order ID first
            $sql = "SELECT purchase_order_id FROM purchase_order_items WHERE id = ?";
            $result = $this->db->fetchOne($sql, [$itemId], 'i');

            if (!$result) {
                return ['success' => false, 'message' => 'Item not found'];
            }

            $orderId = $result['purchase_order_id'];

            // Delete item
            $sql = "DELETE FROM purchase_order_items WHERE id = ?";
            $this->db->execute($sql, [$itemId], 'i');

            // Update total
            $this->updateTotalAmount($orderId);

            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateTotalAmount($orderId) {
        $sql = "UPDATE purchase_orders SET total_amount = (
                SELECT SUM(subtotal) FROM purchase_order_items 
                WHERE purchase_order_id = ?
            ) WHERE id = ?";

        $params = [$orderId, $orderId];
        $types = 'ii';

        return $this->db->execute($sql, $params, $types);
    }

    public function completeOrder($orderId, $deliveryDate) {
        try {
            // Get order items
            $items = $this->getItems($orderId);

            // Update product stock
            foreach ($items as $item) {
                $sql = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
                $this->db->execute($sql, [$item['quantity'], $item['product_id']], 'ii');
            }

            // Update order status
            $sql = "UPDATE purchase_orders SET status = 'Completed', actual_delivery_date = ? WHERE id = ?";
            $result = $this->db->execute($sql, [$deliveryDate, $orderId], 'si');

            if ($result) {
                Helper::logActivity('COMPLETE', 'purchase_orders', $orderId, null, ['delivery_date' => $deliveryDate]);
                return ['success' => true, 'message' => 'Order completed and stock updated'];
            }

            return ['success' => false, 'message' => 'Failed to complete order'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelOrder($orderId) {
        try {
            $sql = "UPDATE purchase_orders SET status = 'Cancelled' WHERE id = ?";
            $result = $this->db->execute($sql, [$orderId], 'i');

            if ($result) {
                Helper::logActivity('CANCEL', 'purchase_orders', $orderId, null, null);
                return ['success' => true, 'message' => 'Order cancelled'];
            }

            return ['success' => false];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as total FROM purchase_orders";
        $result = $this->db->fetchOne($sql);
        return $result['total'] ?? 0;
    }

    public function getPendingCount() {
        $sql = "SELECT COUNT(*) as total FROM purchase_orders WHERE status = 'Pending'";
        $result = $this->db->fetchOne($sql);
        return $result['total'] ?? 0;
    }
}
?>
