<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$conn = mysqli_connect("localhost","root","","student_voice");

if(!$conn){
    die("Database connection failed");
}
?>
