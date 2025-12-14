<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
  die("Unauthorized. <a href='login_admin.php'>Admin Login</a>");
}
require_once 'db.php';

function gp($grade){
  $map = [
    "O"     => 10,
    "A+"    => 9,
    "A"     => 8,
    "B+"    => 7,
    "B"     => 6,
    "C"     => 5,
    "RA/AA" => 0
  ];
  return $map[$grade] ?? 0;
}

// Collect form data
$student_name = trim($_POST['student_name'] ?? '');
$roll_no      = trim($_POST['roll_no'] ?? '');
$dob          = trim($_POST['dob'] ?? '');
$department   = trim($_POST['department'] ?? '');
$batch        = trim($_POST['batch'] ?? '');   // NEW FIELD
$semester     = intval($_POST['semester'] ?? 0);

$codes   = $_POST['subject_code'] ?? [];
$names   = $_POST['subject_name'] ?? [];
$credits = $_POST['credits'] ?? [];
$grades  = $_POST['grade'] ?? [];

// Basic validation
if ($student_name==='' || $roll_no==='' || $dob==='' || $department==='' || $batch==='' || $semester===0) {
  die("Missing student fields. <a href='admin_dashboard.php'>Back</a>");
}
if (!is_array($codes) || count($codes)===0) {
  die("Add at least one subject. <a href='admin_dashboard.php'>Back</a>");
}

$conn->begin_transaction();

try {
  // Check if student exists
  $sql = "SELECT id FROM students WHERE roll_no = ?";
  $st  = $conn->prepare($sql);
  $st->bind_param("s", $roll_no);
  $st->execute();
  $res = $st->get_result();

  if ($row = $res->fetch_assoc()) {
    $student_id = (int)$row['id'];
    $up = $conn->prepare("UPDATE students SET student_name=?, dob=?, department=?, batch=?, current_semester=? WHERE id=?");
    $up->bind_param("ssssii", $student_name, $dob, $department, $batch, $semester, $student_id);
    $up->execute();
    $up->close();
  } else {
    $ins = $conn->prepare("INSERT INTO students (roll_no, student_name, dob, department, batch, current_semester) VALUES (?,?,?,?,?,?)");
    $ins->bind_param("sssssi", $roll_no, $student_name, $dob, $department, $batch, $semester);
    $ins->execute();
    $student_id = $ins->insert_id;
    $ins->close();
  }
  $st->close();

  // Remove old subjects for this semester
  $del = $conn->prepare("DELETE FROM subjects WHERE student_id=? AND semester=?");
  $del->bind_param("ii", $student_id, $semester);
  $del->execute();
  $del->close();

  // Insert subjects and calculate SGPA
  $insSub = $conn->prepare("INSERT INTO subjects (student_id, semester, subject_code, subject_name, credits, grade, grade_point) VALUES (?,?,?,?,?,?,?)");

  $totalC = 0; 
  $totalW = 0;

  for ($i=0; $i<count($codes); $i++) {
    $code = trim($codes[$i] ?? '');
    $name = trim($names[$i] ?? '');
    $cred = floatval($credits[$i] ?? 0);
    $grade= trim($grades[$i] ?? '');
    if ($code==='' || $name==='' || $cred<=0 || $grade==='') continue;

    $gp = gp($grade);
    $insSub->bind_param("iissdsi", $student_id, $semester, $code, $name, $cred, $grade, $gp);
    $insSub->execute();

    $totalC += $cred;
    $totalW += $cred * $gp;
  }
  $insSub->close();

  // Save Semester GPA (SGPA)
  if ($totalC > 0) {
    $sgpa = round($totalW / $totalC, 2);
    $upsert = $conn->prepare("
      INSERT INTO student_cgpa (student_id, semester, cgpa)
      VALUES (?, ?, ?)
      ON DUPLICATE KEY UPDATE cgpa = VALUES(cgpa)
    ");
    $upsert->bind_param("iid", $student_id, $semester, $sgpa);
    $upsert->execute();
    $upsert->close();
  }

  // Calculate Overall CGPA
  $avgQ = $conn->prepare("SELECT AVG(cgpa) AS overall FROM student_cgpa WHERE student_id=?");
  $avgQ->bind_param("i", $student_id);
  $avgQ->execute();
  $avgRes = $avgQ->get_result()->fetch_assoc();
  $avgQ->close();

  $overallCgpa = round($avgRes['overall'], 2);

  $upOverall = $conn->prepare("UPDATE students SET overall_cgpa=? WHERE id=?");
  $upOverall->bind_param("di", $overallCgpa, $student_id);
  $upOverall->execute();
  $upOverall->close();

  $conn->commit();
  header("Location: admin_dashboard.php?ok=1");
} catch (Throwable $e) {
  $conn->rollback();
  die("Error: ".$e->getMessage()." <a href='admin_dashboard.php'>Back</a>");
}

$conn->close();
?>
