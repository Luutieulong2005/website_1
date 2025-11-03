<?php
session_start();
include "config/config.php";
include ROOT."/include/function.php";
spl_autoload_register("loadClass");

$error = '';
$success = '';

// Xử lý đăng nhập
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
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

// Xử lý đăng ký
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
        .login-section {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin: 50px auto;
            max-width: 500px;
        }
        .login-tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
        }
        .login-tab {
            flex: 1;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
        }
        .login-tab.active {
            border-bottom: 3px solid #d32f2f;
            color: #d32f2f;
            font-weight: bold;
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
    </style>
</head>
<body>
    <!-- HEADER TOP -->
    <div class="header-top">
        <div class="container">
            <div class="contact-info">
                <div class="contact-details">
                    <div><strong>HTP</strong></div>
                    <div>0878-216-018</div>
                    <div>tieulong.work@gmail.com</div>
                    <div>53 Võ Văn Ngân, TP Thủ Đức</div>
                </div>
                <div class="user-actions">
                    <div class="user-action-item"><a href="index.php" style="color: inherit; text-decoration: none;">Trang chủ</a></div>
                    <div class="user-action-item">Tìm kiếm</div>
                    <div class="user-action-item">Kiểm tra đơn hàng</div>
                    <div class="user-action-item">Đăng nhập/Đăng ký</div>
                    <div class="user-action-item">DS yêu thích</div>
                    <div class="user-action-item">Giỏ hàng</div>
                </div>
            </div>
        </div>
    </div>
    <!-- /HEADER TOP -->

    <!-- LOGIN SECTION -->
    <div class="section">
        <div class="container">
            <div class="login-section">
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
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary btn-block">ĐĂNG NHẬP</button>
                </form>

                <!-- Form Đăng ký -->
                <form method="POST" action="" class="login-form" id="registerForm">
                    <div class="form-group">
                        <label>Họ và tên:</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại:</label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Xác nhận mật khẩu:</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="register" class="btn btn-success btn-block">ĐĂNG KÝ</button>
                </form>
            </div>
        </div>
    </div>
    <!-- /LOGIN SECTION -->

    <!-- FOOTER -->
    <?php include_once 'subpage/footer.html'; ?>
    <!-- /FOOTER -->

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
