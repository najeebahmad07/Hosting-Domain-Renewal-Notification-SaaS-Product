<?php
session_start();
include('db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin'];

if (!isset($_GET['plan'])) {
    header("Location: upgrade.php");
    exit();
}

$plan_id = intval($_GET['plan']);
$payment_status = isset($_GET['payment']) ? $_GET['payment'] : 'paid';

// 1. Deactivate old subscriptions
$update_prev = "UPDATE user_subscriptions SET status='inactive' WHERE admin_id=?";
$stmt = $conn->prepare($update_prev);
$stmt->bind_param("i", $admin_id);
$stmt->execute();

// 2. Add new active subscription
$insert_new = "INSERT INTO user_subscriptions (admin_id, plan_id, status, start_date, payment_status) VALUES (?, ?, 'active', NOW(), ?)";
$stmt2 = $conn->prepare($insert_new);
$stmt2->bind_param("iis", $admin_id, $plan_id, $payment_status);
$stmt2->execute();

// 3. Log the upgrade activity
$record_id = $stmt2->insert_id;
$action = "Upgraded Plan to ID: $plan_id ($payment_status)";
$log_query = "INSERT INTO activity_logs (admin_name, action, table_name, record_id) VALUES (?, ?, 'user_subscriptions', ?)";
$stmt3 = $conn->prepare($log_query);
$stmt3->bind_param("ssi", $admin_name, $action, $record_id);
$stmt3->execute();

echo "<script>
alert('Your plan has been upgraded successfully!');
window.location.href='upgrade.php';
</script>";
exit();
?>
