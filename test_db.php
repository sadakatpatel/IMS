<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'inventory_system');
echo $conn->connect_error ?: 'Connected';