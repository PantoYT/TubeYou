<?php

class MailService
{
    private string $apiKey;
    private string $from;
    private string $fromName;
    private string $replyTo;

    public function __construct()
    {
        $this->apiKey   = (string) ($_ENV['RESEND_API_KEY'] ?? getenv('RESEND_API_KEY') ?: '');
        $this->from     = (string) ($_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME') ?: '');
        $this->fromName = (string) ($_ENV['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME') ?: 'TubeYou');
        $this->replyTo  = (string) ($_ENV['MAIL_REPLY_TO'] ?? getenv('MAIL_REPLY_TO') ?: $this->from);
    }

    private function send(string $to, string $toName, string $subject, string $html): void
    {
        if ($this->apiKey === '' || $this->from === '') {
            throw new RuntimeException('Resend is not configured');
        }

        $payload = json_encode([
            'from' => $this->fromName . ' <' . $this->from . '>',
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $html,
            'reply_to' => $this->replyTo,
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
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception('Resend connection error: ' . $curlError);
        }

        if ($status !== 200 && $status !== 201) {
            throw new Exception('Resend error: ' . $response);
        }
    }

    public function sendVerification(string $toEmail, string $toName, string $token): void
    {
        $link = $this->baseUrl() . '/verify?token=' . urlencode($token);

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
        $link = $this->baseUrl() . '/reset?token=' . urlencode($token);

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

    private function baseUrl(): string
    {
        $configured = $_ENV['APP_URL'] ?? getenv('APP_URL') ?: '';
        if ($configured !== '') return rtrim($configured, '/');

        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }
}
