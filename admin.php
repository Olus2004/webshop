<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/lab.css">
    <link rel="shortcut icon" href="image/lv-logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị - Louis Vuitton</title>
</head>
<body>
<header>
        <nav class="nav">
            <p>
                <b>
                    <a id="logo" href="index.php">LOUIS VUITTON</a>
                </b>
            </p>
            <a href="#" class="nav_buttom" id="menu-btn">☰ Menu</a>
            <a href="search.php" class="nav_buttom" id="search">Tìm kiếm</a>
            <a href="blog.php" class="nav_buttom" id="blog">Tin tức</a>
            <a href="product.php" class="nav_buttom" id="product">Sản phẩm</a>
            <a href="cart.php" class="nav_buttom" id="wishlist">Giỏ hàng</a>

            <?php if (isset($_SESSION['ten_dang_nhap'])): ?>
                <!-- Khi đã đăng nhập -->
                <a href="profile.php" id="user_info">
                <span>Xin chào, <b><?php echo htmlspecialchars($_SESSION['ten_dang_nhap']); ?></b></span></a>
            <?php else: ?>
                <!-- Khi chưa đăng nhập -->
                <a href="login.php" class="nav_buttom" id="user_info">Đăng nhập</a>
            <?php endif; ?>
        </nav>

        <!-- Menu bật ra bên phải -->
        <div class="side-menu" id="side-menu">
            <a href="#" class="close-btn" id="close-btn">x</a>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="search.php">Tìm kiếm</a></li>
                <li><a href="oder.php">Đơn hàng của tôi</a></li>
                <li><a href="cart.php">Giỏ hàng</a></li>
                <?php if (isset($_SESSION['ten_dang_nhap'])): ?>
                    <li><a href="controller/logout.php">Đăng xuất</a></li>
                <?php else: ?>
                    <li><a href="login.php">Đăng nhập</a></li>
                    <li><a href="register.php">Đăng ký</a></li>
                <?php endif; ?>
                
            </ul>
        </div>
    </header>

    <main>
        <div class="nav_title">
            <p class="title_nav">Trang quản trị</p>
        </div>
        <div class="admin-options">
            <h2>Chọn chức năng quản trị</h2>
            <a href="admin/admin_products.php" class="admin-button">Quản lý sản phẩm</a>
            <a href="admin/admin_users.php" class="admin-button">Quản lý người dùng</a>
            <a href="admin/admin_orders.php" class="admin-button">Quản lý đơn hàng</a>
        </div>
    </main>
    <footer>
        <nav class="nav_footer">
            <a class="footer_1" href="Lab0_index.php">Home</a>
            <a class="footer_1" href="Lab0_Oder-Menu.php">Menu</a>
            <a class="footer_1" href="Lab0_hotline.php">Contact</a>
            <a class="footer_1" href="Lab0_sinhvien.php">Thành viên trong nhóm</a>
        </nav>
    </footer>
</body>
</html>