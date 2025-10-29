<?php
session_start();
include('db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Fetch subscription history
$query = "SELECT us.id, p.plan_name, p.plan_type, p.price, us.status, us.start_date, us.end_date, us.amount_paid
          FROM user_subscriptions us
          JOIN pricing_plans p ON us.plan_id = p.id
          WHERE us.admin_id = ?
          ORDER BY us.start_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upgrade | Pricing Plans</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="css/dashboard.css" rel="stylesheet">
<link rel="icon" type="image/png" sizes="32x32" href="img/logo.png">

<!-- Apple devices -->
<link rel="apple-touch-icon" href="img/logo.png">

<!-- For older browsers -->
<link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
<style>

</style>
</head>
<body>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-box-arrow-right me-2"></i> Confirm Logout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="modal-icon-wrapper mb-3">
          <i class="bi bi-box-arrow-right"></i>
        </div>
        <p class="mb-4">Are you sure you want to log out?</p>
        <div class="d-flex justify-content-center gap-3">
          <a href="logout.php" class="btn btn-danger">
            <i class="bi bi-check-lg me-1"></i> Yes, Logout
          </a>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i> Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Header -->
<div class="header">
  <div class="greeting-wrapper">
    <button class="btn-icon me-3" id="sidebarToggle" type="button">
      <i class="bi bi-list"></i>
    </button>
    <div>
      <div class="greeting-text">Upgrade</div>
      <div class="greeting-name">Transaction History</div>
    </div>
  </div>
  <div class="header-actions">
    <button class="btn-icon" id="themeToggle">
      <i class="bi bi-moon"></i>
    </button>
    <button class="btn-outline" data-bs-toggle="modal" data-bs-target="#logoutModal">
      <i class="bi bi-box-arrow-right me-2"></i>
      <span>Logout</span>
    </button>
  </div>
</div>

<!-- Sidebar -->
<?php include 'sidebar.php'?>

<!-- Content -->
<div class="content">
<div class="container mt-5">
    <h2>Upgrade History</h2>
    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>#</th>
                <th>Plan Name</th>
                <th>Plan Type</th>
                <th>Price (₹)</th>
                <th>Status</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Amount Paid</th>

            </tr>
        </thead>
        <tbody>
            <?php
            $count = 1;
            while($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><?php echo $count++; ?></td>
                <td><?php echo $row['plan_name']; ?></td>
                <td><?php echo ucfirst($row['plan_type']); ?></td>
                <td><?php echo number_format($row['price'], 0); ?></td>
                <td><?php echo ucfirst($row['status']); ?></td>
                <td><?php echo $row['start_date']; ?></td>
                <td><?php echo $row['end_date'] ? $row['end_date'] : '-'; ?></td>
                <td><?php echo $row['amount_paid']; ?></td>

            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
            </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Theme Toggle
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    const currentTheme = localStorage.getItem('theme') || 'light';

    if (currentTheme === 'dark') {
        document.body.classList.add('theme-dark');
        themeToggle.innerHTML = '<i class="bi bi-sun"></i>';
    }

    themeToggle.addEventListener('click', function() {
        document.body.classList.toggle('theme-dark');
        const isDark = document.body.classList.contains('theme-dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        this.innerHTML = isDark ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon"></i>';
    });

    // Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (localStorage.getItem('sidebarState') === 'collapsed') {
        document.body.classList.add('sidebar-collapsed');
    }
    sidebarToggle.addEventListener('click', function() {
        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebarState',
            document.body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
    });
});

function selectPlan(planId, planName) {
    // You can implement payment gateway integration here
    if(confirm(`Do you want to upgrade to ${planName}?`)) {
        window.location.href = `process-upgrade.php?plan=${planId}`;
    }
}
</script>

</body>
</html>