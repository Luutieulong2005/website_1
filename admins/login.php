<?php
session_start();
include "../config/config.php";

$error = '';

if ($_POST['login'] ?? false) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE ad_email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && $admin['ad_pwd'] === $password) {
        $_SESSION['admin_id'] = $admin['ad_id'];
        $_SESSION['admin_name'] = $admin['ad_name'];
        $_SESSION['admin_email'] = $admin['ad_email'];
        $_SESSION['user_role'] = 'admin'; // Dùng chung session với user
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
    <title>Đăng nhập Admin</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        body { background: #f4f6f9; font-family: Arial; }
        .login-box { max-width: 400px; margin: 100px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .btn-login { background: #e74c3c; color: white; }
        .btn-login:hover { background: #c0392b; }
    </style>
</head>
<body>
<div class="login-box">
    <h3 class="text-center">Admin Login</h3>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required placeholder="phat123@asd.com">
        </div>
        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required placeholder="123456">
        </div>
        <button type="submit" name="login" class="btn btn-login w-100">Đăng nhập</button>
    </form>
    <p class="text-center mt-3"><a href="../login.php">User Login</a></p>
</div>
</body>
</html>
