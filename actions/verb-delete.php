<?php
declare(strict_types=1);
$auth->requireAdmin();
$csrf->verify();
$id = (int)($_POST['id'] ?? 0);
if ($id > 0 && $verbRepository->delete($id)) {
    $flash->set('success', 'Verb gelöscht.');
} else {
    $flash->set('error', 'Verb konnte nicht gelöscht werden.');
}
header('Location: index.php?action=admin&notice=verb#verben');
exit;
