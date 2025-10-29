<?php
session_start();
include('db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// Fetch expiring hosting/domain for calendar
$events = [];
$query = mysqli_query($conn, "SELECT client_name, domain_name, expiry_date FROM hosting_domain WHERE expiry_date >= CURDATE()");
while ($row = mysqli_fetch_assoc($query)) {
    $color = (strtotime($row['expiry_date']) <= strtotime('+7 days')) ? '#dc3545' : '#ffc107'; // red for 7 days, orange otherwise
    $events[] = [
        'id' => uniqid(),
        'title' => $row['client_name'].' - '.$row['domain_name'],
        'start' => $row['expiry_date'].'T00:00:00',
        'end' => $row['expiry_date'].'T23:59:59',
        'bgColor' => $color,
        'color' => '#fff'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Calendar View - UltraServe</title>

<!-- TUI Calendar CSS -->
<link rel="stylesheet" href="https://uicdn.toast.com/tui-calendar/latest/tui-calendar.css" />
<link rel="stylesheet" href="https://uicdn.toast.com/tui-date-picker/latest/tui-date-picker.css" />
<link rel="stylesheet" href="https://uicdn.toast.com/tui-time-picker/latest/tui-time-picker.css" />

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="icon" type="image/png" sizes="32x32" href="img/logo.png">

<!-- Apple devices -->
<link rel="apple-touch-icon" href="img/logo.png">

<!-- For older browsers -->
<link rel="shortcut icon" href="img/logo.png" type="image/x-icon">

<style>
body { font-family: Arial,sans-serif; margin:0; padding:0; background:#f8f9fa; }
.header{background-color:#1e3974;color:white;padding:15px 20px;display:flex;justify-content:space-between;align-items:center;}
.sidebar{width:200px;background-color:#1e3974;min-height:100vh;color:white;padding-top:20px;position:fixed;}
.sidebar a{display:block;color:white;padding:12px 20px;text-decoration:none;}
.sidebar a:hover, .sidebar a.active{background-color:#163359;border-radius:4px;}
.content{margin-left:200px;padding:20px;}
.tui-full-calendar {height: 600px;}
</style>
</head>
<body>

<!-- Header -->
<div class="header">
    <h4>Welcome, <?= $_SESSION['admin'] ?></h4>
    <a href="logout.php" class="btn btn-sm btn-light">Logout</a>
</div>

<!-- Sidebar -->
<div class="sidebar">
  <a href="dashboard.php?page=dashboard">Dashboard</a>
  <a href="dashboard.php?page=hosting_domain">Hosting & Domain</a>
  <a href="dashboard.php?page=profile">Profile</a>
  <a href="dashboard.php?page=calendar" class="active">Calendar View</a>
  <a href="dashboard.php?page=theme">Theme & Layout</a>
  <a href="dashboard.php?page=help">Help Center</a>
  <a href="dashboard.php?page=system_info">System Info</a>
  <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
</div>

<!-- Content -->
<div class="content">
<h4>Hosting/Domain Expiry Calendar</h4>
<div id="calendar"></div>
</div>

<!-- TUI Calendar JS -->
<script src="https://uicdn.toast.com/tui-code-snippet/latest/tui-code-snippet.js"></script>
<script src="https://uicdn.toast.com/tui-dom/latest/tui-dom.js"></script>
<script src="https://uicdn.toast.com/tui-time-picker/latest/tui-time-picker.js"></script>
<script src="https://uicdn.toast.com/tui-date-picker/latest/tui-date-picker.js"></script>
<script src="https://uicdn.toast.com/tui-calendar/latest/tui-calendar.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var Calendar = tui.Calendar;

    var calendar = new Calendar('#calendar', {
        defaultView: 'month',
        taskView: false,
        scheduleView: ['time'],
        useCreationPopup: false,
        useDetailPopup: true,
        month: {
            startDayOfWeek: 0
        }
    });

    // Add events
    var events = <?= json_encode($events) ?>;
    events.forEach(function(event){
        calendar.createSchedules([event]);
    });
});
</script>

</body>
</html>
