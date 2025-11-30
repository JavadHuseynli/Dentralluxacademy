<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$toEmail = "info@dentaluxacademy.com";

if($_SERVER["REQUEST_METHOD"] == "POST") {

    // Honeypot spam check
    if(!empty($_POST['website'])) exit;

    // Clean form data
    $name    = htmlspecialchars(strip_tags(trim($_POST['name'])));
    $email   = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(strip_tags(trim($_POST['subject'])));
    $message = htmlspecialchars(strip_tags(trim($_POST['message'])));

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo json_encode(['status'=>'error','message'=>'Invalid email address']);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@dentaluxacademy.com'; // SMTP e-mail
        $mail->Password   = 'Dentalux2025!';           // SMTP password
        $mail->SMTPSecure = 'ssl';                     // SSL or TLS
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('info@dentaluxacademy.com', 'Dentalux Academy'); // mütləq domen e-mail
        $mail->addAddress($toEmail, 'Dentalux Academy');
        $mail->addReplyTo($email, $name); // istifadəçi cavab üçün

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Contact Form: $subject";
        $mail->Body    = "
            <h3>New Message from Contact Form</h3>
            <p><b>Name:</b> {$name}</p>
            <p><b>Email:</b> {$email}</p>
            <p><b>Subject:</b> {$subject}</p>
            <p><b>Message:</b><br>{$message}</p>
        ";
        $mail->AltBody = "New Message from Contact Form\n\nName: $name\nEmail: $email\nSubject: $subject\nMessage:\n$message";

        $mail->send();
        echo json_encode(['status'=>'success','message'=>'Thank you! Your message has been sent.']);
    } catch (Exception $e) {
        echo json_encode(['status'=>'error','message'=>"Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }

} else {
    echo json_encode(['status'=>'error','message'=>'Invalid request.']);
}
