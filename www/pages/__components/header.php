<?php
    include_once '../../../php/classes/User.php';
    include_once '../../../php/functions/checkPageAccessRight.php';
    $user = new User();
    if (isset($_COOKIE['session_id']))
    {
        $user->authorizeBySessionId($_COOKIE['session_id']);
    }
?>
<div class="header">
    <h1 class="header--title">Патентный отдел</h1>
    <ul class="main-nav">
        <li class="main-nav__element">
            <a class="header__button header__home-link" href="/pages/home/">Домой</a>
        </li>
    <!--    --><?php //if ($user->checkRights('page', 'my-patents')) {?>
    <!--        <a class="header__button header__catalogue-link" href="/pages/my-patents">Мои патенты</a>-->
    <!--    --><?php //} ?>

        <?php if ($user->checkRights('page', 'new-patent')) {?>
        <li class="main-nav__element">
            <a class="header__button header__new-order-link" href="/handlers/form/?method=new_patent">Создать заявку на патент</a>
        </li>
        <?php } ?>
    <!--    --><?php //if ($user->checkRights('page', 'patent-requests')) {?>
    <!--        <a class="header__button header__catalogue-link" href="/pages/patent-requests">Заявки на патенты</a>-->
    <!--    --><?php //} ?>
    <!--    --><?php //if ($user->checkRights('page', 'patent-check')) {?>
    <!--        <a class="header__button header__catalogue-link" href="/pages/patent-check">Рассмотрение заявки</a>-->
    <!--    --><?php //} ?>
        <li class="main-nav__element">
            <a class="header__button header__new-order-link" href="#">Регламент подачи документов</a>
        </li>
    </ul>
    <div class="profile-box">
        <div class="profile-box__login"><?php echo $user->getUserName() ?></div>
        <div class="profile-box__role"><?php echo $user->getGroupName() ?></div>
        <a class="profile-box__logout" href="/handlers/form/?method=logout">Выйти</a>
    </div>
</div>

<?php
function formatStatus($status) {
    $status_class = null;
    $status_text = null;
    if ($status == 'editing') {
        $status_class = 'patent-item__status-editing';
        $status_text = 'Редактирование';
    } else if ($status == 'checking_wait') {
        $status_class = 'patent-item__status-checking_wait';
        $status_text = 'Ожидает проверки';
    } else if ($status == 'request_checking') {
        $status_class = 'order-item__status-request_checking';
        $status_text = 'Проверка заявления';
    } else if ($status == 'plagiarism_checking') {
        $status_class = 'order-item__status-plagiarism_checking';
        $status_text = 'Проверка на плагиат';
    } else if ($status == 'description_checking') {
        $status_class = 'order-item__status-description_checking';
        $status_text = 'Проверка на значимость';
    } else if ($status == 'closed') {
        $status_class = 'order-item__status-closed';
        $status_text = 'Выдано авторское свидетельство';
    } else if ($status == 'canceled') {
        $status_class = 'order-item__status-canceled';
        $status_text = 'Отменён';
    } else if ($status == 'denied') {
        $status_class = 'order-item__status-denied';
        $status_text = 'Отказано';
    } else if ($status == 'plagiarism') {
        $status_class = 'order-item__status-plagiarism';
        $status_text = 'Плагиат';
    } else if ($status == 'correcting') {
        $status_class = 'order-item__status-correcting';
        $status_text = 'Необходимо отредактировать данные';
    }

    return array($status_class, $status_text);
}
?>