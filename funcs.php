<?php
session_start();

function registration(): bool //регистриция
{
    global $pdo;

    $email = !empty($_POST['email']) ? trim($_POST['email']) : '';
    $pass = !empty($_POST['pass']) ? trim($_POST['pass']) : '';
    $cpass = !empty($_POST['confirm_pass']) ? trim($_POST['confirm_pass']) : '';

    if (empty($email) || empty($pass) || empty($cpass)) {
        $_SESSION['errors'] = 'Поля email/пароль обязательны';
        return false;
    }

    if ($pass !== $cpass) {
        $_SESSION['errors'] = 'Пароли не совпадают';
        return false;
    }

    $res = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $res->execute([$email]);
    if ($res->fetchColumn()) {
        $_SESSION['errors'] = 'Данный email уже используется';
        return false;
    }

    $pass = password_hash($pass, PASSWORD_DEFAULT);
    $hash = md5($email . time());
    $res = $pdo->prepare("INSERT INTO users (email, password, hash) VALUES (?,?,?)");
    if ($res->execute([$email, $pass, $hash])) {
        $message = "Чтобы подтвердить Email, перейдите по ссылке: " . REDIRECT . $hash;

        if (mail($email, "Подтверждение Email", $message)) {
            $_SESSION['success'] = 'Ссылка для подтверждения пароля отправлена на почту';
            return true;
        } else {
            $_SESSION['errors'] = 'Ошибка регистрации';
            return false;
        }

    }
}

function login(): bool //вход
{

    global $pdo;
    $email = !empty($_POST['email']) ? trim($_POST['email']) : '';
    $pass = !empty($_POST['pass']) ? trim($_POST['pass']) : '';
    $csrf = $_POST['token'];

    if ($csrf != $_SESSION['CSRF']) {
        $_SESSION['errors'] = 'Ошибка';
        return false;
    }

    if (empty($email) || empty($pass)) {
        $_SESSION['errors'] = 'Поля логин/пароль обязательны';
        return false;
    }

    $res = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $res->execute([$email]);
    if (!$user = $res->fetch()) {
        $_SESSION['errors'] = 'Логин/пароль введены неверно';
        return false;
    }

    if (!password_verify($pass, $user['password'])) {
        $_SESSION['errors'] = 'Логин/пароль введены неверно';
        return false;
    } else {
        if ($user['email_confirmed'] == 1) {
            $_SESSION['errors'] = 'Подтвердите свой Email';
            return false;
        }
        $_SESSION['success'] = 'Вы успешно авторизовались';
        $_SESSION['user']['login'] = $user['nickname'] ?? $user['email'];
        $_SESSION['user']['id'] = $user['id'];
        return true;
    }
}

function upload(): bool //загрузка аватара
{
    global $pdo;


    if (!empty($_FILES)) {

        $fileName = $_FILES['file']['name'];

        if ($_FILES['file']['size'] > UPLOAD_MAX_SIZE) {
            $_SESSION['errors'] = 'Недопустимый размер файла ' . $fileName;
            return false;
        }

        if (!in_array($_FILES['file']['type'], ALLOWED_TYPES)) {
            $_SESSION['errors'] = 'Недопустимый формат файла ' . $fileName;
            return false;
        }

        $filePath = UPLOAD_DIR . '/' . time() . basename($fileName);
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
            $_SESSION['errors'] = 'Ошибка загрузки файла ' . $fileName;
            return false;
        } else {
            $res = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?;");
            $res->execute([$filePath, $_SESSION['user']['id']]);
        }
    }
    $_SESSION['success'] = 'Аватар изменен';
    return true;
}

function get_avatar($id) //получить ссылку на аватарку
{
    global $pdo;
    $res = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $res->execute([$id]);
    $res = $res->fetchAll();
    return $res[0]['avatar'];
}

function get_all_users(): array //получить всех пользователей
{
    global $pdo;
    $res = $pdo->prepare("SELECT * FROM users");
    $res->execute([]);
    $res = $res->fetchAll();
    return $res;
}

function get_user($nickname) //получить польователя по никнейму или email
{
    global $pdo;
    $res_nickname = $pdo->prepare("SELECT * FROM users WHERE nickname = ?");
    $res_email = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $res_nickname->execute([$nickname]);
    $res_email->execute([$nickname]);
    if ($res = $res_nickname->fetchAll()) {
        return $res;
    }
    if ($res = $res_email->fetchAll()) {
        return $res;
    } else {
        return false;
    }
}

function clean($value = "") //очистка пользовательского ввода
{
    $value = trim($value);
    $value = stripslashes($value);
    $value = strip_tags($value);
    $value = htmlspecialchars($value);

    return $value;
}

