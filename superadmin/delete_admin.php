<?php
session_start();
include('../db.php');

if (!isset($_SESSION['super_admin'])) {
    header("Location: index.php");
    exit();
}

$admin_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$super_admin_id = $_SESSION['super_admin_id'];

// Verify admin belongs to this super admin
$checkQuery = "SELECT id FROM admin WHERE id = $admin_id AND super_admin_id = $super_admin_id";
$checkResult = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($checkResult) > 0) {
    // Delete admin (cascade will handle related records)
    $deleteQuery = "DELETE FROM admin WHERE id = $admin_id";

    if (mysqli_query($conn, $deleteQuery)) {
        // Log activity
        $admin_name = $_SESSION['super_admin'];
        $logQuery = "INSERT INTO activity_logs (admin_id, admin_name, action, table_name, record_id)
                    VALUES (0, '$admin_name', 'Deleted Admin', 'admin', $admin_id)";
        mysqli_query($conn, $logQuery);

        $_SESSION['success'] = "Admin deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete admin: " . mysqli_error($conn);
    }
} else {
    $_SESSION['error'] = "Admin not found or unauthorized!";
}

header("Location: superadmin_dashboard.php?page=manage_admins");
exit();
?>