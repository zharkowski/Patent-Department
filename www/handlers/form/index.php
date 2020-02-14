<?php
include_once 'login.php';
include_once 'logout.php';
include_once 'new_patent.php';
include_once 'set_patent_status.php';
include_once 'edit_patent_metadata.php';
include_once 'get_request_file.php';
include_once 'get_description_file.php';

$methodCallbacks = array(
    'login' => 'login',
    'logout' => 'logout',
    'new_patent' => 'new_patent',
    'set_patent_status' => 'set_patent_status',
    'edit_patent_metadata' => 'edit_patent_metadata',
    'get_request_file' => 'get_request_file',
    'get_description_file' => 'get_description_file'
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