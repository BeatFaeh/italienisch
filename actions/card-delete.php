<?php
declare(strict_types=1);$auth->requireAdmin();$csrf->verify();$id=(int)($_POST['id']??0);if($id>0&&$cardRepository->delete($id))$flash->set('success','Lernkarte gelöscht.');else$flash->set('error','Lernkarte konnte nicht gelöscht werden.');header('Location: index.php?action=admin#lernkarten');exit;
