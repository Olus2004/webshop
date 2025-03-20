<?php
session_start();
require 'php/db_connect.php';

// Truy vấn các danh mục duy nhất từ bảng san_pham
$sql_categories = "SELECT DISTINCT danh_muc FROM san_pham";
$result_categories = $conn->query($sql_categories);

if (!$result_categories) {
    die("Lỗi truy vấn danh mục: " . $conn->error);
}

// Lấy danh mục được chọn từ tham số GET (nếu có)
$selected_category = isset($_GET['danh_muc']) ? $_GET['danh_muc'] : 'Tất cả';

// Truy vấn sản phẩm với giá thấp nhất từ san_pham_dung_tich và lấy hình ảnh đầu tiên từ san_pham_hinh_anh
$sql_products = "
    SELECT 
        sp.id, 
        sp.ten_san_pham, 
        MIN(spdt.gia) AS gia,
        (SELECT hinh_anh FROM san_pham_hinh_anh WHERE san_pham_id = sp.id LIMIT 1) AS hinh_anh
    FROM san_pham sp
    LEFT JOIN san_pham_dung_tich spdt ON sp.id = spdt.san_pham_id";
if ($selected_category !== 'Tất cả') {
    $sql_products .= " WHERE sp.danh_muc = '" . $conn->real_escape_string($selected_category) . "'";
}
$sql_products .= " GROUP BY sp.id, sp.ten_san_pham
                  ORDER BY sp.ngay_tao DESC";
$result_products = $conn->query($sql_products);

if (!$result_products) {
    die("Lỗi truy vấn sản phẩm: " . $conn->error);
}

// Truy vấn tất cả media phân cách từ bảng separator_media
$sql_media = "SELECT media_path, type, mo_ta 
              FROM separator_media 
              ORDER BY thu_tu ASC"; // Giữ thứ tự cho "Tất cả"
$result_media = $conn->query($sql_media);

if (!$result_media) {
    die("Lỗi truy vấn media: " . $conn->error);
}

// Lưu tất cả media vào mảng
$separator_media = [];
while ($row = $result_media->fetch_assoc()) {
    $separator_media[] = $row;
}

// Xác định tiêu đề hiển thị
$title_display = ($selected_category === 'Tất cả') ? 'Tất cả nước hoa' : $selected_category;

