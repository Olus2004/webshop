<?php
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $ten_san_pham = $_POST['ten_san_pham'];
    $mo_ta = $_POST['mo_ta'];
    $danh_muc = $_POST['danh_muc'];
    $dung_tich = $_POST['dung_tich'];
    $so_luong = $_POST['so_luong'];
    $tinh_trang = $_POST['tinh_trang'];
    $gia = $_POST['gia'];

    // Cập nhật bảng san_pham
    $sql = "UPDATE san_pham SET ten_san_pham = ?, mo_ta = ?, danh_muc = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $ten_san_pham, $mo_ta, $danh_muc, $id);
    $stmt->execute();

    // Cập nhật hoặc thêm vào bảng san_pham_dung_tich
    $sql_dt = "INSERT INTO san_pham_dung_tich (san_pham_id, dung_tich, gia, so_luong, tinh_trang) 
               VALUES (?, ?, ?, ?, ?) 
               ON DUPLICATE KEY UPDATE gia = ?, so_luong = ?, tinh_trang = ?";
    $stmt_dt = $conn->prepare($sql_dt);
    $stmt_dt->bind_param("isiiisii", $id, $dung_tich, $gia, $so_luong, $tinh_trang, $gia, $so_luong, $tinh_trang);
    $stmt_dt->execute();

    // Xử lý upload hình ảnh mới
    if (isset($_FILES['hinh_anh_file'])) {
        $files = $_FILES['hinh_anh_file'];
        for ($i = 0; $i < count($files['name']); $i++) {
            $file_name = $files['name'][$i];
            $file_tmp = $files['tmp_name'][$i];
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/webshop/image/lv/perfume/';
            $new_file_path = $upload_dir . basename($file_name);
            $relative_path = '/webshop/image/lv/perfume/' . basename($file_name);

            if (move_uploaded_file($file_tmp, $new_file_path)) {
                $sql_img = "INSERT INTO san_pham_hinh_anh (san_pham_id, hinh_anh) VALUES (?, ?)
                            ON DUPLICATE KEY UPDATE hinh_anh = ?";
                $stmt_img = $conn->prepare($sql_img);
                $stmt_img->bind_param("iss", $id, $relative_path, $relative_path);
                $stmt_img->execute();
            }
        }
    }

    echo "success";
} else {
    echo "Invalid request";
}
$conn->close();
?>