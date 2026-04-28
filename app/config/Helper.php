<?php
/**
 * Utility Functions and Helpers
 */

class Helper {
    /**
     * Hash password using bcrypt
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Sanitize input
     */
    public static function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate unique token
     */
    public static function generateToken() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Generate SKU
     */
    public static function generateSKU() {
        return 'SKU-' . date('YmdHis') . '-' . rand(1000, 9999);
    }

    /**
     * Generate Order Number
     */
    public static function generateOrderNumber($prefix = 'PO') {
        return $prefix . '-' . date('YmdHis') . '-' . rand(10000, 99999);
    }

    /**
     * Generate Invoice Number
     */
    public static function generateInvoiceNumber($prefix = 'INV') {
        return $prefix . '-' . date('YmdHis') . '-' . rand(10000, 99999);
    }

    /**
     * Format currency
     */
    public static function formatCurrency($amount, $currency = 'USD') {
        return '$' . number_format($amount ?? 0, 2);
    }

    /**
     * Format date
     */
    public static function formatDate($date, $format = 'd M Y') {
        return date($format, strtotime($date));
    }

    /**
     * Format datetime
     */
    public static function formatDateTime($datetime, $format = 'd M Y H:i') {
        return date($format, strtotime($datetime));
    }

    /**
     * Redirect to URL
     */
    public static function redirect($url) {
        header('Location: ' . $url);
        exit();
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user
     */
    public static function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Get current user ID
     */
    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user role
     */
    public static function getCurrentUserRole() {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return self::getCurrentUserRole() === 'Admin';
    }

    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate phone
     */
    public static function validatePhone($phone) {
        return preg_match('/^[0-9+\-\s\(\)]{7,}$/', $phone);
    }

    /**
     * Log activity
     */
    public static function logActivity($action, $module, $record_id = null, $oldData = null, $newData = null) {
        $db = Database::getInstance();
        $user_id = self::getCurrentUserId();
        $ip_address = $_SERVER['REMOTE_ADDR'];

        $sql = "INSERT INTO activity_logs (user_id, action, module, record_id, old_data, new_data, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $user_id,
            $action,
            $module,
            $record_id,
            $oldData ? json_encode($oldData) : null,
            $newData ? json_encode($newData) : null,
            $ip_address
        ];

        $types = 'ississs';

        return $db->execute($sql, $params, $types);
    }

    /**
     * Upload file
     */
    public static function uploadFile($file) {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'File upload error'];
        }

        // Validate file size
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'File size exceeds limit'];
        }

        // Get file extension
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate extension
        if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
            return ['success' => false, 'message' => 'Invalid file type'];
        }

        // Generate unique filename
        $filename = 'IMG-' . date('YmdHis') . '-' . rand(10000, 99999) . '.' . $file_ext;
        $filepath = UPLOAD_DIR . $filename;

        // Create directory if not exists
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'filename' => $filename, 'path' => $filepath];
        }

        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }

    /**
     * Generate PDF file
     */
    public static function generatePDF($html, $filename = 'document.pdf') {
        // This is a placeholder for PDF generation
        // In production, you would use a library like TCPDF or mPDF
        return [
            'success' => true,
            'message' => 'PDF generation would require a PDF library',
            'filename' => $filename
        ];
    }

    /**
     * Export to CSV
     */
    public static function exportCSV($filename, $headers, $data) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Write headers
        fputcsv($output, $headers);

        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit();
    }

    /**
     * Export to Excel (.xls)
     */
    public static function exportExcel($filename, $headers, $data) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        echo '<table border="1">';
        echo '<tr>';
        foreach ($headers as $header) {
            echo '<th style="background-color: #f2f2f2;">' . htmlspecialchars($header) . '</th>';
        }
        echo '</tr>';

        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
        exit();
    }

    /**
     * Get low stock products
     */
    public static function getLowStockProducts() {
        $db = Database::getInstance();
        $sql = "SELECT * FROM products WHERE quantity <= reorder_level AND status = 'Active'";
        return $db->fetchAll($sql);
    }

    /**
     * Calculate total sales
     */
    public static function calculateTotalSales($start_date = null, $end_date = null) {
        $db = Database::getInstance();

        $sql = "SELECT SUM(net_amount) as total FROM sales WHERE status != 'Cancelled'";
        $params = [];
        $types = '';

        if ($start_date && $end_date) {
            $sql .= " AND sale_date BETWEEN ? AND ?";
            $params = [$start_date, $end_date];
            $types = 'ss';
        }

        $result = $db->fetchOne($sql, $params, $types);
        return $result['total'] ?? 0;
    }

    /**
     * Calculate total purchases
     */
    public static function calculateTotalPurchases($start_date = null, $end_date = null) {
        $db = Database::getInstance();

        $sql = "SELECT SUM(total_amount) as total FROM purchase_orders WHERE status = 'Completed'";
        $params = [];
        $types = '';

        if ($start_date && $end_date) {
            $sql .= " AND order_date BETWEEN ? AND ?";
            $params = [$start_date, $end_date];
            $types = 'ss';
        }

        $result = $db->fetchOne($sql, $params, $types);
        return $result['total'] ?? 0;
    }

    /**
     * Pagination helper
     */
    public static function paginate($totalItems, $itemsPerPage = 10, $currentPage = 1) {
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($currentPage - 1) * $itemsPerPage;

        return [
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'offset' => $offset,
            'has_next' => $currentPage < $totalPages,
            'has_previous' => $currentPage > 1
        ];
    }
}
?>
