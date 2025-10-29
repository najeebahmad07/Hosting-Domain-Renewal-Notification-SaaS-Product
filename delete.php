<?php
session_start();
include('db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$table = isset($_GET['table']) ? $_GET['table'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Sanitize table name for security (basic check)
if (!empty($table) && $id > 0 && preg_match('/^[a-zA-Z0-9_]+$/', $table)) {

    $safe_table = mysqli_real_escape_string($conn, $table);

    // Attempt to delete the record
    $query = "DELETE FROM `$safe_table` WHERE id=$id";

    if (mysqli_query($conn, $query)) {
        // --- SUCCESS ---
        // 1. Log the activity
        $admin_name = $_SESSION['admin'];
        mysqli_query($conn, "INSERT INTO activity_logs (admin_name, action, table_name, record_id) VALUES ('$admin_name', 'Deleted', '$safe_table', $id)");

        // 2. Set the success flash message
        $_SESSION['flash_message'] = "Record with ID #$id has been deleted successfully.";
        $_SESSION['flash_type'] = "success"; // 'success' will be used for styling (e.g., green alert)

    } else {
        // --- ERROR ---
        // Set an error flash message
        $_SESSION['flash_message'] = "Error deleting record: " . mysqli_error($conn);
        $_SESSION['flash_type'] = "danger"; // 'danger' will be used for styling (e.g., red alert)
    }
} else {
    // --- INVALID REQUEST ---
    $_SESSION['flash_message'] = "Invalid request. Could not delete record.";
    $_SESSION['flash_type'] = "danger";
}

// Redirect back to the main table view in the dashboard
// This will always go to the hosting_domain page, which is correct for this context.
header("Location: dashboard.php?page=hosting_domain");
exit();
?>