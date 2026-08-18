<?php
declare(strict_types=1);
$auth->requireAdmin();
$csrf->verify();
$id = (int)($_POST['id'] ?? 0);
if ($id > 0 && $grammarRepository->delete($id)) {
    $flash->set('success', 'Grammatikeintrag gelöscht.');
} else {
    $flash->set('error', 'Grammatikeintrag konnte nicht gelöscht werden.');
}
header('Location: index.php?action=admin&notice=grammar#grammatik');
exit;
