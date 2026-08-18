<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap/init.php';

$isAdmin = $auth->isAdmin();
$manage = $isAdmin && (($_GET['verwaltung'] ?? '') === '1' || isset($_POST['file_action']));
if ($manage) {
    $currentDirectoryPath = __DIR__;
    require __DIR__ . '/../directory-actions.php';
}
$managerFlash = $flash->take();
$search = trim((string)($_GET['suche'] ?? ''));
$entries = $grammarRepository->all($search);

function ge(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function grammarPdfUrl(string $pdf): string {
    $pdf = trim($pdf);
    if ($pdf === '') return '';
    if (preg_match('~^https?://~i', $pdf)) return $pdf;
    $name = basename(str_replace('\\', '/', $pdf));
    return rawurlencode($name);
}

$pdfFiles = [];
if ($manage) {
    foreach (scandir(__DIR__) ?: [] as $filename) {
        if ($filename === '.' || $filename === '..' || strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'pdf') continue;
        $full = __DIR__ . DIRECTORY_SEPARATOR . $filename;
        if (!is_file($full)) continue;
        $pdfFiles[] = ['name' => $filename, 'size' => filesize($full) ?: 0, 'modified' => filemtime($full) ?: 0];
    }
    usort($pdfFiles, static fn(array $a,array $b):int => strnatcasecmp($a['name'],$b['name']));
}
?>
<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Grammatik – Italienische Lernkarten</title><link rel="stylesheet" href="style.css">
<style>
.grammar-list{display:grid;gap:14px;padding:22px 26px}.grammar-entry{padding:18px;border:1px solid var(--line);border-radius:14px;background:#fff}.grammar-entry h2{margin:0 0 8px;color:var(--bodhi-dark);font-size:21px}.grammar-text{white-space:pre-wrap}.grammar-meta{display:flex;gap:10px;flex-wrap:wrap;margin-top:12px}.search-form{display:grid;grid-template-columns:1fr auto;gap:10px;width:min(700px,100%)}.search-form input{min-height:48px;padding:11px 15px;border:2px solid var(--line);border-radius:13px;font:inherit}.search-form button,.admin-link{border:0;border-radius:10px;padding:10px 15px;background:var(--saffron);color:#fff;font-weight:800;text-decoration:none;cursor:pointer}.admin-tools,.file-manager{margin:20px 26px;padding:18px;border:1px dashed var(--line);border-radius:14px;background:#fffaf0}.file-row{display:grid;grid-template-columns:minmax(180px,1fr) auto auto;gap:12px;align-items:center;padding:12px 0;border-bottom:1px solid var(--line)}.file-row:last-child{border-bottom:0}.file-actions-inline{display:flex;gap:8px;align-items:end;flex-wrap:wrap}.file-actions-inline input{padding:8px;border:1px solid var(--line);border-radius:8px}.delete-button{background:#b7363e!important}@media(max-width:750px){.search-form,.file-row{grid-template-columns:1fr}.file-actions-inline{align-items:stretch}}
</style></head><body><main class="page"><div class="wrapper">
<header class="hero"><div class="dharma-wheel" aria-hidden="true">🇮🇹</div><p class="eyebrow">Italienische Lernkarten</p><h1>Grammatik</h1><p class="intro">Grammatik nach Stichworten durchsuchen und zugehörige PDF-Unterlagen öffnen.</p></header>
<?php if($managerFlash!==null):?><div class="manager-message <?=ge((string)$managerFlash['type'])?>" role="status"><?=ge((string)$managerFlash['message'])?></div><?php endif;?>
<section class="browser-card"><div class="browser-toolbar"><div class="toolbar-left"><a class="home-button" href="../index.php">← Hauptseite</a><span class="badge"><?=count($entries)?> Treffer</span></div><form class="search-form" method="get"><input type="search" name="suche" value="<?=ge($search)?>" placeholder="Stichwort suchen, z. B. Passato prossimo"><button type="submit">Suchen</button></form></div>
<?php if($search!=='' && count($entries)===0):?><div class="empty-state"><div class="empty-icon">🔎</div><h2>Keine Treffer</h2><p>Für „<?=ge($search)?>“ wurde kein Grammatikeintrag gefunden.</p></div><?php else:?><div class="grammar-list">
<?php foreach($entries as $entry): $pdfUrl=grammarPdfUrl((string)$entry['pdf']); ?><article class="grammar-entry"><h2><?=ge((string)$entry['stichwort'])?></h2><?php if(trim((string)$entry['erklaerung'])!==''):?><div class="grammar-text"><?=nl2br(ge((string)$entry['erklaerung']))?></div><?php endif;?><div class="grammar-meta"><span class="badge">DB-ID #<?=(int)$entry['id']?></span><?php if($pdfUrl!==''):?><a class="open-button" href="<?=ge($pdfUrl)?>" target="_blank" rel="noopener">📄 PDF öffnen</a><?php endif;?></div></article><?php endforeach;?></div><?php endif;?>

<?php if($isAdmin):?><div class="admin-tools"><strong>Administration aktiv.</strong> <a class="admin-link" href="../index.php?action=admin#grammatik">Grammatikeinträge bearbeiten</a> <?php if(!$manage):?><a class="admin-link" href="?verwaltung=1">PDF-Dateien verwalten</a><?php else:?><a class="admin-link" href="./">Dateiverwaltung schliessen</a><?php endif;?></div><?php endif;?>

<?php if($manage):?><section class="file-manager"><h2>PDF-Dateien verwalten</h2><p>Beim Umbenennen wird ein passender Eintrag im Datenbankfeld <strong>pdf</strong> automatisch angepasst. Beim Löschen wird der entsprechende PDF-Verweis geleert.</p><?php if(!$pdfFiles):?><p>Keine PDF-Dateien vorhanden.</p><?php endif;?><?php foreach($pdfFiles as $file):?><div class="file-row"><div><a href="<?=ge(rawurlencode($file['name']))?>" target="_blank" rel="noopener"><strong><?=ge($file['name'])?></strong></a><br><small><?=number_format($file['size']/1024,1,',','’')?> KB · <?=date('d.m.Y H:i',$file['modified'])?></small></div><form method="post" class="file-actions-inline"><input type="hidden" name="csrf_token" value="<?=ge($csrf->token())?>"><input type="hidden" name="file_action" value="rename"><input type="hidden" name="filename" value="<?=ge($file['name'])?>"><label>Neuer Name<input type="text" name="new_name" value="<?=ge($file['name'])?>" required></label><button type="submit" class="rename-button">Umbenennen</button></form><form method="post" onsubmit="return confirm('PDF wirklich löschen?')"><input type="hidden" name="csrf_token" value="<?=ge($csrf->token())?>"><input type="hidden" name="file_action" value="delete"><input type="hidden" name="filename" value="<?=ge($file['name'])?>"><button type="submit" class="delete-button">Löschen</button></form></div><?php endforeach;?></section><?php endif;?>
</section></div></main></body></html>
