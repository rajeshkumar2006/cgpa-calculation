<?php
include 'db.php';

$id = intval($_GET['id']);

// First delete subjects
$conn->query("DELETE FROM subjects WHERE student_id=$id");

// Then delete student
$conn->query("DELETE FROM students WHERE id=$id");

header("Location: admin_dashboard.php");
exit;
?>