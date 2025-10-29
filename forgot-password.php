<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('db.php');
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    $query = "SELECT * FROM admin WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        $tempPassword = substr(md5(rand()), 0, 8); // Temporary plain password

        // Update password in DB as plain text
        mysqli_query($conn, "UPDATE admin SET password='$tempPassword' WHERE email='$email'");

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
             $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'najeebultragits@gmail.com'; // SMTP username
            $mail->Password = 'hqgd qvuu jlmv nmbq';          // SMTP password
            $mail->SMTPSecure = 'tls';                  // Encryption: tls or ssl
            $mail->Port = 587;

            $mail->setFrom('najeebultragits@gmail.com', 'UltraGITS');
            $mail->addAddress($email, $admin['username']);

            $mail->isHTML(true);
$mail->Subject = 'Password Reset - UltraServe';
$mail->Body = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
    .container { max-width: 600px; margin: 20px auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    h2 { color: #1e3974; }
    p { color: #333333; font-size: 16px; line-height: 1.5; }
    .btn { display: inline-block; padding: 12px 20px; background-color: #1e3974; color: #ffffff; text-decoration: none; border-radius: 5px; margin-top: 15px; }
    .footer { margin-top: 30px; font-size: 12px; color: #777777; text-align: center; }
</style>
</head>
<body>
<div class="container">
    <h2>Password Reset Request</h2>
    <p>Hello <b>'.$admin['username'].'</b>,</p>
    <p>You requested a password reset for your UltraServe admin account.</p>
    <p><b>Your temporary password is:</b> '.$tempPassword.'</p>
    <p>Please use this password to login and change it immediately for security reasons.</p>


    <p class="footer">Regards,<br>UltraGITS Team</p>
</div>
</body>
</html>';


            $mail->send();
            $success = "Temporary password sent to your email!";
        } catch (Exception $e) {
            $error = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UltraServe|Forgot Password </title>
<link rel="icon" type="image/png" sizes="32x32" href="img/logo.png">

<!-- Apple devices -->
<link rel="apple-touch-icon" href="img/logo.png">

<!-- For older browsers -->
<link rel="shortcut icon" href="img/logo.png" type="image/x-icon">
<style>
body { font-family: Arial,sans-serif; display:flex; justify-content:center; align-items:center; height:100vh; background:#f8f9fa; }
.form-wrap { background:white; padding:30px; border-radius:8px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:100%; max-width:400px; }
.form-group { margin-bottom:20px; }
.form-group input { width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; }
.form-group button { width:100%; padding:12px; background:#1e3974; color:white; border:none; border-radius:5px; cursor:pointer; }
.form-group button:hover { background:#163359; }
.error { color:red; text-align:center; margin-bottom:10px; }
.success { color:green; text-align:center; margin-bottom:10px; }
</style>
</head>
<body>

<form class="form-wrap" method="POST">
<h2 style="text-align:center; margin-bottom:20px;">Forgot Password</h2>

<?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>
<?php if(!empty($success)) echo "<div class='success'>$success</div>"; ?>

<div class="form-group">
    <input type="email" name="email" placeholder="Enter your admin email" required />
</div>
<div class="form-group">
    <button type="submit">Reset Password</button>
</div>
<p style="text-align:center;"><a href="index.php">Back to Login</a></p>
</form>

</body>
</html>
