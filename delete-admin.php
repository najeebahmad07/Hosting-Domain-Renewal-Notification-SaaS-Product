<?php
session_start();
include('db.php');

if (!isset($_SESSION['super_admin_id'])) {
    header("Location: superadmin-login.php");
    exit();
}

$admin_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$admin_id){
    die("Invalid Admin ID");
}

// Delete admin
$query = "DELETE FROM admin WHERE id=$admin_id";
if(mysqli_query($conn, $query)){
    header("Location: superadmin-dashboard.php?msg=deleted");
} else {
    die("Error deleting admin: " . mysqli_error($conn));
}
?>
