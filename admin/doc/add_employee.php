<?php
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_dang_nhap = $_POST['ten_dang_nhap'];
    $mat_khau = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $email = $_POST['email'];
    $ngay_tao = $_POST['ngay_tao'];
    $role = $_POST['role'];
    $avatar = null;

    if (isset($_FILES['avatar']) && $_FILES['avatar']['name']) {
        $file = $_FILES['avatar'];
        $avatar = time() . '_' . basename($file['name']);
        $target = $_SERVER['DOCUMENT_ROOT'] . '/../webshop/' . $avatar; // Sửa đường dẫn
        move_uploaded_file($file['tmp_name'], $target);
    }

    $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, so_dien_thoai, email, ngay_tao, role, avatar) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $ten_dang_nhap, $mat_khau, $so_dien_thoai, $email, $ngay_tao, $role, $avatar);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Lỗi: " . $conn->error;
    }
    $stmt->close();
}
$conn->close();
?>