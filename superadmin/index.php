<?php
session_start();
include('db.php');

if (isset($_SESSION['super_admin'])) {
    header("Location: superadmin_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM super_admins WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);

        $_SESSION['super_admin'] = $admin['username'];
        $_SESSION['super_admin_id'] = $admin['id'];

        $admin_name = $admin['username'];
        $admin_id = $admin['id'];
        $logQuery = "INSERT INTO activity_logs (admin_id, admin_name, action, table_name, record_id)
                     VALUES ($admin_id, '$admin_name', 'Logged In', 'super_admins', $admin_id)";
        mysqli_query($conn, $logQuery);

        header("Location: superadmin_dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Super Admin Login - UltraServe</title>
<link rel="icon" type="image/png" sizes="32x32" href="img/logo.png">
<style>
/* Root colors */
:root {
    --primary-color: #1e3974;
    --primary-hover: #163359;
    --bg-color: white;
    --text-color: #333;
    --input-bg: #fff;
    --input-border: #ccc;
}

/* Body & Layout */
body {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    min-height: 100vh;
    background-color: var(--bg-color);
}

.illustration-container {
    flex: 1;
    background: url('../img/login.gif') no-repeat center center;
    background-size: cover;
    height: 100vh;
}

.form-container {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px;
}
.form-wrap {
    background-color: rgba(255, 255, 255, 0.2); /* semi-transparent */
    width: 100%;
    max-width: 400px;
    padding: 40px 30px;
    border-radius: 12px;
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    /* box-shadow removed for clean look */
}


.form-wrap:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
}

.form-wrap h2 {
    text-align: center;
    font-size: 28px;
    color: var(--text-color);
    margin-bottom: 25px;
    font-weight: 700;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
    color: var(--text-color);
}

.form-group input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--input-border);
    border-radius: 8px;
    font-size: 16px;
    background-color: var(--input-bg);
    transition: all 0.3s ease;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(30, 57, 116, 0.1);
}

.form-group button {
    width: 100%;
    padding: 14px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-group button:hover {
    background-color: var(--primary-hover);
}

.error {
    color: #dc2626;
    text-align: center;
    margin-bottom: 15px;
    font-weight: 500;
}

.form-links {
    text-align: center;
    margin-top: 15px;
    font-size: 14px;
}

.form-links a {
    color: var(--primary-color);
    text-decoration: none;
}

.form-links a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    body {
        flex-direction: column;
    }

    .illustration-container,
    .form-container {
        flex: none;
        width: 100%;
        height: auto;
    }

    .illustration-container {
        height: 250px;
        background-size: cover;
    }

    .form-wrap {
        margin: 20px;
    }
}
</style>
</head>
<body>

<div class="illustration-container"></div>

<div class="form-container">
    <form class="form-wrap" method="POST">
        <h2>Super Admin Login</h2>

        <?php if(isset($error)){ ?>
            <div class="error"><?= $error ?></div>
        <?php } ?>

        <div class="form-group">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" placeholder="Enter username" required autofocus />
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter password" required />
        </div>

        <div class="form-group">
            <button type="submit">Login</button>
        </div>

        <div class="form-links">
            <p><a href="../login.php">Admin Login here</a></p>
        </div>
    </form>
</div>


</body>
</html>