function change_nickname($nickname) //установить или изменить никнейм
{
    $nickname = clean($nickname);
    if (mb_strlen($nickname) < 3) {
        $_SESSION['errors'] = 'Неверный формат';
        return false;
    }

    global $pdo;

    $res = $pdo->prepare("SELECT COUNT(*) FROM users WHERE nickname = ? AND id <> ?");
    $res->execute([$nickname, $_SESSION['user']['id']]);
    if ($res->fetchColumn()) {
        $_SESSION['errors'] = 'Данный nickname уже используется';
        return false;
    }

    $res = $pdo->prepare("UPDATE users SET nickname = ? WHERE id = ?;");
    if ($res->execute([$nickname, $_SESSION['user']['id']])) {
        $_SESSION['user']['login'] = $nickname;
        $_SESSION['success'] = 'Никнейм успешно изменен';
        return true;
    } else {
        $_SESSION['success'] = 'Что-то пошло не так';
        return false;
    }
}

function get_friends(): array //список друзей
{
    global $pdo;
    $res = $pdo->prepare("SELECT * FROM users JOIN friends ON users.id = friends.friend_id WHERE user_id = ?");
    $res->execute([$_SESSION['user']['id']]);
    $res = $res->fetchAll();
    $arr = [];
    foreach ($res as $item) {
        if ($item['nickname']) {
            $arr[] = ['id' => $item['friend_id'], 'nickname' => $item['nickname']];
        } else {
            $arr[] = ['id' => $item['friend_id'], 'nickname' => $item['email']];
        }
    }
    return $arr;
}

function add_friend($id) //добавить пользователя(id) в друзья
{
    global $pdo;

    $check = $pdo->prepare("SELECT * FROM users JOIN friends ON users.id = friends.friend_id WHERE user_id = ? and friend_id = ?");
    $check->execute([$_SESSION['user']['id'], $id]);
    if ($check->fetchAll()) {
        $_SESSION['errors'] = 'Этот пользователь уже у вас в друзьях';
        return false;
    }

    $res = $pdo->prepare("INSERT INTO friends (user_id, friend_id) VALUES (?,?)");
    if ($res->execute([$_SESSION['user']['id'], $id])) {
        $_SESSION['success'] = 'Новый друг добавлен';
        return true;
    } else {
        $_SESSION['errors'] = 'Что-то пошло не так';
        return false;
    }
}

function get_email($id) //получить email
{
    global $pdo;
    $res = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $res->execute([$id]);
    if ($res = $res->fetchAll()) {
        if ($res[0]['hide_email']) {
            return 'email скрыт';
        }
        return $res[0]['email'];
    } else {
        return false;
    }
}

function get_nickname($id) //получить никнейм(или email) по id
{
    global $pdo;
    $res = $pdo->prepare("SELECT nickname, email FROM users WHERE id = ?");
    $res->execute([$id]);
    $res = $res->fetchAll();
    if ($res[0]['nickname']) {
        return $res[0]['nickname'];
    } elseif ($res[0]['email']) {
        return $res[0]['email'];
    } else {
        return false;
    }
}

function hide_email($flag = false) //скрыть или полказать email
{
    global $pdo;

    $res = $pdo->prepare("SELECT nickname FROM users WHERE id = ?");
    $res->execute([$_SESSION['user']['id']]);
    if (!$res->fetchColumn()) {
        $_SESSION['errors'] = 'Введите никнейм для скрытия email';
        return false;
    }

    if ($flag) {
        $res = $pdo->prepare("UPDATE users SET hide_email = 1 WHERE id = ?");
        $res->execute([$_SESSION['user']['id']]);
        return true;
    } else {
        $res = $pdo->prepare("UPDATE users SET hide_email = 0 WHERE id = ?");
        $res->execute([$_SESSION['user']['id']]);
        return true;
    }
}

function get_chat($id) //получить сообщения чата с пользователем(id)
{
    global $pdo;
    $res = $pdo->prepare("SELECT messages.id as id, users.id as user_id, from_id, to_id, nickname, email, text, date FROM messages join users ON users.id=messages.from_id WHERE (from_id, to_id) in ((?,?), (?,?)) ORDER BY date");
    $res->execute([$id, $_SESSION['user']['id'], $_SESSION['user']['id'], $id]);
    return $res->fetchAll();
}

function send_message($text, $from_id, $to_id) //отправить сообщение от пользователя($from_id) пользователю($to_id)
{
    global $pdo;

    $res = $pdo->prepare("INSERT INTO messages (from_id, to_id, text) VALUES (?,?,?)");
    if ($res->execute([$from_id, $to_id, $text])) {
        return true;
    } else {
        $_SESSION['errors'] = 'Что-то пошло не так';
        return false;
    }
}

