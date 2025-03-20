<?php
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT id, ten_dang_nhap, so_dien_thoai, email, ngay_tao, role, avatar FROM nguoi_dung WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    echo json_encode($employee);
    $stmt->close();
}
$conn->close();
?>