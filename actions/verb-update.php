<?php
declare(strict_types=1);
$auth->requireAdmin();
$csrf->verify();
$id = (int)($_POST['id'] ?? 0);
$verb = trim((string)($_POST['verb'] ?? ''));
$praesens = trim((string)($_POST['praesens'] ?? ''));
$perfekt = trim((string)($_POST['perfekt'] ?? ''));
$futur = trim((string)($_POST['futur'] ?? ''));
$imperativ = trim((string)($_POST['imperativ'] ?? ''));
$endung = trim((string)($_POST['endung'] ?? ''));
$allowedEndings = ['ire','are','ere','unregelmässig'];
if (!in_array($endung, $allowedEndings, true)) { $endung = 'unregelmässig'; }

if ($id <= 0 || $verb === '') {
    $flash->set('error', 'Ungültige oder unvollständige Verbdaten.');
} elseif ($verbRepository->update($id, $verb, $praesens, $perfekt, $futur, $imperativ, $endung)) {
    $flash->set('success', 'Verb aktualisiert.');
} else {
    $flash->set('error', 'Verb konnte nicht aktualisiert werden.');
}
header('Location: index.php?action=admin&notice=verb#verben');
exit;
