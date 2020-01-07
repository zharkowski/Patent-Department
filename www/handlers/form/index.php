<?php
include_once 'login.php';
include_once 'logout.php';
//include_once 'edit_order_metadata.php';
//include_once 'create_order_claim.php';
//include_once 'set_order_status.php';
//include_once 'create_order_set.php';
//include_once 'choose_order_set.php';

$methodCallbacks = array(
    'login' => 'login',
    'logout' => 'logout',
//    'edit_order_metadata' => 'edit_order_metadata',
//    'create_order_claim' => 'create_order_claim',
//    'set_order_status' => 'set_order_status',
//    'create_order_set' => 'create_order_set',
//    'choose_order_set' => 'choose_order_set'
);
$formMethod = $_GET['method'];
// создаём нового юзера и авторизируем его через сессию, если есть такая возможность
$user = new User();
if (isset($_COOKIE['session_id'])) {
    $user->authorizeBySessionId($_COOKIE['session_id']);
}
if (!isset($methodCallbacks[$formMethod])) {
    die('Данный метод отсутвует');
}
if (!$user->checkRights('form', $formMethod)) {
    die('Запрещено');
}
call_user_func($methodCallbacks[$formMethod], $user);