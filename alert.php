<?php
include "db.php";

$today = date('Y-m-d');

/*
Condition:
1. Status not resolved
2. Complaint older than 2 days
3. Last alert sent 2 days ago OR NULL
*/

$sql = "SELECT * FROM complaints 
        WHERE status!='resolved'
        AND DATEDIFF('$today', comp_date) >= 2
        AND (last_alert_date IS NULL 
             OR DATEDIFF('$today', last_alert_date) >= 2)";

$result = mysqli_query($conn, $sql);

while($row = mysqli_fetch_assoc($result)){

    $complaint_id = $row['id'];
    $category     = $row['category'];
    $description  = $row['description'];

    // INSERT ALERT INTO ALERT TABLE (Optional)
    mysqli_query($conn, "
        INSERT INTO alerts (complaint_id, message, alert_date)
        VALUES (
            '$complaint_id',
            'Complaint pending for 2+ days: $category',
            '$today'
        )
    ");

    // Update last alert date
    mysqli_query($conn, "
        UPDATE complaints 
        SET last_alert_date='$today'
        WHERE id='$complaint_id'
    ");
}

echo "Alert check completed.";
?>
