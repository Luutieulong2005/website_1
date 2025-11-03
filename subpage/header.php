<?php
session_start();
$user_name = $_SESSION['user_name'] ?? '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTP - SHOP NICK LIÊN QUÂN</title>
    <style>
        .header-top {
            background: #2c3e50;
            color: white;
            padding: 10px 0;
            font-size: 14px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        .contact-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .contact-details {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .contact-details div {
            margin-right: 15px;
        }
        .user-action {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .user-action-item a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }
        .user-action-item a:hover {
            color: #3498db;
        }
        strong {
            color: #e74c3c;
        }
    </style>
</head>
<body onload="SetDefault();">
    <!-- HEADER TOP MOT -->
    <div class="header-top">
        <div class="container">
            <div class="contact-info">
                <div class="contact-details">
                    <div><strong>HTP - SHOP NICK LIÊN QUÂN</strong></div>
                    <div>📞 0878-216-018</div>
                    <div>📧 tieulong.work@gmail.com</div>
                    <div>📍 53 Võ Văn Ngân, TP Thủ Đức</div>
                </div>
                <div class="user-action">
                    <div class="user-action-item"><a href="index.php">Trang chủ</a></div>
                    <div class="user-action-item"><a href="research.php">Tìm kiếm</a></div>
                    <div class="user-action-item"><a href="orders.php">Đơn hàng</a></div>
                    
                    <?php if (!empty($user_name)): ?>
                        <div class="user-action-item"><strong><?php echo htmlspecialchars($user_name); ?></strong></div>
                        <div class="user-action-item"><a href="admins/logout.php">Đăng xuất</a></div>
                    <?php else: ?>
                        <div class="user-action-item"><a href="admins/login.php">Đăng nhập/Đăng ký</a></div>
                    <?php endif; ?>
                    
                    <div class="user-action-item"><a href="wishlist.php">Yêu thích</a></div>
                    <div class="user-action-item"><a href="cart.php">Giỏ hàng</a></div>
                </div>
            </div>
        </div>
    </div>
    <!-- /HEADER TOP -->
