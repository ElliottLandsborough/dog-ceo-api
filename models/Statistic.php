<?php

namespace models;

use mysqli;

class Statistic
{
    private $dbhost;
    private $dbname;
    private $dbuser;
    private $dbpass;
    private $conn;

    public function __construct()
    {
        // hosting provider has env 'issues'
        if ($_SERVER['SERVER_NAME'] == 'dog.ceo') {
            $this->dbhost = 'localhost';
            $this->dbname = 'dogstats';
            $this->dbuser = 'dogstats';
            $this->dbpass = null;
        } else {
            $this->dbhost = getenv('DB_HOST') ?: false;
            $this->dbname = getenv('DB_NAME') ?: false;
            $this->dbuser = getenv('DB_USER') ?: false;
            $this->dbpass = getenv('DB_PASS') ?: false;
        }
    }

    // important to clean the string so that people can't manipulate mysql
    private function cleanString($string)
    {
        // remove any '?' vars (we currently don't need any)
        // stackoverflow.com/questions/1251582/beautiful-way-to-remove-get-variables-with-php/1251650#1251650
        $string = strtok($string, '?');

        // remove all apart from a-z, 0-9, dash, forward/backslash
        $string = preg_replace('/[^\w\/\\-]/', '', $string);
        
        return $string;
    }

    private function query($sql)
    {
        $query = $this->conn->query($sql);
        if (getenv('DEBUG') && $query !== true && strlen($conn->error)) {
            error_log('Error: '.$sql.': '.$conn->error);
        }

        return $query;
    }

    public function save($routeName = null)
    {
        // clean the string for queries
        $routeName = $this->cleanString($routeName);

        $this->conn = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);

        // if in debug mode, show when cant connect to db
        if ($this->conn->connect_error) {
            if (getenv('DEBUG')) {
                error_log('Connection failed: '.$this->conn->connect_error);
            }
            die();
        }

        // get todays date
        $dateString = date('Y-m-d');

        // only run this if there isn't a connection error, no need for any output
        if (!$this->conn->connect_error) {

            // does an entry for today exist?
            $sql = "SELECT hits FROM daily WHERE date = '$dateString' AND route = '$routeName'";
            $result = $this->query($sql);

            // if not, create one
            if ($result->num_rows == 0) {
                $sql = "INSERT into daily (date, route) VALUES ('$dateString', '$routeName')";
                $this->query($sql);
            }

            // increment the hit count by 1
            $sql = "UPDATE daily SET hits = hits + 1 WHERE date = '$dateString' AND route = '$routeName'";
            $this->query($sql);
        }
    }
}
