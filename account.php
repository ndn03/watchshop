<?php
// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "watchshop";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

session_start();  // Bắt đầu session để lấy thông tin người dùng

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    // Nếu không có user_id trong session, chuyển hướng người dùng đến trang đăng nhập
    header("Location: login.php");
    exit;  // Dừng mã nếu người dùng chưa đăng nhập
}

$user_id = $_SESSION['user_id'];  // Lấy user_id từ session
// Xử lý xóa đơn hàng
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_id"])) {
    $don_hang_id = intval($_POST["delete_id"]);

    // Xóa chi tiết đơn hàng
    $conn->query("DELETE FROM ChiTietDonHang WHERE don_hang_id = $don_hang_id");

    // Xóa đơn hàng
    if ($conn->query("DELETE FROM DonHang WHERE don_hang_id = $don_hang_id")) {
        $message = "Đơn hàng đã được xóa thành công.";
    } else {
        $message = "Lỗi: Không thể xóa đơn hàng.";
    }
}
// Lấy danh sách đơn hàng của người dùng
$sql = "
    SELECT dh.don_hang_id, dh.name_user, dh.dia_chi, dh.so_dien_thoai, pttt.ten_phuong_thuc, dh.ngay_dat, tt.ten_trang_thai, 
           sp.ten AS ten, sp.hinh_anh AS hinh_anh, sp.gia AS gia
    FROM DonHang dh
    INNER JOIN PhuongThucThanhToan pttt ON dh.phuong_thuc_thanh_toan_id = pttt.phuong_thuc_thanh_toan_id
    INNER JOIN TrangThai tt ON dh.trang_thai_id = tt.trang_thai_id
    INNER JOIN ChiTietDonHang ctdh ON dh.don_hang_id = ctdh.don_hang_id
    INNER JOIN SanPham sp ON ctdh.san_pham_id = sp.san_pham_id
    WHERE dh.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);  // Truyền vào user_id để lấy thông tin đơn hàng của người dùng
$stmt->execute();
$result = $stmt->get_result();

// Đóng kết nối
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thay Đổi Thông Tin Cá Nhân</title>
    <link rel="stylesheet" href="CSS/account.css">
</head>
<body>
    <header>
        <div class="container">
            <a href="main.php" class="logo-link">
                <h1 class="logo">Luxury Watches</h1>
            </a>
        </div>
    </header>
    <div class="full-content">
        <div id="sidebar">
            <ul>
                <li><a href="#" data-section="cart">Đơn hàng</a></li>
                <li><a href="#" data-section="acc">Tài khoản</a></li>
              
            </ul>
        </div>
    
        <div id="content">
            <!-- Section cart -->
            <div id="cart" class="section active">
                <h2>Đơn Hàng Của Bạn</h2>
                <table border="1">
                    <thead>
                        <tr>
                          <th>Mã đơn hàng</th>
                          <th>Tên khách hàng</th>
                          <th>Địa chỉ</th>
                          <th>Số điện thoại</th>
                          <th>Phương thức thanh toán</th>
                          <td>Ngày đặt</td>
                          <th>Trạng thái</th>
                          <th>Thao tác</th>
                          <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Hiển thị dữ liệu đơn hàng
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['don_hang_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['name_user']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['dia_chi']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['so_dien_thoai']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ten_phuong_thuc']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ngay_dat']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ten_trang_thai']) . "</td>";
                           
                            echo "<td>
                            <button class='view-details' 
                                data-product-name='" . htmlspecialchars($row['ten']) . "'
                                data-product-image='" . htmlspecialchars($row['hinh_anh']) . "'
                                data-product-price='" . htmlspecialchars($row['gia']) . "'
                            >Xem chi tiết</button>
                          </td>";
                          echo "<td>
                          <form method='POST' action=''>
                              <input type='hidden' name='delete_id' value='" . $row['don_hang_id'] . "'>
                              <button type='submit' onclick='return confirm(\"Bạn có chắc chắn muốn xóa đơn hàng này?\")'>Xóa</button>
                          </form>
                      </td>";
                            "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>Không có đơn hàng nào.</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
    
            <!-- Popup cho chi tiết sản phẩm -->
            <div id="productDetailPopup" class="popup">
                <div class="popup-content">
                    <span class="close" id="closePopup">&times;</span>
                    <h3>Chi tiết sản phẩm</h3>
                    <div id="productDetails">
                        <table>
                            <tr>
                                <td><strong>Tên sản phẩm:</strong></td>
                                <td><strong>Ảnh sản phẩm:</strong></td>
                                <td><strong>Giá:</strong></td>
                            </tr>
                            <tr>
                                <td><span id="productNameDetail"></span></td>
                                <td><img id="productImageDetail" src="" alt="Product Image" width="100"></td>
                                <td><span id="productPriceDetail"></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Section tài khoản -->
            <div id="acc" class="section">
            <div class="container-form">
    <h2>Thay Đổi Thông Tin Cá Nhân</h2>
