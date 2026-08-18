<?php
declare(strict_types=1);

/**
 * Geschützte Dateiaktionen für das Verzeichnis vorlagen.
 * Requires bootstrap/init.php to be loaded and $auth/$csrf to be available.
 */

$editableAreas = ['vorlagen', 'grammatik'];
$currentArea = basename((string)($currentDirectoryPath ?? ''));

if (!in_array($currentArea, $editableAreas, true)) {
    http_response_code(403);
    exit('Ungültiger Bereich.');
}

$protectedNames = [
    'index.php',
    'style.css',
    'directory.js',
    '.htaccess',
    'thumbs.db',
    '.ds_store',
    'readme.md',
];

function managedFilePath(string $directory, string $filename, array $protectedNames): ?string
{
    if ($filename === '' || $filename !== basename($filename) || str_contains($filename, "\0")) {
        return null;
    }

    if (in_array(strtolower($filename), $protectedNames, true) || str_starts_with($filename, '.')) {
        return null;
    }

    $base = realpath($directory);
    $path = realpath($directory . DIRECTORY_SEPARATOR . $filename);

    if ($base === false || $path === false || !is_file($path)) {
        return null;
    }

    $prefix = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    return str_starts_with($path, $prefix) ? $path : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_action'])) {
    if (!$auth->isAdmin()) {
        http_response_code(403);
        exit('Zugriff verweigert.');
    }

    $csrf->verify();

    $action = (string)$_POST['file_action'];
    $oldName = trim((string)($_POST['filename'] ?? ''));
    $oldPath = managedFilePath($currentDirectoryPath, $oldName, $protectedNames);

    if ($oldPath === null) {
        $flash->set('error', 'Die gewünschte Datei wurde nicht gefunden oder darf nicht bearbeitet werden.');
        header('Location: ' . ($currentArea === 'grammatik' ? 'index.php?verwaltung=1' : 'index.php'));
        exit;
    }

    if ($action === 'delete') {
        if (@unlink($oldPath)) {
            if ($currentArea === 'grammatik' && isset($grammarRepository)) { $grammarRepository->clearPdfReference($oldName); }
            $flash->set('success', 'Die Datei „' . $oldName . '“ wurde gelöscht.');
        } else {
            $flash->set('error', 'Die Datei konnte nicht gelöscht werden. Bitte Dateirechte auf dem Server prüfen.');
        }
        header('Location: ' . ($currentArea === 'grammatik' ? 'index.php?verwaltung=1' : 'index.php'));
        exit;
    }

    if ($action === 'rename') {
        $newName = trim((string)($_POST['new_name'] ?? ''));

        if ($newName === '' || $newName !== basename($newName) || str_contains($newName, "\0") || str_starts_with($newName, '.')) {
            $flash->set('error', 'Der neue Dateiname ist ungültig. Verwende nur einen Dateinamen ohne Ordnerpfad.');
            header('Location: ' . ($currentArea === 'grammatik' ? 'index.php?verwaltung=1' : 'index.php'));
            exit;
        }

        if (in_array(strtolower($newName), $protectedNames, true)) {
            $flash->set('error', 'Dieser Dateiname ist für eine Systemdatei reserviert.');
            header('Location: ' . ($currentArea === 'grammatik' ? 'index.php?verwaltung=1' : 'index.php'));
            exit;
        }

        // Auf Windows sind diese Zeichen in Dateinamen unzulässig; auch auf Linux vermeiden wir sie für portable URLs.
        if (preg_match('/[\\\\\/:*?"<>|]/u', $newName) === 1) {
            $flash->set('error', 'Der neue Dateiname enthält unzulässige Zeichen.');
            header('Location: ' . ($currentArea === 'grammatik' ? 'index.php?verwaltung=1' : 'index.php'));
            exit;
        }

        $newPath = $currentDirectoryPath . DIRECTORY_SEPARATOR . $newName;
        if (file_exists($newPath)) {
            $flash->set('error', 'Eine Datei mit dem Namen „' . $newName . '“ existiert bereits.');
            header('Location: ' . ($currentArea === 'grammatik' ? 'index.php?verwaltung=1' : 'index.php'));
            exit;
        }

        if (@rename($oldPath, $newPath)) {
            if ($currentArea === 'grammatik' && isset($grammarRepository)) { $grammarRepository->renamePdfReference($oldName, $newName); }
            $flash->set('success', 'Die Datei wurde in „' . $newName . '“ umbenannt.');
        } else {
            $flash->set('error', 'Die Datei konnte nicht umbenannt werden. Bitte Dateirechte auf dem Server prüfen.');
        }
        header('Location: ' . ($currentArea === 'grammatik' ? 'index.php?verwaltung=1' : 'index.php'));
        exit;
    }

    $flash->set('error', 'Unbekannte Dateiaktion.');
    header('Location: ' . ($currentArea === 'grammatik' ? 'index.php?verwaltung=1' : 'index.php'));
    exit;
}
