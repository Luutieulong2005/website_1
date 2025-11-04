<?php
if (!isset($_SESSION)) session_start();
include "config/config.php";

// === XỬ LÝ ĐĂNG KÝ USER ===
if (isset($_POST['register_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Mật khẩu xác nhận không khớp!";
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = "Mật khẩu phải có ít nhất 6 ký tự!";
    } else {
        try {
            $db = $config->getConnection();
            $stmt = $db->prepare("SELECT user_id FROM `user` WHERE user_email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Email đã tồn tại!";
            } else {
                $user_id = 'user_' . time() . rand(100, 999);
                $hashed_pwd = md5($password);
                $stmt = $db->prepare("INSERT INTO `user` (user_id, user_name, user_pwd, user_email, user_phone) VALUES (?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$user_id, $name, $hashed_pwd, $email, $phone])) {
                    $_SESSION['success'] = "Đăng ký thành công! Vui lòng đăng nhập.";
                } else {
                    $_SESSION['error'] = "Đăng ký thất bại!";
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Lỗi hệ thống! Vui lòng thử lại.";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// === LẤY THÔNG TIN USER ĐÃ ĐĂNG NHẬP ===
$user_name = '';
if (isset($_SESSION['user_id'])) {
    try {
        $db = $config->getConnection();
        $stmt = $db->prepare("SELECT user_name FROM `user` WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        $user_name = $user['user_name'] ?? 'Khách';
    } catch (Exception $e) {
        error_log("User info error: " . $e->getMessage());
    }
}

// === TÌM KIẾM & LỌC TÀI KHOẢN LIÊN QUÂN ===
$search = $_GET['search'] ?? '';
$cat_filter = $_GET['cat'] ?? '';
$rank_filter = $_GET['rank'] ?? '';
$price_filter = $_GET['price'] ?? '';

try {
    $db = $config->getConnection();
    
    $sql = "SELECT p.*, c.cat_name FROM product p 
            JOIN category c ON p.cat_id = c.cat_id 
            WHERE p.product_quantity > 0";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (p.product_name LIKE ? OR p.product_description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if (!empty($cat_filter)) {
        $sql .= " AND p.cat_id = ?";
        $params[] = $cat_filter;
    }
    if (!empty($rank_filter)) {
        $sql .= " AND p.product_rank = ?";
        $params[] = $rank_filter;
    }
    if (!empty($price_filter)) {
        switch ($price_filter) {
            case 'under500k': $sql .= " AND p.product_price <= 500000"; break;
            case '500k-1m': $sql .= " AND p.product_price BETWEEN 500000 AND 1000000"; break;
            case '1m-2m': $sql .= " AND p.product_price BETWEEN 1000000 AND 2000000"; break;
            case 'over2m': $sql .= " AND p.product_price > 2000000"; break;
        }
    }
    $sql .= " ORDER BY p.product_price ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Product query error: " . $e->getMessage());
    $products = [];
}

// === XỬ LÝ MUA TÀI KHOẢN ===
if (isset($_POST['buy_product'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Vui lòng đăng nhập để mua tài khoản!";
        header("Location: login.php");
        exit();
    }

    $product_id = $_POST['product_id'];
    $quantity = 1;

    try {
        $db = $config->getConnection();
        
        // Kiểm tra tài khoản còn hàng
        $stmt = $db->prepare("SELECT * FROM product WHERE product_id = ? AND product_quantity >= ?");
        $stmt->execute([$product_id, $quantity]);
        $product = $stmt->fetch();

        if ($product) {
            // Tạo đơn hàng
            $order_date = date('Y-m-d H:i:s');
            $stmt = $db->prepare("INSERT INTO `order` (order_date, consignee_name, consignee_phone, consignee_address, order_status, user_id) VALUES (?, ?, ?, ?, 'pending', ?)");
            $stmt->execute([$order_date, $_SESSION['user_name'], '', '', $_SESSION['user_id']]);
            $order_id = $db->lastInsertId();

            // Thêm chi tiết đơn hàng
            $stmt = $db->prepare("INSERT INTO order_detail (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $product_id, $quantity, $product['product_price']]);

            // Cập nhật số lượng
            $db->prepare("UPDATE product SET product_quantity = product_quantity - ? WHERE product_id = ?")
               ->execute([$quantity, $product_id]);

            $_SESSION['success'] = "Đặt mua tài khoản thành công! Chúng tôi sẽ liên hệ giao account trong 5 phút.";
        } else {
            $_SESSION['error'] = "Tài khoản đã bán hoặc không tồn tại!";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Lỗi hệ thống! Vui lòng thử lại.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HTP SHOP - Nick Liên Quân Uy Tín</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css"/>
    <link type="text/css" rel="stylesheet" href="css/slick.css"/>
    <link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link type="text/css" rel="stylesheet" href="css/style.css"/>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #e74c3c;
            --accent: #3498db;
            --success: #27ae60;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 25px;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 2px solid transparent;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
            border-color: var(--accent);
        }
        .product-image {
            text-align: center;
            margin-bottom: 15px;
            flex-grow: 1;
            position: relative;
        }
        .product-image img {
            max-height: 180px;
            width: auto;
            border-radius: 10px;
            object-fit: contain;
        }
        .rank-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #000;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .product-name {
            font-weight: bold;
            font-size: 1.1em;
            color: var(--primary);
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .category {
            display: inline-block;
            background: var(--accent);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            margin-bottom: 10px;
        }
        .account-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin: 10px 0;
            font-size: 0.9em;
        }
        .account-info div {
            margin: 3px 0;
            color: #666;
        }
        .price {
            font-size: 1.4em;
            font-weight: bold;
            color: var(--secondary);
            text-align: center;
            margin: 15px 0;
        }
        .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 0.9em;
            margin-left: 10px;
        }
        .buy-btn {
            width: 100%;
            padding: 12px;
            background: var(--success);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .buy-btn:hover { 
            background: #219a52; 
            transform: translateY(-2px);
        }
        .search-section { 
            background: white; 
            padding: 25px; 
            border-radius: 15px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.1); 
            margin: 20px 0; 
        }
        .header-top { 
            background: linear-gradient(135deg, var(--primary), #34495e); 
            color: white; 
            padding: 12px 0; 
            font-size: 14px; 
            border-bottom: 3px solid var(--secondary); 
        }
        .contact-info { 
            display: flex; 
            justify-content: space-between; 
            flex-wrap: wrap; 
            align-items: center;
        }
        .contact-details, .user-actions { 
            display: flex; 
            gap: 20px; 
            align-items: center; 
            flex-wrap: wrap;
        }
        .user-action-item a { 
            color: white; 
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .user-action-item a:hover { 
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }
        .hero-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
            border-radius: 0 0 20px 20px;
            margin-bottom: 40px;
        }
        .feature-card {
            background: white;
            padding: 30px 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<!-- HEADER TOP -->
<div class="header-top">
    <div class="container">
        <div class="contact-info">
            <div class="contact-details">
                <div><i class="fas fa-store"></i> <strong>HTP SHOP - NICK LIÊN QUÂN UY TÍN</strong></div>
                <div><i class="fas fa-phone"></i> 0878-216-018</div>
                <div><i class="fas fa-envelope"></i> tieulong.work@gmail.com</div>
                <div><i class="fas fa-map-marker-alt"></i> 53 Võ Văn Ngân, TP Thủ Đức</div>
            </div>
            <div class="user-actions">
                <div class="user-action-item"><a href="index.php"><i class="fas fa-home"></i> Trang chủ</a></div>
                <div class="user-action-item"><a href="#search"><i class="fas fa-search"></i> Tìm kiếm</a></div>
                <?php if (!empty($user_name)): ?>
                    <div class="user-action-item"><a href="profile.php"><i class="fas fa-user"></i> <strong><?php echo htmlspecialchars($user_name); ?></strong></a></div>
                    <div class="user-action-item"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></div>
                <?php else: ?>
                    <div class="user-action-item"><a href="#" data-toggle="modal" data-target="#loginModal"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a></div>
                    <div class="user-action-item"><a href="#" data-toggle="modal" data-target="#registerModal"><i class="fas fa-user-plus"></i> Đăng ký</a></div>
                <?php endif; ?>
                <div class="user-action-item"><a href="cart.php"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a></div>
            </div>
        </div>
    </div>
</div>
<!-- /HEADER TOP -->

<!-- HERO BANNER -->
<div class="hero-banner">
    <div class="container">
        <h1 style="font-size: 3em; margin-bottom: 20px;"><i class="fas fa-crown"></i> HTP SHOP LIÊN QUÂN</h1>
        <p style="font-size: 1.3em; margin-bottom: 30px;">Uy tín - Chất lượng - Giá tốt nhất thị trường</p>
        <a href="#products" style="background: var(--secondary); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; display: inline-block; transition: transform 0.3s;">
            MUA NICK NGAY <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>

<!-- FEATURES -->
<div class="container">
    <div class="row" style="margin: 40px 0;">
        <div class="col-md-3">
            <div class="feature-card">
                <div class="feature-icon" style="color: #3498db;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h4>Bảo hành vĩnh viễn</h4>
                <p>Cam kết bảo hành tài khoản trọn đời</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="feature-card">
                <div class="feature-icon" style="color: #f39c12;">
                    <i class="fas fa-bolt"></i>
                </div>
                <h4>Giao dịch nhanh</h4>
                <p>Nhận account ngay sau khi thanh toán</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="feature-card">
                <div class="feature-icon" style="color: #2ecc71;">
                    <i class="fas fa-headset"></i>
                </div>
                <h4>Hỗ trợ 24/7</h4>
                <p>Đội ngũ hỗ trợ nhiệt tình 24 giờ</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="feature-card">
                <div class="feature-icon" style="color: #e74c3c;">
                    <i class="fas fa-gem"></i>
                </div>
                <h4>Acc chất lượng</h4>
                <p>100% tài khoản đã được kiểm duyệt</p>
            </div>
        </div>
    </div>
</div>

<!-- SEARCH -->
<div class="container" id="search">
    <div class="search-section">
        <h3 class="text-center mb-4"><i class="fas fa-search"></i> TÌM KIẾM TÀI KHOẢN LIÊN QUÂN</h3>
        <form method="GET">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, skin, rank..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <select name="cat" class="form-control">
                        <option value="">Loại acc</option>
                        <?php
                        try {
                            $db = $config->getConnection();
                            $cats = $db->query("SELECT * FROM category")->fetchAll();
                            foreach ($cats as $cat): ?>
                                <option value="<?php echo $cat['cat_id']; ?>" <?php echo $cat_filter == $cat['cat_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['cat_name']); ?>
                                </option>
                            <?php endforeach;
                        } catch (Exception $e) {
                            echo '<option>Lỗi tải danh mục</option>';
                        } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="rank" class="form-control">
                        <option value="">Tất cả rank</option>
                        <option value="Đồng" <?php echo $rank_filter == 'Đồng' ? 'selected' : ''; ?>>Đồng</option>
                        <option value="Bạc" <?php echo $rank_filter == 'Bạc' ? 'selected' : ''; ?>>Bạc</option>
                        <option value="Vàng" <?php echo $rank_filter == 'Vàng' ? 'selected' : ''; ?>>Vàng</option>
                        <option value="Bạch Kim" <?php echo $rank_filter == 'Bạch Kim' ? 'selected' : ''; ?>>Bạch Kim</option>
                        <option value="Kim Cương" <?php echo $rank_filter == 'Kim Cương' ? 'selected' : ''; ?>>Kim Cương</option>
                        <option value="Cao Thủ" <?php echo $rank_filter == 'Cao Thủ' ? 'selected' : ''; ?>>Cao Thủ</option>
                        <option value="Thách Đấu" <?php echo $rank_filter == 'Thách Đấu' ? 'selected' : ''; ?>>Thách Đấu</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="price" class="form-control">
                        <option value="">Mức giá</option>
                        <option value="under500k" <?php echo $price_filter == 'under500k' ? 'selected' : ''; ?>>Dưới 500K</option>
                        <option value="500k-1m" <?php echo $price_filter == '500k-1m' ? 'selected' : ''; ?>>500K - 1 Triệu</option>
                        <option value="1m-2m" <?php echo $price_filter == '1m-2m' ? 'selected' : ''; ?>>1 - 2 Triệu</option>
                        <option value="over2m" <?php echo $price_filter == 'over2m' ? 'selected' : ''; ?>>Trên 2 Triệu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Tìm kiếm</button>
                    <a href="index.php" class="btn btn-outline-secondary w-100 mt-2"><i class="fas fa-refresh"></i> Xóa lọc</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- PRODUCTS -->
<div class="section" id="products">
    <div class="container">
        <div class="section-title text-center">
            <h3><i class="fas fa-fire"></i> TÀI KHOẢN NỔI BẬT</h3>
            <p class="text-muted">Danh sách nick Liên Quân chất lượng, giá tốt</p>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php if (empty($products)): ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Không tìm thấy tài khoản nào phù hợp.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="product-card">
                            <div class="product-image">
                                <img src="images/<?php echo htmlspecialchars($p['product_img']); ?>" 
                                     alt="<?php echo htmlspecialchars($p['product_name']); ?>"
                                     onerror="this.src='https://via.placeholder.com/300x200?text=Liên+Quân'">
                                <div class="rank-badge"><?php echo htmlspecialchars($p['product_rank'] ?? 'Chưa rank'); ?></div>
                            </div>
                            <div class="category"><?php echo htmlspecialchars($p['cat_name']); ?></div>
                            <div class="product-name"><?php echo htmlspecialchars($p['product_name']); ?></div>
                            
                            <div class="account-info">
                                <div><i class="fas fa-trophy"></i> <strong>Rank:</strong> <?php echo htmlspecialchars($p['product_rank'] ?? 'Chưa xác định'); ?></div>
                                <div><i class="fas fa-user"></i> <strong>Tướng:</strong> <?php echo htmlspecialchars($p['product_hero_count'] ?? '?'); ?></div>
                                <div><i class="fas fa-palette"></i> <strong>Skin:</strong> <?php echo htmlspecialchars($p['product_skin_count'] ?? '?'); ?></div>
                            </div>
                            
                            <div class="price">
                                <?php echo number_format($p['product_price']); ?> ₫
                                <?php if (!empty($p['product_original_price']) && $p['product_original_price'] > $p['product_price']): ?>
                                    <span class="original-price"><?php echo number_format($p['product_original_price']); ?> ₫</span>
                                <?php endif; ?>
                            </div>
                            
                            <button class="buy-btn" onclick="openBuyModal(<?php echo $p['product_id']; ?>)">
                                <i class="fas fa-shopping-cart"></i> MUA NGAY
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL MUA HÀNG -->
<div class="modal fade" id="buyModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-shopping-cart"></i> Xác nhận mua tài khoản</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="buy_product_id">
                    <p><strong>Tài khoản:</strong> <span id="buy_product_name"></span></p>
                    <p><strong>Rank:</strong> <span id="buy_product_rank"></span></p>
                    <p><strong>Giá:</strong> <span id="buy_product_price" class="text-danger font-weight-bold"></span></p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Sau khi xác nhận, chúng tôi sẽ liên hệ giao account trong vòng 5 phút.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="buy_product" class="btn btn-success btn-lg">
                        <i class="fas fa-check"></i> XÁC NHẬN MUA
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL ĐĂNG KÝ -->
<div class="modal fade" id="registerModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h4><i class="fas fa-user-plus"></i> Đăng ký tài khoản</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" name="name" class="form-control" placeholder="Họ tên" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="phone" class="form-control" placeholder="Số điện thoại" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required minlength="6">
                    </div>
                    <div class="form-group">
                        <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="register_user" class="btn btn-success btn-block">
                        <i class="fas fa-user-plus"></i> ĐĂNG KÝ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer style="background: var(--primary); color: white; padding: 40px 0; margin-top: 50px;">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5><i class="fas fa-store"></i> HTP SHOP LIÊN QUÂN</h5>
                <p>Địa chỉ: 53 Võ Văn Ngân, TP Thủ Đức</p>
                <p>Hotline: 0878-216-018</p>
                <p>Email: tieulong.work@gmail.com</p>
            </div>
            <div class="col-md-4">
                <h5>DỊCH VỤ</h5>
                <p><a href="#" style="color: white;">Bán acc Liên Quân</a></p>
                <p><a href="#" style="color: white;">Nhận order acc</a></p>
                <p><a href="#" style="color: white;">Tư vấn tài khoản</a></p>
            </div>
            <div class="col-md-4">
                <h5>HỖ TRỢ</h5>
                <p>Bảo hành vĩnh viễn</p>
                <p>Hỗ trợ 24/7</p>
                <p>Giao dịch an toàn</p>
            </div>
        </div>
        <hr style="background: rgba(255,255,255,0.2);">
        <div class="text-center">
            <p>&copy; 2024 HTP SHOP LIÊN QUÂN. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
function openBuyModal(id) {
    // Trong thực tế, bạn sẽ gọi API để lấy thông tin sản phẩm
    // Ở đây tôi giả lập dữ liệu
    const product = {
        id: id,
        name: document.querySelector(`[onclick="openBuyModal(${id})"]`).closest('.product-card').querySelector('.product-name').textContent,
        rank: document.querySelector(`[onclick="openBuyModal(${id})"]`).closest('.product-card').querySelector('.rank-badge').textContent,
        price: document.querySelector(`[onclick="openBuyModal(${id})"]`).closest('.product-card').querySelector('.price').textContent.trim()
    };
    
    $('#buy_product_id').val(product.id);
    $('#buy_product_name').text(product.name);
    $('#buy_product_rank').text(product.rank);
    $('#buy_product_price').text(product.price);
    $('#buyModal').modal('show');
}

$(document).ready(function() {
    // Auto hide alerts after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 5000);
    
    // Smooth scroll
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $($(this).attr('href')).offset().top - 80
        }, 500);
    });
});
</script>
</body>
</html>
