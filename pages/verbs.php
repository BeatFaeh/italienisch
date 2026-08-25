<?php
declare(strict_types=1);
$search = trim((string)($_GET['suche'] ?? ''));
$verbs = $verbRepository->all($search, 'it');
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Italienische Verben</title>
    <link rel="stylesheet" href="assets/css/verbs.css">
</head>
<body>
<main class="verbs-page">
    <header class="verbs-header">
        <div>
            <p class="eyebrow">Verbenübersicht</p>
            <h1>Italienische Verben</h1>
            <p>Italienische und deutsche Verben mit allen erfassten Verbformen.</p>
        </div>
        <nav class="verbs-actions">
            <a class="button" href="index.php?action=verben-pdf&amp;sort=it<?= $search !== '' ? '&amp;suche=' . rawurlencode($search) : '' ?>">🖨 PDF Italienisch A–Z</a>
            <a class="button" href="index.php?action=verben-pdf&amp;sort=de<?= $search !== '' ? '&amp;suche=' . rawurlencode($search) : '' ?>">🖨 PDF Deutsch A–Z</a>
            <a class="button secondary" href="index.php?action=admin#verben">✏ Verben bearbeiten</a>
            <a class="button neutral" href="index.php">Lernkarten</a>
        </nav>
    </header>

    <section class="search-panel">
        <form method="get" action="index.php">
            <input type="hidden" name="action" value="verben">
            <label>Volltextsuche – Italienisch, Deutsch oder Verbform
                <input type="search" name="suche" value="<?=Html::e($search)?>" placeholder="z. B. essere, sein, sono, ruf oder 12">
            </label>
            <button type="submit">Suchen</button>
            <?php if ($search !== ''): ?><a class="button neutral" href="index.php?action=verben">Alle anzeigen</a><?php endif; ?>
        </form>
        <p class="count"><?=count($verbs)?> <?=count($verbs) === 1 ? 'Verb' : 'Verben'?><?= $search !== '' ? ' gefunden' : ' vorhanden' ?>.</p>
    </section>

    <section class="verb-list">
        <?php if (!$verbs): ?>
            <div class="empty">Keine Verben gefunden.</div>
        <?php else: ?>
            <?php foreach ($verbs as $verb): ?>
                <article class="verb-card">
                    <div class="verb-title">
                        <span class="id">#<?=(int)$verb['id']?></span>
                        <h2><?=Html::e((string)$verb['verb_it'])?></h2>
                        <span class="verb-de">↔ <?=Html::e((string)$verb['verb_de'])?></span>
                        <?php if (trim((string)$verb['endung']) !== ''): ?><span class="ending"><?=Html::e((string)$verb['endung'])?></span><?php endif; ?>
                    </div>
                    <dl>
                        <div><dt>Präsens</dt><dd><?=nl2br(Html::e((string)$verb['praesens']))?></dd></div>
                        <div><dt>Perfekt</dt><dd><?=nl2br(Html::e((string)$verb['perfekt']))?></dd></div>
                        <div><dt>Futur</dt><dd><?=nl2br(Html::e((string)$verb['futur']))?></dd></div>
                        <div><dt>Imperativ</dt><dd><?=nl2br(Html::e((string)$verb['imperativ']))?></dd></div>
                    </dl>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
