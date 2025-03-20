<?php
session_start();
require 'php/db_connect.php';

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: Lab0_mylv.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Truy vấn thông tin người dùng, bao gồm avatar
$sql = "SELECT ten_dang_nhap, email, so_dien_thoai, dia_chi, ngay_sinh, ngay_tao, role, avatar 
        FROM nguoi_dung 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Xử lý cập nhật thông tin nếu form được gửi
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $dia_chi = $_POST['dia_chi'];
    $ngay_sinh = $_POST['ngay_sinh'];
    $mat_khau_moi = $_POST['mat_khau_moi'];
    $xac_nhan_mat_khau = $_POST['xac_nhan_mat_khau'];

    $update_sql = "UPDATE nguoi_dung SET email = ?, so_dien_thoai = ?, dia_chi = ?, ngay_sinh = ?";
    $params = [$email, $so_dien_thoai, $dia_chi, $ngay_sinh];
    $types = "ssss";

    // Xử lý upload avatar
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $target_dir = "image/lv/avt/";
        $file_name = basename($_FILES["avatar"]["name"]);
        $target_file = $target_dir . time() . "_" . $file_name; // Thêm timestamp để tránh trùng tên
        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            $update_sql .= ", avatar = ?";
            $params[] = $target_file;
            $types .= "s";
        } else {
            $message = "Có lỗi khi upload avatar!";
            goto skip_update;
        }
    }

    // Xử lý mật khẩu mới
    if (!empty($mat_khau_moi)) {
        if ($mat_khau_moi === $xac_nhan_mat_khau) {
            $hashed_password = password_hash($mat_khau_moi, PASSWORD_BCRYPT);
            $update_sql .= ", mat_khau = ?";
            $params[] = $hashed_password;
            $types .= "s";
        } else {
            $message = "Mật khẩu xác nhận không khớp!";
            goto skip_update;
        }
    }

    $update_sql .= " WHERE id = ?";
    $params[] = $user_id;
    $types .= "i";

    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param($types, ...$params);
    if ($update_stmt->execute()) {
        $message = "Cập nhật thông tin thành công!";
        $user['email'] = $email;
        $user['so_dien_thoai'] = $so_dien_thoai;
        $user['dia_chi'] = $dia_chi;
        $user['ngay_sinh'] = $ngay_sinh;
        if (isset($target_file)) {
            $user['avatar'] = $target_file;
        }
    } else {
        $message = "Có lỗi xảy ra khi cập nhật!";
    }

    skip_update:
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/lab.css">
    <link rel="shortcut icon" href="image/lv-logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Louis Vuitton</title>
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
        <div class="profile-container">
            <div class="profile-header">
                <img src="<?php echo file_exists($user['avatar']) ? htmlspecialchars(str_replace('\\', '/', $user['avatar'])) : 'image/default-avatar.png'; ?>" alt="Avatar">
                <h1>@<?php echo htmlspecialchars($user['ten_dang_nhap']); ?></h1>
                <p><?php echo $user['role'] == 'admin' ? 'Admin' : 'Thành viên'; ?></p>
            </div>
            <div class="profile-info">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($user['so_dien_thoai']); ?></p>
                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($user['dia_chi']); ?></p>
                <p><strong>Ngày sinh:</strong> <?php echo $user['ngay_sinh'] ? date('d/m/Y', strtotime($user['ngay_sinh'])) : 'Chưa cập nhật'; ?></p>
                <p><strong>Ngày tạo tài khoản:</strong> <?php echo date('d/m/Y', strtotime($user['ngay_tao'])); ?></p>
            </div>
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            <div style="text-align: center;">
                <span class="edit-btn" id="edit-btn">Chỉnh sửa thông tin</span>
            </div>
        </div>

        <!-- Overlay và Form chỉnh sửa -->
        <div class="overlay" id="overlay"></div>
        <div class="edit-form" id="edit-form">
            <h2>Chỉnh sửa thông tin</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                
                <label for="so_dien_thoai">Số điện thoại:</label>
                <input type="text" id="so_dien_thoai" name="so_dien_thoai" value="<?php echo htmlspecialchars($user['so_dien_thoai']); ?>" required>
                
                <label for="dia_chi">Địa chỉ:</label>
                <input type="text" id="dia_chi" name="dia_chi" value="<?php echo htmlspecialchars($user['dia_chi']); ?>" required>
                
                <label for="ngay_sinh">Ngày sinh:</label>
                <input type="date" id="ngay_sinh" name="ngay_sinh" value="<?php echo htmlspecialchars($user['ngay_sinh']); ?>">
                
                <label for="avatar">Avatar (chọn file mới để thay đổi):</label>
                <input type="file" id="avatar" name="avatar" accept="image/*">
                
                <label for="mat_khau_moi">Mật khẩu mới (để trống nếu không đổi):</label>
                <input type="password" id="mat_khau_moi" name="mat_khau_moi">
                
                <label for="xac_nhan_mat_khau">Xác nhận mật khẩu:</label>
                <input type="password" id="xac_nhan_mat_khau" name="xac_nhan_mat_khau">
                
                <div class="button-group">
                    <button type="submit">Lưu</button>
                    <button type="button" class="close-form" id="close-form">Hủy</button>
                </div>
            </form>
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
        const editBtn = document.getElementById('edit-btn');
        const editForm = document.getElementById('edit-form');
        const overlay = document.getElementById('overlay');
        const closeFormBtn = document.getElementById('close-form');

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

        editBtn.addEventListener('click', () => {
            editForm.classList.add('active');
            overlay.classList.add('active');
        });

        closeFormBtn.addEventListener('click', () => {
            editForm.classList.remove('active');
            overlay.classList.remove('active');
        });

        overlay.addEventListener('click', () => {
            editForm.classList.remove('active');
            overlay.classList.remove('active');
        });
    </script>
</body>
</html>