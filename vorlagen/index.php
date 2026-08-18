<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap/init.php';

if (!$auth->isAdmin()) {
    $returnTo = basename(__DIR__) . '/';
    header('Location: ../index.php?action=admin&return_to=' . rawurlencode($returnTo));
    exit;
}

$currentDirectoryPath = __DIR__;
require __DIR__ . '/../directory-actions.php';
$managerFlash = $flash->take();

/*
|--------------------------------------------------------------------------
| Italienischer Vorlagen-Browser
|--------------------------------------------------------------------------
| Diese Datei zeigt automatisch die Dateien des Verzeichnisses an, in dem
| index.php liegt. Dadurch kann derselbe Code z. B. für folgende Ordner
| verwendet werden:
|
| /literatur/
| /lernmodule/
| /suttas/
| /arbeitsblaetter/
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Optionale Konfiguration
|--------------------------------------------------------------------------
| Leer lassen = Titel automatisch aus dem Verzeichnisnamen erzeugen.
*/
$pageTitle = '';
$pageSubtitle = 'Vorlagen und Unterlagen für das Italienischstudium';

/*
|--------------------------------------------------------------------------
| Technische Dateien, die nicht in der Liste erscheinen sollen
|--------------------------------------------------------------------------
*/
$excludedFiles = [
    'index.php',
    'style.css',
    'directory.js',
    '.htaccess',
    'thumbs.db',
    '.ds_store',
    'readme.md',
];

/*
|--------------------------------------------------------------------------
| Hilfsfunktionen
|--------------------------------------------------------------------------
*/
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function formatBytes(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    }

    $units = ['KB', 'MB', 'GB', 'TB'];
    $size = $bytes / 1024;

    foreach ($units as $unit) {
        if ($size < 1024 || $unit === 'TB') {
            return number_format($size, $size >= 10 ? 1 : 2, ',', '’') . ' ' . $unit;
        }

        $size /= 1024;
    }

    return $bytes . ' B';
}

function fileTypeLabel(string $filename): string
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    return match ($extension) {
        'pdf' => 'PDF',
        'doc', 'docx' => 'Word',
        'xls', 'xlsx' => 'Excel',
        'ppt', 'pptx' => 'PowerPoint',
        'txt' => 'Text',
        'md' => 'Markdown',
        'jpg', 'jpeg', 'png', 'gif', 'webp' => 'Bild',
        'mp3', 'wav', 'm4a' => 'Audio',
        'mp4', 'mov', 'webm' => 'Video',
        'zip', 'rar', '7z' => 'Archiv',
        'php' => 'PHP',
        'html', 'htm' => 'HTML',
        default => $extension !== '' ? strtoupper($extension) : 'Datei',
    };
}

function fileIcon(string $filename): string
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    return match ($extension) {
        'pdf' => '📕',
        'doc', 'docx' => '📘',
        'xls', 'xlsx' => '📗',
        'ppt', 'pptx' => '📙',
        'txt', 'md' => '📝',
        'jpg', 'jpeg', 'png', 'gif', 'webp' => '🖼️',
        'mp3', 'wav', 'm4a' => '🎧',
        'mp4', 'mov', 'webm' => '🎬',
        'zip', 'rar', '7z' => '🗜️',
        default => '📄',
    };
}

