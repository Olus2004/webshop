<?php
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $ten_dang_nhap = $_POST['ten_dang_nhap'];
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $email = $_POST['email'];
    $ngay_tao = $_POST['ngay_tao'];
    $role = $_POST['role'];

    $sql = "UPDATE nguoi_dung SET ten_dang_nhap = ?, so_dien_thoai = ?, email = ?, ngay_tao = ?, role = ?";
    $params = [$ten_dang_nhap, $so_dien_thoai, $email, $ngay_tao, $role];
    $types = "sssss";

    if (!empty($_POST['mat_khau'])) {
        $mat_khau = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
        $sql .= ", mat_khau = ?";
        $params[] = $mat_khau;
        $types .= "s";
    }

    if (isset($_FILES['avatar']) && $_FILES['avatar']['name']) {
        $file = $_FILES['avatar'];
        $avatar = time() . '_' . basename($file['name']);
        $target = $_SERVER['DOCUMENT_ROOT'] . '/../webshop/' . $avatar; // Sửa đường dẫn
        move_uploaded_file($file['tmp_name'], $target);
        $sql .= ", avatar = ?";
        $params[] = $avatar;
        $types .= "s";
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Lỗi: " . $conn->error;
    }
    $stmt->close();
}
$conn->close();
?>