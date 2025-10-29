<?php
session_start();
include('../db.php');

if (!isset($_SESSION['super_admin'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $super_admin_id = (int)$_POST['super_admin_id'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $plan_id = (int)$_POST['plan_id'];
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);

    // Check if username or email already exists
    $checkQuery = "SELECT * FROM admin WHERE username = '$username' OR email = '$email'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $_SESSION['error'] = "Username or email already exists!";
        header("Location: superadmin_dashboard.php?page=add_admin");
        exit();
    }

    // Insert admin
    $insertAdmin = "INSERT INTO admin (username, password, email, super_admin_id)
                    VALUES ('$username', '$password', '$email', $super_admin_id)";

    if (mysqli_query($conn, $insertAdmin)) {
        $admin_id = mysqli_insert_id($conn);

        // Get plan details
        $planQuery = mysqli_fetch_assoc(mysqli_query($conn, "SELECT price FROM pricing_plans WHERE id = $plan_id"));
        $amount = $planQuery['price'];

        // Calculate end date if not provided
        if (empty($end_date)) {
            $end_date = date('Y-m-d', strtotime($start_date . ' +1 year'));
        }

        // Insert subscription
        $insertSubscription = "INSERT INTO user_subscriptions
                              (admin_id, plan_id, start_date, end_date, status, payment_status, amount_paid)
                              VALUES ($admin_id, $plan_id, '$start_date', '$end_date', 'active', 'paid', $amount)";

        if (mysqli_query($conn, $insertSubscription)) {
            // Log activity
            $admin_name = $_SESSION['super_admin'];
            $logQuery = "INSERT INTO activity_logs (admin_id, admin_name, action, table_name, record_id)
                        VALUES ($admin_id, '$admin_name', 'Created', 'admin', $admin_id)";
            mysqli_query($conn, $logQuery);

            $_SESSION['success'] = "Admin created successfully!";
            header("Location: superadmin_dashboard.php?page=manage_admins");
        } else {
            $_SESSION['error'] = "Admin created but subscription failed: " . mysqli_error($conn);
            header("Location: superadmin_dashboard.php?page=add_admin");
        }
    } else {
        $_SESSION['error'] = "Failed to create admin: " . mysqli_error($conn);
        header("Location: superadmin_dashboard.php?page=add_admin");
    }
} else {
    header("Location: superadmin_dashboard.php?page=add_admin");
}
?>