<?php
declare(strict_types=1);
if(!$auth->isAdmin()){require __DIR__.'/admin-login.php';return;}
$flashMessage=$flash->take();
$adminSearch=trim((string)($_GET['suche']??''));
$adminField=(string)($_GET['feld']??'all');
$adminLimitRaw=(string)($_GET['anzahl']??'50');
$allowedAdminLimits=['25','50','100','500','1000','alle'];
if(!in_array($adminLimitRaw,$allowedAdminLimits,true))$adminLimitRaw='50';
$adminLimit=$adminLimitRaw==='alle'?null:(int)$adminLimitRaw;
$adminTotal=$cardRepository->adminCount($adminSearch,$adminField);
$cards=$cardRepository->adminSearch($adminSearch,$adminField,$adminLimit);
$verbSearch=trim((string)($_GET['verb_suche']??''));
$verbs=$verbRepository->all($verbSearch);
$grammarSearch=trim((string)($_GET['grammar_suche']??''));
$grammarEntries=$grammarRepository->all($grammarSearch);

// Vorhandene Grammatik-PDFs direkt aus demselben Zielverzeichnis einlesen,
// in das auch der bestehende Upload-Mechanismus speichert.
$grammarPdfFiles=[];
$grammarPdfDirectory=__DIR__.'/../grammatik';
if(is_dir($grammarPdfDirectory)){
    foreach(scandir($grammarPdfDirectory)?:[] as $grammarPdfFilename){
        if($grammarPdfFilename==='.'||$grammarPdfFilename==='..')continue;
        if(strtolower(pathinfo($grammarPdfFilename,PATHINFO_EXTENSION))!=='pdf')continue;
        if(!is_file($grammarPdfDirectory.DIRECTORY_SEPARATOR.$grammarPdfFilename))continue;
        $grammarPdfFiles[]=$grammarPdfFilename;
    }
    natcasesort($grammarPdfFiles);
    $grammarPdfFiles=array_values($grammarPdfFiles);
}
$grammarPdfBasename=static function(string $pdf):string{
    $pdf=trim($pdf);
    if($pdf==='')return '';
    if(preg_match('~^https?://~i',$pdf))return $pdf;
    $pdf=str_replace('\\','/',$pdf);
    return basename($pdf);
};
?>
<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Administration – Italienische Lernkarten</title><link rel="stylesheet" href="assets/css/admin.css"></head><body><main class="admin-shell"><header class="topbar"><h1>Italienische Lernkarten · Administration</h1><nav class="nav-actions"><a class="button neutral" href="#neu">Neue Lernkarte</a><a class="button neutral" href="#lernkarten">Bearbeiten</a><a class="button neutral" href="#verben-neu">Neues Verb</a><a class="button neutral" href="#verben">Verben bearbeiten</a><a class="button neutral" href="index.php?action=verben">Verben anzeigen</a><a class="button neutral" href="#dokument-upload">Vorlage hochladen</a><a class="button neutral" href="vorlagen/">Vorlagen verwalten</a><a class="button neutral" href="#grammatik">Grammatik bearbeiten</a><a class="button neutral" href="grammatik/">Grammatik anzeigen</a><a class="button neutral" href="#passwort">Passwort</a><a class="button secondary" href="index.php">Öffentliche Ansicht</a><a class="button danger" href="index.php?action=logout">Abmelden</a></nav></header><?php $notice=(string)($_GET['notice']??'');$isUploadNotice=$notice==='upload';$isVerbNotice=$notice==='verb';$isGrammarNotice=$notice==='grammar';$isGrammarUploadNotice=$notice==='grammar-upload'; ?><?php if($flashMessage&&!$isUploadNotice&&!$isVerbNotice&&!$isGrammarNotice&&!$isGrammarUploadNotice):?><div class="message <?=Html::e($flashMessage['type'])?>" role="status" aria-live="polite"><?=Html::e($flashMessage['message'])?></div><?php endif;?>
<section class="panel" id="suche">
<h2>Lernkarten suchen</h2>
<form method="get" action="index.php">
    <input type="hidden" name="action" value="admin">
    <div class="form-grid">
        <label>Suche
            <input type="search" name="suche" value="<?=Html::e($adminSearch)?>" placeholder="ID, deutsches oder italienisches Wort">
        </label>
        <label>Suchfeld
            <select name="feld">
                <option value="all" <?=$adminField==='all'?'selected':''?>>ID, Deutsch oder Italienisch</option>
                <option value="id" <?=$adminField==='id'?'selected':''?>>Nur Datenbank-ID</option>
                <option value="de" <?=$adminField==='de'?'selected':''?>>Nur deutsches Wort</option>
                <option value="it" <?=$adminField==='it'?'selected':''?>>Nur italienisches Wort</option>
            </select>
        </label>
    </div>
    <button type="submit">Suchen</button>
    <?php if($adminSearch!==''):?>
        <a class="button neutral" href="index.php?action=admin&amp;anzahl=<?=Html::e($adminLimitRaw)?>#lernkarten">Suche zurücksetzen</a>
    <?php endif;?>
