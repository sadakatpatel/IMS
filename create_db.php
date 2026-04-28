<?php
$conn = new mysqli('127.0.0.1', 'root', 'root');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = $conn->query("CREATE DATABASE IF NOT EXISTS inventory_system");
if ($result) {
    echo "Database created!";
} else {
    echo "Error: " . $conn->error;
}
$conn->close();