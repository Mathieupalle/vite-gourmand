<?php
declare(strict_types=1);

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        if ($path !== '' && $path[0] !== '/') $path = '/' . $path;
        header('Location: ' . base_url() . $path);
        exit;
    }
}