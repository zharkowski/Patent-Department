<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
include_once '../../../php/classes/User.php';
include_once '../../../php/classes/Patent.php';
include_once '../../../php/functions/checkPageAccessRight.php';
checkPageAccessRight('home');
$user = new User();
if (isset($_COOKIE['session_id']))
{
    $user->authorizeBySessionId($_COOKIE['session_id']);
}
?>

<?php
$patents = getPatentsForUser($user);
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../src/css/style.css">
    <title>Главная сраница</title>
</head>
<body>
<?php include_once(dirname(__DIR__).'/__components/header.php'); ?>

    <div class="patents-wrapper">
        <ul class="patents-list">
            <?php foreach($patents as $patent) {
                list($status_class, $status_text) = formatStatus($patent['status']);
                ?>
                <li class="patents-list__card">
                    <a class="patent-item patent-item__link <?php echo $status_class ?>" href="/pages/patent/?id=<?php echo $patent['id'] ?>">
                        <div class="patent-item patent-item__title"><?php echo $patent['title'] ? $patent['title'] : 'Без названия (' . $patent['id'] . ')' ?></div>
                        <div class="patent-item patent-item__status-text">Статус: <?php echo $status_text ?></div>
                        <div class="patent-item patent-item__update-time">Обновлено <?php echo $patent['timestamp'] ?></div>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>

    <script src="../../src/js/main.js"></script>
</body>
</html>
