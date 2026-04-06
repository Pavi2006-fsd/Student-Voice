<?php
session_start();
include "db.php";

if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];

    $q = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($q) == 1){
        $r = mysqli_fetch_assoc($q);

        // ✅ PLAINTEXT CHECK
        if($r['password'] === $pass){

            $_SESSION['user_id'] = $r['id'];
            $_SESSION['name'] = $r['name'];
            $_SESSION['email'] = $r['email'];
            $_SESSION['role'] = $r['role'];
            $_SESSION['department'] = $r['department'];

            header("Location: resource_booking.php");
            exit();
        } else {
            $err = "Invalid password";
        }
    } else {
        $err = "Email not found";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<style>
body{font-family:Segoe UI;background:#f4fdf7;display:flex;justify-content:center;align-items:center;height:100vh;}
.box{background:#fff;padding:30px;width:350px;border-radius:10px;box-shadow:0 0 10px #ccc;}
input{width:100%;padding:10px;margin:10px 0;}
button{width:100%;padding:10px;background:green;color:#fff;border:none;}
.err{color:red;text-align:center;}
</style>
</head>
<body>

<div class="box">
<h2>Login</h2>

<form method="post">
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button name="login">Login</button>
</form>

<?php if(isset($err)) echo "<div class='err'>$err</div>"; ?>

</div>

</body>
</html>
