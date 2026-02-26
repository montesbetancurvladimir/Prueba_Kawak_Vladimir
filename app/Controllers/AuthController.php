<?php

namespace App\Controllers;

use App\Core\Flash;
use App\Core\Security\Auth;
use App\Core\Security\Csrf;
use App\Core\View;
use App\Http\Request;
use App\Http\Response;

final class AuthController extends BaseController
{
    public function __construct(View $view, Auth $auth, Csrf $csrf, Flash $flash)
    {
        parent::__construct($view, $auth, $csrf, $flash);
    }

    /**
     * @param array<string, string> $params
     */
    public function showLogin(Request $request, array $params): Response
    {
        if ($this->auth->check()) {
            return Response::redirect('/documents');
        }

        return $this->render('auth/login.php', [
            'error' => '',
            'username' => '',
        ]);
    }

    /**
     * @param array<string, string> $params
     */
    public function login(Request $request, array $params): Response
    {
        if (!$this->csrf->verify($request->postString('_csrf'))) {
            return $this->badRequest('CSRF inválido');
        }

        $username = $request->postString('username');
        $password = $request->postString('password');

        if ($this->auth->attempt($username, $password)) {
            $this->flash->set('success', 'Sesión iniciada');
            return Response::redirect('/documents');
        }

        return $this->render('auth/login.php', [
            'error' => 'Usuario o contraseña inválidos',
            'username' => $username,
        ], 422);
    }

    /**
     * @param array<string, string> $params
     */
    public function logout(Request $request, array $params): Response
    {
        if (!$this->csrf->verify($request->postString('_csrf'))) {
            return $this->badRequest('CSRF inválido');
        }

        $this->auth->logout();
        $this->flash->set('success', 'Sesión finalizada');
        return Response::redirect('/login');
    }
}

