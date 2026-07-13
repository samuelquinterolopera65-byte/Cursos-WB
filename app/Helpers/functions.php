<?php
function url(string $path = ''): string {
    $base = defined('APP_URL') ? APP_URL : '';
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string {
    return url('assets/' . ltrim($path, '/'));
}

function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function activeRoute(string $page, string $current): string {
    return $page === $current ? 'active' : '';
}
