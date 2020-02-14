<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
include_once '../../../php/classes/User.php';
include_once '../../../php/classes/Patent.php';

// Принимает три POST параметра, id патена, название патента и файлы патента.
// При запросе сначала проверяет, что юзер это админ (group=1) или владелец данного ордера (у чела должна быть роль owner в этом ордере), а ешё проверяет, что ордера сейчас status=editing Если всё норм, то:
// 1) Изменяет в таблице patents название патента
// 2) Добавляет в patent_logs ещё один лог с тем же статусом editing
// 3) Редиректит на ту же страницу патента
function edit_patent_metadata(User $user)
{
    $patentId = $_POST['patent_id'];
    $patentTitle = $_POST['patent_title'];

    // загружаем нужный ордер, щапрашиваем его роли
    $patent = new Patent();
    $patent->loadSelfFromId($patentId);

    if (!$patent->getId()) {
        die('Ошибка. Заявка с таким идентификатором не найдена');
    }

    if ((strlen($patentTitle) > 128)) {
        header("Location: /pages/patent/?id=" . $patent->getId() . "&error_text=" . urldecode('Ошибка. Слишком длинное название.'));
        return;
    }

    $patent->loadRoles();
    $patentRoles = $patent->getRoles();

    $canUserEditMetadata = $patent->canUserEditMetadata($user);
    if ($canUserEditMetadata['status'] != 'ok') {
        die($canUserEditMetadata['error_text']);
    }

    if (isset($_FILES['patentRequestFile']) && basename($_FILES['patentRequestFile']['name'] != "")) {
        $requestFileName = basename($_FILES['patentRequestFile']['name']);
        if (strpos($requestFileName, '.pdf') != (strlen($requestFileName) - strlen('.pdf'))) {
            header("Location: /pages/patent/?id=" . $patent->getId() . "&error_text=" . urldecode('Ошибка. Файл заявки должен быть в формате pdf.'));
            return;
        }
    }

    if (isset($_FILES['$descriptionFileName']) && basename($_FILES['patentRequestFile']['name'] != "")) {
        $descriptionFileName = basename($_FILES['patentDescriptionFile']['name']);
        if (strpos($descriptionFileName, '.pdf') != (strlen($descriptionFileName) - strlen('.pdf'))) {
            header("Location: /pages/patent/?id=" . $patent->getId() . "&error_text=" . urldecode('Ошибка. Файл описания изобретения должен быть в формате pdf.'));
            return;
        }
    }

    $patent->setMetaData($patentTitle);
    $patent->saveFiles();

    header('Location: /pages/patent/?id=' . $patent->getId());
}