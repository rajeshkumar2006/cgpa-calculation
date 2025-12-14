<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) die("Unauthorized");

require_once 'db.php';

$id = intval($_GET['id'] ?? 0);
$student_id = intval($_GET['student_id'] ?? 0);

$del = $conn->prepare("DELETE FROM subjects WHERE id=?");
$del->bind_param("i", $id);
$del->execute();
$del->close();

header("Location: edit_subjects.php?id=".$student_id);
exit;
