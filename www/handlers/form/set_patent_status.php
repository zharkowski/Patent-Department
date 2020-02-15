<?php
include_once '../../../php/classes/Patent.php';
include_once '../../../php/classes/User.php';

/**
 * Экшн отвечает за переключения статусов
 * Принимается два параметра - id патента и новый статус, далее проверяем по правам, если всё ок - проставляем
 * @param User $user
 */
function set_patent_status(User $user) {
    // загружаем нужный патент, запрашиваем его роли
    $patentId = $_POST['patent_id'];
    $patent = new Patent();
    $patent->loadSelfFromId($patentId);

    $skipLock = false;
    if (isset($_GET['skip_lock'])) {
        $skipLock = true;
    }

    $newPatentStatus = $_POST['new_patent_status'];
    $newPatentStatusReason = $_POST['new_patent_status_reason'];
    $canEditPatent = checkPermissionsForPatentStatus($user, $patent, $newPatentStatus, $newPatentStatusReason);

    if ($canEditPatent['access'] == false) {
        header("Location: /pages/patent/?id=" . $patent->getId() . "&error_text=" . urldecode($canEditPatent['error_text']));
        return;
    }

    $result = $patent->addLog($user, $newPatentStatus, $skipLock);
    if ($result['success'] !== true) {
        header("Location: /pages/patent/?id=" . $patent->getId() . "&error_text=" . urldecode($result['error_text']));
        return;
    }

    if ($newPatentStatus == 'request_checking' && $user->getGroupId() == 3) {
        $patent->addRole($user, 'checker');
    }
    /* дополнительные правила записываем сюда */

    header("Location: /pages/patent/?id=" . $patent->getId());
}