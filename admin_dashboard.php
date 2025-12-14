<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
  die("Unauthorized. <a href='login_admin.php'>Admin Login</a>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard • Add Student & Subjects</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      margin: 0;
      background: linear-gradient(135deg, #3b82f6, #2563eb, #1e40af);
      background-attachment: fixed;
      color: #1e293b;
      font-family: "Segoe UI", sans-serif;
    }
    .container {max-width: 1100px;}
    .card {
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,.15);
      background: #ffffff;
    }
    .subject-row {
      border: 1px solid #e0e7ff;
      border-radius: 10px;
      padding: 12px;
      margin-bottom: 10px;
      background: #f8fafc;
      transition: all 0.2s;
    }
    .subject-row:hover {
      border-color: #3b82f6;
      background: #f1f5ff;
    }
    .cgpa-box {
      border-radius: 12px;
      padding: 12px 16px;
      font-weight: 700;
      background: #e0f2fe;
      color: #0c4a6e;
    }
    h3, h5 {color: #1e3a8a;}
    .btn-primary {background-color:#2563eb; border:none;}
    .btn-primary:hover {background-color:#1e40af;}
    .fadeIn {animation: fadeIn 0.4s ease-in-out;}
    @keyframes fadeIn {
      from {opacity:0; transform: translateY(10px);}
      to {opacity:1; transform: translateY(0);}
    }
  </style>
</head>
<body class="py-4">
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-white">⚙️ Admin • Add Student & Subjects</h3>
    <div class="d-flex gap-2">
      <a class="btn btn-light" href="students.php"><i class="bi bi-people"></i> View Students</a>
      <a class="btn btn-light" href="index.php"><i class="bi bi-house"></i> Home</a>
      <a class="btn btn-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>

  <!-- ADD STUDENT FORM -->
  <div class="card p-4 fadeIn">
    <form id="form" action="save_student_and_subjects.php" method="post">
      <h5 class="mb-3">📝 Student Details</h5>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Student Name</label>
          <input type="text" class="form-control" name="student_name" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Register Number</label>
          <input type="text" class="form-control" name="roll_no" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Date of Birth</label>
          <input type="date" class="form-control" name="dob" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Department</label>
          <input type="text" class="form-control" name="department" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Batch (Academic Year)</label>
          <input type="text" name="batch" class="form-control" placeholder="e.g., 2023-2026" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Semester</label>
          <select class="form-select" name="semester" required>
            <option value="">Select…</option>
            <option>1</option><option>2</option><option>3</option>
            <option>4</option><option>5</option><option>6</option>
          </select>
        </div>
      </div>

      <hr class="my-4">

      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5>📚 Subjects (for selected semester)</h5>
        <button type="button" class="btn btn-sm btn-primary" id="addRow"><i class="bi bi-plus-circle"></i> Add Subject</button>
      </div>
      <div id="subjects"></div>

      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="cgpa-box">Live CGPA:
          <span id="cgpaLive" class="badge bg-primary fs-6">--</span>
        </div>
        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Save Student & Semester</button>
      </div>
    </form>
  </div>
</div>

<!-- Subject Template -->
<template id="rowTpl">
  <div class="subject-row row g-2 align-items-end fadeIn">
    <div class="col-md-2">
      <label class="form-label">Code</label>
      <input type="text" class="form-control" name="subject_code[]" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">Subject Name</label>
      <input type="text" class="form-control" name="subject_name[]" required>
    </div>
    <div class="col-md-2">
      <label class="form-label">Credits</label>
      <input type="number" step="0.5" min="0" class="form-control" name="credits[]" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Grade</label>
      <select class="form-select" name="grade[]" required>
        <option value="">Select…</option>
        <option>O</option>
        <option>A+</option>
        <option>A</option>
        <option>B+</option>
        <option>B</option>
        <option>C</option>
        <option>RA/AA</option>
      </select>
    </div>
    <div class="col-md-1 d-grid">
      <button type="button" class="btn btn-outline-danger remove"><i class="bi bi-trash"></i></button>
    </div>
  </div>
</template>

<script>
const GRADE_POINTS = {
  "O": 10,
  "A+": 9,
  "A": 8,
  "B+": 7,
  "B": 6,
  "C": 5,
  "RA/AA": 0
};

const subjects = document.getElementById('subjects');
const tpl = document.getElementById('rowTpl');
const addBtn = document.getElementById('addRow');
const cgpaLive = document.getElementById('cgpaLive');

function addRow(){
  const node = tpl.content.cloneNode(true);
  const row = node.querySelector('.subject-row');
  row.querySelector('.remove').addEventListener('click', ()=>{ row.remove(); compute(); });
  row.querySelectorAll('input,select').forEach(el=>{
    el.addEventListener('input', compute);
    el.addEventListener('change', compute);
  });
  subjects.appendChild(node);
  compute();
}

function compute(){
  let totalC=0, totalW=0;
  subjects.querySelectorAll('.subject-row').forEach(row=>{
    const credits = parseFloat(row.querySelector('input[name="credits[]"]').value);
    const grade   = row.querySelector('select[name="grade[]"]').value;
    if(!isNaN(credits) && credits>0 && GRADE_POINTS.hasOwnProperty(grade)){
      totalC += credits;
      totalW += credits * GRADE_POINTS[grade];
    }
  });
  cgpaLive.textContent = totalC>0 ? (totalW/totalC).toFixed(2) : '--';
}

// start with one row
addBtn.addEventListener('click', addRow);
addRow();
</script>
</body>
</html>  