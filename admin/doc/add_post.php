<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';

if (!isset($_SESSION['ten_dang_nhap']) || $_SESSION['role'] !== 'admin') {
    header("Location: /admin/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tieu_de = mysqli_real_escape_string($conn, $_POST['tieu_de']);
    $noi_dung = mysqli_real_escape_string($conn, $_POST['noi_dung']);
    $nguoi_dang_id = $_SESSION['user_id']; // Giả sử user_id được lưu trong session
    
    $hinh_anh = '';
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/webshop/image/lv/post/';
        $hinh_anh = '/webshop/image/lv/post/' . basename($_FILES['hinh_anh']['name']);
        move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target_dir . basename($_FILES['hinh_anh']['name']));
    }

    $sql = "INSERT INTO bai_viet (tieu_de, noi_dung, hinh_anh, nguoi_dang_id) VALUES ('$tieu_de', '$noi_dung', '$hinh_anh', '$nguoi_dang_id')";
    if (mysqli_query($conn, $sql)) {
        header("Location: manage_posts.php");
        exit();
    } else {
        echo "<script>alert('Lỗi khi thêm bài viết!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Thêm bài viết | Quản trị Admin</title>
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
  <style>
        .centered-tile {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 150px);
        }
        .tile {
            width: 100%;
            max-width: 800px;
        }
    </style>
</head>
<body class="app sidebar-mini rtl">
    <header class="app-header">
        <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
        <ul class="app-nav">
            <li><a class="app-nav__item" href="/index.html"><i class='bx bx-log-out bx-rotate-180'></i></a></li>
        </ul>
    </header>
    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar">
        <div class="app-sidebar__user">
            <img class="app-sidebar__user-avatar" src="/images/hay.jpg" width="50px" alt="User Image">
            <div>
                <p class="app-sidebar__user-name"><b><?php echo htmlspecialchars($_SESSION['ten_dang_nhap']); ?></b></p>
                <p class="app-sidebar__user-designation">Chào mừng bạn trở lại</p>
            </div>
        </div>
        <hr>
        <ul class="app-menu">
            <li><a class="app-menu__item" href="phan-mem-ban-hang.php"><i class='app-menu__icon bx bx-cart-alt'></i><span class="app-menu__label">POS Bán Hàng</span></a></li>
            <li><a class="app-menu__item" href="index.php"><i class='app-menu__icon bx bx-tachometer'></i><span class="app-menu__label">Bảng điều khiển</span></a></li>
            <li><a class="app-menu__item" href="table-data-table.php"><i class='app-menu__icon bx bx-id-card'></i><span class="app-menu__label">Quản lý tài khoản</span></a></li>
            <li><a class="app-menu__item" href="table-data-product.php"><i class='app-menu__icon bx bx-purchase-tag-alt'></i><span class="app-menu__label">Quản lý sản phẩm</span></a></li>
            <li><a class="app-menu__item" href="table-data-oder.php"><i class='app-menu__icon bx bx-task'></i><span class="app-menu__label">Quản lý đơn hàng</span></a></li>
            <li><a class="app-menu__item active" href="manage_posts.php"><i class='app-menu__icon bx bx-news'></i><span class="app-menu__label">Quản lý bài viết</span></a></li>
        </ul>
    </aside>
    <main class="app-content">
        <div class="app-title">
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><a href="manage_posts.php">Danh sách bài viết</a></li>
                <li class="breadcrumb-item active"><b>Thêm bài viết</b></li>
            </ul>
            <div id="clock"></div>
        </div>
        <div class="row centered-tile">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Thêm bài viết mới</h3>
                    <div class="tile-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label class="control-label">Tiêu đề</label>
                                <input class="form-control" type="text" name="tieu_de" required>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Nội dung</label>
                                <textarea class="form-control" name="noi_dung" rows="5" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Hình ảnh</label>
                                <input class="form-control" type="file" name="hinh_anh">
                            </div>
                            <button class="btn btn-primary" type="submit">Thêm bài viết</button>
                            <a class="btn btn-secondary" href="manage_posts.php">Quay lại</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="/admin/js/jquery-3.2.1.min.js"></script>
    <script src="/admin/js/popper.min.js"></script>
    <script src="/admin/js/bootstrap.min.js"></script>
    <script src="/admin/js/main.js"></script>
    <script>
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