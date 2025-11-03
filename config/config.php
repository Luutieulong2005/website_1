<?php
// KHỞI ĐỘNG SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ROOT', dirname(__FILE__));

// === THÔNG TIN CỦA BẠN - 100% GIỮ NGUYÊN ===
$host     = 'mysql-lab-db.mysql.database.azure.com';
$dbname   = 'lienquan_shop';
$username = 'sqladmin';           // BẠN ĐÃ XÁC NHẬN DÙNG CÁI NÀY
$password = 'Long2209@';

// === KẾT NỐI AZURE - ĐÃ TỐI ƯU ===
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // SSL: DÙNG FILE CỦA BẠN (nếu có) – KHÔNG BẮT BUỘC NẾU BẠN ĐÃ CẤU HÌNH TRÊN AZURE
            // PDO::MYSQL_ATTR_SSL_CA => ROOT . '/config/DigiCertGlobalRootCA.crt.pem',
            // PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ]
    );

    // TEST NHẸ (CÓ THỂ XÓA SAU)
    $pdo->query("SELECT 1");

} catch (PDOException $e) {
    // GHI LOG + ẨN LỖI
    error_log("DB Error: " . $e->getMessage());
    die("Hệ thống đang bảo trì. Vui lòng thử lại sau.");
}
?>
