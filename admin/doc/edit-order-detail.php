<?php
ob_start(); // Bắt đầu buffer để tránh lỗi header
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Kiểm tra đăng nhập (chỉ admin được truy cập)
if (!isset($_SESSION['ten_dang_nhap']) || $_SESSION['role'] !== 'admin') {
    header("Location: /admin/login.php");
    exit();
}

// Kiểm tra ID
if (!isset($_GET['id'])) {
    header("Location: table-data-order-details.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Lấy thông tin chi tiết đơn hàng
$sql = "SELECT ctdh.id, ctdh.don_hang_id, ctdh.san_pham_id, ctdh.so_luong, ctdh.gia, sp.ten_san_pham 
        FROM chi_tiet_don_hang ctdh 
        LEFT JOIN san_pham sp ON ctdh.san_pham_id = sp.id 
        WHERE ctdh.id = '$id'";
$result = mysqli_query($conn, $sql);
$detail = mysqli_fetch_assoc($result);

if (!$detail) {
    header("Location: table-data-order-details.php");
    exit();
}

// Tính giá đơn vị ban đầu
$gia_don_vi = $detail['gia'] / $detail['so_luong'];

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $so_luong = mysqli_real_escape_string($conn, $_POST['so_luong']);
    $gia = mysqli_real_escape_string($conn, $_POST['gia']);

    $update_sql = "UPDATE chi_tiet_don_hang SET so_luong = '$so_luong', gia = '$gia' WHERE id = '$id'";
    if (mysqli_query($conn, $update_sql)) {
        // Lưu thông báo vào session
        $_SESSION['success_message'] = "Cập nhật chi tiết đơn hàng thành công!";
        header("Location: table-data-order-details.php");
        exit(); // Đảm bảo thoát ngay sau khi chuyển hướng
    } else {
        $error_message = "Không thể cập nhật chi tiết đơn hàng: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Sửa chi tiết đơn hàng | Quản trị Admin</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <style>
        /* Căn giữa form */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f1f1f1;
        }
        .edit-container {
            width: 100%;
            max-width: 500px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            padding: 10px 20px;
            margin-right: 10px;
        }
        .tile-title {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <h3 class="tile-title">Sửa chi tiết đơn hàng #<?php echo $detail['id']; ?></h3>
        <?php if (isset($error_message)): ?>
            <script>
                swal("Lỗi", "<?php echo $error_message; ?>", "error");
            </script>
        <?php endif; ?>
        <form method="POST" id="editForm">
            <div class="form-group">
                <label>ID Đơn hàng:</label>
                <input type="text" class="form-control" value="<?php echo $detail['don_hang_id']; ?>" disabled>
            </div>
            <div class="form-group">
                <label>Sản phẩm:</label>
                <input type="text" class="form-control" value="<?php echo $detail['ten_san_pham']; ?>" disabled>
            </div>
            <div class="form-group">
                <label>Số lượng:</label>
                <input type="number" class="form-control" name="so_luong" id="so_luong" value="<?php echo $detail['so_luong']; ?>" required>
            </div>
            <div class="form-group">
                <label>Giá (VNĐ):</label>
                <input type="number" class="form-control" name="gia" id="gia" value="<?php echo $detail['gia']; ?>" required>
            </div>
            <div style="text-align: center;">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="table-data-order-details.php" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        // Tính giá mới khi thay đổi số lượng
        $(document).ready(function() {
            const giaDonVi = <?php echo $gia_don_vi; ?>; // Giá đơn vị ban đầu
            $('#so_luong').on('input', function() {
                let soLuongMoi = $(this).val();
                if (soLuongMoi < 1) soLuongMoi = 1; // Đảm bảo số lượng không âm
                let giaMoi = giaDonVi * soLuongMoi;
                $('#gia').val(Math.round(giaMoi)); // Làm tròn giá mới
            });
        });
    </script>
</body>
</html>
<?php ob_end_flush(); // Kết thúc buffer ?>