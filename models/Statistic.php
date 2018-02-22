<?php

namespace models;

use mysqli;

class Statistic
{
    // CREATE DATABASE `statistics`;

    /*
    CREATE TABLE `daily` (
        `id` int(11) NOT NULL,
        `route` varchar(100),
        `date` date NOT NULL,
        `hits` int(11) NOT NULL DEFAULT '0'
    );

    ALTER TABLE `daily`
        ADD PRIMARY KEY (`id`),
        ADD KEY `route` (`route`),
        ADD KEY `date` (`date`);

    ALTER TABLE `daily`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;COMMIT;
    */

    private $dbname;
    private $dbuser;
    private $dbpass;
    private $conn;

    public function __construct()
    {
        $this->dbname = getenv('DB_NAME') ?: false;
        $this->dbuser = getenv('DB_USER') ?: false;
        $this->dbpass = getenv('DB_PASS') ?: false;
    }

    private function query($sql)
    {
        return $this->conn->query($sql);
    }

    public function save($routeName)
    {
        $this->conn = new mysqli('localhost', $this->dbuser, $this->dbpass, $this->dbname);

        // if in debug mode, show when cant connect to db
        if (getenv('DEBUG') && $this->conn->connect_error) {
            die('Connection failed: '.$this->conn->connect_error);
        }

        // get todays date
        $dateString = date('Y-m-d');

        // only run this if there isn't a connection error, no need for any output
        if (!$conn->connect_error) {

            // does an entry for today exist?
            $sql = "SELECT hits FROM daily WHERE date = '$dateString' AND route = '$routeName'";
            echo $sql;
            $result = $this->query($sql);

            // if not, create one
            if ($result->num_rows == 0) {
                $sql = "INSERT into daily (date, route) VALUES ('$dateString', '$routeName')";
                $this->query($sql);
            }

            $sql = "UPDATE daily SET hits = hits + 1 WHERE date = '$dateString' AND route = '$routeName'";
            $this->query($sql);
        }
    }
}
