<?php
declare(strict_types=1);
if (!$auth->isAdmin()) {
    $returnTo = 'index.php?action=verben-pdf';
    if (isset($_GET['sort'])) $returnTo .= '&sort=' . rawurlencode((string)$_GET['sort']);
    if (isset($_GET['suche'])) $returnTo .= '&suche=' . rawurlencode((string)$_GET['suche']);
    header('Location: index.php?action=admin&return_to=' . rawurlencode($returnTo));
    exit;
}
$search = trim((string)($_GET['suche'] ?? ''));
$sort = (string)($_GET['sort'] ?? 'it');
$sort = $sort === 'de' ? 'de' : 'it';
$verbs = $verbRepository->all($search, $sort);
$sortLabel = $sort === 'de' ? 'Deutsch A–Z' : 'Italienisch A–Z';
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
    <div><strong><?=count($verbs)?> <?=count($verbs) === 1 ? 'Verb' : 'Verben'?></strong> · Sortierung: <?=Html::e($sortLabel)?></div>
    <div class="toolbar-actions">
        <form method="get" action="index.php" class="sort-form">
            <input type="hidden" name="action" value="verben-pdf">
            <?php if ($search !== ''): ?><input type="hidden" name="suche" value="<?=Html::e($search)?>"><?php endif; ?>
            <label>PDF sortieren nach
                <select name="sort" onchange="this.form.submit()">
                    <option value="it" <?=$sort==='it'?'selected':''?>>Italienisch A–Z</option>
                    <option value="de" <?=$sort==='de'?'selected':''?>>Deutsch A–Z</option>
                </select>
            </label>
            <noscript><button type="submit">Sortieren</button></noscript>
        </form>
        <button type="button" onclick="window.print()">Drucken / Als PDF speichern</button>
        <a class="button" href="index.php?action=verben<?= $search !== '' ? '&amp;suche=' . rawurlencode($search) : '' ?>">Zurück</a>
    </div>
</div>
<main class="document">
    <h1>Italienische Verben</h1>
    <p class="subtitle">Übersicht aller Verbformen · Sortierung: <?=Html::e($sortLabel)?><?= $search !== '' ? ' · Suche: ' . Html::e($search) : '' ?></p>
    <table>
        <thead><tr><th>ID</th><th>Italienisch</th><th>Deutsch</th><th>Endung</th><th>Präsens</th><th>Perfekt</th><th>Futur</th><th>Imperativ</th></tr></thead>
        <tbody>
        <?php foreach ($verbs as $verb): ?>
            <tr>
                <td><?=(int)$verb['id']?></td>
                <td><strong><?=Html::e((string)$verb['verb_it'])?></strong></td>
                <td><?=Html::e((string)$verb['verb_de'])?></td>
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
