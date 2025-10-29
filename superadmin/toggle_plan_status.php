<?php
session_start();
include('../db.php');

if (!isset($_SESSION['super_admin'])) {
    header("Location: index.php");
    exit();
}

$plan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get current status
$planQuery = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_active, plan_name FROM pricing_plans WHERE id = $plan_id"));

if ($planQuery) {
    // Toggle status (1 becomes 0, 0 becomes 1)
    $new_status = $planQuery['is_active'] == 1 ? 0 : 1;

    $updateQuery = "UPDATE pricing_plans SET is_active = $new_status WHERE id = $plan_id";

    if (mysqli_query($conn, $updateQuery)) {
        $status_text = $new_status == 1 ? 'activated' : 'deactivated';
        $_SESSION['success'] = "Plan '" . htmlspecialchars($planQuery['plan_name']) . "' has been $status_text successfully!";
    } else {
        $_SESSION['error'] = "Failed to update plan status: " . mysqli_error($conn);
    }
} else {
    $_SESSION['error'] = "Plan not found!";
}

header("Location: superadmin_dashboard.php?page=pricing_plans");
exit();
?>

