<?php
session_start();
include('db.php');

if (!isset($_SESSION['super_admin_id'])) {
    header("Location: superadmin-login.php");
    exit();
}

$admin_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$admin_id) {
    die("Invalid Admin ID");
}

// Fetch admin data
$query = "SELECT * FROM admin WHERE id=$admin_id";
$result = mysqli_query($conn, $query);
$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    die("Admin not found");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if(!empty($password)){
        $updateQuery = "UPDATE admin SET username='$username', email='$email', password='$password' WHERE id=$admin_id";
    } else {
        $updateQuery = "UPDATE admins SET username='$username', email='$email' WHERE id=$admin_id";
    }

    if(mysqli_query($conn, $updateQuery)){
        $success = "Admin updated successfully!";
        $admin['username'] = $username;
        $admin['email'] = $email;
    } else {
        $error = "Error updating admin: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="icon" type="image/png" sizes="32x32" href="img/logo.png">

<!-- Apple devices -->
<link rel="apple-touch-icon" href="img/logo.png">

<!-- For older browsers -->
<link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
</head>
<body>
<div class="container mt-5">
<h3>Edit Admin</h3>

<?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<form method="POST">
    <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password (Leave blank if no change)</label>
        <input type="text" name="password" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Update Admin</button>
    <a href="superadmin-dashboard.php" class="btn btn-secondary">Back</a>
</form>
</div>
</body>
</html>
