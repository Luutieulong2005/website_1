<?php
session_start();
include "../config/config.php";

// Kiểm tra admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// === THỐNG KÊ ===
$total_products = $pdo->query("SELECT COUNT(*) FROM product")->fetchColumn();
$sold_quantity = $pdo->query("SELECT SUM(quantity) FROM order_detail")->fetchColumn() ?? 0;
$total_orders = $pdo->query("SELECT COUNT(*) FROM `order`")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM `user`")->fetchColumn();

$revenue = $pdo->query("
    SELECT SUM(od.quantity * p.product_price) 
    FROM order_detail od 
    JOIN product p ON od.product_id = p.product_id 
    JOIN `order` o ON od.order_id = o.order_id 
    WHERE o.order_status = 1
")->fetchColumn() ?? 0;

$pending_orders = $pdo->query("SELECT COUNT(*) FROM `order` WHERE order_status = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Computer Store</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <style>
        .sidebar { background: #2c3e50; color: white; height: 100vh; position: fixed; width: 250px; padding-top: 20px; }
        .main-content { margin-left: 250px; padding: 20px; }
        .stat-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; margin-bottom: 20px; }
        .stat-number { font-size: 2.5em; font-weight: bold; color: #2c3e50; }
        .nav-link { color: #ecf0f1; padding: 15px 20px; display: block; text-decoration: none; border-bottom: 1px solid #34495e; }
        .nav-link:hover { background: #34495e; padding-left: 30px; }
        .nav-link.active { background: #e74c3c; font-weight: bold; }
        .revenue { font-size: 2em; color: #27ae60; font-weight: bold; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="p-3 text-center bg-dark">
        <h5>ADMIN PANEL</h5>
    </div>
    <a class="nav-link active" href="index.php">Dashboard</a>
    <a class="nav-link" href="accounts.php">Sản phẩm</a>
    <a class="nav-link" href="orders.php">Đơn hàng</a>
    <a class="nav-link" href="users.php">Khách hàng</a>
    <a class="nav-link" href="../index.php">Trang chủ</a>
    <a class="nav-link" href="logout.php">Đăng xuất</a>
</div>

<div class="main-content">
    <div class="bg-primary text-white p-3 rounded mb-4">
        <h3>Xin chào, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h3>
    </div>

    <div class="row">
        <div class<?php echo "col-md-3"; ?>>
            <div class="stat-card border-start border-primary border-5">
                <div class="stat-number"><?php echo number_format($total_products); ?></div>
                <div>Tổng sản phẩm</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-start border-warning border-5">
                <div class="stat-number"><?php echo number_format($sold_quantity); ?></div>
                <div>Đã bán</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-start border-success border-5">
                <div class="stat-number"><?php echo number_format($total_orders); ?></div>
                <div>Tổng đơn hàng</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-start border-info border-5">
                <div class="stat-number"><?php echo number_format($total_users); ?></div>
                <div>Khách hàng</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="stat-card">
                <h5>Doanh thu</h5>
                <div class="revenue"><?php echo number_format($revenue); ?> VNĐ</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <h5>Đơn hàng chờ xử lý</h5>
                <div class="stat-number text-warning"><?php echo $pending_orders; ?></div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
