<?php
declare(strict_types=1);
if (!$auth->isAdmin()) {
    header('Location: index.php?action=admin&return_to=' . rawurlencode('index.php?action=verben-pdf'));
    exit;
}
$search = trim((string)($_GET['suche'] ?? ''));
$verbs = $verbRepository->all($search);
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Italienische Verben – PDF</title>
    <link rel="stylesheet" href="assets/css/verbs-print.css">
</head>
<body>
<div class="toolbar">
    <div><strong><?=count($verbs)?> <?=count($verbs) === 1 ? 'Verb' : 'Verben'?></strong></div>
    <div><button type="button" onclick="window.print()">Drucken / Als PDF speichern</button> <a class="button" href="index.php?action=verben<?= $search !== '' ? '&suche=' . rawurlencode($search) : '' ?>">Zurück</a></div>
</div>
<main class="document">
    <h1>Italienische Verben</h1>
    <p class="subtitle">Übersicht aller Verbformen<?= $search !== '' ? ' · Suche: ' . Html::e($search) : '' ?></p>
    <table>
        <thead><tr><th>ID</th><th>Verb</th><th>Endung</th><th>Präsens</th><th>Perfekt</th><th>Futur</th><th>Imperativ</th></tr></thead>
        <tbody>
        <?php foreach ($verbs as $verb): ?>
            <tr>
                <td><?=(int)$verb['id']?></td>
                <td><strong><?=Html::e((string)$verb['verb'])?></strong></td>
                <td><?=Html::e((string)$verb['endung'])?></td>
                <td><?=nl2br(Html::e((string)$verb['praesens']))?></td>
                <td><?=nl2br(Html::e((string)$verb['perfekt']))?></td>
                <td><?=nl2br(Html::e((string)$verb['futur']))?></td>
                <td><?=nl2br(Html::e((string)$verb['imperativ']))?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
