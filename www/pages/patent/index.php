<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
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

if (!($user->isOwnerOrChecker($_GET['id']) || ($user->getGroupId()) == 2))
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

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Создание заявки</title>
</head>
<body>
<?php include_once(dirname(__DIR__).'/__components/header.php'); ?>

<h1>Патент номер <?php echo($_GET['id'])?></h1>

<div class="patent-page">
    <?php if (isset($error_text)) { ?>
        <div class="error-wrapper">
            <div class="error-text">
                <?php echo urldecode($error_text); ?>
            </div>
            <button class="error-close-btn">X</button>
        </div>
    <?php } ?>

    <div class="patent-page__status-controls">
        <?php if (checkPermissionsForOrderStatus($user, $patent, 'checking_wait', 'default')['access'] == true) { ?>
            <form method="POST" action="/handlers/form/?method=set_patent_status" class="status-controls__action-form">
                <input type="hidden" name="order_id" value="<?php echo $patent->getId() ?>" />
                <input type="hidden" name="new_order_status" value="selection_wait" />
                <input type="hidden" name="new_order_status_reason" value="default" />
                <button class="status-controls__action-btn action-btn__approve">Отправить заказ на проверку</button>
            </form>
        <?php } ?>
    </div>
    <?php
    echo $patent->hasChecker();
    ?>
</div>
</body>
</html>
