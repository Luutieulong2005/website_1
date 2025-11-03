<?php
	if(!isset($_SESSION)) session_start();
	
?>
<!DOCTYPE html>
<html lang="vi">
<?php
	include "config/config.php";
	include ROOT."/include/function.php";
	spl_autoload_register("loadClass");
	
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
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		 <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

		<title>Shop Nick Liên Quân Mobile</title>

		<link rel="shortcut icon" href="img/favicon.png">
		<!-- Google font -->
		<link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">

		<!-- Bootstrap -->
		<link type="text/css" rel="stylesheet" href="css/bootstrap.min.css"/>

		<!-- Slick -->
		<link type="text/css" rel="stylesheet" href="css/slick.css"/>
		<link type="text/css" rel="stylesheet" href="css/slick-theme.css"/>

		<!-- nouislider -->
		<link type="text/css" rel="stylesheet" href="css/nouislider.min.css"/>

		<!-- Font Awesome Icon -->
		<link rel="stylesheet" href="css/font-awesome.min.css">

		<!-- Custom stlylesheet -->
		<link type="text/css" rel="stylesheet" href="css/style.css"/>

		<style>
			/* Custom styles for Lien Quan Shop */
			.account-card {
				background: white;
				border-radius: 10px;
				padding: 20px;
				box-shadow: 0 4px 6px rgba(0,0,0,0.1);
				transition: transform 0.3s ease;
				margin-bottom: 20px;
			}
			
			.account-card:hover {
				transform: translateY(-5px);
			}
			
			.account-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 15px;
			}
			
			.username {
				font-weight: bold;
				font-size: 1.2em;
				color: #333;
			}
			
			.rank {
				color: white;
				padding: 5px 10px;
				border-radius: 15px;
				font-size: 0.9em;
			}
			
			.rank-Cao-Thủ { background: linear-gradient(45deg, #ff6b6b, #ee5a24); }
			.rank-Kim-Cương { background: linear-gradient(45deg, #a29bfe, #6c5ce7); }
			.rank-Tinh-Anh { background: linear-gradient(45deg, #fd79a8, #e84393); }
			.rank-Vàng { background: linear-gradient(45deg, #fdcb6e, #e17055); }
			.rank-Bạc { background: linear-gradient(45deg, #dfe6e9, #b2bec3); }
			
			.account-details {
				margin-bottom: 15px;
			}
			
			.detail-item {
				display: flex;
				justify-content: space-between;
				margin-bottom: 5px;
				font-size: 0.9em;
				color: #666;
			}
			
			.price {
				font-size: 1.5em;
				font-weight: bold;
				color: #e74c3c;
				text-align: center;
				margin: 15px 0;
			}
			
			.buy-btn {
				width: 100%;
				padding: 12px;
				background: #27ae60;
				color: white;
				border: none;
				border-radius: 5px;
				cursor: pointer;
				font-size: 16px;
				font-weight: bold;
			}
			
			.buy-btn:hover {
				background: #219a52;
			}
			
			.search-section {
				background: white;
				padding: 30px;
				border-radius: 10px;
				margin: 20px 0;
				box-shadow: 0 4px 6px rgba(0,0,0,0.1);
			}
		</style>

		<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

    </head>
	<body onload="SetDefault();">
		<!-- HEADER -->
		<?php include_once 'subpage/header.php'; ?>
		<!-- /HEADER -->

		<!-- NAVIGATION -->
		<?php include_once 'subpage/navigation.html'; ?>
		<!-- /NAVIGATION -->

		<!-- SECTION BANNER -->
		<div id="banner" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 80px 0; color: white; text-align: center;">
			<div class="container">
				<h1>SHOP NICK LIÊN QUÂN MOBILE</h1>
				<p>Uy tín - Chất lượng - Giá tốt nhất thị trường</p>
			</div>
		</div>
		<!-- /SECTION BANNER -->

		<!-- SEARCH SECTION -->
		<div class="container">
			<div class="search-section">
				<form method="GET" action="">
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo username..." value="<?php echo htmlspecialchars($search); ?>">
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<select name="rank" class="form-control">
									<option value="">Tất cả Rank</option>
									<option value="Cao Thủ" <?php echo $rank_filter == 'Cao Thủ' ? 'selected' : ''; ?>>Cao Thủ</option>
									<option value="Kim Cương" <?php echo $rank_filter == 'Kim Cương' ? 'selected' : ''; ?>>Kim Cương</option>
									<option value="Tinh Anh" <?php echo $rank_filter == 'Tinh Anh' ? 'selected' : ''; ?>>Tinh Anh</option>
									<option value="Vàng" <?php echo $rank_filter == 'Vàng' ? 'selected' : ''; ?>>Vàng</option>
									<option value="Bạc" <?php echo $rank_filter == 'Bạc' ? 'selected' : ''; ?>>Bạc</option>
								</select>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<select name="price" class="form-control">
									<option value="">Tất cả giá</option>
									<option value="under500k" <?php echo $price_filter == 'under500k' ? 'selected' : ''; ?>>Dưới 500K</option>
									<option value="500k-1m" <?php echo $price_filter == '500k-1m' ? 'selected' : ''; ?>>500K - 1 Triệu</option>
									<option value="1m-2m" <?php echo $price_filter == '1m-2m' ? 'selected' : ''; ?>>1 Triệu - 2 Triệu</option>
									<option value="over2m" <?php echo $price_filter == 'over2m' ? 'selected' : ''; ?>>Trên 2 Triệu</option>
								</select>
							</div>
						</div>
						<div class="col-md-2">
							<button type="submit" class="btn btn-primary btn-block">Tìm kiếm</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<!-- /SEARCH SECTION -->

		<!-- ACCOUNTS SECTION -->
		<div class="section">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<div class="section-title">
							<h3 class="title">TÀI KHOẢN LIÊN QUÂN MOBILE</h3>
						</div>
					</div>

					<?php if (isset($_SESSION['message'])): ?>
						<div class="col-md-12">
							<div class="alert alert-success">
								<?php 
								echo $_SESSION['message']; 
								unset($_SESSION['message']);
								?>
							</div>
						</div>
					<?php endif; ?>

					<?php foreach ($accounts as $account): ?>
						<div class="col-md-4">
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
										<span>Số tướng:</span>
										<span><?php echo $account['hero_count']; ?></span>
									</div>
									<div class="detail-item">
										<span>Số skin:</span>
										<span><?php echo $account['skin_count']; ?></span>
									</div>
								</div>
								<div class="description">
									<?php echo htmlspecialchars($account['description']); ?>
								</div>
								<div class="price">
									<?php echo number_format($account['price'], 0, ',', '.'); ?> VNĐ
								</div>
								<button class="buy-btn" onclick="openModal(<?php echo $account['id']; ?>)">MUA NGAY</button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<!-- /ACCOUNTS SECTION -->

		<!-- FOOTER -->
		<?php include_once 'subpage/footer.html'; ?>
		<!-- /FOOTER -->

		<!-- Modal đặt mua -->
		<div id="buyModal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Đặt mua tài khoản</h4>
					</div>
					<div class="modal-body">
						<form method="POST" action="">
							<input type="hidden" name="account_id" id="account_id">
							<div class="form-group">
								<label>Họ và tên:</label>
								<input type="text" name="customer_name" class="form-control" required>
							</div>
							<div class="form-group">
								<label>Email:</label>
								<input type="email" name="customer_email" class="form-control" required>
							</div>
							<div class="form-group">
								<label>Số điện thoại:</label>
								<input type="tel" name="customer_phone" class="form-control" required>
							</div>
							<button type="submit" name="buy_account" class="btn btn-success btn-block">XÁC NHẬN ĐẶT MUA</button>
						</form>
					</div>
				</div>
			</div>
		</div>

		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/slick.min.js"></script>
		<script src="js/nouislider.min.js"></script>
		<script src="js/jquery.zoom.min.js"></script>
		<script src="js/main.js"></script>
		
		<script>
			function openModal(accountId) {
				document.getElementById('account_id').value = accountId;
				$('#buyModal').modal('show');
			}
		</script>
	</body>
</html>
