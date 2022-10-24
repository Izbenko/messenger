<?php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/funcs.php';

/*if (isset($_POST['message']) and isset($_GET['id'])) {
    if (send_message($_POST['message'], $_SESSION['user']['id'], $_GET['id'])) {
        header("Location: /?id={$_GET['id']}");
        die;
    } else {
        $_SESSION['errors'] = 'Что-то пошло не так';
        header("Location: /?id={$_GET['id']}");
        die;
    }
}

if (isset($_POST['message']) and isset($_GET['group_id'])) {
    echo '1';
    if (send_message_group($_POST['message'], $_SESSION['user']['id'], $_GET['group_id'])) {
        header("Location: /?group_id={$_GET['group_id']}");
        die;
    } else {
        $_SESSION['errors'] = 'Что-то пошло не так';
        header("Location: /?group_id={$_GET['group_id']}");
        die;
    }
}*/

if (isset($_POST['delete'])) {
    if ($_POST['delete-group'] == '1') {
        if (message_group_delete($_POST['delete-id'])) {
            $_SESSION['success'] = "Сообщение удалено";
            header("Location: /?group_id={$_GET['group_id']}");
            die;
        } else {
            $_SESSION['errors'] = "Что-то пошло не так";
            header("Location: /?group_id={$_GET['group_id']}");
            die;
        }
    } else {
        if (message_delete($_POST['delete-id'])) {
            $_SESSION['success'] = "Сообщение удалено";
            header("Location: /?id={$_GET['id']}");
            die;
        } else {
            $_SESSION['errors'] = "Что-то пошло не так";
            header("Location: /?id={$_GET['id']}");
            die;
        }
    }
}

if (isset($_POST['edit'])) {
    if ($_POST['edit-group'] == '1') {
        group_edit($_POST['edit-id'], $_POST['edit-message']);
        header("Location: /?group_id={$_GET['group_id']}");
        die;
    } else {
        edit($_POST['edit-id'], $_POST['edit-message']);
        header("Location: /?id={$_GET['id']}");
        die;
    }
}

if (isset($_GET['group_id']) and isset($_GET['add'])) {
    add_in_group($_GET['group_id'], $_GET['add']);
    header("Location: /?group_id={$_GET['group_id']}");
    die;
}

if (isset($_GET['group_id'])) {
    if (!is_in_group($_GET['group_id'], $_SESSION['user']['id'])) {
        $_SESSION['errors'] = 'Что-то пошло не так';
        header("Location: /");
        die;
    }
}

if (isset($_GET['id'])) {
    $flag = false;
    $friends_arr = get_friends();
    $user = get_nickname($_GET['id']);
    foreach ($friends_arr as $item) {
        if ($user == $item['nickname']) {
            $flag = true;
        }
    }
    if (!$flag) {
        $_SESSION['errors'] = 'Что-то пошло не так';
        header("Location: /");
        die;
    }
}

?>
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
            src='<?= get_avatar($_SESSION['user']['id']) ?>'
            onerror="this.src='images/avatar.png'" alt=''
            style='width: 100px; height: 100px;'></p>

