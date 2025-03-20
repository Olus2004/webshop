<?php
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

// Xử lý dữ liệu khi gửi từ AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_dang_nhap = $_POST['ten_dang_nhap'];
    $email = $_POST['email'];
    $dia_chi = $_POST['dia_chi'];
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $ngay_tao = $_POST['ngay_tao'];
    $role = $_POST['role'];

    // Mật khẩu mặc định (bạn có thể yêu cầu người dùng nhập mật khẩu nếu cần)
    $mat_khau = password_hash('default_password', PASSWORD_DEFAULT); // Mật khẩu mặc định, mã hóa

    $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, so_dien_thoai, email, dia_chi, ngay_tao, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $ten_dang_nhap, $mat_khau, $so_dien_thoai, $email, $dia_chi, $ngay_tao, $role);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $conn->error;
    }
    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Thêm nhân viên | Quản trị Admin</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="http://code.jquery.com/jquery.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script>
        function readURL(input, thumbimage) {
            if (input.files && input.files[0]) { //Sử dụng cho Firefox - Chrome
                var reader = new FileReader();
                reader.onload = function (e) {
                    $("#thumbimage").attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
            else { // Sử dụng cho IE
                $("#thumbimage").attr('src', input.value);
            }
            $("#thumbimage").show();
            $('.filename').text($("#uploadfile").val());
            $('.Choicefile').css('background', '#14142B');
            $('.Choicefile').css('cursor', 'default');
            $(".removeimg").show();
            $(".Choicefile").unbind('click');
        }
        $(document).ready(function () {
            $(".Choicefile").bind('click', function () {
                $("#uploadfile").click();
            });
            $(".removeimg").click(function () {
                $("#thumbimage").attr('src', '').hide();
                $("#myfileupload").html('<input type="file" id="uploadfile"  onchange="readURL(this);" />');
                $(".removeimg").hide();
                $(".Choicefile").bind('click', function () {
                    $("#uploadfile").click();
                });
                $('.Choicefile').css('background', '#14142B');
                $('.Choicefile').css('cursor', 'pointer');
                $(".filename").text("");
            });
        });

        function submitForm() {
            var formData = {
                ten_dang_nhap: $('input[name="ten_dang_nhap"]').val(),
                email: $('input[name="email"]').val(),
                dia_chi: $('input[name="dia_chi"]').val(),
                so_dien_thoai: $('input[name="so_dien_thoai"]').val(),
                ngay_tao: $('input[name="ngay_tao"]').val(),
                role: $('select[name="role"]').val()
            };

            if (formData.ten_dang_nhap && formData.email && formData.so_dien_thoai && formData.role) {
                $.ajax({
                    url: 'add_employee.php',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response === "success") {
                            swal("Thành công!", "Nhân viên đã được thêm vào database.", "success").then(() => {
                                window.location.href = "table-data-table.php";
                            });
                        } else {
                            swal("Lỗi!", "Không thể thêm nhân viên. Chi tiết: " + response, "error");
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

        function saveNewChucVu() {
            var newChucVu = $('#new_chuc_vu').val();
            if (newChucVu) {
                $('#exampleSelect1').append(new Option(newChucVu, newChucVu));
                $('#exampleModalCenter').modal('hide');
                swal("Thành công!", "Chức vụ mới đã được thêm vào danh sách.", "success");
            } else {
                swal("Lỗi!", "Vui lòng nhập tên chức vụ.", "error");
            }
        }
    </script>
</head>

<body class="app sidebar-mini rtl">
    <style>
        .Choicefile {
            display: block;
            background: #14142B;
            border: 1px solid #fff;
            color: #fff;
            width: 150px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            padding: 5px 0px;
            border-radius: 5px;
            font-weight: 500;
            align-items: center;
            justify-content: center;
        }

        .Choicefile:hover {
            text-decoration: none;
            color: white;
        }

        #uploadfile,
        .removeimg {
            display: none;
        }

        #thumbbox {
            position: relative;
            width: 100%;
            margin-bottom: 20px;
        }

        .removeimg {
            height: 25px;
            position: absolute;
            background-repeat: no-repeat;
            top: 5px;
            left: 5px;
            background-size: 25px;
            width: 25px;
            border-radius: 50%;
        }

        .removeimg::before {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            content: '';
            border: 1px solid red;
            background: red;
            text-align: center;
            display: block;
            margin-top: 11px;
            transform: rotate(45deg);
        }

        .removeimg::after {
            content: '';
            background: red;
            border: 1px solid red;
            text-align: center;
            display: block;
            transform: rotate(-45deg);
            margin-top: -2px;
        }
    </style>
    <!-- Navbar-->
    <header class="app-header">
        <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
        <ul class="app-nav">
            <li><a class="app-nav__item" href="/index.html"><i class='bx bx-log-out bx-rotate-180'></i></a></li>
        </ul>
    </header>
    <!-- Sidebar menu-->
    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar">
        <div class="app-sidebar__user"><img class="app-sidebar__user-avatar" src="/images/hay.jpg" width="50px" alt="User Image">
            <div>
                <p class="app-sidebar__user-name"><b><?php echo htmlspecialchars($_SESSION['ten_dang_nhap']); ?></b></p>
                <p class="app-sidebar__user-designation">Chào mừng bạn trở lại</p>
            </div>
        </div>
        <hr>
        <ul class="app-menu">
            <li><a class="app-menu__item haha" href="phan-mem-ban-hang.html"><i class='app-menu__icon bx bx-cart-alt'></i><span class="app-menu__label">POS Bán Hàng</span></a></li>
            <li><a class="app-menu__item" href="index.php"><i class='app-menu__icon bx bx-tachometer'></i><span class="app-menu__label">Bảng điều khiển</span></a></li>
            <li><a class="app-menu__item active" href="table-data-table.php"><i class='app-menu__icon bx bx-id-card'></i><span class="app-menu__label">Quản lý nhân viên</span></a></li>
            <li><a class="app-menu__item" href="#"><i class='app-menu__icon bx bx-user-voice'></i><span class="app-menu__label">Quản lý khách hàng</span></a></li>
            <li><a class="app-menu__item" href="table-data-product.html"><i class='app-menu__icon bx bx-purchase-tag-alt'></i><span class="app-menu__label">Quản lý sản phẩm</span></a></li>
            <li><a class="app-menu__item" href="table-data-oder.html"><i class='app-menu__icon bx bx-task'></i><span class="app-menu__label">Quản lý đơn hàng</span></a></li>
            <li><a class="app-menu__item" href="table-data-banned.html"><i class='app-menu__icon bx bx-run'></i><span class="app-menu__label">Quản lý nội bộ</span></a></li>
            <li><a class="app-menu__item" href="table-data-money.html"><i class='app-menu__icon bx bx-dollar'></i><span class="app-menu__label">Bảng kê lương</span></a></li>
            <li><a class="app-menu__item" href="quan-ly-bao-cao.html"><i class='app-menu__icon bx bx-pie-chart-alt-2'></i><span class="app-menu__label">Báo cáo doanh thu</span></a></li>
            <li><a class="app-menu__item" href="page-calendar.html"><i class='app-menu__icon bx bx-calendar-check'></i><span class="app-menu__label">Lịch công tác</span></a></li>
            <li><a class="app-menu__item" href="#"><i class='app-menu__icon bx bx-cog'></i><span class="app-menu__label">Cài đặt hệ thống</span></a></li>
        </ul>
    </aside>
    <main class="app-content">
        <div class="app-title">
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><a href="table-data-table.php"><b>Danh sách nhân viên</b></a></li>
                <li class="breadcrumb-item active"><b>Thêm nhân viên</b></li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Tạo mới nhân viên</h3>
                    <div class="tile-body">
                        <div class="row element-button">
                            <div class="col-sm-2">
                                <a class="btn btn-add btn-sm" data-toggle="modal" data-target="#exampleModalCenter"><b><i class="fas fa-folder-plus"></i> Tạo chức vụ mới</b></a>
                            </div>
                        </div>
                        <form class="row" id="addEmployeeForm">
                            <div class="form-group col-md-4">
                                <label class="control-label">ID nhân viên (tự động)</label>
                                <input class="form-control" type="text" name="id_nhan_vien" readonly value="Tự động tạo">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Họ và tên</label>
                                <input class="form-control" type="text" name="ten_dang_nhap" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Địa chỉ email</label>
                                <input class="form-control" type="text" name="email" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Địa chỉ</label>
                                <input class="form-control" type="text" name="dia_chi" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Số điện thoại</label>
                                <input class="form-control" type="number" name="so_dien_thoai" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="control-label">Ngày sinh</label>
                                <input class="form-control" type="date" name="ngay_tao">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="exampleSelect1" class="control-label">Chức vụ</label>
                                <select class="form-control" name="role" id="exampleSelect1">
                                    <option value="">-- Chọn chức vụ --</option>
                                    <option value="member">Thành viên</option>
                                    <option value="admin">Quản trị viên</option>
                            </div>
                            <div class="form-group col-md-12">
                                <label class="control-label">Ảnh 3x4 nhân viên</label>
                                <div id="myfileupload">
                                    <input type="file" id="uploadfile" name="anh_the" onchange="readURL(this);" />
                                </div>
                                <div id="thumbbox">
                                    <img height="300" width="300" alt="Thumb image" id="thumbimage" style="display: none" />
                                    <a class="removeimg" href="javascript:"></a>
                                </div>
                                <div id="boxchoice">
                                    <a href="javascript:" class="Choicefile"><i class='bx bx-upload'></i></a>
                                    <p style="clear:both"></p>
                                </div>
                            </div>
                        </form>
                        <button class="btn btn-save" type="button" onclick="submitForm()">Lưu lại</button>
                        <a class="btn btn-cancel" href="table-data-table.php">Hủy bỏ</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL -->
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <span class="thong-tin-thanh-toan">
                                <h5>Tạo chức vụ mới</h5>
                            </span>
                        </div>
                        <div class="form-group col-md-12">
                            <label class="control-label">Nhập tên chức vụ mới</label>
                            <input class="form-control" type="text" id="new_chuc_vu" required>
                        </div>
                    </div>
                    <br>
                    <button class="btn btn-save" type="button" onclick="saveNewChucVu()">Lưu lại</button>
                    <a class="btn btn-cancel" data-dismiss="modal" href="#">Hủy bỏ</a>
                    <br>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <!-- END MODAL -->

    <!-- Essential javascripts for application to work-->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <!-- The javascript plugin to display page loading on top-->
    <script src="js/plugins/pace.min.js"></script>
    <script>
        function readURL(input, thumbimage) {
            if (input.files && input.files[0]) { //Sử dụng cho Firefox - Chrome
                var reader = new FileReader();
                reader.onload = function (e) {
                    $("#thumbimage").attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
            else { // Sử dụng cho IE
                $("#thumbimage").attr('src', input.value);
            }
            $("#thumbimage").show();
            $('.filename').text($("#uploadfile").val());
            $('.Choicefile').css('background', '#14142B');
            $('.Choicefile').css('cursor', 'default');
            $(".removeimg").show();
            $(".Choicefile").unbind('click');
        }
        $(document).ready(function () {
            $(".Choicefile").bind('click', function () {
                $("#uploadfile").click();
            });
            $(".removeimg").click(function () {
                $("#thumbimage").attr('src', '').hide();
                $("#myfileupload").html('<input type="file" id="uploadfile"  onchange="readURL(this);" />');
                $(".removeimg").hide();
                $(".Choicefile").bind('click', function () {
                    $("#uploadfile").click();
                });
                $('.Choicefile').css('background', '#14142B');
                $('.Choicefile').css('cursor', 'pointer');
                $(".filename").text("");
            });
        });

        function submitForm() {
            var formData = {
                ten_dang_nhap: $('input[name="ten_dang_nhap"]').val(),
                email: $('input[name="email"]').val(),
                dia_chi: $('input[name="dia_chi"]').val(),
                so_dien_thoai: $('input[name="so_dien_thoai"]').val(),
                ngay_tao: $('input[name="ngay_tao"]').val(),
                role: $('select[name="role"]').val()
            };

            if (formData.ten_dang_nhap && formData.email && formData.so_dien_thoai && formData.role) {
                $.ajax({
                    url: 'form-add-nhan-vien.php',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response === "success") {
                            swal("Thành công!", "Nhân viên đã được thêm vào database.", "success").then(() => {
                                window.location.href = "table-data-table.php";
                            });
                        } else {
                            swal("Lỗi!", "Không thể thêm nhân viên. Chi tiết: " + response, "error");
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

        function saveNewChucVu() {
            var newChucVu = $('#new_chuc_vu').val();
            if (newChucVu) {
                $('#exampleSelect1').append(new Option(newChucVu, newChucVu));
                $('#exampleModalCenter').modal('hide');
                swal("Thành công!", "Chức vụ mới đã được thêm vào danh sách.", "success");
            } else {
                swal("Lỗi!", "Vui lòng nhập tên chức vụ.", "error");
            }
        }
    </script>
</body>

</html>