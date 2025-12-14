<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
  die("Unauthorized. <a href='login_admin.php'>Admin Login</a>");
}
include 'db.php';

// Get student ID
if (!isset($_GET['id'])) {
  die("No student selected. <a href='students.php'>Back</a>");
}
$student_id = intval($_GET['id']);

// Fetch student details
$student = $conn->query("SELECT * FROM students WHERE id=$student_id")->fetch_assoc();
if (!$student) {
  die("Student not found. <a href='students.php'>Back</a>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Student • <?= htmlspecialchars($student['student_name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #0d6efd, #1e3a8a);
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }
    .navbar {
      background: rgba(255,255,255,0.15);
      backdrop-filter: blur(12px);
      border-radius: 12px;
      padding: 12px 20px;
      margin-bottom: 25px;
    }
    .card {
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 0 10px 35px rgba(0,0,0,0.2);
      padding: 25px;
    }
    .sticky-footer {
      position: sticky;
      bottom: 0;
      background: rgba(255,255,255,0.95);
      padding: 15px;
      border-top: 1px solid #ddd;
      border-radius: 0 0 12px 12px;
      display: flex;
      justify-content: space-between;
    }
  </style>
</head>
<body>
<div class="container py-3">

  <!-- Top Nav -->
  <div class="navbar d-flex justify-content-between align-items-center text-white">
    <div><strong>Edit Student</strong></div>
    <div>
      <a href="students.php" class="btn btn-light btn-sm">← Back</a>
      <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
      <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </div>

  <!-- Edit Form -->
  <div class="card">
    <h4 class="mb-3">Student: <?= htmlspecialchars($student['student_name']) ?> (<?= htmlspecialchars($student['roll_no']) ?>)</h4>

    <form method="post" action="update_student.php">
      <input type="hidden" name="id" value="<?= $student['id'] ?>">

      <!-- Student Info -->
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Student Name</label>
          <input type="text" class="form-control" name="student_name" value="<?= htmlspecialchars($student['student_name']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Register Number</label>
          <input type="text" class="form-control" name="roll_no" value="<?= htmlspecialchars($student['roll_no']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Date of Birth</label>
          <input type="date" class="form-control" name="dob" value="<?= $student['dob'] ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Department</label>
          <input type="text" class="form-control" name="department" value="<?= htmlspecialchars($student['department']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Batch (Academic Year)</label>
          <input type="text" class="form-control" name="batch" placeholder="e.g., 2023-2026" value="<?= htmlspecialchars($student['batch']) ?>" required>
        </div>
      </div>

      <!-- Sticky Save Bar -->
      <div class="sticky-footer mt-4">
        <button type="submit" class="btn btn-success">💾 Save Changes</button>
        <a href="students.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