</form>
<?php if($adminSearch!==''):?>
    <p class="muted"><?=$adminTotal?> Treffer für „<?=Html::e($adminSearch)?>“; <?=count($cards)?> werden angezeigt.</p>
<?php endif;?>
</section>

<section class="panel list-panel" id="dokument-upload">
<h2>Vorlage hochladen</h2>
<?php if($flashMessage&&$isUploadNotice):?>
    <div class="message <?=Html::e($flashMessage['type'])?>" role="status" aria-live="polite">
        <?=Html::e($flashMessage['type']==='success'?'✓ ':'✗ ')?><?=Html::e($flashMessage['message'])?>
    </div>
<?php endif;?>
<p class="muted">Neue Dateien werden direkt im geschützten Verzeichnis <strong>vorlagen</strong> gespeichert. Umbenennen und Löschen erfolgt über „Vorlagen verwalten“.</p>
<form method="post" action="index.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?=Html::e($csrf->token())?>">
    <input type="hidden" name="form_action" value="upload_document">
    <input type="hidden" name="document_area" value="vorlagen">
    <input type="hidden" name="MAX_FILE_SIZE" value="26214400">
    <label>Datei auswählen
        <input type="file" name="document_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.md,.jpg,.jpeg,.png,.webp,.mp3,.wav,.m4a,.mp4,.mov,.webm,.zip" required>
    </label>
    <p class="muted">Erlaubt: PDF, Office, Text, Bilder, Audio, Video und ZIP · maximal 25 MB.</p>
    <button type="submit">Datei hochladen</button>
    <a class="button neutral" href="vorlagen/">Vorlagen verwalten</a>
</form>
</section>


<section class="panel" id="verben-neu">
<h2>Neues Verb</h2>
<p class="muted">Verbformen entsprechend der Tabelle <strong>italienisch_verben</strong> erfassen.</p>
<form method="post" action="index.php">
    <input type="hidden" name="csrf_token" value="<?=Html::e($csrf->token())?>">
    <input type="hidden" name="form_action" value="add_verb">
    <div class="form-grid">
        <label>Verb Italienisch<input type="text" name="verb_it" maxlength="250" required></label><label>Verb Deutsch<input type="text" name="verb_de" maxlength="250" required></label>
        <label>Endung<select name="endung" required><option value="ire">ire</option><option value="are">are</option><option value="ere">ere</option><option value="unregelmässig">unregelmässig</option></select></label>
        <label>Präsens<textarea name="praesens" maxlength="500"></textarea></label>
        <label>Perfekt<textarea name="perfekt" maxlength="500"></textarea></label>
        <label>Futur<textarea name="futur" maxlength="500"></textarea></label>
        <label>Imperativ<textarea name="imperativ" maxlength="500"></textarea></label>
    </div>
    <button type="submit">Verb speichern</button>
</form>
</section>

<section class="panel list-panel" id="verben">
<h2>Verben bearbeiten</h2>
<?php if($flashMessage&&$isVerbNotice):?>
<div class="message <?=Html::e($flashMessage['type'])?>" role="status" aria-live="polite"><?=Html::e($flashMessage['type']==='success'?'✓ ':'✗ ')?><?=Html::e($flashMessage['message'])?></div>
<?php endif;?>
<form method="get" action="index.php" class="verb-search-form">
    <input type="hidden" name="action" value="admin">
    <label>Volltextsuche: Italienisch, Deutsch, Form oder ID
        <input type="search" name="verb_suche" value="<?=Html::e($verbSearch)?>" placeholder="z. B. essere, sein, sono, ruf oder 12">
    </label>
    <button type="submit">Suchen</button>
    <?php if($verbSearch!==''):?><a class="button neutral" href="index.php?action=admin#verben">Suche zurücksetzen</a><?php endif;?>
