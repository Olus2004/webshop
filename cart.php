<?php
session_start();
require 'php/db_connect.php';

// Xử lý thanh toán
if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $nguoi_dung_id = $_SESSION['user_id'];
    $tong_tien = 0;
    $selected_items = [];

    foreach ($_SESSION['cart'] as $key => $item) {
        if (isset($_POST['select_' . $key]) && $_POST['select_' . $key] == 'on') {
            $tong_tien += $item['gia'] * $item['so_luong'];
            $selected_items[$key] = $item;
        }
    }

    if (!empty($selected_items)) {
        $sql = "INSERT INTO don_hang (nguoi_dung_id, ngay_dat, tong_tien, trang_thai) VALUES (?, NOW(), ?, 'Chưa thanh toán')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("id", $nguoi_dung_id, $tong_tien);
        if ($stmt->execute()) {
            $don_hang_id = $conn->insert_id;

            $sql = "INSERT INTO chi_tiet_don_hang (don_hang_id, san_pham_id, so_luong, gia, dung_tich) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            foreach ($selected_items as $key => $item) {
                if (strpos($key, '_') !== false) {
                    list($san_pham_id, $dung_tich) = explode('_', $key);
                } else {
                    $san_pham_id = $key;
                    $dung_tich = isset($item['dung_tich']) ? $item['dung_tich'] : '100ml';
                }
                $stmt->bind_param("iiids", $don_hang_id, $san_pham_id, $item['so_luong'], $item['gia'], $dung_tich);
                $stmt->execute();
            }

            foreach ($selected_items as $key => $item) {
                unset($_SESSION['cart'][$key]);
            }
            header("Location: success.php");
            exit();
        } else {
            echo "Lỗi khi đặt hàng: " . $conn->error;
            exit();
        }
    } else {
        echo "<script>alert('Vui lòng chọn ít nhất một sản phẩm để thanh toán!');</script>";
    }
}

// Xử lý xóa sản phẩm được chọn
if (isset($_POST['delete']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $key => $item) {
        if (isset($_POST['select_' . $key]) && $_POST['select_' . $key] == 'on') {
            unset($_SESSION['cart'][$key]);
        }
    }
}

