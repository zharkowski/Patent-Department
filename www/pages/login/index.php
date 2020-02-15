<?php
    include_once '../../../php/classes/User.php';
    include_once '../../../php/functions/checkPageAccessRight.php';
    $user = new User();
    if (isset($_COOKIE['session_id']))
    {
        $user->authorizeBySessionId($_COOKIE['session_id']);
    }

    if ($user->isAuthorized())
    {
        header("Location: /pages/home");    }
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="../../src/css/style.css">
</head>
<body class="login-page">
    <div class="login-wrapper">
        <h1 class="login-title">Патентный отдел</h1>
        <section class="login">
            <h2>Авторизация</h2>
            <form class="login__form" action="/handlers/form/?method=login" method="post">
                <label class="login__label">
                    <input class="login__input" type="text" name="login" placeholder="Логин" required>
                </label>
                <br>
                <label class="login__label">
                    <input class="login__input" type="password" name="password" placeholder="Пароль" required>
                </label>
                <p class="login__error <?php if(!isset($_GET['isAuthorized'])) {echo 'visually-hidden';} ?>">Неверный логин или пароль</p>
                <br>
        <!--        <label>-->
        <!--            <input type="checkbox" class="login__foreign-computer" name="foreign-computer">-->
        <!--            Чужой компьютер-->
        <!--        </label>-->
                <br>
                <input class="login--submit button" type="submit" value="Войти">
            </form>
        <!--    <a class="login__link--guest" href="/home.php">Войти как гость</a>-->
            <a class="login__link login__link--sign-up" href="#" >Регистрация</a>
            <script src="../../src/js/login.js"></script>
        </section>
    </div>
</body>
</html>