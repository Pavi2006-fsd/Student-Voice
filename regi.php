<?php
include "db.php";
$error="";

if(isset($_POST['register'])){
  $name  = $_POST['name'];
  $email = strtolower(trim($_POST['email']));
  $pass  = $_POST['password'];
  $dept  = $_POST['department'];
  $role  = $_POST['role'];

  // ✅ DOMAIN CHECK ONLY (@vcw.ac.in)
  if(!str_ends_with($email, "@vcw.ac.in")){
    $error = "❌ Only @vcw.ac.in email allowed";
  }
  else{

    // ✅ CHECK EXISTING EMAIL
    $check = mysqli_query($conn,"SELECT id FROM users WHERE email='$email'");
    
    if(mysqli_num_rows($check)>0){
      $error="❌ Email already registered";
    }else{

      // ❌ PLAIN TEXT PASSWORD (NOT SECURE)
      $plain_pass = $pass;

      // ✅ INSERT USER
      mysqli_query($conn,"
        INSERT INTO users(name,email,password,department,role)
        VALUES('$name','$email','$plain_pass','$dept','$role')
      ");

      header("Location: login.php");
      exit();
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<style>
:root{--green:#198754;--green-dark:#146c43;}
body{
  margin:0;
  font-family:Segoe UI;
  background:#f4fdf7;
  min-height:100vh;
  display:flex;
  justify-content:center;
  align-items:center;
}
.card{
  background:white;
  padding:30px;
  width:400px;
  border-radius:15px;
  box-shadow:0 10px 25px rgba(0,0,0,.15);
}
h2{text-align:center;color:var(--green);}
input,select{
  width:100%;
  padding:12px;
  margin:10px 0;
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
  cursor:pointer;
}
button:hover{background:var(--green-dark);}
.error{
  background:#f8d7da;
  color:#842029;
  padding:10px;
  border-radius:8px;
  margin-bottom:10px;
  text-align:center;
}
</style>
</head>
<body>

<div class="card">
<h2>Create Account</h2>

<?php if($error!=""){ ?>
<div class="error"><?= $error ?></div>
<?php } ?>

<form method="post">

<input type="text" name="name" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email (@vcw.ac.in)" required>
<input type="password" name="password" placeholder="Password" required>

<select name="department" required>
<option value="">Select Course</option>

<optgroup label="UG - Aided">
<option>B.A., History</option>
<option>B.A., English</option>
<option>B.Sc., Mathematics</option>
<option>B.Sc., Physics</option>
<option>B.Sc., Chemistry</option>
<option>B.Sc., Botany</option>
<option>B.Sc., Zoology</option>
<option>B.Sc., Nutrition & Dietetics</option>
<option>B.Sc., Computer Science</option>
<option>B.Com.,</option>
</optgroup>

<optgroup label="UG - Un-Aided">
<option>B.A., English Literature</option>
<option>B.Sc., Mathematics</option>
<option>B.Sc., Physics</option>
<option>B.Sc., Biochemistry</option>
<option>B.C.A.,</option>
<option>B.Sc., Computer Science (Additional Section)</option>
<option>B.Sc., Information Technology</option>
<option>B.Sc., Computer Technology</option>
<option>B.Sc., Costume Design & Fashion</option>
<option>B.Com., (Additional Section)</option>
<option>B.Com., with CA (Additional Section)</option>
<option>B.Com., Corporate Secretaryship</option>
<option>B.Com., Cooperation</option>
<option>B.Com., E-Commerce</option>
<option>B.B.A., with CA</option>
<option>B.A., Tamil Literature</option>
<option>B.Com., Professional Accounting</option>
<option>B.Com., Banking and Insurance</option>
<option>B.Com., with Accounting and Finance</option>
<option>B.Sc., Computer Science with Data Analytics</option>
<option>B.Sc., Geography</option>
<option>B.Sc., Computer Science with Artificial Intelligence</option>
<option>B.Sc., Computer Science with Cyber Security</option>
<option>B.Voc Fashion and Boutique Management</option>
<option>B.Com., Business Analytics</option>
<option>B.Sc., Internet of Things</option>
<option>B.Sc., Computer Science with Cognitive Systems</option>
</optgroup>

<optgroup label="PG - Aided">
<option>M.A., History</option>
<option>M.A., English Literature</option>
<option>M.Sc., Botany</option>
<option>M.Com.,</option>
</optgroup>

<optgroup label="PG - Un-Aided">
<option>M.A., English Literature</option>
<option>M.Sc., Mathematics</option>
<option>M.Sc., Physics</option>
<option>M.Sc., Foods & Nutrition</option>
<option>M.C.A., Computer Applications</option>
<option>M.Sc., Computer Science</option>
<option>M.Com.,</option>
<option>M.Com., CA</option>
<option>M.Com., Corporate Secretaryship</option>
<option>M.L.I.Sc.,</option>
<option>M.A., Tamil</option>
<option>M.Sc., Chemistry</option>
<option>MSW</option>
<option>M.Sc., Zoology</option>
</optgroup>

</select>

<select name="role" required>
<option value="">Select Role</option>
<option value="student">Student</option>
<option value="admin">Admin</option>
<option value="subadmin">Sub Admin</option>
</select>

<br>
<button name="register">Register</button>

</form>
</div>

</body>
</html>
