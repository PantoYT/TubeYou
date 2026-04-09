<?php

function sanitizeText(string $input, int $maxLength = 1000): string
{
    return mb_substr(trim(strip_tags($input)), 0, $maxLength);
}

function sanitizeTitle(string $input): string
{
    return mb_substr(trim(strip_tags($input)), 0, 255);
}