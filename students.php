<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
  die("Unauthorized. <a href='login_admin.php'>Admin Login</a>");
}
include 'db.php';

$search = trim($_GET['search'] ?? '');

// Prepare query
if (!empty($search)) {
  $sql = "SELECT id, student_name, roll_no, dob, department, batch 
          FROM students 
          WHERE student_name LIKE ? OR roll_no LIKE ? OR batch LIKE ?
          ORDER BY id DESC";
  $stmt = $conn->prepare($sql);
  $like = "%$search%";
  $stmt->bind_param("sss", $like, $like, $like);
  $stmt->execute();
  $students = $stmt->get_result();
  $stmt->close();
} else {
  $sql = "SELECT id, student_name, roll_no, dob, department, batch FROM students ORDER BY id DESC";
  $students = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Stored Students • Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #2563eb, #1e3a8a);
      color: #fff;
      min-height: 100vh;
    }
    .card {
      border-radius: 16px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.15);
      border: none;
    }
    .table-hover tbody tr:hover {
      background-color: #f0f8ff;
    }
    .cgpa-badge {
      padding: 4px 8px;
      border-radius: 8px;
      font-size: 0.85rem;
    }
    .cgpa-high { background:#16a34a; color:#fff; }
    .cgpa-low { background:#dc2626; color:#fff; }
    .cgpa-normal { background:#2563eb; color:#fff; }
    .btn { border-radius: 10px; }
    h3 { font-weight: 600; }
  </style>
</head>
<body class="py-4">
<div class="container">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4 text-white">
    <h3><i class="bi bi-people-fill"></i> Stored Students</h3>
    <div>
      <a href="admin_dashboard.php" class="btn btn-light btn-sm"><i class="bi bi-plus-circle"></i> Add Student</a>
      <a href="logout.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>

  <!-- Search Card -->
  <div class="card p-3 mb-4">
    <form method="get" class="d-flex">
      <input type="text" name="search" class="form-control me-2" placeholder="🔍 Search by name, reg no, or batch..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
      <?php if (!empty($search)): ?>
        <a href="students.php" class="btn btn-secondary ms-2"><i class="bi bi-x-circle"></i> Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Students Table -->
  <div class="card p-3">
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-primary">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Reg No</th>
            <th>DOB</th>
            <th>Department</th>
            <th>Batch</th>
            <th>Semester-wise CGPA</th>
            <th>Overall CGPA</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($students->num_rows > 0):
            while($stu = $students->fetch_assoc()):
              $student_id = $stu['id'];

              // Fetch semester CGPAs
              $cgpaSql = "SELECT semester, cgpa FROM student_cgpa WHERE student_id=? ORDER BY semester";
              $stmt = $conn->prepare($cgpaSql);
              $stmt->bind_param("i", $student_id);
              $stmt->execute();
              $semCgpas = $stmt->get_result();

              $semList = [];
              $sumCgpa = 0; $count = 0;
              $cgpaValues = [];
              while($c = $semCgpas->fetch_assoc()) {
                $cgpaValues[] = $c['cgpa'];
                $semList[] = "Sem ".$c['semester'].": ".number_format($c['cgpa'],2);
                $sumCgpa += $c['cgpa'];
                $count++;
              }
              $stmt->close();

              $overall = $count > 0 ? number_format($sumCgpa/$count, 2) : '--';

              // Highlight badges
              $max = !empty($cgpaValues) ? max($cgpaValues) : null;
              $min = !empty($cgpaValues) ? min($cgpaValues) : null;
          ?>
          <tr>
            <td><?= $stu['id'] ?></td>
            <td><?= htmlspecialchars($stu['student_name']) ?></td>
            <td><?= htmlspecialchars($stu['roll_no']) ?></td>
            <td><?= htmlspecialchars($stu['dob']) ?></td>
            <td><?= htmlspecialchars($stu['department']) ?></td>
            <td><?= htmlspecialchars($stu['batch']) ?></td>
            <td>
              <?php if (!empty($semList)): ?>
                <?php foreach ($cgpaValues as $i=>$val): 
                  $badgeClass = ($val==$max) ? "cgpa-high" : (($val==$min) ? "cgpa-low" : "cgpa-normal"); ?>
                  <span class="cgpa-badge <?= $badgeClass ?>">Sem <?= $i+1 ?>: <?= number_format($val,2) ?></span><br>
                <?php endforeach; ?>
              <?php else: ?>
                --
              <?php endif; ?>
            </td>
            <td><span class="cgpa-badge cgpa-normal"><?= $overall ?></span></td>
            <td class="text-center">
              <a href="view_student.php?id=<?= $stu['id'] ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
              <a href="edit_student.php?id=<?= $stu['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
              <a href="delete_student.php?id=<?= $stu['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this student?')"><i class="bi bi-trash"></i></a>
              <a href="edit_subjects.php?id=<?= $stu['id'] ?>" class="btn btn-sm btn-secondary"><i class="bi bi-journal-text"></i></a>
            </td>
          </tr>
          <?php endwhile; else: ?>
          <tr><td colspan="9" class="text-center text-muted">No students found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</body>
</html>
