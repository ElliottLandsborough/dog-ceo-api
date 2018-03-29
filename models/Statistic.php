<?php

namespace models;

use mysqli;

/**
 * Some super simple stats, see readme for db setup.
 * Queries run after the json has been sent.
 * Errors silently so that the api stays up if db goes down.
 */
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
        if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'dog.ceo') {
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

        // connect to mysql
        $this->conn = $this->connect();
    }

    // attempt to connect to mysql
    private function connect()
    {
        $conn = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);

        // if in debug mode, show when cant connect to db
        if ($conn->connect_error) {
            if (getenv('DEBUG')) {
                error_log('Connection failed: ' . $conn->connect_error);
            }
            die();
        }

        return $conn;
    }

    // important to clean the string so that people can't manipulate mysql
    private function cleanString($string)
    {
        // remove any '?' vars (we currently don't need any)
        // stackoverflow.com/questions/1251582/beautiful-way-to-remove-get-variables-with-php/1251650#1251650
        $string = strtok($string, '?');

        // remove all apart from alphanum/underscore, forward/backslash, dash (regexr.com/3l88o)
        $string = preg_replace('/[^\w\/\\-]/', '', $string);
        
        return $string;
    }

    // run a query, send error to log if debug is enabled
    public function query($sql)
    {
        $query = $this->conn->query($sql);
        if (getenv('DEBUG') && $query !== true && strlen($this->conn->error)) {
            error_log('Error: '.$sql.': '.$this->conn->error);
        }

        return $query;
    }

    // save the stats
    public function save($routeName = null)
    {
        // clean the string for queries
        $routeName = $this->cleanString($routeName);

        // get todays date
        $dateString = date('Y-m-d');

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

        $ip = ((isset($_SERVER['REMOTE_ADDR']) && strlen($_SERVER['REMOTE_ADDR'])) ? "'" . $this->conn->real_escape_string($_SERVER['REMOTE_ADDR']) . "'" : 'NULL');
        $date = "'" . date('Y-m-d h:i:s') . "'";
        $userAgent = ((isset($_SERVER['HTTP_USER_AGENT']) && strlen($_SERVER['HTTP_USER_AGENT'])) ? "'" . $this->conn->real_escape_string($_SERVER['HTTP_USER_AGENT']) . "'" : 'NULL');
        $referrer = ((isset($_SERVER['HTTP_REFERER']) && strlen($_SERVER['HTTP_REFERER'])) ? "'" . $this->conn->real_escape_string($_SERVER['HTTP_REFERER']) . "'" : 'NULL');

        $sql = "INSERT INTO `visits` (`ip`, `date`, `endpoint`, `user-agent`, `referrer`) VALUES ($ip, $date, '$routeName', $userAgent, $referrer);";
        $this->query($sql);
    }

    // get all stats
    public function getAllVisitsWithNoCountry()
    {
        $sql = "SELECT id, ip, country FROM `visits` where `country` IS NULL;";
        return $this->query($sql);
    }

    // get count of countries
    public function getCountryCount()
    {
        $sql = 'select country, count(*) as count from visits group by country;';
        $result = $this->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // get count of user agents
    public function getUserAgentCount()
    {
        $sql = 'select `user-agent`, count(*) as count from visits group by `user-agent`;';
        $result = $this->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // get count of user agents
    public function getReferrerCount()
    {
        $sql = 'select `referrer`, count(*) as count from visits group by `referrer`;';
        $result = $this->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
