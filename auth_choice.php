<!DOCTYPE html>
<html>
<head>
<title>Student Voice - Access</title>
<style>
:root{
  --green:#198754;
  --green-dark:#146c43;
  --bg:#f4fdf7;
}
*{
  margin:0;
  padding:0;
  box-sizing:border-box;
  font-family:Segoe UI,sans-serif;
}
body{
  height:100vh;
  display:flex;
  justify-content:center;
  align-items:center;
  background:linear-gradient(135deg,#e6f4ea,#f4fdf7);
}
.card{
  width:340px;
  background:white;
  padding:35px;
  border-radius:15px;
  text-align:center;
  box-shadow:0 10px 25px rgba(0,0,0,.2);
}
.card h2{color:var(--green);margin-bottom:10px;}
.card p{color:#555;margin-bottom:25px;}
.btn{
  display:block;
  padding:12px;
  margin:12px 0;
  background:var(--green);
  color:white;
  text-decoration:none;
  border-radius:25px;
  font-weight:600;
}
.btn:hover{background:var(--green-dark);}
.register{background:#0f766e;}
.register:hover{background:#115e59;}
</style>
</head>
<body>

<div class="card">
  <h2>Resource Management </h2>
  <p>Please login or create a new account</p>
  <a href="login.php" class="btn">🔐 Login</a>
  <a href="regi.php" class="btn register">📝 Register</a>
</div>

</body>
</html>
