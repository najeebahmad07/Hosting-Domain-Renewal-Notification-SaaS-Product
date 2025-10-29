<?php
session_start();
include('db.php');

// Protect access
if (!isset($_SESSION['super_admin'])) {
    header("Location: index.php");
    exit();
}

$super_admin_id = (int)$_SESSION['super_admin_id'];
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// ------------------- DASHBOARD -------------------
if ($page == 'dashboard') {
    $totalAdminsResult = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) as total FROM admin WHERE super_admin_id = $super_admin_id
    "));

    $totalDomainsResult = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) as total FROM hosting_domain hd
        INNER JOIN admin a ON hd.admin_id = a.id
        WHERE a.super_admin_id = $super_admin_id
    "));

    $expiringSoonResult = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) as expiring FROM hosting_domain hd
        INNER JOIN admin a ON hd.admin_id = a.id
        WHERE a.super_admin_id = $super_admin_id
        AND (hd.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        OR hd.hosting_expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY))
    "));

    $activeSubscriptionsResult = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT COUNT(*) as active FROM user_subscriptions us
        INNER JOIN admin a ON us.admin_id = a.id
        WHERE a.super_admin_id = $super_admin_id AND us.status = 'active'
    "));

    $revenueResult = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT SUM(us.amount_paid) as total_revenue FROM user_subscriptions us
        INNER JOIN admin a ON us.admin_id = a.id
        WHERE a.super_admin_id = $super_admin_id
    "));

    $activityLogs = [];
    $activityResult = mysqli_query($conn, "
        SELECT al.*, a.username as admin_username
        FROM activity_logs al
        LEFT JOIN admin a ON al.admin_id = a.id
        WHERE a.super_admin_id = $super_admin_id
        ORDER BY al.created_at DESC LIMIT 15
    ");
    while ($log = mysqli_fetch_assoc($activityResult)) {
        $activityLogs[] = $log;
    }

    $planDistribution = [];
    $planQuery = mysqli_query($conn, "
        SELECT pp.plan_name, COUNT(*) as count
        FROM user_subscriptions us
        INNER JOIN pricing_plans pp ON us.plan_id = pp.id
        INNER JOIN admin a ON us.admin_id = a.id
        WHERE a.super_admin_id = $super_admin_id AND us.status = 'active'
        GROUP BY pp.plan_name
    ");
    while ($row = mysqli_fetch_assoc($planQuery)) {
        $planDistribution[] = $row;
    }
}

// ------------------- MANAGE ADMINS -------------------
if ($page == 'manage_admins') {
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
    $filter = isset($_GET['filter']) ? $_GET['filter'] : '';
    $where = "WHERE a.super_admin_id = $super_admin_id";

    if (!empty($search)) {
        $where .= " AND (a.username LIKE '%$search%' OR a.email LIKE '%$search%')";
    }

    if (!empty($filter)) {
        if ($filter == 'active') $where .= " AND us.status = 'active'";
        if ($filter == 'expired') $where .= " AND (us.status = 'expired' OR us.end_date < CURDATE())";
    }

    $query = "
        SELECT a.*, us.plan_id, us.status as subscription_status, us.end_date,
               pp.plan_name, COUNT(hd.id) as domain_count
        FROM admin a
        LEFT JOIN user_subscriptions us ON a.id = us.admin_id
        LEFT JOIN pricing_plans pp ON us.plan_id = pp.id
        LEFT JOIN hosting_domain hd ON a.id = hd.admin_id
        $where
        GROUP BY a.id
        ORDER BY a.id DESC
    ";
    $adminsResult = mysqli_query($conn, $query);
}

// ------------------- VIEW ADMIN DETAILS -------------------
if ($page == 'view_admin_details' && isset($_GET['admin_id'])) {
    $admin_id = (int)$_GET['admin_id'];

    $adminInfo = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT a.*, us.plan_id, us.status, us.start_date, us.end_date,
               us.payment_status, us.amount_paid, pp.plan_name
        FROM admin a
        LEFT JOIN user_subscriptions us ON a.id = us.admin_id
        LEFT JOIN pricing_plans pp ON us.plan_id = pp.id
        WHERE a.id = $admin_id AND a.super_admin_id = $super_admin_id
    "));

    if ($adminInfo) {
        $domainsResult = mysqli_query($conn, "
            SELECT * FROM hosting_domain WHERE admin_id = $admin_id ORDER BY id DESC
        ");

        $adminActivityLogs = [];
        $adminLogsResult = mysqli_query($conn, "
            SELECT * FROM activity_logs WHERE admin_id = $admin_id ORDER BY created_at DESC LIMIT 20
        ");
        while ($log = mysqli_fetch_assoc($adminLogsResult)) {
            $adminActivityLogs[] = $log;
        }
    }
}

// ------------------- SUBSCRIPTIONS -------------------
if ($page == 'subscriptions') {
    $query = "
        SELECT us.*, a.username, a.email, pp.plan_name, pp.price
        FROM user_subscriptions us
        INNER JOIN admin a ON us.admin_id = a.id
        INNER JOIN pricing_plans pp ON us.plan_id = pp.id
        WHERE a.super_admin_id = $super_admin_id
        ORDER BY us.created_at DESC
    ";
    $subscriptionsResult = mysqli_query($conn, $query);
}

// ------------------- PRICING PLANS -------------------
if ($page == 'pricing_plans') {
    $plansResult = mysqli_query($conn, "SELECT * FROM pricing_plans ORDER BY sort_order ASC");
}

// ------------------- GLOBAL NOTIFICATIONS -------------------
if ($page == 'global_notifications') {
    $expiringAdminsResult = mysqli_query($conn, "
        SELECT DISTINCT a.id, a.username, a.email,
               COUNT(DISTINCT CASE WHEN hd.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN hd.id END) as expiring_domains,
               COUNT(DISTINCT CASE WHEN hd.hosting_expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN hd.id END) as expiring_hosting
        FROM admin a
        INNER JOIN hosting_domain hd ON a.id = hd.admin_id
        WHERE a.super_admin_id = $super_admin_id
        AND (hd.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             OR hd.hosting_expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY))
        GROUP BY a.id
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UltraServe | SuperAdmin Dashboard</title>

<link href="css/superadmin_dashboard.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/png" sizes="32x32" href="../img/logo.png">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

<!-- Header -->

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

<!-- Dashboard -->
<?php if($page == 'dashboard'){ ?>
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-modern stat-card-primary">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-label">Total Admins</div>
                <div class="stat-value"><?= $totalAdminsResult['total'] ?></div>
                <div class="stat-subtitle">Registered users</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card-modern stat-card-success">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="bi bi-globe"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-label">Total Domains</div>
                <div class="stat-value"><?= $totalDomainsResult['total'] ?></div>
                <div class="stat-subtitle">Across all admins</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card-modern stat-card-danger">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <span class="stat-badge"><?= $expiringSoonResult['expiring'] ?></span>
            </div>
            <div class="stat-card-body">
                <div class="stat-label">Expiring Soon</div>
                <div class="stat-value"><?= $expiringSoonResult['expiring'] ?></div>
                <div class="stat-subtitle">Next 30 days</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card-modern stat-card-warning">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">₹<?= number_format($revenueResult['total_revenue'] ?? 0, 2) ?></div>
                <div class="stat-subtitle">All time</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row g-4 mb-4">
  <div class="col-lg-8">
    <div class="card-modern">
      <div class="card-header-modern">
        <div class="card-title-modern">Plan Distribution</div>
      </div>
      <div class="card-body-modern">
        <canvas id="planDistributionChart" style="max-height: 300px;"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card-modern">
      <div class="card-header-modern">
        <div class="card-title-modern">Quick Stats</div>
      </div>
      <div class="card-body-modern">
        <div class="quick-stat-item">
          <div class="quick-stat-label">Active Subscriptions</div>
          <div class="quick-stat-value"><?= $activeSubscriptionsResult['active'] ?></div>
        </div>
        <div class="quick-stat-item">
          <div class="quick-stat-label">Average Revenue/Admin</div>
          <div class="quick-stat-value">₹<?= $totalAdminsResult['total'] > 0 ? number_format(($revenueResult['total_revenue'] ?? 0) / $totalAdminsResult['total'], 2) : '0.00' ?></div>
        </div>
        <div class="quick-stat-item">
          <div class="quick-stat-label">Domains/Admin</div>
          <div class="quick-stat-value"><?= $totalAdminsResult['total'] > 0 ? round($totalDomainsResult['total'] / $totalAdminsResult['total'], 1) : 0 ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Activity Logs -->
<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">
        <i class="bi bi-clock-history me-2"></i> Recent Activity
    </div>
  </div>
  <div class="table-responsive-modern">
    <table class="table-modern">
      <thead>
        <tr>
            <th>Admin</th>
            <th>Action</th>
            <th>Table</th>
            <th>Record ID</th>
            <th>Time</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($activityLogs as $log){ ?>
      <tr>
        <td><?= htmlspecialchars($log['admin_name']) ?></td>
        <td><span class="badge-modern badge-primary"><?= htmlspecialchars($log['action']) ?></span></td>
        <td><?= htmlspecialchars($log['table_name']) ?></td>
        <td><?= $log['record_id'] ?></td>
        <td><?= $log['created_at'] ?></td>
      </tr>
      <?php } ?>
      <?php if(count($activityLogs)==0){ ?>
      <tr><td colspan="5" class="text-center">No activity found</td></tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<script>
// Plan Distribution Chart
var ctx = document.getElementById('planDistributionChart').getContext('2d');
var planChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?php
            foreach($planDistribution as $pd) {
                echo "'" . $pd['plan_name'] . "',";
            }
        ?>],
        datasets: [{
            label: 'Number of Admins',
            data: [<?php
                foreach($planDistribution as $pd) {
                    echo $pd['count'] . ",";
                }
            ?>],
            backgroundColor: ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b'],
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
<?php } ?>

<!-- Manage Admins -->
<?php if($page == 'manage_admins'){ ?>
<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">
        <i class="bi bi-people-fill me-2"></i> Manage Admins
    </div>
    <a href="superadmin_dashboard.php?page=add_admin" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add New Admin
    </a>
  </div>

  <!-- Search and Filter -->
  <div class="search-section-modern">
    <form method="GET" action="" class="search-form-modern">
      <input type="hidden" name="page" value="manage_admins">
      <div class="search-filter-wrapper">
        <div class="search-wrapper">
          <div class="search-input-group">
            <i class="bi bi-search search-icon"></i>
            <input type="text" name="search" class="search-input-modern"
                   placeholder="Search by username or email..." value="<?= htmlspecialchars($search) ?>">
            <?php if(!empty($search)): ?>
            <a href="?page=manage_admins" class="clear-search" title="Clear search">
              <i class="bi bi-x-circle"></i>
            </a>
            <?php endif; ?>
          </div>
        </div>
        <div class="filter-wrapper">
          <select name="filter" class="filter-select-modern" onchange="this.form.submit()">
            <option value="">All Plans</option>
            <option value="basic" <?= $filter=='basic'?'selected':'' ?>>Basic (Free)</option>
            <option value="standard" <?= $filter=='standard'?'selected':'' ?>>Standard</option>
            <option value="premium" <?= $filter=='premium'?'selected':'' ?>>Premium</option>
            <option value="active" <?= $filter=='active'?'selected':'' ?>>Active</option>
            <option value="expired" <?= $filter=='expired'?'selected':'' ?>>Expired</option>
          </select>
        </div>
        <button type="submit" class="btn-search-modern">
          <i class="bi bi-search me-1"></i> Search
        </button>
      </div>
    </form>
  </div>

  <div class="table-responsive-modern">
    <table class="table-modern">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Plan</th>
          <th>Domains</th>
          <th>Status</th>
          <th>Expiry</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php while($admin = mysqli_fetch_assoc($adminsResult)){ ?>
      <tr>
        <td><?= $admin['id'] ?></td>
        <td><?= htmlspecialchars($admin['username']) ?></td>
        <td><?= htmlspecialchars($admin['email']) ?></td>
        <td>
          <?php
          $plan_badge = 'badge-secondary';
          if($admin['plan_name'] == 'Standard') $plan_badge = 'badge-primary';
          if($admin['plan_name'] == 'Premium') $plan_badge = 'badge-warning';
          ?>
          <span class="badge-modern <?= $plan_badge ?>"><?= $admin['plan_name'] ?? 'Basic' ?></span>
        </td>
        <td><?= $admin['domain_count'] ?></td>
        <td>
          <?php
          $status_badge = 'badge-success';
          if($admin['subscription_status'] == 'expired') $status_badge = 'badge-danger';
          ?>
          <span class="badge-modern <?= $status_badge ?>"><?= ucfirst($admin['subscription_status'] ?? 'active') ?></span>
        </td>
        <td><?= $admin['end_date'] ? date('d M Y', strtotime($admin['end_date'])) : 'N/A' ?></td>
        <td>
          <div class="action-buttons">
            <a href="superadmin_dashboard.php?page=view_admin_details&admin_id=<?= $admin['id'] ?>"
               class="btn-action btn-action-view" title="View Details">
              <i class="bi bi-eye-fill"></i>
            </a>
            <a href="edit_admin.php?id=<?= $admin['id'] ?>"
               class="btn-action btn-action-edit" title="Edit">
              <i class="bi bi-pencil-fill"></i>
            </a>
            <a href="manage_plan.php?admin_id=<?= $admin['id'] ?>"
               class="btn-action btn-action-warning" title="Manage Plan">
              <i class="bi bi-card-checklist"></i>
            </a>
            <a href="#" data-id="<?= $admin['id'] ?>"
               class="btn-action btn-action-delete delete-admin-btn" title="Delete">
              <i class="bi bi-trash-fill"></i>
            </a>
          </div>
        </td>
      </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php } ?>

<!-- View Admin Details -->
<?php if($page == 'view_admin_details' && isset($adminInfo)){ ?>
<div class="row g-4 mb-4">
  <div class="col-lg-4">
    <div class="card-modern">
      <div class="card-header-modern">
        <div class="card-title-modern">Admin Information</div>
      </div>
      <div class="card-body-modern">
        <div class="admin-detail-item">
          <div class="detail-label">Email</div>
          <div class="detail-value"><?= htmlspecialchars($adminInfo['email']) ?></div>
        </div>
        <div class="admin-detail-item">
          <div class="detail-label">Current Plan</div>
          <div class="detail-value">
            <span class="badge-modern badge-primary"><?= $adminInfo['plan_name'] ?? 'Basic' ?></span>
          </div>
        </div>
        <div class="admin-detail-item">
          <div class="detail-label">Status</div>
          <div class="detail-value">
            <span class="badge-modern badge-success"><?= ucfirst($adminInfo['status'] ?? 'active') ?></span>
          </div>
        </div>
        <div class="admin-detail-item">
          <div class="detail-label">Subscription Period</div>
          <div class="detail-value">
            <?= $adminInfo['start_date'] ? date('d M Y', strtotime($adminInfo['start_date'])) : 'N/A' ?> -
            <?= $adminInfo['end_date'] ? date('d M Y', strtotime($adminInfo['end_date'])) : 'N/A' ?>
          </div>
        </div>
        <div class="admin-detail-item">
          <div class="detail-label">Amount Paid</div>
          <div class="detail-value">₹<?= number_format($adminInfo['amount_paid'] ?? 0, 2) ?></div>
        </div>
        <div class="d-flex gap-2 mt-3">
          <a href="edit_admin.php?id=<?= $adminInfo['id'] ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
          </a>
          <a href="manage_plan.php?admin_id=<?= $adminInfo['id'] ?>" class="btn btn-warning btn-sm">
            <i class="bi bi-card-checklist me-1"></i> Manage Plan
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card-modern mb-4">
      <div class="card-header-modern">
        <div class="card-title-modern">Domains (<?= mysqli_num_rows($domainsResult) ?>)</div>
      </div>
      <div class="table-responsive-modern">
        <table class="table-modern">
          <thead>
            <tr>
              <th>Domain Name</th>
              <th>Company</th>
              <th>Expiry Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php while($domain = mysqli_fetch_assoc($domainsResult)){
            $today = new DateTime();
            $expiry = new DateTime($domain['expiry_date']);
            $interval = $today->diff($expiry);
            $daysLeft = (int)$interval->format('%r%a');

            $status_class = 'text-success';
            if($daysLeft < 0) $status_class = 'text-danger';
            elseif($daysLeft <= 30) $status_class = 'text-warning';
          ?>
          <tr>
            <td><?= htmlspecialchars($domain['domain_name']) ?></td>
            <td><?= htmlspecialchars($domain['company_name']) ?></td>
            <td><?= date('d M Y', strtotime($domain['expiry_date'])) ?></td>
            <td class="<?= $status_class ?> fw-bold">
              <?= $daysLeft < 0 ? 'Expired' : $daysLeft . ' days left' ?>
            </td>
          </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-modern">
      <div class="card-header-modern">
        <div class="card-title-modern">Recent Activity</div>
      </div>
      <div class="table-responsive-modern">
        <table class="table-modern">
          <thead>
            <tr>
              <th>Action</th>
              <th>Table</th>
              <th>Record ID</th>
              <th>Time</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($adminActivityLogs as $log){ ?>
          <tr>
            <td><span class="badge-modern badge-primary"><?= htmlspecialchars($log['action']) ?></span></td>
            <td><?= htmlspecialchars($log['table_name']) ?></td>
            <td><?= $log['record_id'] ?></td>
            <td><?= $log['created_at'] ?></td>
          </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php } ?>

<!-- Add New Admin -->
<?php if($page == 'add_admin'){ ?>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card-modern">
      <div class="card-header-modern">
        <div class="card-title-modern">
          <i class="bi bi-person-plus-fill me-2"></i> Add New Admin
        </div>
      </div>
      <div class="card-body-modern">
        <form method="POST" action="process_add_admin.php">
          <input type="hidden" name="super_admin_id" value="<?= $super_admin_id ?>">

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
              <input type="text" class="form-control-modern" name="username" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control-modern" name="email" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control-modern" name="password" required>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold">Select Plan <span class="text-danger">*</span></label>
            <div class="plan-selection-grid">
              <?php
              $plans = mysqli_query($conn, "SELECT * FROM pricing_plans ORDER BY sort_order ASC");
              while($plan = mysqli_fetch_assoc($plans)){
              ?>
              <div class="plan-card-select">
                <input type="radio" name="plan_id" value="<?= $plan['id'] ?>" id="plan<?= $plan['id'] ?>" required>
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
              <label class="form-label fw-semibold">Subscription Start Date</label>
              <input type="date" class="form-control-modern" name="start_date" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Subscription End Date</label>
              <input type="date" class="form-control-modern" name="end_date">
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-1"></i> Create Admin
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
<?php } ?>

<!-- Subscriptions -->
<?php if($page == 'subscriptions'){ ?>
<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">
      <i class="bi bi-credit-card-fill me-2"></i> Subscription Management
    </div>
  </div>
  <div class="table-responsive-modern">
    <table class="table-modern">
      <thead>
        <tr>
          <th>ID</th>
          <th>Admin</th>
          <th>Email</th>
          <th>Plan</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Status</th>
          <th>Amount</th>
          <th>Payment</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php while($sub = mysqli_fetch_assoc($subscriptionsResult)){ ?>
      <tr>
        <td><?= $sub['id'] ?></td>
        <td><?= htmlspecialchars($sub['username']) ?></td>
        <td><?= htmlspecialchars($sub['email']) ?></td>
        <td><span class="badge-modern badge-primary"><?= htmlspecialchars($sub['plan_name']) ?></span></td>
        <td><?= date('d M Y', strtotime($sub['start_date'])) ?></td>
        <td><?= date('d M Y', strtotime($sub['end_date'])) ?></td>
        <td>
          <?php
          $status_badge = $sub['status'] == 'active' ? 'badge-success' : 'badge-danger';
          ?>
          <span class="badge-modern <?= $status_badge ?>"><?= ucfirst($sub['status']) ?></span>
        </td>
        <td>₹<?= number_format($sub['amount_paid'], 2) ?></td>
        <td>
          <?php
          $payment_badge = $sub['payment_status'] == 'paid' ? 'badge-success' : 'badge-warning';
          ?>
          <span class="badge-modern <?= $payment_badge ?>"><?= ucfirst($sub['payment_status']) ?></span>
        </td>
        <td>
          <div class="action-buttons">
            <a href="manage_plan.php?admin_id=<?= $sub['admin_id'] ?>"
               class="btn-action btn-action-edit" title="Manage">
              <i class="bi bi-pencil-fill"></i>
            </a>
          </div>
        </td>
      </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php } ?>

<!-- Pricing Plans -->
<?php if($page == 'pricing_plans'){ ?>
<div class="card-modern mb-4">
  <div class="card-header-modern">
    <div class="card-title-modern">
      <i class="bi bi-tags-fill me-2"></i> Pricing Plans Management
    </div>
    <a href="add_plan.php" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-circle me-1"></i> Add New Plan
    </a>
  </div>
  <div class="table-responsive-modern">
    <table class="table-modern">
      <thead>
        <tr>
          <th>Plan Name</th>
          <th>Type</th>
          <th>Price</th>
          <th>Billing</th>
          <th>Domain Limit</th>
          <th>Users Limit</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php while($plan = mysqli_fetch_assoc($plansResult)){ ?>
      <tr>
        <td>
          <strong><?= htmlspecialchars($plan['plan_name']) ?></strong>
          <?php if($plan['is_popular']){ ?>
          <span class="badge-modern badge-warning ms-2">Popular</span>
          <?php } ?>
        </td>
        <td><span class="badge-modern badge-info"><?= ucfirst($plan['plan_type']) ?></span></td>
        <td>₹<?= number_format($plan['price'], 2) ?></td>
        <td><?= ucfirst($plan['billing_cycle']) ?></td>
        <td><?= htmlspecialchars($plan['domains_limit']) ?></td>
        <td><?= htmlspecialchars($plan['users_limit']) ?></td>
        <td>
          <?php
          $active_badge = $plan['is_active'] ? 'badge-success' : 'badge-danger';
          ?>
          <span class="badge-modern <?= $active_badge ?>">
            <?= $plan['is_active'] ? 'Active' : 'Inactive' ?>
          </span>
        </td>
        <td>
          <div class="action-buttons">
            <a href="edit_plan.php?id=<?= $plan['id'] ?>"
               class="btn-action btn-action-edit" title="Edit">
              <i class="bi bi-pencil-fill"></i>
            </a>
            <a href="toggle_plan_status.php?id=<?= $plan['id'] ?>"
               class="btn-action btn-action-warning" title="Toggle Status">
              <i class="bi bi-toggle-on"></i>
            </a>
          </div>
        </td>
      </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<div class="row g-4">
  <div class="col-md-4">
    <div class="info-card-modern">
      <div class="info-icon bg-primary">
        <i class="bi bi-info-circle-fill"></i>
      </div>
      <h5>Plan Limits</h5>
      <p>Basic: 30 domains | Standard: 1,000 domains | Premium: Unlimited</p>
    </div>
  </div>
  <div class="col-md-4">
    <div class="info-card-modern">
      <div class="info-icon bg-success">
        <i class="bi bi-check-circle-fill"></i>
      </div>
      <h5>Auto-enforcement</h5>
      <p>Domain limits are automatically enforced based on admin's plan</p>
    </div>
  </div>
  <div class="col-md-4">
    <div class="info-card-modern">
      <div class="info-icon bg-warning">
        <i class="bi bi-shield-check"></i>
      </div>
      <h5>Flexible Management</h5>
      <p>Upgrade/downgrade plans anytime from Manage Admins section</p>
    </div>
  </div>
</div>
<?php } ?>

<!-- Global Notifications -->
<?php if($page == 'global_notifications'){ ?>
<div class="card-modern mb-4">
  <div class="card-header-modern">
    <div class="card-title-modern">
      <i class="bi bi-bell-fill me-2"></i> Send Global Notifications
    </div>
  </div>
  <div class="card-body-modern">
    <form method="POST" action="send_bulk_notifications.php">
      <div class="mb-3">
        <label class="form-label fw-semibold">Notification Type</label>
        <select class="form-control-modern" name="notification_type">
          <option value="expiry_reminder">Domain Expiry Reminder</option>
          <option value="subscription_reminder">Subscription Reminder</option>
          <option value="general">General Announcement</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Recipients</label>
        <select class="form-control-modern" name="recipients">
          <option value="all">All Admins</option>
          <option value="expiring">Admins with Expiring Domains</option>
          <option value="basic">Basic Plan Users</option>
          <option value="standard">Standard Plan Users</option>
          <option value="premium">Premium Plan Users</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Subject</label>
        <input type="text" class="form-control-modern" name="subject" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Message</label>
        <textarea class="form-control-modern" name="message" rows="5" required></textarea>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="bi bi-send-fill me-1"></i> Send Notifications
      </button>
    </form>
  </div>
</div>

<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">Admins with Expiring Domains (Next 30 Days)</div>
  </div>
  <div class="table-responsive-modern">
    <table class="table-modern">
      <thead>
        <tr>
          <th>Admin</th>
          <th>Email</th>
          <th>Expiring Domains</th>
          <th>Expiring Hosting</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php while($admin = mysqli_fetch_assoc($expiringAdminsResult)){ ?>
      <tr>
        <td><?= htmlspecialchars($admin['username']) ?></td>
        <td><?= htmlspecialchars($admin['email']) ?></td>
        <td><span class="badge-modern badge-danger"><?= $admin['expiring_domains'] ?></span></td>
        <td><span class="badge-modern badge-warning"><?= $admin['expiring_hosting'] ?></span></td>
        <td>
          <a href="send_notification.php?admin_id=<?= $admin['id'] ?>&type=expiry"
             class="btn btn-sm btn-primary">
            <i class="bi bi-envelope-fill me-1"></i> Send Alert
          </a>
        </td>
      </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php } ?>

<!-- AI Insights -->
<?php if($page == 'ai_insights'){ ?>
<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="ai-insight-card">
      <div class="ai-icon">
        <i class="bi bi-lightbulb-fill"></i>
      </div>
      <h5>Smart Predictions</h5>
      <p>AI-powered domain expiry predictions and renewal likelihood analysis</p>
      <button class="btn btn-sm btn-primary mt-2">
        <i class="bi bi-robot me-1"></i> Generate Insights
      </button>
    </div>
  </div>
  <div class="col-md-4">
    <div class="ai-insight-card">
      <div class="ai-icon">
        <i class="bi bi-graph-up-arrow"></i>
      </div>
      <h5>Revenue Forecasting</h5>
      <p>Predict future revenue based on subscription trends and patterns</p>
      <button class="btn btn-sm btn-success mt-2">
        <i class="bi bi-calculator me-1"></i> View Forecast
      </button>
    </div>
  </div>
  <div class="col-md-4">
    <div class="ai-insight-card">
      <div class="ai-icon">
        <i class="bi bi-person-badge"></i>
      </div>
      <h5>Admin Behavior</h5>
      <p>Analyze admin activity patterns and engagement metrics</p>
      <button class="btn btn-sm btn-warning mt-2">
        <i class="bi bi-bar-chart me-1"></i> Analyze
      </button>
    </div>
  </div>
</div>

<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">
      <i class="bi bi-robot me-2"></i> AI-Powered Filters & Search
    </div>
  </div>
  <div class="card-body-modern">
    <div class="ai-search-box">
      <i class="bi bi-stars"></i>
      <input type="text" class="ai-search-input"
             placeholder="Ask AI: 'Show admins with most expiring domains' or 'Find inactive users'...">
      <button class="btn btn-primary">
        <i class="bi bi-send-fill"></i> Ask AI
      </button>
    </div>

    <div class="ai-suggestions mt-4">
      <h6 class="mb-3">Quick AI Queries:</h6>
      <div class="ai-suggestion-chips">
        <span class="ai-chip">Top 5 revenue generating admins</span>
        <span class="ai-chip">Admins at risk of churning</span>
        <span class="ai-chip">Most active domains this month</span>
        <span class="ai-chip">Predict next month renewals</span>
        <span class="ai-chip">Admins needing plan upgrade</span>
      </div>
    </div>
  </div>
</div>
<?php } ?>


<!-- Settings Page -->
<?php if($page == 'settings'){ ?>
<div class="row g-4">
  <div class="col-lg-8">
    <!-- System Settings Card -->
    <div class="card-modern mb-4">
      <div class="card-header-modern">
        <div class="card-title-modern">
          <i class="bi bi-gear-fill me-2"></i> System Settings
        </div>
      </div>
      <div class="card-body-modern">
        <form method="POST" action="save_super_settings.php">
          <div class="mb-3">
            <label class="form-label fw-semibold">System Name</label>
            <input type="text" class="form-control-modern" name="system_name" value="UltraServe">
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Timezone</label>
              <select class="form-control-modern" name="timezone">
                <option value="Asia/Kolkata" selected>Asia/Kolkata (IST)</option>
                <option value="America/New_York">America/New_York (EST)</option>
                <option value="Europe/London">Europe/London (GMT)</option>
                <option value="Asia/Dubai">Asia/Dubai (GST)</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Date Format</label>
              <select class="form-control-modern" name="date_format">
                <option value="Y-m-d" selected>YYYY-MM-DD</option>
                <option value="d-m-Y">DD-MM-YYYY</option>
                <option value="m/d/Y">MM/DD/YYYY</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Records Per Page</label>
            <select class="form-control-modern" name="records_per_page">
              <option value="10" selected>10</option>
              <option value="20">20</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>

          <div class="mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
              <label class="form-check-label fw-semibold" for="emailNotifications">
                Enable Email Notifications
              </label>
            </div>
          </div>

          <div class="mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="maintenanceMode">
              <label class="form-check-label fw-semibold" for="maintenanceMode">
                Maintenance Mode
              </label>
              <small class="text-muted">When enabled, only superadmins can access the system</small>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i> Save Settings
          </button>
        </form>
      </div>
    </div>

    <!-- Security Card (moved to left column) -->
    <div class="card-modern mb-4">
      <div class="card-header-modern">
        <div class="card-title-modern">
          <i class="bi bi-shield-check me-2"></i> Security
        </div>
      </div>
      <div class="card-body-modern">
        <button type="button" class="btn btn-warning w-100 mb-2" onclick="window.location.href='superadmin_dashboard.php?page=profile'">
          <i class="bi bi-key me-1"></i> Change Master Password
        </button>
        <button type="button" class="btn btn-danger w-100">
          <i class="bi bi-database me-1"></i> Clear All Logs
        </button>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <!-- System Info Card -->
    <div class="card-modern mb-4">
      <div class="card-header-modern">
        <div class="card-title-modern">
          <i class="bi bi-info-circle me-2"></i> System Info
        </div>
      </div>
      <div class="card-body-modern">
        <div class="admin-detail-item">
          <div class="detail-label">PHP Version</div>
          <div class="detail-value"><?= phpversion() ?></div>
        </div>
        <div class="admin-detail-item">
          <div class="detail-label">MySQL Version</div>
          <div class="detail-value"><?= mysqli_get_server_info($conn) ?></div>
        </div>
        <div class="admin-detail-item">
          <div class="detail-label">Server OS</div>
          <div class="detail-value"><?= PHP_OS ?></div>
        </div>
        <div class="admin-detail-item">
          <div class="detail-label">Current User</div>
          <div class="detail-value"><?= get_current_user() ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php } ?>


<!-- Activity Logs Page -->
<?php if($page == 'activity_logs'){ ?>
<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">
      <i class="bi bi-clock-history me-2"></i> System Activity Logs
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-danger" onclick="clearLogs()">
        <i class="bi bi-trash me-1"></i> Clear Logs
      </button>

    </div>
  </div>

  <!-- Filter Section -->
  <div class="search-section-modern">
    <form method="GET" class="search-form-modern">
      <input type="hidden" name="page" value="activity_logs">
      <div class="search-filter-wrapper">
        <div class="search-wrapper">
          <div class="search-input-group">
            <i class="bi bi-search search-icon"></i>
            <input type="text" name="search" class="search-input-modern"
                   placeholder="Search by admin name or action..."
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
          </div>
        </div>
        <div class="filter-wrapper">
          <select name="action_filter" class="filter-select-modern" onchange="this.form.submit()">
            <option value="">All Actions</option>
            <option value="Created" <?php echo (isset($_GET['action_filter']) && $_GET['action_filter'] == 'Created') ? 'selected' : ''; ?>>Created</option>
            <option value="Updated" <?php echo (isset($_GET['action_filter']) && $_GET['action_filter'] == 'Updated') ? 'selected' : ''; ?>>Updated</option>
            <option value="Deleted" <?php echo (isset($_GET['action_filter']) && $_GET['action_filter'] == 'Deleted') ? 'selected' : ''; ?>>Deleted</option>
            <option value="Logged In" <?php echo (isset($_GET['action_filter']) && $_GET['action_filter'] == 'Logged In') ? 'selected' : ''; ?>>Logged In</option>
            <option value="Logged Out" <?php echo (isset($_GET['action_filter']) && $_GET['action_filter'] == 'Logged Out') ? 'selected' : ''; ?>>Logged Out</option>
            <option value="Sent Notification" <?php echo (isset($_GET['action_filter']) && $_GET['action_filter'] == 'Sent Notification') ? 'selected' : ''; ?>>Sent Notification</option>
          </select>
        </div>
        <button type="submit" class="btn-search-modern">
          <i class="bi bi-search me-1"></i> Search
        </button>
      </div>
    </form>
  </div>

  <div class="table-responsive-modern">
    <table class="table-modern">
      <thead>
        <tr>
          <th>ID</th>
          <th>Admin</th>
          <th>Action</th>
          <th>Table</th>
          <th>Record ID</th>
          <th>Timestamp</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php
      // Pagination
      $limit = 50;
      $page_no = isset($_GET['pageno']) ? (int)$_GET['pageno'] : 1;
      $offset = ($page_no - 1) * $limit;

      $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
      $action_filter = isset($_GET['action_filter']) ? mysqli_real_escape_string($conn, $_GET['action_filter']) : '';

      $where = "WHERE 1=1";
      if(!empty($search)) {
          $where .= " AND (admin_name LIKE '%$search%' OR action LIKE '%$search%')";
      }
      if(!empty($action_filter)) {
          $where .= " AND action = '$action_filter'";
      }

      // Count total records
      $count_query = "SELECT COUNT(*) as total FROM activity_logs $where";
      $count_result = mysqli_query($conn, $count_query);
      $count_row = mysqli_fetch_assoc($count_result);
      $total_records = $count_row['total'];
      $total_pages = ceil($total_records / $limit);

      // Fetch logs
      $query = "SELECT * FROM activity_logs
                $where
                ORDER BY created_at DESC
                LIMIT $limit OFFSET $offset";
      $result = mysqli_query($conn, $query);

      if(mysqli_num_rows($result) > 0) {
          while($row = mysqli_fetch_assoc($result)) {
              $status_class = '';
              switch($row['action']) {
                  case 'Created':
                      $status_class = 'badge-success';
                      break;
                  case 'Updated':
                      $status_class = 'badge-warning';
                      break;
                  case 'Deleted':
                      $status_class = 'badge-danger';
                      break;
                  case 'Logged In':
                      $status_class = 'badge-info';
                      break;
                  case 'Logged Out':
                      $status_class = 'badge-secondary';
                      break;
                  default:
                      $status_class = 'badge-primary';
              }

              echo "<tr>";
              echo "<td>" . htmlspecialchars($row['id']) . "</td>";
              echo "<td>" . htmlspecialchars($row['admin_name'] ?? 'Unknown') . "</td>";
              echo "<td>" . htmlspecialchars($row['action']) . "</td>";
              echo "<td>" . htmlspecialchars($row['table_name'] ?? 'N/A') . "</td>";
              echo "<td>" . htmlspecialchars($row['record_id'] ?? 'N/A') . "</td>";
              echo "<td>" . date('M d, Y h:i A', strtotime($row['created_at'])) . "</td>";
              echo "<td><span class='badge $status_class'>" . htmlspecialchars($row['action']) . "</span></td>";
              echo "</tr>";
          }
      } else {
          echo "<tr><td colspan='7' class='text-center'>No activity logs found</td></tr>";
      }
      ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if($total_pages > 1): ?>
  <div class="pagination-modern">
    <nav>
      <ul class="pagination">
        <?php if($page_no > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?page=activity_logs&pageno=<?php echo $page_no - 1; ?>&search=<?php echo urlencode($search); ?>&action_filter=<?php echo urlencode($action_filter); ?>">
            <i class="bi bi-chevron-left"></i>
          </a>
        </li>
        <?php endif; ?>

        <?php
        $start_page = max(1, $page_no - 2);
        $end_page = min($total_pages, $page_no + 2);

        for($i = $start_page; $i <= $end_page; $i++):
        ?>
        <li class="page-item <?php echo ($i == $page_no) ? 'active' : ''; ?>">
          <a class="page-link" href="?page=activity_logs&pageno=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&action_filter=<?php echo urlencode($action_filter); ?>">
            <?php echo $i; ?>
          </a>
        </li>
        <?php endfor; ?>

        <?php if($page_no < $total_pages): ?>
        <li class="page-item">
          <a class="page-link" href="?page=activity_logs&pageno=<?php echo $page_no + 1; ?>&search=<?php echo urlencode($search); ?>&action_filter=<?php echo urlencode($action_filter); ?>">
            <i class="bi bi-chevron-right"></i>
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </nav>
    <div class="pagination-info">
      Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_records); ?> of <?php echo $total_records; ?> entries
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
function clearLogs() {
  if(confirm('Are you sure you want to clear all activity logs? This action cannot be undone.')) {
    window.location.href = 'clear_logs.php';
  }
}

function exportLogs() {
  window.location.href = 'export_logs.php?search=<?php echo urlencode($search); ?>&action_filter=<?php echo urlencode($action_filter); ?>';
}
</script>
<?php } ?>

<!-- Profile -->
<?php if($page == 'profile'){
  $profileResult = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM super_admins WHERE id = $super_admin_id"));
?>
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card-modern">
      <div class="card-header-modern">
        <div class="card-title-modern">
          <i class="bi bi-person-circle me-2"></i> SuperAdmin Profile
        </div>
      </div>
      <div class="card-body-modern">
        <form method="POST" action="update_super_profile.php">
          <div class="mb-3">
            <label class="form-label fw-semibold">Username</label>
            <input type="text" class="form-control-modern" name="username"
                   value="<?= htmlspecialchars($profileResult['username']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" class="form-control-modern" name="email"
                   value="<?= htmlspecialchars($profileResult['email']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">New Password (leave blank to keep current)</label>
            <input type="password" class="form-control-modern" name="new_password">
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i> Update Profile
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php } ?>

</div>

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