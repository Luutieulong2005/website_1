<?php
session_start();
include "../config/config.php";

// Nếu đã đăng nhập, chuyển hướng đến dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $error = "Vui lòng điền đầy đủ thông tin!";
    } else {
        try {
            $db = $config->getConnection();
            
            // Kiểm tra trong bảng admin
            $stmt = $db->prepare("SELECT * FROM admin WHERE ad_email = ? AND ad_pwd = ?");
            $stmt->execute([$email, $password]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                $_SESSION['admin_id'] = $admin['ad_id'];
                $_SESSION['admin_name'] = $admin['ad_name'];
                $_SESSION['admin_email'] = $admin['ad_email'];
                
                header("Location: index.php");
                exit();
            } else {
                $error = "Email hoặc mật khẩu không đúng!";
            }
        } catch (Exception $e) {
            $error = "Lỗi hệ thống! Vui lòng thử lại.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin - HTP Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 400px;
            width: 100%;
        }
        .brand-logo {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        .brand-logo i {
            font-size: 3em;
            color: #e74c3c;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="brand-logo">
                        <i class="fas fa-crown"></i>
                        <h3>HTP SHOP ADMIN</h3>
                        <p class="text-muted">Đăng nhập hệ thống quản lý</p>
                    </div>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="admin@lienquan.com" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="../index.php" class="text-muted">
                            <i class="fas fa-arrow-left"></i> Quay lại trang chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
