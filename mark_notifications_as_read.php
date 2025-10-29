<?php
session_start();
include('db.php');

// Ensure an admin is logged in
if (!isset($_SESSION['admin'])) {
    http_response_code(403); // Forbidden
    exit();
}

// Mark all unread activity logs as read
$updateQuery = "UPDATE activity_logs SET is_read = 1 WHERE is_read = 0";

if (mysqli_query($conn, $updateQuery)) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500); // Internal Server Error
}
?>