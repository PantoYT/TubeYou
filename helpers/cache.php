<?php

function cacheGet(string $key): mixed
{
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
    $file = __DIR__ . '/../storage/cache/' . md5($key) . '.php';
    if (file_exists($file)) unlink($file);
}
