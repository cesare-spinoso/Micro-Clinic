<?php
/**
 * Created by PhpStorm.
 * User: csdp
 * Date: 1/17/2020
 * Time: 11:11 AM
 */
include "common.php";
if(!isset($_COOKIE)){
    setcookie('init_sql', 0, time() + (86400 * 365), "/");
}
print_r($_COOKIE);

if($_COOKIE["init_sql"] == 0) {
    try {
        $connection = new PDO("mysql:host=$servername", $username, $password, $options);
        $sql = file_get_contents("init.sql");
        $connection->exec($sql);

        setcookie("init_sql", 1, time() + (86400 * 365), "/");
        echo "Database created successfully. <a href='home.php'>Back Home.</a>";
    } catch (PDOException $e) {
        echo "Wait what!";
        print_r($e->errorInfo);
    }
}
else{
    print_r($_COOKIE);
    echo "Database has already been created!";
}

