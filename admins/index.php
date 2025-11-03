<?php
session_start();
include "../config/config.php";
include "../include/function.php";
spl_autoload_register("loadClass");

// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Lấy thống kê
$stmt = $pdo->query("SELECT COUNT(*) as total_accounts FROM accounts");
$total_accounts = $stmt->fetch()['total_accounts'];

$stmt = $pdo->query("SELECT COUNT(*) as sold_accounts FROM accounts WHERE status = 'sold'");
$sold_accounts = $stmt->fetch()['sold_accounts'];

$stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
$total_orders = $stmt->fetch()['total_orders'];

$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$total_users = $stmt->fetch()['total_users'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Shop Nick Liên Quân</title>
    <link type="text/css" rel="stylesheet" href="../css/bootstrap.min.css"/>
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <style>
        .sidebar {
            background: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            width: 250px;
            padding-top: 20px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
            border-left: 5px solid #3498db;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .stat-label {
            color: #7f8c8d;
            font-size: 1.1em;
        }
        .nav-link {
            color: white;
            padding: 15px 20px;
            border-bottom: 1px solid #34495e;
            text-decoration: none;
            display: block;
            transition: all 0.3s;
        }
        .nav-link:hover {
            background: #34495e;
            color: white;
            padding-left: 30px;
        }
        .nav-link.active {
            background: #e74c3c;
            border-left: 5px solid #c0392b;
        }
        .admin-header {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="p-3 text-center bg-dark">
            <h4>🛡️ ADMIN PANEL</h4>
            <small>Shop Nick Liên Quân</small>
        </div>
        <div class="nav flex-column">
            <a class="nav-link active" href="index.php">📊 Dashboard</a>
            <a class="nav-link" href="accounts.php">🎮 Quản lý tài khoản</a>
            <a class="nav-link" href="orders.php">📦 Quản lý đơn hàng</a>
            <a class="nav-link" href="users.php">👥 Quản lý users</a>
            <a class="nav-link" href="../index.php">🏠 Về trang chủ</a>
            <a class="nav-link" href="logout.php">🚪 Đăng xuất</a>
        </div>
    </div>

    <div class="main-content">
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Xin chào, <?php echo $_SESSION['user_name']; ?>! 👋</h2>
                    <p class="mb-0">Quản lý Shop Nick Liên Quân Mobile</p>
                </div>
                <span class="badge bg-light text-danger fs-6">ADMIN</span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: #3498db;">
                    <div class="stat-number"><?php echo $total_accounts; ?></div>
                    <div class="stat-label">Tổng tài khoản</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: #27ae60;">
                    <div class="stat-number"><?php echo $sold_accounts; ?></div>
                    <div class="stat-label">Đã bán</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: #e74c3c;">
                    <div class="stat-number"><?php echo $total_orders; ?></div>
                    <div class="stat-label">Tổng đơn hàng</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="border-left-color: #f39c12;">
                    <div class="stat-number"><?php echo $total_users; ?></div>
                    <div class="stat-label">Thành viên</div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="stat-card">
                    <h4>📈 Thống kê nhanh</h4>
                    <p>Chào mừng đến với trang quản trị Shop Nick Liên Quân Mobile!</p>
                    <p><strong>Email đăng nhập:</strong> <?php echo $_SESSION['user_email']; ?></p>
                    <p><strong>Vai trò:</strong> <?php echo $_SESSION['user_role']; ?></p>
                    <div class="mt-3">
                        <a href="accounts.php" class="btn btn-primary">🎮 Quản lý tài khoản</a>
                        <a href="orders.php" class="btn btn-success">📦 Quản lý đơn hàng</a>
                        <a href="users.php" class="btn btn-info">👥 Quản lý users</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</body>
</html>
