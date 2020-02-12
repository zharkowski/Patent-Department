<?php

function checkPageAccessRight ($pageName)
{
    $user = new User();
    if (isset($_COOKIE['session_id']))
    {
        $user->authorizeBySessionId($_COOKIE['session_id']);
    }

    if (!$user->isAuthorized() || !$user->checkRights('page', $pageName)) {
        header("Location: /");
        return;
    }
}