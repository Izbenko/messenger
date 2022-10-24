<?php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/funcs.php';

if (isset($_POST['upload'])) {
    upload();
    header("Location: settings.php");
    die;
}

if (isset($_POST['change_nickname'])) {
    if ($_POST['nickname'] == '') {
        $_SESSION['errors'] = 'Введите корректный никнейм';
        header("Location: settings.php");
        die;
    }
    change_nickname($_POST['nickname']);
    header("Location: settings.php");
    die;
}

if (isset($_POST['hide_email'])) {
    if ($_POST['hide']) {
        if (hide_email(true)) {
            $_SESSION['hide_email'] = true;
            $_SESSION['success'] = 'Email скрыт';
            header("Location: settings.php");
            die;
        }
    } else {
        hide_email(false);
        $_SESSION['hide_email'] = false;
        $_SESSION['success'] = 'Email открыт';
        header("Location: settings.php");
        die;
    }
}

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <title>Мессенджер</title>
</head>
<body>

<div class="container">
    <div class="row my-3">
        <div class="col">
            <?php if (!empty($_SESSION['errors'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php
                    echo $_SESSION['errors'];
                    unset($_SESSION['errors']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <nav class="navbar navbar-light bg-light mb-3">
        <a class="navbar-brand" href="/">
            <p class="text-center"><img src="images/logo.png" width="5%" height="5%">SkillFactoryMessenger</p>
        </a>
    </nav>
    <ul class="nav justify-content-center bg-light mb-3">
        <li class="nav-item">
            <a class="nav-link active" href="/">Чаты</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/friends.php">Друзья</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/settings.php">Настройки</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/?do=exit">Выйти</a>
        </li>
    </ul>
    <p>Вы онлайн: <?= htmlspecialchars($_SESSION['user']['login']) ?> (<?= get_email($_SESSION['user']['id']) ?>) <img
                src="<?= get_avatar($_SESSION['user']['id']) ?>" onerror="this.src='images/avatar.png'" alt=""
                style="width: 100px; height: 100px;"></p>
    <h3>Настройки</h3>
    <form method="post" action="settings.php">
        <div class="row mb-3">
            <label for="inputNickname" class="col-sm-2 col-form-label">Nickname</label>
            <div class="col-sm-10">
                <input placeholder="<?= $_SESSION['user']['login'] ?>" class="form-control" id="inputNickname"
                       name="nickname">
            </div>
        </div>
        <button name="change_nickname" type="submit" class="btn btn-primary mb-3">Сохранить</button>
    </form>

    <form method="post" action="settings.php">
        <div class="row mb-3">
            <div class="col-sm-10">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="gridCheck1" name="hide" <?php
                    if (isset($_SESSION['hide_email']) and $_SESSION['hide_email']) {
                        echo ' checked';
                    }
                    ?>>
                    <label class="form-check-label" for="gridCheck1">Скрыть email(Nickname обязателен)</label>
                </div>
            </div>
        </div>
        <button name="hide_email" type="submit" class="btn btn-primary mb-3">Сохранить</button>
    </form>

    <h4>Загрузите ваш аватар</h4>
    <p>Максимальный размер файла:
        <?php echo UPLOAD_MAX_SIZE / 1000000; ?>Мб.</p>
    <p>Допустимые форматы:
        <?php echo implode(', ', ALLOWED_TYPES) ?>.</p>
    </p>
    <div class="row mb-3">
        <div class="col-sm-10">
            <form class="form-check" method='post' action="settings.php" enctype="multipart/form-data">
                <input type='file' name='file' class="form-control" id='file-drop'><br>
                <input class="btn btn-primary" name="upload" type='submit' value='Загрузить' class="form-control">
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
        crossorigin="anonymous"></script>
</body>
</html>