v<?php
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
            
            // Kiểm tra email tồn tại trong bảng user
            $stmt = $db->prepare("SELECT user_id FROM `user` WHERE user_email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Email đã tồn tại!";
            } else {
                $user_id = 'user_' . time() . rand(100, 999);
                $hashed_pwd = md5($password);
                
                // Thêm vào bảng user
                $stmt = $db->prepare("INSERT INTO `user` (user_id, user_name, user_pwd, user_email, user_phone) VALUES (?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$user_id, $name, $hashed_pwd, $email, $phone])) {
                    $_SESSION['success'] = "Đăng ký thành công! Vui lòng đăng nhập.";
                } else {
                    $_SESSION['error'] = "Đăng ký thất bại! Vui lòng thử lại.";
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Lỗi hệ thống! Vui lòng thử lại.";
            error_log("Register error: " . $e->getMessage());
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// === XỬ LÝ ĐĂNG NHẬP USER ===
if (isset($_POST['login_user'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
    } else {
        try {
            $db = $config->getConnection();
            $hashed_pwd = md5($password);
            
            // Kiểm tra trong bảng user
            $stmt = $db->prepare("SELECT user_id, user_name, user_email FROM `user` WHERE user_email = ? AND user_pwd = ?");
            $stmt->execute([$email, $hashed_pwd]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['user_email'] = $user['user_email'];
                $_SESSION['user_role'] = 'user';
                
                $_SESSION['success'] = "Đăng nhập thành công!";
            } else {
                $_SESSION['error'] = "Email hoặc mật khẩu không đúng!";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Lỗi hệ thống! Vui lòng thử lại.";
            error_log("Login error: " . $e->getMessage());
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// === XỬ LÝ ĐĂNG XUẤT ===
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// === LẤY THÔNG TIN USER ĐÃ ĐĂNG NHẬP ===
$user_name = '';
$user_role = '';
if (isset($_SESSION['user_id'])) {
    $user_name = $_SESSION['user_name'] ?? 'Khách';
    $user_role = $_SESSION['user_role'] ?? 'user';
}

// === TÌM KIẾM & LỌC TÀI KHOẢN LIÊN QUÂN ===
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$rank_filter = $_GET['rank'] ?? '';
$price_filter = $_GET['price'] ?? '';

try {
    $db = $config->getConnection();
    
    // Lấy danh sách tài khoản từ bảng accounts
    $sql = "SELECT a.*, c.cat_name 
            FROM accounts a 
            LEFT JOIN category c ON a.rank LIKE CONCAT('%', c.cat_name, '%')
            WHERE a.status = 'available'";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (a.username LIKE ? OR a.description LIKE ? OR a.rank LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if (!empty($category_filter)) {
        $sql .= " AND c.cat_id = ?";
        $params[] = $category_filter;
    }
    if (!empty($rank_filter)) {
        $sql .= " AND a.rank LIKE ?";
        $params[] = "%$rank_filter%";
    }
    if (!empty($price_filter)) {
        switch ($price_filter) {
            case 'under500k': $sql .= " AND a.price <= 500000"; break;
            case '500k-1m': $sql .= " AND a.price BETWEEN 500000 AND 1000000"; break;
            case '1m-2m': $sql .= " AND a.price BETWEEN 1000000 AND 2000000"; break;
            case 'over2m': $sql .= " AND a.price > 2000000"; break;
        }
    }
    $sql .= " ORDER BY a.price ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $accounts = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Accounts query error: " . $e->getMessage());
    $accounts = [];
}

// === XỬ LÝ MUA TÀI KHOẢN ===
if (isset($_POST['buy_account'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Vui lòng đăng nhập để mua tài khoản!";
        header("Location: " . $_SERVER['PHP_SELF'] . "#login");
        exit();
    }

    $account_id = $_POST['account_id'];

    try {
        $db = $config->getConnection();
        
        // Kiểm tra tài khoản còn hàng
        $stmt = $db->prepare("SELECT * FROM accounts WHERE id = ? AND status = 'available'");
        $stmt->execute([$account_id]);
        $account = $stmt->fetch();

        if ($account) {
            // Tạo đơn hàng trong bảng orders
            $stmt = $db->prepare("INSERT INTO orders (account_id, customer_name, customer_email, customer_phone, total_amount, status) 
                                 VALUES (?, ?, ?, ?, ?, 'pending')");
            
            $stmt->execute([
                $account_id,
                $_SESSION['user_name'],
                $_SESSION['user_email'],
                '', // Có thể thêm trường phone trong form
                $account['price']
            ]);

            // Cập nhật trạng thái tài khoản thành đã bán
            $db->prepare("UPDATE accounts SET status = 'sold' WHERE id = ?")
               ->execute([$account_id]);

            $_SESSION['success'] = "Đặt mua tài khoản thành công! Chúng tôi sẽ liên hệ giao account trong 5 phút.";
        } else {
            $_SESSION['error'] = "Tài khoản đã bán hoặc không tồn tại!";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Lỗi hệ thống! Vui lòng thử lại.";
        error_log("Buy account error: " . $e->getMessage());
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTP SHOP - Nick Liên Quân Uy Tín</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css"/>
    <link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS giữ nguyên như trước */
        :root {
            --primary: #2c3e50;
            --secondary: #e74c3c;
            --accent: #3498db;
            --success: #27ae60;
        }
        
        .account-card {
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
        /* ... (CSS khác giữ nguyên) ... */
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
                    <div class="user-action-item">
                        <a href="profile.php">
                            <i class="fas fa-user"></i> 
                            <strong><?php echo htmlspecialchars($user_name); ?></strong>
                        </a>
                    </div>
                    <div class="user-action-item"><a href="?logout=1"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></div>
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

<!-- ... (Phần còn lại của HTML giữ nguyên) ... -->

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
                        <label>Họ tên *</label>
                        <input type="text" name="name" class="form-control" placeholder="Nhập họ tên" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="Nhập email" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại *</label>
                        <input type="tel" name="phone" class="form-control" placeholder="Nhập số điện thoại" required>
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu *</label>
                        <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu (ít nhất 6 ký tự)" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Xác nhận mật khẩu *</label>
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

<!-- MODAL ĐĂNG NHẬP -->
<div class="modal fade" id="loginModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h4><i class="fas fa-sign-in-alt"></i> Đăng nhập</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Nhập email" required>
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu</label>
                        <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="login_user" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> ĐĂNG NHẬP
                    </button>
                    <div class="text-center mt-2 w-100">
                        <small>Chưa có tài khoản? <a href="#" data-dismiss="modal" data-toggle="modal" data-target="#registerModal">Đăng ký ngay</a></small>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ... (Phần còn lại của HTML) ... -->

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
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
    
    // Show login modal if there's login error
    <?php if (isset($_POST['login_user']) && isset($_SESSION['error'])): ?>
        $('#loginModal').modal('show');
    <?php endif; ?>
    
    // Show register modal if there's register error
    <?php if (isset($_POST['register_user']) && isset($_SESSION['error'])): ?>
        $('#registerModal').modal('show');
    <?php endif; ?>
    
    // Show success message in modal
    <?php if (isset($_POST['register_user']) && isset($_SESSION['success'])): ?>
        $('#registerModal').modal('hide');
    <?php endif; ?>
    
    <?php if (isset($_POST['login_user']) && isset($_SESSION['success'])): ?>
        $('#loginModal').modal('hide');
    <?php endif; ?>
});
</script>
</body>
</html><?php
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
            
            // Kiểm tra email tồn tại trong bảng user
            $stmt = $db->prepare("SELECT user_id FROM `user` WHERE user_email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Email đã tồn tại!";
            } else {
                $user_id = 'user_' . time() . rand(100, 999);
                $hashed_pwd = md5($password);
                
                // Thêm vào bảng user
                $stmt = $db->prepare("INSERT INTO `user` (user_id, user_name, user_pwd, user_email, user_phone) VALUES (?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$user_id, $name, $hashed_pwd, $email, $phone])) {
                    $_SESSION['success'] = "Đăng ký thành công! Vui lòng đăng nhập.";
                } else {
                    $_SESSION['error'] = "Đăng ký thất bại! Vui lòng thử lại.";
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Lỗi hệ thống! Vui lòng thử lại.";
            error_log("Register error: " . $e->getMessage());
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// === XỬ LÝ ĐĂNG NHẬP USER ===
if (isset($_POST['login_user'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
    } else {
        try {
            $db = $config->getConnection();
            $hashed_pwd = md5($password);
            
            // Kiểm tra trong bảng user
            $stmt = $db->prepare("SELECT user_id, user_name, user_email FROM `user` WHERE user_email = ? AND user_pwd = ?");
            $stmt->execute([$email, $hashed_pwd]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['user_email'] = $user['user_email'];
                $_SESSION['user_role'] = 'user';
                
                $_SESSION['success'] = "Đăng nhập thành công!";
            } else {
                $_SESSION['error'] = "Email hoặc mật khẩu không đúng!";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Lỗi hệ thống! Vui lòng thử lại.";
            error_log("Login error: " . $e->getMessage());
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// === XỬ LÝ ĐĂNG XUẤT ===
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// === LẤY THÔNG TIN USER ĐÃ ĐĂNG NHẬP ===
$user_name = '';
$user_role = '';
if (isset($_SESSION['user_id'])) {
    $user_name = $_SESSION['user_name'] ?? 'Khách';
    $user_role = $_SESSION['user_role'] ?? 'user';
}

// === TÌM KIẾM & LỌC TÀI KHOẢN LIÊN QUÂN ===
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$rank_filter = $_GET['rank'] ?? '';
$price_filter = $_GET['price'] ?? '';

try {
    $db = $config->getConnection();
    
    // Lấy danh sách tài khoản từ bảng accounts
    $sql = "SELECT a.*, c.cat_name 
            FROM accounts a 
            LEFT JOIN category c ON a.rank LIKE CONCAT('%', c.cat_name, '%')
            WHERE a.status = 'available'";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (a.username LIKE ? OR a.description LIKE ? OR a.rank LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if (!empty($category_filter)) {
        $sql .= " AND c.cat_id = ?";
        $params[] = $category_filter;
    }
    if (!empty($rank_filter)) {
        $sql .= " AND a.rank LIKE ?";
        $params[] = "%$rank_filter%";
    }
    if (!empty($price_filter)) {
        switch ($price_filter) {
            case 'under500k': $sql .= " AND a.price <= 500000"; break;
            case '500k-1m': $sql .= " AND a.price BETWEEN 500000 AND 1000000"; break;
            case '1m-2m': $sql .= " AND a.price BETWEEN 1000000 AND 2000000"; break;
            case 'over2m': $sql .= " AND a.price > 2000000"; break;
        }
    }
    $sql .= " ORDER BY a.price ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $accounts = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Accounts query error: " . $e->getMessage());
    $accounts = [];
}

// === XỬ LÝ MUA TÀI KHOẢN ===
if (isset($_POST['buy_account'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Vui lòng đăng nhập để mua tài khoản!";
        header("Location: " . $_SERVER['PHP_SELF'] . "#login");
        exit();
    }

    $account_id = $_POST['account_id'];

    try {
        $db = $config->getConnection();
        
        // Kiểm tra tài khoản còn hàng
        $stmt = $db->prepare("SELECT * FROM accounts WHERE id = ? AND status = 'available'");
        $stmt->execute([$account_id]);
        $account = $stmt->fetch();

        if ($account) {
            // Tạo đơn hàng trong bảng orders
            $stmt = $db->prepare("INSERT INTO orders (account_id, customer_name, customer_email, customer_phone, total_amount, status) 
                                 VALUES (?, ?, ?, ?, ?, 'pending')");
            
            $stmt->execute([
                $account_id,
                $_SESSION['user_name'],
                $_SESSION['user_email'],
                '', // Có thể thêm trường phone trong form
                $account['price']
            ]);

            // Cập nhật trạng thái tài khoản thành đã bán
            $db->prepare("UPDATE accounts SET status = 'sold' WHERE id = ?")
               ->execute([$account_id]);

            $_SESSION['success'] = "Đặt mua tài khoản thành công! Chúng tôi sẽ liên hệ giao account trong 5 phút.";
        } else {
            $_SESSION['error'] = "Tài khoản đã bán hoặc không tồn tại!";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Lỗi hệ thống! Vui lòng thử lại.";
        error_log("Buy account error: " . $e->getMessage());
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTP SHOP - Nick Liên Quân Uy Tín</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css"/>
    <link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS giữ nguyên như trước */
        :root {
            --primary: #2c3e50;
            --secondary: #e74c3c;
            --accent: #3498db;
            --success: #27ae60;
        }
        
        .account-card {
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
        /* ... (CSS khác giữ nguyên) ... */
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
                    <div class="user-action-item">
                        <a href="profile.php">
                            <i class="fas fa-user"></i> 
                            <strong><?php echo htmlspecialchars($user_name); ?></strong>
                        </a>
                    </div>
                    <div class="user-action-item"><a href="?logout=1"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></div>
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

<!-- ... (Phần còn lại của HTML giữ nguyên) ... -->

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
                        <label>Họ tên *</label>
                        <input type="text" name="name" class="form-control" placeholder="Nhập họ tên" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="Nhập email" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại *</label>
                        <input type="tel" name="phone" class="form-control" placeholder="Nhập số điện thoại" required>
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu *</label>
                        <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu (ít nhất 6 ký tự)" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Xác nhận mật khẩu *</label>
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

<!-- MODAL ĐĂNG NHẬP -->
<div class="modal fade" id="loginModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h4><i class="fas fa-sign-in-alt"></i> Đăng nhập</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Nhập email" required>
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu</label>
                        <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="login_user" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> ĐĂNG NHẬP
                    </button>
                    <div class="text-center mt-2 w-100">
                        <small>Chưa có tài khoản? <a href="#" data-dismiss="modal" data-toggle="modal" data-target="#registerModal">Đăng ký ngay</a></small>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ... (Phần còn lại của HTML) ... -->

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
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
    
    // Show login modal if there's login error
    <?php if (isset($_POST['login_user']) && isset($_SESSION['error'])): ?>
        $('#loginModal').modal('show');
    <?php endif; ?>
    
    // Show register modal if there's register error
    <?php if (isset($_POST['register_user']) && isset($_SESSION['error'])): ?>
        $('#registerModal').modal('show');
    <?php endif; ?>
    
    // Show success message in modal
    <?php if (isset($_POST['register_user']) && isset($_SESSION['success'])): ?>
        $('#registerModal').modal('hide');
    <?php endif; ?>
    
    <?php if (isset($_POST['login_user']) && isset($_SESSION['success'])): ?>
        $('#loginModal').modal('hide');
    <?php endif; ?>
});
</script>
</body>
</html>
