<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Патентный отдел</title>
</head>
<body>
    <h1>Патентный отдел</h1>
    <?php
        include_once '../php/classes/DataBaseConnection.php';
        use DB\DataBaseConnection as DBCon;
        if ($_GET['param'] == 1) {
            echo "<h2>param</h2>";
        }
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

