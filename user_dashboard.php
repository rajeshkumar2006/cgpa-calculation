<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    die("Unauthorized. <a href='login_user.php'>Login</a>");
}
require_once 'db.php';

$student_id = $_SESSION['student_id'];

// Fetch student details including batch
$st = $conn->prepare("SELECT roll_no, student_name, dob, department, batch FROM students WHERE id=?");
$st->bind_param("i", $student_id);
$st->execute();
$student = $st->get_result()->fetch_assoc();
$st->close();

// Fetch semester-wise CGPA
$sql = "SELECT semester, cgpa FROM student_cgpa WHERE student_id=? ORDER BY semester";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$cgpaRes = $stmt->get_result();

$semesters = [];
$totalC = 0;
while ($row = $cgpaRes->fetch_assoc()) {
    $totalC += $row['cgpa'];
    $semesters[] = $row;
}
$overallCgpa = count($semesters) > 0 ? round($totalC / count($semesters), 2) : null;
$stmt->close();

// Check for RA/AA (arrear) grades
$checkArrear = $conn->prepare("SELECT COUNT(*) as cnt FROM subjects WHERE student_id=? AND grade='RA/AA'");
$checkArrear->bind_param("i", $student_id);
$checkArrear->execute();
$arrearRes = $checkArrear->get_result()->fetch_assoc();
$hasArrear = ($arrearRes['cnt'] > 0);
$checkArrear->close();

