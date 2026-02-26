<?php

namespace App\Http;

final class Response
{
    /** @var int */
    private $status;
    /** @var array<string, string> */
    private $headers;
    /** @var string */
    private $body;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(string $body, int $status = 200, array $headers = [])
    {
        $this->body = $body;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function redirect(string $to): self
    {
        return new self('', 302, ['Location' => $to]);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header($k . ': ' . $v);
        }
        echo $this->body;
    }
}

