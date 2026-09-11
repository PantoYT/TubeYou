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
        $redis = function_exists('redisClient') ? redisClient() : null;
        if ($redis !== null) {
            try {
                $redisKey = 'tubeyou:rate:' . md5($key);
                $now = microtime(true);
                $member = sprintf('%.6f-', $now) . bin2hex(random_bytes(8));
                $script = <<<'LUA'
local key = KEYS[1]
local cutoff = tonumber(ARGV[1])
local now = tonumber(ARGV[2])
local member = ARGV[3]
local maximum = tonumber(ARGV[4])
local ttl = tonumber(ARGV[5])
redis.call('ZREMRANGEBYSCORE', key, '-inf', cutoff)
local count = redis.call('ZCARD', key)
if count >= maximum then
    return 0
end
redis.call('ZADD', key, now, member)
redis.call('EXPIRE', key, ttl)
return 1
LUA;
                return (int)$redis->eval(
                    $script,
                    [$redisKey, $now - $windowSeconds, $now, $member, $maxHits, $windowSeconds],
                    1
                ) === 1;
            } catch (Throwable $e) {
                error_log('[redis] rate limiter failed: ' . $e->getMessage());
            }
        }

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
        $addr = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        // Only trust X-Forwarded-For when explicitly behind a known proxy
        if (($addr === '127.0.0.1' || $addr === '::1') && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwarded = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
            if (filter_var($forwarded, FILTER_VALIDATE_IP)) {
                return $forwarded;
            }
        }
        return $addr;
    }
}
