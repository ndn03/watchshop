<?php
// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = ""; // Thay bằng mật khẩu của bạn
$dbname = "watchshop"; // Tên cơ sở dữ liệu

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý đăng ký khi form được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["dangky"])) {
    $user_name = $_POST["user_name"];
    $email = $_POST["email"];
    $mat_khau = $_POST["mat_khau"];
    $confirm_mat_khau = $_POST["confirm_mat_khau"];
    $role = 0; // Mặc định là người dùng

    // Kiểm tra mật khẩu và xác nhận mật khẩu
    if ($mat_khau != $confirm_mat_khau) {
        echo "<script>alert('Mật khẩu và xác nhận mật khẩu không khớp!');</script>";
    } else {
        // Thêm dữ liệu vào bảng Users
        $sql = "INSERT INTO Users (user_name, email, mat_khau, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $user_name, $email, $mat_khau, $role);

        if ($stmt->execute()) {
            echo "<script>alert('Đăng ký thành công!'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Đăng ký thất bại: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <link rel="stylesheet" href="CSS/sign.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <h1 class="logo">Luxury Watches</h1>
        </div>
    </header>

    <!-- Form Đăng Ký -->
    <div class="container">
        <div class="form-container">
            <form id="register-form" action="" method="POST">
                <h2 id="register">Đăng Ký</h2>
                <input type="text" name="user_name" placeholder="Tên đăng nhập" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="mat_khau" placeholder="Mật khẩu" required>
                <input type="password" name="confirm_mat_khau" placeholder="Xác nhận mật khẩu" required>
                <button type="submit" name="dangky">Đăng Ký</button>
                <p>Bạn đã có tài khoản? <a href="login.php">Đăng Nhập</a></p>
            </form>
            <div class="sale"> <img id="img" onclick="changeImage()" src="img/sale1.jpg" width="720px" height="500px"></div>
        </div>
    </div>

    <script>
        var index = 0;
        var imgs = ["img/sale1.jpg", "img/sale2.jpg", "img/sale3.jpg"];
        function changeImage() {
            index = (index + 1) % imgs.length;
            document.getElementById('img').src = imgs[index];
        }
        setInterval(changeImage, 1800);
    </script>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h6>Về Chúng Tôi</h6>
                    <p>Luxury Watches cung cấp những chiếc đồng hồ cao cấp, chất lượng và phong cách.</p>
                </div>
                <div class="footer-section">
                    <h6>Liên Hệ</h6>
                    <ul>
                        <li><a href="#">Email: support@luxurywatches.com</a></li>
                        <li><a href="#">Điện thoại: 0123 456 789</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h6>Liên Kết Nhanh</h6>
                    <ul>
                        <li><a href="index.php">Trang Chủ</a></li>
                        <li><a href="login.php">Đăng Nhập</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Luxury Watches. Tất cả các quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>
</body>
</html>
