<?php
session_start();
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

// Xử lý đăng nhập khi form được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = $_POST["user_name"];
    $password = $_POST["password"];

    // Truy vấn kiểm tra thông tin đăng nhập
    $sql = "SELECT * FROM Users WHERE user_name = ? AND mat_khau = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user_name, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id']; // Lưu user_id vào session
        $role = $user['role'];

        if ($role == 1) {
            header("Location: admin.php");
        } else {
            header("Location: main.php"); // Có thể điều chỉnh lại
        }
        exit; // Thoát sau khi chuyển hướng
    } else {
        echo "<script>alert('Tên đăng nhập hoặc mật khẩu không chính xác!');</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="CSS/sign.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <h1 class="logo">Luxury Watches</h1>
        </div>
    </header>

    <!-- Form Đăng Nhập -->
    <div class="container">
        <div class="form-container">
            <form action="" method="post" id="login-form">
                <h2>Đăng Nhập</h2>
                <input type="text" name="user_name" placeholder="Tên đăng nhập" required>
                <input type="password" name="password" placeholder="Mật khẩu" required>
                <button type="submit">Đăng Nhập</button>
                <p>Bạn chưa có tài khoản? <a href="register.php">Đăng Ký</a></p>
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
                        <li><a href="register.php">Đăng ký</a></li>
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
