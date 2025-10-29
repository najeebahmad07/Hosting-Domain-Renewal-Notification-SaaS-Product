<?php
session_start();
include('db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Get current user's active subscription
$current_plan_query = "SELECT p.plan_type
                       FROM user_subscriptions us
                       JOIN pricing_plans p ON us.plan_id = p.id
                       WHERE us.admin_id = ? AND us.status = 'active'
                       ORDER BY us.id DESC LIMIT 1";
$stmt = $conn->prepare($current_plan_query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$current_plan = $result->fetch_assoc();
$user_plan_type = $current_plan ? $current_plan['plan_type'] : 'basic'; // Default to basic if no subscription

// Fetch all active pricing plans
$plans_query = "SELECT * FROM pricing_plans WHERE is_active = 1 ORDER BY sort_order ASC";
$plans_result = $conn->query($plans_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upgrade | Pricing Plans</title>
<link rel="icon" type="image/png" sizes="32x32" href="img/logo.png">

<!-- Apple devices -->
<link rel="apple-touch-icon" href="img/logo.png">

<!-- For older browsers -->
<link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="css/dashboard.css" rel="stylesheet">
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <script>
function payWithRazorpay(planId, amount, planName) {
    var options = {
        "key": "rzp_live_RYUENDklwmccjb", // 🔑 Replace with your Razorpay key
        "amount": amount * 100, // Convert to paise
        "currency": "INR",
        "name": "UltraServe",
        "description": "Upgrade to " + planName,
        "theme": { "color": "#1e3974" },
        "handler": function (response) {
            // When payment successful, call process-upgrade.php
            window.location.href = "process-upgrade.php?plan=" + planId + "&payment=paid";
        },
        "prefill": {
            "name": "<?php echo $_SESSION['admin']; ?>",
            "email": "<?php echo $_SESSION['admin_email'] ?? ''; ?>"
        }
    };
    var rzp = new Razorpay(options);
    rzp.open();
}
</script>

<style>
.pricing-section {
    max-width: 1200px;
    margin: auto;
    padding: 40px 20px;
}

.pricing-header {
    text-align: center;
    margin-bottom: 50px;
}

.pricing-title {
    font-size: 32px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 12px;
}

.pricing-subtitle {
    font-size: 16px;
    color: var(--text-secondary);
}

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.pricing-card {
    background: var(--bg-primary);
    border-radius: var(--border-radius);
    border: 2px solid var(--border-color);
    padding: 32px;
    position: relative;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
}

.pricing-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
}

.pricing-card.current-plan {
    border-color: var(--primary);
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.05));
}

