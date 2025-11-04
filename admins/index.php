<?php
session_start();
include "../config/config.php";

// Kiểm tra admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $db = $config->getConnection();
    
    // === THỐNG KÊ CHO BÁN ACC LIÊN QUÂN ===
    $total_accounts = $db->query("SELECT COUNT(*) FROM accounts")->fetchColumn();
    $sold_accounts = $db->query("SELECT COUNT(*) FROM accounts WHERE status = 'sold'")->fetchColumn();
    $available_accounts = $db->query("SELECT COUNT(*) FROM accounts WHERE status = 'available'")->fetchColumn();
    
    $total_orders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $total_users = $db->query("SELECT COUNT(*) FROM user")->fetchColumn();

    // Doanh thu từ các đơn hàng đã hoàn thành
    $revenue = $db->query("
        SELECT SUM(total_amount) 
        FROM orders 
        WHERE status = 'completed'
    ")->fetchColumn() ?? 0;

    // Đơn hàng đang chờ xử lý
    $pending_orders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    
    // Thống kê theo rank
    $rank_stats = $db->query("
        SELECT rank, COUNT(*) as count, 
               SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
               SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available
        FROM accounts 
        GROUP BY rank 
        ORDER BY count DESC
    ")->fetchAll();

    // Đơn hàng mới nhất
    $recent_orders = $db->query("
        SELECT o.*, a.username, a.rank 
        FROM orders o 
        LEFT JOIN accounts a ON o.account_id = a.id 
        ORDER BY o.order_date DESC 
        LIMIT 5
    ")->fetchAll();

} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $total_accounts = $sold_accounts = $available_accounts = $total_orders = $total_users = $revenue = $pending_orders = 0;
    $rank_stats = $recent_orders = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - HTP Shop Liên Quân</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #e74c3c;
            --accent: #3498db;
            --success: #27ae60;
        }
        
        .sidebar { 
            background: var(--primary); 
            color: white; 
            height: 100vh; 
            position: fixed; 
            width: 250px; 
            padding-top: 20px; 
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .main-content { 
            margin-left: 250px; 
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        .stat-card { 
            background: white; 
            padding: 25px; 
            border-radius: 15px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
            text-align: center; 
            margin-bottom: 20px;
            border-left: 5px solid;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-number { 
            font-size: 2.5em; 
            font-weight: bold; 
            color: var(--primary); 
            margin-bottom: 10px;
        }
        .stat-icon {
            font-size: 2em;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        .nav-link { 
            color: #ecf0f1; 
            padding: 15px 20px; 
            display: block; 
            text-decoration: none; 
            border-bottom: 1px solid #34495e;
            transition: all 0.3s;
        }
        .nav-link:hover { 
            background: #34495e; 
            padding-left: 30px;
            color: white;
        }
        .nav-link.active { 
            background: var(--secondary); 
            font-weight: bold;
        }
        .revenue { 
            font-size: 2em; 
            color: var(--success); 
            font-weight: bold; 
        }
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary), #34495e);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .rank-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            margin: 2px;
        }
        .table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="p-3 text-center" style="background: rgba(0,0,0,0.2);">
        <h5><i class="fas fa-crown"></i> HTP SHOP ADMIN</h5>
        <small>Quản lý tài khoản Liên Quân</small>
    </div>
    <a class="nav-link active" href="index.php">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a class="nav-link" href="accounts.php">
        <i class="fas fa-gamepad"></i> Quản lý tài khoản
    </a>
    <a class="nav-link" href="orders.php">
        <i class="fas fa-shopping-cart"></i> Đơn hàng
    </a>
    <a class="nav-link" href="users.php">
        <i class="fas fa-users"></i> Khách hàng
    </a>
    <a class="nav-link" href="categories.php">
        <i class="fas fa-tags"></i> Danh mục
    </a>
    <a class="nav-link" href="../index.php">
        <i class="fas fa-home"></i> Trang chủ
    </a>
    <a class="nav-link" href="logout.php">
        <i class="fas fa-sign-out-alt"></i> Đăng xuất
    </a>
</div>

<div class="main-content">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3><i class="fas fa-tachometer-alt"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h3>
                <p class="mb-0">Quản lý cửa hàng tài khoản Liên Quân</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="bg-success text-white p-2 rounded d-inline-block">
                    <i class="fas fa-wallet"></i> Doanh thu: <?php echo number_format($revenue); ?> ₫
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê chính -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: var(--accent);">
                <div class="stat-icon" style="color: var(--accent);">
                    <i class="fas fa-gamepad"></i>
                </div>
                <div class="stat-number"><?php echo number_format($total_accounts); ?></div>
                <div>Tổng tài khoản</div>
                <small class="text-muted"><?php echo $available_accounts; ?> có sẵn</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: var(--success);">
                <div class="stat-icon" style="color: var(--success);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo number_format($sold_accounts); ?></div>
                <div>Đã bán</div>
                <small class="text-muted">Tài khoản đã bán</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #f39c12;">
                <div class="stat-icon" style="color: #f39c12;">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-number"><?php echo number_format($total_orders); ?></div>
                <div>Tổng đơn hàng</div>
                <small class="text-muted"><?php echo $pending_orders; ?> đang chờ</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #9b59b6;">
                <div class="stat-icon" style="color: #9b59b6;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo number_format($total_users); ?></div>
                <div>Khách hàng</div>
                <small class="text-muted">Người dùng đã đăng ký</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Thống kê theo Rank -->
        <div class="col-md-6">
            <div class="table-card">
                <h5><i class="fas fa-chart-bar"></i> Thống kê theo Rank</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Rank</th>
                                <th>Tổng số</th>
                                <th>Đã bán</th>
                                <th>Còn lại</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rank_stats as $stat): ?>
                                <tr>
                                    <td>
                                        <span class="rank-badge" style="background: 
                                            <?php 
                                            switch($stat['rank']) {
                                                case 'Cao Thủ': echo '#e74c3c'; break;
                                                case 'Kim Cương': echo '#3498db'; break;
                                                case 'Tinh Anh': echo '#9b59b6'; break;
                                                case 'Vàng': echo '#f1c40f'; color: #000; break;
                                                case 'Bạc': echo '#bdc3c7'; break;
                                                default: echo '#95a5a6';
                                            }
                                            ?>">
                                            <?php echo htmlspecialchars($stat['rank']); ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo $stat['count']; ?></strong></td>
                                    <td><span class="text-success"><?php echo $stat['sold']; ?></span></td>
                                    <td><span class="text-primary"><?php echo $stat['available']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Đơn hàng gần đây -->
        <div class="col-md-6">
            <div class="table-card">
                <h5><i class="fas fa-clock"></i> Đơn hàng gần đây</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Khách hàng</th>
                                <th>Tài khoản</th>
                                <th>Rank</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_orders)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Chưa có đơn hàng nào</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['username'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="rank-badge" style="background: #3498db; color: white;">
                                                <?php echo htmlspecialchars($order['rank'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_badge = [
                                                'pending' => 'warning',
                                                'completed' => 'success', 
                                                'cancelled' => 'danger'
                                            ];
                                            $status_text = [
                                                'pending' => 'Chờ xử lý',
                                                'completed' => 'Hoàn thành',
                                                'cancelled' => 'Đã hủy'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_badge[$order['status']] ?? 'secondary'; ?>">
                                                <?php echo $status_text[$order['status']] ?? $order['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('H:i d/m', strtotime($order['order_date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ và thống kê nâng cao -->
    <div class="row">
        <div class="col-md-12">
            <div class="table-card">
                <h5><i class="fas fa-chart-pie"></i> Tổng quan cửa hàng</h5>
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-3">
                            <div class="revenue text-primary"><?php echo number_format($available_accounts); ?></div>
                            <small class="text-muted">Tài khoản có sẵn</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <div class="revenue text-success"><?php echo number_format($sold_accounts); ?></div>
                            <small class="text-muted">Tài khoản đã bán</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <div class="revenue text-warning"><?php echo number_format($pending_orders); ?></div>
                            <small class="text-muted">Đơn hàng chờ xử lý</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <div class="revenue text-info"><?php echo number_format($total_users); ?></div>
                            <small class="text-muted">Khách hàng</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script>
$(document).ready(function() {
    // Auto refresh stats every 30 seconds
    setInterval(function() {
        location.reload();
    }, 30000);
    
    // Add smooth animations
    $('.stat-card').hover(
        function() { $(this).css('transform', 'translateY(-5px)'); },
        function() { $(this).css('transform', 'translateY(0)'); }
    );
});
</script>
</body>
</html>
