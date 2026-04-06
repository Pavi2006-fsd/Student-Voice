<?php
$conn = mysqli_connect("localhost","root","","student_voice");

if(isset($_GET['place']) && isset($_GET['date'])){

    $place = $_GET['place'];
    $date = $_GET['date'];

    $result = mysqli_query($conn,
    "SELECT department, session 
     FROM resource_booking
     WHERE place='$place' 
     AND book_date='$date'");

    if(mysqli_num_rows($result) > 0){

        while($row = mysqli_fetch_assoc($result)){
            echo $row['session']." - ".$row['department']."<br>";
        }

    } else {
        echo "free";
    }

}
?>
