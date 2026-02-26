<?php

namespace App\Core\Security;

final class Csrf
{
    /** @var string */
    private $sessionKey = '_csrf';

    public function token(): string
    {
        if (!isset($_SESSION[$this->sessionKey]) || !is_string($_SESSION[$this->sessionKey]) || $_SESSION[$this->sessionKey] === '') {
            $_SESSION[$this->sessionKey] = bin2hex(random_bytes(16));
        }
        return (string) $_SESSION[$this->sessionKey];
    }

    public function verify(string $token): bool
    {
        $current = $_SESSION[$this->sessionKey] ?? '';
        if (!is_string($current) || $current === '') {
            return false;
        }
        return hash_equals($current, $token);
    }
}

