<?php
session_start();
include "../config/config.php";
include "../include/function.php";
spl_autoload_register("loadClass");

$error = '';

// Xử lý đăng nhập ADMIN
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin - Shop Nick Liên Quân</title>
    <link type="text/css" rel="stylesheet" href="../css/bootstrap.min.css"/>
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <link type="text/css" rel="stylesheet" href="../css/style.css"/>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #666;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-login:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h3>🔐 ĐĂNG NHẬP ADMIN</h3>
            <p>Shop Nick Liên Quân Mobile</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Email Admin:</label>
                <input type="email" name="email" class="form-control" placeholder="admin@gmail.com" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu:</label>
                <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
            </div>
            <button type="submit" name="login" class="btn-login">ĐĂNG NHẬP ADMIN</button>
        </form>

        <div class="text-center mt-3">
            <a href="../index.php" style="color: #667eea;">← Quay lại trang chủ</a>
        </div>
    </div>
</body>
</html>
