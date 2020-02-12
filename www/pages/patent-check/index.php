<?php
    include_once '../../../php/classes/User.php';
    include_once '../../../php/functions/checkPageAccessRight.php';
    checkPageAccessRight('patent-check');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Рассмотрение заявки</title>
</head>
<body>
    <?php include_once(dirname(__DIR__).'/__components/header.php'); ?>

    <h1>Сразу видно заявОЧКА</h1>

    <script src="../../src/js/main.js"></script>
</body>
</html>