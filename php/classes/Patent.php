<?php
include_once 'Patent.php';
include_once 'DBConnection.php';
use DB\DBConnection as DBConnection;

class Patent
{
    private $id = null;
    private $userId = null;
    private $title = null;
    private $status = null;
    private $roles = array();
    private $filesId = null;

    private $mysqli = null;
    public function __construct()
    {
        $this->mysqli = DBConnection::getInstance()->getConnection();
    }

    // загружает базовую информацию - id патента, его название и его текущий статус
    public function loadSelfFromId($patentId) {
        $query = "SELECT p.id, p.title, pl.status
              FROM patents AS p
              INNER JOIN patent_logs AS pl
                  ON (p.id = pl.patent_id AND p.id = ?)";
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param("i", $patentId);
            $stmt->execute();
            $stmt->bind_result($patentId,$patentTitle, $patentStatus);
            $stmt->fetch();
            $stmt->close();
        }
        if (isset($patentId) && isset($patentStatus)) {
            $this->id = $patentId;
            $this->status = $patentStatus;
        }
        if (isset($patentTitle)) {
            $this->title = $patentTitle;
        }
    }

    public function loadRoles() {
        if (!($patentId = $this->id)) {
            die('Ошибка! У патента не найден id');
        }
        $this->roles = array();

        $query = "SELECT user_id, role FROM patent_department_db.patent_roles WHERE patent_id = ?";
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param("i", $patentId);
            $stmt->execute();
            $stmt_result = $stmt->get_result();
            if ($stmt_result->num_rows > 0) {
                while($row_data = $stmt_result->fetch_assoc()) {
                    echo $row_data['user_id'];
                    if (isset($this->roles[$row_data['role']])) {
                        $this->roles[$row_data['role']][] = $row_data['user_id'];
                    } else {
                        $this->roles[$row_data['role']] = array($row_data['user_id']);
                    }
                }
            }
            $stmt->close();
        }
    }

    public function getRoles() {
        return $this->roles;
    }

    public function getId() {
        return $this->id;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getFilesId() {
        return $this->filesId;
    }

    public function createNewPatent() {

    }

    public function setMetaData($title) {
        $query = "UPDATE patent_department_db.patents SET title = ? WHERE id = ?";
        $patentId = $this->id;

        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param("si", $title, $patentId);
            $stmt->execute();
            $stmt->close();
        }
    }

    public function getMetadata() {
        if (!($patentId = $this->id)) {
            die('Ошибка! У патента не найден id');
        }

        $patentTitle = null;
        $query = "SELECT title FROM patents WHERE id = ?";
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param("i", $patentId);
            $stmt->execute();
            $stmt->bind_result($patentTitle);
            $stmt->fetch();
            $stmt->close();
        }

        return $patentTitle;
    }

    public function createNew($owner) {
        $this->id = $this->insertNewPatent($owner);
        $this->addRole($owner, 'owner');
        $this->addLog($owner, 'editing', false);
    }

    private function insertNewPatent(User $user) {
        $newPatentId = null;
        $query = "INSERT INTO patent_department_db.patents (user_id, init_date) VALUES (?, NOW())";
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param('i',$user->getId());
            $stmt->execute();
            $stmt->close();
            $newPatentId = $this->mysqli->insert_id;
        }

        return $newPatentId;
    }

    public function addRole(User $user, $role) {
        $query = "INSERT INTO patent_department_db.patent_roles (patent_id, user_id, role) VALUES (?, ?, ?)";
        $userId = $user->getId();
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param("iis", $this->id, $userId, $role);
            $stmt->execute();
            $stmt->close();
        }
    }

    public function addLog(User $user, $status, $skipLock) {
        $userId = $user->getId();

        // начинаем транзакцию
        $this->mysqli->begin_transaction();

        echo 'look';

        // делаем LOCK на нужном нам заказе
        if ($skipLock != true) {
            $query = "SELECT * FROM patents AS p WHERE p.id = ? FOR UPDATE;";
            if ($stmt = $this->mysqli->prepare($query)) {
                $stmt->bind_param("i", $this->id);
                $stmt->execute();
                $stmt->close();
            }
        }

        if ($status == "closed") {
            // Здесь предполгагается код, который будет собирать патент, сейчас заглушка (15 сек ожидания)
            sleep(15);
        }

        // добавляем непосредственно патент
        $query = "INSERT INTO patent_department_db.patent_logs (patent_id, user_id, status, timestamp) VALUES (?, ?, ?, NOW())";
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param("iis", $this->id, $userId, $status);
            $stmt->execute();
            echo $this->mysqli->error . ' // ' . $this->mysqli->errno;
            if ($this->mysqli->error == "Not able to add records after final record added") {
                return array(
                    'success' => false,
                    'error_text' => 'Больше нет возможности изменить статус патента'
                );
            }
            $stmt->close();
        }

        // заканчиваем транзакцию
        $this->mysqli->commit();

        return array(
            'success' => true
        );
    }

    public function canUserEditMetadata(User $user) {
        // редактировать мета-данные запроса могут либо админы, либо владельцы этих патентов
        if (!(($user->getGroupId() == 1) || in_array($user->getId(), $this->getRoles()['owner']))) {
            return array(
                'status' => 'fail',
                'error_text' => 'Ошибка. У текущего пользователя нет прав для редактирования мета-данных патента'
            );
        }

        // проверяем, что сейчас статус editing
        if ($this->getStatus() != 'editing') {
            return array(
                'status' => 'fail',
                'error_text' => 'Ошибка. Изменять мета-данные запроса можно только во время статуса editing'
            );
        }

        return array(
            'status' => 'ok'
        );
    }

    public function hasChecker() {
        $roles = array();
        $patentId = $this->getId();
        $query = "SELECT role FROM patent_department_db.patent_roles WHERE patent_id = ?";
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param("i",$patentId);
            $stmt->execute();
            $stmt_result = $stmt->get_result();
            if ($stmt_result->num_rows > 0) {
                while ($row_data = $stmt_result->fetch_assoc()['role']) {
                    array_push($roles, $row_data);
                }
            }
            $stmt->close();
        }
        return in_array('checker', $roles);
    }
}

