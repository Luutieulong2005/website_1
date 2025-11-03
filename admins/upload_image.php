<?php
session_start();
include "../config/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

if (isset($_POST['upload'])) {
    $account_id = $_POST['account_id'];
    $upload_dir = "../uploads/accounts/";
    
    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $image_file = $_FILES['account_image'];
    $file_extension = pathinfo($image_file['name'], PATHINFO_EXTENSION);
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array(strtolower($file_extension), $allowed_extensions)) {
        $new_filename = 'account_' . $account_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        // Kiểm tra và upload file
        if (move_uploaded_file($image_file['tmp_name'], $upload_path)) {
            // Cập nhật database
            $stmt = $pdo->prepare("UPDATE accounts SET image = ? WHERE id = ?");
            if ($stmt->execute([$new_filename, $account_id])) {
                $message = "✅ Upload hình ảnh thành công!";
            } else {
                $error = "❌ Lỗi cập nhật database!";
            }
        } else {
            $error = "❌ Upload thất bại!";
        }
    } else {
        $error = "❌ Chỉ chấp nhận file ảnh (JPG, JPEG, PNG, GIF)!";
    }
}

// Lấy danh sách tài khoản
$accounts = $pdo->query("SELECT id, username, rank, image FROM accounts ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Hình Ảnh - Admin</title>
    <link type="text/css" rel="stylesheet" href="../css/bootstrap.min.css"/>
    <style>
        body {
            background: #f8f9fa;
            padding: 20px;
        }
        .upload-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .account-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .account-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }
        .account-image-preview {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="upload-container">
            <h2>📷 Upload Hình Ảnh Tài Khoản</h2>
            <a href="index.php" class="btn btn-secondary mb-3">← Quay lại Dashboard</a>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group mb-3">
                            <label><strong>Chọn tài khoản:</strong></label>
                            <select name="account_id" class="form-control" required>
                                <option value="">-- Chọn tài khoản --</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?php echo $acc['id']; ?>">
                                        <?php echo $acc['username'] . ' - ' . $acc['rank']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label><strong>Chọn hình ảnh:</strong></label>
                            <input type="file" name="account_image" class="form-control" accept="image/*" required>
                            <small class="text-muted">Chấp nhận: JPG, JPEG, PNG, GIF (tối đa 5MB)</small>
                        </div>
                        
                        <button type="submit" name="upload" class="btn btn-primary btn-lg">
                            📤 Upload Hình Ảnh
                        </button>
                    </form>
                </div>
                
                <div class="col-md-6">
                    <h5>📋 Danh sách tài khoản</h5>
                    <div class="account-list">
                        <?php foreach ($accounts as $acc): ?>
                            <div class="account-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo $acc['username']; ?></strong>
                                        <span class="badge bg-info"><?php echo $acc['rank']; ?></span>
                                    </div>
                                    <div>
                                        <?php if (!empty($acc['image'])): ?>
                                            <img src="../uploads/accounts/<?php echo $acc['image']; ?>" 
                                                 class="account-image-preview" 
                                                 alt="Ảnh tài khoản">
                                        <?php else: ?>
                                            <span class="text-muted">Chưa có ảnh</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
