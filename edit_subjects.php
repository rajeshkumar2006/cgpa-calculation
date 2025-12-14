<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
  die("Unauthorized. <a href='login_admin.php'>Admin Login</a>");
}
require_once 'db.php';

$student_id = intval($_GET['id'] ?? 0);
if ($student_id <= 0) die("Invalid student ID");

# Fetch student details
$st = $conn->prepare("SELECT student_name, roll_no FROM students WHERE id=?");
$st->bind_param("i", $student_id);
$st->execute();
$student = $st->get_result()->fetch_assoc();
$st->close();

# Fetch subjects semester-wise
$subs = $conn->prepare("SELECT * FROM subjects WHERE student_id=? ORDER BY semester, subject_code");
$subs->bind_param("i", $student_id);
$subs->execute();
$result = $subs->get_result();
$subs->close();

$subjects_by_sem = [];
while ($row = $result->fetch_assoc()) {
    $subjects_by_sem[$row['semester']][] = $row;
}

# Grade points
# Grade points
$gp = [
  "O" => 10,
  "A+" => 9,
  "A" => 8,
  "B+" => 7,
  "B" => 6,
  "C" => 5,
  "RA/AA" => 0
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Subjects • <?= htmlspecialchars($student['student_name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #0d6efd, #1e3a8a);
      min-height: 100vh;
      font-family: "Segoe UI", sans-serif;
    }
    .container {
      max-width: 1100px;
    }
    .card {
      border-radius: 14px;
      background: #fff;
      box-shadow: 0 6px 25px rgba(0,0,0,.15);
      margin-bottom: 1.5rem;
    }
    .card-header {
      background: #0d6efd;
      color: #fff;
      border-radius: 14px 14px 0 0 !important;
    }
    .accordion-button {
      background: #0d6efd !important;
      color: white !important;
      font-weight: 500;
    }
    .accordion-button:not(.collapsed) {
      background: #1e40af !important;
    }
    .table-striped tbody tr:nth-of-type(odd) {
      background-color: rgba(13,110,253,.05);
    }
  </style>
</head>
<body>
<div class="container py-4">

  <div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        Subjects of <?= htmlspecialchars($student['student_name']) ?> (<?= $student['roll_no'] ?>)
      </h4>
      <a href="students.php" class="btn btn-outline-light bg-primary text-white">← Back</a>
    </div>
  </div>

  <div class="accordion" id="semAccordion">
    <?php 
    $totalCredits = 0; 
    $totalWeighted = 0;
    foreach ($subjects_by_sem as $sem => $subs): 
      $semCredits = 0; $semWeighted = 0;
    ?>
    <div class="accordion-item">
      <h2 class="accordion-header" id="heading<?= $sem ?>">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $sem ?>">
          Semester <?= $sem ?>
        </button>
      </h2>
      <div id="collapse<?= $sem ?>" class="accordion-collapse collapse" data-bs-parent="#semAccordion">
        <div class="accordion-body">
          <table class="table table-bordered table-striped align-middle">
            <thead class="table-primary">
              <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Credits</th>
                <th>Grade</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($subs as $s): 
                $c = $s['credits']; 
                $g = $s['grade'];
                $gpv = $gp[$g] ?? 0;
                $semCredits += $c;
                $semWeighted += $c * $gpv;
                $totalCredits += $c;
                $totalWeighted += $c * $gpv;
              ?>
              <tr>
                <td><?= htmlspecialchars($s['subject_code']) ?></td>
                <td><?= htmlspecialchars($s['subject_name']) ?></td>
                <td><?= $c ?></td>
                <td><?= $g ?></td>
                <td>
                  <a href="update_subject.php?id=<?= $s['id'] ?>&student_id=<?= $student_id ?>" class="btn btn-sm btn-warning">Edit</a>
                  <a href="delete_subject.php?id=<?= $s['id'] ?>&student_id=<?= $student_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete subject?')">Delete</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div class="alert alert-info">
            <strong>Semester <?= $sem ?> GPA:</strong> <?= $semCredits>0 ? round($semWeighted/$semCredits,2) : "N/A" ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="card mt-3">
    <div class="card-body text-center">
      <h5 class="mb-0">
        <strong>Overall CGPA:</strong> <?= $totalCredits>0 ? round($totalWeighted/$totalCredits,2) : "N/A" ?>
      </h5>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
