<?php
session_start();
require 'php/db_connect.php';

// Lấy danh sách bài viết và thông tin người đăng từ cơ sở dữ liệu
$sql = "SELECT bv.*, nd.ten_dang_nhap 
        FROM bai_viet bv 
        JOIN nguoi_dung nd ON bv.nguoi_dang_id = nd.id 
        ORDER BY bv.ngay_dang DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/lab.css">
    <link rel="shortcut icon" href="image/lv-logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Louis Vuitton</title>
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
                <a id="user_info" href="profile.php"><span>Xin chào, <b><?php echo htmlspecialchars($_SESSION['ten_dang_nhap']); ?></b></span></a>
            <?php else: ?>
                <a href="login.php" class="nav_buttom" id="user_info">Đăng nhập</a>
            <?php endif; ?>
        </nav>

        <!-- Overlay liên hệ -->
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

        <!-- Menu bên -->
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
    <a class="return_index" href="javascript:history.back()"> <h3 class="return_index_title"> ← Quay về</h3> </a>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="post">
                    <div class="post-header">
                        <!-- Giả lập ảnh đại diện -->
                        <img src="/../webshop/image/lv/avt/avt1.jpg" alt="Avatar">
                        <div>
                            <div class="username">
                                <a href="profile.php?id=<?php echo $row['nguoi_dang_id']; ?>">@<?php echo htmlspecialchars($row['ten_dang_nhap']); ?></a>
                            </div>
                            <div class="timestamp"><?php echo date('d/m/Y H:i', strtotime($row['ngay_dang'])); ?></div>
                        </div>
                    </div>
                    <div class="post-content">
                        <h2><?php echo htmlspecialchars($row['tieu_de']); ?></h2>
                        <p><?php echo nl2br(htmlspecialchars($row['noi_dung'])); ?></p>
                        <?php if (!empty($row['hinh_anh'])): ?>
                            <img class="post-image" src="<?php echo htmlspecialchars($row['hinh_anh']); ?>" alt="Post Image">
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Chưa có bài viết nào.</p>
        <?php endif; ?>
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
        });

        closeContactBtn.addEventListener('click', () => {
            contactOverlay.classList.remove('active');
        });

        document.addEventListener('click', (e) => {
            if (!contactOverlay.contains(e.target) && e.target !== hotlineBtn && contactOverlay.classList.contains('active')) {
                contactOverlay.classList.remove('active');
            }
        });
    </script>
</body>
</html>