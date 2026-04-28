<?php
/**
 * Database Connection Class
 * Handles all database operations using MySQLi with prepared statements
 */

require_once __DIR__ . '/../config/config.php';

class Database {
    private $connection;
    private static $instance = null;

    /**
     * Singleton pattern - get database instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - establish database connection
     */
    private function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Check connection
        if ($this->connection->connect_error) {
            die(json_encode([
                'status' => false,
                'message' => 'Database Connection Failed: ' . $this->connection->connect_error
            ]));
        }

        // Set charset
        $this->connection->set_charset('utf8mb4');
    }

    /**
     * Get mysqli connection
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Execute prepared statement
     */
    public function query($sql, $params = [], $types = '') {
        try {
            $stmt = $this->connection->prepare($sql);

            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->connection->error);
            }

            // Bind parameters if provided
            if (!empty($params) && !empty($types)) {
                $stmt->bind_param($types, ...$params);
            }

            // Execute query
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            return $stmt;
        } catch (Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch single row
     */
    public function fetchOne($sql, $params = [], $types = '') {
        $stmt = $this->query($sql, $params, $types);
        if (!$stmt) return null;

        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data;
    }

    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = [], $types = '') {
        $stmt = $this->query($sql, $params, $types);
        if (!$stmt) return [];

        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();

        return $data;
    }

    /**
     * Execute insert/update/delete query
     */
    public function execute($sql, $params = [], $types = '') {
        $stmt = $this->query($sql, $params, $types);
        if (!$stmt) return false;

        $affected = $this->connection->affected_rows;
        $stmt->close();

        return $affected;
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->connection->insert_id;
    }

    /**
     * Get affected rows
     */
    public function affectedRows() {
        return $this->connection->affected_rows;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->begin_transaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollback();
    }

    /**
     * Close connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    /**
     * Escape string
     */
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserializing
     */
    public function __wakeup() {}
}

// Create global database instance
$db = Database::getInstance();
?>
