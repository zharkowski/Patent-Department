<?php
    include_once '../../../php/classes/User.php';
    include_once '../../../php/functions/checkPageAccessRight.php';
    checkPageAccessRight('home');

?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная сраница</title>
</head>
<body>
<?php include_once(dirname(__DIR__).'/__components/header.php'); ?>
<?php
//
//$passwords = [
//    0 => "admin",
//    1 => "empl1",
//    2 => "decl1",
//    3 => "decl2",
//    4 => "empl2",
//];
//
//$psw = "qwert";
//
//foreach ($passwords as $i) {
//    echo $i, ": ", password_hash($i, PASSWORD_DEFAULT), " <br> ";
//}
//?>
    <h1>Home sweet hole</h1>
    <h2>Ну привет, <?php echo $user->getUserName()?> </h2>
    <section></section>

    <script src="../../src/js/main.js"></script>
</body>
</html>
