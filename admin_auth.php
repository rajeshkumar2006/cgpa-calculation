<?php
session_start();
require_once 'db.php';

$user = trim($_POST['username'] ?? '');
$pass = trim($_POST['password'] ?? '');

if ($user === '' || $pass === '') {
  $_SESSION['admin_error'] = "Enter username and password.";
  header('Location: login_admin.php');
  exit;
}

$sql = "SELECT id, password_hash FROM admins WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
  if (password_verify($pass, $row['password_hash'])) {
    $_SESSION['admin_id'] = $row['id'];
    header('Location: admin_dashboard.php');
  } else {
    $_SESSION['admin_error'] = "Invalid credentials.";
    header('Location: login_admin.php');
  }
} else {
  $_SESSION['admin_error'] = "Invalid credentials.";
  header('Location: login_admin.php');
}
$stmt->close();
$conn->close();
