<?php
declare(strict_types=1);
$auth->requireAdmin();
$csrf->verify();
$stichwort = trim((string)($_POST['stichwort'] ?? ''));
$erklaerung = trim((string)($_POST['erklaerung'] ?? ''));
$pdf = trim((string)($_POST['pdf'] ?? ''));
if ($stichwort === '') {
    $flash->set('error', 'Das Stichwort muss ausgefüllt sein.');
} elseif ($grammarRepository->add($stichwort, $erklaerung, $pdf)) {
    $flash->set('success', 'Grammatikeintrag gespeichert.');
} else {
    $flash->set('error', 'Grammatikeintrag konnte nicht gespeichert werden.');
}
header('Location: index.php?action=admin&notice=grammar#grammatik');
exit;
