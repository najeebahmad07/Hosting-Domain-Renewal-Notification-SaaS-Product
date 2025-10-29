<?php
session_start();
include('db.php'); // DB connection file should define $conn

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Basic validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $username = mysqli_real_escape_string($conn, $username);
        $email = mysqli_real_escape_string($conn, $email);
        $password = mysqli_real_escape_string($conn, $password);

        // Check for existing username or email
        $checkQuery = "SELECT * FROM admin WHERE username='$username' OR email='$email'";
        $checkResult = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            $error = "Username or Email already exists!";
        } else {
            // Direct text password and assign super_admin_id = 11
            $insert = "INSERT INTO admin (username, email, password, super_admin_id)
                       VALUES ('$username', '$email', '$password', 11)";

            if (mysqli_query($conn, $insert)) {
                echo "<script>alert('Registration successful! You can now login.'); window.location='login.php';</script>";
                exit();
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>UltraServe Admin Registration</title>

<link rel="icon" type="image/png" sizes="32x32" href="img/logo.png">
<link rel="apple-touch-icon" href="img/logo.png">
<link rel="shortcut icon" href="img/logo.png" type="image/x-icon">

<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    height: 100vh;
}
.illustration-container {
    flex: 1;
    background: url('img/registration.jpg') no-repeat center center;
    background-size: cover;
    height: 100vh;
}
.form-container {
    flex: 1;
    background-color: white;
    display: flex;
    justify-content: center;
    align-items: center;
}
.form-wrap {
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    color: #333;
}
.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}
.form-group button {
    width: 100%;
    padding: 12px;
    background-color: #1e3974;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
}
.form-group button:hover {
    background-color: #163359;
}
.error {
    color: red;
    text-align: center;
    margin-bottom: 10px;
}
.success {
    color: green;
    text-align: center;
    margin-bottom: 10px;
}
@media (max-width: 768px) {
    body { flex-direction: column; }
    .illustration-container,
    .form-container {
        flex: none;
        width: 100%;
        height: auto;
    }
    .illustration-container {
        height: 250px;
        background-size: contain;
    }
}
</style>
</head>
<body>

<div class="illustration-container"></div>

<div class="form-container">
<form class="form-wrap" method="POST">
    <h2 style="text-align: center; color: #333; margin-bottom: 20px;">Admin Registration</h2>

    <?php if (!empty($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <div class="form-group">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" placeholder="Enter username" required />
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" placeholder="Enter email" required />
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" placeholder="Enter password" required />
    </div>

    <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input id="confirm_password" name="confirm_password" type="password" placeholder="Confirm password" required />
    </div>

    <div class="form-group">
        <button type="submit">Register</button>
    </div>

    <p style="text-align:center; font-size:14px;">Already have an account? <a href="login.php">Login here</a></p>
    <p style="text-align:center; font-size:14px;"> <a href="superadmin/">Super Admin Login here</a></p>
</form>
</div>

</body>
</html>
