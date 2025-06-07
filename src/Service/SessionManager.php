<?php

namespace Milos\Dentists\Service;

class SessionManager
{
    public static function set(string $key, mixed $value): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION[$key] = $value;
    }

    public static function get(string $key): mixed
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return $_SESSION[$key] ?? null;
    }
}