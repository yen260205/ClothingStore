<?php
// Hàm validation cho form

function validateUsername($username) {
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username không được để trống";
    } elseif (strlen($username) < 4) {
        $errors[] = "Username phải có ít nhất 4 ký tự";
    } elseif (strlen($username) > 50) {
        $errors[] = "Username không được quá 50 ký tự";
    } elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        $errors[] = "Username chỉ được chứa chữ cái, số và dấu gạch dưới";
    }
    
    return $errors;
}

function validateEmail($email) {
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "Email không được để trống";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }
    
    return $errors;
}

function validatePassword($password) {
    $errors = [];
    
    if (empty($password)) {
        $errors[] = "Password không được để trống";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password phải có ít nhất 6 ký tự";
    } elseif (strlen($password) > 50) {
        $errors[] = "Password không được quá 50 ký tự";
    }
    
    return $errors;
}

function validateFullName($fullname) {
    $errors = [];
    
    if (empty($fullname)) {
        $errors[] = "Họ tên không được để trống";
    } elseif (strlen($fullname) < 3) {
        $errors[] = "Họ tên phải có ít nhất 3 ký tự";
    }
    
    return $errors;
}

function validateProduct($code, $name, $category, $size, $price, $quantity) {
    $errors = [];
    
    if (empty($code)) {
        $errors[] = "Mã sản phẩm không được để trống";
    } elseif (!preg_match("/^[a-zA-Z0-9_-]+$/", $code)) {
        $errors[] = "Mã sản phẩm chỉ được chứa chữ, số, gạch ngang và gạch dưới";
    }
    
    if (empty($name)) {
        $errors[] = "Tên sản phẩm không được để trống";
    }
    
    if (empty($category)) {
        $errors[] = "Danh mục không được để trống";
    }
    
    if (empty($size)) {
        $errors[] = "Kích thước không được để trống";
    }
    
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = "Giá phải là số dương";
    }
    
    if (empty($quantity) || !is_numeric($quantity) || $quantity < 0) {
        $errors[] = "Số lượng phải là số không âm";
    }
    
    return $errors;
}

function checkProductCodeExists($conn, $code, $excludeCode = null) {
    $code = mysqli_real_escape_string($conn, $code);
    $sql = "SELECT product_code FROM products WHERE product_code = '$code'";
    
    if ($excludeCode) {
        $excludeCode = mysqli_real_escape_string($conn, $excludeCode);
        $sql .= " AND product_code != '$excludeCode'";
    }
    
    $result = mysqli_query($conn, $sql);
    return mysqli_num_rows($result) > 0;
}

function checkUsernameExists($conn, $username, $excludeId = null) {
    $username = mysqli_real_escape_string($conn, $username);
    $sql = "SELECT id FROM users WHERE username = '$username'";
    
    if ($excludeId) {
        $sql .= " AND id != $excludeId";
    }
    
    $result = mysqli_query($conn, $sql);
    return mysqli_num_rows($result) > 0;
}

function checkEmailExists($conn, $email, $excludeId = null) {
    $email = mysqli_real_escape_string($conn, $email);
    $sql = "SELECT id FROM users WHERE email = '$email'";
    
    if ($excludeId) {
        $sql .= " AND id != $excludeId";
    }
    
    $result = mysqli_query($conn, $sql);
    return mysqli_num_rows($result) > 0;
}

function validateImage($file) {
    $errors = [];
    
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return $errors; // Không có file, không báo lỗi
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Lỗi khi upload file";
        return $errors;
    }
    
    // Kiểm tra kích thước file (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        $errors[] = "Kích thước file không được vượt quá 5MB";
    }
    
    // Kiểm tra định dạng file
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($file['tmp_name']);
    
    if (!in_array($fileType, $allowedTypes)) {
        $errors[] = "Chỉ chấp nhận file ảnh: JPG, PNG, GIF, WEBP";
    }
    
    return $errors;
}

function uploadProductImage($file) {
    // Tạo thư mục nếu chưa có
    $uploadDir = 'uploads/products/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Tạo tên file unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('product_') . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Di chuyển file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filename;
    }
    
    return false;
}

function deleteProductImage($filename) {
    if (empty($filename) || $filename == 'default.jpg') {
        return true;
    }
    
    $filepath = 'uploads/products/' . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    
    return true;
}
?>