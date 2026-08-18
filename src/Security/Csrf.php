<?php
declare(strict_types=1);

final class Csrf
{
    public function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return (string)$_SESSION['csrf_token'];
    }

    public function verify(): void
    {
        $submitted = (string)($_POST['csrf_token'] ?? '');
        $stored = (string)($_SESSION['csrf_token'] ?? '');

        if ($submitted === '' || $stored === '' || !hash_equals($stored, $submitted)) {
            http_response_code(403);
            die('Ungültige oder abgelaufene Anfrage.');
        }
    }
}
