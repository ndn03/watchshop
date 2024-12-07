<?php
session_start(); // Bắt đầu phiên làm việc (session) để sử dụng biến session

// Kết nối cơ sở dữ liệu
$servername = "localhost"; // Máy chủ cơ sở dữ liệu
$username = "root"; // Tên đăng nhập cơ sở dữ liệu
$password = ""; // Mật khẩu cơ sở dữ liệu
$dbname = "watchshop"; // Tên cơ sở dữ liệu

// Tạo đối tượng kết nối với cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error); // Nếu kết nối thất bại, dừng và thông báo lỗi
}

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Chuyển hướng người dùng đến trang đăng nhập
    exit; // Dừng thực thi code tiếp theo
}

$user_id = $_SESSION['user_id']; // Lấy user_id từ session (người dùng hiện tại)
$don_hang_id = $_GET['don_hang_id']; // Lấy ID đơn hàng từ tham số URL

// Truy vấn thông tin đơn hàng
$sql = "SELECT DH.don_hang_id, DH.name_user, DH.tong_tien, DH.dia_chi, DH.so_dien_thoai, 
PT.ten_phuong_thuc AS phuong_thuc_thanh_toan, TT.ten_trang_thai AS trang_thai
FROM donhang DH
INNER JOIN phuongthucthanhtoan PT ON DH.phuong_thuc_thanh_toan_id = PT.phuong_thuc_thanh_toan_id
INNER JOIN trangthai TT ON DH.trang_thai_id = TT.trang_thai_id
WHERE DH.don_hang_id = ? AND DH.user_id = ?"; // Truy vấn lấy thông tin đơn hàng kết hợp với phương thức thanh toán và trạng thái đơn hàng

$stmt = $conn->prepare($sql); // Chuẩn bị câu lệnh SQL
if (!$stmt) {
    die("Lỗi chuẩn bị truy vấn: " . $conn->error); // Kiểm tra nếu có lỗi khi chuẩn bị câu lệnh
}

$stmt->bind_param("ii", $don_hang_id, $user_id); // Gắn giá trị cho các tham số trong câu lệnh SQL (don_hang_id và user_id)
$stmt->execute(); // Thực thi câu lệnh SQL
$order_result = $stmt->get_result(); // Lấy kết quả từ cơ sở dữ liệu
$order = $order_result->fetch_assoc(); // Lấy kết quả dưới dạng mảng kết hợp (associative array)
$stmt->close(); // Đóng statement sau khi thực thi

// Truy vấn lấy chi tiết sản phẩm của đơn hàng
$sql = "SELECT SP.ten, CDH.so_luong, CDH.gia_tai_thoi_diem 
        FROM chitietdonhang CDH
        INNER JOIN sanpham SP ON CDH.san_pham_id = SP.san_pham_id
        WHERE CDH.don_hang_id = ?"; // Truy vấn lấy chi tiết sản phẩm từ bảng chitietdonhang kết hợp với bảng sanpham

$stmt = $conn->prepare($sql); // Chuẩn bị câu lệnh SQL
if (!$stmt) {
    die("Lỗi chuẩn bị truy vấn (chi tiết đơn hàng): " . $conn->error); // Kiểm tra nếu có lỗi khi chuẩn bị câu lệnh
}

$stmt->bind_param("i", $don_hang_id); // Gắn giá trị cho tham số don_hang_id
$stmt->execute(); // Thực thi câu lệnh SQL
$details_result = $stmt->get_result(); // Lấy kết quả chi tiết sản phẩm từ cơ sở dữ liệu
$stmt->close(); // Đóng statement sau khi thực thi
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận Đơn hàng</title>
    <link rel="stylesheet" href="CSS/order.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="main.php" class="logo-link">
                <h1 class="logo">Luxury Watches</h1>
            </a>
        </div>
    </header>

    <div class="container-show">
        <h2>Xác nhận Đơn hàng #<?php echo htmlspecialchars($order['don_hang_id']); ?></h2>
        <p>Cảm ơn bạn đã đặt hàng. Dưới đây là thông tin chi tiết về đơn hàng của bạn:</p>

        <h3>Thông tin đơn hàng</h3>
        <p><strong>Mã đơn hàng:</strong> <?php echo htmlspecialchars($order['don_hang_id']); ?></p>
        <p><strong>Tên khách hàng:</strong> <?php echo htmlspecialchars($order['name_user']); ?></p>
        <p><strong>Địa chỉ giao hàng:</strong> <?php echo htmlspecialchars($order['dia_chi']); ?></p>
        <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['so_dien_thoai']); ?></p>
        <p><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($order['phuong_thuc_thanh_toan']); ?></p>
        <p><strong>Trạng thái:</strong> <?php echo htmlspecialchars($order['trang_thai']); ?></p>
        <p><strong>Tổng tiền:</strong> <?php echo number_format($order['tong_tien'], 0, ',', '.'); ?> VND</p>

        <h3>Chi tiết sản phẩm</h3>
        <table>
            <tr>
                <th>Sản phẩm</th>
                <th>Số lượng</th>
                <th>Giá</th>
            </tr>
            <?php while ($detail = $details_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($detail['ten']); ?></td>
                    <td><?php echo htmlspecialchars($detail['so_luong']); ?></td>
                    <td><?php echo number_format($detail['gia_tai_thoi_diem'], 0, ',', '.'); ?> VND</td>
                </tr>
            <?php endwhile; ?>
        </table>

        <p>Chúng tôi sẽ sớm liên hệ với bạn để xác nhận đơn hàng. Nếu có bất kỳ câu hỏi nào, vui lòng liên hệ với bộ phận hỗ trợ khách hàng của chúng tôi.</p>
        <a href="main.php">Quay lại trang chủ</a><br>
        <a href="account.php">Xem giỏ hàng</a>
    </div>
    
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
                        <li><a href="main.php">Trang Chủ</a></li>
                        <li><a href="main.php">Sản Phẩm</a></li>
                        <li><a href="logout.php">Đăng Xuất</a></li>
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

<?php
$conn->close();
?>
