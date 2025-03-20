<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

if (!isset($_SESSION['ten_dang_nhap']) || $_SESSION['role'] !== 'admin') {
    header("Location: /../webshop/login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    // Lấy đường dẫn hình ảnh để xóa
    $sql = "SELECT hinh_anh FROM bai_viet WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);
    $post = mysqli_fetch_assoc($result);
    if ($post['hinh_anh']) {
        $image_path = $_SERVER['DOCUMENT_ROOT'] . $post['hinh_anh'];
        if (file_exists($image_path)) {
            unlink($image_path); // Xóa file hình ảnh
        }
    }
    $sql = "DELETE FROM bai_viet WHERE id = '$id'";
    if (mysqli_query($conn, $sql)) {
        echo "<script>swal('Đã xóa bài viết thành công!');</script>";
    } else {
        echo "<script>swal('Lỗi khi xóa bài viết!');</script>";
    }
}

$sql = "SELECT bv.id, bv.tieu_de, bv.noi_dung, bv.hinh_anh, bv.ngay_dang, nd.ten_dang_nhap 
        FROM bai_viet bv 
        LEFT JOIN nguoi_dung nd ON bv.nguoi_dang_id = nd.id 
        ORDER BY bv.ngay_dang DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Danh sách bài viết | Quản trị Admin</title>
    <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Main CSS-->
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
  <!-- or -->
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

  <!-- Font-icon css-->
  <link rel="stylesheet" type="text/css"
    href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">

</head>
<body onload="time()" class="app sidebar-mini rtl">
    <header class="app-header">
        <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
        <ul class="app-nav">
            <li><a class="app-nav__item" href="/index.html"><i class='bx bx-log-out bx-rotate-180'></i></a></li>
        </ul>
    </header>
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
            <li><a class="app-menu__item active" href="manage_posts.php"><i class='app-menu__icon bx bx-news'></i><span class="app-menu__label">Quản lý bài viết</span></a></li>
            <li><a class="app-menu__item" href="manage_reviews.php"><i class='app-menu__icon bx bx-star'></i><span class="app-menu__label">Quản lý đánh giá</span></a></li>
            <li><a class="app-menu__item" href="table-data-order-details.php"><i class='app-menu__icon bx bx-list-ul'></i><span class="app-menu__label">Chi tiết đơn hàng</span></a></li>
            <li><a class="app-menu__item" href="manage_separator_media.php"><i class='app-menu__icon bx bx-image'></i><span class="app-menu__label">Separator Media</span></a></li>

        </ul>
    </aside>
    <main class="app-content">
        <div class="app-title">
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item active"><a href="#"><b>Danh sách bài viết</b></a></li>
            </ul>
            <div id="clock"></div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <div class="row element-button">
                            <div class="col-sm-2">
                                <a class="btn btn-add btn-sm" href="add_post.php" title="Thêm"><i class="fas fa-plus"></i> Tạo bài viết mới</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-excel btn-sm" href="" title="In"><i class="fas fa-file-excel"></i> Xuất Excel</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm pdf-file" type="button" title="In" onclick="myFunction(this)"><i class="fas fa-file-pdf"></i> Xuất PDF</a>
                            </div>
                            <div class="col-sm-2">
                                <a class="btn btn-delete btn-sm print-file" type="button" title="In" onclick="myApp.printTable()"><i class="fas fa-print"></i> In dữ liệu</a>
                            </div>
                        </div>
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th width="10"><input type="checkbox" id="all"></th>
                                    <th>ID</th>
                                    <th>Tiêu đề</th>
                                    <th>Người đăng</th>
                                    <th>Ngày đăng</th>
                                    <th>Hình ảnh</th>
                                    <th>Tính năng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr>
                                        <td width="10"><input type="checkbox" name="check1" value="<?php echo $row['id']; ?>"></td>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['tieu_de']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ten_dang_nhap']); ?></td>
                                        <td><?php echo $row['ngay_dang']; ?></td>
                                        <td>
                                            <?php if ($row['hinh_anh']) { ?>
                                                <img src="<?php echo $row['hinh_anh']; ?>" width="50">
                                            <?php } else { ?>
                                                Không có
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm trash" type="button" title="Xóa" data-id="<?php echo $row['id']; ?>"><i class="fas fa-trash-alt"></i></button>
                                            <button class="btn btn-primary btn-sm edit" type="button" title="Sửa" onclick="location.href='edit_post.php?id=<?php echo $row['id']; ?>'"><i class="fa fa-edit"></i></button>
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
    <script src="/admin/js/jquery-3.2.1.min.js"></script>
    <script src="/admin/js/popper.min.js"></script>
    <script src="/admin/js/bootstrap.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="/admin/src/jquery.table2excel.js"></script>
    <script src="/admin/js/main.js"></script>
    <script src="/admin/js/plugins/pace.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <script type="text/javascript" src="/admin/js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="/admin/js/plugins/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript">$('#sampleTable').DataTable();</script>
    <script>
        jQuery(function () {
            jQuery(".trash").click(function () {
                var id = $(this).data('id');
                swal({
                    title: "Cảnh báo",
                    text: "Bạn có chắc chắn muốn xóa bài viết này?",
                    buttons: ["Hủy bỏ", "Đồng ý"],
                })
                    .then((willDelete) => {
                        if (willDelete) {
                            window.location = "?delete=" + id;
                            swal("Đã xóa thành công!", {});
                        }
                    });
            });
        });

        oTable = $('#sampleTable').dataTable();
        $('#all').click(function (e) {
            $('#sampleTable tbody :checkbox').prop('checked', $(this).is(':checked'));
            e.stopImmediatePropagation();
        });

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

        var myApp = new function () {
            this.printTable = function () {
                var tab = document.getElementById('sampleTable');
                var win = window.open('', '', 'height=700,width=700');
                win.document.write(tab.outerHTML);
                win.document.close();
                win.print();
            }
        }
    </script>
</body>
</html>