<?php
    include_once '../../../php/classes/User.php';
    include_once '../../../php/classes/Patent.php';
    include_once '../../../php/functions/checkPageAccessRight.php';
    checkPageAccessRight('new-patent');
    $user = new User();
    if (isset($_COOKIE['session_id']))
    {
        $user->authorizeBySessionId($_COOKIE['session_id']);
    }
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Создание заявки</title>
</head>
<body>
    <?php include_once(dirname(__DIR__).'/__components/header.php'); ?>

    <h1>Создаем заявку на хайповое изобретение без кринжа</h1>

    <form action="/handlers/form/?method=new_patent" method="post" enctype="multipart/form-data">
        <label>
            <span>Введите названиие патента:</span>
            <br>
            <input type="text" name="patentName" required>
            <br>
        </label>
        <label>
            <span>Прикрепите файл заявления в формате pdf</span>
            <br>
            <input type="file" name="patentRequestFile" required>
            <br>
        </label>
        <label>
            <span>Прикрепите файл с описанием изобретения в формате pdf</span>
            <br>
            <input type="file" name="patentDescriptionFile" required>
            <br>
        </label>
        <button type="submit">Отправить на проверку</button>
    </form>

    <script src="../../src/js/main.js"></script>
</body>
</html>