<div class="row">
    <div class="col-sm-3" style="overflow-y: auto; height:50vh;">
        <h6>Мои чаты</h6>
        <div class="col-sm-12">
            <div class="col-sm-12">
                <?php
                $friends = get_friends();
                foreach ($friends as $friend) {
                    $avatar = get_avatar($friend['id']);
                    echo "<p><img src='$avatar'
                onerror=\"this.src='images/avatar.png'\" alt=''
                style='width: 30px; height: 30px;'><a href='?id={$friend['id']}'> {$friend['nickname']}</a></p>";
                }
                ?>
            </div>
        </div>
        <h6>Групповые чаты <a href="/new_group.php">(создать)</a></h6>
        <div class="col-sm-4">
            <div class="col-sm-12">
                <?php
                $groups = get_groups();
                foreach ($groups as $group) {
                    echo "<p><a href='?group_id={$group['id']}'> {$group['name']}</a></p>";
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    if (isset($_GET['id'])) {
        $action = "/?id={$_GET['id']}";
    } elseif (isset($_GET['group_id'])) {
        $action = "/?group_id={$_GET['group_id']}";
    } else {
        $action = '';
    }
    ?>
    <form id="chat" class="col-sm-9" method="post" action="<?= $action ?>">
        <?php
        if (isset($_GET['id'])) {
            $nickname = get_nickname($_GET['id']);
            echo "<h6>Чат с пользователем {$nickname}</h6>";
        } elseif (isset($_GET['group_id'])) {
            $group = get_group($_GET['group_id']);
            $title = "<h6>Групповой чат '$group' (Участники: ";
            $users = get_users_group($_GET['group_id']);
            foreach ($users as $user) {
                $nickname = get_nickname($user['user_id']);
                $title .= "$nickname ";
            }
            $title .= ") <p class='menu btn btn-primary'>Настройки группового чата</p></h6>";
            echo $title;
        }
        ?>
        <div id="divMessages" class="chat message border rounded container">
            <div class="chat-content" id="chat-result">
                <?php
                if (isset($_GET['id'])) {
                    $messages = get_chat($_GET['id']);
                    if (!$messages) {
                        $nickname = get_nickname($_GET['id']);
                        echo "<h6 class='col-md-4 mx-auto mt-3'>Начните переписку с $nickname</h6>";
                    }
                    foreach ($messages as $message) {
                        $id = $message['id'];
                        $avatar = get_avatar($message['user_id']);
                        $a = "<img src='$avatar' onerror=\"this.src='images/avatar.png'\" style='width: 20px; height: 20px;'>";
                        $date = $message['date'];
                        $nickname = get_nickname($message['user_id']);
                        echo "<input type='hidden' class='group' value='0'>
                                <p value=\"$id\" class='msg'> $a ($date) $nickname: <span value='$id'>{$message['text']}</span></p>";
                    }
                } elseif (isset($_GET['group_id'])) {
                    $messages = get_group_chat($_GET['group_id']);
                    if (!$messages) {
                        echo "<h6 class='col-md-4 mx-auto mt-3'>В этом групповом чате еще никто не писал. Будьте первыми</h6>";
                    }
                    $id = 1;
                    foreach ($messages as $message) {
                        $id = $message['id'];
                        $avatar = get_avatar($message['user_id']);
                        $a = "<img src='$avatar' onerror=\"this.src='images/avatar.png'\" style='width: 20px; height: 20px;'>";
                        $date = $message['date'];
                        $nickname = get_nickname($message['user_id']);
                        echo "<input type='hidden' class='group' value='1'>
                            <p value=\"$id\" class='msg'> $a ($date) $nickname: <span value='$id'>{$message['text']}</span></p>";
                    }
                } else {
                    echo '<h6 class="col-md-4 mx-auto mt-3">Откройте активный чат или начните новый</h6>';
                }
                ?>
            </div>
        </div>
        <div id="blockSendMessage" class="row">
            <?php
            $user = $_SESSION['user']['login'];
            $avatar = get_avatar($_SESSION['user']['id']);
            $date = date("Y-m-d H:i:s");
            $from_id = $_SESSION['user']['id'];
            $to_id = $_GET['id'] ?? '';
            $is_group = null;
            if (isset($_GET['group_id'])) {
                $to_id = $_GET['group_id'];
                $is_group = 1;
            }
            ?>
            <input type="hidden" name="user" id="user" value="<?= $user ?>">
            <input type="hidden" name="avatar" id="avatar" value="<?= $avatar ?>">
            <input type="hidden" name="date" id="date" value="<?= $date ?>">
            <input type="hidden" name="to_id" id="to_id" value="<?= $to_id ?>">
            <input type="hidden" name="from_id" id="from_id" value="<?= $from_id ?>">
            <input type="hidden" name="is_group" id="is_group" value="<?= $is_group ?>">
            <input name="message" id="message" class="form-control col-8 col-sm-9 col-md-8" type="text"
                   placeholder="Сообщение">
            <button id="btnSend" type="submit" class="btn btn-secondary col-4 col-sm-3 col-md-4">Отправить</button>
        </div>
    </form>
</div>

<div class="overlay">
    <div class="popup">
        <h2>Настройки</h2>
        <p class="add_user btn btn-danger">Добавить пользователей</p>
        <p class="btn btn-danger">Выключить звук</p>
        <div class="close-popup"></div>
    </div>
</div>

<div class="add">
    <div class="popup">
        <h2>Добавить пользователей</h2>
        <?php
        $group = $_GET['group_id'];
        $friends = get_friends();
        foreach ($friends as $friend) {
            echo "<p>{$friend['nickname']} <a href='?group_id=$group&add={$friend['id']}' class='badge bg-danger rounded-pill'>Добавить</a></p>";
        }
        ?>
        <div class="close-popup-add"></div>
    </div>
</div>

<div class="edit-menu">
    <div class="popup">
        <h2>Настройки</h2>
        <p class="edit btn btn-danger">Редактировать сообщение</p>
        <form action="" method="post">
            <input type='hidden' name='delete-id' id='delete-id' value=''>
            <input type='hidden' name='delete-group' id='delete-group' value=''>
            <button type="submit" name="delete" class="delete btn btn-danger">Удалить сообщение</button>
        </form>
        <div class="close-edit"></div>
    </div>
</div>

<div class="msg-edit">
    <div class="popup">
        <h2>Редактировать сообщение</h2>
        <form action="" method="post">
            <input type='hidden' name='edit-id' id='edit-id' value=''>
            <input type='hidden' name='edit-group' id='edit-group' value=''>
            <input name="edit-message" id="edit-message" class="form-control col-8 col-sm-9 col-md-8"
                   type="text"
                   placeholder="">
            <button type="submit" name="edit" class="btn btn-secondary col-4 col-sm-3 col-md-4">Отправить</button>
        </form>
        <div class="close-msg-edit"></div>
    </div>
</div>
