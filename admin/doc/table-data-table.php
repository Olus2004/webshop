<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Kiểm tra đăng nhập (chỉ admin được truy cập)
if (!isset($_SESSION['ten_dang_nhap']) || $_SESSION['role'] !== 'admin') {
    header("Location: /../webshop/login.php");
    exit();
}

// Truy vấn danh sách tất cả nhân viên với cột avatar
$sql = "SELECT id, ten_dang_nhap, ngay_tao, so_dien_thoai, email, dia_chi, role, avatar FROM nguoi_dung ORDER BY ngay_tao DESC";
$result = $conn->query($sql);
if (!$result) {
    die("Lỗi truy vấn nhân viên: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Danh sách nhân viên | Quản trị Admin</title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Main CSS-->
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
  <!-- or -->
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
  <!-- Font-icon css-->
  <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
</head>
<body onload="time()" class="app sidebar-mini rtl">
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
        <div class="app-sidebar__user"><img class="app-sidebar__user-avatar" src="/../webshop/image/lv/avt/avt1.jpg" width="50px" alt="User Image">
            <div>
                <p class="app-sidebar__user-name"><b><?php echo htmlspecialchars($_SESSION['ten_dang_nhap']); ?></b></p>
                <p class="app-sidebar__user-designation">Chào mừng bạn trở lại</p>
            </div>
        </div>
        <hr>
        <ul class="app-menu">
            <li><a class="app-menu__item" href="index.php"><i class='app-menu__icon bx bx-tachometer'></i><span class="app-menu__label">Bảng điều khiển</span></a></li>
            <li><a class="app-menu__item active" href="table-data-table.php"><i class='app-menu__icon bx bx-id-card'></i><span class="app-menu__label">Quản lý tài khoản</span></a></li>
            <li><a class="app-menu__item" href="table-data-product.php"><i class='app-menu__icon bx bx-purchase-tag-alt'></i><span class="app-menu__label">Quản lý sản phẩm</span></a></li>
            <li><a class="app-menu__item" href="table-data-oder.php"><i class='app-menu__icon bx bx-task'></i><span class="app-menu__label">Quản lý đơn hàng</span></a></li>
            <li><a class="app-menu__item" href="manage_posts.php"><i class='app-menu__icon bx bx-news'></i><span class="app-menu__label">Quản lý bài viết</span></a></li>
            <li><a class="app-menu__item" href="manage_reviews.php"><i class='app-menu__icon bx bx-star'></i><span class="app-menu__label">Quản lý đánh giá</span></a></li>
            <li><a class="app-menu__item" href="table-data-order-details.php"><i class='app-menu__icon bx bx-list-ul'></i><span class="app-menu__label">Chi tiết đơn hàng</span></a></li>
            <li><a class="app-menu__item" href="manage_separator_media.php"><i class='app-menu__icon bx bx-image'></i><span class="app-menu__label">Separator Media</span></a></li>

        </ul>
    </aside>
    <main class="app-content">
        <div class="app-title">
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item active"><a href="#"><b>Danh sách nhân viên</b></a></li>
            </ul>
            <div id="clock"></div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <div class="row element-button">
                            <div class="col-sm-2">
                                <a class="btn btn-add btn-sm" data-toggle="modal" data-target="#ModalAdd" title="Thêm"><i class="fas fa-plus"></i> Tạo mới nhân viên</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm nhap-tu-file" type="button" title="Nhập" onclick="myFunction(this)"><i class="fas fa-file-upload"></i> Tải từ file</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm print-file" type="button" title="In" onclick="myApp.printTable()"><i class="fas fa-print"></i> In dữ liệu</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm js-textareacopybtn" type="button" title="Sao chép"><i class="fas fa-copy"></i> Sao chép</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-excel btn-sm" href="" title="In"><i class="fas fa-file-excel"></i> Xuất Excel</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm pdf-file" type="button" title="In" onclick="myFunction(this)"><i class="fas fa-file-pdf"></i> Xuất PDF</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm" type="button" title="Xóa" onclick="myFunction(this)"><i class="fas fa-trash-alt"></i> Xóa tất cả</a>
                            </div>
                        </div>
                        <table class="table table-hover table-bordered js-copytextarea" cellpadding="0" cellspacing="0" border="0" id="sampleTable">
                            <thead>
                                <tr>
                                    <th width="10"><input type="checkbox" id="all"></th>
                                    <th>ID nhân viên</th>
                                    <th width="150">Họ và tên</th>
                                    <th width="20">Ảnh thẻ</th>
                                    <th width="300">Địa chỉ</th>
                                    <th>Ngày sinh</th>
                                    <th>Giới tính</th>
                                    <th>SĐT</th>
                                    <th>Chức vụ</th>
                                    <th width="100">Tính năng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $id = $row['id'];
                                        $ten_dang_nhap = $row['ten_dang_nhap'];
                                        $ngay_sinh = date('d/m/Y', strtotime($row['ngay_tao'] ?? '0000-00-00'));
                                        $so_dien_thoai = $row['so_dien_thoai'];
                                        $dia_chi = $row['dia_chi'] ?? 'Chưa cập nhật';
                                        $email = $row['email'] ?? 'Chưa cập nhật';
                                        $chuc_vu = $row['role'] ?? 'Chưa xác định';
                                        $gioi_tinh = 'Chưa xác định';
                                        // Sửa đường dẫn ảnh tương đối từ admin/doc/ đến image/lv/avt/
                                        $anh_the = $row['avatar'] ? "/../webshop/" . $row['avatar'] : "/../webshop/image/lv/avt/default.jpg";

                                        echo "<tr>
                                            <td width='10'><input type='checkbox' name='check$id' value='$id'></td>
                                            <td>#NV$id</td>
                                            <td>$ten_dang_nhap</td>
                                            <td><img class='img-card-person' src='$anh_the' alt=''></td>
                                            <td>$dia_chi</td>
                                            <td>$ngay_sinh</td>
                                            <td>$gioi_tinh</td>
                                            <td>$so_dien_thoai</td>
                                            <td>$chuc_vu</td>
                                            <td class='table-td-center'>
                                                <button class='btn btn-primary btn-sm trash' type='button' title='Xóa' data-id='$id'><i class='fas fa-trash-alt'></i></button>
                                                <button class='btn btn-primary btn-sm edit' type='button' title='Sửa' data-id='$id' data-toggle='modal' data-target='#ModalUP'><i class='fas fa-edit'></i></button>
                                            </td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='10'>Không có dữ liệu nhân viên.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL THÊM NHÂN VIÊN -->
    <div class="modal fade" id="ModalAdd" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <span class="thong-tin-thanh-toan">
                                <h5>Thêm mới nhân viên</h5>
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="control-label">Họ và tên</label>
                            <input class="form-control ten_nhan_vien_add" type="text" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Mật khẩu</label>
                            <input class="form-control mat_khau_add" type="password" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Số điện thoại</label>
                            <input class="form-control sdt_nhan_vien_add" type="number" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Địa chỉ email</label>
                            <input class="form-control email_nhan_vien_add" type="email" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Ngày sinh</label>
                            <input class="form-control ngay_sinh_nhan_vien_add" type="date" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleSelect1" class="control-label">Chức vụ</label>
                            <select class="form-control chuc_vu_nhan_vien_add" id="exampleSelect1">
                                <option value="member">Thành viên</option>
                                <option value="Bán hàng">Bán hàng</option>
                                <option value="Tư vấn">Tư vấn</option>
                                <option value="Dịch vụ">Dịch vụ</option>
                                <option value="Thu Ngân">Thu Ngân</option>
                                <option value="Quản kho">Quản kho</option>
                                <option value="Bảo trì">Bảo trì</option>
                                <option value="Kiểm hàng">Kiểm hàng</option>
                                <option value="Bảo vệ">Bảo vệ</option>
                                <option value="Tạp vụ">Tạp vụ</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Ảnh đại diện</label>
                            <input class="form-control anh_dai_dien_add" type="file" accept="image/*">
                        </div>
                    </div>
                    <br>
                    <a href="#" style="float: right; font-weight: 600; color: #ea0000;">Chỉnh sửa nâng cao</a>
                    <br><br>
                    <button class="btn btn-save" type="button" onclick="addEmployee()">Lưu lại</button>
                    <a class="btn btn-cancel" data-dismiss="modal" href="#">Hủy bỏ</a>
                    <br>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>

    <!-- MODAL CHỈNH SỬA -->
    <div class="modal fade" id="ModalUP" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <span class="thong-tin-thanh-toan">
                                <h5>Chỉnh sửa thông tin nhân viên cơ bản</h5>
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="control-label">ID nhân viên</label>
                            <input class="form-control id_nhan_vien" type="text" required value="" disabled>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Họ và tên</label>
                            <input class="form-control ten_nhan_vien" type="text" required value="">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Số điện thoại</label>
                            <input class="form-control sdt_nhan_vien" type="number" required value="">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Địa chỉ email</label>
                            <input class="form-control email_nhan_vien" type="text" required value="">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Ngày sinh</label>
                            <input class="form-control ngay_sinh_nhan_vien" type="date" value="">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleSelect1" class="control-label">Chức vụ</label>
                            <select class="form-control chuc_vu_nhan_vien" id="exampleSelect1">
                                <option value="member">Thành viên</option>
                                <option value="Bán hàng">Bán hàng</option>
                                <option value="Tư vấn">Tư vấn</option>
                                <option value="Dịch vụ">Dịch vụ</option>
                                <option value="Thu Ngân">Thu Ngân</option>
                                <option value="Quản kho">Quản kho</option>
                                <option value="Bảo trì">Bảo trì</option>
                                <option value="Kiểm hàng">Kiểm hàng</option>
                                <option value="Bảo vệ">Bảo vệ</option>
                                <option value="Tạp vụ">Tạp vụ</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Mật khẩu mới (để trống nếu không đổi)</label>
                            <input class="form-control mat_khau_nhan_vien" type="password" value="">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Ảnh đại diện</label>
                            <input class="form-control anh_dai_dien_nhan_vien" type="file" accept="image/*">
                            <img class="img-card-person current-avatar" src="" alt="Avatar hiện tại" style="max-width: 100px; margin-top: 10px;">
                        </div>
                    </div>
                    <br>
                    <a href="#" style="float: right; font-weight: 600; color: #ea0000;">Chỉnh sửa nâng cao</a>
                    <br><br>
                    <button class="btn btn-save" type="button" onclick="saveChanges()">Lưu lại</button>
                    <a class="btn btn-cancel" data-dismiss="modal" href="#">Hủy bỏ</a>
                    <br>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>

    <!-- Essential javascripts for application to work-->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/plugins/pace.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="js/plugins/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript">
        $('#sampleTable').DataTable({
            "paging": true,
            "pageLength": 10,
            "lengthMenu": [5, 10, 25, 50, 100],
            "order": [[1, 'desc']]
        });

        // Xóa nhân viên
        $('.trash').on('click', function () {
            var id = $(this).data('id');
            swal({
                title: "Cảnh báo",
                text: "Bạn có chắc chắn muốn xóa nhân viên này?",
                buttons: ["Hủy bỏ", "Đồng ý"],
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: 'delete_employee.php',
                        type: 'POST',
                        data: { id: id },
                        success: function (response) {
                            if (response === "success") {
                                swal("Đã xóa thành công!", "", "success").then(() => {
                                    location.reload();
                                });
                            } else {
                                swal("Lỗi!", "Không thể xóa nhân viên: " + response, "error");
                            }
                        },
                        error: function (xhr, status, error) {
                            swal("Lỗi!", "Có lỗi xảy ra khi xóa.", "error");
                        }
                    });
                }
            });
        });

        // Hiển thị dữ liệu trong modal khi nhấn Sửa
        $('.edit').on('click', function () {
            var id = $(this).data('id');
            $.ajax({
                url: 'get_employee.php',
                type: 'GET',
                data: { id: id },
                success: function (data) {
                    var employee = JSON.parse(data);
                    $('.id_nhan_vien').val('#NV' + employee.id);
                    $('.ten_nhan_vien').val(employee.ten_dang_nhap);
                    $('.sdt_nhan_vien').val(employee.so_dien_thoai);
                    $('.email_nhan_vien').val(employee.email);
                    $('.ngay_sinh_nhan_vien').val(employee.ngay_tao ? employee.ngay_tao.split(' ')[0] : '');
                    $('.chuc_vu_nhan_vien').val(employee.role);
                    $('.mat_khau_nhan_vien').val(''); // Để trống mật khẩu
                    $('.current-avatar').attr('src', employee.avatar ? '../../../image/lv/avt/' + employee.avatar : '../../../image/lv/avt/default.jpg');
                },
                error: function () {
                    swal("Lỗi!", "Không thể tải dữ liệu nhân viên.", "error");
                }
            });
        });

        // Lưu thay đổi
        function saveChanges() {
            var id = $('.id_nhan_vien').val().replace('#NV', '');
            var ten = $('.ten_nhan_vien').val();
            var sdt = $('.sdt_nhan_vien').val();
            var email = $('.email_nhan_vien').val();
            var ngay_sinh = $('.ngay_sinh_nhan_vien').val();
            var chuc_vu = $('.chuc_vu_nhan_vien').val();
            var mat_khau = $('.mat_khau_nhan_vien').val();
            var anh_dai_dien = $('.anh_dai_dien_nhan_vien')[0].files[0];

            var formData = new FormData();
            formData.append('id', id);
            formData.append('ten_dang_nhap', ten);
            formData.append('so_dien_thoai', sdt);
            formData.append('email', email);
            formData.append('ngay_tao', ngay_sinh);
            formData.append('role', chuc_vu);
            if (mat_khau) formData.append('mat_khau', mat_khau); // Chỉ gửi mật khẩu nếu có nhập
            if (anh_dai_dien) formData.append('avatar', anh_dai_dien); // Sử dụng tên 'avatar'

            $.ajax({
                url: 'update_employee.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response === "success") {
                        swal("Cập nhật thành công!", "", "success").then(() => {
                            location.reload();
                        });
                    } else {
                        swal("Lỗi!", "Không thể cập nhật: " + response, "error");
                    }
                },
                error: function () {
                    swal("Lỗi!", "Có lỗi xảy ra khi cập nhật.", "error");
                }
            });
            $('#ModalUP').modal('hide');
        }

        // Thêm nhân viên mới
        function addEmployee() {
            var ten = $('.ten_nhan_vien_add').val();
            var mat_khau = $('.mat_khau_add').val();
            var sdt = $('.sdt_nhan_vien_add').val();
            var email = $('.email_nhan_vien_add').val();
            var ngay_sinh = $('.ngay_sinh_nhan_vien_add').val();
            var chuc_vu = $('.chuc_vu_nhan_vien_add').val();
            var anh_dai_dien = $('.anh_dai_dien_add')[0].files[0];

            var formData = new FormData();
            formData.append('ten_dang_nhap', ten);
            formData.append('mat_khau', mat_khau);
            formData.append('so_dien_thoai', sdt);
            formData.append('email', email);
            formData.append('ngay_tao', ngay_sinh);
            formData.append('role', chuc_vu);
            if (anh_dai_dien) formData.append('avatar', anh_dai_dien); // Sử dụng tên 'avatar'

            $.ajax({
                url: 'add_employee.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response === "success") {
                        swal("Thêm thành công!", "", "success").then(() => {
                            location.reload();
                        });
                    } else {
                        swal("Lỗi!", "Không thể thêm: " + response, "error");
                    }
                },
                error: function () {
                    swal("Lỗi!", "Có lỗi xảy ra khi thêm.", "error");
                }
            });
            $('#ModalAdd').modal('hide');
        }

        // In dữ liệu
        var myApp = new function () {
            this.printTable = function () {
                var tab = document.getElementById('sampleTable');
                var win = window.open('', '', 'height=700,width=700');
                win.document.write(tab.outerHTML);
                win.document.close();
                win.print();
            }
        }

        // Thời gian
        function time() {
            var today = new Date();
            var weekday = new Array(7);
            weekday[0] = "Chủ Nhật";
            weekday[1] = "Thứ Hai";
            weekday[2] = "Thứ Ba";
            weekday[3] = "Thứ Tư";
            weekday[4] = "Thứ Năm";
            weekday[5] = "Thứ Sáu";
            weekday[6] = "Thứ Bảy";
            var day = weekday[today.getDay()];
            var dd = today.getDate();
            var mm = today.getMonth() + 1;
            var yyyy = today.getFullYear();
            var h = today.getHours();
            var m = today.getMinutes();
            var s = today.getSeconds();
            m = checkTime(m);
            s = checkTime(s);
            nowTime = h + " giờ " + m + " phút " + s + " giây";
            if (dd < 10) { dd = '0' + dd }
            if (mm < 10) { mm = '0' + mm }
            today = day + ', ' + dd + '/' + mm + '/' + yyyy;
            tmp = '<span class="date"> ' + today + ' - ' + nowTime + '</span>';
            document.getElementById("clock").innerHTML = tmp;
            clocktime = setTimeout("time()", "1000", "Javascript");

            function checkTime(i) {
                if (i < 10) { i = "0" + i; }
                return i;
            }
        }
    </script>
</body>
</html>