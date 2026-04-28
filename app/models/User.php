<?php
/**
 * User Model Class
 * Handles all user-related database operations
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Helper.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create new user
     */
    public function create($username, $email, $password, $full_name, $role = 'Staff') {
        try {
            $hashedPassword = Helper::hashPassword($password);

            $sql = "INSERT INTO users (username, email, password, full_name, role, status) 
                    VALUES (?, ?, ?, ?, ?, 'Active')";

            $params = [$username, $email, $hashedPassword, $full_name, $role];
            $types = 'sssss';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                Helper::logActivity('CREATE', 'users', $this->db->lastInsertId(), null, 
                    ['username' => $username, 'email' => $email, 'role' => $role]);
                return ['success' => true, 'id' => $this->db->lastInsertId()];
            }

            return ['success' => false, 'message' => 'Failed to create user'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        $sql = "SELECT id, username, email, full_name, role, status, created_at FROM users WHERE id = ?";
        $params = [$id];
        $types = 'i';

        return $this->db->fetchOne($sql, $params, $types);
    }

    /**
     * Get user by username
     */
    public function getByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $params = [$username];
        $types = 's';

        return $this->db->fetchOne($sql, $params, $types);
    }

    /**
     * Get user by email
     */
    public function getByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $params = [$email];
        $types = 's';

        return $this->db->fetchOne($sql, $params, $types);
    }

    /**
     * Get all users
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT id, username, email, full_name, role, status, created_at FROM users ORDER BY created_at DESC";

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params = [$limit, $offset];
            $types = 'ii';
            return $this->db->fetchAll($sql, $params, $types);
        }

        return $this->db->fetchAll($sql);
    }

    /**
     * Update user
     */
    public function update($id, $data) {
        try {
            $oldData = $this->getById($id);
            $updates = [];
            $params = [];
            $types = '';

            foreach ($data as $key => $value) {
                if (in_array($key, ['username', 'email', 'full_name', 'role', 'status'])) {
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

            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                Helper::logActivity('UPDATE', 'users', $id, $oldData, $data);
                return ['success' => true, 'message' => 'User updated successfully'];
            }

            return ['success' => false, 'message' => 'Failed to update user'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete user
     */
    public function delete($id) {
        try {
            $oldData = $this->getById($id);

            $sql = "DELETE FROM users WHERE id = ?";
            $params = [$id];
            $types = 'i';

            $result = $this->db->execute($sql, $params, $types);

            if ($result) {
                Helper::logActivity('DELETE', 'users', $id, $oldData, null);
                return ['success' => true, 'message' => 'User deleted successfully'];
            }

            return ['success' => false, 'message' => 'Failed to delete user'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $user = $this->getByUsername($username);

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        if ($user['status'] === 'Inactive') {
            return ['success' => false, 'message' => 'User account is inactive'];
        }

        if (!Helper::verifyPassword($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        Helper::logActivity('LOGIN', 'users', $user['id'], null, ['username' => $username]);

        return [
            'success' => true,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ];
    }

    /**
     * Change password
     */
    public function changePassword($id, $oldPassword, $newPassword) {
        try {
            $user = $this->getById($id);

            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Get full user data including password hash
            $sql = "SELECT password FROM users WHERE id = ?";
            $params = [$id];
            $types = 'i';
            $userData = $this->db->fetchOne($sql, $params, $types);

            if (!Helper::verifyPassword($oldPassword, $userData['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }

            $hashedPassword = Helper::hashPassword($newPassword);
            $result = $this->update($id, ['password' => $hashedPassword]);

            if ($result['success']) {
                Helper::logActivity('CHANGE_PASSWORD', 'users', $id, null, ['username' => $user['username']]);
            }

            return $result;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get total user count
     */
    public function getTotalCount() {
        $sql = "SELECT COUNT(*) as total FROM users";
        $result = $this->db->fetchOne($sql);
        return $result['total'] ?? 0;
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT id FROM users WHERE username = ?";
        $params = [$username];
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
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $params = [$email];
        $types = 's';

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
            $types .= 'i';
        }

        $result = $this->db->fetchOne($sql, $params, $types);
        return !empty($result);
    }
}
?>
