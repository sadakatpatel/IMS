<?php
// Redirect to login page
session_start();
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/Helper.php';

if (isset($_SESSION['user_id'])) {
    Helper::redirect(APP_URL . '/public/dashboard.php');
} else {
    Helper::redirect(APP_URL . '/public/index.php');
}
?>
