<?php
session_start();
include "db.php";

/* CHECK ADMIN LOGIN */
if(!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) != "admin"){
    header("Location: login.php");
    exit();
}

/* FILE NAME */
$filename = "complaints_report_" . date('Y-m-d') . ".csv";

/* HEADERS FOR DOWNLOAD */
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=$filename");

/* OPEN OUTPUT */
$output = fopen("php://output", "w");

/* COLUMN HEADERS */
fputcsv($output, ['ID','Student Name','Category','Description','Date','Status']);

/* FETCH DATA */
$query = mysqli_query($conn,"
    SELECT c.*, u.name 
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.id DESC
");

/* WRITE DATA */
while($row = mysqli_fetch_assoc($query)){
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['category'],
        $row['description'],
        $row['comp_date'],
        $row['status']
    ]);
}

fclose($output);
exit();
?>
