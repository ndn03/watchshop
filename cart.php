<?php
session_start();  // Bắt đầu phiên làm việc của người dùng (session)

// Kết nối cơ sở dữ liệu
$servername = "localhost";  // Địa chỉ máy chủ cơ sở dữ liệu
$username = "root";  // Tên người dùng cơ sở dữ liệu
$password = "";  // Mật khẩu cơ sở dữ liệu (trong trường hợp này là rỗng)
$dbname = "watchshop";  // Tên cơ sở dữ liệu

// Tạo kết nối tới cơ sở dữ liệu MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);  // Nếu kết nối thất bại, dừng thực thi và hiển thị thông báo lỗi
}

// Kiểm tra đăng nhập của người dùng
if (!isset($_SESSION['user_id'])) {  // Nếu session không có `user_id`, tức là người dùng chưa đăng nhập
    header("Location: login.php");  // Chuyển hướng người dùng tới trang đăng nhập
    exit;  // Dừng thực thi mã
}

$user_id = $_SESSION['user_id'];  // Lấy `user_id` từ session của người dùng

// Lấy giỏ hàng của người dùng từ cơ sở dữ liệu
$sql = "SELECT gio_hang_id FROM giohang WHERE user_id = ?";  // Truy vấn ID giỏ hàng của người dùng từ bảng `giohang`
$stmt = $conn->prepare($sql);  // Chuẩn bị câu lệnh SQL
$stmt->bind_param("i", $user_id);  // Liên kết tham số `user_id` với câu lệnh SQL
$stmt->execute();  // Thực thi câu lệnh SQL
$result = $stmt->get_result();  // Lấy kết quả trả về từ cơ sở dữ liệu

// Kiểm tra xem người dùng có giỏ hàng không
if ($result->num_rows === 0) {  // Nếu không có giỏ hàng nào cho `user_id` này
    die("Không tìm thấy giỏ hàng cho user_id này.");  // Dừng mã và thông báo lỗi
}

$row = $result->fetch_assoc();  // Lấy kết quả truy vấn vào mảng kết hợp
$gio_hang_id = $row['gio_hang_id'];  // Lấy `gio_hang_id` từ kết quả truy vấn
$stmt->close();  // Đóng kết nối với câu lệnh SQL

// Lấy chi tiết sản phẩm trong giỏ hàng từ bảng `chitietgiohang` và bảng `sanpham`
$sql = "SELECT SP.hinh_anh, SP.ten, GH.gia, GH.san_pham_id, GH.so_luong 
        FROM chitietgiohang GH 
        INNER JOIN sanpham SP ON GH.san_pham_id = SP.san_pham_id 
        WHERE GH.gio_hang_id = ?";  // Truy vấn thông tin sản phẩm trong giỏ hàng của người dùng
$stmt = $conn->prepare($sql);  // Chuẩn bị câu lệnh SQL
$stmt->bind_param("i", $gio_hang_id);  // Liên kết tham số `gio_hang_id` với câu lệnh SQL
$stmt->execute();  // Thực thi câu lệnh SQL
$result = $stmt->get_result();  // Lấy kết quả trả về từ cơ sở dữ liệu

$total_price = 0;  // Khởi tạo biến tổng giá trị giỏ hàng

// Lấy danh sách phương thức thanh toán từ cơ sở dữ liệu
$sql_payment = "SELECT phuong_thuc_thanh_toan_id, ten_phuong_thuc FROM phuongthucthanhtoan";  // Truy vấn danh sách phương thức thanh toán
$result_payment = $conn->query($sql_payment);  // Thực thi câu lệnh SQL để lấy danh sách phương thức thanh toán
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng</title>
    <link rel="stylesheet" href="CSS/cart.css">
</head>
<body>

    <header>
        <div class="container">
            <a href="main.php" class="logo-link"> <!-- Thêm thẻ <a> với href dẫn về main.php -->
                <h1 class="logo">Luxury Watches</h1>
            </a>
        </div>
        
    </header>

    <div class="cart-container">
        <h2>Giỏ Hàng Của Bạn</h2>

        <table class="cart-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php 
                            $item_total = $row['gia'] * $row['so_luong'];
                            $total_price += $item_total; 
                        ?>
                        <tr>
                            <td>
                                <img src="<?php echo $row['hinh_anh']; ?>" alt="<?php echo $row['ten']; ?>" width="50">
                                <span><?php echo $row['ten']; ?></span>
                            </td>
                            <td><?php echo number_format($row['gia'], 0, ',', '.'); ?> VND</td>
                            <td><?php echo $row['so_luong']; ?></td>
                            <td>
                                <form action="delete.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="san_pham_id" value="<?php echo $row['san_pham_id']; ?>">
                                    <button type="submit" class="remove-btn">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Giỏ hàng của bạn đang trống!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="total-price">
            <h3>Tổng tiền: <?php echo number_format($total_price, 0, ',', '.'); ?> VND</h3>
        </div>
    </div>

    <div class="customer-info-container">
        <h2>Thông Tin Khách Hàng</h2>
        <form action="checkout.php" method="POST" class="customer-info-form">
            <div class="form-group">
                <label for="name">Họ và tên:</label>
                <input type="text" id="name_user" name="name_user" required>
            </div>

            <div class="form-group">
                <label for="address">Địa chỉ giao hàng:</label>
                <input type="text" id="dia_chi" name="dia_chi" required>
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại:</label>
                <input type="tel" id="dien_thoai" name="dien_thoai" required>
            </div>

            <div class="form-group">
                <label for="payment_method">Phương thức thanh toán:</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="" disabled selected>Chọn phương thức thanh toán</option>
                    <?php if ($result_payment->num_rows > 0): ?>
                        <?php while ($row_payment = $result_payment->fetch_assoc()): ?>
                            <option value="<?php echo $row_payment['phuong_thuc_thanh_toan_id']; ?>">
                                <?php echo $row_payment['ten_phuong_thuc']; ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="submit-btn">Xác Nhận Đặt Hàng</button>
        </form>
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
// Đóng kết nối
$stmt->close();
$conn->close();
?>
