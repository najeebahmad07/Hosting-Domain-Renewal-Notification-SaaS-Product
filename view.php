<?php
session_start();
include('db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// 1. Get and Sanitize the ID from the URL
$record_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$record = null;
$error = '';

if ($record_id > 0) {
    // 2. Fetch the specific record from the database
    $query = "SELECT * FROM hosting_domain WHERE id = $record_id";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $record = mysqli_fetch_assoc($result);
    } else {
        $error = "Record not found. It may have been deleted.";
    }
} else {
    $error = "Invalid ID provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UltraServe | View Record</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="icon" type="image/png" sizes="32x32" href="img/logo.png">

<!-- Apple devices -->
<link rel="apple-touch-icon" href="img/logo.png">

<!-- For older browsers -->
<link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
<link href="css/dashboard.css" rel="stylesheet">
<style>

.details-container {
  background: white; border-radius: 12px; border: 1px solid #e5e7eb;
}
.details-header {
  padding: 20px 30px; display: flex; justify-content: space-between; align-items: center;
  border-bottom: 1px solid #e5e7eb;
}
.details-title {
  font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 0;
  display: flex; align-items: center; gap: 10px;
}
.details-title i { color: #3b82f6; }
.details-body { padding: 30px; }
.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border: none;
}
.btn-secondary {
  background: white; color: #6b7280; border: 1px solid #d1d5db;
}
.btn-danger { background-color: #ef4444; border: none; }
.alert { border-radius: 8px; padding: 12px 16px; margin-bottom: 24px; font-size: 13px; border: none; }
.alert-danger { background: #fee2e2; color: #991b1b; }
.details-list dt { font-weight: 600; color: #6b7280; }
.details-list dd { color: #1f2937; }


</style>
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
        <button class="btn me-3" id="sidebarToggle" type="button" style="border: none; font-size: 20px;"><i class="bi bi-list"></i></button>
        <h4>Record Details</h4>
    </div>
    <div>

        <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="btn btn-sm">Logout</a>
    </div>
</div>

<!-- Sidebar -->
<?php include 'sidebar.php'?>

<!-- Main Content -->
<div class="content">
  <div class="container-fluid">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php elseif ($record): ?>
        <div class="details-container">
            <div class="details-header">
                <h4 class="details-title">
                    <i class="bi bi-card-list"></i> Details for <?= htmlspecialchars($record['domain_name']) ?>
                </h4>
                <!-- ACTION BUTTONS -->
                <div class="d-flex gap-2">
                    <a href="edit.php?table=hosting_domain&id=<?= $record['id'] ?>" class="btn btn-primary btn-sm" id="editModal"><i class="bi bi-pencil-square me-1"></i> Edit</a>
                    <a href="#" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal"><i class="bi bi-trash-fill me-1"></i> Delete</a>
                    <a href="dashboard.php?page=hosting_domain" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
                </div>
            </div>
            <div class="details-body">
                <dl class="row details-list">
                    <?php
                        // Helper function to calculate and format days left
                        function getDaysLeftStatus($date_string) {
                            if (empty($date_string) || $date_string === '0000-00-00') {
                                return '<span class="text-muted">N/A</span>';
                            }
                            $today = new DateTime();
                            $expiryDate = new DateTime($date_string);
                            $interval = $today->diff($expiryDate);
                            $daysLeft = (int)$interval->format('%r%a');

                            if ($daysLeft < 0) {
                                return '<span class="text-danger fw-bold">Expired</span>';
                            } elseif ($daysLeft <= 30) {
                                return '<span class="text-warning fw-bold">' . $daysLeft . ' days</span>';
                            } else {
                                return '<span class="text-success">' . $daysLeft . ' days</span>';
                            }
                        }

                        // --- Field Definitions ---
                        $fields_to_display = [
                            'id' => 'Record ID', 'client_name' => 'Client Name', 'company_name' => 'Company Name',
                            'domain_name' => 'Domain Name', 'purchased_from' => 'Domain Provider',
                            'registration_date' => 'Domain Registration', 'expiry_date' => 'Domain Expiry',
                            'days_left_domain' => 'Days until Domain Expiry', // New virtual field
                            'hosting_purchased_from' => 'Hosting Provider', 'hosting_registration_date' => 'Hosting Registration',
                            'hosting_expiry_date' => 'Hosting Expiry',
                            'days_left_hosting' => 'Days until Hosting Expiry', // New virtual field
                            'email' => 'Contact Email', 'notes' => 'Notes'
                        ];

                        foreach ($fields_to_display as $db_key => $label) {
                            echo '<dt class="col-sm-3 mb-3">' . $label . '</dt>';
                            echo '<dd class="col-sm-9 mb-3">';

                            // --- Special Handling for new and modified fields ---
                            if ($db_key == 'domain_name') {
                                $domain_url = 'http://' . htmlspecialchars($record['domain_name']);
                                echo '<a href="' . $domain_url . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($record['domain_name']) . ' <i class="bi bi-box-arrow-up-right small"></i></a>';
                            } elseif ($db_key == 'days_left_domain') {
                                echo getDaysLeftStatus($record['expiry_date']);
                            } elseif ($db_key == 'days_left_hosting') {
                                echo getDaysLeftStatus($record['hosting_expiry_date']);
                            } else {
                                echo !empty($record[$db_key]) ? htmlspecialchars($record[$db_key]) : '<span class="text-muted">N/A</span>';
                            }
                            echo '</dd>';
                        }
                    ?>
                </dl>
            </div>
        </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Standard theme and sidebar toggle script
function applyTheme(mode) { document.body.classList.toggle('theme-dark', mode === 'dark'); }
document.addEventListener('DOMContentLoaded', function() {
    // Theme and Sidebar
    applyTheme(localStorage.getItem('dashboardTheme') || 'light');
    const sidebarToggle = document.getElementById('sidebarToggle');
    if(sidebarToggle) {
        if (localStorage.getItem('sidebarState') === 'collapsed') document.body.classList.add('sidebar-collapsed');
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarState', document.body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
        });
    }

    // Modal Delete Button Logic
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        confirmDeleteBtn.href = `delete.php?table=hosting_domain&id=<?= $record_id ?>`;
    }
});
</script>

</body>
</html>