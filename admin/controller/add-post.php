<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tieu_de = $_POST['tieu_de'];
    $noi_dung = $_POST['noi_dung'];
    $nguoi_dang_id = $_POST['nguoi_dang_id'];
    $ngay_dang = $_POST['ngay_dang'] ?: date('Y-m-d H:i:s');
    $hinh_anh = null;

    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/webshop/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $hinh_anh = '/webshop/uploads/' . time() . '-' . basename($_FILES['hinh_anh']['name']);
        move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $hinh_anh);
    }

    $sql = "INSERT INTO bai_viet (tieu_de, noi_dung, hinh_anh, nguoi_dang_id, ngay_dang) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiss", $tieu_de, $noi_dung, $hinh_anh, $nguoi_dang_id, $ngay_dang);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Bài viết đã được thêm thành công.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi thêm bài viết: ' . $conn->error]);
    }
    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Thêm bài viết | Quản trị Admin</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="http://code.jquery.com/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
</head>
<body class="app sidebar-mini rtl">
    <main class="app-content">
        <div class="app-title">
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><a href="manage-posts.php"><b>Quản lý bài viết</b></a></li>
                <li class="breadcrumb-item active"><b>Thêm bài viết</b></li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Tạo mới bài viết</h3>
                    <div class="tile-body">
                        <form id="addPostForm">
                            <div class="form-group col-md-12">
                                <label class="control-label">Tiêu đề <span style="color:red;">*</span></label>
                                <input class="form-control" type="text" name="tieu_de" required>
                            </div>
                            <div class="form-group col-md-12">
                                <label class="control-label">Nội dung <span style="color:red;">*</span></label>
                                <textarea class="form-control" name="noi_dung" rows="5" required></textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="control-label">Hình ảnh</label>
                                <input class="form-control" type="file" name="hinh_anh" accept="image/*">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="control-label">Người đăng <span style="color:red;">*</span></label>
                                <select class="form-control" name="nguoi_dang_id" required>
                                    <?php
                                    $authors = $conn->query("SELECT id, ten_dang_nhap FROM nguoi_dung");
                                    while ($author = $authors->fetch_assoc()) {
                                        echo "<option value='{$author['id']}'>{$author['ten_dang_nhap']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="control-label">Ngày đăng</label>
                                <input class="form-control" type="datetime-local" name="ngay_dang">
                            </div>
                        </form>
                        <button class="btn btn-save" type="button" onclick="submitForm()">Lưu lại</button>
                        <a class="btn btn-cancel" href="manage-posts.php">Hủy bỏ</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        function submitForm() {
            var formData = new FormData();
            formData.append('tieu_de', $('input[name="tieu_de"]').val());
            formData.append('noi_dung', $('textarea[name="noi_dung"]').val());
            formData.append('nguoi_dang_id', $('select[name="nguoi_dang_id"]').val());
            formData.append('ngay_dang', $('input[name="ngay_dang"]').val());
            var fileInput = $('input[name="hinh_anh"]')[0];
            if (fileInput.files.length > 0) {
                formData.append('hinh_anh', fileInput.files[0]);
            }

            if (formData.get('tieu_de') && formData.get('noi_dung') && formData.get('nguoi_dang_id')) {
                $.ajax({
                    url: 'add-post.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            swal("Thành công!", response.message, "success").then(() => {
                                window.location.href = "manage-posts.php";
                            });
                        } else {
                            swal("Lỗi!", response.message, "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        swal("Lỗi!", "Có lỗi xảy ra khi gửi dữ liệu.", "error");
                    }
                });
            } else {
                swal("Lỗi!", "Vui lòng điền đầy đủ thông tin bắt buộc.", "error");
            }
        }
    </script>
</body>
</html>