function getPatentsForUser(User $user) {
    $patents = array();
    $mysqli = DBConnection::getInstance()->getConnection();
    $stmt = null;

    // Администратору выводым вообще все существующие патенты
    if ($user->getGroupId() == 2) {
        $query = "SELECT p.id, p.title, pl.status, pl.timestamp
            FROM patent_department_db.patents AS p
                     INNER JOIN patent_logs AS pl
                                ON (pl.patent_id = p.id)
            
            ORDER BY pl.id DESC";
        $stmt = $mysqli->prepare($query);
    }

    // Сотрудникам выводм патенты, которые сейчас нуждаются проверке и патенты, в проверке которых эти сотрудники уже участвуют
    if ($user->getGroupId() == 3) {
        $query = "SELECT DISTINCT p.id, p.title, pl.status, pl.timestamp, pl.id AS status_id
              FROM patent_department_db.patents AS p
                       INNER JOIN patent_logs AS pl
                                  ON (pl.patent_id = p.id)
                       INNER JOIN patent_roles AS pr
                                  ON (((pr.role = 'checker' AND pr.user_id = ?) OR (pl.status = 'checking_wait')) AND p.id = pr.patent_id)
          
              ORDER BY pl.id DESC";
        if ($stmt = $mysqli->prepare($query)) {
            $userId = $user->getId();
            $stmt->bind_param("i", $userId);
        }
    }

    // Обычным пользователям выводим тольке те патенты, у которых они значаится как owner
    if ($user->getGroupId() == 4) {
        $query = "SELECT DISTINCT p.id, p.title, pl.status, pl.timestamp, pl.id AS status_id
              FROM patent_department_db.patents AS p
                  INNER JOIN patent_logs AS pl
                                  ON (pl.patent_id = p.id)
                  INNER JOIN patent_roles AS pr
                            ON (pr.role = 'owner' AND pr.user_id = ? AND p.id = pr.patent_id)

              ORDER BY pl.id DESC";
        if ($stmt = $mysqli->prepare($query)) {
            $userId = $user->getId();
            $stmt->bind_param("i", $userId);
        }
    }

    // Далее независимо от роли добавляем все данные в массив и возвращаем его как результат
    if ($stmt) {
        $stmt->execute();
        $stmt_result = $stmt->get_result();
        if ($stmt_result->num_rows > 0) {
            while($row_data = $stmt_result->fetch_assoc()) {
                $patents[] = $row_data;
            }
        }
        $stmt->close();
    }

    return $patents;
}

