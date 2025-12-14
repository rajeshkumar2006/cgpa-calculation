<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
  die("Unauthorized. <a href='login_admin.php'>Admin Login</a>");
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = $_POST['id'];
    $name  = $_POST['student_name'];
    $roll  = $_POST['roll_no'];
    $dob   = $_POST['dob'];
    $dep   = $_POST['department'];
    $batch = $_POST['batch']; // ✅ Batch field

    // Update query (removed current_semester)
    $sql = "UPDATE students 
            SET student_name=?, roll_no=?, dob=?, department=?, batch=? 
            WHERE id=?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $name, $roll, $dob, $dep, $batch, $id);

    if ($stmt->execute()) {
        header("Location: students.php?msg=Student updated successfully");
        exit;
    } else {
        echo "Error updating student: " . $conn->error;
    }
}
?>
