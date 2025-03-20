<?php
session_start();
require '../php/db_connect.php';

// Kiểm tra vai trò người dùng (chỉ admin mới truy cập được)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Xử lý thêm người dùng
if (isset($_POST['add_user'])) {
    $ten_dang_nhap = $_POST['ten_dang_nhap'];
    $email = $_POST['email'];
    $mat_khau = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $dia_chi = $_POST['dia_chi'];

    $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, email, role, so_dien_thoai, dia_chi) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $ten_dang_nhap, $mat_khau, $email, $role, $so_dien_thoai, $dia_chi);
    $stmt->execute();
}

// Xử lý sửa người dùng
if (isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $ten_dang_nhap = $_POST['ten_dang_nhap'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $so_dien_thoai = $_POST['so_dien_thoai'];
    $dia_chi = $_POST['dia_chi'];

    if (!empty($_POST['mat_khau'])) {
        $mat_khau = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
        $sql = "UPDATE nguoi_dung SET ten_dang_nhap = ?, mat_khau = ?, email = ?, role = ?, so_dien_thoai = ?, dia_chi = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $ten_dang_nhap, $mat_khau, $email, $role, $so_dien_thoai, $dia_chi, $id);
    } else {
        $sql = "UPDATE nguoi_dung SET ten_dang_nhap = ?, email = ?, role = ?, so_dien_thoai = ?, dia_chi = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $ten_dang_nhap, $email, $role, $so_dien_thoai, $dia_chi, $id);
    }
    $stmt->execute();
}

// Xử lý xóa người dùng
if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    $sql = "DELETE FROM nguoi_dung WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Lấy thông tin người dùng để sửa
$edit_user = null;
if (isset($_GET['edit_user'])) {
    $id = $_GET['edit_user'];
    $sql = "SELECT * FROM nguoi_dung WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_user = $result->fetch_assoc();
}

// Lấy danh sách người dùng
$sql = "SELECT * FROM nguoi_dung";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../css/lab.css">
    <link rel="shortcut icon" href="../image/lv-logo.png" type="image/x-icon">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - Louis Vuitton</title>
</head>
<body>
<header>
            <nav class="nav">
                <p>
                    <b>
                        <a id="logo" href="../index.php">LOUIS VUITTON</a>
                    </b>
                </p>
                <a href="#" class="nav_buttom" id="menu-btn">☰ Menu</a>
                <a href="../search.php" class="nav_buttom" id="search">Tìm kiếm</a>
                <a href="../blog.php" class="nav_buttom" id="blog">Tin tức</a>
                <a href="../product.php" class="nav_buttom" id="product">Sản phẩm</a>
                <a href="../cart.php" class="nav_buttom" id="wishlist">Giỏ hàng</a>

                <?php if (isset($_SESSION['ten_dang_nhap'])): ?>
                    <!-- Khi đã đăng nhập -->
                    <a href="../profile.php" id="user_info">
                    <span>Xin chào, <b><?php echo htmlspecialchars($_SESSION['ten_dang_nhap']); ?></b></span></a>
                <?php else: ?>
                    <!-- Khi chưa đăng nhập -->
                    <a href="../login.php" class="nav_buttom" id="user_info">Đăng nhập</a>
                <?php endif; ?>
            </nav>

            <!-- Menu bật ra bên phải -->
            <div class="side-menu" id="side-menu">
                <a href="#" class="close-btn" id="close-btn">x</a>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../menu.php">Menu</a></li>
                    <li><a href="../search.php">Tìm kiếm</a></li>
                    <li><a href="../oder.php">Đơn hàng của tôi</a></li>
                    <li><a href="../cart.php">Giỏ hàng</a></li>
                    <?php if (isset($_SESSION['ten_dang_nhap'])): ?>
                        <li><a href="../controller/logout.php">Đăng xuất</a></li>
                    <?php else: ?>
                        <li><a href="../login.php">Đăng nhập</a></li>
                        <li><a href="../register.php">Đăng ký</a></li>
                    <?php endif; ?>
                    
                </ul>
            </div>
        </header>
    <main>
        <div class="nav_title">
            <p class="title_nav">Quản lý người dùng</p>
        </div>

        <!-- Form thêm người dùng -->
        <div class="form-container">
            <h2>Thêm người dùng</h2>
            <form method="POST">
                <input type="text" name="ten_dang_nhap" placeholder="Tên đăng nhập" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="mat_khau" placeholder="Mật khẩu" required>
                <select name="role" required>
                    <option value="admin">Admin</option>
                    <option value="member">Member</option>
                </select>
                <input type="text" name="so_dien_thoai" placeholder="Số điện thoại">
                <textarea name="dia_chi" placeholder="Địa chỉ"></textarea>
                <button type="submit" name="add_user">Thêm người dùng</button>
            </form>
        </div>

        <!-- Form sửa người dùng -->
        <?php if ($edit_user): ?>
            <div class="form-container">
                <h2>Sửa người dùng</h2>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                    <input type="text" name="ten_dang_nhap" value="<?php echo $edit_user['ten_dang_nhap']; ?>" required>
                    <input type="email" name="email" value="<?php echo $edit_user['email']; ?>" required>
                    <input type="password" name="mat_khau" placeholder="Nhập mật khẩu mới (nếu muốn thay đổi)">
                    <select name="role" required>
                        <option value="admin" <?php if ($edit_user['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                        <option value="member" <?php if ($edit_user['role'] === 'member') echo 'selected'; ?>>Member</option>
                    </select>
                    <input type="text" name="so_dien_thoai" value="<?php echo $edit_user['so_dien_thoai']; ?>">
                    <textarea name="dia_chi"><?php echo $edit_user['dia_chi']; ?></textarea>
                    <button type="submit" name="edit_user">Cập nhật người dùng</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Danh sách người dùng -->
        <div class="user-list">
            <h2>Danh sách người dùng</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên đăng nhập</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Số điện thoại</th>
                        <th>Địa chỉ</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['ten_dang_nhap']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo $user['role']; ?></td>
                            <td><?php echo $user['so_dien_thoai']; ?></td>
                            <td><?php echo $user['dia_chi']; ?></td>
                            <td>
                                <a href="?edit_user=<?php echo $user['id']; ?>">Sửa</a> |
                                <a href="?delete_user=<?php echo $user['id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa người dùng này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <footer>
        <nav class="nav_footer">
            <a class="footer_1" href="../Lab0_index.php">Home</a>
            <a class="footer_1" href="../Lab0_Oder-Menu.php">Menu</a>
            <a class="footer_1" href="../Lab0_hotline.php">Contact</a>
            <a class="footer_1" href="../Lab0_sinhvien.php">Thành viên trong nhóm</a>
        </nav>
    </footer>
</body>
</html>