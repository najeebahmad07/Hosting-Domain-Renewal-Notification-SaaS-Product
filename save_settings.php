<?php
session_start();
include('db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$settings_type = $_POST['settings_type'] ?? '';

// Check if settings table exists, if not create it
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'admin_settings'");
if(mysqli_num_rows($check_table) == 0) {
    $create_table = "CREATE TABLE admin_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        setting_key VARCHAR(100) NOT NULL,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_admin_setting (admin_id, setting_key)
    )";
    mysqli_query($conn, $create_table);
}

// Function to save or update setting
function saveSetting($conn, $admin_id, $key, $value) {
    $key = mysqli_real_escape_string($conn, $key);
    $value = mysqli_real_escape_string($conn, $value);

    $query = "INSERT INTO admin_settings (admin_id, setting_key, setting_value)
              VALUES ($admin_id, '$key', '$value')
              ON DUPLICATE KEY UPDATE setting_value = '$value'";
    return mysqli_query($conn, $query);
}

// Function to log activity
function logActivity($conn, $admin_id, $admin_name, $action, $table_name, $record_id = 0) {
    $admin_name = mysqli_real_escape_string($conn, $admin_name);
    $action = mysqli_real_escape_string($conn, $action);
    $table_name = mysqli_real_escape_string($conn, $table_name);

    $query = "INSERT INTO activity_logs (admin_id, admin_name, action, table_name, record_id)
              VALUES ($admin_id, '$admin_name', '$action', '$table_name', $record_id)";
    mysqli_query($conn, $query);
}

// Handle General Settings
if($settings_type == 'general') {
    $timezone = $_POST['timezone'] ?? 'Asia/Kolkata';
    $currency = $_POST['currency'] ?? 'INR';
    $date_format = $_POST['date_format'] ?? 'Y-m-d';

    saveSetting($conn, $admin_id, 'timezone', $timezone);
    saveSetting($conn, $admin_id, 'currency', $currency);
    saveSetting($conn, $admin_id, 'date_format', $date_format);

    logActivity($conn, $admin_id, $_SESSION['admin'], 'Updated', 'admin_settings', 0);

    $_SESSION['success'] = "General settings saved successfully!";
    header("Location: dashboard.php?page=settings_general");
    exit();
}

// Handle Branding Settings
if($settings_type == 'branding') {
    $company_name = $_POST['company_name'] ?? 'UltraServe';
    $primary_color = $_POST['primary_color'] ?? '#3b82f6';
    $secondary_color = $_POST['secondary_color'] ?? '#8b5cf6';

    saveSetting($conn, $admin_id, 'company_name', $company_name);
    saveSetting($conn, $admin_id, 'primary_color', $primary_color);
    saveSetting($conn, $admin_id, 'secondary_color', $secondary_color);

    // Handle logo upload
    if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $upload_dir = 'uploads/logos/';
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'svg', 'gif'];

        if(in_array(strtolower($file_ext), $allowed)) {
            $new_filename = 'logo_' . $admin_id . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;

            if(move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
                saveSetting($conn, $admin_id, 'logo_path', $upload_path);
            }
        }
    }

    logActivity($conn, $admin_id, $_SESSION['admin'], 'Updated', 'admin_settings', 0);

    $_SESSION['success'] = "Branding settings saved successfully!";
    header("Location: dashboard.php?page=settings_branding");
    exit();
}

// Handle Backup Settings
if($settings_type == 'backup') {
    $enable_backup = isset($_POST['enable_backup']) ? '1' : '0';
    $backup_frequency = $_POST['backup_frequency'] ?? 'daily';
    $backup_time = $_POST['backup_time'] ?? '02:00';
    $backup_location = $_POST['backup_location'] ?? '/backups/';
    $backup_retention = $_POST['backup_retention'] ?? '30';

    saveSetting($conn, $admin_id, 'enable_backup', $enable_backup);
    saveSetting($conn, $admin_id, 'backup_frequency', $backup_frequency);
    saveSetting($conn, $admin_id, 'backup_time', $backup_time);
    saveSetting($conn, $admin_id, 'backup_location', $backup_location);
    saveSetting($conn, $admin_id, 'backup_retention', $backup_retention);

    logActivity($conn, $admin_id, $_SESSION['admin'], 'Updated', 'admin_settings', 0);

    $_SESSION['success'] = "Backup settings saved successfully!";
    header("Location: dashboard.php?page=settings_backup");
    exit();
}

// Handle Retention Policy Settings
if($settings_type == 'retention') {
    $retention_period = $_POST['retention_period'] ?? '365';
    $logs_retention = $_POST['logs_retention'] ?? '90';
    $auto_delete = isset($_POST['auto_delete']) ? '1' : '0';
    $delete_expired = isset($_POST['delete_expired']) ? '1' : '0';

    saveSetting($conn, $admin_id, 'retention_period', $retention_period);
    saveSetting($conn, $admin_id, 'logs_retention', $logs_retention);
    saveSetting($conn, $admin_id, 'auto_delete', $auto_delete);
    saveSetting($conn, $admin_id, 'delete_expired', $delete_expired);

    logActivity($conn, $admin_id, $_SESSION['admin'], 'Updated', 'admin_settings', 0);

    $_SESSION['success'] = "Data retention policy saved successfully!";
    header("Location: dashboard.php?page=settings_retention");
    exit();
}

// If no valid settings type
$_SESSION['error'] = "Invalid settings type!";
header("Location: dashboard.php?page=dashboard");
exit();
?>