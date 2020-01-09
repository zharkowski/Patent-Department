<?php

include_once 'get_new_session.php';

$actionCallbacks = array(
    'get_new_session' => 'get_new_session'
);

$ajaxAction = $_GET['action'];

$user = new User();
if (isset($_COOKIE['session_id'])) {
    $user->authorizeBySessionId($_COOKIE['session_id']);
}
if (!isset($actionCallbacks[$ajaxAction])) {
    die('Данный метод отсутвует');
}
if (!$user->checkRights('ajax', $ajaxAction)) {
    die('Запрещено');
}

$requestBody = null;
if (isset($_REQUEST['data'])) {
    $requestBody = json_decode($_REQUEST['data'], true);
}
$result = call_user_func($actionCallbacks[$ajaxAction], $user, $requestBody);
echo json_encode($result);