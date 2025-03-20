<?php
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';
$id = $_GET['id'];
$sql = "SELECT id, tieu_de, noi_dung, hinh_anh, nguoi_dang_id, ngay_dang 
        FROM bai_viet 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
echo json_encode($post);
$stmt->close();
$conn->close();
?>