<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

include_once '../../../php/classes/User.php';
include_once '../../../php/classes/Patent.php';

function saveFiles($patentFilesId) {
    if (isset($_FILES['patentRequestFile']))
    {
        $targetPath = '../../../files/requests/' . $patentFilesId . '.pdf';
        move_uploaded_file($_FILES['patentRequestFile']['tmp_name'], $targetPath);
    }
    if (isset($_FILES['patentDescriptionFile']))
    {
        $targetPath = '../../../files/descriptions/' . $patentFilesId . '.pdf';
        move_uploaded_file($_FILES['patentDescriptionFile']['tmp_name'], $targetPath);
    }
}

function new_patent(User $user) {

    $patent = new Patent();
    $patent->createNew($user);
    header("Location: /pages/patent/?id=" . $patent->getId());
}