// Mảng để theo dõi media đã dùng khi random
$used_media_indices = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/lab.css">
    <link rel="shortcut icon" href="image/lv-logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Louis Vuitton - Trang chủ</title>

    <style>
        .nav_title {
            position: relative; 
            margin-bottom: 20px; 
        }
        .media-container {
            position: relative;
            width: 100%;
            margin: 10px 0;
        }
        .media-container img {
            width: 100%;
            display: block;
        }
        .media-text {
            position: absolute;
            bottom: 40px;
            left: 40px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            text-align: left;
            max-width: 30%;
            white-space: normal;
            word-wrap: break-word;
        }
        .media-title {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .media-description {
            font-size: 18px;
        }
        .filter-container {
            position: absolute;
            right: 20px;
            bottom: 20px;
        }
        .filter-btn {
            padding: 8px 16px;
            background-color: white;
            color: black;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-size: 14px;
            border: 0.1px solid #333; /* Đường viền màu đen */
        }
        .filter-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            min-width: 150px;
            z-index: 1;
        }
        .filter-dropdown a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
        }
        .filter-dropdown a:hover {
            background-color: #f0f0f0;
        }
        .filter-container:hover .filter-dropdown {
            display: block;
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

    <div class="vid-background">
        <br>
        <br>
        <img src="image\PERFUMES_FRAGRANCE_WOF_DI3.webp" class="vid_index" alt="">
        <div class="order-now-container">
            <!-- <a href="cart.php" class="order-now-btn">Đặt hàng ngay</a> -->
        </div>
    </div>

    <main>
        <div class="nav_title">
            <p class="title_nav" style="margin-left: 40px"><?php echo htmlspecialchars($title_display); ?></p>
            <div class="filter-container">
                <button class="filter-btn">Bộ lọc</button>
                <div class="filter-dropdown">
                    <a href="index.php?danh_muc=Tất cả">Tất cả</a>
                    <?php while ($category = $result_categories->fetch_assoc()): ?>
                        <a href="index.php?danh_muc=<?php echo urlencode($category['danh_muc']); ?>">
                            <?php echo htmlspecialchars($category['danh_muc']); ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
            <hr>
        </div>

        <div class="box">
            <?php if ($result_products->num_rows > 0): ?>
                <table width="100%">
                    <tr>
                    <?php 
                    $count = 0; // Đếm số sản phẩm trên mỗi dòng
                    $media_index = 0; // Chỉ số cho "Tất cả"
                    while ($row = $result_products->fetch_assoc()): 
                        $count++;
                    ?>
                        <td width="25%" class="box_shop">
                            <div>
                                <a href="product_detail.php?id=<?php echo $row['id']; ?>">
                                    <img class="box_shop_image" src="<?php echo htmlspecialchars($row['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($row['ten_san_pham']); ?>">
                                </a>
                                <div class="box_shop_infomation">
                                    <p class="box_shop_infomation_name">
                                        <a style="text-decoration: none; color: black;" href="product_detail.php?id=<?php echo $row['id']; ?>">
                                            <?php echo htmlspecialchars($row['ten_san_pham']); ?>
                                        </a>
                                    </p>
                                    <p class="box_shop_infomation_prices">
                                        <?php echo number_format($row['gia'], 0, ',', '.'); ?>đ
                                    </p>
                                </div>
                            </div>
                        </td>
                        <?php 
                        // Kết thúc dòng sau mỗi 4 sản phẩm
                        if ($count % 4 == 0): ?>
                            </tr>
                            <?php if ($count < $result_products->num_rows): // Nếu chưa phải dòng cuối ?>
                                <tr>
                                    <td colspan="4">
                                        <?php if (!empty($separator_media)): // Chỉ chèn nếu có media ?>
                                            <?php 
                                            if ($selected_category === 'Tất cả') {
                                                // Giữ nguyên thứ tự cho "Tất cả"
                                                $media = $separator_media[$media_index];
                                                $media_index++;
                                            } else {
                                                // Random không lặp cho danh mục cụ thể
                                                do {
                                                    $random_index = array_rand($separator_media);
                                                } while (in_array($random_index, $used_media_indices) && count($used_media_indices) < count($separator_media));
                                                // Nếu tất cả media đã dùng hết, reset mảng
                                                if (count($used_media_indices) >= count($separator_media)) {
                                                    $used_media_indices = [];
                                                }
                                                $media = $separator_media[$random_index];
                                                $used_media_indices[] = $random_index;
                                            }

                                            if ($media['type'] === 'image' && !empty($media['media_path'])): ?>
                                                <div class="media-container">
                                                    <img src="<?php echo htmlspecialchars($media['media_path']); ?>" alt="Separator Image">
                                                    <?php if (!empty($media['mo_ta'])): ?>
                                                        <?php 
                                                        $text_parts = explode('|', $media['mo_ta'], 2);
                                                        $title = trim($text_parts[0]);
                                                        $description = isset($text_parts[1]) ? trim($text_parts[1]) : '';
                                                        ?>
                                                        <div class="media-text">
                                                            <div class="media-title"><?php echo htmlspecialchars($title); ?></div>
                                                            <?php if (!empty($description)): ?>
                                                                <div class="media-description"><?php echo htmlspecialchars($description); ?></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php elseif ($media['type'] === 'video' && !empty($media['media_path'])): ?>
                                                <video autoplay muted loop style="width: 100%; margin: 10px 0;">
                                                    <source src="<?php echo htmlspecialchars($media['media_path']); ?>" type="video/mp4">
                                                    Trình duyệt của bạn không hỗ trợ video.
                                                </video>
                                            <?php endif; // Nếu type='none' hoặc media_path trống thì không chèn gì ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endwhile; ?>
                    </tr>
                </table>
            <?php else: ?>
                <p>Không có sản phẩm nào để hiển thị.</p>
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