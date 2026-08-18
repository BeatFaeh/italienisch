<?php
declare(strict_types=1);

$direction = ($_GET['richtung'] ?? 'de-it') === 'it-de' ? 'it-de' : 'de-it';
$type = 'satz'; // Prüfungen verwenden immer vollständige Sätze.

$lessonRaw = (string)($_GET['lektion'] ?? '');
$lesson = $lessonRaw !== '' && ctype_digit($lessonRaw) ? (int)$lessonRaw : null;
$lessons = $cardRepository->lessons();

$countRaw = (string)($_GET['anzahl'] ?? '25');
$allowedCounts = ['25', '50', '1002', 'alle'];
if (!in_array($countRaw, $allowedCounts, true)) $countRaw = '25';
$requested = $countRaw === 'alle' ? null : (int)$countRaw;

$exam = $examService->build($requested, $direction, $type, $lesson);
$questions = $exam['questions'];
$total = $exam['count'];
$available = $cardRepository->count($direction, $type, $lesson);

$params = 'richtung='.urlencode($direction)
    .'&typ='.urlencode($type)
    .'&anzahl='.urlencode($countRaw)
    .($lesson !== null ? '&lektion='.$lesson : '');

$requestedLabel = $countRaw === 'alle' ? 'Alle verfügbaren Fragen' : $countRaw.' Fragen';
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Italienisch-Prüfung</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/exam.css">
</head>
<body>
<main class="exam-page">
<div class="exam-wrapper">
<?php require __DIR__.'/../partials/public-hero.php'; ?>

<section class="exam-config-card">
    <div>
        <p class="question-label">Prüfung zusammenstellen</p>
        <h2>Fragen und Lektion wählen</h2>
    </div>
    <form method="get" action="index.php" class="exam-config-form">
        <input type="hidden" name="action" value="pruefung">
        <input type="hidden" name="richtung" value="<?=Html::e($direction)?>">
        <input type="hidden" name="typ" value="satz">

        <label>
            Anzahl Fragen
            <select name="anzahl">
                <option value="25" <?=$countRaw==='25'?'selected':''?>>25</option>
                <option value="50" <?=$countRaw==='50'?'selected':''?>>50</option>
                <option value="1002" <?=$countRaw==='1002'?'selected':''?>>1002</option>
                <option value="alle" <?=$countRaw==='alle'?'selected':''?>>Alle</option>
            </select>
        </label>

        <label>
            Lektion
            <select name="lektion">
                <option value="" <?=$lesson===null?'selected':''?>>Alle Lektionen</option>
                <?php foreach ($lessons as $l): ?>
                    <option value="<?=$l?>" <?=$lesson===$l?'selected':''?>>Lektion <?=$l?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <button class="button button-primary" type="submit">Prüfung starten</button>
    </form>
</section>

<section class="exam-intro">
    <div>
        <p class="question-label">Wissenstest</p>
        <h2><?=Html::e($requestedLabel)?></h2>
        <p>
            <?=$direction==='de-it'?'Deutsch → Italienisch':'Italienisch → Deutsch'?>
            · Ganze Sätze
            · <?=$lesson!==null?'Lektion '.$lesson:'Alle Lektionen'?>
        </p>
        <?php if ($requested !== null && $available < $requested): ?>
            <p class="exam-hint">Für diese Auswahl sind <?=$available?> Fragen verfügbar; die Prüfung verwendet daher alle verfügbaren Fragen.</p>
        <?php endif; ?>
    </div>
    <div class="exam-progress-box"><strong id="answered-count">0</strong> / <?=$total?> beantwortet</div>
</section>

<?php if ($total === 0): ?>
<section class="learning-card">
    <div class="card-content">
        <h2>Keine Prüfungsfragen verfügbar</h2>
        <p>Für Multiple Choice werden mindestens vier verschiedene Antworten benötigt.</p>
        <a class="button button-secondary" href="index.php?richtung=<?=urlencode($direction)?>&typ=<?=urlencode($type)?><?= $lesson!==null?'&lektion='.$lesson:''?>">← Zurück</a>
    </div>
</section>
<?php else: ?>
<form id="exam-form" autocomplete="off">
    <?php foreach ($questions as $i => $q): ?>
    <section class="exam-question" data-correct="<?=Html::e($q['correct_key'])?>">
        <div class="exam-question-head">
            <span class="badge">Frage <?=$i+1?> von <?=$total?></span><span class="badge">DB-ID #<?=(int)$q['id']?></span>
            <span class="exam-status">Noch nicht beantwortet</span>
        </div>
        <h2><?=Html::e($q['frage'])?></h2>
        <div class="exam-options">
            <?php foreach ($q['options'] as $letter => $answer): ?>
            <label class="exam-option">
                <input type="radio" name="question_<?=$i+1?>" value="<?=Html::e($letter)?>">
                <span class="mc-letter"><?=Html::e($letter)?></span>
                <span class="exam-answer"><?=Html::e($answer)?></span>
            </label>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>

    <section class="exam-submit-card">
        <p><strong id="remaining-count"><?=$total?></strong> Fragen offen.</p>
        <button type="button" id="evaluate-exam" class="button button-primary exam-evaluate" disabled>Prüfung auswerten</button>
        <a class="button button-secondary" href="index.php?richtung=<?=urlencode($direction)?>&typ=<?=urlencode($type)?><?= $lesson!==null?'&lektion='.$lesson:''?>">Abbrechen</a>
    </section>
</form>

<section id="exam-result" class="exam-result" hidden>
    <p class="question-label">Dein Ergebnis</p>
    <div class="exam-score"><strong id="score-value">0</strong><span>von <?=$total?> Punkten</span></div>
    <p id="score-percent"></p>
    <p id="score-message"></p>
    <div class="exam-result-actions">
        <a class="button button-primary" href="index.php?action=pruefung&<?=$params?>">Neue Prüfung</a>
        <a class="button button-secondary" href="index.php?richtung=<?=urlencode($direction)?>&typ=<?=urlencode($type)?><?= $lesson!==null?'&lektion='.$lesson:''?>">Zurück</a>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__.'/../partials/site-footer.php'; ?>
</div>
</main>
<?php if ($total > 0): ?><script src="assets/js/exam.js"></script><?php endif; ?>
</body>
</html>
