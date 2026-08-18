<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap/init.php';

$allowedAreas = ['vorlagen'];
$area = (string)($_GET['area'] ?? '');
$file = (string)($_GET['file'] ?? '');

if (!in_array($area, $allowedAreas, true)) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

if (!$auth->isAdmin()) {
    header('Location: index.php?action=admin&return_to=' . rawurlencode($area . '/'));
    exit;
}

// Nur echte Dateinamen zulassen, keine Pfade oder Traversal-Sequenzen.
if ($file === '' || $file !== basename($file) || str_contains($file, "\0")) {
    http_response_code(400);
    exit('Ungültiger Dateiname.');
}

$baseDir = realpath(__DIR__ . DIRECTORY_SEPARATOR . $area);
$fullPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . $area . DIRECTORY_SEPARATOR . $file);

if ($baseDir === false || $fullPath === false || !is_file($fullPath)) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

$basePrefix = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
if (!str_starts_with($fullPath, $basePrefix)) {
    http_response_code(403);
    exit('Zugriff verweigert.');
}

$mime = 'application/octet-stream';
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo !== false) {
        $detected = finfo_file($finfo, $fullPath);
        if (is_string($detected) && $detected !== '') {
            $mime = $detected;
        }
        finfo_close($finfo);
    }
}

$downloadName = basename($fullPath);
$encodedName = rawurlencode($downloadName);

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)filesize($fullPath));
header("Content-Disposition: inline; filename*=UTF-8''" . $encodedName);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store, max-age=0');
header('Pragma: no-cache');

readfile($fullPath);
exit;
