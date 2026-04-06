<?php
session_start();
include "db.php";

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) != "student") {
    header("Location: login.php");
    exit();
}

/* ================= SESSION ================= */
$user_id    = $_SESSION['user_id'] ?? 0;
$name       = $_SESSION['name'] ?? '';
$email      = $_SESSION['email'] ?? '';
$department = $_SESSION['department'] ?? '';

$limit_error = "";

/* ================= DELETE ================= */
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM complaints 
        WHERE id=? AND user_id=? AND status='pending'");
    $stmt->bind_param("ii", $did, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: scom.php?view=complaints");
    exit();
}

/* ================= UPDATE ================= */
if (isset($_POST['update_complaint'])) {
    $cid  = (int)$_POST['cid'];
    $cat  = trim($_POST['category']);
    $desc = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE complaints 
        SET category=?, description=? 
        WHERE id=? AND user_id=? AND status='pending'");
    $stmt->bind_param("ssii", $cat, $desc, $cid, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: scom.php?view=complaints");
    exit();
}

/* ================= NEW COMPLAINT ================= */
if (isset($_POST['submit_complaint'])) {

    $today = $_POST['comp_date'];
    $complaint_type = $_POST['complaint_type'];

    // LIMIT CHECK
    $stmt = $conn->prepare("SELECT COUNT(*) AS total 
        FROM complaints WHERE user_id=? AND comp_date=?");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row['total'] >= 2) {
        $limit_error = "Daily complaint limit reached (Max 2)";
    } else {

        $cat  = trim($_POST['category']);
        $desc = trim($_POST['description']);

        // 🔥 MAIN FIX (anonymous but track owner)
        if ($complaint_type == "anonymous") {
            $insert_email = "anonymous";
        } else {
            $insert_email = $email;
        }

        $stmt = $conn->prepare("INSERT INTO complaints 
            (user_id, email, category, description, comp_date, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("issss", $user_id, $insert_email, $cat, $desc, $today);
        $stmt->execute();
        $stmt->close();

        header("Location: scom.php?view=complaints");
        exit();
    }
}

/* ================= FEEDBACK ================= */
if(isset($_POST['submit_student_feedback'])){

    $cid = (int)$_POST['cid'];
    $message = trim($_POST['student_feedback']);

    // get complaint email
    $get = $conn->prepare("SELECT email FROM complaints WHERE id=? AND user_id=?");
    $get->bind_param("ii", $cid, $user_id);
    $get->execute();
    $data = $get->get_result()->fetch_assoc();
    $get->close();

    $fb_email = $data['email'] == "anonymous" ? "anonymous@system.com" : $email;

    // check duplicate
    $check = $conn->prepare("SELECT id FROM feedback WHERE complaint_id=? AND email=?");
    $check->bind_param("is", $cid, $fb_email);
    $check->execute();
    $res = $check->get_result();

    if($res->num_rows == 0){

        $title = "Feedback for Complaint ID $cid";
        $sub_date = date('Y-m-d');

        $stmt = $conn->prepare("INSERT INTO feedback 
            (complaint_id, email, title, message, sub_date) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $cid, $fb_email, $title, $message, $sub_date);
        $stmt->execute();
        $stmt->close();
    }

    $check->close();

    header("Location: scom.php?view=complaints");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Dashboard</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins;}
body{background:linear-gradient(135deg,#dbeafe,#e0f2fe);}
.nav{background:#2563eb;color:white;padding:18px;display:flex;justify-content:space-between;}
.nav a{color:#2563eb;background:white;padding:8px 14px;border-radius:8px;text-decoration:none;}
.container{max-width:700px;margin:40px auto;}
.card{background:white;padding:30px;border-radius:18px;}
input,select,textarea,button{width:100%;padding:12px;margin-bottom:15px;border-radius:10px;border:1px solid #ccc;}
button{background:#2563eb;color:white;border:none;}
.comp{background:#f1f5f9;padding:15px;border-radius:10px;margin-bottom:15px;}

.radio-group{display:flex;gap:20px;margin-bottom:15px;}
.radio-group input{width:auto;}
.lock-msg{color:red;}
.feedback{background:#e0f7fa;padding:10px;margin-top:10px;border-radius:10px;}
</style>
</head>

<body>

<div class="nav">
<h3>🎓 Student Dashboard</h3>
<div>
<a href="scom.php">Raise</a>
<a href="scom.php?view=complaints">My Complaints</a>
<a href="logout.php">Logout</a>
</div>
</div>

<div class="container">
<div class="card">

<?php
if(isset($_GET['view']) && $_GET['view']=="complaints"){

    echo "<h2>My Complaints</h2>";

    $stmt = $conn->prepare("SELECT * FROM complaints WHERE user_id=? ORDER BY id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while($c = $result->fetch_assoc()){
        echo '<div class="comp">';
        echo "<b>{$c['category']}</b><br>";
        echo "{$c['description']}<br>";
        echo "📅 {$c['comp_date']}<br>";

        if($c['email']=="anonymous"){
            echo "Status: {$c['status']} (Anonymous)<br>";
        } else {
            echo "Status: {$c['status']}<br>";
        }

        if($c['status']=="pending"){
?>

<form method="post">
<input type="hidden" name="cid" value="<?= $c['id'] ?>">
<select name="category">
<option>Lab</option>
<option>Hostel</option>
<option>Library</option>
<option>Transport</option>
<option>Classroom</option>
<option>Canteen</option>
<option>Others</option>

</select>
<textarea name="description"><?= $c['description'] ?></textarea>
<button name="update_complaint">Update</button>
</form>

<a href="scom.php?view=complaints&delete=<?= $c['id'] ?>">Delete</a>

<?php
        } else {
            echo "<div class='lock-msg'>Locked</div>";

            if($c['status']=="resolved"){

                $f = $conn->prepare("SELECT id FROM feedback WHERE complaint_id=? AND email=?");
                $fb_email = $c['email']=="anonymous" ? "anonymous@system.com" : $email;
                $f->bind_param("is", $c['id'], $fb_email);
                $f->execute();
                $fr = $f->get_result();

                if($fr->num_rows==0){
?>

<form method="post">
<input type="hidden" name="cid" value="<?= $c['id'] ?>">
<textarea name="student_feedback" placeholder="Give feedback..." required></textarea>
<button name="submit_student_feedback">Submit Feedback</button>
</form>

<?php
                } else {
                    echo "<div class='feedback'>Feedback submitted ✅</div>";
                }
                $f->close();
            }
        }

        echo "</div>";
    }

} else {
?>

<h2>Raise Complaint</h2>

<?php if($limit_error!="") echo "<div>$limit_error</div>"; ?>

<form method="post">
<input type="date" name="comp_date" required>

<div class="radio-group">
<label><input type="radio" name="complaint_type" value="normal" checked> Normal</label>
<label><input type="radio" name="complaint_type" value="anonymous"> Anonymous</label>
</div>

<select name="category" required>
<option value="">Select Category</option>
<option>Lab</option>
<option>Hostel</option>
<option>Library</option>
<option>Transport</option>
<option>Classroom</option>
</select>

<textarea name="description" required></textarea>

<button name="submit_complaint">Submit</button>
</form>

<?php } ?>

</div>
</div>

</body>
</html>
