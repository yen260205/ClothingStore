<?php
// Cấu hình database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'clothing_store');

// Kết nối database
function getDBConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, 3307);
    
    if (!$conn) {
        die("Kết nối thất bại: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, "utf8mb4");
    return $conn;
}

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Hàm redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Hàm clean input
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Thiết lập timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>