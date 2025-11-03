<?php
// config/config.php - File cấu hình
define('DB_HOST', 'mysql-lab-db.mysql.database.azure.com');
define('DB_NAME', 'lienquan_shop');
define('DB_USER', 'sqladmin');
define('DB_PASS', 'Long2209@');
define('DB_CHARSET', 'utf8');

define('SITE_NAME', 'Shop Nick Liên Quân Mobile');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch(PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
