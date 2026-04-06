<?php
session_start();
include "db.php";

if(isset($_POST['login'])){
  $email=$_POST['email'];
  $pass=$_POST['password'];

  $q=mysqli_query($conn,"SELECT * FROM users WHERE email='$email' AND password='$pass'");
  if(mysqli_num_rows($q)==1){
    $r=mysqli_fetch_assoc($q);
    $_SESSION['user_id']=$r['id'];
    $_SESSION['name']=$r['name'];
    $_SESSION['email']=$r['email'];
    $_SESSION['role']=$r['role'];

    if($r['role']=="student") header("Location:scom.php");
    elseif($r['role']=="admin") header("Location:admincom.php");
    else header("Location:subadmin.php");
    exit();
  }else{
    $err="Invalid email or password";
  }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<style>
:root{--green:#198754;--green-dark:#146c43;}
body{
  margin:0;
  min-height:100vh;
  display:flex;
  justify-content:center;
  align-items:center;
  background:#f4fdf7;
}
.login-box{
  width:360px;
  background:white;
  padding:30px;
  border-radius:15px;
  box-shadow:0 10px 25px rgba(0,0,0,.2);
}
.login-box h2{text-align:center;color:var(--green);}
input{
  width:100%;
  padding:12px;
  margin:12px 0;
  border-radius:8px;
  border:1px solid #ccc;
}
button{
  width:100%;
  padding:12px;
  background:var(--green);
  color:white;
  border:none;
  border-radius:8px;
  font-weight:600;
}
button:hover{background:var(--green-dark);}
.err{text-align:center;color:red;font-weight:600;}
</style>
</head>
<body>

<div class="login-box">
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
