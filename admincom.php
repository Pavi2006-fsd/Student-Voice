<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

session_start();
include "db.php";

/* ================= LOGIN CHECK ================= */
if(!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) != "admin"){
    header("Location: login.php");
    exit();
}

/* ================= ASSIGN ================= */
if(isset($_POST['update'])){

    $id  = (int)$_POST['id'];
    $sub = (int)$_POST['subadmin'];

    if($id > 0 && $sub > 0){

        mysqli_query($conn,"UPDATE complaints SET assigned_to='$sub' WHERE id='$id'");

        // Get subadmin email
        $getEmail = mysqli_query($conn,"SELECT name,email FROM users WHERE id='$sub'");

        if($getEmail && mysqli_num_rows($getEmail) > 0){
            $row = mysqli_fetch_assoc($getEmail);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'yourgmail@gmail.com';
                $mail->Password = 'your_app_password';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('yourgmail@gmail.com', 'Complaint System');
                $mail->addAddress($row['email']);

                $mail->isHTML(true);
                $mail->Subject = 'New Complaint Assigned';
                $mail->Body = "Hello <b>".$row['name']."</b>, Complaint assigned.";

                $mail->send();
            } catch (Exception $e) {
                echo "Mail Error: ".$mail->ErrorInfo;
            }
        }
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<style>
body{font-family:Poppins;background:#f1f5f9;padding:20px;}
table{width:100%;border-collapse:collapse;background:white;}
th,td{padding:10px;text-align:center;border-bottom:1px solid #ddd;}
th{background:#2563eb;color:white;}
.top{background:#0f766e;color:white;padding:18px 30px;display:flex;justify-content:space-between;align-items:center;}
.top a{background:white;color:#0f766e;padding:8px 15px;border-radius:8px;text-decoration:none;font-weight:600;}
button{padding:5px 10px;background:#2563eb;color:white;border:none;border-radius:5px;}
</style>
</head>
<body>

<div class="top">
    <h2>Admin Dashboard</h2>

    <div style="display:flex; gap:10px;">
        <a href="download_report.php">Report</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<table>
<tr>
<th>ID</th>
<th>Student</th>
<th>Category</th>
<th>Description</th>
<th>Date</th>
<th>Status</th>
<th>Feedback</th>
<th>Assign</th>
<th>Action</th>
</tr>

<?php
$q = mysqli_query($conn,"SELECT * FROM complaints ORDER BY id DESC");

if($q && mysqli_num_rows($q) > 0){

while($c = mysqli_fetch_assoc($q)){

    /* ===== STUDENT NAME ===== */
    if($c['user_id'] == 0){
        $student = "Anonymous";
    } else {
        $res = mysqli_query($conn,"SELECT name FROM users WHERE id=".$c['user_id']);
        if($res && mysqli_num_rows($res) > 0){
            $u = mysqli_fetch_assoc($res);
            $student = $u['name'];
        } else {
            $student = "Unknown";
        }
    }

    /* ===== FEEDBACK SAFE FETCH ===== */
    $feedback_text = "No Feedback";

    $fres = mysqli_query($conn,"SELECT message FROM feedback WHERE complaint_id=".$c['id']." ORDER BY id DESC LIMIT 1");

    if($fres){
        if(mysqli_num_rows($fres) > 0){
            $f = mysqli_fetch_assoc($fres);
            $feedback_text = htmlspecialchars($f['message']);
        }
    }
?>

<tr>
<form method="post">

<td><?= $c['id'] ?></td>
<td><?= $student ?></td>
<td><?= htmlspecialchars($c['category']) ?></td>
<td><?= htmlspecialchars($c['description']) ?></td>
<td><?= $c['comp_date'] ?></td>

<td>
<?php
if($c['status']=="pending") echo "🔴 Pending";
elseif($c['status']=="in progress") echo "🟠 In Progress";
elseif($c['status']=="resolved") echo "🟢 Resolved";
else echo "Not Set";
?>
</td>

<td><?= $feedback_text ?></td>

<td>
<select name="subadmin" required>
<option value="">Select</option>
<?php
$subs = mysqli_query($conn,"SELECT id,name FROM users WHERE role='subadmin'");
if($subs){
while($s=mysqli_fetch_assoc($subs)){
?>
<option value="<?= $s['id'] ?>" <?= ($c['assigned_to']==$s['id'])?'selected':'' ?>>
<?= $s['name'] ?>
</option>
<?php }} ?>
</select>
</td>

<td>
<input type="hidden" name="id" value="<?= $c['id'] ?>">
<?php if(empty($c['assigned_to'])){ ?>
<button name="update">Assign</button>
<?php } else { ?>
<button disabled style="background:green;cursor:not-allowed;">Assigned</button>
<?php } ?>

</td>

</form>
</tr>

<?php
}
} else {
    echo "<tr><td colspan='9'>No complaints found</td></tr>";
}
?>

</table>

</body>
</html>
