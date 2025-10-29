<?php
session_start();
include('db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$table = 'hosting_domain';
$admin_id = $_SESSION['admin_id'];

// Get all available column names from the table to display in the form
$columns_result = mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
$all_columns = [];
while ($row = mysqli_fetch_assoc($columns_result)) {
    $all_columns[] = $row['Field'];
}

// EXPORT LOGIC
if (isset($_POST['export_csv'])) {
    if (empty($_POST['columns']) || !is_array($_POST['columns'])) {
        die("Error: No columns were selected for export.");
    }

    $selected_columns = $_POST['columns'];
    $safe_columns = array_intersect($selected_columns, $all_columns);

    if (empty($safe_columns)) {
        die("Error: Invalid columns selected.");
    }

    // Build query dynamically
    $columns_list = implode(", ", array_map(function($col){ return "`$col`"; }, $safe_columns));
    $query = "SELECT $columns_list FROM `$table` WHERE admin_id = $admin_id";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }

    // CSV headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename='.$table.'_export_'.date('Y-m-d').'.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, $safe_columns);

    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UltraServe | Export Data</title>
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
.btn-submit {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none;
  padding: 10px 24px; font-size: 13px; border-radius: 8px; font-weight: 600;
}
.btn-secondary {
  background: white; color: #6b7280; border: 1px solid #d1d5db; padding: 10px 24px;
  font-size: 13px; border-radius: 8px; font-weight: 600; text-decoration: none;
}
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
<div class="modal fade" id="logoutModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="bi bi-box-arrow-right me-2"></i> Confirm Logout</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body text-center"><p class="mb-3 fw-semibold">Are you sure you want to log out?</p><div class="d-flex justify-content-center gap-3"><a href="logout.php" class="btn btn-danger px-4">Logout</a><button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button></div></div></div></div></div>

<!-- Header -->
<div class="header">
    <div class="d-flex align-items-center">
        <button class="btn me-3" id="sidebarToggle" type="button" style="border: none; font-size: 20px;"><i class="bi bi-list"></i></button>
        <h4>Export Data</h4>
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
    <div class="form-container">
        <div class="form-title">
            <i class="bi bi-cloud-download"></i> Export Hosting & Domain Data
        </div>
        <p>Select the columns you wish to export and click the button to download the CSV file.</p>

        <form method="POST">
            <h5 class="mt-4 mb-3">Select Columns to Export:</h5>
            <div class="row mb-3">
                <?php foreach ($all_columns as $column): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="columns[]" value="<?= htmlspecialchars($column) ?>" id="col_<?= htmlspecialchars($column) ?>" checked>
                            <label class="form-check-label" for="col_<?= htmlspecialchars($column) ?>">
                                <?= ucwords(str_replace('_', ' ', $column)) ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" name="export_csv" class="btn btn-submit"><i class="bi bi-file-earmark-arrow-down me-1"></i> Export Data as CSV</button>
                <a href="dashboard.php?page=hosting_domain" class="btn btn-secondary">Back</a>
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