/**
 * Проверяет, может ли юзер $user изменить статус у ордера $order на новый статус $new_order_status
 * @param User $user
 * @param Patent $patent
 * @param string $newPatentStatus
 * @param string changeType
 * @return array canEditPatentStatus
 */
function checkPermissionsForOrderStatus(User $user, Patent $patent, $newPatentStatus, $newPatentStatusReason) {
    $patent->loadRoles();
    $patentRoles = $patent->getRoles();

    // checking_wait (ожидание проверки) - сюда кидает владелец ордера
    if ($newPatentStatus == 'checking_wait') {
        // отправлять далее может либо админ, либо владелец заявки
        if (!(($user->getGroupId() == 2) || in_array($user->getId(), $patentRoles['owner']))) {
            return array(
                'access' => false,
                'error_text' => 'У текущего пользователя нет прав для задания данного статуса'
            );
        }

        // проверяем, что сейчас статус editing
        if ($patent->getStatus() != 'editing') {
            return array(
                'access' => false,
                'error_text' => 'Статус checking_wait можно получить только из editing'
            );
        }

        $title = $patent->getMetadata();
        if (!isset($title) || !strlen($title) || !$patent->getFilesId() == null) { //НАДО ДОБАВИТЬ ПРОВЕРКУ НА НАЛИЧИЕ ФАЙЛОВ
            return array(
                'access' => false,
                'error_text' => 'Нельзя отправить заказ в ожидание проверки, если у заказа нет названия'
            );
        }

        return array(
            'access' => true
        );
    }

    // request_checking (проверка заявления) - появляется, когда заказ выбирается кем-то из сотрудников (тут надо ещё роль выставить)
    if (($newPatentStatus == 'request_checking') && ($newPatentStatusReason == 'default')) {
        // перейти у проверке заявления может либо тот сотрудник, который собственно выбрал, либо админ
        if (!(($user->getGroupId() == 2) || ($user->getGroupId() == 3))) {
            return array(
                'access' => false,
                'error_text' => 'У текущего пользователя нет прав для задания данного статуса'
            );
        }

        // проверяем, что сейчас статус checking_wait
        if ($patent->getStatus() != 'checking_wait') {
            return array(
                'access' => false,
                'error_text' => 'Статус request_checking можно получить только из checking_wait'
            );
        }

        return array(
            'access' => true
        );
    }

    //plagiarism__checking (проверка на плагиат) - появляется после прохождения проверки заявления
    if (($newPatentStatus == 'plagiarism_checking') && ($newPatentStatusReason == 'default')) {
        // перейти у проверке на  плагиат может либо проверяющий сотрудник, либо админ
        if (!(($user->getGroupId() == 2) || (in_array($user->getId(), $patentRoles['checker'])))) {
            return array(
                'access' => false,
                'error_text' => 'У текущего пользователя нет прав для задания данного статуса'
            );
        }

        // проверяем, что сейчас статус request_checking
        if ($patent->getStatus() != 'request_checking') {
            return array(
                'access' => false,
                'error_text' => 'Статус plagiarism_checking можно получить только из request_checking'
            );
        }

        return array(
            'access' => true
        );
    }

    // description_checking (проверка описания патента) - появляется, после прохождения проверки на плагиат
    if (($newPatentStatus == 'description_checking') && ($newPatentStatusReason == 'default')) {
        // перекинуть в выборку может либо проверяющий сотрудник, либо админ
        if (!(($user->getGroupId() == 2) || (in_array($user->getId(), $patentRoles['checker'])))) {
            return array(
                'access' => false,
                'error_text' => 'У текущего пользователя нет прав для задания данного статуса'
            );
        }

        // проверяем, что сейчас статус plagiarism__checking
        if ($patent->getStatus() != 'plagiarism_checking') {
            return array(
                'access' => false,
                'error_text' => 'Статус request_checking можно получить только из plagiarism_checking'
            );
        }

        return array(
            'access' => true
        );
    }

    // editing (редактировние) - может отправить сотрудник если патент требует доработок
    if (($newPatentStatus == 'editing') && ($newPatentStatusReason == 'fail')) {
        // отправлять может либо админ, либо проверяющий
        if (!(($user->getGroupId() == 2) || in_array($user->getId(), $patentRoles['checker']))) {
            return array(
                'access' => false,
                'error_text' => 'У текущего пользователя нет прав для задания данного статуса'
            );
        }

        // проверяем, что сейчас статус request_checking или description_checking
        if (!($patent->getStatus() == 'request_checking' || $patent->getStatus() == 'description_checking')) {
            return array(
                'access' => false,
                'error_text' => 'Статус editing можно получить только из request_checking или description_checking'
            );
        }

        return array(
            'access' => true
        );
    }

    // editing (редактировние) - может владелец если решит сделать доработки
    if (($newPatentStatus == 'editing') && ($newPatentStatusReason == 'fail')) {
        // отправлять может либо админ, либо владелец патента
        if (!(($user->getGroupId() == 2) || in_array($user->getId(), $patentRoles['owner']))) {
            return array(
                'access' => false,
                'error_text' => 'У текущего пользователя нет прав для задания данного статуса'
            );
        }

        // проверяем, что сейчас статус checking_wait
        if (!($patent->getStatus() == 'checking_wait')) {
            return array(
                'access' => false,
                'error_text' => 'Статус editing можно получить только из checking_wait'
            );
        }

        return array(
            'access' => true
        );
    }

    // closed (закрытие) - принятие патента
    if ($newPatentStatus == 'closed') {
        // дать разрешение на патент может или админ, или тот, что учасвтует проверке патента
        if (!(($user->getGroupId() == 2) || (isset($patentRoles['checker']) && in_array($user->getId(), $patentRoles['checker'])))) {
            return array(
                'access' => false,
                'error_text' => 'У текущего пользователя нет прав для задания данного статуса'
            );
        }

        // проверяем, что сейчас статус description_checking
        if ($patent->getStatus() != 'description_checking') {
            return array(
                'access' => false,
                'error_text' => 'Статус closed можно получить только из description_checking'
            );
        }

        return array(
            'access' => true
        );
    }

    // canceled (удаление патента навсегда) - удалить может владелец патента
    if ($newPatentStatus == 'canceled') {
        // удалить заявку может либо админ, либо владелец заявки
        if (!(($user->getGroupId() == 2) || in_array($user->getId(), $patentRoles['owner']))) {
            return array(
                'access' => false,
                'error_text' => 'У текущего пользователя нет прав для задания данного статуса'
            );
        }

        // можно отменить только незакрытый, неотменённый патент, а так же патенты, которые не уличили в плагиате
        if (($patent->getStatus() == 'canceled') || ($patent->getStatus() == 'closed') || ($patent->getStatus() == 'denied') || ($patent->getStatus() == 'plagiarism')) {
            return array(
                'access' => false,
                'error_text' => 'Статус canceled нельзя получить из canceled, denied, plagiarism или closed'
            );
        }

        return array(
            'access' => true
        );
    }

    //denied (отказано в оформлении патента) - проверяющий отказывает в оформлении патента в случае невыявления значимости изобретения
    if ($newPatentStatus == 'denied') {
        //отказать в оформлении патента может админ или проверяющий заявку
        if (!(($user->getGroupId() == 2) || in_array($user->getId(), $patentRoles['checker']))) {
            return array(
                'access' => false,
                'error_text' => 'У текущего пользователя нет прав для задания данного статуса'
            );
        }

        // проверяем, что сейчас статус description_checking
        if ($patent->getStatus() != 'description_checking') {
            return array(
                'access' => false,
                'error_text' => 'Статус denied можно получить только из description_checking'
            );
        }

        return array(
            'access' => true
        );
    }

    //plagiarism (плагиат) - ставится в случае уличения в плагиате (далее должна быть отправлена информация в ВАК)
    if ($newPatentStatus == 'plagiarism') {
        //уличить в плагиате может админ или проверяющий заявку
        if (!(($user->getGroupId() == 2) || in_array($user->getId(), $patentRoles['checker']))) {
            return array(
                'access' => false,
                'error_text' => 'У текущего пользователя нет прав для задания данного статуса'
            );
        }

        // проверяем, что сейчас статус checking
        if ($patent->getStatus() != 'plagiarism_checking') {
            return array(
                'access' => false,
                'error_text' => 'Статус plagiarism можно получить только из plagiarism_checking'
            );
        }

        return array(
            'access' => true
        );
    }

    /* дополнительные правила записываем сюда */

    die('Неизвестный статус патента');
}