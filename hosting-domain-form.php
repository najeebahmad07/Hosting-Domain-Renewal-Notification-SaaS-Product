<?php
session_start();
include('db.php');

$error = '';
$success = '';

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Check current plan and domain limit
$current_plan_query = "SELECT p.plan_type, p.domains_limit
                       FROM user_subscriptions us
                       JOIN pricing_plans p ON us.plan_id = p.id
                       WHERE us.admin_id = ? AND us.status = 'active'
                       ORDER BY us.id DESC LIMIT 1";
$stmt = $conn->prepare($current_plan_query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$current_plan = $result->fetch_assoc();
$domains_limit = $current_plan ? $current_plan['domains_limit'] : 30; // default 30 for free

// Count current domains added
$count_query = "SELECT COUNT(*) as total FROM hosting_domain WHERE admin_id = ?";
$stmt2 = $conn->prepare($count_query);
$stmt2->bind_param("i", $admin_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$count = $result2->fetch_assoc()['total'];

// If limit reached, redirect to upgrade page
if ($count >= $domains_limit) {
    echo "<script>alert('You have reached your domain/hosting limit. Please upgrade to add more.'); window.location.href='upgrade.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_name = mysqli_real_escape_string($conn, $_POST['client_name']);
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $domain_name = mysqli_real_escape_string($conn, $_POST['domain_name']);
    $purchased_from = mysqli_real_escape_string($conn, $_POST['purchased_from']);
    $registration_date = $_POST['registration_date'];
    $expiry_date = $_POST['expiry_date'];
    $hosting_name = mysqli_real_escape_string($conn, $_POST['hosting_name']);
    $hosting_registration_date = $_POST['hosting_registration_date'];
    $hosting_expiry_date = $_POST['hosting_expiry_date'];
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    $checkQuery = "SELECT * FROM hosting_domain WHERE domain_name='$domain_name'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $error = "This domain already exists!";
    } else {
        $insertQuery = "INSERT INTO hosting_domain
        (client_name, company_name, domain_name, purchased_from, registration_date, expiry_date,
        hosting_purchased_from, hosting_registration_date, hosting_expiry_date, email, notes, admin_id)
        VALUES
        ('$client_name', '$company_name', '$domain_name', '$purchased_from', '$registration_date', '$expiry_date',
        '$hosting_name', '$hosting_registration_date', '$hosting_expiry_date', '$email', '$notes', '$admin_id')";

        if (mysqli_query($conn, $insertQuery)) {
            // Log the activity
            $admin_name = $_SESSION['admin'];
            $action = 'Created';
            $table_name = 'hosting_domain';
            $record_id = mysqli_insert_id($conn);
            $log_query = "INSERT INTO activity_logs (admin_name, action, table_name, record_id) VALUES ('$admin_name', '$action', '$table_name', '$record_id')";
            mysqli_query($conn, $log_query);

            $success = "Hosting & Domain details added successfully!";
            $_POST = array();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UltraServe | Add Hosting & Domain</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/dashboard.css">
<link rel="icon" type="image/png" sizes="32x32" href="img/logo.png">

<!-- Apple devices -->
<link rel="apple-touch-icon" href="img/logo.png">

<!-- For older browsers -->
<link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
<style>

.form-container {
  background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 30px;
}
.form-title {
  font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 24px;
  display: flex; align-items: center; gap: 10px;
}
.form-title i { color: #3b82f6; font-size: 24px; }
.form-label {
  display: block; font-weight: 600; color: #374151; margin-bottom: 8px;
}
.form-control, .form-select {
  border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px;
  font-size: 13px; transition: all 0.2s ease; background: white; color: #1f2937;
}
.form-control:focus, .form-select:focus {
  border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); outline: none;
}
.btn-submit {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none;
  padding: 10px 24px; font-size: 13px; border-radius: 8px; font-weight: 600;
}
.btn-cancel {
  background: white; color: #6b7280; border: 1px solid #d1d5db; padding: 10px 24px;
  font-size: 13px; border-radius: 8px; font-weight: 600; text-decoration: none;
}
.alert { border-radius: 8px; padding: 12px 16px; margin-bottom: 24px; font-size: 13px; border: none; }
.alert-success { background: #dcfce7; color: #166534; }
.alert-danger { background: #fee2e2; color: #991b1b; }
.modal-content { border-radius: 12px; border: 1px solid #e5e7eb; }
.modal-header { border-bottom: 1px solid #e5e7eb; padding: 20px; }
@media (max-width: 768px) {
  .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); }
  .header { left: 0; } .content { margin-left: 0; }
}

/* --- NEW & IMPROVED STYLES START HERE --- */
.sidebar, .content, .header { transition: margin-left 0.3s ease-in-out, left 0.3s ease-in-out; }
body.sidebar-collapsed .sidebar { margin-left: -260px; }
body.sidebar-collapsed .content,
body.sidebar-collapsed .header { margin-left: 0; left: 0; }
body.theme-dark { background-color: #111827; color: #d1d5db; }
body.theme-dark .header, body.theme-dark .sidebar, body.theme-dark .form-container, body.theme-dark .modal-content {
  background-color: #1f2937; border-color: #374151;
}
body.theme-dark h4, body.theme-dark .sidebar-logo, body.theme-dark .form-title, body.theme-dark .modal-title {
  color: #f9fafb;
}
body.theme-dark p, body.theme-dark small { color: #9ca3af; }
body.theme-dark .sidebar a { color: #d1d5db; }
body.theme-dark .sidebar a:hover { background-color: #374151; color: #ffffff; }
body.theme-dark .sidebar a.active { background-color: #374151; color: #3b82f6; }
body.theme-dark .header .btn, body.theme-dark #sidebarToggle {
  background-color: #374151; color: #d1d5db; border-color: #4b5563;
}
body.theme-dark .btn-cancel {
    background-color: #374151; color: #d1d5db; border-color: #4b5563;
}
body.theme-dark .form-label, body.theme-dark .form-check-label { color: #d1d5db; }
body.theme-dark .form-control, body.theme-dark .form-select {
    background-color: #374151; color: #f9fafb; border-color: #4b5563;
}
body.theme-dark .form-control::placeholder { color: #6b7280; }
body.theme-dark .alert-success { background: #14532d; color: #dcfce7; }
body.theme-dark .alert-danger { background: #991b1b; color: #fee2e2; }

</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="bi bi-box-arrow-right me-2"></i> Confirm Logout</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body text-center"><p class="mb-3 fw-semibold">Are you sure you want to log out?</p><div class="d-flex justify-content-center gap-3"><a href="logout.php" class="btn btn-danger px-4">Logout</a><button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button></div></div></div></div></div>

<!-- Header -->
<div class="header">
    <div class="d-flex align-items-center">
        <button class="btn me-3" id="sidebarToggle" type="button" style="border: none; font-size: 20px;"><i class="bi bi-list"></i></button>
        <h4>Add New Record</h4>
    </div>
    <div>

        <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="btn btn-sm">Logout</a>
    </div>
</div>

<!-- Sidebar -->
<?php include 'sidebar.php'?>

<!-- Content -->
<div class="content">
  <div class="container-fluid">
    <?php if (!empty($success)) echo "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>$success</div>"; ?>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'><i class='bi bi-exclamation-circle me-2'></i>$error</div>"; ?>

    <div class="form-container">
      <div class="form-title"><i class="bi bi-globe"></i> Add New Hosting & Domain</div>
      <form method="POST">
          <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label">Client Name <span class="text-danger">*</span></label><input type="text" name="client_name" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Company Name <span class="text-danger">*</span></label><input type="text" name="company_name" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Domain Name <span class="text-danger">*</span></label><input type="text" name="domain_name" class="form-control" placeholder="example.com" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Purchased From <span class="text-danger">*</span></label><select name="purchased_from" class="form-select" required><option value="">Select Provider</option><option>Hostinger</option><option>GoDaddy</option><option>Bluehost</option><option>Namecheap</option><option>Other</option></select></div>
            <div class="col-md-6 mb-3"><label class="form-label">Domain Registration Date <span class="text-danger">*</span></label><input type="date" name="registration_date" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Domain Expiry Date <span class="text-danger">*</span></label><input type="date" name="expiry_date" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Hosting Provider <span class="text-danger">*</span></label><input type="text" name="hosting_name" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Hosting Registration Date <span class="text-danger">*</span></label><input type="date" name="hosting_registration_date" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Hosting Expiry Date <span class="text-danger">*</span></label><input type="date" name="hosting_expiry_date" class="form-control" required></div>
            <div class="col-md-6 mb-3"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required></div>
            <div class="col-12 mb-3"><label class="form-label">Notes</label><input type="text" name="notes" class="form-control" placeholder="Any additional information..."></div>
          </div>
        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn btn-submit flex-grow-1"><i class="bi bi-plus-circle me-1"></i> Add Record</button>
          <a href="dashboard.php" class="btn btn-cancel flex-grow-1"><i class="bi bi-x-circle me-1"></i> Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Function to apply theme by adding/removing a class from the body
function applyTheme(mode) {
    document.body.classList.toggle('theme-dark', mode === 'dark');
}

document.addEventListener('DOMContentLoaded', function() {
    // --- Sidebar Toggle Logic ---
    const sidebarToggle = document.getElementById('sidebarToggle');
    if(sidebarToggle) {
        if (localStorage.getItem('sidebarState') === 'collapsed') {
            document.body.classList.add('sidebar-collapsed');
        }
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarState', document.body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
        });
    }

    // --- Theme Switcher Logic ---
    const savedTheme = localStorage.getItem('dashboardTheme') || 'light';
    applyTheme(savedTheme);
});
</script>

</body>
</html>
