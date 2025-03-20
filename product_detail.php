<?php
session_start();
require 'php/db_connect.php';

// Lấy ID sản phẩm từ URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Lấy thông tin sản phẩm (bao gồm video_path)
$sql = "SELECT sp.*, sp.video_path 
        FROM san_pham sp 
        WHERE sp.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "Sản phẩm không tồn tại!";
    exit();
}

// Lấy 2 hình ảnh từ bảng san_pham_hinh_anh
$sql_images = "SELECT hinh_anh FROM san_pham_hinh_anh WHERE san_pham_id = ? LIMIT 2";
$stmt_images = $conn->prepare($sql_images);
$stmt_images->bind_param("i", $id);
$stmt_images->execute();
$images_result = $stmt_images->get_result();
$images = $images_result->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách dung tích và giá
$sql = "SELECT dung_tich, gia, so_luong, tinh_trang FROM san_pham_dung_tich WHERE san_pham_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$capacities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Xử lý thêm vào giỏ hàng
$message = '';
if (isset($_POST['add_to_cart']) && isset($_POST['dung_tich'])) {
    $san_pham_id = $id;
    $dung_tich = $_POST['dung_tich'];
    $key = $san_pham_id . '_' . $dung_tich;

    $sql = "SELECT sp.ten_san_pham, 
            (SELECT hinh_anh FROM san_pham_hinh_anh WHERE san_pham_id = sp.id LIMIT 1) AS hinh_anh, 
            spdt.gia, spdt.so_luong 
            FROM san_pham sp 
            JOIN san_pham_dung_tich spdt ON sp.id = spdt.san_pham_id 
            WHERE sp.id = ? AND spdt.dung_tich = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $san_pham_id, $dung_tich);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product && $product['so_luong'] > 0) {
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['so_luong'] += 1;
        } else {
            $_SESSION['cart'][$key] = [
                'ten_san_pham' => $product['ten_san_pham'],
                'hinh_anh' => $product['hinh_anh'],
                'gia' => $product['gia'],
                'so_luong' => 1,
                'dung_tich' => $dung_tich
            ];
        }
        $message = 'success';
    } else {
        $message = 'error';
    }
}

// Xử lý gửi đánh giá
if (isset($_POST['submit_review']) && isset($_SESSION['user_id'])) {
    $diem_danh_gia = intval($_POST['diem_danh_gia']);
    $noi_dung = trim($_POST['noi_dung']);
    $nguoi_dung_id = $_SESSION['user_id'];

    $sql = "INSERT INTO danh_gia (san_pham_id, nguoi_dung_id, diem_danh_gia, noi_dung) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $id, $nguoi_dung_id, $diem_danh_gia, $noi_dung);
    $stmt->execute();
}

// Lấy danh sách đánh giá
$sql = "SELECT dg.*, nd.ten_dang_nhap 
        FROM danh_gia dg 
        JOIN nguoi_dung nd ON dg.nguoi_dung_id = nd.id 
        WHERE dg.san_pham_id = ? 
        ORDER BY dg.ngay_danh_gia DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Tính điểm trung bình
$sql = "SELECT AVG(diem_danh_gia) as avg_rating FROM danh_gia WHERE san_pham_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$avg_rating = $stmt->get_result()->fetch_assoc()['avg_rating'];

