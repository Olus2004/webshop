<?php
session_start();
require 'php/db_connect.php';

// Kiểm tra vai trò người dùng (chỉ admin mới truy cập được)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Chuyển hướng nếu không phải admin
    exit();
}

// Xử lý thêm sản phẩm
if (isset($_POST['add_product'])) {
    $ten_san_pham = $_POST['ten_san_pham'];
    $mo_ta = $_POST['mo_ta'];
    $gia = $_POST['gia'];
    $so_luong = $_POST['so_luong'];
    $hinh_anh = $_FILES['hinh_anh']['name'];

    // Upload hình ảnh
    $target_dir = "image/lv/Shirt/";
    $target_file = $target_dir . basename($hinh_anh);
    move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target_file);

    $stmt = $pdo->prepare("INSERT INTO san_pham (ten_san_pham, mo_ta, gia, so_luong, hinh_anh) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$ten_san_pham, $mo_ta, $gia, $so_luong, $target_file]);
}

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM san_pham WHERE id = ?");
    $stmt->execute([$id]);
}

// Lấy danh sách sản phẩm
$stmt = $pdo->query("SELECT * FROM san_pham");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="Lab_0_1.css">
    <link rel="shortcut icon" href="image/lv-logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lí - Louis Vuitton</title>
</head>
<body>
    <header>
        <nav class="nav_shop">
            <p><b><a class="logo_shop" href="Lab0_index.html">LOUIS VUITTON</a></b></p>
            <a href="Lab0_Oder-Menu.html" class="nav_buttom_shop" id="menu">☰ Menu</a>
            <a href="Lab0_Seacrh.html" class="nav_buttom_shop" id="search">Search</a>
            <a href="Lab0_hotline.html" class="nav_buttom_shop" id="hotline">Hotline</a>
            <a href="Lab0_wishlist.html" class="nav_buttom_shop" id="wishlist">Wishlist</a>
            <a href="Lab0_mylv.html" class="nav_buttom_shop" id="mylv">MyLV</a>
        </nav>    
    </header>
    <main>
        <div class="nav_title">
            <p class="title_nav">Trang quản trị</p>
        </div>

        <!-- Form thêm sản phẩm -->
        <div class="form-container">
            <h2>Thêm sản phẩm</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="ten_san_pham" placeholder="Tên sản phẩm" required>
                <textarea name="mo_ta" placeholder="Mô tả" required></textarea>
                <input type="number" name="gia" placeholder="Giá (VNĐ)" required>
                <input type="number" name="so_luong" placeholder="Số lượng" required>
                <input type="file" name="hinh_anh" accept="image/*" required>
                <button type="submit" name="add_product">Thêm sản phẩm</button>
            </form>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="product-list">
            <h2>Danh sách sản phẩm</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Mô tả</th>
                        <th>Giá (VNĐ)</th>
                        <th>Số lượng</th>
                        <th>Hình ảnh</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo $product['ten_san_pham']; ?></td>
                            <td><?php echo $product['mo_ta']; ?></td>
                            <td><?php echo number_format($product['gia']); ?></td>
                            <td><?php echo $product['so_luong']; ?></td>
                            <td><img src="<?php echo $product['hinh_anh']; ?>" alt="<?php echo $product['ten_san_pham']; ?>" width="50"></td>
                            <td>
                                <a href="?delete=<?php echo $product['id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <footer>
        <nav class="nav_footer">
            <a class="footer_1" href="Lab0_index.html">Home</a>
            <a class="footer_1" href="Lab0_Oder-Menu.html">Menu</a>
            <a class="footer_1" href="Lab0_hotline.html">Contact</a>
            <a class="footer_1" href="Lab0_sinhvien.html">Thành viên trong nhóm</a>
        </nav>
    </footer>
</body>
</html>