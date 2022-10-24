<?php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/funcs.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>Мессенджер</title>
</head>
<body>
<nav class="navbar navbar-light bg-light mb-3">
    <a class="navbar-brand" href="/">
        <p class="text-center"><img src="images/logo.png" width="5%" height="5%">SkillFactoryMessenger</p>
    </a>
</nav>
<?php
if ($_GET['hash']) {
    $hash = $_GET['hash'];
    $res = $pdo->prepare("SELECT * FROM users WHERE hash = ?");
    $res->execute([$hash]);
    if ($user = $res->fetch()) {
        if ($user['email_confirmed'] == 1) {
            $res = $pdo->prepare("UPDATE `users` SET `email_confirmed` = 0 WHERE `id` = ?");
            $res->execute([$user['id']]);
            echo "<p>Email подтверждён</p>";
            echo "<a href='/'>На главную</a>";
        } else {
                echo "Что то пошло не так";
            }
    } else {
        echo "Что то пошло не так";
    }
} else {
    echo "Что то пошло не так";
}
?>
</body>
</html>