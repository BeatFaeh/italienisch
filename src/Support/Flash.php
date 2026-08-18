<?php
declare(strict_types=1);

final class Flash
{
    public function set(string $type, string $message): void
    {
        $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
    }

    public function take(): ?array
    {
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        return is_array($message) ? $message : null;
    }
}
