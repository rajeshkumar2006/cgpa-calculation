<?php
include 'db.php';
$id = intval($_GET['id']);

// Fetch student
$student = $conn->query("SELECT * FROM students WHERE id=$id")->fetch_assoc();

// Fetch subjects (ordered by semester)
$subjects = $conn->query("SELECT * FROM subjects WHERE student_id=$id ORDER BY semester, subject_code");

// Grade points
$gp = [
  "O" => 10,
  "A+" => 9,
  "A" => 8,
  "B+" => 7,
  "B" => 6,
  "C" => 5,
  "RA/AA" => 0
];

$current_sem = null;
$totalOverallCredits = 0; 
$totalOverallWeighted = 0;
$semCgpaData = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Student</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
  <style>
    body {
      background: linear-gradient(135deg,#2563eb,#1e3a8a);
      font-family: 'Segoe UI', sans-serif;
      color: #fff;
      min-height: 100vh;
    }
    .card {
      border-radius: 16px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.2);
      border: none;
    }
    .table {
      background: #fff;
      color: #1e3a8a;
      border-radius: 12px;
      overflow: hidden;
    }
    .table thead {
      background: #2563eb;
      color: #fff;
    }
    .badge-cgpa {
      font-size: 1rem;
      padding: 6px 12px;
      border-radius: 8px;
    }
    .cgpa-good { background: #16a34a; }
    .cgpa-mid { background: #f59e0b; }
    .cgpa-bad { background: #dc2626; }
    h3,h5 { color:#1e3a8a; }
  </style>
</head>
<body class="py-4">
<div class="container">

  <!-- Profile Card -->
  <div class="card p-4 mb-4">
    <h3 class="mb-3">🎓 Student Profile</h3>
    <div class="row g-3">
      <div class="col-md-4"><strong>Name:</strong> <?= htmlspecialchars($student['student_name']) ?></div>
      <div class="col-md-4"><strong>Roll No:</strong> <?= htmlspecialchars($student['roll_no']) ?></div>
      <div class="col-md-4"><strong>DOB:</strong> <?= htmlspecialchars($student['dob']) ?></div>
      <div class="col-md-4"><strong>Department:</strong> <?= htmlspecialchars($student['department']) ?></div>
      <div class="col-md-4"><strong>Batch:</strong> <?= htmlspecialchars($student['batch']) ?></div> <!-- ✅ New Batch field -->
    </div>
  </div>

  <!-- Subjects & Semester CGPA -->
  <div class="card p-4 mb-4">
    <h5 class="mb-3">📘 Subjects & CGPA</h5>
    <?php 
    if ($subjects->num_rows > 0):
      while($sub = $subjects->fetch_assoc()):
        if ($current_sem !== $sub['semester']):
          if ($current_sem !== null): ?>
              </tbody></table>
              <div class="mb-3">
                <?php 
                  $semCgpa = $semCredits>0 ? round($semWeighted/$semCredits,2) : "N/A"; 
                  $semCgpaData[] = ["sem"=>$current_sem,"cgpa"=>$semCgpa];
                  $badgeClass = $semCgpa >=8 ? "cgpa-good" : ($semCgpa>=6 ? "cgpa-mid" : "cgpa-bad");
                ?>
                <span class="badge badge-cgpa <?= $badgeClass ?>">Semester <?= $current_sem ?> CGPA: <?= $semCgpa ?></span>
              </div>
          <?php endif;

          $current_sem = $sub['semester'];
          $semCredits = 0;
          $semWeighted = 0;
          ?>
          <h6 class="mt-3">Semester <?= $current_sem ?></h6>
          <table class="table table-bordered mb-3">
            <thead>
              <tr>
                <th>Code</th><th>Name</th><th>Credits</th><th>Grade</th>
              </tr>
            </thead>
            <tbody>
        <?php endif;

        $c = $sub['credits']; 
        $g = $sub['grade'];
        $gpv = isset($gp[$g]) ? $gp[$g] : 0;

        $semCredits += $c;
        $semWeighted += $c * $gpv;

        $totalOverallCredits += $c;
        $totalOverallWeighted += $c * $gpv;
        ?>
          <tr>
            <td><?= htmlspecialchars($sub['subject_code']) ?></td>
            <td><?= htmlspecialchars($sub['subject_name']) ?></td>
            <td><?= $c ?></td>
            <td><?= $g ?></td>
          </tr>
      <?php endwhile; ?>
          </tbody></table>
          <div class="mb-3">
            <?php 
              $semCgpa = $semCredits>0 ? round($semWeighted/$semCredits,2) : "N/A"; 
              $semCgpaData[] = ["sem"=>$current_sem,"cgpa"=>$semCgpa];
              $badgeClass = $semCgpa >=8 ? "cgpa-good" : ($semCgpa>=6 ? "cgpa-mid" : "cgpa-bad");
            ?>
            <span class="badge badge-cgpa <?= $badgeClass ?>">Semester <?= $current_sem ?> CGPA: <?= $semCgpa ?></span>
          </div>
    <?php endif; ?>

    <div class="alert alert-success mt-3">
      <?php 
        $overall = $totalOverallCredits>0 ? round($totalOverallWeighted/$totalOverallCredits,2) : "N/A"; 
        $overallBadge = $overall >=8 ? "cgpa-good" : ($overall>=6 ? "cgpa-mid" : "cgpa-bad");
      ?>
      <strong>Overall CGPA:</strong> 
      <span class="badge badge-cgpa <?= $overallBadge ?>"><?= $overall ?></span>
    </div>
  </div>

  <!-- Chart -->
  <div class="card p-4 mb-4">
    <h5 class="mb-3">📊 Semester CGPA Trend</h5>
    <canvas id="cgpaChart" height="120"></canvas>
  </div>

  <!-- Actions -->
  <div class="d-flex justify-content-between">
    <a href="students.php" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
    <button class="btn btn-primary" id="downloadPDF"><i class="bi bi-download"></i> Download Transcript (PDF)</button>
  </div>
</div>

<script>
// Chart.js
const semData = <?= json_encode($semCgpaData) ?>;
if(semData.length>0){
  new Chart(document.getElementById('cgpaChart'),{
    type:'bar',
    data:{
      labels: semData.map(s=>"Sem "+s.sem),
      datasets:[{
        label:'CGPA',
        data: semData.map(s=>s.cgpa),
        backgroundColor:'#2563eb'
      }]
    },
    options:{scales:{y:{min:0,max:10}}}
  });
}

// PDF Download
document.getElementById('downloadPDF').addEventListener('click', ()=>{
  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF('p','pt','a4');
  const pageWidth = pdf.internal.pageSize.getWidth();

  pdf.setFontSize(18);
  pdf.setTextColor(0,0,128);
  pdf.text("Merit Arts and Science College", pageWidth/2,40,{align:"center"});
  pdf.setFontSize(14);
  pdf.setTextColor(0,0,0);
  pdf.text("Transcript Report", pageWidth/2,60,{align:"center"});

  pdf.setFontSize(12);
  pdf.text("Name: <?= addslashes($student['student_name']) ?>",40,90);
  pdf.text("Roll No: <?= addslashes($student['roll_no']) ?>",300,90);
  pdf.text("Dept: <?= addslashes($student['department']) ?>",40,110);
  pdf.text("DOB: <?= addslashes($student['dob']) ?>",300,110);
  pdf.text("Batch: <?= addslashes($student['batch']) ?>",40,130); // ✅ Added Batch in PDF
  pdf.text("Generated on: "+new Date().toLocaleDateString(),40,150);

  // Semester CGPA Table
  pdf.autoTable({
    startY:170,
    head:[["Semester","CGPA"]],
    body: semData.map(s=>[s.sem,s.cgpa]),
    theme:'striped',
    headStyles:{fillColor:[37,99,235]},
    alternateRowStyles:{fillColor:[220,235,255]}
  });

  // Overall CGPA
  pdf.setFontSize(14);
  pdf.setTextColor(0,128,0);
  pdf.text("Overall CGPA: <?= $overall ?>",40,pdf.lastAutoTable.finalY+40);

  pdf.save("Transcript_<?= $student['roll_no'] ?>.pdf");
});
</script>
</body>
</html>
