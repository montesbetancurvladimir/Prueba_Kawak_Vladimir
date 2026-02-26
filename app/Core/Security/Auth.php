<?php

namespace App\Core\Security;

final class Auth
{
    /** @var string */
    private $username;
    /** @var string */
    private $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function check(): bool
    {
        return isset($_SESSION['user']) && is_string($_SESSION['user']) && $_SESSION['user'] !== '';
    }

    public function user(): string
    {
        $u = $_SESSION['user'] ?? '';
        return is_string($u) ? $u : '';
    }

    public function attempt(string $username, string $password): bool
    {
        if (!hash_equals($this->username, $username)) {
            return false;
        }
        if (!hash_equals($this->password, $password)) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = $username;
        return true;
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }
}

