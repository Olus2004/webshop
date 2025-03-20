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

// Xử lý xóa chi tiết đơn hàng
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $sql = "DELETE FROM chi_tiet_don_hang WHERE id = '$id'";
    if (mysqli_query($conn, $sql)) {
        echo "<script>swal('Thành công!', 'Đã xóa chi tiết đơn hàng thành công!', 'success');</script>";
    } else {
        echo "<script>swal('Lỗi', 'Không thể xóa chi tiết đơn hàng: " . mysqli_error($conn) . "', 'error');</script>";
    }
}

// Lấy danh sách chi tiết đơn hàng với thông tin người mua
$sql = "SELECT ctdh.id, dh.id AS don_hang_id, sp.ten_san_pham, ctdh.so_luong, ctdh.gia, nd.ten_dang_nhap 
        FROM chi_tiet_don_hang ctdh 
        LEFT JOIN don_hang dh ON ctdh.don_hang_id = dh.id 
        LEFT JOIN san_pham sp ON ctdh.san_pham_id = sp.id 
        LEFT JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Danh sách chi tiết đơn hàng | Quản trị Admin</title>
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
        <div class="app-sidebar__user">
            <img class="app-sidebar__user-avatar" src="/../webshop/image/lv/avt/avt1.jpg" width="50px" alt="User Image">
            <div>
                <p class="app-sidebar__user-name"><b><?php echo htmlspecialchars($_SESSION['ten_dang_nhap']); ?></b></p>
                <p class="app-sidebar__user-designation">Chào mừng bạn trở lại</p>
            </div>
        </div>
        <hr>
        <ul class="app-menu">
            <li><a class="app-menu__item" href="index.php"><i class='app-menu__icon bx bx-tachometer'></i><span class="app-menu__label">Bảng điều khiển</span></a></li>
            <li><a class="app-menu__item" href="table-data-table.php"><i class='app-menu__icon bx bx-id-card'></i><span class="app-menu__label">Quản lý tài khoản</span></a></li>
            <li><a class="app-menu__item" href="table-data-product.php"><i class='app-menu__icon bx bx-purchase-tag-alt'></i><span class="app-menu__label">Quản lý sản phẩm</span></a></li>
            <li><a class="app-menu__item" href="table-data-oder.php"><i class='app-menu__icon bx bx-task'></i><span class="app-menu__label">Quản lý đơn hàng</span></a></li>
            <li><a class="app-menu__item" href="manage_posts.php"><i class='app-menu__icon bx bx-news'></i><span class="app-menu__label">Quản lý bài viết</span></a></li>
            <li><a class="app-menu__item" href="manage_reviews.php"><i class='app-menu__icon bx bx-star'></i><span class="app-menu__label">Quản lý đánh giá</span></a></li>
            <li><a class="app-menu__item active"table-data-order-details.php"><i class='app-menu__icon bx bx-list-ul'></i><span class="app-menu__label">Chi tiết đơn hàng</span></a></li>
            <li><a class="app-menu__item" href="manage_separator_media.php"><i class='app-menu__icon bx bx-image'></i><span class="app-menu__label">Separator Media</span></a></li>
        </ul>
    </aside>
    <main class="app-content">
        <div class="app-title">
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item active"><a href="#"><b>Danh sách chi tiết đơn hàng</b></a></li>
            </ul>
            <div id="clock"></div>
        </div>
        <?php 
        // Hiển thị thông báo thành công từ session
        if (isset($_SESSION['success_message'])): ?>
            <script>
                swal("Thành công!", "<?php echo $_SESSION['success_message']; ?>", "success");
            </script>
            <?php unset($_SESSION['success_message']); // Xóa thông báo sau khi hiển thị ?>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <div class="row element-button">
                            <div class="col-sm-2">
                                <a class="btn btn-excel btn-sm" href="#" id="exportExcel" title="Xuất Excel"><i class="fas fa-file-excel"></i> Xuất Excel</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm pdf-file" href="#" id="exportPDF" title="Xuất PDF"><i class="fas fa-file-pdf"></i> Xuất PDF</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm print-file" href="#" id="printTable" title="In"><i class="fas fa-print"></i> In dữ liệu</a>
                            </div>
                        </div>
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th width="10"><input type="checkbox" id="all"></th>
                                    <th>ID</th>
                                    <th>ID Đơn hàng</th>
                                    <th>Người mua</th>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Giá</th>
                                    <th>Tính năng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr>
                                        <td width="10"><input type="checkbox" name="check1" value="<?php echo $row['id']; ?>"></td>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['don_hang_id']; ?></td>
                                        <td><?php echo $row['ten_dang_nhap'] ? $row['ten_dang_nhap'] : 'Không xác định'; ?></td>
                                        <td><?php echo $row['ten_san_pham'] ? $row['ten_san_pham'] : 'Không xác định'; ?></td>
                                        <td><?php echo $row['so_luong']; ?></td>
                                        <td><?php echo number_format($row['gia'], 0, ',', '.') . ' đ'; ?></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm trash" type="button" title="Xóa" data-id="<?php echo $row['id']; ?>"><i class="fas fa-trash-alt"></i></button>
                                            <button class="btn btn-primary btn-sm edit" type="button" title="Sửa" data-id="<?php echo $row['id']; ?>"><i class="fa fa-edit"></i></button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- Essential javascripts for application to work-->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="src/jquery.table2excel.js"></script>
    <script src="js/main.js"></script>
    <!-- The javascript plugin to display page loading on top-->
    <script src="js/plugins/pace.min.js"></script>
    <!-- Page specific javascripts-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <!-- Data table plugin-->
    <script type="text/javascript" src="js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="js/plugins/dataTables.bootstrap.min.js"></script>
    <!-- Thư viện jsPDF để xuất PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script type="text/javascript">
        $('#sampleTable').DataTable();

        // Xóa chi tiết đơn hàng
        jQuery(function () {
            jQuery(".trash").click(function () {
                var id = $(this).data('id');
                swal({
                    title: "Cảnh báo",
                    text: "Bạn có chắc chắn là muốn xóa chi tiết đơn hàng này?",
                    buttons: ["Hủy bỏ", "Đồng ý"],
                })
                .then((willDelete) => {
                    if (willDelete) {
                        window.location = "?delete=" + id;
                    }
                });
            });

            // Sửa chi tiết đơn hàng
            jQuery(".edit").click(function () {
                var id = $(this).data('id');
                window.location = "edit-order-detail.php?id=" + id;
            });
        });

        // Chọn tất cả checkbox
        $('#all').click(function (e) {
            $('#sampleTable tbody :checkbox').prop('checked', $(this).is(':checked'));
            e.stopImmediatePropagation();
        });

        // Xuất Excel
        $('#exportExcel').click(function (e) {
            e.preventDefault();
            $("#sampleTable").table2excel({
                filename: "ChiTietDonHang.xls"
            });
        });

        // Xuất PDF
        $('#exportPDF').click(function (e) {
            e.preventDefault();
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.autoTable({ html: '#sampleTable' });
            doc.save('ChiTietDonHang.pdf');
        });

        // In dữ liệu
        $('#printTable').click(function (e) {
            e.preventDefault();
            var printWindow = window.open('', '', 'height=700,width=700');
            printWindow.document.write('<html><head><title>In dữ liệu</title>');
            printWindow.document.write('<link rel="stylesheet" href="css/main.css" type="text/css" />');
            printWindow.document.write('</head><body>');
            printWindow.document.write(document.getElementById('sampleTable').outerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        });

        // Thời gian
        function time() {
            var today = new Date();
            var weekday = ["Chủ Nhật", "Thứ Hai", "Thứ Ba", "Thứ Tư", "Thứ Năm", "Thứ Sáu", "Thứ Bảy"];
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
            if (dd < 10) dd = '0' + dd;
            if (mm < 10) mm = '0' + mm;
            today = day + ', ' + dd + '/' + mm + '/' + yyyy;
            tmp = '<span class="date"> ' + today + ' - ' + nowTime + '</span>';
            document.getElementById("clock").innerHTML = tmp;
            clocktime = setTimeout("time()", "1000", "Javascript");

            function checkTime(i) {
                if (i < 10) i = "0" + i;
                return i;
            }
        }
    </script>
</body>
</html>