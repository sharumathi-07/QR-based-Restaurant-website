<?php
include('config.php');

$username = 'manager';
$password = password_hash('manager@123', PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO managers (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $password);

if ($stmt->execute()) {
    echo "✅ Manager created successfully!";
} else {
    echo "❌ Error: " . $stmt->error;
}
