<?php
session_start();
require '../php/db_connect.php';

// Kiểm tra ID sản phẩm
if (!isset($_GET['id'])) {
    echo "Không có sản phẩm nào được chọn!";
    exit();
}

$id = intval($_GET['id']);

// Lấy thông tin sản phẩm từ CSDL
$sql = "SELECT * FROM san_pham WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Sản phẩm không tồn tại!";
    exit();
}

$product = $result->fetch_assoc();

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Thêm hoặc cập nhật sản phẩm trong giỏ
if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]['so_luong'] += 1; // Tăng số lượng nếu đã có
} else {
    $_SESSION['cart'][$id] = [
        'ten_san_pham' => $product['ten_san_pham'],
        'gia' => $product['gia'],
        'hinh_anh' => $product['hinh_anh'],
        'so_luong' => 1
    ];
}

// Chuyển hướng đến giỏ hàng
header("Location: ../cart.php");
exit();
?>