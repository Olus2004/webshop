<?php
session_start();
require 'php/db_connect.php';

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy ID đơn hàng từ URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Truy vấn thông tin đơn hàng
$sql = "SELECT dh.id, dh.ngay_dat, dh.tong_tien, dh.trang_thai 
        FROM don_hang dh 
        WHERE dh.id = ? AND dh.nguoi_dung_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows > 0) {
    $order = $order_result->fetch_assoc();
} else {
    echo "Đơn hàng không tồn tại hoặc bạn không có quyền xem!";
    exit();
}

// Truy vấn chi tiết đơn hàng, lấy giá từ san_pham_dung_tich
$sql = "SELECT ctdh.san_pham_id, ctdh.so_luong, ctdh.dung_tich, 
               sp.ten_san_pham, 
               spdt.gia AS don_gia,
               (SELECT hinh_anh FROM san_pham_hinh_anh WHERE san_pham_id = sp.id LIMIT 1) AS hinh_anh 
        FROM chi_tiet_don_hang ctdh 
        JOIN san_pham sp ON ctdh.san_pham_id = sp.id 
        JOIN san_pham_dung_tich spdt ON ctdh.san_pham_id = spdt.san_pham_id AND ctdh.dung_tich = spdt.dung_tich
        WHERE ctdh.don_hang_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_details = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Đóng kết nối statement
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <link rel="stylesheet" href="css/lab.css">
    <link rel="shortcut icon" href="image/lv-logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo $order_id; ?> - Louis Vuitton</title>
    <style>
        /* Style cho tiêu đề và thông tin đơn hàng */
        .order-info {
            margin: 20px 40px;
            text-align: left;
        }
        .order-info h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .order-info p {
            font-size: 16px;
            margin: 5px 0;
        }
        .order-info strong {
            color: #333;
        }

        /* Overlay styles */
        .contact-overlay {
            position: fixed;
            top: 0;
            right: -500px;
            width: 500px;
            height: 100%;
            background: white;
            color: black;
            transition: right 0.3s ease;
            z-index: 1000;
            box-shadow: -2px 0 5px rgba(0,0,0,0.3);
        }

        .contact-overlay.active {
            right: 0;
        }

        .contact-content {
            padding: 20px;
            position: relative;
            font-family: Arial, sans-serif;
        }

        .close-contact {
            position: absolute;
            top: 10px;
            right: 10px;
            border: none;
            background: none;
            font-size: 24px;
            cursor: pointer;
        }

        .contact-content h3 {
            margin-bottom: 15px;
        }

        .hotline {
            font-size: 20px;
            font-weight: bold;
            margin: 15px 0;
        }

        .contact-links a {
            display: block;
            margin: 10px 0;
            color: black;
            text-decoration: none;
        }

        .contact-links a:hover {
            text-decoration: underline;
        }

        .help-text {
            margin: 20px 0;
            font-weight: bold;
        }

        .support-links a {
            display: block;
            margin: 10px 0;
            color: black;
            text-decoration: none;
            font-weight: bold;
        }

        .support-links a:hover {
            text-decoration: underline;
        }

        /* Làm mờ nội dung phía sau overlay */
        .main-content.blur-background {
            filter: blur(5px);
            pointer-events: none;
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

<div class="main-content" id="main-content">
    <main>
    <a class="return_index" href="javascript:history.back()"> <h3 class="return_index_title"> ← Quay về</h3> </a>
        <div class="order-info">
            <h1>Chi tiết đơn hàng #<?php echo $order_id; ?></h1>
            <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['ngay_dat'])); ?></p>
            <p><strong>Tổng tiền:</strong> <?php echo number_format($order['tong_tien'], 0, ',', '.'); ?> ₫</p>
            <p><strong>Trạng thái:</strong> <?php echo htmlspecialchars($order['trang_thai'] ?: 'Chưa thanh toán'); ?></p>
        </div>

        <div class="box">
            <?php if (!empty($order_details)): ?>
                <?php foreach ($order_details as $detail): ?>
                    <div class="box_shop">
                        <a href="product_detail.php?id=<?php echo $detail['san_pham_id']; ?>">
                            <img class="box_shop_image" src="<?php echo htmlspecialchars($detail['hinh_anh'] ?? 'image/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($detail['ten_san_pham']); ?>">
                        </a>
                        <div class="box_shop_infomation">
                            <p class="box_shop_infomation_name">
                                <a href="product_detail.php?id=<?php echo $detail['san_pham_id']; ?>">
                                    <?php echo htmlspecialchars($detail['ten_san_pham']); ?>
                                </a>
                            </p>
                            <p class="box_shop_infomation_prices">
                                <?php echo number_format($detail['don_gia'], 0, ',', '.'); ?> ₫ x <?php echo $detail['so_luong']; ?> (<?php echo htmlspecialchars($detail['dung_tich']); ?>)
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Không có sản phẩm nào trong đơn hàng này.</p>
            <?php endif; ?>
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
</div>

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