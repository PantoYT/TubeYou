<?php

class MailService
{
    private string $apiKey;
    private string $from;
    private string $fromName;

    public function __construct()
    {
        $this->apiKey   = $_ENV['RESEND_API_KEY']  ?? getenv('RESEND_API_KEY');
        $this->from     = $_ENV['MAIL_USERNAME']   ?? getenv('MAIL_USERNAME');
        $this->fromName = $_ENV['MAIL_FROM_NAME']  ?? getenv('MAIL_FROM_NAME');
    }

    private function send(string $to, string $toName, string $subject, string $html): void
    {
        $payload = json_encode([
            'from'    => $this->fromName . ' <onboarding@resend.dev>',
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $html,
        ]);

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 200 && $status !== 201) {
            throw new Exception('Resend error: ' . $response);
        }
    }

    public function sendVerification(string $toEmail, string $toName, string $token): void
    {
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $link   = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/verify?token=' . $token;

        $html = "
            <div style='font-family:sans-serif;max-width:480px;margin:0 auto;'>
                <h2 style='color:#e05a5a;'>Welcome to TubeYou, {$toName}!</h2>
                <p>Click the button below to verify your email address.</p>
                <a href='{$link}' style='display:inline-block;padding:10px 24px;background:#e05a5a;color:white;text-decoration:none;border-radius:6px;font-weight:600;'>
                    Verify Email
                </a>
                <p style='color:#888;font-size:0.85rem;margin-top:1.5rem;'>Or copy this link: {$link}</p>
            </div>
        ";

        $this->send($toEmail, $toName, 'Verify your TubeYou account', $html);
    }

    public function sendPasswordReset(string $toEmail, string $toName, string $token): void
    {
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $link   = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/reset?token=' . $token;

        $html = "
            <div style='font-family:sans-serif;max-width:480px;margin:0 auto;'>
                <h2 style='color:#e05a5a;'>Password Reset</h2>
                <p>Hi {$toName}, click below to reset your password. Link expires in 1 hour.</p>
                <a href='{$link}' style='display:inline-block;padding:10px 24px;background:#e05a5a;color:white;text-decoration:none;border-radius:6px;font-weight:600;'>
                    Reset Password
                </a>
                <p style='color:#888;font-size:0.85rem;margin-top:1.5rem;'>{$link}</p>
            </div>
        ";

        $this->send($toEmail, $toName, 'Reset your TubeYou password', $html);
    }
}