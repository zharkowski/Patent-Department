<?php

include_once '../../../php/classes/User.php';
use DB\DBConnection as DBConnection;

function get_new_session (User $user, $requestBody) {
    $newSessionId = $user->createSessionId();

    setcookie("session_id", $newSessionId, time()+120, '/');
    return array(
        'status' => 'ok'
    );
}

