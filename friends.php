<?php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/funcs.php';

if (isset($_GET['add'])) {
    if ($_GET['add'] == $_SESSION['user']['id']) {
        $_SESSION['errors'] = 'Вы не можете добавить сами себя в друзья';
        header("Location: friends.php");
        die;
    }
    add_friend($_GET['add']);
    header("Location: friends.php");
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

    <p>Вы онлайн: <?= htmlspecialchars($_SESSION['user']['login']) ?> (<?= get_email($_SESSION['user']['id']) ?>) <img src="<?= get_avatar($_SESSION['user']['id']) ?>" onerror="this.src='images/avatar.png'" alt="" style="width: 100px; height: 100px;"></p>

    <div class="row">
        <div class="col-sm-3" style="overflow-y: auto; height:65vh;">
            <h3>Мои друзья</h3>
            <div class="col-sm-4">
                <?php
                $friends = get_friends();
                foreach ($friends as $friend) {
                    echo "<p>{$friend['nickname']}</p>";
                }
                ?>
            </div>
        </div>
        <div class="col-sm-9">
            <form class="mb-3" action="friends.php" method="post">
                <div class="form-group mb-3">
                    <label class="mb-3" for="exampleInputEmail1">Введите email или никнейм пользователя(Пустая строка - список всех пользователей)</label>
                    <input name="nickname" class="form-control mb-3" id="exampleInputEmail1" aria-describedby="emailHelp"
                           placeholder="Найти">
                </div>
                <button type="submit" class="btn btn-primary mb-3" name="search">Поиск</button>
            </form>
            <ul class="list-group">
            <?php
            $post_nickname = $_POST['nickname'] ?? null;
            $users = (isset($_POST['search']) and $_POST['nickname'] != '') ? get_user($_POST['nickname']) : get_all_users();
            if ($users) {
                foreach ($users as $user) {
                    if ($user['email'] == $post_nickname and $user['hide_email']) {
                        echo "<p>Пользователь не найден</p>";
                        break;
                    }
                    elseif ($user['nickname']) {
                        $nickname = $user['nickname'];
                    } else {
                        $nickname = $user['email'];
                    }
                    echo "<li class='list-group-item d-flex justify-content-between align-items-center'>$nickname<a href='?add={$user['id']}' class='badge bg-primary rounded-pill'>Добавить в друзья</a> </li>";
                }
            } else {
                echo "<p>Пользователь не найден</p>";
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
            crossorigin="anonymous"></script>
</body>
</html>