function humanizeDirectoryName(string $name): string
{
    $name = str_replace(['_', '-'], ' ', $name);
    $name = preg_replace('/\s+/', ' ', $name) ?? $name;

    return mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| Titel bestimmen
|--------------------------------------------------------------------------
*/
$currentDirectory = basename(__DIR__);

if (trim($pageTitle) === '') {
    $pageTitle = humanizeDirectoryName($currentDirectory);
}

/*
|--------------------------------------------------------------------------
| Dateien einlesen
|--------------------------------------------------------------------------
*/
$files = [];

$handle = opendir(__DIR__);

if ($handle !== false) {
    while (($filename = readdir($handle)) !== false) {
        if ($filename === '.' || $filename === '..') {
            continue;
        }

        if (in_array(strtolower($filename), $excludedFiles, true)) {
            continue;
        }

        $fullPath = __DIR__ . DIRECTORY_SEPARATOR . $filename;

        /*
         * Der Browser zeigt bewusst nur Dateien an.
         * Unterverzeichnisse können denselben Browser mit eigener index.php
         * enthalten.
         */
        if (!is_file($fullPath)) {
            continue;
        }

        $files[] = [
            'name' => $filename,
            'url' => '../protected-file.php?area=' . rawurlencode($currentDirectory) . '&file=' . rawurlencode($filename),
            'type' => fileTypeLabel($filename),
            'icon' => fileIcon($filename),
            'size' => formatBytes((int) filesize($fullPath)),
            'size_bytes' => (int) filesize($fullPath),
            'modified' => date('d.m.Y H:i', (int) filemtime($fullPath)),
            'modified_timestamp' => (int) filemtime($fullPath),
        ];
    }

    closedir($handle);
}

usort(
    $files,
    static fn(array $a, array $b): int =>
        strnatcasecmp((string) $a['name'], (string) $b['name'])
);

$fileCount = count($files);
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= e($pageTitle) ?> – Italienische Lernkarten</title>

    <link rel="stylesheet" href="style.css">
    <script src="directory.js" defer></script>
</head>

<body>
<main class="page">
    <div class="wrapper">

        <header class="hero">
            <div class="dharma-wheel" aria-hidden="true">🇮🇹</div>

            <p class="eyebrow">
                Italienische Lernkarten
            </p>

            <h1><?= e($pageTitle) ?></h1>

            <p class="intro">
                <?= e($pageSubtitle) ?>
            </p>
        </header>

        <?php if ($managerFlash !== null): ?>
            <div class="manager-message <?= e((string)$managerFlash['type']) ?>" role="status">
                <?= e((string)$managerFlash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="browser-card">

            <div class="browser-toolbar">
                <div class="toolbar-left">
                    <a class="home-button" href="../index.php" title="Zur Hauptseite">
                        ← Hauptseite
                    </a>

                    <span class="badge">
                        <?= $fileCount ?>
                        <?= $fileCount === 1 ? 'Dokument' : 'Dokumente' ?>
                    </span>
                </div>

                <label class="search-box">
                    <span class="visually-hidden">Dokumente durchsuchen</span>

                    <input
                        id="file-search"
                        type="search"
                        placeholder="Dokumente durchsuchen …"
                        autocomplete="off"
                    >
                </label>
            </div>

            <?php if ($fileCount === 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">📂</div>
                    <h2>Keine Dokumente vorhanden</h2>
                    <p>
                        Lege Dateien in dieses Verzeichnis.
                        Sie erscheinen danach automatisch in dieser Übersicht.
                    </p>
                </div>
            <?php else: ?>

                <div class="table-wrapper">
                    <table class="file-table" id="file-table">
                        <thead>
                            <tr>
                                <th
                                    class="number-column"
                                    data-sort="number"
                                    aria-label="Nach Nummer sortieren"
                                >
                                    Nr.
                                </th>

                                <th
                                    data-sort="name"
                                    aria-label="Nach Dateiname sortieren"
                                >
                                    Dokument
                                    <span class="sort-indicator" aria-hidden="true">↕</span>
                                </th>

                                <th
                                    data-sort="type"
                                    aria-label="Nach Dateityp sortieren"
                                >
                                    Typ
                                    <span class="sort-indicator" aria-hidden="true">↕</span>
                                </th>

                                <th
                                    data-sort="size"
                                    aria-label="Nach Dateigrösse sortieren"
                                >
                                    Grösse
                                    <span class="sort-indicator" aria-hidden="true">↕</span>
                                </th>

                                <th
                                    data-sort="modified"
                                    aria-label="Nach Änderungsdatum sortieren"
                                >
                                    Geändert
                                    <span class="sort-indicator" aria-hidden="true">↕</span>
                                </th>

                                <th class="action-column">
                                    Öffnen
                                </th>

                                <th class="manage-column">
                                    Bearbeiten
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($files as $index => $file): ?>
                                <tr
                                    data-search="<?= e(mb_strtolower(
                                        $file['name'] . ' ' . $file['type'],
                                        'UTF-8'
                                    )) ?>"
                                >
                                    <td
                                        class="number-column"
                                        data-value="<?= $index + 1 ?>"
                                    >
                                        <?= $index + 1 ?>
                                    </td>

                                    <td
                                        class="file-name-cell"
                                        data-value="<?= e(mb_strtolower($file['name'], 'UTF-8')) ?>"
                                    >
                                        <span class="file-icon" aria-hidden="true">
                                            <?= $file['icon'] ?>
                                        </span>

                                        <a
                                            class="file-name"
                                            href="<?= e($file['url']) ?>"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            <?= e($file['name']) ?>
                                        </a>
                                    </td>

                                    <td data-value="<?= e($file['type']) ?>">
                                        <span class="type-badge">
                                            <?= e($file['type']) ?>
                                        </span>
                                    </td>

                                    <td
                                        class="size-cell"
                                        data-value="<?= $file['size_bytes'] ?>"
                                    >
                                        <?= e($file['size']) ?>
                                    </td>

                                    <td
                                        class="date-cell"
                                        data-value="<?= $file['modified_timestamp'] ?>"
                                    >
                                        <?= e($file['modified']) ?>
                                    </td>

                                    <td class="action-column">
                                        <a
                                            class="open-button"
                                            href="<?= e($file['url']) ?>"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            Öffnen
                                        </a>
                                    </td>

                                    <td class="manage-column">
                                        <details class="file-actions">
                                            <summary>Bearbeiten</summary>
                                            <div class="file-actions-panel">
                                                <form method="post" class="rename-form">
                                                    <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                                                    <input type="hidden" name="file_action" value="rename">
                                                    <input type="hidden" name="filename" value="<?= e($file['name']) ?>">
                                                    <label>
                                                        <span>Neuer Dateiname</span>
                                                        <input type="text" name="new_name" value="<?= e($file['name']) ?>" required>
                                                    </label>
                                                    <button type="submit" class="rename-button">Umbenennen</button>
                                                </form>

                                                <form method="post" class="delete-form" onsubmit="return confirm('Datei wirklich endgültig löschen: <?= e(addslashes($file['name'])) ?>?');">
                                                    <input type="hidden" name="csrf_token" value="<?= e($csrf->token()) ?>">
                                                    <input type="hidden" name="file_action" value="delete">
                                                    <input type="hidden" name="filename" value="<?= e($file['name']) ?>">
                                                    <button type="submit" class="delete-button">Löschen</button>
                                                </form>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div
                    id="no-results"
                    class="empty-state compact"
                    hidden
                >
                    <div class="empty-icon">🔎</div>
                    <h2>Keine Treffer</h2>
                    <p>
                        Für diesen Suchbegriff wurden keine Dokumente gefunden.
                    </p>
                </div>

            <?php endif; ?>
        </section>

        <footer>
            Parola dopo parola. Frase dopo frase.
            <br><br>
            Design, Programming by Beat Faeh ·
            <a
                href="https://www.faeh.sh"
                target="_blank"
                rel="noopener"
            >
                www.faeh.sh
            </a>
<br><br>			

        </footer>

    </div>
</main>
</body>
</html>
