<?php
$conn = new mysqli('127.0.0.1', 'root', 'root', 'inventory_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$password = password_hash('password', PASSWORD_BCRYPT);
$conn->query("INSERT IGNORE INTO users (username, email, password, full_name, role, status) 
VALUES ('admin', 'admin@inven.com', '$password', 'Administrator', 'Admin', 'Active')");

$conn->query("INSERT IGNORE INTO categories (name, description, status) VALUES 
('Electronics', 'Electronic items', 'Active'),
('Office Supplies', 'Office supplies', 'Active'),
('Furniture', 'Furniture items', 'Active'),
('Software', 'Software licenses', 'Active'),
('Other', 'Other items', 'Active')");

echo "Admin user & categories added!";
$conn->close();