<?php
session_start();
include "db.php";

$error="";

if(isset($_POST['register'])){
  $name = mysqli_real_escape_string($conn,$_POST['name']);
  $email = mysqli_real_escape_string($conn,$_POST['email']);
  $pass = mysqli_real_escape_string($conn,$_POST['password']); // plain text
  $dept = mysqli_real_escape_string($conn,$_POST['department']);
  $role = mysqli_real_escape_string($conn,$_POST['role']);

  // ✅ Only vcw email validation
  if(!preg_match('/^[a-zA-Z]+@vcw\.ac\.in$/', $email)){
      $error = "❌ Only vcw.ac.in email allowed (Example: staff@vcw.ac.in)";
  }
  else {

    // ✅ Check duplicate
    $check = mysqli_query($conn,"SELECT id FROM users WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){
      $error="❌ Email already registered";
    } 
    else {

      // ❗ Plain text password (no hashing)
      $insert = mysqli_query($conn,"
        INSERT INTO users(name,email,password,department,role)
        VALUES('$name','$email','$pass','$dept','$role')
      ");

      if($insert){
          header("Location: login.1.php");
          exit();
      } else {
          $error = "❌ Registration failed: " . mysqli_error($conn);
      }
    }
  }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>

<style>
:root{
  --green:#198754;
  --green-dark:#146c43;
}

body{
  margin:0;
  font-family:Segoe UI;
  background:#f4fdf7;
  display:flex;
  justify-content:center;
  align-items:center;
  height:100vh;
}

.card{
  background:white;
  padding:30px;
  width:400px;
  border-radius:15px;
  box-shadow:0 10px 25px rgba(0,0,0,.15);
}

h2{
  text-align:center;
  color:var(--green);
}

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
  cursor:pointer;
}

button:hover{
  background:var(--green-dark);
}

.error{
  background:#f8d7da;
  color:#842029;
  padding:10px;
  border-radius:8px;
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

<!-- ✅ VCW EMAIL ONLY -->
<input type="email" name="email"
pattern="[a-zA-Z]+@vcw\.ac\.in"
placeholder="Enter VCW Email (e.g. staff@vcw.ac.in)" required>

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
<option>B.Sc., Information Technology</option>
<option>B.Sc., Computer Technology</option>
<option>B.Sc., Costume Design & Fashion</option>
<option>B.Com., (Additional Section)</option>
<option>B.Com., with CA</option>
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
<option value="staff">Staff</option>
</select>

<button name="register">Register</button>

</form>
</div>

</body>
</html>
