<?php
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';
$id = $_POST['id'];
$tieu_de = $_POST['tieu_de'];
$noi_dung = $_POST['noi_dung'];
$nguoi_dang_id = $_POST['nguoi_dang_id'];
$ngay_dang = $_POST['ngay_dang'];
$hinh_anh = $_POST['hinh_anh'];

if (isset($_FILES['hinh_anh_file']) && $_FILES['hinh_anh_file']['error'] == 0) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/webshop/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $hinh_anh = '/webshop/uploads/' . time() . '-' . basename($_FILES['hinh_anh_file']['name']);
    move_uploaded_file($_FILES['hinh_anh_file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $hinh_anh);
}

$sql = "UPDATE bai_viet SET tieu_de = ?, noi_dung = ?, hinh_anh = ?, nguoi_dang_id = ?, ngay_dang = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssisi", $tieu_de, $noi_dung, $hinh_anh, $nguoi_dang_id, $ngay_dang, $id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $conn->error;
}
$stmt->close();
$conn->close();
?>