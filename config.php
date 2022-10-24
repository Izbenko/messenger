<?php

define('URL', '/'); // URL текущей страницы

define('UPLOAD_MAX_SIZE', 10000000); // 10mb
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_DIR', 'avatars');

define('PORT', '8090');

define('REDIRECT', 'http://messenger/confirm.php?hash='); //ссылка для письма с подтверждением

$host = 'localhost';
$db = 'messenger';
$user = 'root';
$pass = '';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$pdo = new PDO($dsn, $user, $pass, $options);