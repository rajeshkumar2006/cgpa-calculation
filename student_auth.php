<?php
session_start();
require_once 'db.php';

$regno = trim($_POST['regno'] ?? '');
$dob   = trim($_POST['dob'] ?? '');

if ($regno === '' || $dob === '') {
  $_SESSION['login_error'] = "Please enter Register No and DOB.";
  header('Location: login_user.php');
  exit;
}

$sql = "SELECT id FROM students WHERE roll_no = ? AND dob = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $regno, $dob);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
  $_SESSION['student_id'] = $row['id'];
  header('Location: user_dashboard.php');
} else {
  $_SESSION['login_error'] = "Invalid credentials.";
  header('Location: login_user.php');
}
$stmt->close();
$conn->close();
