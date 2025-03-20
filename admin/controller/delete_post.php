<?php
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';
$id = $_POST['id'];

$sql = "DELETE FROM bai_viet WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $conn->error;
}
$stmt->close();
$conn->close();
?>