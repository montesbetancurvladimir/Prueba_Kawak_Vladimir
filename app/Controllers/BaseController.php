<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Core\Security\Auth;
use App\Core\Security\Csrf;
use App\Core\View;
use App\Http\Response;

abstract class BaseController
{
    /** @var View */
    protected $view;
    /** @var Auth */
    protected $auth;
    /** @var Csrf */
    protected $csrf;
    /** @var Flash */
    protected $flash;

    public function __construct(View $view, Auth $auth, Csrf $csrf, Flash $flash)
    {
        $this->view = $view;
        $this->auth = $auth;
        $this->csrf = $csrf;
        $this->flash = $flash;
    }

    protected function requireAuth(): ?Response
    {
        if ($this->auth->check()) {
            return null;
        }
        return Response::redirect('/login');
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $template, array $data = [], int $status = 200): Response
    {
        $flash = $this->flash->pull();

        $content = $this->view->render($template, $data + [
            'csrfToken' => $this->csrf->token(),
            'authUser' => $this->auth->user(),
        ]);

        $html = $this->view->render('layout.php', [
            'content' => $content,
            'csrfToken' => $this->csrf->token(),
            'authUser' => $this->auth->user(),
            'flash' => $flash,
        ]);

        return new Response($html, $status, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    protected function badRequest(string $message): Response
    {
        return new Response($message, 400, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}

