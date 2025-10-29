<?php
session_start();
include('../db.php');

if (!isset($_SESSION['super_admin'])) {
    header("Location: index.php");
    exit();
}

$admin_id = isset($_GET['admin_id']) ? (int)$_GET['admin_id'] : 0;
$super_admin_id = $_SESSION['super_admin_id'];

// Get admin details
$adminQuery = "SELECT a.*, us.plan_id, us.status, us.start_date, us.end_date, pp.plan_name
               FROM admin a
               LEFT JOIN user_subscriptions us ON a.id = us.admin_id
               LEFT JOIN pricing_plans pp ON us.plan_id = pp.id
               WHERE a.id = $admin_id AND a.super_admin_id = $super_admin_id";
$adminResult = mysqli_fetch_assoc(mysqli_query($conn, $adminQuery));

if (!$adminResult) {
    $_SESSION['error'] = "Admin not found!";
    header("Location: superadmin_dashboard.php?page=manage_admins");
    exit();
}

// Get all plans
$plansResult = mysqli_query($conn, "SELECT * FROM pricing_plans WHERE is_active = 1 ORDER BY sort_order ASC");

// Get domain count for this admin
$domainCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hosting_domain WHERE admin_id = $admin_id"));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_plan_id = (int)$_POST['plan_id'];
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $payment_status = mysqli_real_escape_string($conn, $_POST['payment_status']);

    // Get new plan price
    $newPlan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT price FROM pricing_plans WHERE id = $new_plan_id"));
    $amount = $newPlan['price'];

    // Update or insert subscription
    $checkSub = mysqli_query($conn, "SELECT id FROM user_subscriptions WHERE admin_id = $admin_id");

    if (mysqli_num_rows($checkSub) > 0) {
        // Update existing subscription
        $updateQuery = "UPDATE user_subscriptions
                       SET plan_id = $new_plan_id,
                           start_date = '$start_date',
                           end_date = '$end_date',
                           status = '$status',
                           payment_status = '$payment_status',
                           amount_paid = $amount
                       WHERE admin_id = $admin_id";
    } else {
        // Insert new subscription
        $updateQuery = "INSERT INTO user_subscriptions
                       (admin_id, plan_id, start_date, end_date, status, payment_status, amount_paid)
                       VALUES ($admin_id, $new_plan_id, '$start_date', '$end_date', '$status', '$payment_status', $amount)";
    }

    if (mysqli_query($conn, $updateQuery)) {
        // Log activity
        $admin_name = $_SESSION['super_admin'];
        $logQuery = "INSERT INTO activity_logs (admin_id, admin_name, action, table_name, record_id)
                    VALUES ($admin_id, '$admin_name', 'Updated Plan', 'user_subscriptions', $admin_id)";
        mysqli_query($conn, $logQuery);

        $_SESSION['success'] = "Plan updated successfully!";
        header("Location: superadmin_dashboard.php?page=manage_admins");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update plan: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Plan - <?= htmlspecialchars($adminResult['username']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/superadmin_dashboard.css">
</head>
<body>

<?php include 'super_admin_header.php'?>

<?php include 'super_admin_sidebar.php'?>

<div class="content">

<!-- Success/Error Messages -->
<?php if(isset($_SESSION['success'])){ ?>
<div class="alert alert-success alert-dismissible fade show">
  <i class="bi bi-check-circle me-2"></i>
  <?= $_SESSION['success'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['success']); } ?>

<?php if(isset($_SESSION['error'])){ ?>
<div class="alert alert-danger alert-dismissible fade show">
  <i class="bi bi-exclamation-triangle me-2"></i>
  <?= $_SESSION['error'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['error']); } ?>


<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-header-modern">
                    <div class="card-title-modern">
                        <i class="bi bi-card-checklist me-2"></i>
                        Manage Plan for <?= htmlspecialchars($adminResult['username']) ?>
                    </div>
                </div>
                <div class="card-body-modern">
                    <!-- Current Plan Info -->
                    <div class="alert alert-info mb-4">
                        <h6 class="mb-2"><i class="bi bi-info-circle me-2"></i> Current Plan Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Plan:</strong> <?= $adminResult['plan_name'] ?? 'Basic (Free)' ?><br>
                                <strong>Status:</strong> <?= ucfirst($adminResult['status'] ?? 'active') ?><br>
                                <strong>Domains:</strong> <?= $domainCount['total'] ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Start:</strong> <?= $adminResult['start_date'] ? date('d M Y', strtotime($adminResult['start_date'])) : 'N/A' ?><br>
                                <strong>End:</strong> <?= $adminResult['end_date'] ? date('d M Y', strtotime($adminResult['end_date'])) : 'N/A' ?>
                            </div>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Select New Plan</label>
                            <div class="plan-selection-grid">
                                <?php while($plan = mysqli_fetch_assoc($plansResult)){ ?>
                                <div class="plan-card-select">
                                    <input type="radio" name="plan_id" value="<?= $plan['id'] ?>"
                                           id="plan<?= $plan['id'] ?>"
                                           <?= $plan['id'] == $adminResult['plan_id'] ? 'checked' : '' ?> required>
                                    <label for="plan<?= $plan['id'] ?>">
                                        <div class="plan-header">
                                            <h5><?= htmlspecialchars($plan['plan_name']) ?></h5>
                                            <div class="plan-price">₹<?= number_format($plan['price'], 0) ?></div>
                                        </div>
                                        <div class="plan-features">
                                            <div class="plan-feature">
                                                <i class="bi bi-check-circle-fill"></i>
                                                <?= htmlspecialchars($plan['domains_limit']) ?>
                                            </div>
                                            <div class="plan-feature">
                                                <i class="bi bi-check-circle-fill"></i>
                                                <?= htmlspecialchars($plan['users_limit']) ?>
                                            </div>
                                            <div class="plan-feature">
                                                <i class="bi bi-check-circle-fill"></i>
                                                <?= htmlspecialchars($plan['support_type']) ?>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Start Date</label>
                                <input type="date" class="form-control-modern" name="start_date"
                                       value="<?= $adminResult['start_date'] ?? date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">End Date</label>
                                <input type="date" class="form-control-modern" name="end_date"
                                       value="<?= $adminResult['end_date'] ?? date('Y-m-d', strtotime('+1 year')) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Subscription Status</label>
                                <select class="form-control-modern" name="status" required>
                                    <option value="active" <?= ($adminResult['status'] ?? 'active') == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="expired" <?= ($adminResult['status'] ?? '') == 'expired' ? 'selected' : '' ?>>Expired</option>
                                    <option value="suspended" <?= ($adminResult['status'] ?? '') == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Payment Status</label>
                                <select class="form-control-modern" name="payment_status" required>
                                    <option value="paid">Paid</option>
                                    <option value="pending">Pending</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> Plan changes will take effect immediately. Domain limits will be enforced based on the selected plan.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Update Plan
                            </button>
                            <a href="superadmin_dashboard.php?page=manage_admins" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>