.plan-badge {
    position: absolute;
    top: -12px;
    right: 20px;
    background: var(--primary);
    color: white;
    padding: 4px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.plan-header {
    text-align: center;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 24px;
}

.plan-name {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.plan-price {
    font-size: 36px;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 4px;
}

.plan-price .currency {
    font-size: 20px;
    font-weight: 600;
}

.plan-price .period {
    font-size: 16px;
    color: var(--text-secondary);
    font-weight: 500;
}

.plan-ideal {
    font-size: 14px;
    color: var(--text-secondary);
    font-style: italic;
}

.plan-features {
    flex-grow: 1;
    margin-bottom: 24px;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 16px;
    font-size: 14px;
    color: var(--text-primary);
}

.feature-icon {
    color: var(--success);
    margin-right: 12px;
    margin-top: 2px;
    flex-shrink: 0;
}

.feature-label {
    font-weight: 600;
    color: var(--text-primary);
    display: block;
    margin-bottom: 2px;
}

.feature-value {
    color: var(--text-secondary);
    font-size: 13px;
}

.plan-action {
    margin-top: auto;
}

.btn-upgrade {
    width: 100%;
    padding: 14px;
    border-radius: var(--border-radius-sm);
    font-weight: 600;
    font-size: 15px;
    border: none;
    transition: var(--transition);
}

.btn-current {
    background: var(--bg-tertiary);
    color: var(--text-secondary);
    cursor: not-allowed;
}

.btn-select {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
}

.btn-select:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
}

.pricing-footer {
    background: var(--bg-secondary);
    border-radius: var(--border-radius);
    padding: 20px;
    text-align: center;
    color: var(--text-secondary);
    font-size: 14px;
}

.popular-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    padding: 4px 20px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}
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
      <div class="greeting-text">Subscription</div>
      <div class="greeting-name">Choose Your Plan</div>
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
  <div class="pricing-section">
    <div class="pricing-header">
      <h1 class="pricing-title">Choose Your Perfect Plan</h1>
      <p class="pricing-subtitle">Select the plan that best fits your needs. Upgrade or downgrade anytime.</p>
    </div>

    <div class="pricing-grid">
      <?php while($plan = $plans_result->fetch_assoc()): ?>
      <div class="pricing-card <?php echo ($plan['plan_type'] == $user_plan_type) ? 'current-plan' : ''; ?>">
        <?php if($plan['plan_type'] == $user_plan_type): ?>
          <span class="plan-badge">Current Plan</span>
        <?php endif; ?>

        <?php if($plan['is_popular']): ?>
          <span class="popular-badge">Most Popular</span>
        <?php endif; ?>

        <div class="plan-header" style="border-top: 4px solid <?php echo $plan['border_color']; ?>;">
          <div class="plan-name"><?php echo $plan['plan_name']; ?></div>
          <div class="plan-price">
            <?php if($plan['price'] == 0): ?>
              Free Forever
            <?php else: ?>
              <span class="currency">₹</span><?php echo number_format($plan['price'], 0); ?>
              <span class="period">/<?php echo $plan['billing_cycle']; ?></span>
            <?php endif; ?>
          </div>
          <div class="plan-ideal"><?php echo $plan['ideal_for']; ?></div>
        </div>

        <div class="plan-features">
          <div class="feature-item">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <div>
              <span class="feature-label">Domains & Hosting</span>
              <span class="feature-value"><?php echo $plan['domains_limit']; ?></span>
            </div>
          </div>

          <div class="feature-item">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <div>
              <span class="feature-label">Notifications</span>
              <span class="feature-value"><?php echo $plan['notifications']; ?></span>
            </div>
          </div>

          <div class="feature-item">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <div>
              <span class="feature-label">Reminder Scheduling</span>
              <span class="feature-value"><?php echo $plan['reminder_scheduling']; ?></span>
            </div>
          </div>

          <div class="feature-item">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <div>
              <span class="feature-label">Dashboard</span>
              <span class="feature-value"><?php echo $plan['dashboard_access']; ?></span>
            </div>
          </div>

          <div class="feature-item">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <div>
              <span class="feature-label">Data Import</span>
              <span class="feature-value"><?php echo $plan['data_import']; ?></span>
            </div>
          </div>

          <div class="feature-item">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <div>
              <span class="feature-label">Users</span>
              <span class="feature-value"><?php echo $plan['users_limit']; ?></span>
            </div>
          </div>

          <div class="feature-item">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <div>
              <span class="feature-label">Analytics</span>
              <span class="feature-value"><?php echo $plan['analytics_reports']; ?></span>
            </div>
          </div>

          <div class="feature-item">
            <i class="bi bi-check-circle-fill feature-icon"></i>
            <div>
              <span class="feature-label">Support</span>
              <span class="feature-value"><?php echo $plan['support_type']; ?></span>
            </div>
          </div>

          <div class="feature-item">
            <i class="bi bi-<?php echo ($plan['white_label'] == 'No' ? 'x-circle' : 'check-circle-fill'); ?> feature-icon"
               style="color: <?php echo ($plan['white_label'] == 'No' ? 'var(--text-tertiary)' : 'var(--success)'); ?>;">
            </i>
            <div>
              <span class="feature-label">White Label</span>
              <span class="feature-value"><?php echo $plan['white_label']; ?></span>
            </div>
          </div>
        </div>

        <div class="plan-action">
          <?php if($plan['plan_type'] == $user_plan_type): ?>
            <button class="btn-upgrade btn-current" disabled>
              <i class="bi bi-check-circle me-2"></i>Current Plan
            </button>
          <?php else: ?>
         <?php
$plan_price = $plan['price'];

// Make ₹3000 plan cost ₹1 for testing
if ($plan_price == 3000) {
    $plan_price = 1;
}

if ($plan_price == 0) {
?>
    <button class="btn-upgrade btn-select"
        onclick="window.location.href='process-upgrade.php?plan=<?php echo $plan['id']; ?>&payment=free'">
        <i class="bi bi-rocket-takeoff me-2"></i>Activate Free Plan
    </button>
<?php
} else {
?>
    <button class="btn-upgrade btn-select"
        onclick="payWithRazorpay(<?php echo $plan['id']; ?>, <?php echo $plan_price; ?>, '<?php echo $plan['plan_name']; ?>')">
        <i class="bi bi-rocket-takeoff me-2"></i>Upgrade Now
    </button>
<?php
}
?>

          <?php endif; ?>
        </div>
      </div>
      <?php endwhile; ?>
    </div>

    <div class="pricing-footer">
      <i class="bi bi-info-circle me-2"></i>
      All Plans Include: Centralized dashboard, automated expiry reminders, secure cloud storage, data backup, and multi-device access (Web + Mobile).
    </div>
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


function selectFreePlan(planId) {
    if (confirm("Activate the Free Plan?")) {
        window.location.href = "process-upgrade.php?plan=" + planId + "&payment=free";
    }
}

function confirmUpgrade(planId, planName) {
    // Save selected plan temporarily in session via AJAX before redirecting to Razorpay
    sessionStorage.setItem("selectedPlan", planId);
    sessionStorage.setItem("selectedPlanName", planName);
    alert("You'll be redirected to Razorpay to complete payment for " + planName + ".");
    return true; // Allow link to open Razorpay
}

// After successful payment, Razorpay should redirect user back here:
// upgrade-success.php?plan=ID&status=success


</script>

</body>
</html>