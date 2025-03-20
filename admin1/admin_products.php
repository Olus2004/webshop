<?php
session_start();
require '../php/db_connect.php';

// Kiểm tra vai trò người dùng (chỉ admin mới truy cập được)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Xử lý thêm sản phẩm
if (isset($_POST['add_product'])) {
    $ten_san_pham = $_POST['ten_san_pham'];
    $mo_ta = $_POST['mo_ta'];
    $gia = $_POST['gia'];
    $so_luong = $_POST['so_luong'];
    $hinh_anh = $_FILES['hinh_anh']['name'];

    $target_dir = "../image/lv/Shirt/";
    $target_file = $target_dir . basename($hinh_anh);
    move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target_file);

    $sql = "INSERT INTO san_pham (ten_san_pham, mo_ta, gia, so_luong, hinh_anh) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssis", $ten_san_pham, $mo_ta, $gia, $so_luong, $target_file);
    $stmt->execute();
}

// Xử lý sửa sản phẩm
if (isset($_POST['edit_product'])) {
    $id = $_POST['id'];
    $ten_san_pham = $_POST['ten_san_pham'];
    $mo_ta = $_POST['mo_ta'];
    $gia = $_POST['gia'];
    $so_luong = $_POST['so_luong'];

    if (!empty($_FILES['hinh_anh']['name'])) {
        $hinh_anh = $_FILES['hinh_anh']['name'];
        $target_dir = "../image/lv/Shirt/";
        $target_file = $target_dir . basename($hinh_anh);
        move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target_file);
    } else {
        $sql = "SELECT hinh_anh FROM san_pham WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $target_file = $product['hinh_anh'];
    }

    $sql = "UPDATE san_pham SET ten_san_pham = ?, mo_ta = ?, gia = ?, so_luong = ?, hinh_anh = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisi", $ten_san_pham, $mo_ta, $gia, $so_luong, $target_file, $id);
    $stmt->execute();
}

// Xử lý xóa sản phẩm
if (isset($_GET['delete_product'])) {
    $id = $_GET['delete_product'];
    $sql = "DELETE FROM san_pham WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Lấy thông tin sản phẩm để sửa
$edit_product = null;
if (isset($_GET['edit_product'])) {
    $id = $_GET['edit_product'];
    $sql = "SELECT * FROM san_pham WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_product = $result->fetch_assoc();
}

// Lấy danh sách sản phẩm
$sql = "SELECT * FROM san_pham";
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../css/lab.css">
    <link rel="shortcut icon" href="../image/lv-logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Louis Vuitton</title>
</head>
<body>
<header>
            <nav class="nav">
                <p>
                    <b>
                        <a id="logo" href="../index.php">LOUIS VUITTON</a>
                    </b>
                </p>
                <a href="#" class="nav_buttom" id="menu-btn">☰ Menu</a>
                <a href="../search.php" class="nav_buttom" id="search">Tìm kiếm</a>
                <a href="../blog.php" class="nav_buttom" id="blog">Tin tức</a>
                <a href="../product.php" class="nav_buttom" id="product">Sản phẩm</a>
                <a href="../cart.php" class="nav_buttom" id="wishlist">Giỏ hàng</a>

                <?php if (isset($_SESSION['ten_dang_nhap'])): ?>
                    <!-- Khi đã đăng nhập -->
                    <a href="../profile.php" id="user_info">
                    <span>Xin chào, <b><?php echo htmlspecialchars($_SESSION['ten_dang_nhap']); ?></b></span></a>
                <?php else: ?>
                    <!-- Khi chưa đăng nhập -->
                    <a href="../login.php" class="nav_buttom" id="user_info">Đăng nhập</a>
                <?php endif; ?>
            </nav>

            <!-- Menu bật ra bên phải -->
            <div class="side-menu" id="side-menu">
                <a href="#" class="close-btn" id="close-btn">x</a>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../menu.php">Menu</a></li>
                    <li><a href="../search.php">Tìm kiếm</a></li>
                    <li><a href="../oder.php">Đơn hàng của tôi</a></li>
                    <li><a href="../cart.php">Giỏ hàng</a></li>
                    <?php if (isset($_SESSION['ten_dang_nhap'])): ?>
                        <li><a href="../controller/logout.php">Đăng xuất</a></li>
                    <?php else: ?>
                        <li><a href="../login.php">Đăng nhập</a></li>
                        <li><a href="../register.php">Đăng ký</a></li>
                    <?php endif; ?>
                    
                </ul>
            </div>
        </header>
    <main>
        <div class="nav_title">
            <p class="title_nav">Quản lý sản phẩm</p>
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

        <!-- Form sửa sản phẩm -->
        <?php if ($edit_product): ?>
            <div class="form-container">
                <h2>Sửa sản phẩm</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                    <input type="text" name="ten_san_pham" value="<?php echo $edit_product['ten_san_pham']; ?>" required>
                    <textarea name="mo_ta" required><?php echo $edit_product['mo_ta']; ?></textarea>
                    <input type="number" name="gia" value="<?php echo $edit_product['gia']; ?>" required>
                    <input type="number" name="so_luong" value="<?php echo $edit_product['so_luong']; ?>" required>
                    <input type="file" name="hinh_anh" accept="image/*">
                    <p>Hình ảnh hiện tại: <img src="../<?php echo $edit_product['hinh_anh']; ?>" width="50"></p>
                    <button type="submit" name="edit_product">Cập nhật sản phẩm</button>
                </form>
            </div>
        <?php endif; ?>

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
                            <td><img src="../<?php echo $product['hinh_anh']; ?>" alt="<?php echo $product['ten_san_pham']; ?>" width="50"></td>
                            <td>
                                <a href="?edit_product=<?php echo $product['id']; ?>">Sửa</a> |
                                <a href="?delete_product=<?php echo $product['id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <footer>
        <nav class="nav_footer">
            <a class="footer_1" href="../Lab0_index.php">Home</a>
            <a class="footer_1" href="../Lab0_Oder-Menu.php">Menu</a>
            <a class="footer_1" href="../Lab0_hotline.php">Contact</a>
            <a class="footer_1" href="../Lab0_sinhvien.php">Thành viên trong nhóm</a>
        </nav>
    </footer>
</body>
</html>