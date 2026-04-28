<?php
/**
 * Database Configuration File
 * Configure your MySQL database credentials here
 */

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'inventory_system');

// Application Configuration
define('APP_NAME', 'Inventory Management System');
define('APP_VERSION', '1.0.0');

// Dynamically determine the application URL
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$path = str_replace(['/public/index.php', '/index.php'], '', $script_name);
$current_url = $protocol . "://" . $host . $path;

// If we are in a subdirectory like /inven, ensure it's captured
// For this specific project structure, we can also use a fallback
if (strpos($host, 'localhost') !== false && !isset($_SERVER['HTTP_HOST'])) {
    define('APP_URL', 'http://localhost/inven');
} else {
    // If running from CLI or other context where $_SERVER is not fully populated
    if (PHP_SAPI === 'cli') {
        define('APP_URL', 'http://localhost/inven');
    } else {
        // Construct base URL
        $baseUrl = $protocol . "://" . $host . rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/\\');
        define('APP_URL', $baseUrl);
    }
}
define('APP_TIMEZONE', 'UTC');

// Security Configuration
define('SESSION_TIMEOUT', 30); // minutes
define('PASSWORD_HASH_ALGORITHM', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', ['cost' => 10]);

// File Upload Configuration
define('UPLOAD_DIR', dirname(__DIR__) . '/public/images/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Error Reporting (set to false in production)
define('DEBUG_MODE', true);
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
