<?php
session_start();
include "db.php";

/* ADMIN CHECK */
if(!isset($_SESSION['user_id']) || strtolower($_SESSION['role'])!="admin"){
    header("Location: login.php");
    exit();
}

/* FILTER */
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year  = isset($_GET['year'])  ? $_GET['year']  : date('Y');

/* MONTHLY SUMMARY */
$stmt = $conn->prepare("
SELECT 
COUNT(*) as total,
SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
SUM(CASE WHEN status='in progress' THEN 1 ELSE 0 END) as progress,
SUM(CASE WHEN status='resolved' THEN 1 ELSE 0 END) as resolved
FROM complaints
WHERE MONTH(comp_date)=? AND YEAR(comp_date)=?
");
$stmt->bind_param("ii",$month,$year);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

/* DAILY ANALYTICS */
$daily_q = $conn->prepare("
SELECT DAY(comp_date) as day, COUNT(*) as count
FROM complaints
WHERE MONTH(comp_date)=? AND YEAR(comp_date)=?
GROUP BY DAY(comp_date)
");
$daily_q->bind_param("ii",$month,$year);
$daily_q->execute();
$daily_res = $daily_q->get_result();

$days = [];
$counts = [];
while($row = $daily_res->fetch_assoc()){
    $days[] = $row['day'];
    $counts[] = $row['count'];
}

/* YEARLY ANALYTICS */
$year_q = $conn->prepare("
SELECT MONTH(comp_date) as month, COUNT(*) as count
FROM complaints
WHERE YEAR(comp_date)=?
GROUP BY MONTH(comp_date)
");
$year_q->bind_param("i",$year);
$year_q->execute();
$year_res = $year_q->get_result();

$months = [];
$year_counts = [];
while($row = $year_res->fetch_assoc()){
    $months[] = $row['month'];
    $year_counts[] = $row['count'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Report</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{font-family:Poppins;background:#f1f5f9;padding:30px;}
.container{display:flex;gap:20px;flex-wrap:wrap;}
.box{
background:white;padding:20px;border-radius:15px;
box-shadow:0 10px 30px rgba(0,0,0,.08);
width:250px;text-align:center;
}
form{margin-bottom:20px;text-align:center;}
canvas{background:white;padding:20px;border-radius:15px;margin-top:20px;}
button{padding:8px 15px;border:none;background:#2563eb;color:white;border-radius:8px;}
</style>
</head>

<body>

<h2 align="center">📊 Complaint Dashboard</h2>

<!-- FILTER -->
<form method="GET">
<select name="month">
<?php for($m=1;$m<=12;$m++): ?>
<option value="<?= $m ?>" <?= ($m==$month)?'selected':'' ?>>
<?= date("F", mktime(0,0,0,$m,1)) ?>
</option>
<?php endfor; ?>
</select>

<select name="year">
<?php for($y=2020;$y<=date('Y');$y++): ?>
<option value="<?= $y ?>" <?= ($y==$year)?'selected':'' ?>>
<?= $y ?>
</option>
<?php endfor; ?>
</select>

<button type="submit">Generate</button>
<a href="download_pdf.php?month=<?= $month ?>&year=<?= $year ?>">
<button type="button">📥 Download PDF</button>
</a>
</form>

<!-- SUMMARY -->
<div class="container">
<div class="box">
<h3>Total</h3>
<h1><?= $data['total'] ?></h1>
</div>

<div class="box">
<h3>Pending</h3>
<h1 style="color:red;"><?= $data['pending'] ?></h1>
</div>

<div class="box">
<h3>In Progress</h3>
<h1 style="color:orange;"><?= $data['progress'] ?></h1>
</div>

<div class="box">
<h3>Resolved</h3>
<h1 style="color:green;"><?= $data['resolved'] ?></h1>
</div>
</div>

<!-- DAILY CHART -->
<canvas id="dailyChart"></canvas>

<!-- YEARLY CHART -->
<canvas id="yearChart"></canvas>

<script>
// DAILY
new Chart(document.getElementById("dailyChart"), {
type: "line",
data: {
labels: <?= json_encode($days) ?>,
datasets: [{
label: "Daily Complaints",
data: <?= json_encode($counts) ?>,
borderWidth: 2
}]
}
});

// YEARLY
new Chart(document.getElementById("yearChart"), {
type: "bar",
data: {
labels: <?= json_encode($months) ?>,
datasets: [{
label: "Monthly Complaints (Year)",
data: <?= json_encode($year_counts) ?>,
borderWidth: 2
}]
}
});
</script>

</body>
</html>
