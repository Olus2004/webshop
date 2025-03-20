<?php
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';

if (isset($_GET['id']) && isset($_GET['dung_tich'])) {
    $id = $_GET['id'];
    $dung_tich = $_GET['dung_tich'];
    $sql = "SELECT 
                sp.ten_san_pham, 
                sp.mo_ta, 
                sp.danh_muc, 
                spdt.dung_tich, 
                spdt.gia, 
                spdt.so_luong, 
                spdt.tinh_trang, 
                GROUP_CONCAT(spha.hinh_anh) AS hinh_anh 
            FROM san_pham sp 
            LEFT JOIN san_pham_dung_tich spdt ON sp.id = spdt.san_pham_id 
            LEFT JOIN san_pham_hinh_anh spha ON sp.id = spha.san_pham_id 
            WHERE sp.id = ? AND (spdt.dung_tich = ? OR spdt.dung_tich IS NULL)
            GROUP BY sp.id, spdt.dung_tich";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id, $dung_tich);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    echo json_encode($product);
}
$conn->close();
?>