<?php
declare(strict_types=1);
require __DIR__.'/bootstrap/init.php';
$formAction=(string)($_POST['form_action']??'');
if($_SERVER['REQUEST_METHOD']==='POST'&&$formAction!==''){
 $routes=['login'=>'login.php','change_password'=>'change-password.php','add_card'=>'card-add.php','update_card'=>'card-update.php','delete_card'=>'card-delete.php','upload_document'=>'document-upload.php','add_verb'=>'verb-add.php','update_verb'=>'verb-update.php','delete_verb'=>'verb-delete.php','add_grammar'=>'grammar-add.php','update_grammar'=>'grammar-update.php','delete_grammar'=>'grammar-delete.php','add_link'=>'link-add.php','update_link'=>'link-update.php','delete_link'=>'link-delete.php'];
 if(isset($routes[$formAction]))require __DIR__.'/actions/'.$routes[$formAction];
}
$action=(string)($_GET['action']??'');if($action==='logout')require __DIR__.'/actions/logout.php';
$routes=[''=>'home.php','admin'=>'admin.php','pdf'=>'pdf.php','pruefung'=>'exam.php','verben'=>'verbs.php','verben-pdf'=>'verbs-pdf.php'];require __DIR__.'/pages/'.($routes[$action]??'home.php');
