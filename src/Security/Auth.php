<?php
declare(strict_types=1);

final class Auth
{
    public function isAdmin(): bool
    {
        return ($_SESSION['admin_authenticated'] ?? false) === true;
    }

    public function login(): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_authenticated'] = true;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?action=admin');
            exit;
        }
    }
}
