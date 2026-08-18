<?php
declare(strict_types=1);

if (!$auth->isAdmin()) {
    http_response_code(403);
    exit('Nicht autorisiert.');
}

$redirect = static function (string $area = 'vorlagen') use ($flash) {
    $anchor = $area === 'grammatik' ? '#grammatik-dateien' : '#dokument-upload';
    $notice = $area === 'grammatik' ? 'grammar-upload' : 'upload';
    header('Location: index.php?action=admin&notice=' . $notice . $anchor);
    exit;
};

// Gleiche CSRF-Prüfung wie in den bestehenden Aktionen der Anwendung.
// Csrf stellt verify() bereit; validate() existiert nicht und verursachte HTTP 500.
$csrf->verify();

if (strtolower((string)ini_get('file_uploads')) === '0' || ini_get('file_uploads') === '') {
    $flash->set('error', 'Datei-Uploads sind in PHP deaktiviert (file_uploads=Off).');
    $redirect();
}

$areas = [
    'vorlagen' => __DIR__ . '/../vorlagen',
    'grammatik' => __DIR__ . '/../grammatik',
];
$labels = [
    'vorlagen' => 'Vorlagen',
    'grammatik' => 'Grammatik',
];

$area = (string)($_POST['document_area'] ?? '');
$file = $_FILES['document_file'] ?? null;

if (!isset($areas[$area])) {
    $flash->set('error', 'Ungültiger Zielbereich.');
    $redirect($area);
}

if (!is_array($file)) {
    $flash->set(
        'error',
        'Der Browser hat keine Datei an PHP übergeben. Bitte PHP-Einstellungen upload_max_filesize und post_max_size prüfen.'
    );
    $redirect($area);
}

$uploadError = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
if ($uploadError !== UPLOAD_ERR_OK) {
    $messages = [
        UPLOAD_ERR_INI_SIZE => 'Die Datei überschreitet upload_max_filesize des Servers (' . ini_get('upload_max_filesize') . ').',
        UPLOAD_ERR_FORM_SIZE => 'Die Datei überschreitet die im Formular erlaubte Grösse.',
        UPLOAD_ERR_PARTIAL => 'Die Datei wurde nur teilweise übertragen. Bitte erneut versuchen.',
        UPLOAD_ERR_NO_FILE => 'Es wurde keine Datei ausgewählt.',
        UPLOAD_ERR_NO_TMP_DIR => 'Auf dem Server fehlt das temporäre Upload-Verzeichnis.',
        UPLOAD_ERR_CANT_WRITE => 'PHP konnte die Datei nicht in das temporäre Verzeichnis schreiben.',
        UPLOAD_ERR_EXTENSION => 'Eine PHP-Erweiterung hat den Upload gestoppt.',
    ];
    $flash->set('error', $messages[$uploadError] ?? ('Der Upload ist mit PHP-Fehlercode ' . $uploadError . ' fehlgeschlagen.'));
    error_log('Italo Uploadfehler: PHP error=' . $uploadError . ', file=' . (string)($file['name'] ?? ''));
    $redirect($area);
}

$maxBytes = 25 * 1024 * 1024;
$fileSize = (int)($file['size'] ?? 0);
if ($fileSize <= 0 || $fileSize > $maxBytes) {
    $flash->set('error', 'Die Datei ist leer oder grösser als 25 MB.');
    $redirect($area);
}

$original = basename((string)($file['name'] ?? ''));
$extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
$allowed = $area === 'grammatik' ? ['pdf'] : ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','md','jpg','jpeg','png','webp','mp3','wav','m4a','mp4','mov','webm','zip'];
if (!in_array($extension, $allowed, true)) {
    $flash->set('error', 'Dieser Dateityp (.' . Html::e($extension) . ') ist nicht erlaubt.');
    $redirect($area);
}

$base = pathinfo($original, PATHINFO_FILENAME);
$base = preg_replace('/[^\pL\pN._() -]+/u', '_', $base) ?: 'Dokument';
$base = trim($base, " .\t\n\r\0\x0B");
if ($base === '') {
    $base = 'Dokument';
}
$filename = $base . '.' . $extension;

$targetDir = realpath($areas[$area]);
if ($targetDir === false || !is_dir($targetDir)) {
    $flash->set('error', 'Das Zielverzeichnis „' . $labels[$area] . '“ wurde nicht gefunden.');
    $redirect($area);
}

// Auf manchen Shared-Hosting-Systemen werden entpackte Ordner ohne Gruppen-Schreibrecht angelegt.
// Falls möglich, korrigieren wir dies einmal automatisch.
if (!is_writable($targetDir)) {
    @chmod($targetDir, 0775);
    clearstatcache(true, $targetDir);
}

if (!is_writable($targetDir)) {
    $flash->set(
        'error',
        'Das Verzeichnis „' . $labels[$area] . '“ ist für PHP nicht beschreibbar. '
        . 'Bitte dem Ordner Schreibrechte geben (typischerweise 775; je nach Hoster ggf. 755 mit passendem Besitzer).'
    );
    error_log('Italo Uploadfehler: Ziel nicht beschreibbar: ' . $targetDir);
    $redirect($area);
}

$counter = 2;
$target = $targetDir . DIRECTORY_SEPARATOR . $filename;
while (file_exists($target)) {
    $filename = $base . '_' . $counter . '.' . $extension;
    $target = $targetDir . DIRECTORY_SEPARATOR . $filename;
    $counter++;
}

$tmpName = (string)($file['tmp_name'] ?? '');
if ($tmpName === '' || !is_uploaded_file($tmpName)) {
    $flash->set('error', 'Die temporäre Upload-Datei ist ungültig oder nicht mehr vorhanden.');
    error_log('Italo Uploadfehler: tmp-Datei ungueltig: ' . $tmpName);
    $redirect($area);
}

$stored = move_uploaded_file($tmpName, $target);

// Fallback für einzelne Hosting-Konfigurationen, bei denen move_uploaded_file scheitert,
// obwohl die geprüfte temporäre Datei lesbar und das Ziel beschreibbar ist.
if (!$stored && is_readable($tmpName)) {
    $stored = @copy($tmpName, $target);
    if ($stored) {
        @unlink($tmpName);
    }
}

if (!$stored || !is_file($target)) {
    $lastError = error_get_last();
    $detail = is_array($lastError) ? (string)($lastError['message'] ?? '') : '';
    error_log('Italo Uploadfehler: Speichern fehlgeschlagen. target=' . $target . '; detail=' . $detail);
    $flash->set(
        'error',
        'Die Datei wurde übertragen, konnte aber nicht im Ordner „' . $labels[$area] . '“ gespeichert werden. '
        . 'Bitte die Ordnerrechte prüfen.'
    );
    $redirect($area);
}

@chmod($target, 0644);
clearstatcache(true, $target);

$flash->set(
    'success',
    '„' . $filename . '“ wurde erfolgreich in ' . $labels[$area]
    . ' hochgeladen (' . number_format((int)filesize($target) / 1024, 1, ',', '’') . ' KB).'
);
$redirect($area);
