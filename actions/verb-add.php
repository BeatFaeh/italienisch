<?php
declare(strict_types=1);
$auth->requireAdmin();
$csrf->verify();
$verb = trim((string)($_POST['verb'] ?? ''));
$praesens = trim((string)($_POST['praesens'] ?? ''));
$perfekt = trim((string)($_POST['perfekt'] ?? ''));
$futur = trim((string)($_POST['futur'] ?? ''));
$imperativ = trim((string)($_POST['imperativ'] ?? ''));
$endung = trim((string)($_POST['endung'] ?? ''));
$allowedEndings = ['ire','are','ere','unregelmässig'];
if (!in_array($endung, $allowedEndings, true)) { $endung = 'unregelmässig'; }

if ($verb === '') {
    $flash->set('error', 'Das Verb muss ausgefüllt sein.');
} elseif ($verbRepository->add($verb, $praesens, $perfekt, $futur, $imperativ, $endung)) {
    $flash->set('success', 'Verb gespeichert.');
} else {
    $flash->set('error', 'Verb konnte nicht gespeichert werden.');
}
header('Location: index.php?action=admin&notice=verb#verben');
exit;
