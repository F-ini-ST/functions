<?php
    session_start();
    require_once './db.php';
    $db = new Db("localhost","user","password");
    my_admin($db);
?>