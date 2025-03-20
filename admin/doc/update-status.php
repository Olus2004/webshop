<?php
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';

if (isset($_POST['id']) && isset($_POST['trang_thai'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $trang_thai = mysqli_real_escape_string($conn, $_POST['trang_thai']);
    $sql = "UPDATE don_hang SET trang_thai = '$trang_thai' WHERE id = '$id'";
    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "error";
    }
}
mysqli_close($conn);
?>