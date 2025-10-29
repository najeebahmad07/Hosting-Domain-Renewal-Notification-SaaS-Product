<div class="header">
    <div class="d-flex align-items-center">
        <button class="btn-icon me-3" id="sidebarToggle" type="button">
            <i class="bi bi-list"></i>
        </button>
       <?php
date_default_timezone_set('Asia/Kolkata');
$current_hour = date('H');

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

if (isset($_SESSION['super_admin'])) {
    echo "<div class='greeting-wrapper'>";
    echo "<span class='greeting-emoji'>{$emoji}</span>";
    echo "<div>";
    echo "<div class='greeting-text'>{$greeting}</div>";
    echo "<div class='greeting-name'>{$_SESSION['super_admin']} <span class='badge-super'>SUPER</span></div>";
    echo "</div>";
    echo "</div>";
}
?>
    </div>

    <div class="header-actions">
        <button class="btn-icon" id="themeToggle" title="Toggle Theme">
            <i class="bi bi-moon-stars-fill"></i>
        </button>
        <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="btn btn-outline">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
    </div>
</div>