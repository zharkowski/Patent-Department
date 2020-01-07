<?php
include_once 'DBConnection.php';
use DB\DBConnection as DBConnection;

class Patent
{
    private $id = null;
    private $status = null;
    private $roles = array();

    private $mysqli = null;
    public function __construct()
    {
        $this->mysqli = DBConnection::getInstance()->getConnection();
    }


}