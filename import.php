<?php
session_start();
include('db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$success = '';
$errors = [];
$importedCount = 0;
$skippedCount = 0;

// ✅ Helper function to fix date format
function fixDate($date) {
    if (empty($date)) return '';
    $date = str_replace('/', '-', trim($date)); // handle both / and -
    $timestamp = strtotime($date);
    if ($timestamp && $timestamp > 0) {
        return date('Y-m-d', $timestamp); // convert to MySQL format
    }
    return ''; // invalid → blank
}

if (isset($_POST['import'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
        $fileName = $_FILES['csv_file']['tmp_name'];
        $file = fopen($fileName, "r");

        $csv_headers = fgetcsv($file, 10000, ",");
        if ($csv_headers === false) {
            $errors[] = "Could not read the header from the CSV file. The file might be empty or corrupted.";
        } else {
            $required_db_columns = [
                'client_name', 'company_name', 'domain_name', 'purchased_from',
                'registration_date', 'expiry_date', 'hosting_purchased_from',
                'hosting_registration_date', 'hosting_expiry_date', 'email'
            ];

            $trimmed_csv_headers = array_map('trim', $csv_headers);
            $missing_columns = array_diff($required_db_columns, $trimmed_csv_headers);

            if (!empty($missing_columns)) {
                $errors[] = "Import failed. Missing columns: <strong>" . implode(', ', $missing_columns) . "</strong>";
            } else {
                $header_map = array_flip($trimmed_csv_headers);

                while (($data_row = fgetcsv($file, 10000, ",")) !== FALSE) {
                    $domain = isset($data_row[$header_map['domain_name']]) ? mysqli_real_escape_string($conn, $data_row[$header_map['domain_name']]) : '';

                    // Check duplicate domain for this admin
                    $check = mysqli_query($conn, "SELECT * FROM hosting_domain WHERE domain_name='$domain' AND admin_id=$admin_id");
                    if (mysqli_num_rows($check) > 0) {
                        $skippedCount++;
                        continue;
                    }

                    $insert_cols = [];
                    $insert_vals = [];

                    foreach ($required_db_columns as $db_col) {
                        $csv_index = $header_map[$db_col];
                        $value = isset($data_row[$csv_index]) ? mysqli_real_escape_string($conn, $data_row[$csv_index]) : '';

                        // ✅ DATE FIX ADDED HERE
                        if (in_array($db_col, ['registration_date', 'expiry_date', 'hosting_registration_date', 'hosting_expiry_date'])) {
                            $value = fixDate($value);
                        }

                        if ($value === '') {
                            $insert_vals[] = "NULL";
                        } else {
                            $insert_vals[] = "'$value'";
                        }

                        $insert_cols[] = "`$db_col`";
                    }

                    // Add admin_id for foreign key
                    $insert_cols[] = "admin_id";
                    $insert_vals[] = $admin_id;

                    $query = "INSERT INTO hosting_domain (" . implode(', ', $insert_cols) . ") VALUES (" . implode(', ', $insert_vals) . ")";
                    if (mysqli_query($conn, $query)) {
                        $importedCount++;
                        $lastId = mysqli_insert_id($conn);
                        mysqli_query($conn, "INSERT INTO activity_logs (admin_name, action, table_name, record_id) VALUES ('".$_SESSION['admin']."', 'Imported row', 'hosting_domain', $lastId)");
                    } else {
                        $errors[] = "Error importing domain '$domain': " . mysqli_error($conn);
                    }
                }

                if ($importedCount > 0 || $skippedCount > 0) {
                    $success = "Import completed. Imported: $importedCount record(s), Skipped: $skippedCount duplicate(s).";
                } elseif (empty($errors)) {
                    $errors[] = "The selected file was empty or contained no new records.";
                }
            }
        }
        fclose($file);
    } else {
        $errors[] = "No file selected or there was an error uploading the file.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UltraServe | Import Data</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="icon" type="image/png" sizes="32x32" href="img/logo.png">

<!-- Apple devices -->
<link rel="apple-touch-icon" href="img/logo.png">

<!-- For older browsers -->
<link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
<link href="css/dashboard.css" rel="stylesheet">
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
.form-control {
  border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px;
  font-size: 13px; transition: all 0.2s ease; background: white; color: #1f2937;
}
.btn-submit {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none;
  padding: 10px 24px; font-size: 13px; border-radius: 8px; font-weight: 600;
}
.btn-secondary {
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

</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-box-arrow-right me-2"></i> Confirm Logout</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <p class="mb-3 fw-semibold">Are you sure you want to log out?</p>
        <div class="d-flex justify-content-center gap-3">
          <a href="logout.php" class="btn btn-danger px-4">Logout</a>
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
    <h4>Import Data</h4>
  </div>
  <div>

    <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="btn btn-sm">Logout</a>
  </div>
</div>

<!-- Sidebar -->
<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-logo">
    <i class="bi bi-lightning-fill"></i> UltraServe
  </div>

  <a href="dashboard.php?page=dashboard" class="<?= $page=='dashboard'?'active':'' ?>"><i class="bi bi-house-door me-2"></i> Dashboard</a>
  <a href="dashboard.php?page=hosting_domain" class="<?= $page=='hosting_domain'?'active':'' ?>"><i class="bi bi-globe me-2"></i> Hosting & Domain</a>

  <a href="hosting-domain-form.php"><i class="bi bi-plus-circle me-2"></i> Add Hosting & Domain</a>
  <a href="import.php?page=<?= $page ?>"><i class="bi bi-file-earmark-arrow-up me-2"></i> Import</a>
  <a href="export.php"><i class="bi bi-file-earmark-arrow-down me-2"></i> Export</a>
  <a href="dashboard.php?page=calendar"><i class="bi bi-calendar3 me-2"></i> Calendar View</a>

  <!-- Bootstrap Settings Dropdown -->




  <a href="dashboard.php?page=theme"><i class="bi bi-palette me-2"></i> Theme & Layout</a>
  <a href="dashboard.php?page=help"><i class="bi bi-question-circle me-2"></i> Help Center</a>
  <a href="dashboard.php?page=system_info"><i class="bi bi-info-circle me-2"></i> System Info</a>

  <a href="dashboard.php?page=profile" class="<?= $page=='profile'?'active':'' ?>"><i class="bi bi-person me-2"></i> Profile</a>

  <div class="dropdown">
    <a class="dropdown-toggle <?= in_array($page, ['settings_general', 'settings_branding', 'settings_backup', 'settings_logs', 'settings_retention'])?'active':'' ?>"
       href="#"
       role="button"
       id="settingsDropdown"
       data-bs-toggle="dropdown"
       aria-expanded="false">
      <i class="bi bi-gear me-2"></i> Settings
      <i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
      <li>
        <a class="dropdown-item <?= $page=='settings_general'?'active':'' ?>" href="dashboard.php?page=settings_general">
          <i class="bi bi-sliders me-2"></i> General Settings
        </a>
      </li>
      <li>
        <a class="dropdown-item <?= $page=='settings_branding'?'active':'' ?>" href="dashboard.php?page=settings_branding">
          <i class="bi bi-palette-fill me-2"></i> Logo & Branding
        </a>
      </li>
      <li>
        <a class="dropdown-item <?= $page=='settings_backup'?'active':'' ?>" href="dashboard.php?page=settings_backup">
          <i class="bi bi-cloud-arrow-up me-2"></i> Auto-Backup Settings
        </a>
      </li>
      <li>
        <a class="dropdown-item <?= $page=='settings_logs'?'active':'' ?>" href="dashboard.php?page=settings_logs">
          <i class="bi bi-journal-text me-2"></i> System Logs
        </a>
      </li>
      <li>
        <a class="dropdown-item <?= $page=='settings_retention'?'active':'' ?>" href="dashboard.php?page=settings_retention">
          <i class="bi bi-database me-2"></i> Data Retention Policy
        </a>
      </li>
    </ul>
  </div>

  <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
    <i class="bi bi-box-arrow-right me-2"></i> Logout
  </a>

</div>

<!-- Content -->
<div class="content">
  <div class="container-fluid">
    <div class="form-container">
      <div class="form-title">
        <i class="bi bi-cloud-upload"></i> Import CSV into Hosting & Domain
      </div>

      <?php if($success) echo "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>$success</div>"; ?>
      <?php if(!empty($errors)) {
          foreach($errors as $err) echo "<div class='alert alert-danger'><i class='bi bi-exclamation-circle me-2'></i>$err</div>";
      } ?>

      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label for="csv_file" class="form-label">Select CSV File</label>
          <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
          <div class="form-text mt-2">
            <strong>Important:</strong> The first row of your CSV must be a header with exact columns:
            <code>client_name, company_name, domain_name, purchased_from, registration_date, expiry_date, hosting_purchased_from, hosting_registration_date, hosting_expiry_date, email, notes</code>
          </div>
        </div>
        <div class="d-flex gap-2 mt-4">
          <button type="submit" name="import" class="btn btn-submit"><i class="bi bi-file-earmark-arrow-up me-1"></i> Import</button>
          <a href="dashboard.php?page=hosting_domain" class="btn btn-secondary">Back</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>

</body>
</html>
