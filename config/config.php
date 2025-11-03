<?php
// Đảm bảo session đã được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Đường dẫn gốc
define('ROOT', dirname(__FILE__));

// === CẤU HÌNH DATABASE CHO computer_store.sql ===
$host     = 'mysql-lab-db.mysql.database.azure.com';                    // Hoặc IP của bạn
$dbname   = 'lienquan_shop';               // TÊN DATABASE MỚI
$username = 'sqladmin';                         // Username MySQL
$password = 'Long2209';                             // Password (để trống nếu XAMPP/WAMP)
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Ghi log lỗi (không lộ ra người dùng)
    error_log("Database connection failed: " . $e->getMessage());
    die("Hệ thống đang bảo trì. Vui lòng thử lại sau.");
}
?>
