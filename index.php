<?php
require_once 'config/config.php';

// Xử lý tìm kiếm và lọc
$search = $_GET['search'] ?? '';
$rank_filter = $_GET['rank'] ?? '';
$price_filter = $_GET['price'] ?? '';

// Xây dựng query
$sql = "SELECT * FROM accounts WHERE status = 'available'";
$params = [];

if (!empty($search)) {
    $sql .= " AND (username LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($rank_filter)) {
    $sql .= " AND rank = ?";
    $params[] = $rank_filter;
}

if (!empty($price_filter)) {
    switch ($price_filter) {
        case 'under500k': $sql .= " AND price <= 500000"; break;
        case '500k-1m': $sql .= " AND price BETWEEN 500000 AND 1000000"; break;
        case '1m-2m': $sql .= " AND price BETWEEN 1000000 AND 2000000"; break;
        case 'over2m': $sql .= " AND price > 2000000"; break;
    }
}

$sql .= " ORDER BY price ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$accounts = $stmt->fetchAll();

// Xử lý mua nick
if (isset($_POST['buy_account'])) {
    $account_id = $_POST['account_id'];
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $customer_phone = $_POST['customer_phone'];
    
    // Lấy thông tin tài khoản
    $stmt = $pdo->prepare("SELECT * FROM accounts WHERE id = ?");
    $stmt->execute([$account_id]);
    $account = $stmt->fetch();
    
    if ($account) {
        // Tạo đơn hàng
        $stmt = $pdo->prepare("INSERT INTO orders (account_id, customer_name, customer_email, customer_phone, total_amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$account_id, $customer_name, $customer_email, $customer_phone, $account['price']]);
        
        // Cập nhật trạng thái tài khoản
        $stmt = $pdo->prepare("UPDATE accounts SET status = 'sold' WHERE id = ?");
        $stmt->execute([$account_id]);
        
        $_SESSION['message'] = "Đặt mua thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; color: white; margin-bottom: 30px; }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        .filters { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .filter-group { display: flex; gap: 15px; flex-wrap: wrap; }
        .filter-item { flex: 1; min-width: 200px; }
        .filter-item input, .filter-item select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .filter-item button { width: 100%; padding: 10px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .filter-item button:hover { background: #764ba2; }
        .accounts-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .account-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
        .account-card:hover { transform: translateY(-5px); }
        .account-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .username { font-weight: bold; font-size: 1.2em; color: #333; }
        .rank { background: #667eea; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.9em; }
        .account-details { margin-bottom: 15px; }
        .detail-item { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9em; color: #666; }
        .price { font-size: 1.5em; font-weight: bold; color: #e74c3c; text-align: center; margin: 15px 0; }
        .buy-btn { width: 100%; padding: 12px; background: #27ae60; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .buy-btn:hover { background: #219a52; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 5% auto; padding: 30px; border-radius: 10px; width: 90%; max-width: 500px; }
        .close { float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        /* Rank colors */
        .rank-Cao-Thủ { background: linear-gradient(45deg, #ff6b6b, #ee5a24); }
        .rank-Kim-Cương { background: linear-gradient(45deg, #a29bfe, #6c5ce7); }
        .rank-Tinh-Anh { background: linear-gradient(45deg, #fd79a8, #e84393); }
        .rank-Vàng { background: linear-gradient(45deg, #fdcb6e, #e17055); }
        .rank-Bạc { background: linear-gradient(45deg, #dfe6e9, #b2bec3); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo SITE_NAME; ?></h1>
            <p>Uy tín - Chất lượng - Giá tốt nhất thị trường</p>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success">
                <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="filters">
            <form method="GET" action="">
                <div class="filter-group">
                    <div class="filter-item">
                        <input type="text" name="search" placeholder="Tìm kiếm theo username..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="filter-item">
                        <select name="rank">
                            <option value="">Tất cả Rank</option>
                            <option value="Cao Thủ" <?php echo $rank_filter == 'Cao Thủ' ? 'selected' : ''; ?>>Cao Thủ</option>
                            <option value="Kim Cương" <?php echo $rank_filter == 'Kim Cương' ? 'selected' : ''; ?>>Kim Cương</option>
                            <option value="Tinh Anh" <?php echo $rank_filter == 'Tinh Anh' ? 'selected' : ''; ?>>Tinh Anh</option>
                            <option value="Vàng" <?php echo $rank_filter == 'Vàng' ? 'selected' : ''; ?>>Vàng</option>
                            <option value="Bạc" <?php echo $rank_filter == 'Bạc' ? 'selected' : ''; ?>>Bạc</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <select name="price">
                            <option value="">Tất cả giá</option>
                            <option value="under500k" <?php echo $price_filter == 'under500k' ? 'selected' : ''; ?>>Dưới 500K</option>
                            <option value="500k-1m" <?php echo $price_filter == '500k-1m' ? 'selected' : ''; ?>>500K - 1 Triệu</option>
                            <option value="1m-2m" <?php echo $price_filter == '1m-2m' ? 'selected' : ''; ?>>1 Triệu - 2 Triệu</option>
                            <option value="over2m" <?php echo $price_filter == 'over2m' ? 'selected' : ''; ?>>Trên 2 Triệu</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <button type="submit">Tìm kiếm</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="accounts-grid">
            <?php foreach ($accounts as $account): ?>
                <div class="account-card">
                    <div class="account-header">
                        <div class="username"><?php echo htmlspecialchars($account['username']); ?></div>
                        <div class="rank rank-<?php echo str_replace(' ', '-', $account['rank']); ?>">
                            <?php echo htmlspecialchars($account['rank']); ?>
                        </div>
                    </div>
                    <div class="account-details">
                        <div class="detail-item">
                            <span>Cấp độ:</span>
                            <span><?php echo $account['level']; ?></span>
                        </div>
                        <div class="detail-item">
                           