// Classification
$classification = (!$hasArrear && $overallCgpa !== null) ? "First Class with Distinction" : null;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard • CGPA Portal</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<style>
body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg,#2563eb,#1e40af); color:#fff; min-height:100vh; }
.container { max-width: 1000px; }
.card { border-radius: 16px; box-shadow: 0 8px 28px rgba(0,0,0,0.15); border: none; background:#fff; color:#1e3a8a; transition: transform .3s;}
.card:hover { transform: translateY(-3px); }
.cgpa-badge { font-size:18px; padding:8px 14px; border-radius:10px; font-weight:500; }
.high { background:#16a34a; color:#fff; }
.low { background:#dc2626; color:#fff; }
.normal { background:#2563eb; color:#fff; }
h3,h5 { color:#1e3a8a; }
.btn-outline-primary { border-color: #2563eb; color:#2563eb; }
.btn-outline-primary:hover { background:#2563eb; color:#fff; }
.table-striped>tbody>tr:nth-child(odd)>td { background:#f0f8ff; }
</style>
</head>
<body class="py-4">
<div class="container">

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 text-white">
    <h3>🎓 Merit Arts and Science College</h3>
    <div>
        <a class="btn btn-light btn-sm" href="index.php">Home</a>
        <a class="btn btn-danger btn-sm" href="logout.php">Logout</a>
    </div>
</div>

<!-- Profile Card -->
<div class="card p-4 mb-4">
    <h5 class="mb-3"><i class="bi bi-person-circle"></i> My Profile</h5>
    <div class="row g-3">
        <div class="col-md-3"><b>Name:</b> <?= htmlspecialchars($student['student_name']) ?></div>
        <div class="col-md-3"><b>Roll No:</b> <?= htmlspecialchars($student['roll_no']) ?></div>
        <div class="col-md-3"><b>DOB:</b> <?= htmlspecialchars($student['dob']) ?></div>
        <div class="col-md-3"><b>Department:</b> <?= htmlspecialchars($student['department']) ?></div>
        <div class="col-md-3"><b>Batch:</b> <?= htmlspecialchars($student['batch']) ?></div>
    </div>
</div>

<!-- Semester Table -->
<div class="card p-4 mb-4">
    <h5 class="mb-3">📊 Semester-wise CGPA</h5>
    <?php if (!empty($semesters)): ?>
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>Semester</th>
                <th>CGPA</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $maxCgpa = max(array_column($semesters,'cgpa'));
        $minCgpa = min(array_column($semesters,'cgpa'));
        foreach ($semesters as $s): 
            $badgeClass = ($s['cgpa']==$maxCgpa) ? "high" : (($s['cgpa']==$minCgpa) ? "low" : "normal");
        ?>
            <tr>
                <td><?= $s['semester'] ?></td>
                <td><span class="cgpa-badge <?= $badgeClass ?>"><?= number_format($s['cgpa'],2) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p class="text-muted">No semester records found.</p>
    <?php endif; ?>
</div>

<!-- Overall CGPA & Classification -->
<div class="card p-4 mb-4 text-center">
    <h5 class="mb-3">🏆 Overall CGPA</h5>
    <?php if($overallCgpa!==null): ?>
    <span class="cgpa-badge normal"><?= $overallCgpa ?></span>
    <?php endif; ?>
    <?php if($classification): ?>
    <div class="mt-3">
        <h5 class="text-success"><?= $classification ?></h5>
    </div>
    <?php endif; ?>
    <?php if(!empty($semesters)): ?>
    <canvas id="cgpaChart" class="mt-4" height="150"></canvas>
    <?php endif; ?>
    <div class="mt-4">
        <button class="btn btn-outline-primary" id="downloadPDF">
            <i class="bi bi-download"></i> Download Professional Report
        </button>
    </div>
</div>

</div>

<script>
// Chart.js for CGPA Trend
<?php if (!empty($semesters)): ?>
const ctx = document.getElementById('cgpaChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(fn($s)=>"Sem ".$s['semester'],$semesters)) ?>,
        datasets: [{
            label: 'CGPA',
            data: <?= json_encode(array_column($semesters,'cgpa')) ?>,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.2)',
            fill: true,
            tension: 0.3,
            pointRadius: 5,
            pointBackgroundColor: '#2563eb'
        }]
    },
    options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{min:0,max:10}} }
});
<?php endif; ?>

// PDF Generation
document.getElementById('downloadPDF').addEventListener('click', async () => {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p','pt','a4');
    const pageWidth = pdf.internal.pageSize.getWidth();

    // Header
    pdf.setFontSize(18);
    pdf.setTextColor(0,0,128);
    pdf.text("Merit Arts and Science College", pageWidth/2, 40, {align:"center"});
    pdf.setFontSize(12);
    pdf.setTextColor(80,80,80);
    pdf.text("Student CGPA Report", pageWidth/2, 60, {align:"center"});

    // Student info
    pdf.setFontSize(11);
    pdf.setTextColor(0,0,0);
    pdf.text(`Name: <?= addslashes($student['student_name']) ?>`, 40, 90);
    pdf.text(`Roll No: <?= addslashes($student['roll_no']) ?>`, 300, 90);
    pdf.text(`Department: <?= addslashes($student['department']) ?>`, 40, 110);
    pdf.text(`Batch: <?= addslashes($student['batch']) ?>`, 300, 110);
    pdf.text(`Generated on: ${new Date().toLocaleDateString()}`, 40, 130);

    // Chart as image
    <?php if(!empty($semesters)): ?>
    const chartCanvas = document.getElementById('cgpaChart');
    const chartImg = chartCanvas.toDataURL("image/png",1.0);
    pdf.addImage(chartImg,'PNG',40,150,pageWidth-80,180);
    <?php endif; ?>

    // Semester Table
    const columns = ["Semester","CGPA"];
    const rows = <?= json_encode(array_map(fn($s)=>[$s['semester'],number_format($s['cgpa'],2)],$semesters)) ?>;
    pdf.autoTable({
        startY: <?php echo !empty($semesters)? "360":"160"; ?>,
        head: [columns],
        body: rows,
        theme: 'striped',
        headStyles:{fillColor:[37,99,235]},
        alternateRowStyles:{fillColor:[220,235,255]},
        margin: {left:40,right:40}
    });

    // Overall CGPA
    pdf.setFontSize(13);
    pdf.setTextColor(20,20,20);
    pdf.text(`Overall CGPA: <?= $overallCgpa ?>`, 40, pdf.lastAutoTable.finalY + 30);

    // Classification if any
    <?php if($classification): ?>
    pdf.setTextColor(0,128,0);
    pdf.text(`<?= $classification ?>`, 40, pdf.lastAutoTable.finalY + 50);
    <?php endif; ?>

    // Footer
    const pageCount = pdf.internal.getNumberOfPages();
    for(let i=1;i<=pageCount;i++){
        pdf.setPage(i);
        pdf.setFontSize(10);
        pdf.setTextColor(100);
        pdf.text(`Page ${i} of ${pageCount} • Generated by CGPA Portal`, pageWidth/2, pdf.internal.pageSize.getHeight()-20, {align:"center"});
    }

    pdf.save("CGPA_Report_<?= htmlspecialchars($student['roll_no']) ?>.pdf");
});
</script>
</body>
</html>
