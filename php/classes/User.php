<?php
include_once 'DBConnection.php';
use DB\DBConnection as DBConnection;

class User
{
    private $id = null;
    private $login;
    private $name;
    private $groupId = 1; //Изначально аноним
    private $groupName;

    private $mysqli = null;

    public function __construct() {
        $this->mysqli = DBConnection::getInstance()->getConnection();
    }

    private function getUserIdByCredentials($login, $password) {
        $query = "SELECT users.id, users.group_id, users.password_hash, users.name, groups.name 
          FROM patent_department_db.users 
          INNER JOIN patent_department_db.groups 
            ON (users.group_id = groups.id) 
          WHERE login = ?";

        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param("s", $login);
            $stmt->execute();
            $stmt->bind_result($userId, $userGroupId, $userPasswordHash, $userName, $userGroupName);
            $stmt->fetch();
            $stmt->close();
        }

        if (password_verify($password, $userPasswordHash)) {
            return array($userId, $userGroupId, $userName, $userGroupName);
        }

        return array(null, 1, null, null);
    }

    private function getUserIdBySessionId($sessionId) {
        $query = "SELECT users.id, users.group_id, users.login, users.name, groups.name 
          FROM patent_department_db.user_sessions 
          INNER JOIN patent_department_db.users 
            ON (users.id = user_sessions.user_id) 
          INNER JOIN patent_department_db.groups 
            ON (groups.id = users.group_id) 
          WHERE user_sessions.id = ? AND user_sessions.valid_until >= NOW()";

        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param("s", $sessionId);
            $stmt->execute();
            $stmt->bind_result($userId, $userGroupId, $userLogin, $userName, $userGroupName);
            $stmt->fetch();
            $stmt->close();
        }

        return array($userId, $userGroupId, $userLogin, $userName, $userGroupName);
    }

    private function insertSessionId($userId) {
        $query = "INSERT INTO patent_department_db.user_sessions (id, user_id, valid_until) 
          VALUES (?, ?, NOW() + INTERVAL 2 MINUTE )";

        if ($stmt = $this->mysqli->prepare($query)) {
            do {
                $newSessionId = substr(str_shuffle(MD5(microtime())), 0, 32);
                $stmt->bind_param("si", $newSessionId, $userId);
                $stmt->execute();
            } while ($this->mysqli->affected_rows == 0);
            $stmt->close();
        }

        return $newSessionId;
    }

    public function createSessionId() {
        return $this->insertSessionId($this->id);
    }

    public function authorizeBySessionId($sessionId) {
        list($userId, $userGroupId, $userLogin, $userName, $userGroupName) = $this->getUserIdBySessionId($sessionId);
        if ($userId) {
            $this->id = $userId;
            $this->groupId = $userGroupId;
            $this->login = $userLogin;
            $this->groupName = $userGroupName;
            $this->name = $userName;
        }
    }

    public function authorizeByCredentials($login, $password) {
        list($userId, $userGroupId, $userName, $userGroupName) = $this->getUserIdByCredentials($login, $password);
        if ($userId) {
            $this->id = $userId;
            $this->groupId = $userGroupId;
            $this->login = $login;
            $this->groupName = $userGroupName;
            $this->name = $userName;
        }
    }

    public function isAuthorized() {
        return $this->id != null;
    }

    public function checkRights($type, $name) {
        $userGroupId = $this->groupId;

        $query = "SELECT id FROM patent_department_db.group_rights WHERE group_id = ? AND type = ? AND name = ?";
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param("iss", $userGroupId, $type, $name);
            $stmt->execute();
            $stmt->bind_result($groupRigthId);
            $stmt->fetch();
            $stmt->close();
        }

        return isset($groupRigthId);
    }

    public function getId() {
        return $this->id;
    }

    public function getUserName() {
        return $this->name;
    }

    public function getGroupId() {
        return $this->groupId;
    }

    public function getLogin() {
        return $this->login;
    }

    public function getGroupName() {
        return $this->groupName;
    }
}