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
<title>UltraServe | Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="stylesheet" href="css/dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel">
          <i class="bi bi-exclamation-triangle me-2"></i> Confirm Delete
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="fw-semibold mb-3">Are you sure you want to delete this record?</p>
        <div class="d-flex justify-content-center gap-3">
          <a href="#" id="confirmDeleteBtn" class="btn btn-danger px-4">OK</a>
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="logoutModalLabel">
          <i class="bi bi-box-arrow-right me-2"></i> Confirm Logout
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="mb-3 fw-semibold">Are you sure you want to log out?</p>
        <div class="d-flex justify-content-center gap-3">
          <a href="logout.php" class="btn btn-danger px-4">OK</a>
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Edit Confirmation Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="editModalLabel">
          <i class="bi bi-pencil-square me-2"></i> Confirm Edit
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="fw-semibold mb-3">Do you want to edit this record?</p>
        <div class="d-flex justify-content-center gap-3">
          <a href="#" id="confirmEditBtn" class="btn btn-primary px-4">Yes</a>
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Header -->
<div class="header">
    <div class="d-flex align-items-center">
        <button class="btn me-3" id="sidebarToggle" type="button" style="border: none; font-size: 20px;"><i class="bi bi-list"></i></button>
       <?php
// Set the timezone to Kolkata, India (Indian Standard Time)
date_default_timezone_set('Asia/Kolkata');

// Get the current hour in 24-hour format
$current_hour = date('H');




// Determine time-based greeting with emojis
if ($current_hour >= 5 && $current_hour < 12) {
    $greeting = "Good Morning 🌞";
} elseif ($current_hour >= 12 && $current_hour < 17) {
    $greeting = "Good Afternoon 🌻";
} elseif ($current_hour >= 17 && $current_hour < 21) {
    $greeting = "Good Evening 🌇";
} else {
    $greeting = "Good Night 🌙";
}

// Ensure $_SESSION['admin'] is set before echoing it
if (isset($_SESSION['admin'])) {
    echo "<h4>{$greeting}, {$_SESSION['admin']}!</h4>";
} else {
    echo "<h4>Welcome, Guest!</h4>";  // In case admin session is not set
}
?>

    </div>

    <div>
        <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="btn btn-sm">Logout</a>
        <?php if($page!='dashboard'){ ?>
            <a href="import.php?page=<?= $page ?>" class="btn btn-sm">Import</a>
            <a href="export.php?page=<?= $page ?>" class="btn btn-sm">Export</a>
        <?php } ?>
    </div>
</div>

<!-- Sidebar -->
<!-- Sidebar -->
<?php include 'sidebar.php'?>
  <!-- End Sidebar -->

<div class="content">

  <!-- General Settings -->

<?php if($page=='settings_general'){ ?>
<div class="table-container">
  <div class="table-header">
    <div class="table-title"><i class="bi bi-sliders me-2"></i> General Settings</div>
  </div>
  <div style="padding: 20px 24px;">
    <form method="POST" action="save_settings.php">
      <input type="hidden" name="settings_type" value="general">
      <input type="hidden" name="admin_id" value="<?= $admin_id ?>">

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Timezone</label>
          <select class="form-select" name="timezone">
            <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
            <option value="America/New_York">America/New_York (EST)</option>
            <option value="Europe/London">Europe/London (GMT)</option>
            <option value="Asia/Dubai">Asia/Dubai (GST)</option>
            <option value="Australia/Sydney">Australia/Sydney (AEST)</option>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Currency</label>
          <select class="form-select" name="currency">
            <option value="INR">INR - Indian Rupee (₹)</option>
            <option value="USD">USD - US Dollar ($)</option>
            <option value="EUR">EUR - Euro (€)</option>
            <option value="GBP">GBP - British Pound (£)</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Date Format</label>
        <select class="form-select" name="date_format">
          <option value="Y-m-d">YYYY-MM-DD (2024-01-15)</option>
          <option value="d-m-Y">DD-MM-YYYY (15-01-2024)</option>
          <option value="m/d/Y">MM/DD/YYYY (01/15/2024)</option>
          <option value="d M Y">DD Mon YYYY (15 Jan 2024)</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i> Save Settings</button>
    </form>
  </div>
</div>
<?php } ?>


