<?php
session_start();
include('../db.php');

if(!isset($_SESSION['super_admin'])){
    header("Location: index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $super_admin_id = $_SESSION['super_admin_id'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);

    // Check if username/email is taken by another super admin
    $checkQuery = "SELECT id FROM super_admins WHERE (username = '$username' OR email = '$email') AND id != $super_admin_id";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $_SESSION['error'] = "Username or email already exists!";
        header("Location: superadmin_dashboard.php?page=profile");
        exit();
    }

    // Update super admin profile
    if(!empty($new_password)){
        $updateQuery = "UPDATE super_admins SET username='$username', email='$email', password='$new_password' WHERE id=$super_admin_id";
    } else {
        $updateQuery = "UPDATE super_admins SET username='$username', email='$email' WHERE id=$super_admin_id";
    }

    if(mysqli_query($conn, $updateQuery)){
        $_SESSION['super_admin'] = $username; // Update session with new username
        $_SESSION['success'] = "Profile updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update profile: " . mysqli_error($conn);
    }
} else {
    $_SESSION['error'] = "Invalid request method!";
}

header("Location: superadmin_dashboard.php?page=profile");
exit();
?>