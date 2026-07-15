<?php

//Controlador para manejar la autenticación de usuarios, incluyendo inicio de sesión, cierre de sesión y gestión de sesiones utilizando la clase Session

namespace OfficeHub\Controllers;

use OfficeHub\Core\Controller;
use OfficeHub\Core\Session;
use OfficeHub\Core\Response;
use OfficeHub\Models\PasswordReset;
use OfficeHub\Models\User;
use OfficeHub\Services\MailService;
use Throwable;

class AuthController extends Controller
{
    // GET /login
    public function showLogin(): void
    {
        $error = Session::getFlash('error');
        $success = Session::getFlash('success');
        $mode = 'login';
        require BASE_PATH . '/src/Views/auth/login.php';
    }

    // POST /login
    public function login(array $params = []): void
    {
        $username = trim($this->request->input('username', ''));
        $password = $this->request->input('password', '');

        if (empty($username) || empty($password)) {
            Session::flash('error', 'Completá usuario y contraseña.');
            $this->redirect('/login');
        }

        $user = User::findByUsername($username);

        if (!$user || !User::verifyPassword($password, $user['password_hash'])) {
            Session::flash('error', 'Usuario o contraseña incorrectos.');
            $this->redirect('/login');
        }

        unset($user['password_hash']);
        Session::regenerate();
        Session::setUser($user);

        if (($user['role'] ?? '') !== 'admin' && (int)($user['repo_access'] ?? 1) !== 1 && in_array(($user['support_role'] ?? 'none'), ['support_admin', 'support_viewer'], true)) {
            $this->redirect('/soporte');
        }

        $this->redirect('/');
    }

    // GET /olvide-mi-contrasena
    public function showForgotPassword(array $params = []): void
    {
        $error = Session::getFlash('error');
        $success = Session::getFlash('success');
        $mode = 'forgot';
        require BASE_PATH . '/src/Views/auth/login.php';
    }

    // POST /olvide-mi-contrasena
    public function requestPasswordReset(array $params = []): void
    {
        $email = strtolower(trim((string)$this->request->input('email', '')));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Ingresá una dirección de correo válida.');
            $this->redirect('/olvide-mi-contrasena');
        }

        $user = User::findActiveByEmail($email);

        if ($user && !PasswordReset::hasRecentRequest((int)$user['id'])) {
            $token = bin2hex(random_bytes(32));

            PasswordReset::cleanupExpired();
            PasswordReset::create(
                (int)$user['id'],
                $token,
                $_SERVER['REMOTE_ADDR'] ?? null
            );

            $resetUrl = url('restablecer-contrasena?token=' . rawurlencode($token));

            try {
                MailService::sendPasswordReset(
                    (string)$user['email'],
                    (string)$user['username'],
                    $resetUrl
                );
            } catch (Throwable $exception) {
                PasswordReset::invalidatePending((int)$user['id']);
                error_log('OfficeHub password reset mail: ' . MailService::errorSummary($exception));
            }
        }

        Session::flash(
            'success',
            'Si el correo corresponde a una cuenta activa, recibirás un enlace para restablecer la contraseña.'
        );
        $this->redirect('/olvide-mi-contrasena');
    }

    // GET /restablecer-contrasena
    public function showResetPassword(array $params = []): void
    {
        $token = trim((string)$this->request->query('token', ''));
        $reset = PasswordReset::findValid($token);
        $tokenValid = $reset !== null;
        $error = Session::getFlash('error');
        $success = null;
        $mode = 'reset';

        require BASE_PATH . '/src/Views/auth/login.php';
    }

    // POST /restablecer-contrasena
    public function resetPassword(array $params = []): void
    {
        $token = trim((string)$this->request->input('token', ''));
        $password = (string)$this->request->input('password', '');
        $passwordConfirmation = (string)$this->request->input('password_confirmation', '');
        $resetUrl = '/restablecer-contrasena?token=' . rawurlencode($token);

        if (!PasswordReset::findValid($token)) {
            Session::flash('error', 'El enlace es inválido, ya fue utilizado o venció.');
            $this->redirect($resetUrl);
        }

        if (strlen($password) < 8) {
            Session::flash('error', 'La nueva contraseña debe tener al menos 8 caracteres.');
            $this->redirect($resetUrl);
        }

        if ($password !== $passwordConfirmation) {
            Session::flash('error', 'Las contraseñas no coinciden.');
            $this->redirect($resetUrl);
        }

        if (!PasswordReset::consumeAndUpdatePassword($token, $password)) {
            Session::flash('error', 'El enlace es inválido, ya fue utilizado o venció.');
            $this->redirect($resetUrl);
        }

        Session::flash('success', 'Tu contraseña fue actualizada. Ya podés iniciar sesión.');
        $this->redirect('/login');
    }

    // GET /logout
    public function logout(array $params = []): void
    {
        Session::logout();
        Session::destroy();
        $this->redirect('/login');
    }
}