<!-- Logo & Branding -->
<?php if($page=='settings_branding'){ ?>
<div class="table-container">
  <div class="table-header">
    <div class="table-title"><i class="bi bi-palette-fill me-2"></i> Logo & Branding Customization</div>
  </div>
  <div style="padding: 20px 24px;">
    <form method="POST" action="save_settings.php" enctype="multipart/form-data">
      <input type="hidden" name="settings_type" value="branding">
      <input type="hidden" name="admin_id" value="<?= $admin_id ?>">

      <div class="mb-3">
        <label class="form-label fw-semibold">Company Name</label>
        <input type="text" class="form-control" name="company_name" placeholder="UltraServe" value="UltraServe">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Upload Logo</label>
        <input type="file" class="form-control" name="logo" accept="image/*">
        <small class="text-muted">Recommended size: 200x50px (PNG, JPG, SVG)</small>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Primary Color</label>
          <input type="color" class="form-control form-control-color w-100" name="primary_color" value="#3b82f6">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Secondary Color</label>
          <input type="color" class="form-control form-control-color w-100" name="secondary_color" value="#8b5cf6">
        </div>
      </div>

      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i> Save Branding</button>
    </form>
  </div>
</div>
<?php } ?>

<!-- Auto-Backup Settings -->
<?php if($page=='settings_backup'){ ?>
<div class="table-container">
  <div class="table-header">
    <div class="table-title"><i class="bi bi-cloud-arrow-up me-2"></i> Auto-Backup Settings</div>
  </div>
  <div style="padding: 20px 24px;">
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
          <select class="form-select" name="backup_frequency">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Backup Time</label>
          <input type="time" class="form-control" name="backup_time" value="02:00">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Backup Location</label>
        <input type="text" class="form-control" name="backup_location" placeholder="/var/backups/" value="/backups/">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Keep Backups For</label>
        <select class="form-select" name="backup_retention">
          <option value="7">7 Days</option>
          <option value="30" selected>30 Days</option>
          <option value="90">90 Days</option>
          <option value="365">1 Year</option>
        </select>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i> Save Backup Settings</button>
        <button type="button" class="btn btn-success"><i class="bi bi-cloud-upload me-2"></i> Backup Now</button>
      </div>
    </form>
  </div>
</div>
<?php } ?>


