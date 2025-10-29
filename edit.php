<?php
session_start();
include('db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$table = isset($_GET['table']) ? $_GET['table'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = $success = '';
$row = null;

// Validate table and id
if (!empty($table) && $id > 0) {
    // Sanitize table name for security (basic example)
    $safe_table = mysqli_real_escape_string($conn, $table);

    $query = "SELECT * FROM `$safe_table` WHERE id=$id";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        $error = "Record not found.";
    }
} else {
    $error = "Invalid request.";
}


if($_SERVER['REQUEST_METHOD'] === 'POST' && $row) {
    $columns = array_keys($row);
    $updates = [];
    foreach($columns as $col) {
        if($col == 'id') continue;
        if(isset($_POST[$col])) {
            $value = mysqli_real_escape_string($conn, $_POST[$col]);
            $updates[] = "`$col`='$value'";
        }
    }

    if (!empty($updates)) {
        $updateQuery = "UPDATE `$safe_table` SET ".implode(",", $updates)." WHERE id=$id";
        if(mysqli_query($conn, $updateQuery)) {
            // Log the activity
            mysqli_query($conn, "INSERT INTO activity_logs (admin_name, action, table_name, record_id) VALUES ('".$_SESSION['admin']."', 'Updated', '$safe_table', $id)");

            $success = "Record updated successfully! Refreshing data...";
            // Re-fetch the data to show the updated values in the form
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
        } else {
            $error = "Error: ".mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UltraServe | Edit Record</title>
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
.form-control:focus {
  border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); outline: none;
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
<!-- Header, Sidebar, etc. -->
<div class="header">
    <div class="d-flex align-items-center">
        <button class="btn me-3" id="sidebarToggle" type="button" style="border: none; font-size: 20px;"><i class="bi bi-list"></i></button>
        <h4>Edit Record</h4>
    </div>
    <div>
        <a href="logout.php" class="btn btn-sm">Logout</a>
    </div>
</div>
<?php include 'sidebar.php'?>

<!-- Main Content -->
<div class="content">
  <div class="container-fluid">
    <?php if($success) echo "<div class='alert alert-success'><i class='bi bi-check-circle me-2'></i>$success</div>"; ?>
    <?php if($error) echo "<div class='alert alert-danger'><i class='bi bi-exclamation-circle me-2'></i>$error</div>"; ?>

    <?php if ($row): ?>
    <div class="form-container">
        <div class="form-title">
            <i class="bi bi-pencil-square"></i> Editing Record #<?= htmlspecialchars($row['id']) ?> from '<?= htmlspecialchars($table) ?>'
        </div>
        <form method="POST">
            <div class="row">
                <?php foreach($row as $key => $value) {
                    // Skip the ID field, as it should not be editable
                    if($key == 'id') continue;

                    // Determine input type based on column name
                    $inputType = 'text';
                    if (str_contains($key, 'date')) {
                        $inputType = 'date';
                        $displayValue = !empty($value) ? date('Y-m-d', strtotime($value)) : '';
                    } elseif (str_contains($key, 'email')) {
                        $inputType = 'email';
                        $displayValue = htmlspecialchars($value);
                    } else {
                        $displayValue = htmlspecialchars($value);
                    }
                ?>
                <div class="col-md-6 mb-3">
                    <label for="<?= $key ?>" class="form-label"><?= ucwords(str_replace('_',' ',$key)) ?></label>
                    <input type="<?= $inputType ?>" name="<?= $key ?>" id="<?= $key ?>" value="<?= $displayValue ?>" class="form-control" required>
                </div>
                <?php } ?>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-submit"><i class="bi bi-check-lg me-1"></i> Update Record</button>
                <a href="dashboard.php?page=hosting_domain" class="btn btn-secondary"><i class="bi bi-x-lg me-1"></i> Back to Table</a>
            </div>
        </form>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Standard theme and sidebar toggle script
function applyTheme(mode) { document.body.classList.toggle('theme-dark', mode === 'dark'); }
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    if(sidebarToggle) {
        if (localStorage.getItem('sidebarState') === 'collapsed') document.body.classList.add('sidebar-collapsed');
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarState', document.body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
        });
    }
    applyTheme(localStorage.getItem('dashboardTheme') || 'light');
});
</script>

</body>
</html>