<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Include PHPMailer manually (no Composer)
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

include('../db.php');

if (!isset($_SESSION['super_admin'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $super_admin_id = $_SESSION['super_admin_id'];
    $notification_type = mysqli_real_escape_string($conn, $_POST['notification_type']);
    $recipients = $_POST['recipients'];
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Build recipient query based on selection
    $recipientQuery = "SELECT a.id, a.username, a.email FROM admin a ";

    switch($recipients) {
        case 'all':
            $recipientQuery .= "WHERE a.super_admin_id = $super_admin_id";
            break;

        case 'expiring':
            $recipientQuery .= "
                INNER JOIN hosting_domain hd ON a.id = hd.admin_id
                WHERE a.super_admin_id = $super_admin_id
                AND (hd.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                     OR hd.hosting_expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY))
                GROUP BY a.id
            ";
            break;

        case 'basic':
            $recipientQuery .= "
                LEFT JOIN user_subscriptions us ON a.id = us.admin_id
                WHERE a.super_admin_id = $super_admin_id
                AND (us.plan_id = 1 OR us.plan_id IS NULL)
            ";
            break;

        case 'standard':
            $recipientQuery .= "
                INNER JOIN user_subscriptions us ON a.id = us.admin_id
                WHERE a.super_admin_id = $super_admin_id AND us.plan_id = 2
            ";
            break;

        case 'premium':
            $recipientQuery .= "
                INNER JOIN user_subscriptions us ON a.id = us.admin_id
                WHERE a.super_admin_id = $super_admin_id AND us.plan_id = 3
            ";
            break;
    }

    $recipientResult = mysqli_query($conn, $recipientQuery);
    $successCount = 0;
    $failCount = 0;

    while($admin = mysqli_fetch_assoc($recipientResult)) {
        $mail = new PHPMailer(true);

        try {
            // ✅ Hostinger SMTP configuration
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'pt@ultragits.com';
            $mail->Password   = 'Ultragits@123';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            // Sender & recipient
            $mail->setFrom('pt@ultragits.com', 'UltraGITS');
            $mail->addAddress($admin['email'], $admin['username']);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $subject;

            // HTML email body
            $emailBody = "
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>$subject</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header {
                        background: linear-gradient(135deg, #1e3974, #4b6cb7);
                        color: white;
                        padding: 30px;
                        text-align: center;
                        border-radius: 10px 10px 0 0;
                    }
                    .content {
                        background: #f9fafb;
                        padding: 30px;
                        border-radius: 0 0 10px 10px;
                    }
                    .message {
                        background: white;
                        padding: 20px;
                        border-radius: 8px;
                        margin: 20px 0;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    .footer {
                        text-align: center;
                        margin-top: 30px;
                        color: #6b7280;
                        font-size: 14px;
                    }
                    .btn {
                        display: inline-block;
                        background: #1e3974;
                        color: white;
                        padding: 12px 24px;
                        text-decoration: none;
                        border-radius: 6px;
                        margin-top: 20px;
                    }
                    h1 { margin: 0; font-size: 24px; }
                    h2 { color: #111827; margin-top: 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🔔 UltraServe Notification</h1>
                    </div>
                    <div class='content'>
                        <h2>Hello, {$admin['username']}!</h2>
                        <div class='message'>
                            <p>" . nl2br($message) . "</p>
                        </div>
                        <a href='https://hosting-domain.ultragits.com/login.php' class='btn'>Go to Dashboard</a>
                    </div>
                    <div class='footer'>
                        <p>&copy; 2025 UltraServe. All rights reserved.</p>
                        <p>This is an automated message from UltraGITS.</p>
                    </div>
                </div>
            </body>
            </html>
            ";

            $mail->Body = $emailBody;
            $mail->AltBody = strip_tags($message);

            $mail->send();
            $successCount++;

            // Log the activity
            $admin_name = $_SESSION['super_admin'];
            $logQuery = "INSERT INTO activity_logs (admin_id, admin_name, action, table_name, record_id)
                        VALUES ({$admin['id']}, '$admin_name', 'Sent Notification', 'admin', {$admin['id']})";
            mysqli_query($conn, $logQuery);

        } catch (Exception $e) {
            $failCount++;
            error_log("Email failed to {$admin['email']}: {$mail->ErrorInfo}");
        }
    }

    if ($successCount > 0) {
        $_SESSION['success'] = "Successfully sent $successCount notification(s)!" . ($failCount > 0 ? " ($failCount failed)" : "");
    } else {
        $_SESSION['error'] = "Failed to send notifications. Please check your SMTP configuration.";
    }

    header("Location: superadmin_dashboard.php?page=global_notifications");
    exit();
} else {
    header("Location: superadmin_dashboard.php?page=global_notifications");
    exit();
}
?>
