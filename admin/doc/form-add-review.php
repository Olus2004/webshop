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

// Xử lý thêm đánh giá
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $san_pham_id = mysqli_real_escape_string($conn, $_POST['san_pham_id']);
    $nguoi_dung_id = mysqli_real_escape_string($conn, $_POST['nguoi_dung_id']);
    $diem_danh_gia = mysqli_real_escape_string($conn, $_POST['diem_danh_gia']);
    $noi_dung = mysqli_real_escape_string($conn, $_POST['noi_dung']);
    $ngay_danh_gia = date('Y-m-d H:i:s'); // Lấy thời gian hiện tại

    $sql = "INSERT INTO danh_gia (san_pham_id, nguoi_dung_id, diem_danh_gia, noi_dung, ngay_danh_gia) 
            VALUES ('$san_pham_id', '$nguoi_dung_id', '$diem_danh_gia', '$noi_dung', '$ngay_danh_gia')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Thêm đánh giá thành công!'); window.location='manage_reviews.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi thêm đánh giá: " . mysqli_error($conn) . "');</script>";
    }
}

// Lấy danh sách sản phẩm và người dùng để hiển thị trong dropdown
$sql_products = "SELECT id, ten_san_pham FROM san_pham";
$result_products = mysqli_query($conn, $sql_products);

$sql_users = "SELECT id, ten_dang_nhap FROM nguoi_dung";
$result_users = mysqli_query($conn, $sql_users);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Thêm đánh giá mới | Quản trị Admin</title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" type="text/css" href="css/main.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
  <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
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
    <div class="app-sidebar__user"><img class="app-sidebar__user-avatar" src="/images/hay.jpg" width="50px" alt="User Image">
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
      <li><a class="app-menu__item active" href="manage_reviews.php"><i class='app-menu__icon bx bx-star'></i><span class="app-menu__label">Quản lý đánh giá</span></a></li>
    </ul>
  </aside>
  <main class="app-content">
    <div class="app-title">
      <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="manage_reviews.php">Danh sách đánh giá</a></li>
        <li class="breadcrumb-item active"><b>Thêm đánh giá mới</b></li>
      </ul>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="tile">
          <h3 class="tile-title">Thêm đánh giá mới</h3>
          <div class="tile-body">
            <form method="POST">
              <div class="form-group">
                <label>Sản phẩm:</label>
                <select name="san_pham_id" class="form-control" required>
                  <option value="">Chọn sản phẩm</option>
                  <?php while ($row = mysqli_fetch_assoc($result_products)) { ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['ten_san_pham']; ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="form-group">
                <label>Người dùng:</label>
                <select name="nguoi_dung_id" class="form-control" required>
                  <option value="">Chọn người dùng</option>
                  <?php while ($row = mysqli_fetch_assoc($result_users)) { ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['ten_dang_nhap']; ?></option>
                  <?php } ?>
                </select>
              </div>
              <div class="form-group">
                <label>Điểm đánh giá (1-5):</label>
                <input type="number" name="diem_danh_gia" class="form-control" min="1" max="5" required>
              </div>
              <div class="form-group">
                <label>Nội dung:</label>
                <textarea name="noi_dung" class="form-control" rows="5"></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Thêm đánh giá</button>
              <a href="manage_reviews.php" class="btn btn-secondary">Hủy</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
  <script src="js/jquery-3.2.1.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/main.js"></script>
</body>
</html>