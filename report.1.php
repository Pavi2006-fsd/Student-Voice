<?php
session_start();
include "db.php";

/* LOGIN CHECK */
if(!isset($_SESSION['user_id'])){
    header("Location: login.1.php");
    exit();
}

$type = isset($_GET['type']) ? $_GET['type'] : 'daily';

// Set Excel file name
$filename = "resource_booking_".$type."_".date("Y-m-d").".xls";

// Headers to force download as Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");

// Start table
echo "<table border='1'>";
echo "<tr>
<th>Place</th>
<th>Department</th>
<th>Date</th>
<th>Session</th>
<th>Booked By</th>
</tr>";

// Build query depending on type
$query = "SELECT rb.place, rb.department, rb.book_date, rb.session, u.name as student_name
          FROM resource_bookings rb
          JOIN users u ON rb.booked_by = u.id";

if($type == "daily"){
    $query .= " WHERE rb.book_date = CURDATE()";
} else if($type == "monthly"){
    $query .= " WHERE MONTH(rb.book_date) = MONTH(CURDATE()) AND YEAR(rb.book_date) = YEAR(CURDATE())";
} else if($type == "yearly"){
    $query .= " WHERE YEAR(rb.book_date) = YEAR(CURDATE())";
}

$query .= " ORDER BY rb.book_date DESC";

$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        echo "<tr>
        <td>{$row['place']}</td>
        <td>{$row['department']}</td>
        <td>{$row['book_date']}</td>
        <td>{$row['session']}</td>
        <td>{$row['student_name']}</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='5'>No bookings found</td></tr>";
}

echo "</table>";
exit();
?>
