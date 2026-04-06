<?php
include "db.php";
if(isset($_SESSION['role'])){
    if($_SESSION['role']=="student") header("Location:scom.php");
    elseif($_SESSION['role']=="admin") header("Location:admincom.php");
    else header("Location:subadmin.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Student Voice - VCW</title>
<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}
body{
    background:#f0fff4;
}

/* HEADER */
.header{
    background:#2f855a;
    color:white;
    padding:18px;
    text-align:center;
}
.header h1{
    font-size:26px;
}

/* CONTAINER */
.container{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:40px;
    margin-top:80px;
}

/* CARD */
.card{
    width:320px;
    background:white;
    padding:30px;
    border-radius:10px;
    text-align:center;
    box-shadow:0 8px 20px rgba(0,0,0,.15);
    transition:.3s;
}
.card:hover{
    transform:translateY(-6px);
}

.card h2{
    color:#2f855a;
    margin-bottom:10px;
}
.card p{
    color:#444;
    margin-bottom:20px;
}

/* BUTTON */
.card a{
    display:inline-block;
    padding:12px 30px;
    background:#2f855a;
    color:white;
    text-decoration:none;
    border-radius:6px;
    font-weight:600;
}
.card a:hover{
    background:#276749;
}

/* ICON */
.icon{
    font-size:45px;
    margin-bottom:15px;
}
</style>
</head>

<body>

<div class="header">
    <h1>Vellalar College for Women</h1>
    <p>Smart Complaint & Resource Management System</p>
</div>

<div class="container">

    <!-- Complaint Portal -->
    <div class="card">
        <div class="icon">📢</div>
        <h2>Student Complaint Portal</h2>
        <p>Submit and track your complaints online</p>
        <a href="auth_choice.php">ENTER</a>
    </div>

    <!-- Resource Booking -->
    <div class="card">
        <div class="icon">🏢</div>
        <h2>Resource Booking</h2>
        <p>Book auditorium, seminar halls & rooms</p>
        <a href="resource_booking.php">BOOK NOW</a>
    </div>

</div>

</body>
</html>
