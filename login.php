<?php
session_start();
require 'php/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        $sql = "SELECT id, ten_dang_nhap, mat_khau, role FROM nguoi_dung WHERE ten_dang_nhap = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['mat_khau']) || $password === $user['mat_khau']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['ten_dang_nhap'] = $user['ten_dang_nhap'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: admin\doc\index.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Sai mật khẩu. Vui lòng thử lại!";
            }
        } else {
            $error = "Tài khoản không tồn tại!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - MyLv</title>
    <link rel="stylesheet" href="css/lab.css">
    <link rel="shortcut icon" href="image/lv-logo.png" type="image/x-icon">

</head>
<body>
<header>
    <nav class="nav">
        <p><b><a id="logo" href="index.php">LOUIS VUITTON</a></b></p>
        <a href="#" class="nav_buttom" id="menu-btn">☰ Menu</a>
        <a href="search.php" class="nav_buttom" id="search">Tìm kiếm</a>
        <a href="blog.php" class="nav_buttom" id="blog">Tin tức</a>
        <a href="cart.php" class="nav_buttom" id="wishlist">Giỏ hàng</a>
        <a href="#" class="nav_buttom" id="hotline">Liên hệ</a>
        <?php if (isset($_SESSION['ten_dang_nhap'])): ?>
            <a id="user_info" href="profile.php"><span >Xin chào, <b><?php echo htmlspecialchars($_SESSION['ten_dang_nhap']); ?></b></span></a>
        <?php else: ?>
            <a href="login.php" class="nav_buttom" id="user_info">Đăng nhập</a>
        <?php endif; ?>
    </nav>

    <div class="contact-overlay" id="contact-overlay">
        <div class="contact-content">
            <button class="close-contact" id="close-contact">×</button>
            <h3>Trung tâm Tư vấn Khách hàng</h3>
            <p>Trung tâm Tư vấn Khách hàng của Louis Vuitton rất hân hạnh được hỗ trợ quý khách.</p>
            <p class="hotline">+84 2838614107</p>
            <div class="contact-links">
                <a href="#">Gửi email</a>
                <a href="#">WhatsApp</a>
                <a href="#">Apple Message</a>
                <a href="#">Facebook Messenger</a>
                <a href="#">Zalo</a>
            </div>
            <p class="help-text">Chúng tôi có thể giúp gì được cho quý khách?</p>
            <div class="support-links">
                <a href="#">CÂU HỎI THƯỜNG GẶP</a>
                <a href="#">DỊCH VỤ CHĂM SÓC</a>
                <a href="#">Tìm cửa hàng</a>
            </div>
        </div>
    </div>

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
    <div class="mylv">
        <div class="mylv_view">
            <a class="return_index" href="javascript:history.back()"> <h3 class="return_index_title"> ← Quay về</h3> </a>
            <h2 class="mylv_title">Đăng nhập vào tài khoản Louis Vuitton của bạn</h2>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="POST" action="">
                <input class="mylv_account" type="text" name="username" placeholder="Tài khoản MyLV hoặc Email" required> <br><br>
                <input class="mylv_account" type="password" name="password" placeholder="Mật khẩu" required> <br><br>
                <a href="forgot_password.php" class="mylv_account_forgot">Bạn quên mật khẩu?</a>
                <div class="mylv_button">
                    <button type="submit" class="mylv_login">Đăng nhập</button>
                    <p class="mylv_login_and">hoặc</p>
                    <button type="button" class="mylv_login" onclick="window.location.href='register.php'">Đăng ky</button>
                </div>
            </form>
        </div>
        <p class="mylv_note">Tận hưởng nhiều lợi ích và trải nghiệm phong phú hơn bằng cách tạo tài khoản cá nhân </p>
    </div>
</main>
<script>
    const menuBtn = document.getElementById('menu-btn');
    const sideMenu = document.getElementById('side-menu');
    const closeBtn = document.getElementById('close-btn');
    const hotlineBtn = document.getElementById('hotline');
    const contactOverlay = document.getElementById('contact-overlay');
    const closeContactBtn = document.getElementById('close-contact');
    const mainContent = document.getElementById('main-content');

    menuBtn.addEventListener('click', (e) => {
        e.preventDefault();
        sideMenu.style.left = '0';
    });

    closeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        sideMenu.style.left = '-250px';
    });

    hotlineBtn.addEventListener('click', (e) => {
        e.preventDefault();
        contactOverlay.classList.add('active');
        mainContent.classList.add('blur-background');
    });

    closeContactBtn.addEventListener('click', () => {
        contactOverlay.classList.remove('active');
        mainContent.classList.remove('blur-background');
    });

    document.addEventListener('click', (e) => {
        if (!contactOverlay.contains(e.target) && e.target !== hotlineBtn && contactOverlay.classList.contains('active')) {
            contactOverlay.classList.remove('active');
            mainContent.classList.remove('blur-background');
        }
    });
</script>
</body>
</html>