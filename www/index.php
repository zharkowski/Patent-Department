<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="src/css/style.css">
</head>
<body>
    <h1>Патентный отдел</h1>
    <section class="login">
        <h2>Авторизация</h2>
        <form class="login__form" action="home.php" method="post">
            <label class="login__label">
                <input class="login__input" type="text" name="login" placeholder="Логин">
            </label>
            <br>
            <label class="login__label">
                <input class="login__input" type="password" name="password" placeholder="Пароль">
            </label>
            <p class="login__error <?= 'visually-hidden' ?>"> Неверный логин или пароль </p>
            <br>
            <input type="submit" placeholder="Войти">
        </form>
        <a class="login__link--guest" href="home.php">Войти как гость</a>
        <a class="login__link--sign-up" href="#" >Регистрация</a>
    </section>
    <?php
        include_once '../php/classes/DataBaseConnection.php';
        use DB\DataBaseConnection as DBCon;
        $mysqli = DBCon::getInstance()->getConnection();
        $query = "SELECT id FROM users";
        $res = $mysqli->query($query);
        $res->data_seek(0);
        while ($row = $res->fetch_assoc()) {
            echo "<p> id = " . $row['id'] . "</p>";
        }
    ?>
</body>
</html>

