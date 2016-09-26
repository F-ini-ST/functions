<?php
class Db {
    public $link;
    public function __construct($server,$user,$password) {
        $this->link = mysql_connect($server,$user,$password) or die("could not connect to database");
    }
    public function selectDB($db_name) {
        mysql_select_db($db_name) or die("could not select database");
    }
    public function query($query) {
        return mysql_query($query);
    }
    
}