<?php
session_start();
include "config/config.php";
include "include/function.php";
spl_autoload_register("loadClass");

$error = '';
$success = '';

// Xử lý đăng nhập USER
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Truy vấn theo user_email
    $stmt = $pdo->prepare("SELECT * FROM user WHERE user_email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Mật khẩu trong DB là MD5
        if (md5($password) === $user['user_pwd']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'] ?? 'Người dùng';
            $_SESSION['user_email'] = $user['user_email'];
            // Không có role → mặc định là user
            $_SESSION['user_role'] = 'user';

            header("Location: index.php");
            exit();
        } else {
            $error = "Mật khẩu không đúng!";
        }
    } else {
        $error = "Email không tồn tại!";
    }
}

// Xử lý đăng ký USER
if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Kiểm tra email tồn tại
    $stmt = $pdo->prepare("SELECT user_id FROM user WHERE user_email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $error = "Email đã tồn tại!";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự!";
    } else {
        // Tạo user_id tự động (ví dụ: user_ + timestamp + rand)
        $user_id = 'user_' . time() . rand(100, 999);
        $hashed_password = md5($password); // DB dùng MD5

        $stmt = $pdo->prepare("INSERT INTO user (user_id, user_name, user_pwd, user_email, user_phone) VALUES (?, ?, ?, ?, ?)");

        if ($stmt->execute([$user_id, $name, $hashed_password, $email, $phone])) {
            $success = "Đăng ký thành công! Vui lòng đăng nhập.";
        } else {
            $error = "Đăng ký thất bại! Vui lòng thử lại.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Cửa hàng máy tính</title>
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
            font-family: 'Segoe UI', sans-serif;
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
        .btn-login, .btn-register {
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
            border: none;
            color: white;
        }
        .btn-login {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }
        .btn-register {
            background: linear-gradient(135deg, #27ae60, #219a52);
        }
        .btn-login:hover, .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .login-header h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #7f8c8d;
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <!-- HEADER TOP -->
    <div class="header-top" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background: #2c3e50; color: white; padding: 10px 0;">
        <div class="container">
            <div class="contact-info" style="display: flex; justify-content: space-between; font-size: 14px;">
                <div>
                    <strong>COMPUTER STORE</strong> | 0938 926 315 | support@computerstore.vn
                </div>
                <div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <strong>Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?></strong> |
                        <a href="logout.php" style="color: #fff; text-decoration: underline;">Đăng xuất</a>
                    <?php else: ?>
                        <a href="login.php" style="color: #fff;">Đăng nhập</a>
                    <?php endif; ?>
                    | <a href="index.php" style="color: #fff;">Trang chủ</a>
                </div>
            </div>
        </div>
    </div>

    <div class="login-container" style="margin-top: 100px;">
        <div class="login-header text-center">
            <h3>Đăng nhập / Đăng ký</h3>
            <p>Quản lý tài khoản khách hàng</p>
        </div>

        <div class="login-tabs">
            <div class="login-tab active" onclick="showTab('login')">ĐĂNG NHẬP</div>
            <div class="login-tab" onclick="showTab('register')">ĐĂNG KÝ</div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Form Đăng nhập -->
        <form method="POST" class="login-form active" id="loginForm">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" placeholder="nhập email" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu:</label>
                <input type="password" name="password" class="form-control" placeholder="nhập mật khẩu" required>
            </div>
            <button type="submit" name="login" class="btn-login">ĐĂNG NHẬP</button>
        </form>

        <!-- Form Đăng ký -->
        <form method="POST" class="login-form" id="registerForm">
            <div class="form-group">
                <label>Họ tên:</label>
                <input type="text" name="name" class="form-control" placeholder="Nguyễn Văn A" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại:</label>
                <input type="tel" name="phone" class="form-control" placeholder="0901234567" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu:</label>
                <input type="password" name="password" class="form-control" placeholder="ít nhất 6 ký tự" required minlength="6">
            </div>
            <div class="form-group">
                <label>Nhập lại mật khẩu:</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" name="register" class="btn-register">ĐĂNG KÝ</button>
        </form>

        <div class="text-center mt-3">
            <p><a href="index.php">Quay lại trang chủ</a></p>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            document.querySelectorAll('.login-form').forEach(form => form.classList.remove('active'));
            document.querySelectorAll('.login-tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabName + 'Form').classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
