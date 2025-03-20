<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/webshop/php/db_connect.php';

if (!isset($_SESSION['ten_dang_nhap']) || $_SESSION['role'] !== 'admin') {
    header("Location: /admin/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $media_path = mysqli_real_escape_string($conn, $_POST['media_path']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $mo_ta = mysqli_real_escape_string($conn, $_POST['mo_ta']);
    $thu_tu = (int)$_POST['thu_tu'];

    $sql = "INSERT INTO separator_media (media_path, type, mo_ta, thu_tu) 
            VALUES ('$media_path', '$type', '$mo_ta', '$thu_tu')";
    if (mysqli_query($conn, $sql)) {
        header("Location: manage_separator_media.php");
        exit();
    } else {
        echo "<script>swal('Lỗi!', 'Không thể thêm Separator Media: " . mysqli_error($conn) . "', 'error');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Thêm Separator Media | Quản trị Admin</title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Main CSS-->
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
  <!-- Font-icon css-->
  <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
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
      <img class="app-sidebar__user-avatar" src="/images/hay.jpg" width="50px" alt="User Image">
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
      <li><a class="app-menu__item active" href="manage_separator_media.php"><i class='app-menu__icon bx bx-image'></i><span class="app-menu__label">Quản lý Separator Media</span></a></li>
    </ul>
  </aside>
  <main class="app-content">
    <div class="app-title">
      <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="manage_separator_media.php">Danh sách Separator Media</a></li>
        <li class="breadcrumb-item active"><a href="#">Thêm Separator Media</a></li>
      </ul>
      <div id="clock"></div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="tile">
          <h3 class="tile-title">Thêm Separator Media</h3>
          <div class="tile-body">
            <form method="POST" class="row">
              <div class="form-group col-md-6">
                <label class="control-label">Đường dẫn Media</label>
                <input class="form-control" type="text" name="media_path" required>
              </div>
              <div class="form-group col-md-6">
                <label class="control-label">Loại</label>
                <select class="form-control" name="type" required>
                  <option value="image">Hình ảnh</option>
                  <option value="video">Video</option>
                  <option value="none">Không</option>
                </select>
              </div>
              <div class="form-group col-md-12">
                <label class="control-label">Mô tả</label>
                <textarea class="form-control" name="mo_ta" rows="4"></textarea>
              </div>
              <div class="form-group col-md-6">
                <label class="control-label">Thứ tự</label>
                <input class="form-control" type="number" name="thu_tu" required>
              </div>
              <div class="form-group col-md-12">
                <button class="btn btn-save" type="submit">Lưu lại</button>
                <a class="btn btn-cancel" href="manage_separator_media.php">Hủy bỏ</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
  <!-- Essential javascripts -->
  <script src="js/jquery-3.2.1.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/main.js"></script>
  <script src="js/plugins/pace.min.js"></script>
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