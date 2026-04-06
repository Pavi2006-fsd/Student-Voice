<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) != "subadmin"){
    header("Location: login.php");
    exit();
}

$subadmin_id = (int)$_SESSION['user_id'];

/* START WORK */
if(isset($_POST['start'])){
    $cid = (int)$_POST['cid'];

    mysqli_query($conn,"
        UPDATE complaints 
        SET status='in progress'
        WHERE id='$cid' 
        AND assigned_to='$subadmin_id'
    ");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/* MARK RESOLVED */
if(isset($_POST['resolve'])){
    $cid = (int)$_POST['cid'];

    mysqli_query($conn,"
        UPDATE complaints 
        SET status='resolved'
        WHERE id='$cid' 
        AND assigned_to='$subadmin_id'
    ");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Sub Admin Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body{margin:0;font-family:Segoe UI;background:#f8fafc;}
.top{background:#0f766e;color:white;padding:18px 30px;display:flex;justify-content:space-between;align-items:center;}
.top a{background:white;color:#0f766e;padding:8px 15px;border-radius:8px;text-decoration:none;font-weight:600;}
.container{width:95%;max-width:1100px;margin:30px auto;}
.card{background:white;padding:25px;border-radius:15px;box-shadow:0 10px 20px rgba(0,0,0,.1);}
table{width:100%;border-collapse:collapse;margin-top:20px;}
th,td{padding:12px;text-align:left;border-bottom:1px solid #e5e7eb;}
th{background:#ccfbf1;}

.status-pending{ color:#dc2626; font-weight:600; }
.status-progress{ color:#ca8a04; font-weight:600; }
.status-resolved{ color:#16a34a; font-weight:600; }

button{padding:6px 12px;border-radius:6px;border:none;color:white;cursor:pointer;}
.start{background:#f59e0b;}
.resolve{background:#16a34a;}
button:hover{opacity:0.9;}
</style>
</head>

<body>

<div class="top">
    <h2>Sub Admin Dashboard</h2>
    <a href="logout.php">Logout</a>
</div>

<div class="container">
<div class="card">

<h3>Assigned Complaints</h3>

<table>
<tr>
    <th>ID</th>
    <th>Category</th>
    <th>Description</th>
    <th>Date</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php
$q = mysqli_query($conn,"
SELECT * FROM complaints 
WHERE assigned_to='$subadmin_id'
ORDER BY id DESC
");

if(mysqli_num_rows($q)>0){
while($row=mysqli_fetch_assoc($q)){

$status = strtolower($row['status']);
$cls = "status-pending";
if($status=="in progress") $cls="status-progress";
if($status=="resolved") $cls="status-resolved";
?>
<tr>
<td><?= $row['id']; ?></td>
<td><?= htmlspecialchars($row['category']); ?></td>
<td><?= htmlspecialchars($row['description']); ?></td>
<td><?= $row['comp_date']; ?></td>

<td class="<?= $cls; ?>">
<?= ucfirst($status); ?>
</td>

<td>

<?php if($status=="pending"){ ?>
<form method="post" style="display:inline;">
<input type="hidden" name="cid" value="<?= $row['id']; ?>">
<button type="submit" name="start" class="start">
Start Work
</button>
</form>

<?php } elseif($status=="in progress"){ ?>
<form method="post" style="display:inline;">
<input type="hidden" name="cid" value="<?= $row['id']; ?>">
<button type="submit" name="resolve" class="resolve">
Mark Resolved
</button>
</form>

<?php } else { ?>
✔ Completed
<?php } ?>

</td>
</tr>
<?php
}}
else{
    echo "<tr><td colspan='6'>No assigned complaints</td></tr>";
}
?>

</table>

</div>
</div>

</body>
</html>
