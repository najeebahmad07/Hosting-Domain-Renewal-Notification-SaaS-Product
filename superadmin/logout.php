<?php
session_start();
include('db.php');

// Log the logout activity before destroying session
if(isset($_SESSION['admin_id']) && isset($_SESSION['admin'])){
    $admin_id = $_SESSION['admin_id'];
    $admin_name = $_SESSION['admin'];

    $logQuery = "INSERT INTO activity_logs (admin_id, admin_name, action, table_name, record_id)
                VALUES ($admin_id, '$admin_name', 'Logged Out', 'admin', $admin_id)";
    mysqli_query($conn, $logQuery);
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page with a success message
header("Location: index.php?logout=success");
exit();
?>