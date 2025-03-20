<?php
session_start();
require 'php/db_connect.php'; // Kết nối CSDL

// Xử lý tìm kiếm
$search_query = '';
$products = [];
if (isset($_GET['q'])) {
    $search_query = trim($_GET['q']);
    if (!empty($search_query)) {
        $sql = "
            SELECT 
                sp.id, 
                sp.ten_san_pham, 
                (SELECT hinh_anh FROM san_pham_hinh_anh WHERE san_pham_id = sp.id LIMIT 1) AS hinh_anh, 
                MIN(spdt.gia) AS gia 
            FROM san_pham sp
            LEFT JOIN san_pham_dung_tich spdt ON sp.id = spdt.san_pham_id
            WHERE sp.ten_san_pham LIKE ? OR sp.mo_ta LIKE ?
            GROUP BY sp.id, sp.ten_san_pham
            LIMIT 4"; // Giới hạn tối đa 4 sản phẩm
        $stmt = $conn->prepare($sql);
        $search_term = "%" . $search_query . "%";
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// Lấy 4 sản phẩm bán chạy mới nhất
$sql_hot = "
    SELECT 
        sp.id, 
        sp.ten_san_pham, 
        (SELECT hinh_anh FROM san_pham_hinh_anh WHERE san_pham_id = sp.id LIMIT 1) AS hinh_anh, 
        MIN(spdt.gia) AS gia 
    FROM san_pham sp
    LEFT JOIN san_pham_dung_tich spdt ON sp.id = spdt.san_pham_id
    GROUP BY sp.id, sp.ten_san_pham
    ORDER BY sp.ngay_tao DESC 
    LIMIT 4"; // Lấy 4 sản phẩm
$stmt_hot = $conn->prepare($sql_hot);
$stmt_hot->execute();
$hot_products = $stmt_hot->get_result()->fetch_all(MYSQLI_ASSOC);

// Lấy 4 sản phẩm từ danh mục "Nước hoa unisex"
$fixed_category = "Nước hoa unisex"; // Danh mục cố định
$sql_random = "
    SELECT 
        sp.id, 
        sp.ten_san_pham, 
        (SELECT hinh_anh FROM san_pham_hinh_anh WHERE san_pham_id = sp.id LIMIT 1) AS hinh_anh, 
        MIN(spdt.gia) AS gia 
    FROM san_pham sp
    LEFT JOIN san_pham_dung_tich spdt ON sp.id = spdt.san_pham_id
    WHERE sp.danh_muc = ?
    GROUP BY sp.id, sp.ten_san_pham
    ORDER BY RAND() 
    LIMIT 4"; // Lấy 4 sản phẩm
$stmt_random = $conn->prepare($sql_random);
$stmt_random->bind_param("s", $fixed_category);
$stmt_random->execute();
$random_products = $stmt_random->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/lab.css">
    <link rel="shortcut icon" href="image/lv-logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - Louis Vuitton</title>
    <style>
        /* Kiểu dáng cho box giống trang chủ */
        .box {
            width: 100%;
            margin: 0 auto;
        }
        .box_shop {
            padding: 10px;
            box-sizing: border-box;
        }
        .box_shop_image {
            width: 100%;
            height: auto;
            max-width: 100%;
        }
        .box_shop_infomation {
            margin-top: 10px;
        }
        .box_shop_infomation_name a {
            text-decoration: none;
            color: black;
            font-weight: bold;
        }
        .box_shop_infomation_prices {
            color: #333;
            font-size: 16px;
        }
        /* Đảm bảo bố cục rõ ràng */
        .box_search_oder {
            width: 100%;
            display: block;
        }
        .box_search_title {
            width: 100%;
            margin-bottom: 20px;
        }
        .box_search_title_info {
            width: 100%;
        }
        .box_search_title_info_title {
            width: 100%;
        }
        .search_newspaper {
            width: 100%;
            display: block; 
        }
        .search_shop {
            width: 100%;
        }
        /* Sử dụng Flexbox cho tất cả các phần */
        .search_shop .box,
        .box_search_title_info .box,
        .search_newspaper .box {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
        }
        .search_shop .box_shop,
        .box_search_title_info .box_shop,
        .search_newspaper .box_shop {
            width: 50%; /* Mỗi hàng 2 box, mỗi box 50% */
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
    
    <div class="search_bar">
        <p class="search_bar_title">Nhập từ khóa tìm kiếm</p>
        <form method="GET" action="search.php">
            <input class="search_bar_input" id="input_search" type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Nhập tên sản phẩm...">
            <button type="submit" id="buttom_search" class="search_bar_searchbutton">Tìm kiếm</button>
        </form>
    </div>
    <div class="box_search">
        <div class="box_search_oder">
            <div class="box_search_title">
                <div class="box_search_title_info">
                    <p class="box_search_title_info_title">SẢN PHẨM ĐANG BÁN CHẠY</p>
                    <div class="box">
                        <?php foreach ($hot_products as $hot_product): ?>
                            <div class="box_shop">
                                <a href="product_detail.php?id=<?php echo $hot_product['id']; ?>">
                                    <img class="box_shop_image" src="<?php echo htmlspecialchars($hot_product['hinh_anh'] ?? 'image/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($hot_product['ten_san_pham']); ?>">
                                </a>
                                <div class="box_shop_infomation">
                                    <p class="box_shop_infomation_name">
                                        <a href="product_detail.php?id=<?php echo $hot_product['id']; ?>">
                                            <?php echo htmlspecialchars($hot_product['ten_san_pham']); ?>
                                        </a>
                                    </p>
                                    <p class="box_shop_infomation_prices">
                                        <?php echo number_format($hot_product['gia'] ?? 0, 0, ',', '.'); ?> ₫
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="search_newspaper">
                <p class="search_newspaper_title">SẢN PHẨM THEO DANH MỤC: <?php echo htmlspecialchars($fixed_category); ?></p>
                <div class="box">
                    <?php foreach ($random_products as $random_product): ?>
                        <div class="box_shop">
                            <a href="product_detail.php?id=<?php echo $random_product['id']; ?>">
                                <img class="box_shop_image" src="<?php echo htmlspecialchars($random_product['hinh_anh'] ?? 'image/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($random_product['ten_san_pham']); ?>">
                            </a>
                            <div class="box_shop_infomation">
                                <p class="box_shop_infomation_name">
                                    <a href="product_detail.php?id=<?php echo $random_product['id']; ?>">
                                        <?php echo htmlspecialchars($random_product['ten_san_pham']); ?>
                                    </a>
                                </p>
                                <p class="box_shop_infomation_prices">
                                    <?php echo number_format($random_product['gia'] ?? 0, 0, ',', '.'); ?> ₫
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="box_search_title">
            <div class="box_search_title_info_title">Sản phẩm</div>
            <div class="search_shop">
                <?php if (!empty($products)): ?>
                    <div class="box">
                        <?php foreach ($products as $product): ?>
                            <div class="box_shop">
                                <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                                    <img class="box_shop_image" src="<?php echo htmlspecialchars($product['hinh_anh'] ?? 'image/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['ten_san_pham']); ?>">
                                </a>
                                <div class="box_shop_infomation">
                                    <p class="box_shop_infomation_name">
                                        <a href="product_detail.php?id=<?php echo $product['id']; ?>">
                                            <?php echo htmlspecialchars($product['ten_san_pham']); ?>
                                        </a>
                                    </p>
                                    <p class="box_shop_infomation_prices">
                                        <?php echo number_format($product['gia'] ?? 0, 0, ',', '.'); ?> ₫
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($search_query): ?>
                    <p style="margin-left: 100px;">Không tìm thấy sản phẩm nào phù hợp với từ khóa "<?php echo htmlspecialchars($search_query); ?>"</p>
                <?php else: ?>
                    <p style="margin-left: 100px;">Vui lòng nhập từ khóa để tìm kiếm sản phẩm.</p>
                <?php endif; ?>
            </div>
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
</script>
</body>
</html>