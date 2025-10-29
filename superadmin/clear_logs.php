<?php
session_start();
require_once('../db.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header('Location: ../superadmin');
    exit();
}

// Get admin details
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];

// Clear all activity logs
$query = "DELETE FROM activity_logs";
$result = mysqli_query($conn, $query);

if($result) {
    // Log this action
    $log_query = "INSERT INTO activity_logs (admin_id, admin_name, action, table_name, record_id, created_at)
                  VALUES ('$admin_id', '$admin_name', 'Cleared', 'activity_logs', 'all', NOW())";
    mysqli_query($conn, $log_query);

    $_SESSION['success_message'] = 'Activity logs cleared successfully!';
} else {
    $_SESSION['error_message'] = 'Failed to clear activity logs: ' . mysqli_error($conn);
}

// Redirect back to activity logs page
header('Location: ../superadmin_dashboard.php?page=activity_logs');
exit();
?>