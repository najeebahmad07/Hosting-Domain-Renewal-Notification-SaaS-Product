<?php
session_start();
include('../db.php');

if (!isset($_SESSION['super_admin'])) {
    header("Location: index.php");
    exit();
}

$plan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get plan details
$planQuery = "SELECT * FROM pricing_plans WHERE id = $plan_id";
$planResult = mysqli_fetch_assoc(mysqli_query($conn, $planQuery));

if (!$planResult) {
    $_SESSION['error'] = "Plan not found!";
    header("Location: superadmin_dashboard.php?page=pricing_plans");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plan_name = mysqli_real_escape_string($conn, $_POST['plan_name']);
    $plan_type = mysqli_real_escape_string($conn, $_POST['plan_type']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $billing_cycle = mysqli_real_escape_string($conn, $_POST['billing_cycle']);
    $ideal_for = mysqli_real_escape_string($conn, $_POST['ideal_for']);
    $domains_limit = mysqli_real_escape_string($conn, $_POST['domains_limit']);
    $notifications = mysqli_real_escape_string($conn, $_POST['notifications']);
    $reminder_scheduling = mysqli_real_escape_string($conn, $_POST['reminder_scheduling']);
    $dashboard_access = mysqli_real_escape_string($conn, $_POST['dashboard_access']);
    $data_import = mysqli_real_escape_string($conn, $_POST['data_import']);
    $users_limit = mysqli_real_escape_string($conn, $_POST['users_limit']);
    $analytics_reports = mysqli_real_escape_string($conn, $_POST['analytics_reports']);
    $support_type = mysqli_real_escape_string($conn, $_POST['support_type']);
    $white_label = mysqli_real_escape_string($conn, $_POST['white_label']);
    $is_popular = isset($_POST['is_popular']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $border_color = mysqli_real_escape_string($conn, $_POST['border_color']);
    $sort_order = (int)$_POST['sort_order'];

    $updateQuery = "UPDATE pricing_plans SET
                    plan_name = '$plan_name',
                    plan_type = '$plan_type',
                    price = $price,
                    billing_cycle = '$billing_cycle',
                    ideal_for = '$ideal_for',
                    domains_limit = '$domains_limit',
                    notifications = '$notifications',
                    reminder_scheduling = '$reminder_scheduling',
                    dashboard_access = '$dashboard_access',
                    data_import = '$data_import',
                    users_limit = '$users_limit',
                    analytics_reports = '$analytics_reports',
                    support_type = '$support_type',
                    white_label = '$white_label',
                    is_popular = $is_popular,
                    is_active = $is_active,
                    border_color = '$border_color',
                    sort_order = $sort_order
                    WHERE id = $plan_id";

    if (mysqli_query($conn, $updateQuery)) {
        $_SESSION['success'] = "Plan updated successfully!";
        header("Location: superadmin_dashboard.php?page=pricing_plans");
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
<title>Edit Plan - <?= htmlspecialchars($planResult['plan_name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/superadmin_dashboard.css">
</head>
<body>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <div class="modal-icon-wrapper mb-3">
          <i class="bi bi-trash3-fill"></i>
        </div>
        <p class="fw-semibold mb-3">Are you sure you want to delete this admin?</p>
        <p class="text-muted small">This will also delete all their domains and data.</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
          <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-box-arrow-right me-2"></i> Confirm Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <div class="modal-icon-wrapper mb-3">
          <i class="bi bi-power"></i>
        </div>
        <p class="mb-3 fw-semibold">Are you sure you want to log out?</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
          <a href="logout.php" class="btn btn-danger">Logout</a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'super_admin_header.php'?>


<?php include 'super_admin_sidebar.php'?>


<div class="content">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card-modern">
                <div class="card-header-modern">
                    <div class="card-title-modern">
                        <i class="bi bi-tags-fill me-2"></i> Edit Pricing Plan
                    </div>
                </div>
                <div class="card-body-modern">
                    <?php if(isset($_SESSION['error'])){ ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= $_SESSION['error'] ?>
                    </div>
                    <?php unset($_SESSION['error']); } ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Plan Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control-modern" name="plan_name"
                                       value="<?= htmlspecialchars($planResult['plan_name']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Plan Type <span class="text-danger">*</span></label>
                                <select class="form-control-modern" name="plan_type" required>
                                    <option value="basic" <?= $planResult['plan_type']=='basic'?'selected':'' ?>>Basic</option>
                                    <option value="standard" <?= $planResult['plan_type']=='standard'?'selected':'' ?>>Standard</option>
                                    <option value="premium" <?= $planResult['plan_type']=='premium'?'selected':'' ?>>Premium</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Price (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control-modern" name="price"
                                       value="<?= $planResult['price'] ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Billing Cycle</label>
                                <select class="form-control-modern" name="billing_cycle">
                                    <option value="lifetime" <?= $planResult['billing_cycle']=='lifetime'?'selected':'' ?>>Lifetime</option>
                                    <option value="month" <?= $planResult['billing_cycle']=='month'?'selected':'' ?>>Monthly</option>
                                    <option value="year" <?= $planResult['billing_cycle']=='year'?'selected':'' ?>>Yearly</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Sort Order</label>
                                <input type="number" class="form-control-modern" name="sort_order"
                                       value="<?= $planResult['sort_order'] ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ideal For</label>
                            <input type="text" class="form-control-modern" name="ideal_for"
                                   value="<?= htmlspecialchars($planResult['ideal_for']) ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Domain Limit</label>
                                <input type="text" class="form-control-modern" name="domains_limit"
                                       value="<?= htmlspecialchars($planResult['domains_limit']) ?>">
                                <small class="text-muted">e.g., "Up to 30", "Up to 1,000", "Unlimited"</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Users Limit</label>
                                <input type="text" class="form-control-modern" name="users_limit"
                                       value="<?= htmlspecialchars($planResult['users_limit']) ?>">
                                <small class="text-muted">e.g., "1 Admin", "Up to 5 Users"</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Notifications</label>
                                <input type="text" class="form-control-modern" name="notifications"
                                       value="<?= htmlspecialchars($planResult['notifications']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Support Type</label>
                                <input type="text" class="form-control-modern" name="support_type"
                                       value="<?= htmlspecialchars($planResult['support_type']) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Reminder Scheduling</label>
                                <input type="text" class="form-control-modern" name="reminder_scheduling"
                                       value="<?= htmlspecialchars($planResult['reminder_scheduling']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Dashboard Access</label>
                                <input type="text" class="form-control-modern" name="dashboard_access"
                                       value="<?= htmlspecialchars($planResult['dashboard_access']) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Data Import</label>
                                <input type="text" class="form-control-modern" name="data_import"
                                       value="<?= htmlspecialchars($planResult['data_import']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Analytics Reports</label>
                                <input type="text" class="form-control-modern" name="analytics_reports"
                                       value="<?= htmlspecialchars($planResult['analytics_reports']) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">White Label</label>
                                <select class="form-control-modern" name="white_label">
                                    <option value="No" <?= $planResult['white_label']=='No'?'selected':'' ?>>No</option>
                                    <option value="Yes" <?= $planResult['white_label']=='Yes'?'selected':'' ?>>Yes</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Border Color</label>
                                <input type="color" class="form-control-modern" name="border_color"
                                       value="<?= $planResult['border_color'] ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_popular"
                                           id="is_popular" <?= $planResult['is_popular']?'checked':'' ?>>
                                    <label class="form-check-label fw-semibold" for="is_popular">
                                        Mark as Popular
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active"
                                           id="is_active" <?= $planResult['is_active']?'checked':'' ?>>
                                    <label class="form-check-label fw-semibold" for="is_active">
                                        Active Plan
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Update Plan
                            </button>
                            <a href="superadmin_dashboard.php?page=pricing_plans" class="btn btn-secondary">
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

<script>
// Theme Toggle
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    const savedTheme = localStorage.getItem('theme') || 'light';

    function updateTheme(theme) {
        const themeIcon = themeToggle.querySelector('i');
        themeIcon.style.transform = 'rotate(360deg)';

        setTimeout(() => {
            if (theme === 'dark') {
                document.body.classList.add('theme-dark');
                themeIcon.className = 'bi bi-sun-fill';
            } else {
                document.body.classList.remove('theme-dark');
                themeIcon.className = 'bi bi-moon-stars-fill';
            }
            themeIcon.style.transform = 'rotate(0deg)';
        }, 150);
    }

    updateTheme(savedTheme);

    themeToggle.addEventListener('click', function() {
        const currentTheme = document.body.classList.contains('theme-dark') ? 'dark' : 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        updateTheme(newTheme);
        localStorage.setItem('theme', newTheme);
    });
});

// Sidebar Toggle
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.body.classList.toggle('sidebar-collapsed');
});

// Delete Admin Confirmation
document.querySelectorAll('.delete-admin-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const adminId = this.getAttribute('data-id');
        document.getElementById('confirmDeleteBtn').href = 'delete_admin.php?id=' + adminId;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
});
</script>

</body>
</html>