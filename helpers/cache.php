<?php

/**
 * Shared Redis connection used by cache, rate limiting and session bootstrap.
 * Redis is optional so local XAMPP development continues to work unchanged.
 */
function redisClient(): mixed
{
    static $attempted = false;
    static $client = null;

    if ($attempted) return $client;
    $attempted = true;

    $host = $_ENV['REDIS_HOST'] ?? getenv('REDIS_HOST') ?: '';
    $port = (int)($_ENV['REDIS_PORT'] ?? getenv('REDIS_PORT') ?: 6379);

    if ($host === '' || !class_exists('Redis')) return null;

    try {
        $client = new Redis();
        $client->connect($host, $port, 0.5, null, 0, 0.5);
        $client->ping();
        return $client;
    } catch (Throwable $e) {
        error_log('[redis] unavailable, using file fallback: ' . $e->getMessage());
        $client = null;
        return null;
    }
}

function cacheGet(string $key): mixed
{
    $redis = redisClient();
    if ($redis !== null) {
        try {
            $value = $redis->get('tubeyou:cache:' . md5($key));
            if ($value === false) return null;
            return unserialize($value, ['allowed_classes' => false]);
        } catch (Throwable $e) {
            error_log('[redis] cache read failed: ' . $e->getMessage());
        }
    }

    $file = __DIR__ . '/../storage/cache/' . md5($key) . '.php';
    if (!file_exists($file)) return null;
    $data = include $file;
    if (!is_array($data) || $data['ttl'] < time()) {
        @unlink($file);
        return null;
    }
    return $data['value'];
}

function cacheSet(string $key, mixed $value, int $ttlSeconds = 60): void
{
    $redis = redisClient();
    if ($redis !== null) {
        try {
            $redis->setex('tubeyou:cache:' . md5($key), $ttlSeconds, serialize($value));
            return;
        } catch (Throwable $e) {
            error_log('[redis] cache write failed: ' . $e->getMessage());
        }
    }

    $dir = __DIR__ . '/../storage/cache/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents(
        $dir . md5($key) . '.php',
        '<?php return ' . var_export(['ttl' => time() + $ttlSeconds, 'value' => $value], true) . ';',
        LOCK_EX
    );
}

function cacheClear(string $key): void
{
    $redis = redisClient();
    if ($redis !== null) {
        try {
            $redis->del('tubeyou:cache:' . md5($key));
        } catch (Throwable $e) {
            error_log('[redis] cache delete failed: ' . $e->getMessage());
        }
    }

    $file = __DIR__ . '/../storage/cache/' . md5($key) . '.php';
    if (file_exists($file)) unlink($file);
}