function add_in_group($group_id, $user_id) //добавить в группу(id) пользователя(id)
{
    global $pdo;

    if (is_in_group($group_id, $user_id)) {
        $_SESSION['errors'] = 'Пользователь уже состоит в группе';
        return false;
    }

    $res = $pdo->prepare("INSERT INTO group_users (group_id, user_id) VALUES (?, ?)");
    $res->execute([$group_id, $user_id]);
}

function create_group($name, $users = []) //создать групповой чат
{
    global $pdo;
    $name = clean($name);

    $res = $pdo->prepare("SELECT id FROM groups WHERE name = ?");
    $res->execute([$name]);
    if ($res->fetchColumn()) {
        $_SESSION['errors'] = 'Чат с таким названием уже существует';
        return false;
    }

    $res = $pdo->prepare("INSERT INTO groups (name) VALUES (?)");
    $res->execute([$name]);

    $res = $pdo->prepare("SELECT id FROM groups WHERE name = ?");
    $res->execute([$name]);
    $res = $res->fetchColumn();
    $group_id = $res;

    add_in_group($group_id, $_SESSION['user']['id']);
    foreach ($users as $user) {
        add_in_group($group_id, $user);
    }
    $_SESSION['success'] = "Групповой чат '$name' успешно создан";
}

function is_in_group($group_id, $user_id): bool //является ли пользователь(id) участником группы(id)
{
    global $pdo;

    $res = $pdo->prepare("SELECT * FROM group_users WHERE group_id = ? and user_id = ?");
    $res->execute([$group_id, $user_id]);
    if ($res->fetch()) {
        return true;
    } else {
        return false;
    }
}

function get_groups(): array //список всеъ групп
{
    global $pdo;
    $res = $pdo->prepare("SELECT groups.id as id, name FROM group_users JOIN groups ON group_users.group_id = groups.id WHERE user_id = ?");
    $res->execute([$_SESSION['user']['id']]);
    $res = $res->fetchAll();
    $arr = [];
    foreach ($res as $item) {
        $arr[] = ['id' => $item['id'], 'name' => $item['name']];
    }
    return $arr;
}

function get_group($id) //получить название группы по id
{
    global $pdo;
    $res = $pdo->prepare("SELECT name FROM groups WHERE id = ?");
    $res->execute([$id]);
    if($res = $res->fetchColumn()) {
        return $res;
    } else {
        return false;
    }
}

function get_group_chat($id) // получить все сообщения группы(id)
{
    global $pdo;

    $res = $pdo->prepare("SELECT users.id as user_id, group_messages.id as id, text, date FROM group_messages join users ON users.id = group_messages.user_id WHERE group_id = ? ORDER BY date");
    $res->execute([$id]);
    return $res->fetchAll();
}

function get_users_group($id) // получить участников группы(id)
{
    global $pdo;

    $res = $pdo->prepare("SELECT user_id FROM group_users WHERE group_id = ?");
    $res->execute([$id]);
    return $res->fetchAll();
}

function send_message_group($text, $from_id, $group_id) //отправить сообщение в группу
{
    global $pdo;

    $res = $pdo->prepare("INSERT INTO group_messages (group_id, user_id, text) VALUES (?,?,?)");
    if ($res->execute([$group_id, $from_id, $text])) {
        return true;
    } else {
        $_SESSION['errors'] = 'Что-то пошло не так';
        return false;
    }
}

function edit($id, $text) //отредактировать сообщение(id)
{
    global $pdo;

    $res = $pdo->prepare("UPDATE messages SET text = ? WHERE id = ?");
    if ($res->execute([$text, $id])) {
        return true;
    } else {
        $_SESSION['errors'] = 'Что-то пошло не так';
        return false;
    }
}

function group_edit($id, $text) //отредактировать сообщение(id) в групповом чате
{
    global $pdo;

    $res = $pdo->prepare("UPDATE group_messages SET text = ? WHERE id = ?");
    if ($res->execute([$text, $id])) {
        return true;
    } else {
        $_SESSION['errors'] = 'Что-то пошло не так';
        return false;
    }
}

function message_delete($id) //удалить сообщение(id)
{
    global $pdo;

    $res = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    if ($res->execute([$id])) {
        return true;
    } else {
        $_SESSION['errors'] = 'Что-то пошло не так';
        return false;
    }
}

function message_group_delete($id) //удалить сообщение(id) в грцпповом чате
{
    global $pdo;

    $res = $pdo->prepare("DELETE FROM group_messages WHERE id = ?");
    if ($res->execute([$id])) {
        return true;
    } else {
        $_SESSION['errors'] = 'Что-то пошло не так';
        return false;
    }
}