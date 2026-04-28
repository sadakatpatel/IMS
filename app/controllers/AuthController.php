<?php
/**
 * Authentication Controller
 */

require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Handle login
     */
    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required'];
        }

        $result = $this->userModel->authenticate($username, $password);

        if ($result['success']) {
            // Set session variables
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['username'] = $result['username'];
            $_SESSION['email'] = $result['email'];
            $_SESSION['user_name'] = $result['full_name'];
            $_SESSION['user_role'] = $result['role'];
            $_SESSION['login_time'] = time();

            return $result;
        }

        return $result;
    }

    /**
     * Handle logout
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            Helper::logActivity('LOGOUT', 'auth', $_SESSION['user_id'], null, null);
        }

        session_destroy();
        return ['success' => true];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Verify admin access
     */
    public function requireAdmin() {
        if (!$this->isLoggedIn()) {
            Helper::redirect(APP_URL . '/public/index.php');
        }

        if ($_SESSION['user_role'] !== 'Admin') {
            die('Access Denied. Admin privileges required.');
        }
    }

    /**
     * Verify login
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            Helper::redirect(APP_URL . '/public/index.php');
        }
    }

    /**
     * Check session timeout
     */
    public function checkSessionTimeout() {
        if ($this->isLoggedIn()) {
            $timeout = SESSION_TIMEOUT * 60; // Convert to seconds
            $elapsed = time() - $_SESSION['login_time'];

            if ($elapsed > $timeout) {
                $this->logout();
                return false;
            }

            $_SESSION['login_time'] = time();
        }

        return true;
    }
}
?>
