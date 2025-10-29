<?php
session_start();
include('db.php');

if (!isset($_GET['plan']) || !isset($_GET['status'])) {
    header("Location: upgrade.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$plan_id = intval($_GET['plan']);
$status = $_GET['status'];

// If payment success
if ($status === 'success') {
    // Deactivate previous active subscriptions
    $conn->query("UPDATE user_subscriptions SET status='inactive' WHERE admin_id='$admin_id'");

    // Add new subscription
    $query = "INSERT INTO user_subscriptions (admin_id, plan_id, status, start_date) VALUES ('$admin_id', '$plan_id', 'active', NOW())";
    if ($conn->query($query)) {
        echo "<script>
            alert('Upgrade successful! Your plan has been updated.');
            window.location.href='upgrade.php';
        </script>";
    } else {
        echo "Error updating plan: " . $conn->error;
    }
} else {
    echo "<script>alert('Payment failed or cancelled. Please try again.'); window.location.href='upgrade.php';</script>";
}
?>
