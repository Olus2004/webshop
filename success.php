<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/lab.css">
    <link rel="shortcut icon" href="image/lv-logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - Louis Vuitton</title>
    <style>
        /* Chỉ thiết kế lại phần main với phong cách đen trắng */
        .success-container {
            max-width: 700px;
            margin: 100px auto;
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            position: relative;
            border: 1px solid #000;
        }

        .success-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.05), rgba(255, 255, 255, 0.05));
            z-index: -1;
        }

        .success-title {
            font-size: 36px;
            font-weight: bold;
            color: #000;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: url('image/lv/icon/1.png') no-repeat center;
            background-size: contain;
        }

        .success-message {
            font-size: 18px;
            color: #333;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .action-btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #000;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .action-btn:hover {
            background-color: #333;
            transform: translateY(-2px);
        }

        .action-btn.secondary {
            background-color: #fff;
            color: #000;
            border: 2px solid #000;
        }

        .action-btn.secondary:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
        }

        @media (max-width: 600px) {
            .success-container {
                margin: 50px 20px;
                padding: 20px;
            }

            .success-title {
                font-size: 28px;
            }

            .success-message {
                font-size: 16px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }

            .action-btn {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
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
    <div class="success-container">
        <div class="success-icon"></div>
        <h1 class="success-title">Đặt Hàng Thành Công!</h1>
        <p class="success-message">
            Cảm ơn bạn đã tin tưởng và mua sắm tại Louis Vuitton. <br>
            Đơn hàng của bạn đã được ghi nhận và đang trong quá trình xử lý. <br>
            Chúng tôi sẽ thông báo chi tiết qua email hoặc số điện thoại của bạn.
        </p>
        <div class="action-buttons">
            <a href="index.php" class="action-btn">Tiếp Tục Mua Sắm</a>
            <a href="oder.php" class="action-btn secondary">Xem Đơn Hàng</a>
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