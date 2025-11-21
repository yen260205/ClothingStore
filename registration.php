<?php
require_once 'config.php';
require_once 'validation.php';

// Nếu đã đăng nhập thì redirect về home
if (isLoggedIn()) {
    redirect('home.php');
}

$errors = [];
$success = '';
$formData = [
    'username' => '',
    'email' => '',
    'full_name' => ''
];

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $password = cleanInput($_POST['password']);
    $confirm_password = cleanInput($_POST['confirm_password']);
    $full_name = cleanInput($_POST['full_name']);
    //Hàm cleanInput() dùng để làm sạch: loại bỏ khoảng trắng, xử lý ký tự đặc biệt
    
    // Lưu dữ liệu form
    $formData['username'] = $username;
    $formData['email'] = $email;
    $formData['full_name'] = $full_name;
    
    // Validate
    $errors = array_merge($errors, validateUsername($username));
    $errors = array_merge($errors, validateEmail($email));
    $errors = array_merge($errors, validatePassword($password));
    $errors = array_merge($errors, validateFullName($full_name));
    
    if ($password !== $confirm_password) {
        $errors[] = "Password và Confirm Password không khớp";
    }
    
    if (empty($errors)) {
        $conn = getDBConnection();
        
        // Kiểm tra username và email đã tồn tại
        if (checkUsernameExists($conn, $username)) {
            $errors[] = "Username đã được sử dụng";
        }
        
        if (checkEmailExists($conn, $email)) {
            $errors[] = "Email đã được sử dụng";
        }
        
        if (empty($errors)) {
            // Chạy hàm mysqli_real_escape_string() để chống lỗi SQL Injection.
            $username = mysqli_real_escape_string($conn, $username);
            $email = mysqli_real_escape_string($conn, $email);
            $password_md5 = md5($password);
            $full_name = mysqli_real_escape_string($conn, $full_name);
            
            $sql = "INSERT INTO users (username, email, password, full_name) 
                    VALUES ('$username', '$email', '$password_md5', '$full_name')";
            
            if (mysqli_query($conn, $sql)) {
                $success = "Đăng ký thành công! Đang chuyển đến trang đăng nhập...";
                $formData = ['username' => '', 'email' => '', 'full_name' => ''];
                
                // Redirect sau 2 giây
                header("refresh:2;url=login.php");
            } else {
                $errors[] = "Lỗi: " . mysqli_error($conn);
            }
        }
        
        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Clothing Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-image: url('images/background.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .register-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            margin-top: 10px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        
        .error-message ul {
            margin-left: 20px;
        }
        
        .success-message {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .required {
            color: red;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>🛍️ Clothing Store</h1>
            <p>Tạo tài khoản mới</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <strong>Vui lòng sửa các lỗi sau:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($formData['username']); ?>"
                       placeholder="Nhập username (ít nhất 4 ký tự)">
            </div>
            
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($formData['email']); ?>"
                       placeholder="Nhập email">
            </div>
            
            <div class="form-group">
                <label for="full_name">Họ và Tên <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" 
                       value="<?php echo htmlspecialchars($formData['full_name']); ?>"
                       placeholder="Nhập họ và tên đầy đủ">
            </div>
            
            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" 
                       placeholder="Nhập password (ít nhất 6 ký tự)">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       placeholder="Nhập lại password">
            </div>
            
            <button type="submit" class="btn-register">Đăng ký</button>
        </form>
        
        <div class="login-link">
            Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
        </div>
    </div>
</body>
</html>