<?php
// Khởi động session (nếu chưa có)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ROOT', dirname(__FILE__));

// === THÔNG TIN KẾT NỐI AZURE CỦA BẠN ===
$host     = 'mysql-lab-db.mysql.database.azure.com';  // Host Azure
$dbname   = 'lienquan_shop';                         // TÊN DB MỚI (phải tạo)
$username = 'sqladmin@mysql-lab-db';                  // Username + @server
$password = 'Long2209';                               // Password của bạn

try {
    // Kết nối với charset utf8 + SSL (bắt buộc trên Azure)
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_SSL_CA       => ROOT . '/DigiCertGlobalRootCA.crt.pem', // SSL
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true
        ]
    );

    // Đảm bảo UTF-8
    $pdo->exec("SET NAMES utf8");

} catch (PDOException $e) {
    // Ghi log lỗi (không lộ ra user)
    error_log("Azure DB Connection Failed: " . $e->getMessage());
    die("Hệ thống đang bảo trì. Vui lòng thử lại sau.");
}
?>
