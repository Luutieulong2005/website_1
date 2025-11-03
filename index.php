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
    
    $stmt = $pdo->prepare("SELECT * FROM accounts WHERE id = ?");
    $stmt->execute([$account_id]);
    $account = $stmt->fetch();
    
    if ($account) {
        $stmt = $pdo->prepare("INSERT INTO orders (account_id, customer_name, customer_email, customer_phone, total_amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$account_id, $customer_name, $customer_email, $customer_phone, $account['price']]);
        
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
    <!-- CSS giữ nguyên như trước -->
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Arial', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; color: white; margin-bottom: 30px; }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        /* ... CSS còn lại giữ nguyên ... */
    </style>
</head>
<body>
    <!-- HTML giữ nguyên -->
    <div class="container">
        <div class="header">
            <h1><?php echo SITE_NAME; ?></h1>
            <p>Uy tín - Chất lượng - Giá tốt nhất thị trường</p>
        </div>
        <!-- ... Phần còn lại giữ nguyên ... -->
    </div>
</body>
</html>