// Mô tả mẫu với xuống dòng
$mo_ta_mau = "Các nốt hương chính:\nGỗ trầm hương\nNhựa thơm Benzoin\nHương trầm\nQuả mâm xôi\n\nCác lựa chọn dung tích nước hoa khác:\nChai xịt 100ml và chai xịt 200ml.\nBộ nước hoa du lịch dạng xịt. Được thiết kế để đồng hành cùng bạn trong mọi chuyến đi, bộ sản phẩm này bao gồm một bình xịt và 4 lõi nước hoa dung tích 7,5ml. Được thiết kế với nắp đậy nam châm, các lõi nước hoa kiểu mới này dễ dàng gắn vào bình xịt, sẵn sàng cho một hành trình mới.\nBộ lõi nước hoa du lịch: Bộ lõi này chỉ được sử dụng với bộ nước hoa du lịch Louis Vuitton.\n\nThành phần:\n\nCỒN, HƯƠNG THƠM, NƯỚC, CITRONELLOL, ETHYLHEXYL METHOXYCINNAMATE, LINALOOL, BHT, BUTYL METHOXYDIBENZOYLMETHANE, BUTYLENE GLYCOL DICAPRYLATE/DICAPRATE, GERANIOL, CINNAMYL ALCOHOL, FARNESOL, LIMONENE, RƯỢU BENZYL, BENZYL BENZOATE, EUGENOL, CITRAL, ISOEUGENOL, TOCOPHEROL, CI 60730 (TÍM SYRAH ĐẬM 2), CI 14700 (ĐỎ 4), TOCOPHEROL, CI 19140 (VÀNG 5)\n\nLưu ý: Danh sách thành phần sản phẩm của Louis Vuitton được cập nhật định kỳ. Trước khi sử dụng, vui lòng đọc kỹ bảng thành phần trên bao bì để kiểm tra sự phù hợp với bạn.\n\nLưu ý: Vui lòng đọc kỹ hướng dẫn sử dụng";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/lab.css">
    <link rel="shortcut icon" href="image/lv-logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sản phẩm: <?php echo htmlspecialchars($row['ten_san_pham']); ?></title>
    <style>
        
        /* Container cho video và ảnh */
        .show_shop_info_box_image {
            width: 100%;
            height: 700px; /* Đặt chiều cao cố định để video và ảnh đồng bộ */
            position: relative;
            overflow-y: auto; /* Cho phép cuộn dọc */
            scrollbar-width: none; /* Ẩn thanh cuộn trên Firefox */
            -ms-overflow-style: none; /* Ẩn thanh cuộn trên IE/Edge */
        }

        /* Ẩn thanh cuộn trên Chrome, Safari */
        .show_shop_info_box_image::-webkit-scrollbar {
            display: none;
        }

        /* Style cho video và ảnh */
        .show_shop_info_box_image video,
        .show_shop_info_box_image img {
            width: 100%;
            height: 100%; /* Khớp chiều cao thẻ cha */
            object-fit: cover; /* Đảm bảo lấp đầy mà không méo */
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
<main>
    <h1 class="show_shop_info_title">Thông tin sản phẩm</h1>
    <div class="show_shop">
        <div class="show_shop_info">
        <div class="show_shop_info_box_image">
            <?php if (!empty($row['video_path'])): ?>
                <video autoplay muted loop>
                    <source src="<?php echo htmlspecialchars($row['video_path']); ?>" type="video/mp4">
                    Trình duyệt của bạn không hỗ trợ video.
                </video>
            <?php endif; ?>
            <?php foreach ($images as $image): ?>
                <?php if (!is_null($image['hinh_anh'])): ?>
                    <img src="<?php echo htmlspecialchars($image['hinh_anh']); ?>" class="show_shop_info_image" alt="<?php echo htmlspecialchars($row['ten_san_pham']); ?>">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
                <div class="show_shop_info_box">
            </div>
        </div>

        <div class="show_shop_info">
            <div class="show_shop_info_prices">
                <p class="show_shop_info_id">ID#<?php echo $row['id']; ?></p>
                <h2 class="show_shop_info_name"><?php echo htmlspecialchars($row['ten_san_pham']); ?></h2>
                
                <form method="POST" action="">
                    <div class="capacity-options">
                        <label>Chọn dung tích:</label><br>
                        <?php foreach ($capacities as $index => $capacity): ?>
                            <input type="radio" name="dung_tich" 
                                   id="capacity_<?php echo $index; ?>" 
                                   value="<?php echo htmlspecialchars($capacity['dung_tich']); ?>" 
                                   data-price="<?php echo $capacity['gia']; ?>" 
                                   data-stock="<?php echo $capacity['so_luong']; ?>" 
                                   <?php echo $index === 0 ? 'checked' : ''; ?> 
                                   onchange="updatePrice(this)">
                            <label for="capacity_<?php echo $index; ?>">
                                <?php echo htmlspecialchars($capacity['dung_tich']) . ' - ' . number_format($capacity['gia'], 0, ',', '.') . ' ₫ (' . $capacity['tinh_trang'] . ')'; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <h3 class="show_shop_info_prices_title">
                        Giá: <span id="price_display"><?php echo number_format($capacities[0]['gia'], 0, ',', '.'); ?></span> ₫ (Đã bao gồm thuế VAT)
                    </h3>

                    <div class="description-container">
                        <p class="show_shop_info_description"><?php echo nl2br(htmlspecialchars($mo_ta_mau)); ?></p>
                        <button type="button" class="read-more-btn" onclick="toggleDescription()">Xem thêm</button>
                    </div>

                    <div class="show_shop_oder">
                        <button type="submit" name="add_to_cart" class="show_shop_oder_button">Thêm vào giỏ hàng</button>
                    </div>
                    <input type="hidden" name="san_pham_id" value="<?php echo $id; ?>">
                </form>
            </div>

            <div class="show_shop_support">
                <ul>
                    <li class="show_shop_support_info"><a href="#" id="warranty-link">Chi tiết bảo hành sản phẩm</a></li>
                    <li class="show_shop_support_info"><a href="#" id="policy-link">Chính sách giao hàng và Đổi trả</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal bảo hành -->
    <div class="modal" id="warranty-modal">
        <a href="#" class="close-btn" id="warranty-close-btn">x</a>
        <h2>Chi tiết bảo hành sản phẩm</h2>
        <p>Đây là thông tin chi tiết về chính sách bảo hành sản phẩm của Louis Vuitton:</p>
        <ul>
            <li>Thời gian bảo hành: 12 tháng kể từ ngày mua.</li>
            <li>Áp dụng cho các lỗi kỹ thuật do sản xuất.</li>
            <li>Không bảo hành cho hư hỏng do sử dụng sai cách hoặc hao mòn tự nhiên.</li>
            <li>Vui lòng mang sản phẩm cùng hóa đơn đến cửa hàng chính hãng để được hỗ trợ.</li>
        </ul>
        <p>Liên hệ hotline: 1800-XXXX để biết thêm chi tiết.</p>
    </div>

    <!-- Modal chính sách giao hàng và đổi trả -->
    <div class="modal" id="policy-modal">
        <a href="#" class="close-btn" id="policy-close-btn">x</a>
        <div class="policy-item" id="shipping-info">
            <h3>Thông tin giao hàng</h3>
            <p>Giao hàng miễn phí hoặc nhận tại cửa hàng</p>
        </div>
        <div class="policy-item" id="return-policy">
            <h3>Chính sách đổi hàng</h3>
            <p>Dành cho đơn hàng mua tại cửa hàng và trực tuyến</p>
        </div>
        <div class="policy-item" id="packaging-info">
            <h3>Đóng gói</h3>
            <p>Chiếc hộp tinh tế và thân thiện với môi trường được lấy cảm hứng từ di sản của Louis Vuitton</p>
        </div>
    </div>

    <!-- Modal chi tiết giao hàng -->
    <div class="modal" id="shipping-modal">
        <a href="#" class="close-btn" id="shipping-close-btn">x</a>
        <h2>Thông tin giao hàng</h2>
        <p>Louis Vuitton chỉ giao hàng ở quốc gia quý khách đã đặt hàng trực tuyến hoặc qua điện thoại.</p>
        <p><strong>Lưu ý:</strong><br>
        Quý khách có thể tìm mã theo dõi đơn đặt hàng trong email Xác nhận giao hàng hoặc theo dõi tình trạng giao hàng trên trang ĐƠN ĐẶT HÀNG CỦA TÔI trong tài khoản MyLV.</p>
        <p><strong>Trường hợp ngoại lệ:</strong><br>
        Vui lòng lưu ý rằng đơn đặt hàng My LV Heritage và My LV World Tour sẽ được giao trong vòng 8 tuần, còn sản phẩm đặt làm riêng sẽ được giao trong vòng 5 tuần tại Singapore, Malaysia và New Zealand.<br>
        Khi gửi đi, chúng tôi sẽ gói quà cho mọi sản phẩm (ngoại trừ sổ tay kế hoạch). Để biết thêm thông tin về giao hàng hoặc thay đổi lịch giao hàng, vui lòng liên hệ với Trung tâm Tư vấn khách hàng.</p>
    </div>

    <!-- Modal chính sách đổi hàng -->
    <div class="modal" id="return-modal">
        <a href="#" class="close-btn" id="return-close-btn">x</a>
        <h2>Chính sách đổi hàng</h2>
        <p>Nếu mua sản phẩm của Louis Vuitton qua điện thoại, quý khách có thể đổi hàng trong vòng 30 ngày kể từ ngày nhận hàng.</p>
        <p>Không áp dụng chính sách đổi hàng đối với mọi sản phẩm có sử dụng dịch vụ cá nhân hóa (dập nóng, khắc tên, My LV Heritage, My LV World Tour, v.v.).<br>
        Có thể đổi Đồng hồ và Trang sức lấy sản phẩm khác cùng danh mục. Khi quý khách có nhu cầu đổi Đồng hồ và Trang sức, vui lòng gửi sản phẩm trong bao bì gốc, kèm theo phiếu hướng dẫn, giấy bảo hành, giấy chứng nhận sản phẩm như COSC và GIA (nếu có), cùng hóa đơn hoặc biên lai nhận hàng.<br>
        Nước hoa là hàng hóa dễ cháy nên phải tuân theo các luật và quy định về vận chuyển. Trước khi quý khách trả hàng, vui lòng liên hệ với Trung tâm Tư vấn khách hàng để được trợ giúp. Mọi trường hợp đổi hàng nước hoa do quý khách đổi ý, sản phẩm phải còn nguyên trong bao bì gốc (hộp trắng), chưa mở và còn nguyên tem. Nếu quý khách không thực hiện theo đúng hướng dẫn, sản phẩm có nguy cơ bị tịch thu bởi các cơ quan nhà nước, liên bang và quốc tế quản lý việc vận chuyển nước hoa an toàn và hợp pháp.<br>
        Danh sách các quốc gia không áp dụng chính sách đổi hàng đa quốc gia: Việt Nam, Brazil, Trung Quốc, Colombia, Ấn Độ, Jordan, Kazakhstan, Lebanon, Mexico, Cộng hoà Dominica, Nga, Đài Loan, Thái Lan, Thổ Nhĩ Kỳ và Ukraine. Ngoài các quốc gia này, khách hàng có thể đổi hàng trên toàn thế giới, ở bất kì cửa hàng chính hãng nào của Louis Vuitton kèm các điều kiện phía trên.</p>
        <p><strong>LÀM THẾ NÀO ĐỂ ĐỔI ĐƠN HÀNG?</strong><br>
        <strong>Liên hệ với Trung tâm Tư vấn khách hàng</strong><br>
        Chuyên viên tư vấn khách hàng sẽ sắp xếp để nhận lại sản phẩm.<br>
        Quý khách sẽ nhận được email xác nhận yêu cầu đổi hàng, cũng như mọi thông tin nhận hàng. Yêu cầu hủy sẽ có hiệu lực từ ngày quý khách gọi điện cho chúng tôi.<br>
        Đóng sản phẩm vào bao bì gốc của Louis Vuitton cùng với hóa đơn gốc và phiếu đổi trả, sau đó dán kín gói hàng.<br>
        Giao gói hàng cho nhân viên vận chuyển.<br>
        <strong>Tại cửa hàng</strong><br>
        Đối với nước hoa, quý khách chỉ được đổi tại cửa hàng ở quốc gia mua hàng.<br>
        Sau khi nhận được hàng và chuyển cho Bộ phận dịch vụ chất lượng kiểm tra, chúng tôi sẽ tiến hành đổi hàng. Louis Vuitton sẽ cố gắng xử lý yêu cầu đổi hàng nhanh nhất có thể, thường không quá 30 ngày. Sau khi quý khách hủy đơn đặt hàng, quá trình xử lý yêu cầu hoàn tiền có thể mất đến 14 ngày.</p>
    </div>

    <!-- Modal đóng gói -->
    <div class="modal" id="packaging-modal">
        <a href="#" class="close-btn" id="packaging-close-btn">x</a>
        <h2>Đóng gói</h2>
        <p>[Thẻ ảnh sẽ được sửa sau]</p>
        <p>Tất cả sản phẩm của Louis Vuitton được gói trong chiếc hộp biểu tượng màu nâu cam "Safran Impérial". Hộp được nhấn nhá với màu xanh dương, gợi nhớ đến những dải ruy băng của Maison và tông màu được dùng để cá nhân hóa sản phẩm từ năm 1854. Sự kết hợp màu sắc tương phản tạo nên vẻ đẹp sang trọng vượt thời gian và nét tinh tế đương đại, như bài ngợi ca về thời kỳ hoàn kim của nghệ thuật du lịch.</p>
    </div>

    <!-- Lớp phủ mờ -->
    <div class="overlay" id="overlay"></div>

    <!-- Phần đánh giá -->
    <div class="review-section">
        <h2>Đánh giá sản phẩm</h2>
        
        <!-- Tóm tắt đánh giá trung bình -->
        <?php if ($avg_rating): ?>
            <div class="review-summary">
                <div class="avg-rating"><?php echo number_format($avg_rating, 1); ?></div>
                <div class="stars">
                    <?php
                    $full_stars = floor($avg_rating);
                    $half_star = $avg_rating - $full_stars >= 0.5 ? 1 : 0;
                    $empty_stars = 5 - $full_stars - $half_star;
                    for ($i = 0; $i < $full_stars; $i++) echo '★';
                    if ($half_star) echo '☆';
                    for ($i = 0; $i < $empty_stars; $i++) echo '☆';
                    ?>
                </div>
                <div class="review-count"><?php echo count($reviews); ?> đánh giá</div>
            </div>
        <?php endif; ?>

        <!-- Form gửi đánh giá -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="review-form">
                <form method="POST" action="">
                    <label for="diem_danh_gia">Điểm đánh giá:</label>
                    <select name="diem_danh_gia" id="diem_danh_gia" required>
                        <option value="5">★★★★★ (5 sao)</option>
                        <option value="4">★★★★☆ (4 sao)</option>
                        <option value="3">★★★☆☆ (3 sao)</option>
                        <option value="2">★★☆☆☆ (2 sao)</option>
                        <option value="1">★☆☆☆☆ (1 sao)</option>
                    </select>
                    <label for="noi_dung">Nhận xét của bạn:</label>
                    <textarea name="noi_dung" id="noi_dung" placeholder="Viết nhận xét của bạn..." required></textarea>
                    <button type="submit" name="submit_review">Gửi đánh giá</button>
                </form>
            </div>
        <?php else: ?>
            <p class="review-login">Vui lòng <a href="login.php">đăng nhập</a> để gửi đánh giá.</p>
        <?php endif; ?>

        <!-- Danh sách đánh giá -->
        <div class="review-list">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <span class="username"><?php echo htmlspecialchars($review['ten_dang_nhap']); ?></span>
                            <span class="stars">
                                <?php
                                for ($i = 0; $i < $review['diem_danh_gia']; $i++) echo '★';
                                for ($i = $review['diem_danh_gia']; $i < 5; $i++) echo '☆';
                                ?>
                            </span>
                        </div>
                        <span class="date"><?php echo date('d/m/Y', strtotime($review['ngay_danh_gia'])); ?></span>
                        <div class="review-content"><?php echo htmlspecialchars($review['noi_dung'] ?: 'Không có nội dung'); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Chưa có đánh giá nào cho sản phẩm này.</p>
            <?php endif; ?>
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
<!-- Thẻ toast để hiển thị thông báo -->
<div id="toast" class="toast"></div>

<script>
    function updatePrice(radio) {
        const price = radio.getAttribute('data-price');
        document.getElementById('price_display').innerText = Number(price).toLocaleString('vi-VN');
    }

    function showToast(message, type) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'toast ' + type;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    function toggleDescription() {
        const description = document.querySelector('.show_shop_info_description');
        const button = document.querySelector('.read-more-btn');
        description.classList.toggle('expanded');
        button.textContent = description.classList.contains('expanded') ? 'Thu gọn' : 'Xem thêm';
    }

    // Xử lý modal
    const body = document.body;
    const overlay = document.getElementById('overlay');

    // Modal bảo hành
    const warrantyLink = document.getElementById('warranty-link');
    const warrantyModal = document.getElementById('warranty-modal');
    const warrantyCloseBtn = document.getElementById('warranty-close-btn');

    warrantyLink.addEventListener('click', (e) => {
        e.preventDefault();
        warrantyModal.classList.add('active');
        overlay.classList.add('active');
        body.classList.add('modal-open');
    });

    warrantyCloseBtn.addEventListener('click', (e) => {
        e.preventDefault();
        warrantyModal.classList.remove('active');
        overlay.classList.remove('active');
        body.classList.remove('modal-open');
    });

    // Modal chính sách giao hàng và đổi trả
    const policyLink = document.getElementById('policy-link');
    const policyModal = document.getElementById('policy-modal');
    const policyCloseBtn = document.getElementById('policy-close-btn');

    policyLink.addEventListener('click', (e) => {
        e.preventDefault();
        policyModal.classList.add('active');
        overlay.classList.add('active');
        body.classList.add('modal-open');
    });

    policyCloseBtn.addEventListener('click', (e) => {
        e.preventDefault();
        policyModal.classList.remove('active');
        overlay.classList.remove('active');
        body.classList.remove('modal-open');
    });

    // Modal chi tiết giao hàng
    const shippingInfo = document.getElementById('shipping-info');
    const shippingModal = document.getElementById('shipping-modal');
    const shippingCloseBtn = document.getElementById('shipping-close-btn');

    shippingInfo.addEventListener('click', () => {
        policyModal.classList.remove('active');
        shippingModal.classList.add('active');
    });

    shippingCloseBtn.addEventListener('click', (e) => {
        e.preventDefault();
        shippingModal.classList.remove('active');
        overlay.classList.remove('active');
        body.classList.remove('modal-open');
    });

    // Modal chính sách đổi hàng
    const returnPolicy = document.getElementById('return-policy');
    const returnModal = document.getElementById('return-modal');
    const returnCloseBtn = document.getElementById('return-close-btn');

    returnPolicy.addEventListener('click', () => {
        policyModal.classList.remove('active');
        returnModal.classList.add('active');
    });

    returnCloseBtn.addEventListener('click', (e) => {
        e.preventDefault();
        returnModal.classList.remove('active');
        overlay.classList.remove('active');
        body.classList.remove('modal-open');
    });

    // Modal đóng gói
    const packagingInfo = document.getElementById('packaging-info');
    const packagingModal = document.getElementById('packaging-modal');
    const packagingCloseBtn = document.getElementById('packaging-close-btn');

    packagingInfo.addEventListener('click', () => {
        policyModal.classList.remove('active');
        packagingModal.classList.add('active');
    });

    packagingCloseBtn.addEventListener('click', (e) => {
        e.preventDefault();
        packagingModal.classList.remove('active');
        overlay.classList.remove('active');
        body.classList.remove('modal-open');
    });

    // Đóng modal khi nhấn overlay
    overlay.addEventListener('click', () => {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
        overlay.classList.remove('active');
        body.classList.remove('modal-open');
    });

    <?php if ($message === 'success'): ?>
        showToast('Đã thêm vào giỏ hàng!', 'success');
    <?php elseif ($message === 'error'): ?>
        showToast('Sản phẩm đã hết hàng!', 'error');
    <?php endif; ?>

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