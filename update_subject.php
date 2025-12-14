<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) die("Unauthorized");

require_once 'db.php';

$id = intval($_GET['id'] ?? 0);
$student_id = intval($_GET['student_id'] ?? 0);

# Fetch subject
$st = $conn->prepare("SELECT * FROM subjects WHERE id=?");
$st->bind_param("i", $id);
$st->execute();
$sub = $st->get_result()->fetch_assoc();
$st->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $sem = intval($_POST['semester']);
  $code = trim($_POST['subject_code']);
  $name = trim($_POST['subject_name']);
  $cred = floatval($_POST['credits']);
  $grade = trim($_POST['grade']);

  $up = $conn->prepare("UPDATE subjects 
                        SET semester=?, subject_code=?, subject_name=?, credits=?, grade=? 
                        WHERE id=?");
  $up->bind_param("issdsi", $sem, $code, $name, $cred, $grade, $id);
  $up->execute();
  $up->close();

  header("Location: edit_subjects.php?id=".$student_id);
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Subject</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #0d6efd, #1e3a8a);
      min-height: 100vh;
      font-family: "Segoe UI", sans-serif;
      color: #1e293b;
    }
    .card {
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 6px 25px rgba(0,0,0,.15);
      margin: auto;
      margin-top: 40px;
      max-width: 600px;
    }
    .card-header {
      background: #0d6efd;
      color: #fff;
      border-radius: 14px 14px 0 0 !important;
      font-weight: bold;
    }
    .btn-custom {
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="card-header">
        ✏️ Edit Subject
      </div>
      <div class="card-body">
        <form method="post">
          <!-- Semester -->
          <div class="mb-3">
            <label class="form-label">Semester</label>
            <select name="semester" class="form-select" required>
              <?php for($i=1; $i<=6; $i++): ?>
                <option value="<?= $i ?>" <?= ($sub['semester']==$i?'selected':'') ?>><?= $i ?></option>
              <?php endfor; ?>
            </select>
          </div>

          <!-- Subject Code -->
          <div class="mb-3">
            <label class="form-label">Code</label>
            <input type="text" name="subject_code" class="form-control" 
                   value="<?= htmlspecialchars($sub['subject_code']) ?>" required>
          </div>

          <!-- Subject Name -->
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="subject_name" class="form-control" 
                   value="<?= htmlspecialchars($sub['subject_name']) ?>" required>
          </div>

          <!-- Credits -->
          <div class="mb-3">
            <label class="form-label">Credits</label>
            <input type="number" name="credits" step="0.5" min="1" max="10" class="form-control" 
                   value="<?= htmlspecialchars($sub['credits']) ?>" required>
          </div>

          <!-- Grade -->
          <div class="mb-3">
            <label class="form-label">Grade</label>
            <select name="grade" class="form-select" required>
              <?php 
              $grades = ["O","A+","A","B+","B","C","RA/AA"];
              foreach($grades as $g): ?>
                <option value="<?= $g ?>" <?= ($sub['grade']==$g?'selected':'') ?>><?= $g ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Buttons -->
          <div class="d-flex justify-content-between">
            <a href="edit_subjects.php?id=<?= $student_id ?>" class="btn btn-secondary btn-custom">← Back</a>
            <button type="submit" class="btn btn-primary btn-custom">💾 Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
