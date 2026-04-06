<?php
session_start();
include "db.php";

/* LOGIN CHECK */
if(!isset($_SESSION['user_id'])){
    header("Location: login.1.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$department = $_SESSION['department'];

/* ---------------- BOOKING PROCESS ---------------- */
$msg = [];
if(isset($_POST['place']) && isset($_POST['session'])){
    $place = $_POST['place'];
    $date = $_POST['book_date'];
    $session = $_POST['session'];

    if($date < date("Y-m-d")){
        $msg[$place] = "❌ Cannot book past date";
    } else {
        $check = mysqli_query($conn,"SELECT session FROM resource_bookings WHERE place='$place' AND book_date='$date'");
        $booked = [];
        while($row = mysqli_fetch_assoc($check)){
            $booked[] = $row['session'];
        }

        $alreadyBooked = false;
        if(in_array("Full Day",$booked)){
            $alreadyBooked = true;
        } else if($session=="Full Day" && (in_array("FN",$booked) || in_array("AN",$booked))){
            $alreadyBooked = true;
        } else if(in_array($session,$booked)){
            $alreadyBooked = true;
        }

        if(!$alreadyBooked){
            $insert = mysqli_query($conn,
                "INSERT INTO resource_bookings(place, department, book_date, session, booked_by)
                 VALUES('$place','$department','$date','$session','$user_id')");
            $msg[$place] = $insert ? "✅ Booking Successful" : "❌ DB Error";
        } else {
            $msg[$place] = "❌ Already Booked!";
        }
    }
}

/* ---------------- VIEW ---------------- */
$view = isset($_GET['view']) ? $_GET['view'] : "booking";

$filter_place = isset($_GET['filter_place']) ? $_GET['filter_place'] : "";
$filter_dept  = isset($_GET['filter_dept']) ? $_GET['filter_dept'] : "";
$filter_date  = isset($_GET['filter_date']) ? $_GET['filter_date'] : "";

if($view=="my"){
    $result = mysqli_query($conn,"SELECT place, book_date, session, id
                                  FROM resource_bookings
                                  WHERE booked_by='$user_id'
                                  ORDER BY book_date DESC");
} else if($view=="dept"){
    $where = [];
    if($filter_place != "") $where[] = "rb.place='$filter_place'";
    if($filter_dept  != "") $where[] = "rb.department='$filter_dept'";
    if($filter_date  != "") $where[] = "rb.book_date='$filter_date'";

    $where_sql = count($where) ? "WHERE ".implode(" AND ", $where) : "";

    $result = mysqli_query($conn,"
        SELECT rb.place, rb.department, rb.book_date, rb.session, u.name as student_name
        FROM resource_bookings rb
        JOIN users u ON rb.booked_by=u.id
        $where_sql
        ORDER BY rb.book_date DESC
    ");
}

/* ---------------- CANCEL ---------------- */
if(isset($_GET['cancel'])){
    $id = (int)$_GET['cancel'];
    mysqli_query($conn,"DELETE FROM resource_bookings WHERE id=$id AND booked_by='$user_id'");
    header("Location: resource_booking.php?view=my");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Resource Booking</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
/* ---------- STYLES SAME AS BEFORE ---------- */
body{font-family:'Roboto',sans-serif;background:linear-gradient(135deg,#e0f2fe,#f0fdf4);margin:0;}
.nav{background:#198754;color:white;padding:15px 25px;display:flex;justify-content:space-between;align-items:center;}
.left{font-size:18px;font-weight:500;}
.menu{display:flex;align-items:center;}
.menu a{background:white;color:#198754;padding:8px 14px;border-radius:8px;margin-left:10px;text-decoration:none;font-weight:500;transition:0.3s;}
.menu a:hover{background:#e2e6ea;}
.menu a.active{background:#0f5132;color:white;}
.report-btn{background:#ffc107 !important;color:black !important;padding:8px 14px;border-radius:8px;font-weight:500;text-decoration:none;}
h2{text-align:center;margin-top:20px;color:#198754;}
.filter-buttons{display:flex;justify-content:center;gap:15px;margin-bottom:20px;}
.filter-btn{background:#198754;color:white;border:none;padding:8px 10px;border-radius:10px;cursor:pointer;font-size:14px;font-weight:500;min-width:90px;width:auto;}
.filter-btn.active{background:#145c32;}
.filter-form{display:flex;justify-content:center;align-items:center;gap:10px;}
.filter-form select{padding:10px 14px;border-radius:10px;border:1px solid #ccc;min-width:220px;}
.filter-form button{background:#198754;color:white;border:none;padding:6px 8px;border-radius:8px;font-size:13px;cursor:pointer;width:auto;min-width:70px;}
.filter-form button:hover{background:#157347;}
.container{padding:20px;text-align:center;}
.card{background:white;width:280px;display:inline-block;margin:15px;padding:20px;border-radius:15px;box-shadow:0 10px 25px rgba(0,0,0,.1);}
.card h3{color:#198754;}
input,button{width:100%;padding:10px;margin:8px 0;border-radius:8px;border:1px solid #ccc;}
button{background:#198754;color:white;border:none;cursor:pointer;}
button:hover{background:#145c36;}
.msg{font-weight:bold;}
.success{color:green;}
.error{color:red;}
.session-line{display:flex;justify-content:center;gap:15px;margin-top:10px;}
table{width:90%;margin:20px auto;border-collapse:collapse;background:white;box-shadow:0 5px 15px rgba(0,0,0,.1);border-radius:10px;overflow:hidden;}
th{background:#198754;color:white;}
th,td{padding:10px;border:1px solid #ccc;text-align:center;}
tr:nth-child(even){background:#f9f9f9;}
.cancel{color:red;text-decoration:none;font-weight:bold;}
</style>
<script>
function setSession(place){
    let selected = document.querySelector("input[name='session_"+place+"']:checked");
    document.getElementById("hidden_"+place).value = selected.value;
}
function showFilter(type){
    document.getElementById('filter_place_form').style.display='none';
    document.getElementById('filter_dept_form').style.display='none';
    document.getElementById('filter_date_form').style.display='none';
    document.getElementById('filter_place_btn').classList.remove('active');
    document.getElementById('filter_dept_btn').classList.remove('active');
    document.getElementById('filter_date_btn').classList.remove('active');
    if(type=='place'){document.getElementById('filter_place_form').style.display='inline-block';document.getElementById('filter_place_btn').classList.add('active');}
    if(type=='department'){document.getElementById('filter_dept_form').style.display='inline-block';document.getElementById('filter_dept_btn').classList.add('active');}
    if(type=='date'){document.getElementById('filter_date_form').style.display='inline-block';document.getElementById('filter_date_btn').classList.add('active');}
}
</script>
</head>
<body>

<div class="nav">
<div class="left">👋 Welcome, <?php echo $name; ?></div>
<div class="menu">
<a href="?view=booking" class="<?php if($view=='booking') echo 'active'; ?>">Book</a>
<a href="?view=my" class="<?php if($view=='my') echo 'active'; ?>">My Bookings</a>
<a href="?view=dept" class="<?php if($view=='dept') echo 'active'; ?>">All Bookings</a>
<?php if($view=="dept"): ?>
<a href="report.1.php?type=daily" class="report-btn">Daily Report</a>
<a href="report.1.php?type=monthly" class="report-btn">Monthly Report</a>
<a href="report.1.php?type=yearly" class="report-btn">Yearly Report</a>
<?php endif; ?>
<a href="logout.php">Logout</a>
</div>
</div>

<div class="container">

<?php if($view=="booking"): ?>
<?php
$places = ["Auditorium","Conference Hall","SCS Hall","Non Residence Hall","Guest Room 1","Guest Room 2"];
foreach($places as $place){
$safe = preg_replace('/[^A-Za-z0-9]/','_',$place);
?>
<div class="card">
<h3><?php echo $place; ?></h3>
<form method="POST">
<input type="hidden" name="place" value="<?php echo $place; ?>">
<input type="date" name="book_date" min="<?php echo date('Y-m-d'); ?>" required>
<div class="session-line">
<label><input type="radio" name="session_<?php echo $safe; ?>" value="FN" onclick="setSession('<?php echo $safe; ?>')" required> FN</label>
<label><input type="radio" name="session_<?php echo $safe; ?>" value="AN" onclick="setSession('<?php echo $safe; ?>')"> AN</label>
<label><input type="radio" name="session_<?php echo $safe; ?>" value="Full Day" onclick="setSession('<?php echo $safe; ?>')"> Full</label>
</div>
<input type="hidden" name="session" id="hidden_<?php echo $safe; ?>">
<button type="submit">Book</button>
</form>
<?php if(isset($msg[$place])){ ?>
<div class="msg <?php echo strpos($msg[$place],'✅')!==false?'success':'error'; ?>"><?php echo $msg[$place]; ?></div>
<?php } ?>
</div>
<?php } endif; ?>

<?php if($view=="my" || $view=="dept"): ?>
<h2><?php echo $view=="my"?"My Bookings":"All Bookings"; ?></h2>

<?php if($view=="dept"): ?>
<?php
$places = ["Auditorium","Conference Hall","SCS Hall","Non Residence Hall","Guest Room 1","Guest Room 2"];
?>
<div class="filter-buttons">
<button class="filter-btn <?php if($filter_place) echo 'active'; ?>" id="filter_place_btn" onclick="showFilter('place')">By Place</button>
<button class="filter-btn <?php if($filter_dept) echo 'active'; ?>" id="filter_dept_btn" onclick="showFilter('department')">By Department</button>
<button class="filter-btn <?php if($filter_date) echo 'active'; ?>" id="filter_date_btn" onclick="showFilter('date')">By Date</button>
</div>

<div class="filter-form">
<form id="filter_place_form" method="GET" style="display:<?php echo $filter_place?'inline-block':'none'; ?>;">
<input type="hidden" name="view" value="dept">
<select name="filter_place">
<option value="">-- Select Place --</option>
<?php foreach($places as $p){$selected = $filter_place==$p?"selected":""; echo "<option value='$p' $selected>$p</option>";} ?>
</select>
<button type="submit">Apply</button>
<button type="button" onclick="window.location='?view=dept'">Reset</button>
</form>

<form id="filter_dept_form" method="GET" style="display:<?php echo $filter_dept?'inline-block':'none'; ?>;">
<input type="hidden" name="view" value="dept">
<select name="filter_dept">
<option value="">-- Select Department --</option>
<?php
$depts = mysqli_query($conn,"SELECT DISTINCT department FROM resource_bookings");
while($row=mysqli_fetch_assoc($depts)){
$selected = $filter_dept==$row['department'] ? "selected" : "";
echo "<option value='{$row['department']}' $selected>{$row['department']}</option>";
}
?>
</select>
<button type="submit">Apply</button>
<button type="button" onclick="window.location='?view=dept'">Reset</button>
</form>

<form id="filter_date_form" method="GET" style="display:<?php echo $filter_date?'inline-block':'none'; ?>;">
<input type="hidden" name="view" value="dept">
<input type="date" name="filter_date" value="<?php echo $filter_date; ?>">
<button type="submit">Apply</button>
<button type="button" onclick="window.location='?view=dept'">Reset</button>
</form>
</div>
<?php endif; ?>

<table>
<tr>
<th>Place</th>
<th>Date</th>
<th>Session</th>
<?php if($view=="my") echo "<th>Action</th>"; ?>
<?php if($view=="dept") echo "<th>Department</th><th>Booked By</th>"; ?>
</tr>

<?php
if(mysqli_num_rows($result)>0){
while($row=mysqli_fetch_assoc($result)){
echo "<tr>
<td>{$row['place']}</td>
<td>{$row['book_date']}</td>
<td>{$row['session']}</td>";
if($view=="my"){echo "<td><a class='cancel' href='?cancel={$row['id']}'>Cancel</a></td>";}
if($view=="dept"){echo "<td>{$row['department']}</td><td>{$row['student_name']}</td>";}
echo "</tr>";
}
}else{
$colspan = $view=="my"?4:5;
echo "<tr><td colspan='$colspan'>No bookings</td></tr>";
}
?>
</table>
<?php endif; ?>

</div>
</body>
</html>
