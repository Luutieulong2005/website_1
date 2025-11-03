<?php
session_start();
include "../config/config.php";

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

echo "<h1>🎉 ĐĂNG NHẬP ADMIN THÀNH CÔNG!</h1>";
echo "<p>👋 Xin chào: " . $_SESSION['user_name'] . "</p>";
echo "<p>📧 Email: " . $_SESSION['user_email'] . "</p>";
echo "<p>🛡️ Vai trò: " . $_SESSION['user_role'] . "</p>";
echo "<br>";
echo "<a href='logout.php'>🚪 Đăng xuất</a> | ";
echo "<a href='../index.php'>🏠 Về trang chủ</a>";

// Hiển thị tất cả session để debug
echo "<hr><h3>Debug Session:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
