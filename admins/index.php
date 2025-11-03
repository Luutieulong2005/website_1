<?php
session_start();
include "../config/config.php";

echo "<h1>🎉 ĐĂNG NHẬP ADMIN THÀNH CÔNG!</h1>";
echo "<p>👋 Xin chào: " . $_SESSION['user_name'] . "</p>";
echo "<p>📧 Email: " . $_SESSION['user_email'] . "</p>";
echo "<p>🛡️ Vai trò: " . $_SESSION['user_role'] . "</p>";
echo "<br>";
echo "<a href='logout.php'>🚪 Đăng xuất</a> | ";
echo "<a href='../index.php'>🏠 Về trang chủ</a>";
?>
