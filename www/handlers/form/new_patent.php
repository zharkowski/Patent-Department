<?php

include_once '../../../php/classes/User.php';
include_once '../../../php/classes/Patent.php';

function new_patent(User $user) {

    $patent = new Patent();
    $patent->createNew($user);
    header("Location: /pages/patent/?id=" . $patent->getId());
}
