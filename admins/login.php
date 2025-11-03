<?php
session_start();
include "config/config.php";
include "include/function.php";
spl_autoload_register("loadClass");

$error = '';
$success = '';

// Xử lý đăng nhập USER
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'user'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        header("Location: index.php");
        exit();
    } else {
        $error = "Email hoặc mật khẩu không đúng!";
    }
}

// Xử lý đăng ký USER
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Kiểm tra email tồn tại
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        $error = "Email đã tồn tại!";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'user')");
        
        if ($stmt->execute([$name, $email, $phone, $hashed_password])) {
            $success = "Đăng ký thành công! Vui lòng đăng nhập.";
        } else {
            $error = "Đăng ký thất bại!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Shop Nick Liên Quân</title>
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link type="text/css" rel="stylesheet" href="css/style.css"/>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
        }
        .login-tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }
        .login-tab {
            flex: 1;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: 600;
            transition: all 0.3s;
        }
        .login-tab.active {
            border-bottom: 3px solid #e74c3c;
            color: #e74c3c;
        }
        .login-form {
            display: none;
        }
        .login-form.active {
            display: block;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn-login {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border: none;
            color: white;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }
        .btn-register {
            background: linear-gradient(135deg, #27ae60, #219a52);
            border: none;
            color: white;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <!-- HEADER TOP GIỐNG TRANG CHỦ -->
    <div class="header-top" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000;">
        <div class="container">
            <div class="contact-info">
                <div class="contact-details">
                    <div><strong>HTP - SHOP NICK LIÊN QUÂN</strong></div>
                    <div>📞 0878-216-018</div>
                    <div>📧 tieulong.work@gmail.com</div>
                    <div>📍 53 Võ Văn Ngân, TP Thủ Đức</div>
                </div>
                <div class="user-actions">
                    <div class="user-action-item"><a href="index.php">🏠 Trang chủ</a></div>
                    <div class="user-action-item"><a href="#search">🔍 Tìm kiếm</a></div>
                    <div class="user-action-item"><a href="#orders">📦 Đơn hàng</a></div>
                    
                    <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_name'])): ?>
                        <div class="user-action-item"><strong>👋 <?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></div>
                        <div class="user-action-item"><a href="logout.php">🚪 Đăng xuất</a></div>
                    <?php else: ?>
                        <div class="user-action-item"><a href="login.php">🔐 Đăng nhập/Đăng ký</a></div>
                    <?php endif; ?>
                    
                    <div class="user-action-item"><a href="#wishlist">❤️ Yêu thích</a></div>
                    <div class="user-action-item"><a href="#cart">🛒 Giỏ hàng</a></div>
                </div>
            </div>
        </div>
    </div>

    <div class="login-container" style="margin-top: 80px;">
        <div class="login-header">
            <h3>🔐 TÀI KHOẢN NGƯỜI DÙNG</h3>
            <p>Đăng nhập hoặc đăng ký tài khoản mới</p>
        </div>

        <div class="login-tabs">
            <div class="login-tab active" onclick="showTab('login')">ĐĂNG NHẬP</div>
            <div class="login-tab" onclick="showTab('register')">ĐĂNG KÝ</div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Form Đăng nhập -->
        <form method="POST" action="" class="login-form active" id="loginForm">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" placeholder="user@gmail.com" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu:</label>
                <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
            </div>
            <button type="submit" name="login" class="btn-login">ĐĂNG NHẬP</button>
            
            <div class="text-center mt-3">
                <p class="text-muted">Tài khoản demo: <strong>user@gmail.com</strong> / <strong>password</strong></p>
            </div>
        </form>

        <!-- Form Đăng ký -->
        <form method="POST" action="" class="login-form" id="registerForm">
            <div class="form-group">
                <label>Họ và tên:</label>
                <input type="text" name="name" class="form-control" placeholder="Nguyễn Văn A" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" placeholder="user@gmail.com" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại:</label>
                <input type="tel" name="phone" class="form-control" placeholder="0938123456" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu:</label>
                <input type="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required minlength="6">
            </div>
            <div class="form-group">
                <label>Xác nhận mật khẩu:</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" required>
            </div>
            <button type="submit" name="register" class="btn-register">ĐĂNG KÝ TÀI KHOẢN</button>
        </form>

        <div class="text-center mt-4">
            <p><a href="index.php" style="color: #667eea; text-decoration: none;">← Quay lại trang chủ</a></p>
            <p class="text-muted small">Là nhân viên? <a href="admins/login.php" style="color: #e74c3c;">Đăng nhập Admin →</a></p>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Ẩn tất cả form
            document.querySelectorAll('.login-form').forEach(form => {
                form.classList.remove('active');
            });
            
            // Xóa active tất cả tab
            document.querySelectorAll('.login-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Hiển thị form được chọn
            document.getElementById(tabName + 'Form').classList.add('active');
            
            // Active tab được chọn
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
