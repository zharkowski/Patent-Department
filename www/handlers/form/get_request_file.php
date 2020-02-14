<?php
include_once '../../../php/classes/User.php';
include_once '../../../php/classes/Patent.php';

function get_request_file() {
    if (!isset($_GET['file_id'])) {
        header('Location: /');
    } else {
        $fileId = $_GET['file_id'];
        $fileLocation = $_SERVER["DOCUMENT_ROOT"] . "/../files/requests/" . $fileId . ".pdf";
        if (file_exists($fileLocation)) {
            header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
            header("Content-Type: pdf");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length:".filesize($fileLocation));
            header("Content-Disposition: attachment; filename=$fileId.pdf");
            readfile($fileLocation);
            die();
        } else {
            die("Ошибка: файл не найден");
        }
    }
}