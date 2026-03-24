<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'includes/db.php';

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT user_id, fullname FROM user_tb WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conn->prepare("
            UPDATE user_tb 
            SET reset_token=?, reset_token_expiry=? 
            WHERE email=?
        ");

        $update->bind_param("sss", $token, $expiry, $email);
        $update->execute();

        $resetLink = "http://115.88.1.11/System/ITSM/reset_password.php?token=" . $token;


        // SEND EMAIL
        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();

            $mail->Host = 'smtp.gmail.com';

            $mail->SMTPAuth = true;

            $mail->Username = 'koppelit101424@gmail.com';

            $mail->Password = 'eymk qyiv awbw wvxb';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            $mail->Port = 587;

            $mail->setFrom('koppelit101424@gmail.com', 'Koppel ITSM');

            $mail->addAddress($email, $user['fullname']);

            $mail->isHTML(true);

            $mail->Subject = 'Password Reset Request';

            $mail->Body = "
                Hello {$user['fullname']},<br><br>
                Click the link below to reset your password:<br><br>
                <a href='$resetLink'>$resetLink</a><br><br>
                This link will expire in 1 hour.<br><br>
                Koppel ITSM
            ";

            $mail->send();

            $message = "<div class='alert alert-success'>
                        Reset link sent to your email.
                        </div>";

        } catch (Exception $e) {

            $message = "<div class='alert alert-danger'>
                        Email could not be sent.
                        </div>";
        }

    } else {

        $message = "<div class='alert alert-danger'>
                    Email not found.
                    </div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card p-4">

<h4>Forgot Password</h4>

<?= $message ?>

<form method="POST">

<input type="email" name="email" class="form-control mb-3" placeholder="Enter your email" required>

<button class="btn btn-primary">Send Reset Link</button>

</form>

</div>

</div>

</body>
</html>
