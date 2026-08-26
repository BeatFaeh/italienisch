<?php
declare(strict_types=1);
$appConfig=require __DIR__.'/../config/app.php';$dbConfig=require __DIR__.'/../config/database.php';
session_name((string)$appConfig['session_name']);session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off','samesite'=>'Strict','path'=>'/']);session_start();
foreach(['Database.php','Security/Auth.php','Security/Csrf.php','Support/Flash.php','Support/Html.php','Repository/SettingsRepository.php','Repository/CardRepository.php','Repository/VerbRepository.php','Repository/GrammarRepository.php','Repository/LinkRepository.php','Service/QuizService.php','Service/ExamService.php'] as $file) require_once __DIR__.'/../src/'.$file;
$database=new Database($dbConfig);$db=$database->connection();$auth=new Auth();$csrf=new Csrf();$flash=new Flash();$settingsRepository=new SettingsRepository($db);$cardRepository=new CardRepository($db);$verbRepository=new VerbRepository($db);$grammarRepository=new GrammarRepository($db);$linkRepository=new LinkRepository($db);$quizService=new QuizService($db);$examService=new ExamService($cardRepository,$quizService);
require __DIR__.'/../database/schema.php';
