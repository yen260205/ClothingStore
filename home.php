<?php
require_once 'config.php';
require_once 'validation.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    redirect('login.php');
}

$conn = getDBConnection();
$errors = [];
$success = '';
$mode = 'view'; // view, add, edit
$editProduct = null;

// Xử lý các action
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // DELETE
    if ($action == 'delete' && isset($_GET['code'])) {
        $code = mysqli_real_escape_string($conn, $_GET['code']);
        
        // Lấy tên file ảnh trước khi xóa
        $sql = "SELECT image FROM products WHERE product_code = '$code'";
        $result = mysqli_query($conn, $sql);
        if ($row = mysqli_fetch_assoc($result)) {
            deleteProductImage($row['image']);
        }
        
        $sql = "DELETE FROM products WHERE product_code = '$code'";
        
        if (mysqli_query($conn, $sql)) {
            $success = "Xóa sản phẩm thành công!";
        } else {
            $errors[] = "Lỗi khi xóa: " . mysqli_error($conn);
        }
    }
    
    // EDIT - Load dữ liệu
    if ($action == 'edit' && isset($_GET['code'])) {
        $code = mysqli_real_escape_string($conn, $_GET['code']);
        $sql = "SELECT * FROM products WHERE product_code = '$code'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) == 1) {
            $editProduct = mysqli_fetch_assoc($result);
            $mode = 'edit';
        }
    }
    
    // ADD
    if ($action == 'add') {
        $mode = 'add';
    }
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_code = cleanInput($_POST['product_code']);
    $product_name = cleanInput($_POST['product_name']);
    $category = cleanInput($_POST['category']);
    $size = cleanInput($_POST['size']);
    $price = cleanInput($_POST['price']);
    $quantity = cleanInput($_POST['quantity']);
    $description = cleanInput($_POST['description']);
    
    $errors = validateProduct($product_code, $product_name, $category, $size, $price, $quantity);
    
    // Validate image nếu có upload
    $imageFilename = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imageErrors = validateImage($_FILES['product_image']);
        $errors = array_merge($errors, $imageErrors);
        
        if (empty($imageErrors)) {
            $imageFilename = uploadProductImage($_FILES['product_image']);
            if (!$imageFilename) {
                $errors[] = "Không thể upload hình ảnh";
            }
        }
    }
    
    if (empty($errors)) {
        $product_code = mysqli_real_escape_string($conn, $product_code);
        $product_name = mysqli_real_escape_string($conn, $product_name);
        $category = mysqli_real_escape_string($conn, $category);
        $size = mysqli_real_escape_string($conn, $size);
        $price = mysqli_real_escape_string($conn, $price);
        $quantity = mysqli_real_escape_string($conn, $quantity);
        $description = mysqli_real_escape_string($conn, $description);
        
        if (isset($_POST['old_product_code']) && !empty($_POST['old_product_code'])) {
            // UPDATE
            $old_code = mysqli_real_escape_string($conn, $_POST['old_product_code']);
            
            // Kiểm tra nếu đổi mã sản phẩm
            if ($product_code !== $old_code) {
                if (checkProductCodeExists($conn, $product_code)) {
                    $errors[] = "Mã sản phẩm '$product_code' đã tồn tại";
                }
            }
            
            if (empty($errors)) {
                // Xử lý ảnh cũ
                if ($imageFilename) {
                    $sql = "SELECT image FROM products WHERE product_code = '$old_code'";
                    $result = mysqli_query($conn, $sql);
                    if ($row = mysqli_fetch_assoc($result)) {
                        deleteProductImage($row['image']);
                    }
                    
                    $imageSql = ", image = '$imageFilename'";
                } else {
                    $imageSql = "";
                }
                
                $sql = "UPDATE products SET 
                        product_code = '$product_code',
                        product_name = '$product_name',
                        category = '$category',
                        size = '$size',
                        price = '$price',
                        quantity = '$quantity',
                        description = '$description'
                        $imageSql
                        WHERE product_code = '$old_code'";
                
                if (mysqli_query($conn, $sql)) {
                    $success = "Cập nhật sản phẩm thành công!";
                    $mode = 'view';
                } else {
                    $errors[] = "Lỗi: " . mysqli_error($conn);
                }
            }
        } else {
            // CREATE - Kiểm tra mã sản phẩm đã tồn tại
            if (checkProductCodeExists($conn, $product_code)) {
                $errors[] = "Mã sản phẩm '$product_code' đã tồn tại";
            }
            
            if (empty($errors)) {
                $imageValue = $imageFilename ? "'$imageFilename'" : "NULL";
                
                $sql = "INSERT INTO products (product_code, product_name, category, size, price, quantity, description, image) 
                        VALUES ('$product_code', '$product_name', '$category', '$size', '$price', '$quantity', '$description', $imageValue)";
                
                if (mysqli_query($conn, $sql)) {
                    $success = "Thêm sản phẩm thành công!";
                    $mode = 'view';
                } else {
                    $errors[] = "Lỗi: " . mysqli_error($conn);
                }
            }
        }
    }
}

