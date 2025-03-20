<?php
session_start();
require '../php/db_connect.php';

// Kiểm tra vai trò người dùng (chỉ admin mới truy cập được)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Xử lý cập nhật trạng thái đơn hàng
if (isset($_POST['update_order'])) {
    $id = $_POST['id'];
    $trang_thai = $_POST['trang_thai'];

    $sql = "UPDATE don_hang SET trang_thai = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $trang_thai, $id);
    $stmt->execute();
}

// Lấy thông tin đơn hàng để sửa (bao gồm chi tiết)
$edit_order = null;
$order_details = [];
if (isset($_GET['edit_order'])) {
    $id = $_GET['edit_order'];
    // Lấy thông tin đơn hàng
    $sql = "SELECT dh.*, nd.ten_dang_nhap 
            FROM don_hang dh 
            JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id 
            WHERE dh.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_order = $result->fetch_assoc();

    // Lấy chi tiết đơn hàng
    $sql = "SELECT ctdh.*, sp.ten_san_pham 
            FROM chi_tiet_don_hang ctdh 
            JOIN san_pham sp ON ctdh.san_pham_id = sp.id 
            WHERE ctdh.don_hang_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order_details = $result->fetch_all(MYSQLI_ASSOC);
}

// Lấy danh sách đơn hàng
$sql = "SELECT dh.*, nd.ten_dang_nhap 
        FROM don_hang dh 
        JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id";
$result = $conn->query($sql);
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../css/lab.css">
    <link rel="shortcut icon" href="../image/lv-logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Louis Vuitton</title>
</head>
<body>
<header>
        <nav class="nav">
            <p>
                <b>
                    <a id="logo" href="../index.php">LOUIS VUITTON</a>
                </b>
            </p>
            <a href="#" class="nav_buttom" id="menu-btn">☰ Menu</a>
            <a href="../search.php" class="nav_buttom" id="search">Tìm kiếm</a>
            <a href="../blog.php" class="nav_buttom" id="blog">Tin tức</a>
            <a href="../product.php" class="nav_buttom" id="product">Sản phẩm</a>
            <a href="../cart.php" class="nav_buttom" id="wishlist">Giỏ hàng</a>

            <?php if (isset($_SESSION['ten_dang_nhap'])): ?>
                <!-- Khi đã đăng nhập -->
                <a href="../profile.php" id="user_info">
                <span>Xin chào, <b><?php echo htmlspecialchars($_SESSION['ten_dang_nhap']); ?></b></span></a>
            <?php else: ?>
                <!-- Khi chưa đăng nhập -->
                <a href="../login.php" class="nav_buttom" id="user_info">Đăng nhập</a>
            <?php endif; ?>
        </nav>

        <!-- Menu bật ra bên phải -->
        <div class="side-menu" id="side-menu">
            <a href="#" class="close-btn" id="close-btn">x</a>
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="../menu.php">Menu</a></li>
                <li><a href="../search.php">Tìm kiếm</a></li>
                <li><a href="../oder.php">Đơn hàng của tôi</a></li>
                <li><a href="../cart.php">Giỏ hàng</a></li>
                <?php if (isset($_SESSION['ten_dang_nhap'])): ?>
                    <li><a href="../controller/logout.php">Đăng xuất</a></li>
                <?php else: ?>
                    <li><a href="../login.php">Đăng nhập</a></li>
                    <li><a href="../register.php">Đăng ký</a></li>
                <?php endif; ?>
                
            </ul>
        </div>
    </header>

    <main>
        <div class="nav_title">
            <p class="title_nav">Quản lý đơn hàng</p>
        </div>

        <!-- Form sửa trạng thái đơn hàng -->
        <?php if ($edit_order): ?>
            <div class="form-container">
                <h2>Cập nhật trạng thái đơn hàng #<?php echo $edit_order['id']; ?></h2>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $edit_order['id']; ?>">
                    <p>Người đặt: <?php echo $edit_order['ten_dang_nhap']; ?></p>
                    <p>Ngày đặt: <?php echo $edit_order['ngay_dat']; ?></p>
                    <p>Tổng tiền: <?php echo number_format($edit_order['tong_tien']); ?> VNĐ</p>
                    <label for="trang_thai">Trạng thái:</label>
                    <select name="trang_thai" id="trang_thai" required>
                        <option value="Chưa thanh toán" <?php if ($edit_order['trang_thai'] === 'Chưa thanh toán') echo 'selected'; ?>>Chưa thanh toán</option>
                        <option value="Đã thanh toán" <?php if ($edit_order['trang_thai'] === 'Đã thanh toán') echo 'selected'; ?>>Đã thanh toán</option>
                        <option value="Đang giao hàng" <?php if ($edit_order['trang_thai'] === 'Đang giao hàng') echo 'selected'; ?>>Đang giao hàng</option>
                        <option value="Đã giao hàng" <?php if ($edit_order['trang_thai'] === 'Đã giao hàng') echo 'selected'; ?>>Đã giao hàng</option>
                        <option value="Đã hủy" <?php if ($edit_order['trang_thai'] === 'Đã hủy') echo 'selected'; ?>>Đã hủy</option>
                    </select>
                    <button type="submit" name="update_order">Cập nhật trạng thái</button>
                </form>

                <!-- Hiển thị chi tiết đơn hàng -->
                <h3>Chi tiết đơn hàng</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Giá (VNĐ)</th>
                            <th>Tổng (VNĐ)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_details as $detail): ?>
                            <tr>
                                <td><?php echo $detail['ten_san_pham']; ?></td>
                                <td><?php echo $detail['so_luong']; ?></td>
                                <td><?php echo number_format($detail['gia']); ?></td>
                                <td><?php echo number_format($detail['so_luong'] * $detail['gia']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Danh sách đơn hàng -->
        <div class="order-list">
            <h2>Danh sách đơn hàng</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Người đặt</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền (VNĐ)</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo $order['ten_dang_nhap']; ?></td>
                            <td><?php echo $order['ngay_dat']; ?></td>
                            <td><?php echo number_format($order['tong_tien']); ?></td>
                            <td><?php echo $order['trang_thai'] ?: 'Chưa thanh toán'; ?></td>
                            <td>
                                <a href="?edit_order=<?php echo $order['id']; ?>">Sửa trạng thái</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <footer>
        <nav class="nav_footer">
            <a class="footer_1" href="../index.php">Home</a>
            <a class="footer_1" href="../Oder-Menu.php">Menu</a>
            <a class="footer_1" href="../hotline.php">Contact</a>
            <a class="footer_1" href="../sinhvien.php">Thành viên trong nhóm</a>
        </nav>
    </footer>
</body>
</html>