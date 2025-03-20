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

// Truy vấn danh sách sản phẩm với từng dung tích riêng
$sql = "SELECT 
            sp.id, 
            sp.ten_san_pham, 
            sp.mo_ta, 
            sp.danh_muc, 
            sp.ngay_tao, 
            spdt.dung_tich, 
            spdt.gia, 
            spdt.so_luong, 
            spdt.tinh_trang, 
            GROUP_CONCAT(spha.hinh_anh) AS hinh_anh 
        FROM san_pham sp 
        LEFT JOIN san_pham_dung_tich spdt ON sp.id = spdt.san_pham_id 
        LEFT JOIN san_pham_hinh_anh spha ON sp.id = spha.san_pham_id 
        GROUP BY sp.id, spdt.dung_tich 
        ORDER BY sp.ngay_tao DESC, spdt.dung_tich ASC";
$result = $conn->query($sql);
if (!$result) {
    die("Lỗi truy vấn sản phẩm: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Danh sách sản phẩm | Quản trị Admin</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
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
            <li><a class="app-menu__item" href="table-data-table.php"><i class='app-menu__icon bx bx-id-card'></i><span class="app-menu__label">Quản lý tài khoản</span></a></li>
            <li><a class="app-menu__item active" href="table-data-product.php"><i class='app-menu__icon bx bx-purchase-tag-alt'></i><span class="app-menu__label">Quản lý sản phẩm</span></a></li>
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
                <li class="breadcrumb-item active"><a href="#"><b>Danh sách sản phẩm</b></a></li>
            </ul>
            <div id="clock"></div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <div class="row element-button">
                            <div class="col-sm-2">
                                <a class="btn btn-add btn-sm" href="form-add-san-pham.php" title="Thêm"><i class="fas fa-plus"></i> Tạo mới sản phẩm</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm nhap-tu-file" type="button" title="Nhập" onclick="myFunction(this)"><i class="fas fa-file-upload"></i> Tải từ file</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm print-file" type="button" title="In" onclick="myApp.printTable()"><i class="fas fa-print"></i> In dữ liệu</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm print-file js-textareacopybtn" type="button" title="Sao chép"><i class="fas fa-copy"></i> Sao chép</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-excel btn-sm" href="" title="In"><i class="fas fa-file-excel"></i> Xuất Excel</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm pdf-file" type="button" title="In" onclick="myFunction(this)"><i class="fas fa-file-pdf"></i> Xuất PDF</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm" type="button" title="Xóa" onclick="myFunction(this)"><i class="fas fa-trash-alt"></i> Xóa tất cả </a>
                            </div>
                        </div>
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th width="10"><input type="checkbox" id="all"></th>
                                    <th>Tên sản phẩm</th>
                                    <th>Mô tả</th>
                                    <th>Ảnh</th>
                                    <th>Dung tích</th>
                                    <th>Số lượng</th>
                                    <th>Tình trạng</th>
                                    <th>Giá tiền</th>
                                    <th>Danh mục</th>
                                    <th>Chức năng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $id = $row['id'];
                                        $ten_san_pham = $row['ten_san_pham'] ?? 'Chưa có tên';
                                        $mo_ta = substr($row['mo_ta'] ?? 'Chưa có mô tả', 0, 50) . '...';
                                        // Xử lý đường dẫn ảnh
                                        $hinh_anh_raw = $row['hinh_anh'] ?? '';
                                        $hinh_anh_array = explode(',', $hinh_anh_raw);
                                        $hinh_anh_base = !empty($hinh_anh_array[0]) ? trim($hinh_anh_array[0]) : '';

                                        // Xử lý đường dẫn ảnh
                                        if (empty($hinh_anh_base)) {
                                            $hinh_anh = '/webshop/img-sanpham/default.jpg'; // Không có ảnh
                                        } elseif (strpos($hinh_anh_base, '/webshop/') === 0) {
                                            // Nếu đã là đường dẫn tương đối đầy đủ
                                            $hinh_anh = $hinh_anh_base;
                                        } else {
                                            // Nếu là đường dẫn tương đối thiếu /webshop/ (như trong database)
                                            $hinh_anh = '/webshop/' . $hinh_anh_base;
                                        }

                                        // Kiểm tra xem file có tồn tại không
                                        $file_path = $_SERVER['DOCUMENT_ROOT'] . $hinh_anh;
                                        if (!file_exists($file_path) && $hinh_anh !== '/webshop/img-sanpham/default.jpg') {
                                            $hinh_anh = '/webshop/img-sanpham/default.jpg';
                                        }

                                        $dung_tich = $row['dung_tich'] ?? 'N/A';
                                        $so_luong = $row['so_luong'] ?? 0;
                                        $tinh_trang = $row['tinh_trang'] ?? 'Còn hàng';
                                        $gia = $row['gia'] ? number_format($row['gia'], 0, ',', '.') . ' đ' : 'N/A';
                                        $danh_muc = $row['danh_muc'] ?? 'Chưa xác định';
                                        $badge_class = ($tinh_trang == 'Còn hàng') ? 'bg-success' : (($tinh_trang == 'Hết hàng') ? 'bg-danger' : 'bg-warning');

                                        echo "<tr>
                                            <td width='10'><input type='checkbox' name='check$id' value='$id'></td>
                                            <td>$ten_san_pham</td>
                                            <td>$mo_ta</td>
                                            <td><img src='$hinh_anh' alt='' width='100px' onerror=\"this.src='/webshop/img-sanpham/default.jpg'\"></td>
                                            <td>$dung_tich</td>
                                            <td>$so_luong</td>
                                            <td><span class='badge $badge_class'>$tinh_trang</span></td>
                                            <td>$gia</td>
                                            <td>$danh_muc</td>
                                            <td>
                                                <button class='btn btn-primary btn-sm trash' type='button' title='Xóa' data-id='$id'><i class='fas fa-trash-alt'></i></button>
                                                <button class='btn btn-primary btn-sm edit' type='button' title='Sửa' data-id='$id' data-dung-tich='$dung_tich' data-toggle='modal' data-target='#ModalUP'><i class='fas fa-edit'></i></button>
                                            </td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='10'>Không có dữ liệu sản phẩm.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL -->
    <div class="modal fade" id="ModalUP" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <span class="thong-tin-thanh-toan">
                                <h5>Chỉnh sửa thông tin sản phẩm</h5>
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="control-label">Tên sản phẩm</label>
                            <input class="form-control ten_san_pham" type="text" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Mô tả</label>
                            <textarea class="form-control mo_ta" rows="3"></textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Danh mục</label>
                            <select class="form-control danh_muc" id="danh_muc_select">
                                <option value="Nước hoa nam">Nước hoa nam</option>
                                <option value="Nước hoa nữ">Nước hoa nữ</option>
                                <option value="Nước hoa unisex">Nước hoa unisex</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">Hình ảnh</label>
                            <input class="form-control hinh_anh" type="text" value="">
                            <input type="file" id="upload_hinh_anh" accept="image/*" style="margin-top: 10px;" multiple>
                        </div>
                    </div>
                    <div class="row dung-tich-container">
                        <div class="form-group col-md-3">
                            <label class="control-label">Dung tích (ml)</label>
                            <input class="form-control dung_tich" type="text" required readonly>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">Số lượng</label>
                            <input class="form-control so_luong" type="number" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">Tình trạng</label>
                            <select class="form-control tinh_trang" id="tinh_trang_select">
                                <option value="Còn hàng">Còn hàng</option>
                                <option value="Hết hàng">Hết hàng</option>
                                <option value="Đang nhập hàng">Đang nhập hàng</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label">Giá bán (VND)</label>
                            <input class="form-control gia" type="number" required>
                        </div>
                    </div>
                    <br>
                    <button class="btn btn-save" type="button" onclick="saveChanges()">Lưu lại</button>
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
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="src/jquery.table2excel.js"></script>
    <script src="js/main.js"></script>
    <script src="js/plugins/pace.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <script type="text/javascript" src="js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="js/plugins/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript">
        $('#sampleTable').DataTable();

        // Xóa sản phẩm
        $('.trash').on('click', function () {
            var id = $(this).data('id');
            swal({
                title: "Cảnh báo",
                text: "Bạn có chắc chắn muốn xóa sản phẩm này?",
                buttons: ["Hủy bỏ", "Đồng ý"],
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: 'delete_product.php',
                        type: 'POST',
                        data: { id: id },
                        success: function (response) {
                            if (response === "success") {
                                swal("Đã xóa thành công!", "", "success").then(() => {
                                    location.reload();
                                });
                            } else {
                                swal("Lỗi!", "Không thể xóa sản phẩm. Chi tiết: " + response, "error");
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
            var dung_tich = $(this).data('dung-tich');
            $.get("get_product.php", { id: id, dung_tich: dung_tich }, function (data) {
                var product = JSON.parse(data);
                $('.ten_san_pham').val(product.ten_san_pham);
                $('.mo_ta').val(product.mo_ta);
                $('.danh_muc').val(product.danh_muc);
                $('.hinh_anh').val(product.hinh_anh.split(',')[0]);
                $('.dung_tich').val(product.dung_tich);
                $('.so_luong').val(product.so_luong);
                $('.tinh_trang').val(product.tinh_trang);
                $('.gia').val(product.gia);
            });
        });

        // Lưu thay đổi
        function saveChanges() {
            var formData = new FormData();
            formData.append('id', $('.edit').data('id'));
            formData.append('ten_san_pham', $('.ten_san_pham').val());
            formData.append('mo_ta', $('.mo_ta').val());
            formData.append('danh_muc', $('.danh_muc').val());
            formData.append('dung_tich', $('.dung_tich').val());
            formData.append('so_luong', $('.so_luong').val());
            formData.append('tinh_trang', $('.tinh_trang').val());
            formData.append('gia', $('.gia').val());
            var fileInput = document.getElementById('upload_hinh_anh');
            if (fileInput.files.length > 0) {
                for (var i = 0; i < fileInput.files.length; i++) {
                    formData.append('hinh_anh_file[]', fileInput.files[i]);
                }
            }

            $.ajax({
                url: "update_product.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response === "success") {
                        swal("Cập nhật thành công!", "", "success").then(() => {
                            location.reload();
                        });
                    } else {
                        swal("Lỗi!", "Không thể cập nhật. " + response, "error");
                    }
                },
                error: function (xhr, status, error) {
                    swal("Lỗi!", "Có lỗi xảy ra khi cập nhật.", "error");
                }
            });
            $('#ModalUP').modal('hide');
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

        // Chọn tất cả checkbox
        $('#all').click(function (e) {
            $('#sampleTable tbody :checkbox').prop('checked', $(this).is(':checked'));
            e.stopImmediatePropagation();
        });
    </script>
</body>

</html>