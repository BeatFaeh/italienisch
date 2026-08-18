<?php
declare(strict_types=1);$auth->requireAdmin();$csrf->verify();
$wd=trim((string)($_POST['wort_de']??''));$wi=trim((string)($_POST['wort_it']??''));$sd=trim((string)($_POST['satz_de']??''));$si=trim((string)($_POST['satz_it']??''));$lRaw=trim((string)($_POST['lektion']??''));$l=$lRaw===''?null:(int)$lRaw;
if($wd===''||$wi==='')$flash->set('error','Deutsches und italienisches Wort müssen ausgefüllt sein.');elseif($cardRepository->add($wd,$wi,$sd,$si,$l))$flash->set('success','Lernkarte gespeichert.');else$flash->set('error','Lernkarte konnte nicht gespeichert werden.');header('Location: index.php?action=admin#lernkarten');exit;
