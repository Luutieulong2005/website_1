<?php
define('ROOT', dirname(dirname(__FILE__)));

// CẤU HÌNH DATABASE CỦA BẠN - SỬA THÔNG TIN NÀY
$host = 'mysql-lab-db.mysql.database.azure.com';  // Host của bạn
$dbname = 'lienquan_shop';                        // Tên database
$username = 'sqladmin';                   // SỬA: username của bạn
$password = 'Long2209@';                   // SỬA: password của bạn

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kết nối database thất bại: " . $e->getMessage());
}
?>
