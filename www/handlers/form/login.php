<?php

include_once '../../../php/classes/User.php';

function login(User $user)
{
    $login = $_POST['login'];
    $password = $_POST['password'];

    $user->authorizeByCredentials($login, $password);

    if (!$user->isAuthorized())
    {
        header('Location: /index.php/?isAuthorized=0');
        return;
    }

    $newSessionId = $user->createSessionId();
    setcookie("session_id", $newSessionId, time()+3600, '/');
    header('Location: /index.php');
}