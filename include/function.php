<?php
function loadClass($className) {
    $file = ROOT . '/classes/' . $className . '.php';
    if (file_exists($file)) {
        include $file;
    }
}

function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

function getRankColor($rank) {
    $colors = [
        'Cao Thủ' => '#ff6b6b',
        'Kim Cương' => '#a29bfe', 
        'Tinh Anh' => '#fd79a8',
        'Vàng' => '#fdcb6e',
        'Bạc' => '#dfe6e9'
    ];
    return $colors[$rank] ?? '#cccccc';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
?>
