<?php
include_once '../../../php/classes/User.php';

function logout(User $user) {
    setcookie('session_id', null, -1, '/');
    header('Location: /login');
}