<form action="changepass.php" method="POST">
    
    <div class="form-group">
        <label for="current-password">Mật khẩu hiện tại:</label>
        <div class="password-wrapper">
            <input type="password" id="change-password" name="mat_khau" required>
            <span class="toggle-password" onclick="togglePassword('change-password')">👁️</span>
        </div>
    </div>
    
    <div class="form-group">
        <label for="new-password">Mật khẩu mới:</label>
        <div class="password-wrapper">
            <input type="password" id="new-password" name="mat_khau_moi" required>
            <span class="toggle-password" onclick="togglePassword('new-password')">👁️</span>
        </div>
    </div>
    
    <div class="form-group">
        <label for="confirm-password">Xác nhận mật khẩu mới:</label>
        <div class="password-wrapper">
            <input type="password" id="confirm-password" name="xac_nhan_mat_khau" required>
            <span class="toggle-password" onclick="togglePassword('confirm-password')">👁️</span>
        </div>
    </div>
    
    <button type="submit" class="submit-btn">Lưu Thay Đổi</button>
</form>

<script>
// Hàm để bật/tắt chế độ hiển thị mật khẩu
function togglePassword(inputId) {
    var input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}
</script>

</div>
            </div>
            
            <!-- Các section khác -->
            <div id="services" class="section">
                <h2>Dịch Vụ</h2>
                <p>Chi tiết về các dịch vụ bạn cung cấp.</p>
            </div>
            <div id="contact" class="section">
                <h2>Liên Hệ</h2>
                <p>Thông tin liên hệ của bạn.</p>
            </div>
        </div>
    
        <!-- JavaScript -->
        <script>
            /// Quản lý điều hướng sidebar
            const menuLinks = document.querySelectorAll('#sidebar a');

            // Lắng nghe sự kiện click trên các liên kết
            menuLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault(); // Ngừng hành động mặc định (không tải lại trang)

                    // Ẩn tất cả các section
                    const sections = document.querySelectorAll('.section');
                    sections.forEach(section => section.classList.remove('active'));

                    // Hiển thị section tương ứng với liên kết được click
                    const sectionId = link.getAttribute('data-section');
                    const sectionToShow = document.getElementById(sectionId);
                    sectionToShow.classList.add('active');
                });
            });

            // Quản lý popup chi tiết sản phẩm
            const viewDetailsLinks = document.querySelectorAll('.view-details');
            const productDetailPopup = document.getElementById('productDetailPopup');
            const closePopup = document.getElementById('closePopup');
            const productNameDetail = document.getElementById('productNameDetail');
            const productImageDetail = document.getElementById('productImageDetail');
            const productPriceDetail = document.getElementById('productPriceDetail');

            // Thêm sự kiện click cho các liên kết "Xem chi tiết"
            viewDetailsLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();  // Ngừng hành động mặc định của liên kết

                    // Lấy thông tin từ các thuộc tính data của liên kết
                    const productName = link.getAttribute('data-product-name');
                    const productImage = link.getAttribute('data-product-image');
                    const productPrice = link.getAttribute('data-product-price');

                    // Cập nhật thông tin sản phẩm vào popup
                    productNameDetail.textContent = productName;
                    productImageDetail.src = productImage;
                    productPriceDetail.textContent = productPrice;

                    // Hiển thị popup
                    productDetailPopup.style.display = 'flex';
                });
            });

            // Đóng popup khi nhấp vào dấu "x"
            closePopup.addEventListener('click', function() {
                productDetailPopup.style.display = 'none';
            });

            // Đóng popup khi nhấp ngoài popup
            window.addEventListener('click', function(event) {
                if (event.target === productDetailPopup) {
                    productDetailPopup.style.display = 'none';
                }
            });

            // Chuyển đổi hiển thị mật khẩu
            function togglePassword(id) {
                const passwordField = document.getElementById(id);
                const icon = passwordField.nextElementSibling;

                if (passwordField.type === "password") {
                    passwordField.type = "text"; // Hiển thị mật khẩu
                    icon.textContent = "🙈"; // Thay đổi biểu tượng
                } else {
                    passwordField.type = "password"; // Ẩn mật khẩu
                    icon.textContent = "👁️"; // Thay đổi biểu tượng
                }
            }

            // Xử lý form thay đổi thông tin cá nhân
            document.querySelector('.submit-btn').addEventListener('click', function(event) {
                const newPassword = document.getElementById('new-password').value;
                const confirmPassword = document.getElementById('confirm-password').value;

                if (newPassword !== confirmPassword) {
                    event.preventDefault(); // Ngăn chặn gửi form
                    alert('Mật khẩu mới và xác nhận mật khẩu không khớp!');
                }
            });

            
        </script>
    </div>

    <!-- Footer -->
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
                        <li><a href="#">Đăng Xuất</a></li>
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