</form>
<div class="admin-section-actions"><a class="button neutral" href="index.php?action=verben">Geschützte Verbenliste</a><a class="button neutral" href="index.php?action=verben-pdf&amp;sort=it">PDF Italienisch A–Z</a><a class="button neutral" href="index.php?action=verben-pdf&amp;sort=de">PDF Deutsch A–Z</a></div>
<p class="muted"><?=count($verbs)?> <?= $verbSearch!=='' ? 'Treffer' : 'Verben vorhanden' ?>.</p>
<?php foreach($verbs as $verb):?>
<details class="entry">
    <summary>DB-ID #<?=(int)$verb['id']?> · <?=Html::e((string)$verb['verb_it'])?> ↔ <?=Html::e((string)$verb['verb_de'])?><?=trim((string)$verb['endung'])!==''?' · '.Html::e((string)$verb['endung']):''?></summary>
    <form method="post" action="index.php">
        <input type="hidden" name="csrf_token" value="<?=Html::e($csrf->token())?>">
        <input type="hidden" name="form_action" value="update_verb">
        <input type="hidden" name="id" value="<?=(int)$verb['id']?>">
        <div class="form-grid">
            <label>Datenbank-ID<input type="text" value="<?=(int)$verb['id']?>" readonly></label>
            <label>Verb Italienisch<input type="text" name="verb_it" maxlength="250" value="<?=Html::e((string)$verb['verb_it'])?>" required></label><label>Verb Deutsch<input type="text" name="verb_de" maxlength="250" value="<?=Html::e((string)$verb['verb_de'])?>" required></label>
            <label>Endung<select name="endung" required><?php foreach(['ire','are','ere','unregelmässig'] as $ending):?><option value="<?=Html::e($ending)?>" <?=((string)$verb['endung']===$ending)?'selected':''?>><?=Html::e($ending)?></option><?php endforeach;?></select></label>
            <label>Präsens<textarea name="praesens" maxlength="500"><?=Html::e((string)$verb['praesens'])?></textarea></label>
            <label>Perfekt<textarea name="perfekt" maxlength="500"><?=Html::e((string)$verb['perfekt'])?></textarea></label>
            <label>Futur<textarea name="futur" maxlength="500"><?=Html::e((string)$verb['futur'])?></textarea></label>
            <label>Imperativ<textarea name="imperativ" maxlength="500"><?=Html::e((string)$verb['imperativ'])?></textarea></label>
        </div>
        <button type="submit">Änderungen speichern</button>
    </form>
    <form method="post" action="index.php" class="delete-form" onsubmit="return confirm('Verb wirklich löschen?')">
        <input type="hidden" name="csrf_token" value="<?=Html::e($csrf->token())?>">
        <input type="hidden" name="form_action" value="delete_verb">
        <input type="hidden" name="id" value="<?=(int)$verb['id']?>">
        <button class="danger" type="submit">Verb löschen</button>
    </form>
</details>
<?php endforeach;?>
</section>

<section class="panel" id="neu"><h2>Neue Lernkarte</h2><p class="muted">Das Wortpaar ist obligatorisch. Beispielsätze sind optional, werden aber für den Satz-Lernmodus benötigt.</p><form method="post" action="index.php"><input type="hidden" name="csrf_token" value="<?=Html::e($csrf->token())?>"><input type="hidden" name="form_action" value="add_card"><div class="form-grid"><label>Deutsch<input type="text" name="wort_de" required></label><label>Italiano<input type="text" name="wort_it" required></label><label>Deutscher Satz<textarea name="satz_de"></textarea></label><label>Frase italiana<textarea name="satz_it"></textarea></label><label>Lektion<input type="number" name="lektion" min="0"></label></div><button type="submit">Lernkarte speichern</button></form></section>

<section class="panel" id="grammatik-neu">
<h2>Neuer Grammatikeintrag</h2>
<p class="muted">Stichwort, Erklärung und optional eine bereits hochgeladene PDF-Datei aus dem Verzeichnis <strong>grammatik</strong> auswählen.</p>
<form method="post" action="index.php">
<input type="hidden" name="csrf_token" value="<?=Html::e($csrf->token())?>"><input type="hidden" name="form_action" value="add_grammar">
<div class="form-grid"><label>Stichwort<input type="text" name="stichwort" maxlength="250" required></label><label>PDF-Link<select name="pdf"><option value="">— kein PDF —</option><?php foreach($grammarPdfFiles as $grammarPdfFile):?><option value="<?=Html::e($grammarPdfFile)?>"><?=Html::e($grammarPdfFile)?></option><?php endforeach;?></select><?php if(!$grammarPdfFiles):?><small class="muted">Noch keine PDF-Datei im Verzeichnis grammatik vorhanden.</small><?php endif;?></label><label style="grid-column:1/-1">Erklärung<textarea name="erklaerung"></textarea></label></div>
<button type="submit">Grammatikeintrag speichern</button>
</form></section>

