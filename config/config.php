<?php
$configDB = array();
$configDB["host"] 		= "mysql-lab-db.mysql.database.azure.com";
$configDB["database"]	= "lienquan_shop";
$configDB["username"] 	= "sqladmin";
$configDB["password"] 	= "Long2209@";
define("HOST", "mysql-lab-db.mysql.database.azure.com");
define("DB_NAME", "lienquan_shop");
define("DB_USER", "sqladmin");
define("DB_PASS", "Long2209@");
define('ROOT', dirname(dirname(__FILE__) ) );
//Thu muc tuyet doi truoc cua config; c:/wamp/www/lab/
define("BASE_URL", "http://".$_SERVER['SERVER_NAME']);//dia chi website

// Kết nối database
try {
    $pdo = new PDO(
        "mysql:host=" . HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}
?>
