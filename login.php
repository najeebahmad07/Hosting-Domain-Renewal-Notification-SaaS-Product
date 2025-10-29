<?php
session_start();
include('db.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // get input (you may want to validate / trim as needed)
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Use prepared statement to avoid SQL injection
    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ? LIMIT 1");
    if ($stmt === false) {
        // handle prepare error (production: log rather than display)
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    // Direct string comparison (plaintext)
    if ($admin && $password === $admin['password']) {
        // Login success
        $_SESSION['admin'] = $admin['username'];
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: dashboard.php?page=dashboard");
        exit;
    } else {
        echo "<script>alert('Invalid credentials!');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UltraServe | Admin Login</title>
<link rel="icon" type="image/png" sizes="32x32" href="img/logo.png">

<!-- Apple devices -->
<link rel="apple-touch-icon" href="img/logo.png">

<!-- For older browsers -->
<link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
<style>
/* --- Keep your existing styles --- */
body { font-family: Arial, sans-serif; margin:0; padding:0; display:flex; height:100vh; }
.illustration-container { flex:1; background:url('img/login.gif') no-repeat center center; background-size:cover; height:100vh; }
.form-container { flex:1; background-color:white; display:flex; justify-content:center; align-items:center; }
.form-wrap { background:white; padding:30px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:100%; max-width:400px; }
.form-group { margin-bottom:20px; }
.form-group label { display:block; margin-bottom:8px; font-size:14px; color:#333; }
.form-group input { width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; font-size:16px; }
.form-group button { width:100%; padding:12px; background-color:#1e3974; color:white; border:none; border-radius:5px; font-size:16px; cursor:pointer; }
.form-group button:hover { background-color:#163359; }
.error { color:red; text-align:center; margin-top:10px; }
.forgot-link { display:block; text-align:right; margin-top:5px; font-size:14px; }
.forgot-link a { text-decoration:none; color:#1e3974; }
.forgot-link a:hover { text-decoration:underline; }

@media (max-width: 768px) {
    body { flex-direction:column; }
    .illustration-container, .form-container { flex:none; width:100%; height:auto; }
    .illustration-container { height:250px; background-size:contain; }
}
</style>
</head>
<body>

<div class="illustration-container"></div>

<div class="form-container">
<form class="form-wrap" method="POST">
    <h2 style="text-align:center; color:#333; margin-bottom:20px;">UltraServe Admin Login</h2>

    <div class="form-group">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" placeholder="Enter your username" required />
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" placeholder="Enter your password" required />
        <span class="forgot-link"><a href="forgot-password.php">Forgot Password?</a></span>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-group">
        <button type="submit">Login</button>
    </div>
</form>
</div>

</body>
</html>