<section class="panel list-panel" id="grammatik-dateien">
<h2>Grammatik-PDF hochladen</h2>
<?php if($flashMessage&&$isGrammarUploadNotice):?><div class="message <?=Html::e($flashMessage['type'])?>" role="status"><?=Html::e($flashMessage['type']==='success'?'✓ ':'✗ ')?><?=Html::e($flashMessage['message'])?></div><?php endif;?>
<p class="muted">PDF-Dateien werden in <strong>grammatik</strong> gespeichert. Umbenennen und Löschen ist über „Grammatik-Dateien verwalten“ möglich; vorhandene DB-Verweise im Feld <strong>pdf</strong> werden beim Umbenennen/Löschen automatisch angepasst.</p>
<form method="post" action="index.php" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?=Html::e($csrf->token())?>"><input type="hidden" name="form_action" value="upload_document"><input type="hidden" name="document_area" value="grammatik"><input type="hidden" name="MAX_FILE_SIZE" value="26214400"><label>PDF auswählen<input type="file" name="document_file" accept=".pdf,application/pdf" required></label><p class="muted">PDF · maximal 25 MB.</p><button type="submit">PDF hochladen</button> <a class="button neutral" href="grammatik/?verwaltung=1">Grammatik-Dateien verwalten</a></form>
</section>

<section class="panel list-panel" id="grammatik">
<h2>Grammatik bearbeiten</h2>
<?php if($flashMessage&&$isGrammarNotice):?><div class="message <?=Html::e($flashMessage['type'])?>" role="status"><?=Html::e($flashMessage['type']==='success'?'✓ ':'✗ ')?><?=Html::e($flashMessage['message'])?></div><?php endif;?>
<form method="get" action="index.php" class="verb-search-form"><input type="hidden" name="action" value="admin"><label>Stichwort, Inhalt oder ID suchen<input type="search" name="grammar_suche" value="<?=Html::e($grammarSearch)?>" placeholder="z. B. Passato prossimo oder 4"></label><button type="submit">Suchen</button><?php if($grammarSearch!==''):?><a class="button neutral" href="index.php?action=admin#grammatik">Suche zurücksetzen</a><?php endif;?></form>
<div class="admin-section-actions"><a class="button neutral" href="#grammatik-neu">Neuer Eintrag</a><a class="button neutral" href="#grammatik-dateien">PDF hochladen</a><a class="button neutral" href="grammatik/">Öffentliche Grammatik</a><a class="button neutral" href="grammatik/?verwaltung=1">Dateien verwalten</a></div><p class="muted"><?=count($grammarEntries)?> <?= $grammarSearch!=='' ? 'Treffer' : 'Grammatikeinträge vorhanden' ?>.</p>
<?php foreach($grammarEntries as $grammar): $currentGrammarPdf=$grammarPdfBasename((string)$grammar['pdf']); $currentGrammarPdfExists=$currentGrammarPdf===''||in_array($currentGrammarPdf,$grammarPdfFiles,true);?><details class="entry"><summary>DB-ID #<?=(int)$grammar['id']?> · <?=Html::e((string)$grammar['stichwort'])?></summary><form method="post" action="index.php"><input type="hidden" name="csrf_token" value="<?=Html::e($csrf->token())?>"><input type="hidden" name="form_action" value="update_grammar"><input type="hidden" name="id" value="<?=(int)$grammar['id']?>"><div class="form-grid"><label>Datenbank-ID<input type="text" value="<?=(int)$grammar['id']?>" readonly></label><label>Stichwort<input type="text" name="stichwort" maxlength="250" value="<?=Html::e((string)$grammar['stichwort'])?>" required></label><label>PDF-Link<select name="pdf"><option value="" <?=$currentGrammarPdf===''?'selected':''?>>— kein PDF —</option><?php if(!$currentGrammarPdfExists):?><option value="<?=Html::e($currentGrammarPdf)?>" selected>⚠ <?=Html::e($currentGrammarPdf)?> (Datei nicht gefunden)</option><?php endif;?><?php foreach($grammarPdfFiles as $grammarPdfFile):?><option value="<?=Html::e($grammarPdfFile)?>" <?=$currentGrammarPdf===$grammarPdfFile?'selected':''?>><?=Html::e($grammarPdfFile)?></option><?php endforeach;?></select></label><label style="grid-column:1/-1">Erklärung<textarea name="erklaerung"><?=Html::e((string)$grammar['erklaerung'])?></textarea></label></div><button type="submit">Änderungen speichern</button></form><form method="post" action="index.php" class="delete-form" onsubmit="return confirm('Grammatikeintrag wirklich löschen?')"><input type="hidden" name="csrf_token" value="<?=Html::e($csrf->token())?>"><input type="hidden" name="form_action" value="delete_grammar"><input type="hidden" name="id" value="<?=(int)$grammar['id']?>"><button class="danger" type="submit">Eintrag löschen</button></form></details><?php endforeach;?>
</section>

