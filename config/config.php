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
    public $site_name = "HTP SHOP - Nick Liên Quân Uy Tín";
    public $site_url = "http://localhost/htpshop";
    public $admin_email = "tieulong.work@gmail.com";
    
    // Payment configuration
    public $currency = "VND";
    
    // File upload configuration
    public $upload_dir = "uploads/";
    public $max_file_size = 5242880; // 5MB
    public $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            // Test connection
            $this->conn->query("SELECT 1");

        } catch (PDOException $e) {
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
            'sold' => '<span style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">Đã bán</span>'
        ];
        return $badges[$status] ?? '';
    }
    
    public function getOrderStatusBadge($status) {
        $badges = [
            'pending' => '<span style="background: #f39c12; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">Chờ xử lý</span>',
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
