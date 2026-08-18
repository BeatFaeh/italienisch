<?php
declare(strict_types=1);
$auth->requireAdmin();
$csrf->verify();
$current = (string)($_POST['current_password'] ?? '');
$new = (string)($_POST['new_password'] ?? '');
$repeat = (string)($_POST['new_password_repeat'] ?? '');
$stored = $settingsRepository->get('admin_password_hash', (string)$appConfig['admin_password_hash']);
if (!password_verify($current, $stored)) $flash->set('error', 'Das bisherige Passwort ist nicht korrekt.');
elseif (mb_strlen($new, 'UTF-8') < 12) $flash->set('error', 'Das neue Passwort muss mindestens 12 Zeichen lang sein.');
elseif ($new !== $repeat) $flash->set('error', 'Die beiden neuen Passwörter stimmen nicht überein.');
elseif (password_verify($new, $stored)) $flash->set('error', 'Das neue Passwort muss sich vom bisherigen Passwort unterscheiden.');
else {
    $hash = password_hash($new, PASSWORD_DEFAULT);
    if (is_string($hash) && $settingsRepository->save('admin_password_hash', $hash)) {
        session_regenerate_id(true);
        $flash->set('success', 'Das Administrationspasswort wurde erfolgreich geändert.');
    } else $flash->set('error', 'Das neue Passwort konnte nicht gespeichert werden.');
}
header('Location: index.php?action=admin#passwort');
exit;