<section class="panel list-panel" id="passwort"><h2>Administrationspasswort ändern</h2><form method="post" action="index.php"><input type="hidden" name="csrf_token" value="<?=Html::e($csrf->token())?>"><input type="hidden" name="form_action" value="change_password"><div class="form-grid"><label>Bisheriges Passwort<input type="password" name="current_password" required></label><label>Neues Passwort<input type="password" name="new_password" minlength="12" required></label><label>Neues Passwort wiederholen<input type="password" name="new_password_repeat" minlength="12" required></label></div><button type="submit">Passwort ändern</button></form></section>
<section class="panel list-panel" id="lernkarten"><h2>Lernkarten bearbeiten</h2>
<form method="get" action="index.php" class="verb-search-form">
    <input type="hidden" name="action" value="admin">
    <?php if($adminSearch!==''):?><input type="hidden" name="suche" value="<?=Html::e($adminSearch)?>"><input type="hidden" name="feld" value="<?=Html::e($adminField)?>"><?php endif;?>
    <label>Anzahl Datensätze anzeigen
        <select name="anzahl" onchange="this.form.submit()">
            <?php foreach(['25','50','100','500','1000','alle'] as $limitOption):?>
                <option value="<?=Html::e($limitOption)?>" <?=$adminLimitRaw===$limitOption?'selected':''?>><?=Html::e($limitOption==='alle'?'Alle':$limitOption)?></option>
            <?php endforeach;?>
        </select>
    </label>
    <noscript><button type="submit">Anzeigen</button></noscript>
</form>
<p class="muted"><?php if($adminLimit===null):?>Alle <?=$adminTotal?> <?= $adminSearch!=='' ? 'Treffer' : 'Datensätze' ?> werden angezeigt.<?php else:?><?=count($cards)?> von <?=$adminTotal?> <?= $adminSearch!=='' ? 'Treffern' : 'Datensätzen' ?> werden angezeigt.<?php endif;?></p><?php foreach($cards as $card):?><details class="entry"><summary>DB-ID #<?=(int)$card['id']?> · L<?=Html::e((string)($card['lektion']??'–'))?> · <?=Html::e((string)$card['wort_de'])?> ↔ <?=Html::e((string)$card['wort_it'])?></summary><form method="post" action="index.php"><input type="hidden" name="csrf_token" value="<?=Html::e($csrf->token())?>"><input type="hidden" name="form_action" value="update_card"><input type="hidden" name="id" value="<?=(int)$card['id']?>"><div class="form-grid"><label>Datenbank-ID<input type="text" value="<?=(int)$card['id']?>" readonly></label><label>Deutsch<input type="text" name="wort_de" value="<?=Html::e((string)$card['wort_de'])?>" required></label><label>Italiano<input type="text" name="wort_it" value="<?=Html::e((string)$card['wort_it'])?>" required></label><label>Deutscher Satz<textarea name="satz_de"><?=Html::e((string)$card['satz_de'])?></textarea></label><label>Frase italiana<textarea name="satz_it"><?=Html::e((string)$card['satz_it'])?></textarea></label><label>Lektion<input type="number" name="lektion" value="<?=Html::e((string)($card['lektion']??''))?>"></label></div><button>Änderungen speichern</button></form><form method="post" action="index.php" class="delete-form" onsubmit="return confirm('Lernkarte wirklich löschen?')"><input type="hidden" name="csrf_token" value="<?=Html::e($csrf->token())?>"><input type="hidden" name="form_action" value="delete_card"><input type="hidden" name="id" value="<?=(int)$card['id']?>"><button class="danger">Löschen</button></form></details><?php endforeach;?></section></main></body></html>
