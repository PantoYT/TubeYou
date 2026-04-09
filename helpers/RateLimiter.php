<?php

class RateLimiter
{
    private string $dir;

    public function __construct()
    {
        $this->dir = __DIR__ . '/../storage/rate_limits/';
        if (!is_dir($this->dir)) mkdir($this->dir, 0755, true);
    }

    public function check(string $key, int $maxHits, int $windowSeconds): bool
    {
        $file = $this->dir . md5($key) . '.json';
        $now  = time();
        $data = [];

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?? [];
        }

        $data = array_filter($data, fn($t) => $t > $now - $windowSeconds);

        if (count($data) >= $maxHits) return false;

        $data[] = $now;
        file_put_contents($file, json_encode(array_values($data)), LOCK_EX);
        return true;
    }

    public function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}