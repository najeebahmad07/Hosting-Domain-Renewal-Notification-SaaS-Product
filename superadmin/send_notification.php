<?php
session_start();
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('db.php');

// Check login
if (!isset($_SESSION['admin']) && !isset($_SESSION['super_admin'])) {
    header("Location: index.php");
    exit();
}

$admin_id = isset($_GET['admin_id']) ? (int)$_GET['admin_id'] : 0;
if ($admin_id <= 0) {
    $_SESSION['error'] = "Invalid admin selected.";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Fetch admin info
$admin_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM admin WHERE id = $admin_id"));
if (!$admin_info) {
    $_SESSION['error'] = "Admin not found.";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Fetch expiring domains
$domains = mysqli_query($conn, "
    SELECT domain_name, expiry_date, hosting_expiry_date, company_name
    FROM hosting_domain
    WHERE admin_id = $admin_id
    AND (expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
         OR hosting_expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY))
");

$total_expiring = mysqli_num_rows($domains);

// Prepare email
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pt@ultragits.com';
    $mail->Password = 'Ultragits@123';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom('pt@ultragits.com', 'UltraServe Notifications');
    $mail->addAddress($admin_info['email']);

    $mail->isHTML(true);
    $mail->Subject = " Domain Expiry Alert - $total_expiring Domain(s) Expiring Soon";

    $body = "<h2>Dear {$admin_info['username']},</h2>";
    $body .= "<p>The following domains under your management are expiring soon:</p>";
    $body .= "<table border='1' cellpadding='5'>
                <tr>
                    <th>Domain Name</th>
                    <th>Company</th>
                    <th>Domain Expiry</th>
                    <th>Hosting Expiry</th>
                </tr>";

    while($d = mysqli_fetch_assoc($domains)){
        $body .= "<tr>
                    <td>{$d['domain_name']}</td>
                    <td>{$d['company_name']}</td>
                    <td>{$d['expiry_date']}</td>
                    <td>{$d['hosting_expiry_date']}</td>
                  </tr>";
    }
    $body .= "</table>";
    $body .= "<p>Please take necessary action to renew these domains/hosting.</p>";
    $body .= "<p>Regards,<br>UltraServe Team</p>";

    $mail->Body = $body;
    $mail->AltBody = strip_tags($body);

    $mail->send();
    $_SESSION['success'] = "Email sent successfully to {$admin_info['email']}";
} catch (Exception $e) {
    $_SESSION['error'] = "Email could not be sent. Error: {$mail->ErrorInfo}";
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>
