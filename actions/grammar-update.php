<?php
declare(strict_types=1);
$auth->requireAdmin();
$csrf->verify();
$id = (int)($_POST['id'] ?? 0);
$stichwort = trim((string)($_POST['stichwort'] ?? ''));
$erklaerung = trim((string)($_POST['erklaerung'] ?? ''));
$pdf = trim((string)($_POST['pdf'] ?? ''));
if ($id <= 0 || $stichwort === '') {
    $flash->set('error', 'Ungültiger oder unvollständiger Grammatikeintrag.');
} elseif ($grammarRepository->update($id, $stichwort, $erklaerung, $pdf)) {
    $flash->set('success', 'Grammatikeintrag aktualisiert.');
} else {
    $flash->set('error', 'Grammatikeintrag konnte nicht aktualisiert werden.');
}
header('Location: index.php?action=admin&notice=grammar#grammatik');
exit;
