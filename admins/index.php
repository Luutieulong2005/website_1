<?php
session_start();
include "../config/config.php";
include "../include/function.php";
spl_autoload_register("loadClass");

// === KIỂM TRA ĐĂNG NHẬP ADMIN ===
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Kiểm tra nếu là admin (dựa vào email hoặc user_id đặc biệt)
$admin_emails = ['admin@computerstore.vn', 'phat123@asd.com']; // Danh sách admin
if (!in_array($_SESSION['user_email'] ?? '', $admin_emails)) {
    header("Location: ../index.php");
    exit();
}

// === LẤY THỐNG KÊ TỪ CSDL `computer_store` ===

// 1. Tổng sản phẩm
$stmt = $pdo->query("SELECT COUNT(*) as total_products FROM product");
$total_products = $stmt->fetch()['total_products'];

// 2. Sản phẩm đã bán (dựa vào order_detail)
$stmt = $pdo->query("SELECT SUM(quantity) as sold_quantity FROM order_detail");
$sold_quantity = $stmt->fetch()['sold_quantity'] ?? 0;

// 3. Tổng đơn hàng
$stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM `order`");
$total_orders = $stmt->fetch()['total_orders'];

// 4. Tổng người dùng
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM `user`");
$total_users = $stmt->fetch()['total_users'];

// 5. Doanh thu (tính từ order_detail + product_price)
$stmt = $pdo->query("
    SELECT SUM(od.quantity * p.product_price) as revenue 
    FROM order_detail od 
    JOIN product p ON od.product_id = p.product_id 
    JOIN `order` o ON od.order_id = o.order_id 
    WHERE o.order_status = 1
");
$revenue = $stmt->fetch()['revenue'] ?? 0;

// 6. Đơn hàng đang xử lý
$stmt = $pdo->query("SELECT COUNT(*) as pending_orders FROM `order` WHERE order_status = 0");
$pending_orders = $stmt->fetch()['pending_orders'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Computer Store</title>
    <link type="text/css" rel="stylesheet" href="../css/bootstrap.min.css"/>
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; }
        .sidebar {
            background: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            width: 250px;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2.8em;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 1.1em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .nav-link {
            color: #ecf0f1;
            padding: 14px 20px;
            border-bottom: 1px solid #34495e;
            text-decoration: none;
            display: block;
            transition: all 0.3s;
            font-weight: 500;
        }
        .nav-link:hover {
            background: #34495e;
            color: white;
            padding-left: 28px;
        }
        .nav-link.active {
            background: #e74c3c;
            border-left: 5px solid #c0392b;
            font-weight: bold;
        }
        .admin-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        .revenue {
            font-size: 2.2em;
            color: #27ae60;
            font-weight: bold;
        }
        .badge-admin {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="p-3 text-center bg-dark">
            <h4>ADMIN PANEL</h4>
            <small>Computer Store</small>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link active" href="index.php">Dashboard</a>
            <a class="nav-link" href="products.php">Sản phẩm</a>
            <a class="nav-link" href="orders.php">Đơn hàng</a>
            <a class="nav-link" href="users.php">Khách hàng</a>
            <a class="nav-link" href="categories.php">Danh mục</a>
            <a class="nav-link" href="../index.php">Về trang chủ</a>
            <a class="nav-link" href="logout.php">Đăng xuất</a>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Xin chào, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>!</h2>
                    <p class="mb-0">Quản lý cửa hàng máy tính</p>
                </div>
                <span class="badge-admin">ADMIN</span>
            </div>
        </div>

        <!-- THỐNG KÊ NHANH -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card" style="border-left: 5px solid #3498db;">
                    <div class="stat-number"><?php echo number_format($total_products); ?></div>
                    <div class="stat-label">Tổng sản phẩm</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-left: 5px solid #e67e22;">
                    <div class="stat-number"><?php echo number_format($sold_quantity); ?></div>
                    <div class="stat-label">Sản phẩm đã bán</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-left: 5px solid #27ae60;">
                    <div class="stat-number"><?php echo number_format($total_orders); ?></div>
                    <div class="stat-label">Tổng đơn hàng</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-left: 5px solid #9b59b6;">
                    <div class="stat-number"><?php echo number_format($total_users); ?></div>
                    <div class="stat-label">Khách hàng</div>
                </div>
            </div>
        </div>

        <!-- DOANH THU & THỐNG KÊ -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="stat-card">
                    <h4>Doanh thu</h4>
                    <div class="revenue"><?php echo number_format($revenue, 0, ',', '.'); ?> VNĐ</div>
                    <p class="text-muted">Từ các đơn hàng đã giao (order_status = 1)</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <h4>Đơn hàng đang xử lý</h4>
                    <div class="stat-number text-warning"><?php echo $pending_orders; ?></div>
                    <p class="text-muted">Chờ xác nhận / giao hàng</p>
                    <div class="mt-3">
                        <a href="orders.php" class="btn btn-warning btn-sm">Xem chi tiết</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- THÔNG TIN ADMIN -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="stat-card">
                    <h5>Thông tin quản trị viên</h5>
                    <hr>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                    <p><strong>User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
                    <p><strong>Phiên bản DB:</strong> computer_store.sql (MySQL 5.7)</p>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</body>
</html>
