<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include('db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$email = isset($_GET['email']) ? $_GET['email'] : '';
if(!$email) exit("No email specified");

// Fetch user info from hosting_domain table
$query = "SELECT client_name, company_name, domain_name, expiry_date FROM hosting_domain WHERE email='$email' LIMIT 1";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if(!$user) exit("User not found");

// Send email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pt@ultragits.com';
    $mail->Password = 'Ultragits@123';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('pt@ultragits.com', 'UltraGITS');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Domain & Hosting Renewal Reminder";

    $mail->Body = "
    <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.5;'>
        <div style='max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
            <div style='background-color: #1e3974; color: white; text-align: center; padding: 20px; font-size: 22px; font-weight: bold;'>
                UltraGITS
            </div>
            <div style='padding: 20px;'>
                <p>Dear <strong>{$user['client_name']}</strong>,</p>
                <p>This is a friendly reminder that your domain <strong>{$user['domain_name']}</strong> for company <strong>{$user['company_name']}</strong> is <strong>expiring on {$user['expiry_date']}</strong>.</p>
                <p>Please ensure to renew your hosting and domain services to avoid any disruption.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='tel:8610213611' style='background-color: #1e3974; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Contact Us</a>
                </div>
                <p>Thank you for choosing UltraGITS.</p>
                <p>Regards,<br>UltraGITS Team</p>
            </div>
            <div style='background-color: #f1f1f1; text-align: center; padding: 15px; font-size: 12px; color: #555;'>
                &copy; ".date('Y')." UltraServe. All rights reserved.
            </div>
        </div>
    </div>
    ";

    $mail->send();
    echo "<script>alert('Email sent to $email'); window.location='dashboard.php?table=hosting_domain';</script>";
} catch (Exception $e) {
    echo "Email could not be sent. Error: {$mail->ErrorInfo}";
}
?>
