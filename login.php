<?php
session_start();
include "config/config.php";

$error = $success = '';

if ($_POST['login'] ?? false) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM `user` WHERE user_email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && md5($password) === $user['user_pwd']) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'] ?? 'Khách';
        $_SESSION['user_email'] = $user['user_email'];
        $_SESSION['user_role'] = 'user';
        header("Location: index.php");
        exit();
    } else {
        $error = "Email hoặc mật khẩu sai!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; display: flex; align-items: center; }
        .login-form { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); max-width: 400px; margin: auto; }
    </style>
</head>
<body>
<div class="login-form">
    <h3 class="text-center">Đăng nhập</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100">Đăng nhập</button>
    </form>
    <p class="text-center mt-3"><a href="admins/login.php">Admin Login</a></p>
</div>
</body>
</html>
