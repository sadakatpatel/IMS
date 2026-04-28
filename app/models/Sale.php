<?php
/**
 * Sale Model Class
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Helper.php';

class Sale {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($customerData, $saleDate, $amount = 0) {
        try {
            $invoiceNumber = Helper::generateInvoiceNumber('INV');
            $totalAmount = floatval($amount);
            $taxAmount = $totalAmount * 0.1; // 10% tax
            $netAmount = $totalAmount + $taxAmount;

            $sql = "INSERT INTO sales (invoice_number, customer_name, customer_phone, customer_email, 
                    sale_date, total_amount, tax_amount, net_amount, status, payment_status, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Unpaid', ?)";

            $params = [
                $invoiceNumber,
                $customerData['name'] ?? '',
                $customerData['phone'] ?? '',
                $customerData['email'] ?? '',
                $saleDate,
                $totalAmount,
                $taxAmount,
                $netAmount,
                Helper::getCurrentUserId()
            ];

            $types = 'sssssdddi';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                $saleId = $this->db->lastInsertId();
                Helper::logActivity('CREATE', 'sales', $saleId, null, ['invoice_number' => $invoiceNumber, 'amount' => $netAmount]);
                return ['success' => true, 'id' => $saleId, 'invoice_number' => $invoiceNumber];
            }

            return ['success' => false, 'message' => 'Failed to create sale'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAll($limit = null, $offset = 0, $filters = []) {
        $sql = "SELECT * FROM sales WHERE 1=1";

        $params = [];
        $types = '';

        if (isset($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (isset($filters['payment_status'])) {
            $sql .= " AND payment_status = ?";
            $params[] = $filters['payment_status'];
            $types .= 's';
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $sql .= " AND sale_date BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
            $types .= 'ss';
        }

        $sql .= " ORDER BY sale_date DESC";

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
        }

        return $this->db->fetchAll($sql, $params, $types);
    }

    public function getById($id) {
        $sql = "SELECT * FROM sales WHERE id = ?";
        $params = [$id];
        $types = 'i';

        return $this->db->fetchOne($sql, $params, $types);
    }

    public function addItem($saleId, $productId, $quantity, $unitPrice) {
        try {
            // Check product stock
            $product = $this->db->fetchOne(
                "SELECT quantity FROM products WHERE id = ?",
                [$productId],
                'i'
            );

            if (!$product || $product['quantity'] < $quantity) {
                return ['success' => false, 'message' => 'Insufficient stock'];
            }

            $subtotal = $quantity * $unitPrice;

            $sql = "INSERT INTO sales_items (sale_id, product_id, quantity, unit_price, subtotal) 
                    VALUES (?, ?, ?, ?, ?)";

            $params = [$saleId, $productId, $quantity, $unitPrice, $subtotal];
            $types = 'iiidd';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                // Reduce product stock
                $this->db->execute(
                    "UPDATE products SET quantity = quantity - ? WHERE id = ?",
                    [$quantity, $productId],
                    'ii'
                );

                // Update sale totals
                $this->updateTotals($saleId);

                return ['success' => true, 'id' => $this->db->lastInsertId()];
            }

            return ['success' => false];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getItems($saleId) {
        $sql = "SELECT si.*, p.name as product_name, p.sku 
                FROM sales_items si
                LEFT JOIN products p ON si.product_id = p.id
                WHERE si.sale_id = ?";

        $params = [$saleId];
        $types = 'i';

        return $this->db->fetchAll($sql, $params, $types);
    }

    public function updateTotals($saleId) {
        // Calculate totals from items
        $sql = "SELECT SUM(subtotal) as total FROM sales_items WHERE sale_id = ?";
        $result = $this->db->fetchOne($sql, [$saleId], 'i');

        $totalAmount = $result['total'] ?? 0;
        $taxAmount = ($totalAmount * 0.1); // 10% tax
        $netAmount = $totalAmount + $taxAmount;

        $updateSql = "UPDATE sales SET total_amount = ?, tax_amount = ?, net_amount = ? WHERE id = ?";
        $params = [$totalAmount, $taxAmount, $netAmount, $saleId];
        $types = 'dddi';

        return $this->db->execute($updateSql, $params, $types);
    }

    public function deliverSale($saleId, $deliveryDate) {
        try {
            $sql = "UPDATE sales SET status = 'Delivered', delivery_date = ? WHERE id = ?";
            $result = $this->db->execute($sql, [$deliveryDate, $saleId], 'si');

            if ($result) {
                Helper::logActivity('DELIVER', 'sales', $saleId, null, ['delivery_date' => $deliveryDate]);
                return ['success' => true];
            }

            return ['success' => false];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updatePaymentStatus($saleId, $paymentStatus) {
        try {
            $sql = "UPDATE sales SET payment_status = ? WHERE id = ?";
            $result = $this->db->execute($sql, [$paymentStatus, $saleId], 'si');

            if ($result) {
                Helper::logActivity('UPDATE_PAYMENT', 'sales', $saleId, null, ['payment_status' => $paymentStatus]);
                return ['success' => true];
            }

            return ['success' => false];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateAmount($saleId, $amount) {
        try {
            $totalAmount = floatval($amount);
            $taxAmount = $totalAmount * 0.1;
            $netAmount = $totalAmount + $taxAmount;

            $sql = "UPDATE sales SET total_amount = ?, tax_amount = ?, net_amount = ? WHERE id = ?";
            $result = $this->db->execute($sql, [$totalAmount, $taxAmount, $netAmount, $saleId], 'dddi');

            if ($result) {
                Helper::logActivity('UPDATE_AMOUNT', 'sales', $saleId, null, ['amount' => $netAmount]);
                return ['success' => true];
            }
            return ['success' => false];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelSale($saleId) {
        try {
            // Get sale items to restore stock
            $items = $this->getItems($saleId);

            foreach ($items as $item) {
                $this->db->execute(
                    "UPDATE products SET quantity = quantity + ? WHERE id = ?",
                    [$item['quantity'], $item['product_id']],
                    'ii'
                );
            }

            // Update sale status
            $sql = "UPDATE sales SET status = 'Cancelled' WHERE id = ?";
            $result = $this->db->execute($sql, [$saleId], 'i');

            if ($result) {
                Helper::logActivity('CANCEL', 'sales', $saleId, null, null);
                return ['success' => true, 'message' => 'Sale cancelled and stock restored'];
            }

            return ['success' => false];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getTotalSales($startDate = null, $endDate = null) {
        $sql = "SELECT SUM(net_amount) as total FROM sales WHERE status != 'Cancelled'";
        $params = [];
        $types = '';

        if ($startDate && $endDate) {
            $sql .= " AND sale_date BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
            $types = 'ss';
        }

        $result = $this->db->fetchOne($sql, $params, $types);
        return $result['total'] ?? 0;
    }

    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as total FROM sales";
        $result = $this->db->fetchOne($sql);
        return $result['total'] ?? 0;
    }

    public function getRecentSales($limit = 10) {
        $sql = "SELECT * FROM sales WHERE status != 'Cancelled' ORDER BY sale_date DESC LIMIT ?";
        $params = [$limit];
        $types = 'i';

        return $this->db->fetchAll($sql, $params, $types);
    }
}
?>
