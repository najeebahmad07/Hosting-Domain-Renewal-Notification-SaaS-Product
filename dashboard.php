<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('db.php');


if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// Determine page and filter
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$filter = '';
$admin_id = $_SESSION['admin_id']; // Make sure you store admin_id in session on login

if(isset($_GET['filter'])){
    if($_GET['filter']=='expiring') $filter = "WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    elseif($_GET['filter']=='all') $filter = "";
    elseif(strpos($_GET['filter'],'provider_')===0){
        $prov = substr($_GET['filter'],9);
        $filter = "WHERE purchased_from='".$prov."'";
    }
}

// --- Add admin_id filter ---
if($filter != ''){
    $filter .= " AND admin_id = $admin_id";
} else {
    $filter = "WHERE admin_id = $admin_id";
}

// Hosting Domain data
if($page=='hosting_domain' || $page=='dashboard'){
    // Pagination setup
    $limit = 20; // records per page
    $page_no = isset($_GET['pageno']) ? (int)$_GET['pageno'] : 1;
    $offset = ($page_no - 1) * $limit;

    // Count total rows
    $count_query = "SELECT COUNT(*) as total FROM hosting_domain $filter";
    $count_result = mysqli_fetch_assoc(mysqli_query($conn, $count_query));
    $total_rows = $count_result['total'];
    $total_pages = ceil($total_rows / $limit);

    // Fetch limited data
    $query = "SELECT * FROM hosting_domain $filter ORDER BY id DESC LIMIT $offset, $limit";
    $result = mysqli_query($conn, $query);
}

// Analytics for dashboard
if($page=='dashboard'){
    $expiringSoonResult = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as expiring FROM hosting_domain WHERE admin_id=$admin_id AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)"));
    $totalClientsResult = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hosting_domain WHERE admin_id=$admin_id"));
    $providerResult = mysqli_query($conn, "SELECT purchased_from, COUNT(*) as count FROM hosting_domain WHERE admin_id=$admin_id GROUP BY purchased_from");
}


// Admin profile
if($page == 'profile'){
    // Get the logged-in admin's ID from session
    $admin_id = $_SESSION['admin_id'];

    // Fetch only their profile
    $profileResult = mysqli_query($conn, "SELECT * FROM admin WHERE id = $admin_id");

    // Fetch the row as associative array
    $profile = mysqli_fetch_assoc($profileResult);
}