// Xử lý cập nhật số lượng
if (isset($_POST['save_quantity']) && !empty($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $key => $quantity) {
        if (isset($_SESSION['cart'][$key]) && $quantity > 0) {
            if (strpos($key, '_') !== false) {
                list($san_pham_id, $dung_tich) = explode('_', $key);
            } else {
                $san_pham_id = $key;
                $dung_tich = isset($_SESSION['cart'][$key]['dung_tich']) ? $_SESSION['cart'][$key]['dung_tich'] : '100ml';
            }
            $sql = "SELECT so_luong FROM san_pham_dung_tich WHERE san_pham_id = ? AND dung_tich = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $san_pham_id, $dung_tich);
            $stmt->execute();
            $result = $stmt->get_result();
            $stock = $result->fetch_assoc()['so_luong'] ?? 0;

            if ($quantity <= $stock) {
                $_SESSION['cart'][$key]['so_luong'] = (int)$quantity;
            } else {
                echo "<script>alert('Số lượng vượt quá tồn kho cho sản phẩm: " . htmlspecialchars($_SESSION['cart'][$key]['ten_san_pham']) . " (" . $dung_tich . ")');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/lab.css">
    <link rel="shortcut icon" href="image/lv-logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng - Louis Vuitton</title>
    <style>
        .cart-container { 
            padding: 20px; 
        }
        .cart-container .box { 
            display: flex; 
            flex-wrap: wrap; 
            justify-content: flex-start; 
        }
        .cart-container .box_shop {
            width: 25%; 
            min-height: 590px; 
            padding: 10px; 
            box-sizing: border-box; 
            transition: border 0.3s;
        }
        .cart-container .box_shop.selected { 
            border: 2px solid black; 
        }
        .cart-container .box_shop_image { 
            width: 100%; 
            height: auto; 
            margin: 0 auto; 
            display: block; 
        }
        .cart-container .box_shop_infomation { 
            padding: 10px; 
        }
        .cart-actions {
            width: 100%; 
            display: flex; 
            justify-content: space-between; 
            padding: 20px 0;
            border-top: 1px solid rgb(220, 220, 220); 
            border-bottom: 1px solid rgb(220, 220, 220);
        }
        .cart-actions .left-actions { 
            text-align: left; 
        }
        .cart-actions .right-actions { 
            text-align: right; 
        }
        .cart-actions button { 
            padding: 10px 20px; 
            margin-left: 10px; 
            cursor: pointer; 
        }
        .cart-actions .checkout-btn { 
            background-color: #000; 
            color: #fff; 
            border: none; 
        }
        .cart-actions .checkout-btn:hover { 
            background-color: #333; 
        }
        .cart-actions .delete-btn { 
            background-color: #fff; 
            color: #f00; 
            border: 1px solid #f00; 
        }
        .cart-actions .delete-btn:hover { 
            background-color: #fee; 
        }
        .cart-actions .save-btn { 
            background-color: #fff; 
            color: #000; 
            border: 1px solid #000; 
        }
        .cart-actions .save-btn:hover { 
            background-color: #f0f0f0; 
        }
        .cart-actions .select-all-btn { 
            background-color: #fff; 
            color: #000; 
            border: 1px solid #000; 
        }
        .cart-actions .select-all-btn:hover { 
            background-color: #f0f0f0; 
        }
        .cart-actions .right-actions .cart-total { 
            margin-top: 10px; 
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
    <a class="return_index" href="javascript:history.back()"> <h3 class="return_index_title"> ← Quay về</h3> </a>
    <h1 class="show_shop_info_title" >Giỏ Hàng</h1>
    <form method="POST" action="" class="cart-container">
        <div class="cart-actions">
            <div class="left-actions">
                <button type="button" class="select-all-btn" onclick="toggleSelectAll()">Chọn tất cả</button>
                <button type="submit" name="delete" class="delete-btn">Xóa</button>
                <button type="submit" name="save_quantity" class="save-btn">Lưu</button>
            </div>
            <div class="right-actions">
                <button type="submit" name="checkout" class="checkout-btn">Thanh toán</button>
                <div class="cart-total">
                    <p>Tổng tiền (chọn): <span id="total_price">0 ₫</span></p>
                </div>
            </div>
        </div>
        <?php
        if (!empty($_SESSION['cart'])) {
            echo '<div class="box">';
            foreach ($_SESSION['cart'] as $key => $item) {
                $thanh_tien = $item['gia'] * $item['so_luong'];
                if (strpos($key, '_') !== false) {
                    list($san_pham_id, $dung_tich) = explode('_', $key);
                } else {
                    $san_pham_id = $key;
                    $dung_tich = isset($item['dung_tich']) ? $item['dung_tich'] : '100ml';
                }
                ?>
                <div class="box_shop">
                    <input type="checkbox" name="select_<?php echo $key; ?>" id="select_<?php echo $key; ?>" onchange="toggleBorder(this)">
                    <a href="product_detail.php?id=<?php echo $san_pham_id; ?>">
                        <img class="box_shop_image" src="<?php echo htmlspecialchars($item['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($item['ten_san_pham']); ?>">
                    </a>
                    <div class="box_shop_infomation">
                        <p class="box_shop_infomation_name">
                            <a style="text-decoration: none; color: black;" href="product_detail.php?id=<?php echo $san_pham_id; ?>">
                                <?php echo htmlspecialchars($item['ten_san_pham']); ?>
                            </a>
                        </p>
                        <p class="box_shop_infomation_prices">
                            Dung tích: <?php echo htmlspecialchars($dung_tich); ?>
                        </p>
                        <p class="box_shop_infomation_prices">
                            Giá: <?php echo number_format($item['gia'], 0, ',', '.'); ?> ₫
                        </p>
                        <p>
                            Số lượng: 
                            <input type="number" name="quantity[<?php echo $key; ?>]" value="<?php echo $item['so_luong']; ?>" min="1" style="width: 50px;">
                        </p>
                        <p>Thành tiền: <?php echo number_format($thanh_tien, 0, ',', '.'); ?> ₫</p>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
        } else {
            echo '<p>Giỏ hàng trống!</p>';
        }
        ?>
    </form>
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
    function toggleBorder(checkbox) {
        const box = checkbox.parentElement;
        if (checkbox.checked) {
            box.classList.add('selected');
        } else {
            box.classList.remove('selected');
        }
        updateTotalPrice();
    }

    function updateTotalPrice() {
        let total = 0;
        <?php foreach ($_SESSION['cart'] as $key => $item): ?>
            if (document.getElementById('select_<?php echo $key; ?>') && document.getElementById('select_<?php echo $key; ?>').checked) {
                total += <?php echo $item['gia'] * $item['so_luong']; ?>;
            }
        <?php endforeach; ?>
        document.getElementById('total_price').innerText = total.toLocaleString('vi-VN') + ' ₫';
    }

    function toggleSelectAll() {
        const checkboxes = document.querySelectorAll('.box_shop input[type="checkbox"]');
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked; // Nếu tất cả đã chọn thì bỏ chọn, ngược lại chọn tất cả
            toggleBorder(checkbox); // Cập nhật viền
        });
    }

    window.onload = updateTotalPrice;

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