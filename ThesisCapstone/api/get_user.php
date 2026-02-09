<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // allow Android app to access

include "../config/db.php"; // connect to MySQL

// Query your users table
$sql = "SELECT id, name, email FROM users"; 
$result = $conn->query($sql);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data); // send JSON to Android
?>
