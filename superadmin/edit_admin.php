<?php
session_start();
include('../db.php');

if (!isset($_SESSION['super_admin'])) {
    header("Location: index.php");
    exit();
}

$admin_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$super_admin_id = $_SESSION['super_admin_id'];

// Get admin details
$adminQuery = "SELECT * FROM admin WHERE id = $admin_id AND super_admin_id = $super_admin_id";
$adminResult = mysqli_fetch_assoc(mysqli_query($conn, $adminQuery));

if (!$adminResult) {
    $_SESSION['error'] = "Admin not found!";
    header("Location: superadmin_dashboard.php?page=manage_admins");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if username/email is taken by another admin
    $checkQuery = "SELECT id FROM admin WHERE (username = '$username' OR email = '$email') AND id != $admin_id";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $_SESSION['error'] = "Username or email already exists!";
    } else {
        // Update admin
        if (!empty($password)) {
            $updateQuery = "UPDATE admin SET username = '$username', email = '$email', password = '$password' WHERE id = $admin_id";
        } else {
            $updateQuery = "UPDATE admin SET username = '$username', email = '$email' WHERE id = $admin_id";
        }

        if (mysqli_query($conn, $updateQuery)) {
            // Log activity
            $admin_name = $_SESSION['super_admin'];
            $logQuery = "INSERT INTO activity_logs (admin_id, admin_name, action, table_name, record_id)
                        VALUES ($admin_id, '$admin_name', 'Updated', 'admin', $admin_id)";
            mysqli_query($conn, $logQuery);

            $_SESSION['success'] = "Admin updated successfully!";
            header("Location: superadmin_dashboard.php?page=manage_admins");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update admin: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Admin - <?= htmlspecialchars($adminResult['username']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/superadmin_dashboard.css">
<style>
    body { background: #f9fafb; padding: 20px; }
</style>
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

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card-modern">
                <div class="card-header-modern">
                    <div class="card-title-modern">
                        <i class="bi bi-pencil-fill me-2"></i> Edit Admin
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
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control-modern" name="username"
                                   value="<?= htmlspecialchars($adminResult['username']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control-modern" name="email"
                                   value="<?= htmlspecialchars($adminResult['email']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password</label>
                            <input type="password" class="form-control-modern" name="password"
                                   placeholder="Leave blank to keep current password">
                            <small class="text-muted">Only enter if you want to change the password</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            To change the subscription plan, use the "Manage Plan" button from the admin list.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Update Admin
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
</div></div>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>