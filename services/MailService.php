<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class MailService
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host       = $_ENV['MAIL_HOST'];
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = $_ENV['MAIL_USERNAME'];
        $this->mail->Password   = $_ENV['MAIL_PASSWORD'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = $_ENV['MAIL_PORT'];
        $this->mail->setFrom($_ENV['MAIL_USERNAME'], $_ENV['MAIL_FROM_NAME']);
        $this->mail->CharSet    = 'UTF-8';
    }

    public function sendVerification(string $toEmail, string $toName, string $token): void
    {
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'];
        $link   = $scheme . '://' . $host . '/verify?token=' . $token;

        $this->mail->addAddress($toEmail, $toName);
        $this->mail->isHTML(true);
        $this->mail->Subject = 'Verify your TubeYou account';
        $this->mail->Body    = "
            <div style='font-family:sans-serif;max-width:480px;margin:0 auto;'>
                <h2 style='color:#e05a5a;'>Welcome to TubeYou, {$toName}!</h2>
                <p>Click the button below to verify your email address.</p>
                <a href='{$link}' 
                    style='display:inline-block;padding:10px 24px;background:#e05a5a;
                        color:white;text-decoration:none;border-radius:6px;font-weight:600;'>
                    Verify Email
                </a>
                <p style='color:#888;font-size:0.85rem;margin-top:1.5rem;'>
                    Or copy this link: {$link}
                </p>
            </div>
        ";
        $this->mail->AltBody = "Verify your TubeYou account: {$link}";

        $this->mail->send();
    }
}