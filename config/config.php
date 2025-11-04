<?php
// config/config.php

// KHỞI ĐỘNG SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ROOT', dirname(dirname(__FILE__)));

class Config {
    // === THÔNG TIN CỦA BẠN - 100% GIỮ NGUYÊN ===
    private $host     = 'mysql-lab-db.mysql.database.azure.com';
    private $dbname   = 'lienquan_shop';
    private $username = 'sqladmin';
    private $password = 'Long2209@';
    public $conn;

    // Site configuration
    public $site_name = "HTP SHOP - Nick Liên Quân";
    public $site_url = "http://localhost/htpshop";
    public $admin_email = "tieulong.work@gmail.com";
    
    // Payment configuration
    public $currency = "VND";
    public $vat_rate = 0.1; // 10%
    
    // File upload configuration
    public $upload_dir = "uploads/";
    public $max_file_size = 5242880; // 5MB
    public $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=$this->host;dbname=$this->dbname;charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // SSL: DÙNG FILE CỦA BẠN (nếu có) – KHÔNG BẮT BUỘC NẾU BẠN ĐÃ CẤU HÌNH TRÊN AZURE
                    // PDO::MYSQL_ATTR_SSL_CA => ROOT . '/config/DigiCertGlobalRootCA.crt.pem',
                    // PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
                ]
            );

            // TEST NHẸ (CÓ THỂ XÓA SAU)
            $this->conn->query("SELECT 1");

        } catch (PDOException $e) {
            // GHI LOG + ẨN LỖI
            error_log("DB Error: " . $e->getMessage());
            throw new Exception("Hệ thống đang bảo trì. Vui lòng thử lại sau.");
        }
        return $this->conn;
    }
    
    public function formatPrice($price) {
        return number_format($price, 0, ',', '.') . ' ₫';
    }
    
    public function getStatusBadge($status) {
        $badges = [
            'available' => '<span style="background: #2ecc71; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">Còn hàng</span>',
            'sold' => '<span style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">Đã bán</span>',
            'hidden' => '<span style="background: #95a5a6; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">Ẩn</span>'
        ];
        return $badges[$status] ?? '';
    }
    
    public function getOrderStatusBadge($status) {
        $badges = [
            'pending' => '<span style="background: #f39c12; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">Chờ xử lý</span>',
            'paid' => '<span style="background: #3498db; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">Đã thanh toán</span>',
            'completed' => '<span style="background: #2ecc71; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">Hoàn thành</span>',
            'cancelled' => '<span style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">Đã hủy</span>'
        ];
        return $badges[$status] ?? '';
    }
}

// Tạo instance toàn cục
$config = new Config();

// Hàm helper toàn cục
function formatPrice($price) {
    global $config;
    return $config->formatPrice($price);
}

function getStatusBadge($status) {
    global $config;
    return $config->getStatusBadge($status);
}

function getOrderStatusBadge($status) {
    global $config;
    return $config->getOrderStatusBadge($status);
}
?>
