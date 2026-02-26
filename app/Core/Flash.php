<?php

namespace App\Core;

final class Flash
{
    /** @var string */
    private $key = '_flash';

    public function set(string $type, string $message): void
    {
        $_SESSION[$this->key] = ['type' => $type, 'message' => $message];
    }

    /** @return array{type:string,message:string}|null */
    public function pull(): ?array
    {
        $v = $_SESSION[$this->key] ?? null;
        unset($_SESSION[$this->key]);

        if (!is_array($v) || !isset($v['type'], $v['message'])) {
            return null;
        }
        if (!is_string($v['type']) || !is_string($v['message'])) {
            return null;
        }
        return ['type' => $v['type'], 'message' => $v['message']];
    }
}

