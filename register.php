<?php
// Kết nối đến cơ sở dữ liệu
require 'php/db_connect.php';

// Xử lý đăng ký
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $mat_khau = $_POST['password'] ?? '';
    $xac_nhan_mat_khau = $_POST['confirm_password'] ?? '';
    $so_dien_thoai = trim($_POST['phone'] ?? '');
    $dia_chi = trim($_POST['address'] ?? '');
    $role = $_POST['role'] ?? 'member'; // Mặc định là member nếu không chọn

    // Kiểm tra thông tin nhập vào
    if (empty($email) || empty($mat_khau) || empty($xac_nhan_mat_khau)) {
        echo '<script>alert("Vui lòng điền đầy đủ thông tin!");</script>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<script>alert("Email không hợp lệ!");</script>';
    } elseif (strlen($mat_khau) < 6) {
        echo '<script>alert("Mật khẩu phải có ít nhất 6 ký tự!");</script>';
    } elseif ($mat_khau !== $xac_nhan_mat_khau) {
        echo '<script>alert("Mật khẩu xác nhận không khớp!");</script>';
    } else {
        // Kiểm tra email đã tồn tại chưa
        $sql_check = "SELECT id FROM nguoi_dung WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            echo '<script>alert("Email đã tồn tại!");</script>';
        } else {
            // Mã hóa mật khẩu
            $mat_khau_hash = password_hash($mat_khau, PASSWORD_BCRYPT);
            $ten_dang_nhap = explode('@', $email)[0];

            // Chèn dữ liệu vào CSDL
            $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, email, role, so_dien_thoai, dia_chi, ngay_tao) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $ten_dang_nhap, $mat_khau_hash, $email, $role, $so_dien_thoai, $dia_chi);

            if ($stmt->execute()) {
                // Thông báo thành công bằng alert
                echo '<script>alert("Đăng ký thành công! Bạn sẽ được chuyển hướng đến trang đăng nhập.");</script>';
                // Chuyển hướng sau 1 giây để người dùng có thời gian đọc thông báo
                echo '<script>setTimeout(function(){ window.location.href = "login.php?success=1"; }, 1000);</script>';
                exit();
            } else {
                echo '<script>alert("Có lỗi xảy ra, vui lòng thử lại sau!");</script>';
            }
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - MyLv</title>
    <link rel="stylesheet" href="css/lab.css">
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
            <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form action="" method="POST">
                <a class="return_index" href="javascript:history.back()"> <h3 class="return_index_title"> ← Quay về</h3> </a>
                <input class="mylv_account" type="email" name="email" placeholder="Email" required> <br><br>
                <input class="mylv_account" type="password" name="password" placeholder="Mật khẩu" required> <br><br>
                <input class="mylv_account" type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required> <br><br>
                <input class="mylv_account" type="text" name="phone" placeholder="Số điện thoại"> <br><br>
                <textarea class="mylv_account" name="address" placeholder="Địa chỉ"></textarea> <br><br>
                <select class="mylv_account" name="role">
                    <option value="member">Khách hàng</option>
                </select>
                <br><br>
                <div class="mylv_button">
                    <button type="submit" class="mylv_login">Đăng ký</button>
                    <p class="mylv_login_and">hoặc</p>
                    <a href="login.php" class="mylv_login">Đăng nhập</a>
                </div>
            </form>
        </div>
    </div>
    </main>
    <footer>
            <div class="footer-container">
                <div class="footer-column">
                    <h3>Hỗ trợ</h3>
                    <p>Quý khách có thể liên hệ với chúng tôi qua Hotline <a href="tel:+842838614107">+84 2838614107</a>, <a href="#">Zalo</a>, <a href="mailto:support@louisvuitton.com">Email</a>, hoặc <a href="#">các phương thức liên hệ khác</a>.</p>
                    <ul>
                        <li><a href="#"><i class="fas fa-question-circle"></i>Câu hỏi thường gặp</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Chăm sóc sản phẩm</h3>
                    <p>Tìm hiểu cách bảo quản và chăm sóc sản phẩm của bạn để giữ được vẻ đẹp lâu dài.</p>
                </div>
                <div class="footer-column">
                    <h3>Cửa hàng</h3>
                    <p>Khám phá các cửa hàng Louis Vuitton tại Việt Nam và trên toàn thế giới.</p>
                </div>
                <div class="footer-column">
                    <h3>Dịch vụ</h3>
                    <ul>
                        <li><a href="#"><i class="fas fa-shield-alt"></i>Dịch vụ bảo hành</a></li>
                        <li><a href="#"><i class="fas fa-user-cog"></i>Dịch vụ cá nhân hóa</a></li>
                        <li><a href="#"><i class="fas fa-gift"></i>Nghệ thuật tặng quà</a></li>
                        <li><a href="#"><i class="fas fa-mobile-alt"></i>Tải ứng dụng của chúng tôi</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 Louis Vuitton. All rights reserved.</p>
            </div>
        </footer>
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
</body>
</html>
