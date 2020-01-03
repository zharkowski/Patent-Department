<?php

namespace DB;
use mysqli;

class DBConnection
{
    private $host = 'localhost';
    private $database = 'patent_department_db';
    private $username = 'root';
    private $password = 'root';
    private $port = '8889';

    private static $instance = null;
    private static $mysqli = null;

    private function __construct()
    {
        self::$mysqli = new mysqli($this->host, $this->username, $this->password, $this->database, $this->port);
        self::$mysqli->set_charset("utf-8");
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new DBConnection();
        }

        return self::$instance;
    }

    public function getConnection() {
        return self::$mysqli;
    }
}