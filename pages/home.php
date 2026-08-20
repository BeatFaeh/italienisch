<?php

declare(strict_types=1);

$mode = in_array(
    ($_GET['modus'] ?? 'text'),
    ['text', 'mc'],
    true
) ? (string) $_GET['modus'] : 'text';

$direction = ($_GET['richtung'] ?? 'de-it') === 'it-de'
    ? 'it-de'
    : 'de-it';

$type = ($_GET['typ'] ?? 'wort') === 'satz'
    ? 'satz'
    : 'wort';

$lessonRaw = (string) ($_GET['lektion'] ?? '');

$lesson = $lessonRaw !== '' && ctype_digit($lessonRaw)
    ? (int) $lessonRaw
    : null;

$search = trim((string) ($_GET['suche'] ?? ''));

$msg = '';

if ($search !== '') {
    $card = ctype_digit($search)
        ? $cardRepository->findById(
            (int) $search,
            $direction,
            $type
        )
        : $cardRepository->findByTerm(
            $search,
            $direction,
            $type,
            $lesson
        );

    if (!$card) {
        $msg = 'Keine passende Lernkarte gefunden.';
    }
} else {
    $card = $cardRepository->random(
        $direction,
        $type,
        $lesson
    );
}

$count = $cardRepository->count(
    $direction,
    $type,
    $lesson
);

$lessons = $cardRepository->lessons();

$quiz = [
    'options'     => [],
    'correct_key' => '',
];

if ($mode === 'mc' && $card) {
    $quiz = $quizService->build(
        $card,
        $direction,
        $type,
        $lesson
    );
}

$params = 'richtung=' . urlencode($direction)
    . '&typ=' . urlencode($type)
    . ($lesson !== null ? '&lektion=' . $lesson : '');

?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">

    <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
    >

    <title>Italienische Lernkarten</title>

    <link
            rel="stylesheet"
            href="assets/css/main.css"
    >
</head>

<body>

