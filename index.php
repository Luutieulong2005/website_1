<?php
if (!isset($_SESSION)) session_start();
include "config/config.php";
include "include/function.php";
spl_autoload_register("loadClass");

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
        $stmt = $pdo->prepare("SELECT user_id FROM `user` WHERE user_email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email đã tồn tại!";
        } else {
            $user_id = 'user_' . time() . rand(100, 999);
            $hashed_pwd = md5($password); // DB dùng MD5
            $stmt = $pdo->prepare("INSERT INTO `user` (user_id, user_name, user_pwd, user_email, user_phone) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $name, $hashed_pwd, $email, $phone])) {
                $_SESSION['success'] = "Đăng ký thành công! Vui lòng đăng nhập.";
            } else {
                $_SESSION['error'] = "Đăng ký thất bại!";
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// === LẤY THÔNG TIN USER ĐÃ ĐĂNG NHẬP ===
$user_name = '';
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT user_name FROM `user` WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $user_name = $user['user_name'] ?? 'Khách';
}

// === TÌM KIẾM & LỌC SẢN PHẨM ===
$search = $_GET['search'] ?? '';
$cat_filter = $_GET['cat'] ?? '';
$price_filter = $_GET['price'] ?? '';

$sql = "SELECT p.*, c.cat_name FROM product p JOIN category c ON p.cat_id = c.cat_id WHERE p.product_quantity > 0";
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
if (!empty($price_filter)) {
    switch ($price_filter) {
        case 'under2m': $sql .= " AND p.product_price <= 2000000"; break;
        case '2m-5m': $sql .= " AND p.product_price BETWEEN 2000000 AND 5000000"; break;
        case '5m-10m': $sql .= " AND p.product_price BETWEEN 5000000 AND 10000000"; break;
        case 'over10m': $sql .= " AND p.product_price > 10000000"; break;
    }
}
$sql .= " ORDER BY p.product_price ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// === XỬ LÝ MUA HÀNG ===
if (isset($_POST['buy_product'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Vui lòng đăng nhập để mua hàng!";
        header("Location: login.php");
        exit();
    }

    $product_id = $_POST['product_id'];
    $quantity = 1;

    // Kiểm tra sản phẩm tồn tại và còn hàng
    $stmt = $pdo->prepare("SELECT * FROM product WHERE product_id = ? AND product_quantity >= ?");
    $stmt->execute([$product_id, $quantity]);
    $product = $stmt->fetch();

    if ($product) {
        // Tạo đơn hàng
        $order_date = date('Y-m-d');
        $stmt = $pdo->prepare("INSERT INTO `order` (order_date, consignee_name, consignee_phone, consignee_address, order_status, user_id) VALUES (?, ?, ?, ?, 0, ?)");
        $stmt->execute([$order_date, $_SESSION['user_name'], '', '', $_SESSION['user_id']]);
        $order_id = $pdo->lastInsertId();

        // Thêm chi tiết đơn hàng
        $stmt = $pdo->prepare("INSERT INTO order_detail (order_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$order_id, $product_id, $quantity]);

        // Cập nhật số lượng
        $pdo->prepare("UPDATE product SET product_quantity = product_quantity - ? WHERE product_id = ?")
           ->execute([$quantity, $product_id]);

        $_SESSION['message'] = "Đặt hàng thành công! Chúng tôi sẽ liên hệ sớm.";
    } else {
        $_SESSION['error'] = "Sản phẩm hết hàng hoặc không tồn tại!";
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
    <title>Computer Store - Cửa hàng linh kiện máy tính</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css"/>
    <link type="text/css" rel="stylesheet" href="css/slick.css"/>
    <link type="text/css" rel="stylesheet" href="css/font-awesome.min.css">
    <link type="text/css" rel="stylesheet" href="css/style.css"/>
    <style>
        .product-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 25px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .product-image {
            text-align: center;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        .product-image img {
            max-height: 180px;
            width: auto;
            border-radius: 8px;
            object-fit: contain;
        }
        .product-name {
            font-weight: bold;
            font-size: 1.1em;
            color: #2c3e50;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .category {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            margin-bottom: 10px;
        }
        .price {
            font-size: 1.4em;
            font-weight: bold;
            color: #e74c3c;
            text-align: center;
            margin: 15px 0;
        }
        .buy-btn {
            width: 100%;
            padding: 10px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        .buy-btn:hover { background: #219a52; }
        .search-section { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin: 20px 0; }
        .header-top { background: linear-gradient(135deg, #2c3e50, #34495e); color: white; padding: 12px 0; font-size: 14px; border-bottom: 3px solid #e74c3c; }
        .contact-info { display: flex; justify-content: space-between; flex-wrap: wrap; }
        .contact-details, .user-actions { display: flex; gap: 20px; align-items: center; }
        .user-action-item a { color: white; text-decoration: none; }
        .user-action-item:hover { background: rgba(255,255,255,0.1); padding: 5px 10px; border-radius: 5px; }
    </style>
</head>
<body>

<!-- HEADER TOP -->
<div class="header-top">
    <div class="container">
        <div class="contact-info">
            <div class="contact-details">
                <div><strong>COMPUTER STORE</strong></div>
                <div>0938 926 315</div>
                <div>support@computerstore.vn</div>
                <div>53 Võ Văn Ngân, TP Thủ Đức</div>
            </div>
            <div class="user-actions">
                <div class="user-action-item"><a href="index.php">Trang chủ</a></div>
                <div class="user-action-item"><a href="#search">Tìm kiếm</a></div>
                <?php if (!empty($user_name)): ?>
                    <div class="user-action-item"><strong>Xin chào, <?php echo htmlspecialchars($user_name); ?></strong></div>
                    <div class="user-action-item"><a href="logout.php">Đăng xuất</a></div>
                <?php else: ?>
                    <div class="user-action-item"><a href="login.php">Đăng nhập</a></div>
                <?php endif; ?>
                <div class="user-action-item"><a href="admins/login.php">Admin</a></div>
            </div>
        </div>
    </div>
</div>
<!-- /HEADER TOP -->

<?php include_once 'subpage/navigation.html'; ?>

<!-- BANNER -->
<div id="banner" style="background: linear-gradient(135deg, #667eea, #764ba2); padding: 80px 0; color: white; text-align: center;">
    <div class="container">
        <h1>COMPUTER STORE</h1>
        <p>Linh kiện chính hãng - Giá tốt - Bảo hành dài hạn</p>
    </div>
</div>

<!-- SEARCH -->
<div class="container">
    <div class="search-section">
        <form method="GET">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm CPU, RAM, VGA..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="cat" class="form-control">
                        <option value="">Tất cả danh mục</option>
                        <?php
                        $cats = $pdo->query("SELECT * FROM category")->fetchAll();
                        foreach ($cats as $cat): ?>
                            <option value="<?php echo $cat['cat_id']; ?>" <?php echo $cat_filter == $cat['cat_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['cat_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="price" class="form-control">
                        <option value="">Tất cả giá</option>
                        <option value="under2m" <?php echo $price_filter == 'under2m' ? 'selected' : ''; ?>>Dưới 2 triệu</option>
                        <option value="2m-5m" <?php echo $price_filter == '2m-5m' ? 'selected' : ''; ?>>2 - 5 triệu</option>
                        <option value="5m-10m" <?php echo $price_filter == '5m-10m' ? 'selected' : ''; ?>>5 - 10 triệu</option>
                        <option value="over10m" <?php echo $price_filter == 'over10m' ? 'selected' : ''; ?>>Trên 10 triệu</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- PRODUCTS -->
<div class="section">
    <div class="container">
        <div class="section-title text-center">
            <h3>SẢN PHẨM NỔI BẬT</h3>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($products as $p): ?>
                <div class="col-md-4">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="images/<?php echo htmlspecialchars($p['product_img']); ?>" 
                                 alt="<?php echo htmlspecialchars($p['product_name']); ?>">
                        </div>
                        <div class="category"><?php echo htmlspecialchars($p['cat_name']); ?></div>
                        <div class="product-name"><?php echo htmlspecialchars($p['product_name']); ?></div>
                        <div class="price"><?php echo number_format($p['product_price']); ?> VNĐ</div>
                        <button class="buy-btn" onclick="openBuyModal(<?php echo $p['product_id']; ?>)">MUA NGAY</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- MODAL MUA HÀNG -->
<div class="modal fade" id="buyModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h4 class="modal-title">Xác nhận đặt hàng</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="buy_product_id">
                    <p><strong>Sản phẩm:</strong> <span id="buy_product_name"></span></p>
                    <p><strong>Giá:</strong> <span id="buy_product_price"></span> VNĐ</p>
                    <p class="text-muted">Chúng tôi sẽ liên hệ xác nhận trong 5 phút.</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="buy_product" class="btn btn-success">XÁC NHẬN</button>
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
                    <h4>Đăng ký tài khoản</h4>
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
                    <button type="submit" name="register_user" class="btn btn-success">Đăng ký</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once 'subpage/footer.html'; ?>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
function openBuyModal(id) {
    $.get('', {ajax: 'product', id: id}, function(data) {
        const p = JSON.parse(data);
        $('#buy_product_id').val(p.product_id);
        $('#buy_product_name').text(p.product_name);
        $('#buy_product_price').text(p.product_price.toLocaleString());
        $('#buyModal').modal('show');
    });
}
$(document).ready(function() {
    <?php if (isset($_SESSION['error']) || isset($_SESSION['success'])): ?>
        setTimeout(() => $('.alert').fadeOut(), 3000);
    <?php endif; ?>
});
</script>
</body>
</html>
