<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
include_once '../../../php/classes/User.php';
include_once '../../../php/classes/Patent.php';
include_once '../../../php/functions/checkPageAccessRight.php';
checkPageAccessRight('patent');

$user = new User();
if (isset($_COOKIE['session_id']))
{
    $user->authorizeBySessionId($_COOKIE['session_id']);
}

$patent = new Patent();
if (isset($_GET['id']))
{
    $patent->loadSelfFromId($_GET['id']);
} else {
    header("Location: /");
    return;
}

if (!($user->isOwnerOf($_GET['id']) || $user->isCheckerOf($_GET['id']) || ($user->getGroupId() == 2) || ($user->getGroupId() == 3 && !$patent->hasChecker())))
{
    header("Location: /");
    return;
}

$patentTitle = $patent->getMetadata();
$patentTitle = ($patentTitle == null) ? '' : $patentTitle;
$patent->loadRoles();
$patentRoles = $patent->getRoles();

if (isset($_GET['error_text'])) {
    $error_text = $_GET['error_text'];
}

$skipLock = false;
if (isset($_GET['skip_lock'])) {
    $skipLock = true;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../src/css/style.css">
    <title>Создание заявки</title>
</head>
<body>
<?php include_once(dirname(__DIR__).'/__components/header.php'); ?>

<div class="patents-wrapper">
    <?php if (isset($error_text)) { ?>
        <div class="error-wrapper">
            <div class="error-text">
                <?php echo urldecode($error_text); ?>
            </div>
            <button class="error-close-btn">X</button>
        </div>
    <?php } ?>
    <a class="patent-back-button" href="/pages/home">Назад</a>

    <div class="patent-block patent-metadata">
<!--        --><?php //if ($patent->canUserEditMetadata($user)['status'] == 'ok') { ?>
<!--            <button class="edit-btn patent-metadata patent-metadata__edit-btn">Редактировать</button>-->
<!--        --><?php //} ?>

        <div class="patent-metadata patent-metadata__view">
            <h2 class="patent-metadata patent-metadata__title"><?php echo (($patent !== '') ? $patentTitle : 'Без названия')?></h2>
            <div class="patent-data patent-status"><span class="patent-statement">Статус:</span> <?php echo formatStatus($patent->getStatus())[1]; ?></div>
            <div class="patent-data patent-metadata patent-metadata__request-label"><span class="patent-statement">Заявление:</span> <span class="patent-data__link"><?php echo $patent->loadRequestsFiles()?></span></div>
            <div class="patent-data patent-metadata patent-metadata__description-label"><span class="patent-statement">Описание изобретения:</span> <span class="patent-data__link"><?php echo $patent->loadDescriptionFiles()?></span></div>
        </div>

        <div class="patent-metadata__form <?php if (!(($patent->getStatus() == 'editing' || $patent->getStatus() == 'correcting') && (($user->getGroupId() == 2) || (in_array($user->getId(), $patent->getRoles()['owner']))))) {echo 'visually-hidden';} ?>">
            <form class="metadata-form" action="/handlers/form/?method=edit_patent_metadata" method="post" enctype="multipart/form-data">
                <input type="hidden" name="patent_id" value="<?php echo $patent->getId() ?>" />
                <label class="metadata-input patent-metadata__form__title-label">
                    <span>Название патента:</span>
                    <input name="patent_title" class="patent-metadata__form__title-input"  <?php if (!isset($_GET['skip_check'])) { echo 'maxlength="128"';} ?> value="<?php echo $patentTitle ?>" />
                    <br>
                </label>
                <label class="metadata-input">
                    <span>Прикрепите файл заявления в формате pdf:</span>
                    <br>
                    <input type="file" name="patentRequestFile">
                    <br>
                </label>
                <label class="metadata-input">
                    <span>Прикрепите файл с описанием изобретения в формате pdf:</span>
                    <br>
                    <input type="file" name="patentDescriptionFile">
                    <br>
                </label>

                <input class="form__submit patent-metadata__form__submit" type="submit" value="Изменить"/>
                <button class="form__cancel patent-metadata__form__cancel">Отмена</button>
            </form>
        </div>
    </div>

    <ul class="patent-page__status-controls status-control__list">
        <?php if (checkPermissionsForPatentStatus($user, $patent, 'checking_wait', 'default')['access'] == true) { ?>
            <li class="status-control__item">
                <form method="POST" action="/handlers/form/?method=set_patent_status" class="status-controls__action-form">
                    <input type="hidden" name="patent_id" value="<?php echo $patent->getId() ?>" />
                    <input type="hidden" name="new_patent_status" value="checking_wait" />
                    <input type="hidden" name="new_patent_status_reason" value="default" />
                    <button class="status-controls__action-btn action-btn__approve" type="submit">Отправить патент на проверку</button>
                </form>
            </li>
        <?php } ?>
        <?php if (checkPermissionsForPatentStatus($user, $patent, 'request_checking', 'default')['access'] == true) { ?>
            <li class="status-control__item">
                <form method="POST" action="/handlers/form/?method=set_patent_status" class="status-controls__action-form">
                    <input type="hidden" name="patent_id" value="<?php echo ($patent->getId()) ?>" />
                    <input type="hidden" name="new_patent_status" value="request_checking" />
                    <input type="hidden" name="new_patent_status_reason" value="default" />
                    <button class="status-controls__action-btn action-btn__take" type="submit">Взять патент на проверку</button>
                </form>
            </li>
        <?php } ?>
        <?php if (checkPermissionsForPatentStatus($user, $patent, 'plagiarism_checking', 'default')['access'] == true) { ?>
            <li class="status-control__item">
                <form method="POST" action="/handlers/form/?method=set_patent_status" class="status-controls__action-form">
                    <input type="hidden" name="patent_id" value="<?php echo ($patent->getId()) ?>" />
                    <input type="hidden" name="new_patent_status" value="plagiarism_checking" />
                    <input type="hidden" name="new_patent_status_reason" value="default" />
                    <button class="status-controls__action-btn action-btn__take" type="submit">Одобрить заявление</button>
                </form>
            </li>
        <?php } ?>
        <?php if (checkPermissionsForPatentStatus($user, $patent, 'description_checking', 'default')['access'] == true) { ?>
            <li class="status-control__item"><form method="POST" action="/handlers/form/?method=set_patent_status" class="status-controls__action-form">
                    <input type="hidden" name="patent_id" value="<?php echo ($patent->getId()) ?>" />
                    <input type="hidden" name="new_patent_status" value="description_checking" />
                    <input type="hidden" name="new_patent_status_reason" value="default" />
                    <button class="status-controls__action-btn action-btn__take" type="submit">Плагиат не выявлен</button>
                </form>
            </li>
        <?php } ?>
        <?php if (checkPermissionsForPatentStatus($user, $patent, 'closed', 'default')['access'] == true) { ?>
            <li class="status-control__item">
                <form method="POST" action="/handlers/form/?method=set_patent_status<?php if ($skipLock) {echo "&skip_lock=1";} ?>" class="status-controls__action-form">
                    <input type="hidden" name="patent_id" value="<?php echo ($patent->getId()) ?>" />
                    <input type="hidden" name="new_patent_status" value="closed" />
                    <input type="hidden" name="new_patent_status_reason" value="default" />
                    <button class="status-controls__action-btn action-btn__take" type="submit">Одобрить оформление патента</button>
                </form>
            </li>
        <?php } ?>
        <?php if (checkPermissionsForPatentStatus($user, $patent, 'editing', 'default')['access'] == true) { ?>
            <li class="status-control__item">
                <form method="POST" action="/handlers/form/?method=set_patent_status" class="status-controls__action-form">
                    <input type="hidden" name="patent_id" value="<?php echo ($patent->getId()) ?>" />
                    <input type="hidden" name="new_patent_status" value="editing" />
                    <input type="hidden" name="new_patent_status_reason" value="default" />
                    <button class="status-controls__action-btn action-btn__take" type="submit">Редактировать</button>
                </form>
            </li>
        <?php } ?>
        <?php if (checkPermissionsForPatentStatus($user, $patent, 'correcting', 'default')['access'] == true) { ?>
            <li class="status-control__item">
                <form method="POST" action="/handlers/form/?method=set_patent_status" class="status-controls__action-form">
                    <input type="hidden" name="patent_id" value="<?php echo ($patent->getId()) ?>" />
                    <input type="hidden" name="new_patent_status" value="correcting" />
                    <input type="hidden" name="new_patent_status_reason" value="default" />
                    <button class="status-controls__action-btn action-btn__take" type="submit">Редактировать</button>
                </form>
            </li>
        <?php } ?>
        <?php if (checkPermissionsForPatentStatus($user, $patent, 'correcting', 'fail')['access'] == true) { ?>
            <li class="status-control__item">
                <form method="POST" action="/handlers/form/?method=set_patent_status" class="status-controls__action-form">
                    <input type="hidden" name="patent_id" value="<?php echo ($patent->getId()) ?>" />
                    <input type="hidden" name="new_patent_status" value="correcting" />
                    <input type="hidden" name="new_patent_status_reason" value="fail" />
                    <button class="status-controls__action-btn action-btn__take" type="submit">Уточнить данные</button>
                </form>
            </li>
        <?php } ?>
        <?php if (checkPermissionsForPatentStatus($user, $patent, 'request_checking', 'fail')['access'] == true) { ?>
            <li class="status-control__item">
                <form method="POST" action="/handlers/form/?method=set_patent_status" class="status-controls__action-form">
                    <input type="hidden" name="patent_id" value="<?php echo ($patent->getId()) ?>" />
                    <input type="hidden" name="new_patent_status" value="request_checking" />
                    <input type="hidden" name="new_patent_status_reason" value="fail" />
                    <button class="status-controls__action-btn action-btn__take" type="submit">Отправить на перепроверку</button>
                </form>
            </li>
        <?php } ?>
        <?php if (checkPermissionsForPatentStatus($user, $patent, 'denied', 'default')['access'] == true) { ?>
            <li class="status-control__item">
                <form method="POST" action="/handlers/form/?method=set_patent_status" class="status-controls__action-form">
                    <input type="hidden" name="patent_id" value="<?php echo ($patent->getId()) ?>" />
                    <input type="hidden" name="new_patent_status" value="denied" />
                    <input type="hidden" name="new_patent_status_reason" value="default" />
                    <button class="status-controls__action-btn action-btn__take" type="submit">Отказать в оформлении патента</button>
                </form>
            </li>
        <?php } ?>
        <?php if (checkPermissionsForPatentStatus($user, $patent, 'canceled','default')['access'] == true) { ?>
            <li class="status-control__item">
                <form method="POST" action="/handlers/form/?method=set_patent_status<?php if ($skipLock) {echo "&skip_lock=1";} ?>"" class="status-controls__action-form">
                    <input type="hidden" name="patent_id" value="<?php echo ($patent->getId()) ?>" />
                    <input type="hidden" name="new_patent_status" value="canceled" />
                    <input type="hidden" name="new_patent_status_reason" value="default" />
                    <button class="status-controls__action-btn action-btn__take" type="submit">Отменить оформление патента</button>
                </form>
            </li>
        <?php } ?>
        <?php if (checkPermissionsForPatentStatus($user, $patent, 'plagiarism', 'default')['access'] == true) { ?>
            <li class="status-control__item">
                <form method="POST" action="/handlers/form/?method=set_patent_status" class="status-controls__action-form">
                    <input type="hidden" name="patent_id" value="<?php echo ($patent->getId()) ?>" />
                    <input type="hidden" name="new_patent_status" value="plagiarism" />
                    <input type="hidden" name="new_patent_status_reason" value="default" />
                    <button class="status-controls__action-btn action-btn__take" type="submit">Выявлен плагиат</button>
                </form>
            </li>
        <?php } ?>
    </ul>
</div>
<script src="../../src/js/main.js"></script>
<script src="../../src/js/patent.js"></script>
</body>
</html>
