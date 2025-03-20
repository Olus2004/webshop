<?php
session_start();
require 'php/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "Bạn cần đăng nhập để thanh toán!";
    exit();
}

$user_id = $_SESSION['user_id'];
$tong_tien = 0;

// Lấy sản phẩm trong giỏ hàng
$sql_cart = "SELECT * FROM gio_hang WHERE nguoi_dung_id = $user_id";
$result_cart = $conn->query($sql_cart);

if ($result_cart->num_rows == 0) {
    echo "Giỏ hàng trống!";
    exit();
}

// Tính tổng tiền
while ($row = $result_cart->fetch_assoc()) {
    $tong_tien += $row['so_luong'] * getGiaSanPham($row['san_pham_id'], $conn);
}

// Tạo đơn hàng mới
$sql_order = "INSERT INTO don_hang (nguoi_dung_id, ngay_dat, tong_tien, trang_thai) 
              VALUES ($user_id, NOW(), $tong_tien, 'Đang xử lý')";
if ($conn->query($sql_order) === TRUE) {
    $order_id = $conn->insert_id;

    // Chuyển sản phẩm từ giỏ hàng vào chi tiết đơn hàng
    $result_cart->data_seek(0);
    while ($row = $result_cart->fetch_assoc()) {
        $san_pham_id = $row['san_pham_id'];
        $so_luong = $row['so_luong'];
        $gia = getGiaSanPham($san_pham_id, $conn);

        $sql_detail = "INSERT INTO chi_tiet_don_hang (don_hang_id, san_pham_id, so_luong, gia) 
                       VALUES ($order_id, $san_pham_id, $so_luong, $gia)";
        $conn->query($sql_detail);
    }

    // Xóa giỏ hàng sau khi thanh toán
    $sql_clear_cart = "DELETE FROM gio_hang WHERE nguoi_dung_id = $user_id";
    $conn->query($sql_clear_cart);

    echo "Thanh toán thành công! Đơn hàng đã được tạo.";
} else {
    echo "Lỗi khi tạo đơn hàng: " . $conn->error;
}

$conn->close();

// Hàm lấy giá sản phẩm từ bảng san_pham
function getGiaSanPham($san_pham_id, $conn) {
    $sql = "SELECT gia FROM san_pham WHERE id = $san_pham_id";
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_assoc()['gia'] : 0;
}
?>
