<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная сраница</title>
</head>
<body>
<h1>Главная страница</h1>
<?php

$passwords = [
    0 => "admin",
    1 => "empl1",
    2 => "decl1",
    3 => "decl2",
    4 => "empl2",
];

$psw = "qwert";

foreach ($passwords as $i) {
//    echo "<p>".$i."</p>"."\n";
    echo $i, ": ", password_hash($i, PASSWORD_DEFAULT), " <br> ";
}
?>
</body>
</html>
<?php
