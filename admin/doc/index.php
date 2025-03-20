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

// Truy vấn dữ liệu từ cơ sở dữ liệu
// Tổng số khách hàng
$sql_tong_khach_hang = "SELECT COUNT(*) as tong_khach_hang FROM nguoi_dung WHERE role = 'member'";
$result_tong_khach_hang = $conn->query($sql_tong_khach_hang);
if (!$result_tong_khach_hang) {
    die("Lỗi truy vấn khách hàng: " . $conn->error);
}
$row_tong_khach_hang = $result_tong_khach_hang->fetch_assoc();
$tong_khach_hang = $row_tong_khach_hang['tong_khach_hang'] ?? 0;

// Tổng số sản phẩm
$sql_tong_san_pham = "SELECT COUNT(*) as tong_san_pham FROM san_pham";
$result_tong_san_pham = $conn->query($sql_tong_san_pham);
if (!$result_tong_san_pham) {
    die("Lỗi truy vấn sản phẩm: " . $conn->error);
}
$row_tong_san_pham = $result_tong_san_pham->fetch_assoc();
$tong_san_pham = $row_tong_san_pham['tong_san_pham'] ?? 0;

// Tổng số đơn hàng trong tháng
$sql_tong_don_hang = "SELECT COUNT(*) as tong_don_hang FROM don_hang WHERE MONTH(ngay_dat) = MONTH(CURRENT_DATE())";
$result_tong_don_hang = $conn->query($sql_tong_don_hang);
if (!$result_tong_don_hang) {
    die("Lỗi truy vấn đơn hàng: " . $conn->error);
}
$row_tong_don_hang = $result_tong_don_hang->fetch_assoc();
$tong_don_hang = $row_tong_don_hang['tong_don_hang'] ?? 0;

// Số sản phẩm sắp hết hàng (sửa sang bảng san_pham_dung_tich)
$sql_sap_het_hang = "SELECT COUNT(DISTINCT san_pham_id) as sap_het_hang FROM san_pham_dung_tich WHERE so_luong <= 5";
$result_sap_het_hang = $conn->query($sql_sap_het_hang);
if (!$result_sap_het_hang) {
    die("Lỗi truy vấn sản phẩm hết hàng: " . $conn->error);
}
$row_sap_het_hang = $result_sap_het_hang->fetch_assoc();
$sap_het_hang = $row_sap_het_hang['sap_het_hang'] ?? 0;

// Danh sách 4 đơn hàng gần đây
$sql_don_hang = "SELECT don_hang.id AS id_don_hang, nguoi_dung.ten_dang_nhap AS ten_khach_hang, don_hang.tong_tien, don_hang.trang_thai 
                 FROM don_hang 
                 JOIN nguoi_dung ON don_hang.nguoi_dung_id = nguoi_dung.id 
                 ORDER BY don_hang.ngay_dat DESC 
                 LIMIT 4";
$result_don_hang = $conn->query($sql_don_hang);
if (!$result_don_hang) {
    die("Lỗi truy vấn đơn hàng: " . $conn->error);
}

// Danh sách 4 khách hàng mới
$sql_khach_hang = "SELECT id, ten_dang_nhap AS ten_khach_hang, ngay_tao AS ngay_sinh, so_dien_thoai 
                   FROM nguoi_dung 
                   WHERE role = 'member' 
                   ORDER BY ngay_tao DESC 
                   LIMIT 4";
$result_khach_hang = $conn->query($sql_khach_hang);
if (!$result_khach_hang) {
    die("Lỗi truy vấn khách hàng: " . $conn->error);
}

// Thống kê 6 tháng đầu vào và doanh thu
$months = [];
$sales_data = [];
$input_data = [];
$current_month = date('m');
$current_year = date('Y');

