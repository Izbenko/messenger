<?php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/funcs.php';

if (isset($_POST['new_group'])) {
    if (!$_POST['name']) {
        $_SESSION['errors'] = 'Введите название чата';
        header("Location: new_group.php");
        die;
    }
    if (!$_POST['users']) {
        $_SESSION['errors'] = 'Выберите пользователей';
        header("Location: new_group.php");
        die;
    }
    create_group($_POST['name'], $_POST['users']);
    header("Location: /");
    die;
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

    <div class="col-md-6 offset-md-3 mb-3">
        <h5>Создание группового чата</h5>
    </div>
    <form action="/new_group.php" method="post" class="row g-3">
        <div class="col-md-6 offset-md-3">
            <div class="form-floating">
                <input type="text" name="name" class="form-control" id="floatingInput" placeholder="Название чата">
                <label for="floatingInput">Название чата</label>
            </div>
        </div>
        <div class="col-md-6 offset-md-3">
            <label for="users">Выберите пользователей (зажать Ctrl)</label>
            <select class="form-select" multiple aria-label="multiple select example" id="users" name="users[]">
                <?php
                $friends = get_friends();
                foreach ($friends as $friend) {
                    echo "<option value='{$friend['id']}'>{$friend['nickname']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-6 offset-md-3">
            <button type="submit" name="new_group" class="btn btn-primary">Создать</button>
        </div>
    </form>
</div>
</body>
</html>