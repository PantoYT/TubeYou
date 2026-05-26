<?php

function requireAuth(string $redirect = '/login'): void
{
    if (!isset($_SESSION['user']['id'])) {
        header('Location: ' . $redirect);
        exit;
    }
}

function requireAuthJson(): void
{
    if (!isset($_SESSION['user']['id'])) {
        http_response_code(403);
        exit;
    }
}
