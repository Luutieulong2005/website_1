<?php
session_start();
include "../config/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Xử lý thêm tài khoản mới
if (isset($_POST['add_account'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $rank = $_POST['rank'];
    $level = $_POST['level'];
    $price = $_POST['price'];
    $hero_count = $_POST['hero_count'];
    $skin_count = $_POST['skin_count'];
    $description = $_POST['description'];
    
    $stmt = $pdo->prepare("INSERT INTO accounts (username, password, rank, level, price, hero_count, skin_count, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $password, $rank, $level, $price, $hero_count, $skin_count, $description]);
    
    $_SESSION['message'] = "Thêm tài khoản thành công!";
    header("Location: accounts.php");
    exit();
}

// Xử lý xóa tài khoản
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM accounts WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['message'] = "Xóa tài khoản thành công!";
    header("Location: accounts.php");
    exit();
}

// Lấy danh sách tài khoản
$accounts = $pdo->query("SELECT * FROM accounts ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quản lý Tài khoản - Admin</title>
    <link type="text/css" rel="stylesheet" href="../css/bootstrap.min.css"/>
    <style>
        .table-accounts img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .status-available { color: #27ae60; font-weight: bold; }
        .status-sold { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 bg-dark text-light vh-100">
                <div class="p-3">
                    <h5>🛡️ ADMIN PANEL</h5>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link text-light" href="index.php">📊 Dashboard</a>
                    <a class="nav-link text-light bg-primary" href="accounts.php">🎮 Tài khoản</a>
                    <a class="nav-link text-light" href="orders.php">📦 Đơn hàng</a>
                    <a class="nav-link text-light" href="users.php">👥 Users</a>
                    <a class="nav-link text-light" href="upload_image.php">📷 Upload ảnh</a>
                    <a class="nav-link text-light" href="../index.php">🏠 Trang chủ</a>
                    <a class="nav-link text-light" href="logout.php">🚪 Đăng xuất</a>
                </nav>
            </div>

            <!-- Main content -->
            <div class="col-md-10">
                <div class="p-4">
                    <h2>🎮 Quản lý Tài khoản Game</h2>
                    
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                    <?php endif; ?>

                    <!-- Form thêm tài khoản -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>➕ Thêm tài khoản mới</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="password" class="form-control" placeholder="Password" required>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="rank" class="form-control" required>
                                            <option value="Cao Thủ">Cao Thủ</option>
                                            <option value="Kim Cương">Kim Cương</option>
                                            <option value="Tinh Anh">Tinh Anh</option>
                                            <option value="Vàng">Vàng</option>
                                            <option value="Bạc">Bạc</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <input type="number" name="level" class="form-control" placeholder="Level" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="price" class="form-control" placeholder="Giá" min="0" required>
                                    </div>
                                    <div class="col-md-1">
                                        <input type="number" name="hero_count" class="form-control" placeholder="Tướng" min="0">
                                    </div>
                                    <div class="col-md-1">
                                        <input type="number" name="skin_count" class="form-control" placeholder="Skin" min="0">
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-10">
                                        <textarea name="description" class="form-control" placeholder="Mô tả tài khoản"></textarea>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" name="add_account" class="btn btn-success btn-block">Thêm</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Danh sách tài khoản -->
                    <div class="card">
                        <div class="card-header">
                            <h5>📋 Danh sách tài khoản (<?php echo count($accounts); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-accounts">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Hình ảnh</th>
                                            <th>Username</th>
                                            <th>Rank</th>
                                            <th>Level</th>
                                            <th>Giá</th>
                                            <th>Tướng/Skin</th>
                                            <th>Trạng thái</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($accounts as $account): ?>
                                        <tr>
                                            <td><?php echo $account['id']; ?></td>
                                            <td>
                                                <?php if (!empty($account['image'])): ?>
                                                    <img src="../uploads/accounts/<?php echo $account['image']; ?>" alt="Ảnh">
                                                <?php else: ?>
                                                    <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo $account['username']; ?></strong></td>
                                            <td>
                                                <span class="badge" style="background: <?php echo getRankColor($account['rank']); ?>">
                                                    <?php echo $account['rank']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $account['level']; ?></td>
                                            <td><strong><?php echo number_format($account['price'], 0, ',', '.'); ?>₫</strong></td>
                                            <td>
                                                <small>Tướng: <?php echo $account['hero_count']; ?></small><br>
                                                <small>Skin: <?php echo $account['skin_count']; ?></small>
                                            </td>
                                            <td>
                                                <?php if ($account['status'] == 'available'): ?>
                                                    <span class="status-available">🟢 Có sẵn</span>
                                                <?php else: ?>
                                                    <span class="status-sold">🔴 Đã bán</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="upload_image.php?account_id=<?php echo $account['id']; ?>" class="btn btn-sm btn-info">📷</a>
                                                <a href="?delete=<?php echo $account['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xóa tài khoản này?')">🗑️</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