// READ - Lấy danh sách sản phẩm
$sql = "SELECT * FROM products ORDER BY product_code ASC";
$products = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Cửa hàng - Clothing Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-image: url('images/background_home.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
            min-height: 100vh;
            /* transform: rotate(-90deg); */
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
        }
        
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .navbar-user span {
            font-size: 14px;
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border: 1px solid white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .header-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-section h2 {
            color: #333;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }
        
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-group input[readonly] {
            background: #f0f0f0;
            cursor: not-allowed;
        }
        
        .image-upload-section {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: #f9fafb;
        }
        
        .image-preview {
            margin-top: 15px;
        }
        
        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #ddd;
        }
        
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f9fafb;
        }
        
        th {
            padding: 12px;
            text-align: left;
            color: #374151;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        tbody tr:hover {
            background: #f9fafb;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-category {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .price {
            color: #059669;
            font-weight: 600;
        }
        
        .required {
            color: red;
        }
        
        .product-image-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
        }
        
        .no-image {
            width: 60px;
            height: 60px;
            background: #e5e7eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 12px;
        }
        
        .product-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-brand">🛍️ Clothing Store Management</div>
        <div class="navbar-user">
            <span>Xin chào, <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong></span>
            <a href="logout.php" class="btn-logout">Đăng xuất</a>
        </div>
    </div>
    
    <div class="container">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Lỗi:</strong>
                <ul style="margin-left: 20px; margin-top: 5px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($mode == 'add' || $mode == 'edit'): ?>
            <div class="form-container">
                <h2 style="margin-bottom: 20px; color: #333;">
                    <?php echo $mode == 'add' ? '➕ Thêm Sản Phẩm Mới' : '✏️ Chỉnh Sửa Sản Phẩm'; ?>
                </h2>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php if ($mode == 'edit'): ?>
                        <input type="hidden" name="old_product_code" value="<?php echo $editProduct['product_code']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Mã Sản Phẩm <span class="required">*</span></label>
                            <input type="text" name="product_code" 
                                   value="<?php echo $mode == 'edit' ? htmlspecialchars($editProduct['product_code']) : ''; ?>" 
                                   placeholder="VD: SP001, CLOTH-001"
                                   style="text-transform: uppercase;">
                            <small style="color: #666;">Chỉ chứa chữ, số, gạch ngang và gạch dưới</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Tên Sản Phẩm <span class="required">*</span></label>
                            <input type="text" name="product_name" 
                                   value="<?php echo $mode == 'edit' ? htmlspecialchars($editProduct['product_name']) : ''; ?>" 
                                   placeholder="VD: Áo Thun Basic">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Danh Mục <span class="required">*</span></label>
                            <select name="category">
                                <option value="">-- Chọn danh mục --</option>
                                <option value="Áo" <?php echo ($mode == 'edit' && $editProduct['category'] == 'Áo') ? 'selected' : ''; ?>>Áo</option>
                                <option value="Quần" <?php echo ($mode == 'edit' && $editProduct['category'] == 'Quần') ? 'selected' : ''; ?>>Quần</option>
                                <option value="Váy" <?php echo ($mode == 'edit' && $editProduct['category'] == 'Váy') ? 'selected' : ''; ?>>Váy</option>
                                <option value="Đầm" <?php echo ($mode == 'edit' && $editProduct['category'] == 'Đầm') ? 'selected' : ''; ?>>Đầm</option>
                                <option value="Áo khoác" <?php echo ($mode == 'edit' && $editProduct['category'] == 'Áo khoác') ? 'selected' : ''; ?>>Áo khoác</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Kích Thước <span class="required">*</span></label>
                            <select name="size">
                                <option value="">-- Chọn size --</option>
                                <option value="S" <?php echo ($mode == 'edit' && $editProduct['size'] == 'S') ? 'selected' : ''; ?>>S</option>
                                <option value="M" <?php echo ($mode == 'edit' && $editProduct['size'] == 'M') ? 'selected' : ''; ?>>M</option>
                                <option value="L" <?php echo ($mode == 'edit' && $editProduct['size'] == 'L') ? 'selected' : ''; ?>>L</option>
                                <option value="XL" <?php echo ($mode == 'edit' && $editProduct['size'] == 'XL') ? 'selected' : ''; ?>>XL</option>
                                <option value="XXL" <?php echo ($mode == 'edit' && $editProduct['size'] == 'XXL') ? 'selected' : ''; ?>>XXL</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Giá (VNĐ) <span class="required">*</span></label>
                            <input type="number" name="price" step="1000" 
                                   value="<?php echo $mode == 'edit' ? $editProduct['price'] : ''; ?>" 
                                   placeholder="VD: 150000">
                        </div>
                        
                        <div class="form-group">
                            <label>Số Lượng <span class="required">*</span></label>
                            <input type="number" name="quantity" 
                                   value="<?php echo $mode == 'edit' ? $editProduct['quantity'] : ''; ?>" 
                                   placeholder="VD: 50">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Mô Tả</label>
                        <textarea name="description" placeholder="Nhập mô tả sản phẩm..."><?php echo $mode == 'edit' ? htmlspecialchars($editProduct['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>🖼️ Hình Ảnh Sản Phẩm</label>
                        <div class="image-upload-section">
                            <input type="file" name="product_image" id="product_image" accept="image/*" onchange="previewImage(this)">
                            <p style="margin-top: 10px; color: #666; font-size: 13px;">
                                Chấp nhận: JPG, PNG, GIF, WEBP (Tối đa 5MB)
                            </p>
                            <div class="image-preview" id="imagePreview">
                                <?php if ($mode == 'edit' && !empty($editProduct['image'])): ?>
                                    <img src="uploads/products/<?php echo htmlspecialchars($editProduct['image']); ?>" alt="Product">
                                    <p style="margin-top: 8px; font-size: 13px; color: #666;">Ảnh hiện tại (upload ảnh mới để thay đổi)</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn btn-success">
                            <?php echo $mode == 'add' ? '➕ Thêm Sản Phẩm' : '💾 Cập Nhật'; ?>
                        </button>
                        <a href="home.php" class="btn btn-secondary">❌ Hủy</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="header-section">
                <h2>📦 Danh Sách Sản Phẩm</h2>
                <a href="?action=add" class="btn btn-primary">➕ Thêm Sản Phẩm Mới</a>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Hình Ảnh</th>
                            <th>Mã SP</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Danh Mục</th>
                            <th>Size</th>
                            <th>Giá</th>
                            <th>Số Lượng</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($products) > 0): ?>
                            <?php while ($product = mysqli_fetch_assoc($products)): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                            <img src="uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                                 class="product-image-thumb">
                                        <?php else: ?>
                                            <div class="no-image">No Image</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="product-code"><?php echo htmlspecialchars($product['product_code']); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($product['product_name']); ?></strong></td>
                                    <td><span class="badge badge-category"><?php echo htmlspecialchars($product['category']); ?></span></td>
                                    <td><?php echo htmlspecialchars($product['size']); ?></td>
                                    <td class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?> ₫</td>
                                    <td><?php echo $product['quantity']; ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?action=edit&code=<?php echo urlencode($product['product_code']); ?>" class="btn btn-warning">✏️ Sửa</a>
                                            <a href="?action=delete&code=<?php echo urlencode($product['product_code']); ?>" 
                                               class="btn btn-danger" 
                                               onclick="return confirm('Bạn có chắc muốn xóa sản phẩm <?php echo htmlspecialchars($product['product_code']); ?>?')">🗑️ Xóa</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 30px; color: #999;">
                                    Chưa có sản phẩm nào. Hãy thêm sản phẩm mới!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;">';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Tự động uppercase cho mã sản phẩm
        const codeInput = document.querySelector('input[name="product_code"]');
        if (codeInput) {
            codeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>