<main class="page">
    <div class="wrapper">

        <?php require __DIR__ . '/../partials/public-hero.php'; ?>

        <section class="control-card">

            <form
                    method="get"
                    action="index.php"
                    class="filters"
            >
                <input
                        type="hidden"
                        name="modus"
                        value="<?= Html::e($mode) ?>"
                >

                <label>
                    Richtung

                    <select name="richtung">
                        <option
                                value="de-it"
                            <?= $direction === 'de-it' ? 'selected' : '' ?>
                        >
                            Deutsch → Italienisch
                        </option>

                        <option
                                value="it-de"
                            <?= $direction === 'it-de' ? 'selected' : '' ?>
                        >
                            Italienisch → Deutsch
                        </option>
                    </select>
                </label>

                <label>
                    Inhalt

                    <select name="typ">
                        <option
                                value="wort"
                            <?= $type === 'wort' ? 'selected' : '' ?>
                        >
                            Wörter
                        </option>

                        <option
                                value="satz"
                            <?= $type === 'satz' ? 'selected' : '' ?>
                        >
                            Sätze
                        </option>
                    </select>
                </label>

                <label>
                    Lektion

                    <select name="lektion">
                        <option value="">
                            Alle Lektionen
                        </option>

                        <?php foreach ($lessons as $l): ?>
                            <option
                                    value="<?= $l ?>"
                                <?= $lesson === $l ? 'selected' : '' ?>
                            >
                                Lektion <?= $l ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="search-grow">
                    ID oder Suchbegriff

                    <input
                            type="search"
                            name="suche"
                            value="<?= Html::e($search) ?>"
                            placeholder="z. B. amore oder 12"
                    >
                </label>

                <button
                        class="button button-primary"
                        type="submit"
                >
                    Anzeigen
                </button>
            </form>

            <nav class="mode-switch">

                <a
                        class="mode-button <?= $mode === 'text' ? 'active' : '' ?>"
                        href="index.php?modus=text&<?= $params ?>"
                >
                    📖 Lernkarte
                </a>

                <a
                        class="mode-button <?= $mode === 'mc' ? 'active' : '' ?>"
                        href="index.php?modus=mc&<?= $params ?>"
                >
                    ✓ Multiple Choice
                </a>

            </nav>

        </section>

        <?php if ($msg !== ''): ?>
            <div class="search-message">
                <?= Html::e($msg) ?>
            </div>
        <?php endif; ?>

        <section class="learning-card">
            <div class="card-content">

                <?php if (!$card): ?>

                    <h2>
                        Keine Lernkarten für diese Auswahl vorhanden
                    </h2>

                    <p>
                        Prüfe Lektion, Lernrichtung oder ob für diese
                        Zeilen vollständige Daten vorhanden sind.
                    </p>

                <?php else: ?>

                    <div class="meta">

                        <span class="badge">
                            DB-ID #<?= (int) $card['id'] ?>
                        </span>

                        <span class="badge">
                            <?= $type === 'wort' ? 'Wort' : 'Satz' ?>
                        </span>

                        <span class="badge lesson">
                            Lektion
                            <?= Html::e((string) ($card['lektion'] ?? '–')) ?>
                        </span>

                        <span class="counter">
                            <?= $count ?> Karten
                        </span>

                    </div>

                    <p class="question-label">
                        <?= $direction === 'de-it'
                            ? 'Deutsch → Italienisch'
                            : 'Italienisch → Deutsch'
                        ?>
                    </p>

                    <h2 class="question">
                        <?= Html::e((string) $card['frage']) ?>
                    </h2>

                    <?php if ($mode === 'mc'): ?>

                        <?php if (count($quiz['options']) === 4): ?>

                            <div
                                    class="mc-quiz"
                                    data-correct="<?= Html::e($quiz['correct_key']) ?>"
                            >

                                <?php foreach ($quiz['options'] as $letter => $answer): ?>

                                    <button
                                            type="button"
                                            class="mc-option"
                                            data-option="<?= Html::e($letter) ?>"
                                    >
                                        <span class="mc-letter">
                                            <?= Html::e($letter) ?>
                                        </span>

                                        <span>
                                            <?= Html::e($answer) ?>
                                        </span>
                                    </button>

                                <?php endforeach; ?>

                                <div
                                        class="mc-feedback"
                                        aria-live="polite"
                                ></div>

                            </div>

                        <?php else: ?>

                            <div class="mc-error">
                                Für Multiple Choice werden mindestens
                                vier unterschiedliche Antworten in der
                                gewählten Lektion benötigt.
                            </div>

                        <?php endif; ?>

                    <?php else: ?>

                        <details class="accordion">

                            <summary>
                                Übersetzung anzeigen
                            </summary>

                            <div class="accordion-content">

                                <div class="answer-main">
                                    <?= nl2br(
                                        Html::e(
                                            (string) $card['antwort']
                                        )
                                    ) ?>
                                </div>

                                <?php if (
                                    $type === 'wort'
                                    && trim((string) $card['satz_de']) !== ''
                                    && trim((string) $card['satz_it']) !== ''
                                ): ?>

                                    <div class="example">

                                        <strong>
                                            Beispielsatz
                                        </strong>

                                        <span>
                                            <?= Html::e(
                                                (string) $card['satz_it']
                                            ) ?>
                                        </span>

                                        <span>
                                            <?= Html::e(
                                                (string) $card['satz_de']
                                            ) ?>
                                        </span>

                                    </div>

                                <?php endif; ?>

                            </div>

                        </details>

                    <?php endif; ?>

                <?php endif; ?>

                <div class="actions">

                    <a
                            class="button button-primary"
                            href="index.php?modus=<?= $mode ?>&<?= $params ?>"
                    >
                        <?= $mode === 'mc'
                            ? 'Neue Multiple-Choice-Frage'
                            : 'Neue Zufallskarte'
                        ?>
                    </a>

                    <a
                            class="button button-secondary"
                            href="index.php?action=pruefung&<?= $params ?>"
                    >
                        📝 Prüfung
                    </a>

                    <a
                            class="button button-secondary"
                            href="index.php?action=pdf&<?= $params ?>"
                    >
                        🖨 Alle Karten / PDF
                    </a>

                    <a
                            class="button button-secondary"
                            href="index.php?action=verben"
                    >
                        🔤 Verben
                    </a>

                    <a
                            class="button button-secondary"
                            href="vorlagen/"
                    >
                        📁 Vorlagen
                    </a>

                    <a
                            class="button button-secondary"
                            href="grammatik/"
                    >
                        📚 Grammatik
                    </a>

                    <a
                            class="button button-admin"
                            href="index.php?action=admin"
                    >
                        Administration
                    </a>

                </div>

            </div>
        </section>

        <?php require __DIR__ . '/../partials/site-footer.php'; ?>

    </div>
</main>

<script src="assets/js/quiz.js"></script>

</body>
</html>