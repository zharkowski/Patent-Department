<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
    include_once '../../../php/classes/User.php';
    include_once '../../../php/classes/Patent.php';
    include_once '../../../php/functions/checkPageAccessRight.php';
    checkPageAccessRight('my-patents');
    $user = new User();
    if (isset($_COOKIE['session_id']))
    {
        $user->authorizeBySessionId($_COOKIE['session_id']);
    }
?>

<?php
    $patents = getPatentsForUser($user);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои патенты</title>
</head>
<body>
    <?php include_once(dirname(__DIR__).'/__components/header.php'); ?>

    <h1>Патенты мои патенты</h1>

    <div class="orders-wrapper">
        <div class="orders-grid">
            <?php foreach($patents as $patent) {
                list($status_class, $status_text) = formatStatus($patent['status']);
                ?>
                <a class="order-item <?php echo $status_class ?>" href="/pages/patent/?id=<?php echo $patent['id'] ?>">
                    <div class="order-item__title"><?php echo $patent['title'] ? $patent['title'] : 'Без названия (' . $patent['id'] . ')' ?></div>
                    <div class="order-item__status-text"><?php echo $status_text ?></div>
                    <div class="order-item__update-time">Обновлено <?php echo $patent['timestamp'] ?> UTC</div>
                </a>
            <?php } ?>
        </div>
    </div>

    <script src="../../src/js/main.js"></script>
</body>
</html>
