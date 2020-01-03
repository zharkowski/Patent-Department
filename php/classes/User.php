<?php
include_once 'DataBaseConnection.php';
use DB\DBConnection as DBConnection;

class User
{
    private $id = null;
    private $login;
    private $password;
    private $name;
    private $email;
    private $group = 1; //Изначально аноним

    private $mysqli = null;
    public function __construct() {
        $this->mysqli = DBConnection::getInstance()->getConnection();
    }

    public function getUser() {
        return $this->login;
    }
}