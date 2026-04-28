<?php
$conn = new mysqli('127.0.0.1', 'root', 'root', 'inventory_system');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = file_get_contents('C:/xampp/htdocs/inven/database.sql');
if ($conn->multi_query($sql)) {
    echo "Tables created!";
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
} else {
    echo "Error: " . $conn->error;
}
$conn->close();