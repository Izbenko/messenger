<div class="col-md-6 offset-md-3 mb-3">
    <h5>Войдите или зарегистрируйтесь</h5>
</div>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <h3>Регистрация</h3>
    </div>
</div>

<form action="index.php" method="post" class="row g-3">
    <div class="col-md-6 offset-md-3">
        <div class="form-floating">
            <input type="email" name="email" class="form-control" id="floatingInput" placeholder="Email">
            <label for="floatingInput">Email</label>
        </div>
    </div>

    <div class="col-md-6 offset-md-3">
        <div class="form-floating">
            <input type="password" name="pass" class="form-control" id="floatingPassword" placeholder="Пароль">
            <label for="floatingPassword">Пароль</label>
        </div>
    </div>

    <div class="col-md-6 offset-md-3">
        <div class="form-floating">
            <input type="password" name="confirm_pass" class="form-control" id="floatingConfirmPassword"
                   placeholder="Подтвердите пароль">
            <label for="floatingConfirmPassword">Подтверждение пароля</label>
        </div>
    </div>

    <div class="col-md-6 offset-md-3">
        <button type="submit" name="register" class="btn btn-primary">Зарегистрироваться</button>
    </div>
</form>

<div class="row mt-3">
    <div class="col-md-6 offset-md-3">
        <h3>Авторизация</h3>
    </div>
</div>

<form action="index.php" method="post" class="row g-3">
    <?php
    $token = hash('gost-crypto', random_int(0, 999999)); //генерация CSRF-токена
    $_SESSION["CSRF"] = $token;
    ?>
    <div class="col-md-6 offset-md-3">
        <div class="form-floating">
            <input type="email" name="email" class="form-control" id="floatingAuthInput" placeholder="Email">
            <label for="floatingAuthInput">Email</label>
        </div>
    </div>

    <input type="hidden" name="token" value="<?= $token ?>"> <br/>

    <div class="col-md-6 offset-md-3">
        <div class="form-floating">
            <input type="password" name="pass" class="form-control" id="floatingAuthPassword"
                   placeholder="Пароль">
            <label for="floatingAuthPassword">Пароль</label>
        </div>
    </div>

    <div class="col-md-6 offset-md-3">
        <button type="submit" name="auth" class="btn btn-primary">Войти</button>
    </div>
</form>