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
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            color: #7f8c8d;
        }
        .nav-link {
            color: white;
            padding: 15px 20px;
            border-bottom: 1px solid #34495e;
        }
        .nav-link:hover {
            background: #34495e;
            color: white;
        }
        .nav-link.active {
            background: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="p-3 text-center bg-dark">
            <h4>🛡️ ADMIN PANEL</h4>
            <small>Shop Nick Liên Quân</small>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link active" href="index.php">📊 Dashboard</a>
            <a class="nav-link" href="accounts.php">🎮 Quản lý tài khoản</a>
            <a class="nav-link" href="orders.php">📦 Quản lý đơn hàng</a>
            <a class="nav-link" href="users.php">👥 Quản lý users</a>
            <a class="nav-link" href="../index.php">🏠 Về trang chủ</a>
            <a class="nav-link" href="logout.php">🚪 Đăng xuất</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Xin chào, <?php echo $_SESSION['user_name']; ?>! 👋</h2>
            <span class="badge bg-danger">ADMIN</span>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_accounts; ?></div>
                    <div class="stat-label">Tổng tài khoản</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $sold_accounts; ?></div>
                    <div class="stat-label">Đã bán</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_orders; ?></div>
                    <div class="stat-label">Tổng đơn hàng</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
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
                    <p>Bạn có thể quản lý tất cả tài khoản, đơn hàng và người dùng từ đây.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
