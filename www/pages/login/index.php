<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="../../src/css/style.css">
</head>
<body>
<h1>Патентный отдел</h1>
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
        <input type="submit" placeholder="Войти">
    </form>
<!--    <a class="login__link--guest" href="/home.php">Войти как гость</a>-->
    <a class="login__link--sign-up" href="#" >Регистрация</a>
</section>
</body>
</html>