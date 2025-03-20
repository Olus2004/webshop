<?php
session_start();
require '../php/db_connect.php';

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Xử lý đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $mat_khau = $_POST['current-password'] ?? '';

    // Debug: Kiểm tra dữ liệu gửi từ form
    // echo "Debug: Username = $username, Password = $mat_khau<br>";

    // Kiểm tra thông tin nhập vào
    if (empty($username) || empty($mat_khau)) {
        echo '<script>swal("Lỗi!", "Vui lòng điền đầy đủ tài khoản và mật khẩu!", "error");</script>';
    } else {
        // Kiểm tra thông tin đăng nhập trong cơ sở dữ liệu
        $sql = "SELECT ten_dang_nhap, mat_khau, role FROM nguoi_dung WHERE ten_dang_nhap = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo '<script>swal("Lỗi!", "Lỗi truy vấn: ' . $conn->error . '", "error");</script>';
            exit();
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Xác minh mật khẩu
            if (password_verify($mat_khau, $user['mat_khau'])) {
                // Kiểm tra vai trò (chỉ admin được đăng nhập)
                if ($user['role'] === 'admin') {
                    // Lưu thông tin vào session
                    $_SESSION['ten_dang_nhap'] = $user['ten_dang_nhap'];
                    $_SESSION['role'] = $user['role'];
                    // Chuyển hướng bằng PHP thay vì JavaScript
                    header("Location: ../admin/doc/index.php"); // Đường dẫn tuyệt đối
                    exit();
                } else {
                    echo '<script>alert("Bạn không có quyền truy cập vào hệ thống quản trị!");</script>';
                }
            } else {
                echo '<script>alert("Tài khoản hoặc mật khẩu không đúng!");</script>';
            }
        } else {
            echo '<script>alert("Tài khoản không tồn tại!");</script>';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Đăng nhập quản trị | Website quản trị v2.0</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/1.0.1/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
</head>
<body>
    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic js-tilt" data-tilt>
                    <img src="images/team.jpg" alt="IMG">
                </div>
                <!--=====TIÊU ĐỀ======-->
                <form class="login100-form validate-form" method="POST" action="">
                    <span class="login100-form-title">
                        <b>ĐĂNG NHẬP HỆ THỐNG
                        </br> CỬA HÀNG NƯỚC HOA</b>
                    </span>
                    <!--=====FORM INPUT TÀI KHOẢN VÀ PASSWORD======-->
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="text" placeholder="Tài khoản quản trị" name="username" id="username">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class='bx bx-user'></i>
                        </span>
                    </div>
                    <div class="wrap-input100 validate-input">
                        <input autocomplete="off" class="input100" type="password" placeholder="Mật khẩu" name="current-password" id="password-field">
                        <span toggle="#password-field" class="bx fa-fw bx-hide field-icon click-eye"></span>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class='bx bx-key'></i>
                        </span>
                    </div>

                    <!--=====ĐĂNG NHẬP======-->
                    <div class="container-login100-form-btn">
                        <input type="submit" value="Đăng nhập" id="submit" class="login100-form-btn" />
                    </div>
                    <!--=====LINK TÌM MẬT KHẨU======-->
                    <div class="text-right p-t-12">
                        <a class="txt2" href="forgot.html">
                            Bạn quên mật khẩu?
                        </a>
                    </div>
                    <!--=====FOOTER======-->
                    <div class="text-center p-t-70 txt2">
                        Phần mềm quản lý <i class="far fa-copyright" aria-hidden="true"></i>
                        <script type="text/javascript">document.write(new Date().getFullYear());</script> <a class="txt2" href=""> </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--Javascript-->
    <script src="/js/main.js"></script>
    <script src="https://unpkg.com/boxicons@latest/dist/boxicons.js"></script>
    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap/js/popper.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="vendor/select2/select2.min.js"></script>
    <script type="text/javascript">
        //show - hide mật khẩu
        function myFunction() {
            var x = document.getElementById("myInput");
            if (x.type === "password") {
                x.type = "text"
            } else {
                x.type = "password";
            }
        }
        $(".click-eye").click(function () {
            $(this).toggleClass("bx-show bx-hide");
            var input = $($(this).attr("toggle"));
            if (input.attr("type") == "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }
        });
    </script>
</body>
</html>