// Activity logs
$activityLogs = [];
if($page=='dashboard'){
    // Only fetch logs for the logged-in admin
    $activityResult = mysqli_query($conn, "SELECT * FROM activity_logs WHERE admin_id = $admin_id ORDER BY created_at DESC LIMIT 10");
    while($log = mysqli_fetch_assoc($activityResult)){
        $activityLogs[] = $log;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UltraServe | Modern Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/png" sizes="32x32" href="img/logo.png">

<!-- Apple devices -->
<link rel="apple-touch-icon" href="img/logo.png">

<!-- For older browsers -->
<link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
<link rel="stylesheet" href="css/dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">
          <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Delete
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="modal-icon-wrapper mb-3">
          <i class="bi bi-trash3-fill"></i>
        </div>
        <p class="fw-semibold mb-3">Are you sure you want to delete this record?</p>
        <p class="text-muted small">This action cannot be undone.</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
          <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutModalLabel">
          <i class="bi bi-box-arrow-right me-2"></i> Confirm Logout
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

<!-- Edit Confirmation Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">
          <i class="bi bi-pencil-square me-2"></i> Confirm Edit
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="modal-icon-wrapper mb-3">
          <i class="bi bi-pencil-fill"></i>
        </div>
        <p class="fw-semibold mb-3">Do you want to edit this record?</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
          <a href="#" id="confirmEditBtn" class="btn btn-primary">Yes, Edit</a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Header -->
<div class="header">
    <div class="d-flex align-items-center">
        <button class="btn-icon me-3" id="sidebarToggle" type="button">
            <i class="bi bi-list"></i>
        </button>
       <?php
// Set the timezone to Kolkata, India (Indian Standard Time)
date_default_timezone_set('Asia/Kolkata');

// Get the current hour in 24-hour format
$current_hour = date('H');

// Determine time-based greeting with emojis
if ($current_hour >= 5 && $current_hour < 12) {
    $greeting = "Good Morning";
    $emoji = "🌅";
} elseif ($current_hour >= 12 && $current_hour < 17) {
    $greeting = "Good Afternoon";
    $emoji = "☀️";
} elseif ($current_hour >= 17 && $current_hour < 21) {
    $greeting = "Good Evening";
    $emoji = "🌆";
} else {
    $greeting = "Good Night";
    $emoji = "🌙";
}

// Ensure $_SESSION['admin'] is set before echoing it
if (isset($_SESSION['admin'])) {
    echo "<div class='greeting-wrapper'>";
    echo "<span class='greeting-emoji'>{$emoji}</span>";
    echo "<div>";
    echo "<div class='greeting-text'>{$greeting}</div>";
    echo "<div class='greeting-name'>{$_SESSION['admin']}</div>";
    echo "</div>";
    echo "</div>";
} else {
    echo "<h4>Welcome, Guest!</h4>";
}
?>
    </div>

    <div class="header-actions">
        <button class="btn-icon" id="themeToggle" title="Toggle Theme">
            <i class="bi bi-moon-stars-fill"></i>
        </button>
        <?php if($page!='dashboard'){ ?>

        <?php } ?>
        <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="btn btn-outline">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
    </div>
</div>

<!-- Sidebar -->
<?php include 'sidebar.php'?>

<div class="content">

  <!-- General Settings -->
<?php if($page=='settings_general'){ ?>
<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">
        <i class="bi bi-sliders me-2"></i> General Settings
    </div>
  </div>
  <div class="card-body-modern">
    <form method="POST" action="save_settings.php">
      <input type="hidden" name="settings_type" value="general">
      <input type="hidden" name="admin_id" value="<?= $admin_id ?>">

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Timezone</label>
          <select class="form-control-modern" name="timezone">
            <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
            <option value="America/New_York">America/New_York (EST)</option>
            <option value="Europe/London">Europe/London (GMT)</option>
            <option value="Asia/Dubai">Asia/Dubai (GST)</option>
            <option value="Australia/Sydney">Australia/Sydney (AEST)</option>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Currency</label>
          <select class="form-control-modern" name="currency">
            <option value="INR">INR - Indian Rupee (₹)</option>
            <option value="USD">USD - US Dollar ($)</option>
            <option value="EUR">EUR - Euro (€)</option>
            <option value="GBP">GBP - British Pound (£)</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Date Format</label>
        <select class="form-control-modern" name="date_format">
          <option value="Y-m-d">YYYY-MM-DD (2024-01-15)</option>
          <option value="d-m-Y">DD-MM-YYYY (15-01-2024)</option>
          <option value="m/d/Y">MM/DD/YYYY (01/15/2024)</option>
          <option value="d M Y">DD Mon YYYY (15 Jan 2024)</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-circle me-2"></i> Save Settings
      </button>
    </form>
  </div>
</div>
<?php } ?>


<!-- Logo & Branding -->
<?php if($page=='settings_branding'){ ?>
<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">
        <i class="bi bi-palette-fill me-2"></i> Logo & Branding
    </div>
  </div>
  <div class="card-body-modern">
    <form method="POST" action="save_settings.php" enctype="multipart/form-data">
      <input type="hidden" name="settings_type" value="branding">
      <input type="hidden" name="admin_id" value="<?= $admin_id ?>">

      <div class="mb-3">
        <label class="form-label fw-semibold">Company Name</label>
        <input type="text" class="form-control-modern" name="company_name" placeholder="UltraServe" value="UltraServe">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Upload Logo</label>
        <input type="file" class="form-control-modern" name="logo" accept="image/*">
        <small class="text-muted">Recommended size: 200x50px (PNG, JPG, SVG)</small>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Primary Color</label>
          <input type="color" class="form-control-modern" name="primary_color" value="#6366f1">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Secondary Color</label>
          <input type="color" class="form-control-modern" name="secondary_color" value="#8b5cf6">
        </div>
      </div>

      <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-circle me-2"></i> Save Branding
      </button>
    </form>
  </div>
</div>
<?php } ?>

<!-- Auto-Backup Settings -->
<?php if($page=='settings_backup'){ ?>
<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">
        <i class="bi bi-cloud-arrow-up me-2"></i> Auto-Backup Settings
    </div>
  </div>
  <div class="card-body-modern">
    <form method="POST" action="save_settings.php">
      <input type="hidden" name="settings_type" value="backup">
      <input type="hidden" name="admin_id" value="<?= $admin_id ?>">

      <div class="mb-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="enableBackup" name="enable_backup" checked>
          <label class="form-check-label fw-semibold" for="enableBackup">Enable Auto-Backup</label>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Backup Frequency</label>
          <select class="form-control-modern" name="backup_frequency">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Backup Time</label>
          <input type="time" class="form-control-modern" name="backup_time" value="02:00">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Backup Location</label>
        <input type="text" class="form-control-modern" name="backup_location" placeholder="/var/backups/" value="/backups/">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Keep Backups For</label>
        <select class="form-control-modern" name="backup_retention">
          <option value="7">7 Days</option>
          <option value="30" selected>30 Days</option>
          <option value="90">90 Days</option>
          <option value="365">1 Year</option>
        </select>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-2"></i> Save Settings
        </button>
        <button type="button" class="btn btn-success">
            <i class="bi bi-cloud-upload me-2"></i> Backup Now
        </button>
      </div>
    </form>
  </div>
</div>
<?php } ?>


<!-- System Logs -->
<?php if($page=='settings_logs'){ ?>
<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">
        <i class="bi bi-journal-text me-2"></i> System Logs
    </div>
    <button class="btn btn-sm btn-danger">
        <i class="bi bi-trash me-1"></i> Clear All
    </button>
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
          <th>Time</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $logs_result = mysqli_query($conn, "SELECT * FROM activity_logs WHERE admin_id = $admin_id ORDER BY created_at DESC LIMIT 100");
        if(mysqli_num_rows($logs_result) > 0){
          while($log = mysqli_fetch_assoc($logs_result)){
        ?>
        <tr>
          <td><?= $log['id'] ?></td>
          <td><?= htmlspecialchars($log['admin_name']) ?></td>
          <td><span class="badge-modern badge-info"><?= $log['action'] ?></span></td>
          <td><?= $log['table_name'] ?></td>
          <td><?= $log['record_id'] ?></td>
          <td><?= $log['created_at'] ?></td>
        </tr>
        <?php
          }
        } else {
        ?>
        <tr><td colspan="6" class="text-center">No logs found</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php } ?>

 <!-- Data Retention Policy -->
<?php if($page=='settings_retention'){ ?>
<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">
        <i class="bi bi-database me-2"></i> Data Retention Policy
    </div>
  </div>
  <div class="card-body-modern">
    <form method="POST" action="save_settings.php">
      <input type="hidden" name="settings_type" value="retention">
      <input type="hidden" name="admin_id" value="<?= $admin_id ?>">

      <div class="mb-3">
        <label class="form-label fw-semibold">Delete Expired Domains Older Than</label>
        <select class="form-control-modern" name="retention_period">
          <option value="never">Never Delete</option>
          <option value="90">3 Months after expiry</option>
          <option value="180">6 Months after expiry</option>
          <option value="365" selected>1 Year after expiry</option>
          <option value="730">2 Years after expiry</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Delete Activity Logs Older Than</label>
        <select class="form-control-modern" name="logs_retention">
          <option value="30">30 Days</option>
          <option value="90" selected>90 Days</option>
          <option value="180">6 Months</option>
          <option value="365">1 Year</option>
        </select>
      </div>

      <div class="mb-3">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="autoDelete" name="auto_delete" checked>
          <label class="form-check-label fw-semibold" for="autoDelete">Enable Automatic Deletion</label>
        </div>
        <small class="text-muted">Automatically delete records based on retention policy</small>
      </div>

      <div class="mb-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="deleteExpired" name="delete_expired">
          <label class="form-check-label fw-semibold" for="deleteExpired">Auto-delete Expired Domains</label>
        </div>
        <small class="text-muted">Automatically remove expired domain records</small>
      </div>

      <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Warning:</strong> Deleted data cannot be recovered. Make sure you have backups enabled.
      </div>

      <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-circle me-2"></i> Save Policy
      </button>
    </form>
  </div>
</div>
<?php } ?>

<!-- Success/Error Messages -->
<?php if(isset($_SESSION['success'])){ ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="bi bi-check-circle me-2"></i>
  <?= $_SESSION['success'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['success']); } ?>

<?php if(isset($_SESSION['error'])){ ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <i class="bi bi-exclamation-triangle me-2"></i>
  <?= $_SESSION['error'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['error']); } ?>

<!-- Dashboard Cards -->
<?php if($page=='dashboard'){ ?>
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card-modern stat-card-danger" onclick="window.location='dashboard.php?page=hosting_domain&filter=expiring'">
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
        <div class="stat-card-modern stat-card-primary" onclick="window.location='dashboard.php?page=hosting_domain&admin_id=<?= $_SESSION['admin_id'] ?>&filter=all'">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-label">Total Clients</div>
                <div class="stat-value"><?= $totalClientsResult['total'] ?></div>
                <div class="stat-subtitle">All domains</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card-modern stat-card-success">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="bi bi-diagram-3-fill"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-label">Active Providers</div>
                <div class="stat-value">
                    <?php
                    $prov_count = 0;
                    $tempResult = mysqli_query($conn, "SELECT COUNT(DISTINCT purchased_from) as count FROM hosting_domain WHERE admin_id = $admin_id");
                    $tempRow = mysqli_fetch_assoc($tempResult);
                    echo $tempRow['count'];
                    ?>
                </div>
                <div class="stat-subtitle">Hosting providers</div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card-modern stat-card-warning">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="bi bi-activity"></i>
                </div>
            </div>
            <div class="stat-card-body">
                <div class="stat-label">Recent Activity</div>
                <div class="stat-value"><?= count($activityLogs) ?></div>
                <div class="stat-subtitle">Latest logs</div>
            </div>
        </div>
    </div>
</div>

<?php
$admin_id = $_SESSION['admin_id'] ?? 0;

// Monthly Registrations (only for this admin)
$monthlyRegistrations = [];
$monthQuery = mysqli_query($conn, "
    SELECT MONTH(registration_date) as month, COUNT(*) as count
    FROM hosting_domain
    WHERE admin_id = $admin_id
    GROUP BY MONTH(registration_date)
");
while($row = mysqli_fetch_assoc($monthQuery)){
    $monthlyRegistrations[$row['month']] = $row['count'];
}

// Domains by Provider (only for this admin)
$providerData = [];
$providerQuery = mysqli_query($conn, "
    SELECT purchased_from, COUNT(*) as count
    FROM hosting_domain
    WHERE admin_id = $admin_id
    GROUP BY purchased_from
");
while($row = mysqli_fetch_assoc($providerQuery)){
    $providerData[$row['purchased_from']] = $row['count'];
}
?>

<!-- Charts Row -->
<div class="row g-4 mb-4">
  <div class="col-lg-6">
    <div class="card-modern">
      <div class="card-header-modern">
        <div class="card-title-modern">Monthly Registrations</div>
      </div>
      <div class="card-body-modern">
        <canvas id="monthlyRegistrations" style="max-height: 280px;"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card-modern">
      <div class="card-header-modern">
        <div class="card-title-modern">Domains by Provider</div>
      </div>
      <div class="card-body-modern">
        <canvas id="providerUsage" style="max-height: 280px;"></canvas>
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
        <td><?= $log['admin_name'] ?></td>
        <td><span class="badge-modern badge-primary"><?= $log['action'] ?></span></td>
        <td><?= $log['table_name'] ?></td>
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

<?php } ?>

<!-- Hosting Domain Table -->
<?php if($page=='hosting_domain'){
    // Get search and filter parameters
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
    $filter = isset($_GET['filter']) ? $_GET['filter'] : '';

    // Base query with search and filter functionality
    $where_conditions = [];

    // ✅ Restrict data to logged-in admin using admin_id
    if(isset($_SESSION['admin_id'])) {
        $admin = mysqli_real_escape_string($conn, $_SESSION['admin_id']);
        $where_conditions[] = "admin_id = '$admin'";
    } else {
        echo "<div class='alert alert-danger'>Admin session not found.</div>";
        exit;
    }

    // Search condition
    if(!empty($search)) {
        $where_conditions[] = "(domain_name LIKE '%$search%' OR
            company_name LIKE '%$search%' OR
            email LIKE '%$search%' OR
            purchased_from LIKE '%$search%' OR
            hosting_purchased_from LIKE '%$search%')";
    }

    // Filter condition for expiring domains/hosting
    if(!empty($filter)) {
        $today = date('Y-m-d');
        $days_30 = date('Y-m-d', strtotime('+30 days'));

        if($filter == 'domain_expiring') {
            $where_conditions[] = "(expiry_date BETWEEN '$today' AND '$days_30')";
        } elseif($filter == 'hosting_expiring') {
            $where_conditions[] = "(hosting_expiry_date BETWEEN '$today' AND '$days_30')";
        } elseif($filter == 'both_expiring') {
            $where_conditions[] = "((expiry_date BETWEEN '$today' AND '$days_30') OR (hosting_expiry_date BETWEEN '$today' AND '$days_30'))";
        } elseif($filter == 'domain_expired') {
            $where_conditions[] = "(expiry_date < '$today')";
        } elseif($filter == 'hosting_expired') {
            $where_conditions[] = "(hosting_expiry_date < '$today')";
        }
    }

    // Build WHERE clause
    $where_clause = "";
    if(count($where_conditions) > 0) {
        $where_clause = " WHERE " . implode(" AND ", $where_conditions);
    }

    // Get total records for pagination
    $count_query = "SELECT COUNT(*) as total FROM hosting_domain" . $where_clause;
    $count_result = mysqli_query($conn, $count_query);
    $total_records = mysqli_fetch_assoc($count_result)['total'];

    // Pagination setup
    $records_per_page = 10;
    $total_pages = ceil($total_records / $records_per_page);
    $page_no = isset($_GET['pageno']) ? (int)$_GET['pageno'] : 1;
    $page_no = max(1, min($page_no, $total_pages));
    $offset = ($page_no - 1) * $records_per_page;

    // Main query with search, filter and pagination
    $query = "SELECT * FROM hosting_domain" . $where_clause .
             " ORDER BY id DESC LIMIT $offset, $records_per_page";
    $result = mysqli_query($conn, $query);
?>

<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">
        <i class="bi bi-server me-2"></i> Hosting & Domains
    </div>
  </div>

  <!-- Search and Filter Bar Section -->
  <div class="search-section-modern">
    <form method="GET" action="" class="search-form-modern">
      <input type="hidden" name="page" value="hosting_domain">

      <div class="search-filter-wrapper">
        <!-- Search Input -->
        <div class="search-wrapper">
          <div class="search-input-group">
            <i class="bi bi-search search-icon"></i>
            <input type="text"
                   name="search"
                   class="search-input-modern"
                   placeholder="Search by domain, company, email..."
                   value="<?= htmlspecialchars($search) ?>">
            <?php if(!empty($search)): ?>
            <a href="?page=hosting_domain<?= !empty($filter) ? '&filter='.$filter : '' ?>"
               class="clear-search"
               title="Clear search">
              <i class="bi bi-x-circle"></i>
            </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Filter Dropdown -->
        <div class="filter-wrapper">
          <select name="filter" class="filter-select-modern" onchange="this.form.submit()">
            <option value="">All Records</option>
            <option value="domain_expiring" <?= $filter=='domain_expiring'?'selected':'' ?>>
              Domain Expiring (30 days)
            </option>
            <option value="hosting_expiring" <?= $filter=='hosting_expiring'?'selected':'' ?>>
              Hosting Expiring (30 days)
            </option>
            <option value="both_expiring" <?= $filter=='both_expiring'?'selected':'' ?>>
              Both Expiring (30 days)
            </option>
            <option value="domain_expired" <?= $filter=='domain_expired'?'selected':'' ?>>
              Domain Expired
            </option>
            <option value="hosting_expired" <?= $filter=='hosting_expired'?'selected':'' ?>>
              Hosting Expired
            </option>
          </select>
        </div>

        <!-- Search Button -->
        <button type="submit" class="btn-search-modern">
          <i class="bi bi-search me-1"></i> Search
        </button>
      </div>
    </form>

    <?php if(!empty($search) || !empty($filter)): ?>
    <div class="search-results-info">
      <span class="text-muted">
        Found <strong><?= $total_records ?></strong> result(s)
        <?php if(!empty($search)): ?>
          for "<strong><?= htmlspecialchars($search) ?></strong>"
        <?php endif; ?>
        <?php if(!empty($filter)): ?>
          <?php
          $filter_names = [
            'domain_expiring' => 'Domain Expiring in 30 days',
            'hosting_expiring' => 'Hosting Expiring in 30 days',
            'both_expiring' => 'Domain/Hosting Expiring in 30 days',
            'domain_expired' => 'Domain Expired',
            'hosting_expired' => 'Hosting Expired'
          ];
          ?>
          with filter: <strong><?= $filter_names[$filter] ?? $filter ?></strong>
        <?php endif; ?>
      </span>
      <a href="?page=hosting_domain" class="btn-clear-all">
        <i class="bi bi-x-circle me-1"></i> Clear All
      </a>
    </div>
    <?php endif; ?>
  </div>

  <div class="table-responsive-modern">
    <table class="table-modern">
      <thead>
        <tr>
       <?php
$show_columns = [
  'id',
  'company_name',
  'domain_name',
  'purchased_from',
  'registration_date',
  'expiry_date',
  'hosting_purchased_from',
  'hosting_registration_date',
  'hosting_expiry_date',
  'email'
];

foreach($show_columns as $col){
  echo "<th>".ucwords(str_replace('_',' ',$col))."</th>";
  if ($col == 'expiry_date') {
      echo "<th>Domain Days Left</th>";
  }
  if ($col == 'hosting_expiry_date') {
      echo "<th>Hosting Days Left</th>";
  }
}
?>
        <th>Actions</th>
        <th>Email</th>
        </tr>
      </thead>
      <tbody>
      <?php while($row=mysqli_fetch_assoc($result)){ ?>
      <tr>
      <?php
      $today = new DateTime();

      $domainExpiryDate = new DateTime($row['expiry_date']);
      $domainInterval = $today->diff($domainExpiryDate);
      $domainDaysLeft = (int)$domainInterval->format('%r%a');

      $hostingExpiryDate = new DateTime($row['hosting_expiry_date']);
      $hostingInterval = $today->diff($hostingExpiryDate);
      $hostingDaysLeft = (int)$hostingInterval->format('%r%a');

      foreach($show_columns as $col){
      ?>
      <td>
        <?php
            if ($col == 'domain_name') {
                $domain_url = 'http://' . htmlspecialchars($row[$col]);
                echo '<a href="' . $domain_url . '" target="_blank" rel="noopener noreferrer" class="link-modern">' . htmlspecialchars($row[$col]) . ' <i class="bi bi-box-arrow-up-right small"></i></a>';
            } else {
                echo htmlspecialchars($row[$col]);
            }
        ?>
      </td>
      <?php
          if ($col == 'expiry_date') {
              $class = '';
              $displayText = '';
              if ($domainDaysLeft < 0) {
                  $class = 'text-danger fw-bold';
                  $displayText = 'Expired';
              } elseif ($domainDaysLeft <= 30) {
                  $class = 'text-warning fw-bold';
                  $displayText = $domainDaysLeft . ' days';
              } else {
                  $class = 'text-success';
                  $displayText = $domainDaysLeft . ' days';
              }
              echo '<td class="' . $class . '">' . $displayText . '</td>';
          }

          if ($col == 'hosting_expiry_date') {
              $class = '';
              $displayText = '';
              if ($hostingDaysLeft < 0) {
                  $class = 'text-danger fw-bold';
                  $displayText = 'Expired';
              } elseif ($hostingDaysLeft <= 30) {
                  $class = 'text-warning fw-bold';
                  $displayText = $hostingDaysLeft . ' days';
              } else {
                  $class = 'text-success';
                  $displayText = $hostingDaysLeft . ' days';
              }
              echo '<td class="' . $class . '">' . $displayText . '</td>';
          }
      }
      ?>

      <td>
        <div class="action-buttons">
            <a href="view.php?id=<?= $row['id'] ?>" class="btn-action btn-action-view" title="View">
                <i class="bi bi-eye-fill"></i>
            </a>
            <a href="#" data-id="<?= $row['id'] ?>" class="btn-action btn-action-edit edit-btn" title="Edit">
                <i class="bi bi-pencil-fill"></i>
            </a>
            <a href="#" data-id="<?= $row['id'] ?>" class="btn-action btn-action-delete delete-btn" title="Delete">
                <i class="bi bi-trash-fill"></i>
            </a>
        </div>
      </td>
      <td>
      <?php if(isset($row['email']) && !empty($row['email'])): ?>
        <a href="send_email.php?email=<?= $row['email'] ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-envelope-fill me-1"></i> Send
        </a>
      <?php else: ?>
        <span class="text-muted">—</span>
      <?php endif; ?>
      </td>
      </tr>
      <?php } ?>
      <?php if(mysqli_num_rows($result)==0){ ?>
      <tr><td colspan="<?= count($show_columns)+4 ?>" class="text-center">
        <?php if(!empty($search) || !empty($filter)): ?>
          No records found matching your criteria
        <?php else: ?>
          No records found
        <?php endif; ?>
      </td></tr>
      <?php } ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if($total_pages>1){
  $searchQuery = !empty($search) ? '&search='.urlencode($search) : '';
  $filterQuery = !empty($filter) ? '&filter='.$filter : '';
  ?>
  <div class="pagination-modern">
      <button class="btn-pagination" <?= ($page_no <= 1) ? 'disabled' : '' ?>
              onclick="window.location='?page=hosting_domain<?= $searchQuery.$filterQuery ?>&pageno=<?= $page_no-1 ?>'">
          <i class="bi bi-chevron-left"></i>
      </button>

      <?php for($i=1; $i<=$total_pages; $i++){ ?>
      <button class="btn-pagination <?= ($i==$page_no)?'active':'' ?>"
              onclick="window.location='?page=hosting_domain<?= $searchQuery.$filterQuery ?>&pageno=<?= $i ?>'">
          <?= $i ?>
      </button>
      <?php } ?>

      <button class="btn-pagination" <?= ($page_no >= $total_pages) ? 'disabled' : '' ?>
              onclick="window.location='?page=hosting_domain<?= $searchQuery.$filterQuery ?>&pageno=<?= $page_no+1 ?>'">
          <i class="bi bi-chevron-right"></i>
      </button>
  </div>
  <?php } ?>
</div>
<?php } ?>

<!-- Profile Table -->
<?php if($page=='profile'){ ?>
<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">
        <i class="bi bi-person-circle me-2"></i> Admin Profile
    </div>
  </div>
  <div class="table-responsive-modern">
    <table class="table-modern">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?= htmlspecialchars($profile['id']) ?></td>
          <td><?= htmlspecialchars($profile['username']) ?></td>
          <td><?= htmlspecialchars($profile['email']) ?></td>
          <td>
            <div class="action-buttons">
                <a href="edit.php?table=admin&id=<?= $profile['id'] ?>" class="btn-action btn-action-edit">
                    <i class="bi bi-pencil-fill"></i>
                </a>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<?php } ?>

<!-- Calendar View -->
<?php if($page=='calendar'){ ?>
<div class="card-modern">
  <div class="card-header-modern">
    <div class="card-title-modern">
        <i class="bi bi-calendar-event me-2"></i> Calendar View
    </div>
  </div>
  <div class="card-body-modern p-0">
    <iframe src="https://calendar.google.com/calendar/embed?src=94dd21dca79e3a1bc4d84f268d51a00b84cb6bd9767100896953e362a7a77116%40group.calendar.google.com&ctz=Asia%2FKolkata"
            style="border: 0" width="100%" height="600" frameborder="0" scrolling="no"></iframe>
  </div>
</div>
<?php } ?>

<!-- Theme & Layout -->
<?php if($page=='theme'){ ?>
<div class="row">
  <div class="col-lg-8">
    <div class="card-modern">
      <div class="card-header-modern">
        <div class="card-title-modern">
            <i class="bi bi-palette me-2"></i> Theme & Layout
        </div>
      </div>
      <div class="card-body-modern">
        <p class="mb-4">Choose a theme for the dashboard:</p>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="theme-option">
              <input class="form-check-input" type="radio" name="themeColor" id="themeLight" value="light">
              <label class="form-check-label" for="themeLight">
                <div class="theme-preview light"></div>
                <strong>Light Mode</strong>
                <small class="d-block text-muted">Clean and professional</small>
              </label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="theme-option">
              <input class="form-check-input" type="radio" name="themeColor" id="themeDark" value="dark">
              <label class="form-check-label" for="themeDark">
                <div class="theme-preview dark"></div>
                <strong>Dark Mode</strong>
                <small class="d-block text-muted">Easy on the eyes</small>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php } ?>

<!-- Help Center -->
<?php if($page=='help'){ ?>
<div class="page-header mb-4">
    <h3><i class="bi bi-question-circle me-2"></i> Help Center</h3>
</div>
<div class="row g-4">
  <?php
  $help_cards = [
    ['title'=>'Add Hosting/Domain','text'=>'Go to "Add Hosting & Domain" and fill the form to add a new client domain or hosting details.', 'icon'=>'plus-circle-fill'],
    ['title'=>'Import Data','text'=>'Click on "Import" to upload CSV or Excel files containing hosting/domain records.', 'icon'=>'upload'],
    ['title'=>'Export Data','text'=>'Click on "Export" to download all hosting/domain records in CSV or Excel format.', 'icon'=>'download'],
    ['title'=>'Login','text'=>'Use your admin credentials on the login page to access the dashboard.', 'icon'=>'shield-lock-fill'],
    ['title'=>'Logout','text'=>'Click the "Logout" button in the header to securely exit the admin dashboard.', 'icon'=>'box-arrow-right'],
    ['title'=>'Profile Management','text'=>'Go to "Profile" to view or edit admin account details like username and email.', 'icon'=>'person-fill'],
    ['title'=>'Dashboard Overview','text'=>'The dashboard shows analytics, expiring domains, provider stats, and recent activity logs.', 'icon'=>'speedometer2'],
    ['title'=>'Theme & Layout','text'=>'Go to "Theme & Layout" to choose Light, Dark, or Custom theme for the dashboard.', 'icon'=>'palette-fill'],
    ['title'=>'Filters & Analytics','text'=>'Use filters to view expiring domains or by provider. See charts and analytics for quick insights.', 'icon'=>'funnel-fill']
  ];

  foreach($help_cards as $card){
  ?>
  <div class="col-md-4">
    <div class="help-card-modern">
      <div class="help-icon">
        <i class="bi bi-<?= $card['icon'] ?>"></i>
      </div>
      <h5><?= $card['title'] ?></h5>
      <p><?= $card['text'] ?></p>
    </div>
  </div>
  <?php } ?>
</div>
<?php } ?>

<?php if($page=='payment_methods'){ ?>
<div class="page-header mb-4">
  <h3><i class="bi bi-credit-card-2-front me-2"></i> Payment Methods</h3>
</div>

<div class="row g-4">
  <?php
  $payment_cards = [
    [
      'title' => 'PhonePe',
      'text'  => 'Pay securely using PhonePe UPI for fast and safe transactions.',
      'icon'  => 'phone' // bootstrap icon (PhonePe doesn’t have native icon)
    ],
    [
      'title' => 'Paytm',
      'text'  => 'Make payments easily via Paytm wallet or UPI linked accounts.',
      'icon'  => 'wallet2'
    ],
    [
      'title' => 'Google Pay (GPay)',
      'text'  => 'Use your Google Pay account to send UPI payments instantly.',
      'icon'  => 'google' // custom name; you can replace with image if needed
    ],
    [
      'title' => 'Net Banking',
      'text'  => 'Transfer money directly through your bank’s NetBanking service.',
      'icon'  => 'bank'
    ],
    [
      'title' => 'Debit / Credit Card',
      'text'  => 'Pay using any Visa, MasterCard, or RuPay card securely.',
      'icon'  => 'credit-card-2-front'
    ],
    [
      'title' => 'UPI QR Code',
      'text'  => 'Scan the QR code with any UPI app for instant payment.',
      'icon'  => 'qr-code'
    ]
  ];

  foreach($payment_cards as $card){
  ?>
    <div class="col-md-4">
      <div class="help-card-modern">
        <div class="help-icon">
          <i class="bi bi-<?= $card['icon'] ?>"></i>
        </div>
        <h5><?= $card['title'] ?></h5>
        <p><?= $card['text'] ?></p>
      </div>
    </div>
  <?php } ?>
</div>
<?php } ?>


<!-- System Info -->
<?php if($page=='system_info'){ ?>
<div class="page-header mb-4">
    <h3><i class="bi bi-info-circle me-2"></i> System Information</h3>
</div>
<div class="row g-4">
  <?php
  $system_info = [
      ['title'=>'PHP Version','value'=>phpversion(), 'icon'=>'code-square'],
      ['title'=>'MySQL Version','value'=>mysqli_get_server_info($conn), 'icon'=>'database-fill'],
      ['title'=>'Server OS','value'=>php_uname(), 'icon'=>'server'],
      ['title'=>'Current User','value'=>get_current_user(), 'icon'=>'person-badge-fill']
  ];

  foreach($system_info as $info){
  ?>
  <div class="col-md-6">
    <div class="system-card-modern">
      <div class="system-icon">
        <i class="bi bi-<?= $info['icon'] ?>"></i>
      </div>
      <div>
        <div class="system-label"><?= $info['title'] ?></div>
        <div class="system-value"><?= $info['value'] ?></div>
      </div>
    </div>
  </div>
  <?php } ?>
</div>
<?php } ?>

</div>
<script>
  // Enhanced version with smooth icon transition
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');

    // Function to update theme
    function updateTheme(theme) {
        const themeIcon = themeToggle.querySelector('i');

        // Add rotation animation
        themeIcon.style.transform = 'rotate(360deg)';

        setTimeout(() => {
            if (theme === 'dark') {
                document.body.classList.add('theme-dark');
                themeIcon.className = 'bi bi-sun-fill';
                themeToggle.title = 'Switch to Light Mode';
            } else {
                document.body.classList.remove('theme-dark');
                themeIcon.className = 'bi bi-moon-stars-fill';
                themeToggle.title = 'Switch to Dark Mode';
            }
            themeIcon.style.transform = 'rotate(0deg)';
        }, 150);
    }

    // Check and apply saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    updateTheme(savedTheme);

    // Toggle theme on click
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.body.classList.contains('theme-dark') ? 'dark' : 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        updateTheme(newTheme);
        localStorage.setItem('theme', newTheme);
    });
});
</script>
<script src="js/dashboard.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if($page=='dashboard'){
$admin_id = $_SESSION['admin_id'];
?>
<script>
// Monthly Registrations Chart
var ctx1 = document.getElementById('monthlyRegistrations').getContext('2d');
var monthlyRegistrations = new Chart(ctx1, {
    type: 'line',
    data: {
        labels: [<?php
            $months = mysqli_query($conn, "SELECT DATE_FORMAT(registration_date,'%b %Y') as month, COUNT(*) as count FROM hosting_domain WHERE admin_id=$admin_id GROUP BY month ORDER BY registration_date");
            $monthLabels = [];
            $monthCounts = [];
            while($m=mysqli_fetch_assoc($months)){
                $monthLabels[] = "'".$m['month']."'";
                $monthCounts[] = $m['count'];
            }
            echo implode(',',$monthLabels);
        ?>],
        datasets: [{
            label: 'New Domains',
            data: [<?= implode(',',$monthCounts) ?>],
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#6366f1',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                borderRadius: 8,
                titleColor: '#fff',
                bodyColor: '#fff'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0, 0, 0, 0.05)' },
                ticks: { color: '#6b7280' }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#6b7280' }
            }
        }
    }
});

// Provider Usage Chart
var ctx2 = document.getElementById('providerUsage').getContext('2d');
var providerUsage = new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: [<?php
            $providers = mysqli_query($conn, "SELECT purchased_from, COUNT(*) as count FROM hosting_domain WHERE admin_id=$admin_id GROUP BY purchased_from");
            $provLabels = [];
            $provCounts = [];
            while($p=mysqli_fetch_assoc($providers)){
                $provLabels[] = "'".$p['purchased_from']."'";
                $provCounts[] = $p['count'];
            }
            echo implode(',',$provLabels);
        ?>],
        datasets: [{
            label: 'Domains',
            data: [<?= implode(',',$provCounts) ?>],
            backgroundColor: [
                '#6366f1',
                '#8b5cf6',
                '#ec4899',
                '#f59e0b',
                '#10b981',
                '#06b6d4',
                '#ef4444'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 15,
                    color: '#6b7280',
                    font: { size: 12 }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                borderRadius: 8
            }
        }
    }
});
</script>
<?php } ?>

</body>
</html>