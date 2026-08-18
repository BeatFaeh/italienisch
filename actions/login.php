<?php
declare(strict_types=1);
$csrf->verify();
$password = (string)($_POST['password'] ?? '');
$returnTo = trim((string)($_POST['return_to'] ?? ''));
$stored = $settingsRepository->get('admin_password_hash', (string)$appConfig['admin_password_hash']);
$allowedReturnTargets = ['vorlagen/', 'index.php?action=verben', 'index.php?action=verben-pdf'];

if (password_verify($password, $stored)) {
    $auth->login();
    $flash->set('success', 'Anmeldung erfolgreich.');
    if (in_array($returnTo, $allowedReturnTargets, true)) {
        header('Location: ' . $returnTo);
        exit;
    }
    header('Location: index.php?action=admin');
    exit;
}

$flash->set('error', 'Passwort nicht korrekt.');
$location = 'index.php?action=admin';
if (in_array($returnTo, $allowedReturnTargets, true)) {
    $location .= '&return_to=' . rawurlencode($returnTo);
}
header('Location: ' . $location);
exit;