<!-- System Logs -->
<?php if($page=='settings_logs'){ ?>
<div class="table-container">
  <div class="table-header">
    <div class="table-title"><i class="bi bi-journal-text me-2"></i> System Logs</div>
    <button class="btn btn-sm btn-danger"><i class="bi bi-trash me-1"></i> Clear All Logs</button>
  </div>
  <div style="overflow-x: auto;">
    <table class="table">
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
          <td><span class="badge bg-info"><?= $log['action'] ?></span></td>
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
<div class="table-container">
  <div class="table-header">
    <div class="table-title"><i class="bi bi-database me-2"></i> Data Retention Policy</div>
  </div>
  <div style="padding: 20px 24px;">
    <form method="POST" action="save_settings.php">
      <input type="hidden" name="settings_type" value="retention">
      <input type="hidden" name="admin_id" value="<?= $admin_id ?>">

      <div class="mb-3">
        <label class="form-label fw-semibold">Delete Expired Domains Older Than</label>
        <select class="form-select" name="retention_period">
          <option value="never">Never Delete</option>
          <option value="90">3 Months after expiry</option>
          <option value="180">6 Months after expiry</option>
          <option value="365" selected>1 Year after expiry</option>
          <option value="730">2 Years after expiry</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Delete Activity Logs Older Than</label>
        <select class="form-select" name="logs_retention">
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

      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i> Save Retention Policy</button>
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
<div class="row mb-4 g-3">
<div class="col-lg-3 col-md-6">
    <div class="stat-card" onclick="window.location='dashboard.php?page=hosting_domain&filter=expiring'">
        <div class="stat-badge"><?= $expiringSoonResult['expiring'] ?></div>
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #fca5a5 0%, #fecaca 100%); color: #dc2626;">
            <i class="bi bi-exclamation-circle"></i>
        </div>
        <div class="stat-card-label">Domains Expiring Soon</div>
        <div class="stat-card-value"><?= $expiringSoonResult['expiring'] ?></div>
        <div class="stat-card-sub">Next 30 days</div>
    </div>
</div>
<div class="col-lg-3 col-md-6">
    <div class="stat-card" onclick="window.location='dashboard.php?page=hosting_domain&admin_id=<?= $_SESSION['admin_id'] ?>&filter=all'">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #93c5fd 0%, #bfdbfe 100%); color: #1e40af;">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-card-label">Total Clients</div>
        <div class="stat-card-value"><?= $totalClientsResult['total'] ?></div>
        <div class="stat-card-sub">All domains</div>
    </div>
</div>
<div class="col-lg-3 col-md-6">
    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #86efac 0%, #bbf7d0 100%); color: #166534;">
            <i class="bi bi-diagram-3"></i>
        </div>
        <div class="stat-card-label">Active Providers</div>
        <div class="stat-card-value">
         <?php
$admin_id = $_SESSION['admin_id']; // make sure this is set

$prov_count = 0;
$tempResult = mysqli_query($conn, "SELECT COUNT(DISTINCT purchased_from) as count FROM hosting_domain WHERE admin_id = $admin_id");
$tempRow = mysqli_fetch_assoc($tempResult);
echo $tempRow['count'];
?>

        </div>
        <div class="stat-card-sub">Hosting providers</div>
    </div>
</div>
<div class="col-lg-3 col-md-6">
    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #fbbf24 0%, #fcd34d 100%); color: #92400e;">
            <i class="bi bi-graph-up"></i>
        </div>
        <div class="stat-card-label">Recent Activities</div>
        <div class="stat-card-value"><?= count($activityLogs) ?></div>
        <div class="stat-card-sub">Latest logs</div>
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
<div class="row mb-4 g-3">
  <div class="col-lg-6">
    <div class="chart-container">
      <div class="chart-title">Monthly Registrations</div>
      <canvas id="monthlyRegistrations" class="chart-small"></canvas>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="chart-container">
      <div class="chart-title">Domains by Provider</div>
      <canvas id="providerUsage" class="chart-small"></canvas>
    </div>
  </div>
</div>



<!-- Activity Logs -->
<div class="table-container">
  <div class="table-header">
    <div class="table-title">Recent Activity Logs</div>
  </div>
  <div style="overflow-x: auto;">
    <table class="table">
      <thead>
        <tr><th>Admin</th><th>Action</th><th>Table</th><th>Record ID</th><th>Time</th></tr>
      </thead>
      <tbody>
      <?php foreach($activityLogs as $log){ ?>
      <tr>
      <td><?= $log['admin_name'] ?></td>
      <td><?= $log['action'] ?></td>
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
<?php if($page=='hosting_domain'){ ?>
<div class="table-container">
  <div class="table-header">

  </div>


  <div style="overflow-x: auto;">
    <table class="table table-striped">
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
      // --- Calculate days left for BOTH domain and hosting ---
      $today = new DateTime();

      // Domain Expiry Calculation
      $domainExpiryDate = new DateTime($row['expiry_date']);
      $domainInterval = $today->diff($domainExpiryDate);
      $domainDaysLeft = (int)$domainInterval->format('%r%a');

      // Hosting Expiry Calculation
      $hostingExpiryDate = new DateTime($row['hosting_expiry_date']);
      $hostingInterval = $today->diff($hostingExpiryDate);
      $hostingDaysLeft = (int)$hostingInterval->format('%r%a');

      foreach($show_columns as $col){
      ?>
      <td>
        <?php
            // Make the domain name a link to its website
            if ($col == 'domain_name') {
                $domain_url = 'http://' . htmlspecialchars($row[$col]);
                echo '<a href="' . $domain_url . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($row[$col]) . ' <i class="bi bi-box-arrow-up-right small"></i></a>';
            } else {
                echo htmlspecialchars($row[$col]);
            }
        ?>
      </td>
      <?php
          // --- Display the "Domain Days Left" cell ---
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

          // --- Display the "Hosting Days Left" cell ---
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

      <td class="action-icons">
        <a href="view.php?id=<?= $row['id'] ?>" class="text-info">
            <i class="bi bi-eye-fill text-success"></i>
        </a>
        <a href="#" data-id="<?= $row['id'] ?>" class="edit-btn text-primary">
            <i class="bi bi-pencil-square"></i>
        </a>
        <a href="#" data-id="<?= $row['id'] ?>" class="delete-btn">
            <i class="bi bi-trash-fill text-danger"></i>
        </a>
      </td>
      <td>
      <?php if(isset($row['email']) && !empty($row['email'])): ?>
        <a href="send_email.php?email=<?= $row['email'] ?>" class="btn btn-email btn-sm">Send Email</a>
      <?php else: ?> - <?php endif; ?>
      </td>
      </tr>
      <?php } ?>
      <?php if(mysqli_num_rows($result)==0){ ?>
      <tr><td colspan="<?= count($show_columns)+4 ?>" class="text-center">No records found</td></tr>
      <?php } ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if($page=='hosting_domain' && $total_pages>1){
  $filterQuery = isset($_GET['filter']) ? '&filter='.$_GET['filter'] : '';
  ?>
  <nav>
  <ul class="pagination justify-content-center">
      <li class="page-item <?= ($page_no <= 1) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=hosting_domain<?= $filterQuery ?>&pageno=<?= $page_no-1 ?>">Previous</a>
      </li>
      <?php for($i=1; $i<=$total_pages; $i++){ ?>
      <li class="page-item <?= ($i==$page_no)?'active':'' ?>">
          <a class="page-link" href="?page=hosting_domain<?= $filterQuery ?>&pageno=<?= $i ?>"><?= $i ?></a>
      </li>
      <?php } ?>
      <li class="page-item <?= ($page_no >= $total_pages) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=hosting_domain<?= $filterQuery ?>&pageno=<?= $page_no+1 ?>">Next</a>
      </li>
  </ul>
  </nav>
  <?php } ?>
</div>
<?php } ?>




<!-- Profile Table -->
<?php if($page=='profile'){ ?>
<div class="table-container">
  <div class="table-header">
    <div class="table-title">Admin Profile</div>
  </div>
  <div style="overflow-x: auto;">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th><th>Username</th><th>Email</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?= htmlspecialchars($profile['id']) ?></td>
          <td><?= htmlspecialchars($profile['username']) ?></td>
          <td><?= htmlspecialchars($profile['email']) ?></td>
          <td class="action-icons">
            <a href="edit.php?table=admin&id=<?= $profile['id'] ?>"><i class="bi bi-pencil-square"></i></a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<?php } ?>

<!-- Calendar View -->
<?php if($page=='calendar'){ ?>
<div class="table-container">
  <div class="table-header">
    <div class="table-title">Calendar View</div>
  </div>
  <iframe src="https://calendar.google.com/calendar/embed?src=94dd21dca79e3a1bc4d84f268d51a00b84cb6bd9767100896953e362a7a77116%40group.calendar.google.com&ctz=Asia%2FKolkata" style="border: 0" width="100%" height="600" frameborder="0" scrolling="no"></iframe>
</div>
<?php } ?>

<!-- Theme & Layout -->
<?php if($page=='theme'){ ?>
<div class="row">
  <div class="col-lg-8">
    <div class="table-container">
      <div class="table-header">
        <div class="table-title">Theme & Layout</div>
      </div>
      <div style="padding: 20px 24px;">
        <p class="theme-text" style="margin-bottom: 16px;">Choose a theme for the dashboard:</p>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="form-check p-3" style="background: #f9fafb; border-radius: 12px; border: 1px solid #e5e7eb;">
              <input class="form-check-input" type="radio" name="themeColor" id="themeLight" value="light">
              <label class="form-check-label" for="themeLight">
                <strong>Default</strong>
                <small class="d-block" style="color: #6b7280;">Clean and professional</small>
              </label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-check p-3" style="background: #f9fafb; border-radius: 12px; border: 1px solid #e5e7eb;">
              <input class="form-check-input" type="radio" name="themeColor" id="themeDark" value="dark">
              <label class="form-check-label" for="themeDark">
                <strong>Dark Mode</strong>
                <small class="d-block" style="color: #6b7280;">Easy on the eyes</small>
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
<h4 class="mb-4">Help Center</h4>
<div class="row g-4">

  <?php
  $help_cards = [
    ['title'=>'Add Hosting/Domain','text'=>'Go to "Add Hosting & Domain" and fill the form to add a new client domain or hosting details.'],
    ['title'=>'Import Data','text'=>'Click on "Import" to upload CSV or Excel files containing hosting/domain records.'],
    ['title'=>'Export Data','text'=>'Click on "Export" to download all hosting/domain records in CSV or Excel format.'],
    ['title'=>'Login','text'=>'Use your admin credentials on the login page to access the dashboard.'],
    ['title'=>'Logout','text'=>'Click the "Logout" button in the header to securely exit the admin dashboard.'],
    ['title'=>'Profile Management','text'=>'Go to "Profile" to view or edit admin account details like username and email.'],
    ['title'=>'Dashboard Overview','text'=>'The dashboard shows analytics, expiring domains, provider stats, and recent activity logs.'],
    ['title'=>'Theme & Layout','text'=>'Go to "Theme & Layout" to choose Light, Dark, or Custom theme for the dashboard.'],
    ['title'=>'Filters & Analytics','text'=>'Use filters to view expiring domains or by provider. See charts and analytics for quick insights.']
  ];

  foreach($help_cards as $card){
  ?>
  <div class="col-md-4">
    <div class="help-card">
      <h5><?= $card['title'] ?></h5>
      <p><?= $card['text'] ?></p>
    </div>
  </div>
  <?php } ?>

</div>
<?php } ?>


<!-- System Info -->
<?php if($page=='system_info'){ ?>
<h4 class="mb-4">System Info</h4>
<div class="row g-4">

  <?php
  $system_info = [
      ['title'=>'PHP Version','value'=>phpversion()],
      ['title'=>'MySQL Version','value'=>mysqli_get_server_info($conn)],
      ['title'=>'Server OS','value'=>php_uname()],
      ['title'=>'Current User','value'=>get_current_user()]
  ];

  foreach($system_info as $info){
  ?>
  <div class="col-md-3">
    <div class="system-card">
      <h5><?= $info['title'] ?></h5>
      <p><?= $info['value'] ?></p>
    </div>
  </div>
  <?php } ?>

</div>
<?php } ?>

</div>

<!-- REPLACED JAVASCRIPT BLOCK -->
<script src="js/dashboard.js">
// Function to apply theme by adding/removing a class from the body

</script>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if($page=='dashboard'){
$admin_id = $_SESSION['admin_id']; // logged-in admin
?>

<script>

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
            label: 'New Domains per Month',
            data: [<?= implode(',',$monthCounts) ?>],
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.08)',
            fill:true,
            tension:0.3,
            pointBackgroundColor: '#3b82f6',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:true,
        plugins:{ legend:{ display:false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.1)' } },
            x: { grid: { display: false } }
        }
    }
});

// Provider Usage Pie Chart
var ctx2 = document.getElementById('providerUsage').getContext('2d');
var providerUsage = new Chart(ctx2, {
    type:'doughnut',
    data:{
        labels:[<?php
            $providers = mysqli_query($conn, "SELECT purchased_from, COUNT(*) as count FROM hosting_domain WHERE admin_id=$admin_id GROUP BY purchased_from");
            $provLabels=[]; $provCounts=[];
            while($p=mysqli_fetch_assoc($providers)){
                $provLabels[] = "'".$p['purchased_from']."'";
                $provCounts[] = $p['count'];
            }
            echo implode(',',$provLabels);
        ?>],
        datasets:[{
            label:'Domains by Provider',
            data:[<?= implode(',',$provCounts) ?>],
            backgroundColor:['#3b82f6','#8b5cf6','#ec4899','#f59e0b','#10b981','#06b6d4','#6366f1']
        }]
    },
    options:{
        responsive:true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { usePointStyle: true, padding: 12, color: document.body.classList.contains('theme-dark') ? '#d1d5db' : '#333' }
            }
        }
    }
});
</script>
<?php } ?>

</script>

</body>
</html>