for ($i = 5; $i >= 0; $i--) {
    $month = ($current_month - $i <= 0) ? ($current_month - $i + 12) : ($current_month - $i);
    $year = ($month > $current_month) ? ($current_year - 1) : $current_year;
    $month_name = date('F', mktime(0, 0, 0, $month, 1));
    $months[] = $month_name . ' ' . $year;

    // Tổng doanh thu (loại bỏ các giá trị nhỏ hơn 10,000 VNĐ)
    $sql_sales = "SELECT SUM(tong_tien) as total_sales FROM don_hang WHERE MONTH(ngay_dat) = ? AND YEAR(ngay_dat) = ? AND tong_tien > 10000";
    $stmt_sales = $conn->prepare($sql_sales);
    $stmt_sales->bind_param("ii", $month, $year);
    $stmt_sales->execute();
    $result_sales = $stmt_sales->get_result();
    $row_sales = $result_sales->fetch_assoc();
    $sales_data[] = $row_sales['total_sales'] ?? 0;

    // Tổng số đơn hàng (đầu vào)
    $sql_input = "SELECT COUNT(*) as total_input FROM don_hang WHERE MONTH(ngay_dat) = ? AND YEAR(ngay_dat) = ?";
    $stmt_input = $conn->prepare($sql_input);
    $stmt_input->bind_param("ii", $month, $year);
    $stmt_input->execute();
    $result_input = $stmt_input->get_result();
    $row_input = $result_input->fetch_assoc();
    $input_data[] = $row_input['total_input'] ?? 0;

    $stmt_sales->close();
    $stmt_input->close();
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
  <link rel="stylesheet" type="text/css"
    href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">

</head>


<body onload="time()" class="app sidebar-mini rtl">
    <!-- Navbar -->
    <header class="app-header">
        <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
        <ul class="app-nav">
            <li><a class="app-nav__item" href="/index.html"><i class='bx bx-log-out bx-rotate-180'></i></a></li>
        </ul>
    </header>
    <!-- Sidebar menu -->
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
            <li><a class="app-menu__item active" href="index.php"><i class='app-menu__icon bx bx-tachometer'></i><span class="app-menu__label">Bảng điều khiển</span></a></li>
            <li><a class="app-menu__item" href="table-data-table.php"><i class='app-menu__icon bx bx-id-card'></i><span class="app-menu__label">Quản lý tài khoản</span></a></li>
            <li><a class="app-menu__item" href="table-data-product.php"><i class='app-menu__icon bx bx-purchase-tag-alt'></i><span class="app-menu__label">Quản lý sản phẩm</span></a></li>
            <li><a class="app-menu__item" href="table-data-oder.php"><i class='app-menu__icon bx bx-task'></i><span class="app-menu__label">Quản lý đơn hàng</span></a></li>
            <li><a class="app-menu__item" href="manage_posts.php"><i class='app-menu__icon bx bx-news'></i><span class="app-menu__label">Quản lý bài viết</span></a></li>
            <li><a class="app-menu__item" href="manage_reviews.php"><i class='app-menu__icon bx bx-star'></i><span class="app-menu__label">Quản lý đánh giá</span></a></li>
            <li><a class="app-menu__item" href="table-data-order-details.php"><i class='app-menu__icon bx bx-list-ul'></i><span class="app-menu__label">Chi tiết đơn hàng</span></a></li>
            <li><a class="app-menu__item" href="manage_separator_media.php"><i class='app-menu__icon bx bx-image'></i><span class="app-menu__label">Separator Media</span></a></li>


        </ul>
    </aside>
    <main class="app-content">
        <div class="row">
            <div class="col-md-12">
                <div class="app-title">
                    <ul class="app-breadcrumb breadcrumb">
                        <li class="breadcrumb-item"><a href="#"><b>Bảng điều khiển</b></a></li>
                    </ul>
                    <div id="clock"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Left -->
            <div class="col-md-12 col-lg-6">
                <div class="row">
                    <div class="col-md-6">
                        <div class="widget-small primary coloured-icon"><i class='icon bx bxs-user-account fa-3x'></i>
                            <div class="info">
                                <h4>Tổng khách hàng</h4>
                                <p><b><?php echo htmlspecialchars($tong_khach_hang); ?> khách hàng</b></p>
                                <p class="info-tong">Tổng số khách hàng được quản lý.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="widget-small info coloured-icon"><i class='icon bx bxs-data fa-3x'></i>
                            <div class="info">
                                <h4>Tổng sản phẩm</h4>
                                <p><b><?php echo htmlspecialchars($tong_san_pham); ?> sản phẩm</b></p>
                                <p class="info-tong">Tổng số sản phẩm được quản lý.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="widget-small warning coloured-icon"><i class='icon bx bxs-shopping-bags fa-3x'></i>
                            <div class="info">
                                <h4>Tổng đơn hàng</h4>
                                <p><b><?php echo htmlspecialchars($tong_don_hang); ?> đơn hàng</b></p>
                                <p class="info-tong">Tổng số hóa đơn bán hàng trong tháng.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="widget-small danger coloured-icon"><i class='icon bx bxs-error-alt fa-3x'></i>
                            <div class="info">
                                <h4>Sắp hết hàng</h4>
                                <p><b><?php echo htmlspecialchars($sap_het_hang); ?> sản phẩm</b></p>
                                <p class="info-tong">Số sản phẩm cảnh báo hết cần nhập thêm.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="tile">
                            <h3 class="tile-title">Tình trạng đơn hàng</h3>
                            <div>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID đơn hàng</th>
                                            <th>Tên khách hàng</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result_don_hang->num_rows > 0) {
                                            while ($row_don_hang = $result_don_hang->fetch_assoc()) {
                                                $trang_thai_class = '';
                                                switch (strtolower($row_don_hang['trang_thai'])) {
                                                    case 'chưa thanh toán':
                                                        $trang_thai_class = 'bg-info';
                                                        break;
                                                    case 'đang giao hàng':
                                                        $trang_thai_class = 'bg-warning';
                                                        break;
                                                    case 'đã giao hàng':
                                                        $trang_thai_class = 'bg-success';
                                                        break;
                                                    case 'đã hủy':
                                                        $trang_thai_class = 'bg-danger';
                                                        break;
                                                    default:
                                                        $trang_thai_class = 'bg-secondary';
                                                }
                                                echo "<tr>
                                                    <td>" . htmlspecialchars($row_don_hang['id_don_hang']) . "</td>
                                                    <td>" . htmlspecialchars($row_don_hang['ten_khach_hang']) . "</td>
                                                    <td>" . number_format($row_don_hang['tong_tien'], 0, ',', '.') . " đ</td>
                                                    <td><span class='badge " . $trang_thai_class . "'>" . htmlspecialchars($row_don_hang['trang_thai']) . "</span></td>
                                                </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4'>Không có dữ liệu đơn hàng.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="tile">
                            <h3 class="tile-title">Khách hàng mới</h3>
                            <div>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên khách hàng</th>
                                            <th>Ngày sinh</th>
                                            <th>Số điện thoại</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result_khach_hang->num_rows > 0) {
                                            while ($row_khach_hang = $result_khach_hang->fetch_assoc()) {
                                                $tag_class = 'tag-success';
                                                if (rand(0, 3) == 1) $tag_class = 'tag-warning';
                                                elseif (rand(0, 3) == 2) $tag_class = 'tag-primary';
                                                elseif (rand(0, 3) == 3) $tag_class = 'tag-danger';
                                                echo "<tr>
                                                    <td>#" . htmlspecialchars($row_khach_hang['id']) . "</td>
                                                    <td>" . htmlspecialchars($row_khach_hang['ten_khach_hang']) . "</td>
                                                    <td>" . htmlspecialchars(date('Y-m-d', strtotime($row_khach_hang['ngay_sinh'] ?? '0000-00-00'))) . "</td>
                                                    <td><span class='tag " . $tag_class . "'>" . htmlspecialchars($row_khach_hang['so_dien_thoai']) . "</span></td>
                                                </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4'>Không có dữ liệu khách hàng.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right -->
            <div class="col-md-12 col-lg-6">
                <div class="row">
                    <div class="col-md-12">
                        <div class="tile">
                            <h3 class="tile-title">Dữ liệu 6 tháng đầu vào</h3>
                            <div class="embed-responsive embed-responsive-16by9">
                                <canvas class="embed-responsive-item" id="lineChartDemo"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="tile">
                            <h3 class="tile-title">Thống kê 6 tháng doanh thu</h3>
                            <div class="embed-responsive embed-responsive-16by9">
                                <canvas class="embed-responsive-item" id="barChartDemo"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END Right -->
        </div>

        <div class="text-center" style="font-size: 13px">
            <p><b>Copyright
                    <script type="text/javascript">
                        document.write(new Date().getFullYear());
                    </script> Phần mềm quản lý bán hàng | Dev By Trường
                </b></p>
        </div>
    </main>
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="https://unpkg.com/boxicons@latest/dist/boxicons.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
    <script src="../js/plugins/pace.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script type="text/javascript">
        // Dữ liệu từ PHP
        var months = <?php echo json_encode($months); ?>;
        var salesData = <?php echo json_encode($sales_data); ?>;
        var inputData = <?php echo json_encode($input_data); ?>;

        // Biểu đồ Dữ liệu 6 tháng đầu vào (Line Chart)
        var ctxl = document.getElementById('lineChartDemo').getContext('2d');
        var lineChart = new Chart(ctxl, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Số đơn hàng',
                    data: inputData,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Số đơn hàng'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tháng'
                        }
                    }
                }
            }
        });

        // Biểu đồ Thống kê 6 tháng doanh thu (Bar Chart)
        var ctxb = document.getElementById('barChartDemo').getContext('2d');
        var barChart = new Chart(ctxb, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: salesData,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Doanh thu (VNĐ)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + ' VNĐ';
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tháng'
                        }
                    }
                }
            }
        });
    </script>
    <script type="text/javascript">
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