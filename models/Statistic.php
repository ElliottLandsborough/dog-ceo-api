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

        // connect to mysql
        $this->connect();
    }

    // attempt to connect to mysql
    private function connect()
    {
        $this->conn = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);

        // if in debug mode, show when cant connect to db
        if ($this->conn->connect_error) {
            if (getenv('DEBUG')) {
                error_log('Connection failed: ' . $this->conn->connect_error);
            }
            die();
        }
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
    private function query($sql)
    {
        $query = $this->conn->query($sql);
        if (getenv('DEBUG') && $query !== true && strlen($conn->error)) {
            error_log('Error: '.$sql.': '.$conn->error);
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
