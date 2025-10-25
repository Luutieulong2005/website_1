<?php
$configDB = array();
$configDB["host"] 		= "mysql-lab-db.mysql.database.azure.com";
$configDB["database"]	= "computer_store";
$configDB["username"] 	= "sqladmin";
$configDB["password"] 	= "Long2209@";
define("HOST", "mysql-lab-db.mysql.database.azure.com");
define("DB_NAME", "computer_store");
define("DB_USER", "sqladmin");
define("DB_PASS", "Long2209@");
define('ROOT', dirname(dirname(__FILE__) ) );
//Thu muc tuyet doi truoc cua config; c:/wamp/www/lab/
define("BASE_URL", "http://".$_SERVER['SERVER_NAME']);//dia chi website
?>
