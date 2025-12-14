<?php
$conn = new mysqli("localhost", "root", "", "cgpa_db");
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Create admins table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
)");

// Insert default admin (only if table is empty)
$result = $conn->query("SELECT COUNT(*) as cnt FROM admins");
$row = $result->fetch_assoc();

if ($row['cnt'] == 0) {
    $username = "admin";
    $password = md5("admin123"); // password = admin123
    $conn->query("INSERT INTO admins (username, password) VALUES ('$username', '$password')");
}
?>  