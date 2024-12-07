<?php
// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "watchshop");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Truy vấn lấy dữ liệu sản phẩm
$sqlProducts = "
    SELECT sp.san_pham_id, sp.ten, sp.hinh_anh, sp.thuong_hieu, sp.gia, sp.mo_ta, dm.ten_danh_muc AS danh_muc 
    FROM sanpham sp 
    LEFT JOIN DanhMuc dm ON sp.danh_muc_id = dm.danh_muc_id
";
$resultProducts = $conn->query($sqlProducts);

// Truy vấn lấy dữ liệu đơn hàng
$sqlOrders = "
SELECT dh.don_hang_id, dh.name_user, dh.dia_chi, dh.so_dien_thoai, dh.ngay_dat, dh.tong_tien, 
pttt.ten_phuong_thuc AS phuong_thuc, tt.trang_thai_id, tt.ten_trang_thai AS trang_thai,
sp.ten AS ten, sp.hinh_anh AS hinh_anh, sp.gia AS gia
FROM DonHang dh
LEFT JOIN PhuongThucThanhToan pttt ON dh.phuong_thuc_thanh_toan_id = pttt.phuong_thuc_thanh_toan_id
LEFT JOIN ChiTietDonHang ctdh ON dh.don_hang_id = ctdh.don_hang_id
LEFT JOIN TrangThai tt ON dh.trang_thai_id = tt.trang_thai_id
LEFT JOIN sanpham sp ON ctdh.san_pham_id = sp.san_pham_id
";
$resultOrders = $conn->query($sqlOrders);



// Truy vấn lấy danh sách trạng thái
$sqlStatuses = "SELECT trang_thai_id, ten_trang_thai FROM TrangThai";
$resultStatuses = $conn->query($sqlStatuses);

$statuses = [];
if ($resultStatuses->num_rows > 0) {
    while ($status = $resultStatuses->fetch_assoc()) {
        $statuses[] = $status;
    }
}
//vấn danh sách danh mục
$sqlDanhMuc = "SELECT danh_muc_id, ten_danh_muc FROM DanhMuc";
$resultDanhMuc = $conn->query($sqlDanhMuc);

// Mảng lưu danh mục
$danhMucs = [];
if ($resultDanhMuc->num_rows > 0) {
    while ($danhMuc = $resultDanhMuc->fetch_assoc()) {
        $danhMucs[] = $danhMuc;
    }
}

// Kiểm tra nếu $sanPhamId được truyền qua GET hoặc POST
if (isset($_GET['san_pham_id'])) {
    $sanPhamId = intval($_GET['san_pham_id']); // Lấy ID sản phẩm từ URL hoặc form

    // Truy vấn sản phẩm cụ thể
    $sqlProduct = "SELECT * FROM sanpham WHERE san_pham_id = ?";
    $stmt = $conn->prepare($sqlProduct);
    $stmt->bind_param("i", $sanPhamId); // Gán giá trị $sanPhamId
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if ($product) {
        $currentDanhMucId = $product['danh_muc_id']; // Gán danh mục hiện tại của sản phẩm
    } else {
        $currentDanhMucId = null; // Nếu không tìm thấy sản phẩm
    }
} else {
    $currentDanhMucId = null; // Nếu không có ID sản phẩm được truyền
}

// Xử lý xóa sản phẩm khi nhận POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_id"])) {
    $san_pham_id = intval($_POST["delete_id"]); // Lấy ID sản phẩm từ POST và chuyển sang kiểu số nguyên

    // Nếu có bảng liên quan như ChiTietSanPham, bạn cần xóa các bản ghi chi tiết liên quan trước
    // Ví dụ, xóa chi tiết sản phẩm (nếu có)
    // $conn->query("DELETE FROM ChiTietSanPham WHERE san_pham_id = $san_pham_id");

    // Xóa sản phẩm khỏi bảng sanpham
    $sqlDelete = "DELETE FROM sanpham WHERE san_pham_id = ?";
    $stmt = $conn->prepare($sqlDelete);
    $stmt->bind_param("i", $san_pham_id); // Gắn ID sản phẩm vào câu truy vấn

    // Thực thi câu lệnh xóa
    if ($stmt->execute()) {
        $message = "Sản phẩm đã được xóa thành công.";
        
    } else {
        $message = "Lỗi: Không thể xóa sản phẩm.";
    }
}


// Xử lý xóa đơn hàng
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_order_id"])) {
    $orderId = intval($_POST["delete_order_id"]);

    // Xóa chi tiết đơn hàng
    $conn->query("DELETE FROM ChiTietDonHang WHERE don_hang_id = $orderId");

    // Xóa đơn hàng
    if ($conn->query("DELETE FROM DonHang WHERE don_hang_id = $orderId")) {
        $message = "Đơn hàng đã được xóa thành công.";
    } else {
        $message = "Lỗi: Không thể xóa đơn hàng.";
    }
}
// Lấy danh sách sản phẩm
$sql = "SELECT * FROM sanpham";
$result = $conn->query($sql);
// Lấy danh sách đơn hàng
$orderSql = "SELECT * FROM DonHang";
$orderResult = $conn->query($orderSql);

// Xử lý xóa người dùng
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $userId = $_GET['id'];

    if (!empty($userId)) {
        $sqlDeleteUser = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sqlDeleteUser);
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            echo "<script>alert('Xóa người dùng thành công!'); window.location.href = 'admin.php';</script>";
        } else {
            echo "<script>alert('Lỗi khi xóa người dùng: " . $conn->error . "');</script>";
        }
        $stmt->close();
    }
}

?>



<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Admin - Luxury Watches</title>
    <link rel="stylesheet" href="CSS/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
        <a href="main.php" class="logo-link">
                <h1 class="logo">Trang quản trị-ADMIN</h1>
                <style>
                            h1{
                                font-size:30px;
                                color:  #f9f9f9;

                            }
                            </style>
        </div>
    </header>

    <!-- Sidebar -->
    <div class="sidebar"> 
        <ul class="menu">
            <li class="menu-item">
                <a href="#" onclick="showSection('product-info')">
                    <i class="fas fa-home"></i> Thông tin sản phẩm
                </a>
            </li>
            <li class="menu-item">
                <a href="#" onclick="showSection('add-product')">
                    <i class="fas fa-edit"></i> Thêm sản phẩm 
                </a>
            </li>
            <li class="menu-item">
                <a href="#" onclick="showSection('donhang')">
                    <i class="fab fa-first-order"></i> Đơn hàng
                </a>
            </li>
            <li class="menu-item">
                <a href="#" onclick="showSection('user')">
                    <i class="fas fa-user-circle"></i> Tài khoản
                </a>
            </li>
            
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Thông tin sản phẩm -->
        <div id="product-info" class="content-section" style="display: block;">
            <h2>Thông tin sản phẩm</h2>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên sản phẩm</th>
                        <th>Ảnh</th>
                        <th>Thương hiệu</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Mô tả</th>
                        <th>Thao tác</th>
                        <th>Xóa</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultProducts && $resultProducts->num_rows > 0): ?>
                        <?php while ($row = $resultProducts->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['san_pham_id']) ?></td>
                                <td><?= htmlspecialchars($row['ten']) ?></td>
                                <td>
                                    <img src="<?= htmlspecialchars($row['hinh_anh']) ?>" 
                                         alt="<?= htmlspecialchars($row['ten']) ?>" 
                                         width="50">
                                </td>
                                <td><?= htmlspecialchars($row['thuong_hieu']) ?></td>
                                <td><?= htmlspecialchars($row['danh_muc'] ?? 'Không xác định') ?></td>
                                <td><?= number_format($row['gia'], 0, ',', '.') ?> VNĐ</td>
                                <td><?= htmlspecialchars($row['mo_ta']) ?></td>
                                <td>
                                    <a 
                                        href="javascript:void(0);" 
                                        class="edit-btn" 
                                        onclick="openEditPopup(this)" 
                                        data-id="<?= $row['san_pham_id'] ?>" 
                                        data-name="<?= htmlspecialchars($row['ten']) ?>" 
                                        data-image="<?= htmlspecialchars($row['hinh_anh']) ?>" 
                                        data-brand="<?= htmlspecialchars($row['thuong_hieu']) ?>" 
                                        data-category="<?= htmlspecialchars($row['danh_muc']) ?>" 
                                        data-price="<?= htmlspecialchars($row['gia']) ?>" 
                                        data-description="<?= htmlspecialchars($row['mo_ta']) ?>">
                                        Chỉnh sửa
                                    </a>
                                </td>

                                <td>
                        <!-- Nút xóa sản phẩm -->
                        <a href="javascript:void(0);" onclick="deleteProduct(<?= $row['san_pham_id'] ?>)">Xóa</a>
                        <!-- Form ẩn để gửi yêu cầu xóa -->
                        <form id="deleteForm_<?= $row['san_pham_id'] ?>" method="POST" action="" style="display: none;">
                            <input type="hidden" name="delete_id" value="<?= $row['san_pham_id'] ?>">
                        </form>
                    </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Không có sản phẩm nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
      <!-- Popup chỉnh sửa thông tin sản phẩm -->
      <div id="editPopup" class="popup">
    <div class="popup-content">
        <span class="close">&times;</span>
        <h3>Chỉnh sửa sản phẩm</h3>
        <form id="editForm" action="editsanpham.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="san_pham_id" id="productId">

            <label for="productName">Tên sản phẩm:</label>
            <input type="text" id="productName" name="ten" required><br><br>

            <label for="image">Ảnh:</label>
            <input type="file" id="image" name="hinhanh" accept="image/*"><br><br>
            
            <!-- Image preview -->
            <div id="imagePreview"></div><br><br>

            <label for="productBrand">Thương hiệu:</label>
            <input type="text" id="productBrand" name="thuong_hieu" required><br><br>

            <div class="form-group">
                <label for="productCategory">Danh mục:</label>
            <select id="productCategory" name="danh_muc_id" required>
                <?php foreach ($danhMucs as $danhMuc): ?>
                    <option value="<?= $danhMuc['danh_muc_id'] ?>">
                        <?= htmlspecialchars($danhMuc['ten_danh_muc']) ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>
                    </select>
                </div>

            <label for="productPrice">Giá:</label>
            <input type="text" id="productPrice" name="gia" required><br><br>

            <label for="productDescription">Mô tả:</label>
            <textarea id="productDescription" name="mo_ta" required></textarea><br><br>

            <button type="submit">Lưu thay đổi</button>
            
        </form>
    </div>
</div>

        <!-- Thêm sản phẩm Section -->
        <div id="add-product" class="content-section">
            <h2>Thêm sản phẩm, danh mục mới</h2>
            <div class="form-group">
                <button id="addProductBtn" class="btn">Thêm sản phẩm</button>
                <button id="addCategoryBtn" class="btn">Thêm danh mục</button>
            </div>
        </div>
    
        <div id="donhang" class="content-section" style="display:none;">
            <h2>Đơn hàng</h2>
            <p>Danh sách đơn hàng sẽ được hiển thị tại đây.</p>
            <table>
        <thead>
            <tr>
                <th>Mã đơn hàng</th>
                <th>Tên khách hàng</th>
                <th>Địa chỉ</th>
                <th>Số điện thoại</th>
                <th>Ngày đặt</th>
                <th>Tổng tiền</th>
                <th>Phương thức thanh toán</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
                <th>Xóa</th>
            </tr>
        </thead>
        
        <tbody>
            <?php if ($resultOrders->num_rows > 0): ?>
                <?php while ($order = $resultOrders->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['don_hang_id']) ?></td>
                        <td><?= htmlspecialchars($order['name_user']) ?></td>
                        <td><?= htmlspecialchars($order['dia_chi']) ?></td>
                        <td><?= htmlspecialchars($order['so_dien_thoai']) ?></td>
                        <td><?= htmlspecialchars($order['ngay_dat']) ?></td>
                        <td><?= number_format($order['tong_tien'], 0, ',', '.') ?> VNĐ</td>
                        <td><?= htmlspecialchars($order['phuong_thuc']) ?></td>
                        <td>
                            <form method="POST" action="update_status.php">
                                <input type="hidden" name="don_hang_id" value="<?= $order['don_hang_id'] ?>">
                                <select name="trang_thai_id" onchange="this.form.submit()">
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?= $status['trang_thai_id'] ?>" 
                                            <?= $status['trang_thai_id'] == $order['trang_thai_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($status['ten_trang_thai']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td>
                        <a href="#" class="view-details" 
                          data-product-name="<?= htmlspecialchars($order['ten']) ?>"
                          data-product-image="<?= htmlspecialchars($order['hinh_anh']) ?>"
                          data-product-price="<?= number_format($order['gia'], 0, ',', '.') ?> VNĐ">
                             Xem chi tiết
                </a>
                </td>   
                <td>
    <!-- Liên kết để kích hoạt form xóa -->
    <a href="javascript:void(0);" 
       onclick="deleteOrder(<?= $order['don_hang_id'] ?>)">Xóa</a>

    <!-- Form ẩn để xử lý yêu cầu xóa -->
    <form id="deleteForm_<?= $order['don_hang_id'] ?>" method="POST" action="" style="display: none;">
        <input type="hidden" name="delete_order_id" value="<?= $order['don_hang_id'] ?>">
    </form>
</td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">Không có đơn hàng nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
        </div>
        <!-- Popup Xem Chi Tiết Sản Phẩm -->
<!-- Popup Xem Chi Tiết Đơn Hàng -->
<div id="orderDetailsPopup" class="popup">
    <div class="popup-content">
        <span class="close" id="closeOrderDetailsPopup">&times;</span>
        <h3>Chi tiết đơn hàng</h3>
        <div id="orderDetails">
            <table>
                <tr>
                    <td><strong>Tên sản phẩm:</strong></td>
                    <td><strong>Ảnh sản phẩm:</strong></td>
                    <td><strong>Giá sản phẩm:</strong></td>
                   
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
        <!-- Tài khoản Section -->
<div id="user" class="content-section" style="display:none;">
    <h2>Tài khoản</h2>
    <table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Tài khoản</th>
            <th>Mật khẩu</th>
            <th>Email</th>
            <th>Vai trò</th>
            <th>Ngày tạo</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sqlUsers = "SELECT user_id, user_name, mat_khau, email, role, ngay_tao FROM users";
        $resultUsers = $conn->query($sqlUsers);

        if ($resultUsers->num_rows > 0):
            while ($user = $resultUsers->fetch_assoc()):
        ?>
                <tr>
                    <td><?= $user['user_id'] ?></td>
                    <td><?= htmlspecialchars($user['user_name']) ?></td>
                    <td>
                        <span class="password-value" style="display:none;"><?= htmlspecialchars($user['mat_khau']) ?></span>
                        <span class="toggle-password" onclick="togglePasswordVisibility(this)">*****👁️</span>
                    </td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <form action="update_role.php" method="POST">
                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                            <select name="role" onchange="this.form.submit()">
                                <option value="0" <?= $user['role'] == 0 ? 'selected' : '' ?>>Người dùng</option>
                                <option value="1" <?= $user['role'] == 1 ? 'selected' : '' ?>>Quản trị viên</option>
                            </select>
                        </form>
                    </td>
                    <td><?= htmlspecialchars($user['ngay_tao']) ?></td>
                    <td>
                        <a href="admin.php?action=delete&id=<?= $user['user_id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">Xóa</a>
                    </td>
                </tr>
        <?php
            endwhile;
        else:
        ?>
            <tr>
                <td colspan="7">Không có người dùng nào.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</div>
    </div>
    </div>
    <div id="addProductPopup" class="popup">
        <div class="popup-content">
            <span class="close" id="closeProductPopup">&times;</span>
            <h3>Thêm sản phẩm mới</h3>
            <form action="addsanpham.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product-name">Tên sản phẩm</label>
                    <input type="text" id="product-name" name="ten" required>
                </div>
                <div class="form-group">
                    <label for="product-name">Thương hiệu</label>
                    <input type="text" id="product-name" name="thuonghieu" required>
                </div>
                <div class="form-group">
                <label for="productCategory">Danh mục:</label>
            <select id="productCategory" name="danh_muc_id" required>
                <?php foreach ($danhMucs as $danhMuc): ?>
                    <option value="<?= $danhMuc['danh_muc_id'] ?>">
                        <?= htmlspecialchars($danhMuc['ten_danh_muc']) ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Giá</label>
                    <input type="text" id="price" name="price" required>
                </div>
                <div class="form-group">
                    <label for="description">Mô tả sản phẩm</label>
                    <textarea id="description" name="description" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Ảnh sản phẩm</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <button type="submit">Thêm sản phẩm</button>
                </div>
            </form>
        </div>
       
    </div>
    </div>

    <!-- Popup Thêm danh mục -->
    <div id="addCategoryPopup" class="popup">
        <div class="popup-content">
            <span class="close" id="closeCategoryPopup">&times;</span>
            <h3>Thêm danh mục mới</h3>
            <form action="adddanhmuc.php" method="POST">
        <div class="form-group">
            <label for="category-name">Tên danh mục</label>
            <input type="text" id="category-name" name="category-name" required>
        </div>
        <div class="form-group">
            <button type="submit" name="add_category">Thêm danh mục</button>
        </div>
    </form>

    <h3>Danh Sách Danh Mục</h3>
    <!-- Hiển thị các danh mục đã có -->
    <?php
// Hiển thị thông báo nếu có
if (isset($_SESSION['success_message'])) {
    echo "<p style='color: green;'>{$_SESSION['success_message']}</p>";
    unset($_SESSION['success_message']);
} elseif (isset($_SESSION['error_message'])) {
    echo "<p style='color: red;'>{$_SESSION['error_message']}</p>";
    unset($_SESSION['error_message']);
}
?>
   <table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên Danh Mục</th>
            <th>Chỉnh sửa</th>
            <th>Xóa</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($danhMucs as $danhMuc): ?>
            <tr>
                <td><?= $danhMuc['danh_muc_id'] ?></td>
                <td><?= htmlspecialchars($danhMuc['ten_danh_muc']) ?></td>
                <td>
                    <!-- Form chỉnh sửa danh mục -->
                    <form action="adddanhmuc.php" method="POST">
                        <input type="hidden" name="danh_muc_id" value="<?= $danhMuc['danh_muc_id'] ?>">
                        <input type="text" name="category-name" value="<?= htmlspecialchars($danhMuc['ten_danh_muc']) ?>" required>
                        <button type="submit" name="edit_category">Chỉnh sửa</button>
                    </form>
                </td>
                <td>
                    <!-- Form xóa danh mục -->
                    <form action="adddanhmuc.php" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa danh mục này?');">
                        <input type="hidden" name="danh_muc_id" value="<?= $danhMuc['danh_muc_id'] ?>">
                        <button type="submit" name="delete_category">Xóa</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

        </div>
    </div>

  
    <!-- Footer -->
    <footer>
        <p>© 2024 Luxury Watches. Tất cả quyền lợi được bảo lưu.</p>
    </footer>


    <script>
    // Hàm để hiển thị section tương ứng
    function showSection(sectionId) {
        const sections = document.querySelectorAll('.content-section');
        sections.forEach(section => section.style.display = 'none');  // Ẩn tất cả các section
        const selectedSection = document.getElementById(sectionId);  // Lấy section được chọn
        if (selectedSection) {
            selectedSection.style.display = 'block';  // Hiển thị section được chọn
        }
    }


    // Hàm để bật/tắt hiển thị mật khẩu
    function togglePasswordVisibility(iconElement) {
        var passwordValue = iconElement.previousElementSibling; // Thẻ <span> chứa mật khẩu
        if (passwordValue.style.display === "none") {
            passwordValue.style.display = "inline";  // Hiển thị mật khẩu
            iconElement.textContent = "👁️";       // Thay đổi biểu tượng thành mắt mở
        } else {
            passwordValue.style.display = "none";  // Ẩn mật khẩu
            iconElement.textContent = "*****👁️";  // Thay đổi biểu tượng thành mắt đóng
        }
    }

// Mở popup chỉnh sửa thông tin sản phẩm
function openEditPopup(button) {
    const popup = document.getElementById('editPopup');
    
    // Lấy dữ liệu từ thuộc tính data
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    const image = button.getAttribute('data-image');
    const brand = button.getAttribute('data-brand');
    const category = button.getAttribute('data-category');
    const price = button.getAttribute('data-price');
    const description = button.getAttribute('data-description');

    // Điền dữ liệu vào các trường trong form
    document.getElementById('productId').value = id;
    document.getElementById('productName').value = name;
    document.getElementById('productBrand').value = brand;
    document.getElementById('productPrice').value = price;
    document.getElementById('productDescription').value = description;

    // Chọn đúng danh mục trong dropdown
    const categorySelect = document.getElementById('productCategory');
    categorySelect.value = category;  // Gán giá trị danh mục đã chọn vào dropdown

    // Hiển thị ảnh sản phẩm nếu có
    

    // Hiển thị popup
    popup.style.display = 'block';
}
// Đóng popup khi nhấn vào nút 'X'
const closePopup = document.querySelector('.close');
closePopup.addEventListener('click', function() {
    document.getElementById('editPopup').style.display = 'none';
});

// Đóng popup khi nhấn ngoài vùng popup
window.addEventListener('click', function(event) {
    const popup = document.getElementById('editPopup');
    if (event.target === popup) {
        popup.style.display = 'none';
    }
});


    // Chức năng hiển thị popup "Thêm sản phẩm"
    const addProductBtn = document.getElementById('addProductBtn');
    const addProductPopup = document.getElementById('addProductPopup');
    const closeProductPopup = document.getElementById('closeProductPopup');
    addProductBtn.addEventListener('click', () => {
        addProductPopup.style.display = 'block';  // Mở popup "Thêm sản phẩm"
    });
    closeProductPopup.addEventListener('click', () => {
        addProductPopup.style.display = 'none';  // Đóng popup "Thêm sản phẩm"
    });

    // Chức năng hiển thị popup "Thêm danh mục"
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    const addCategoryPopup = document.getElementById('addCategoryPopup');
    const closeCategoryPopup = document.getElementById('closeCategoryPopup');
    addCategoryBtn.addEventListener('click', () => {
        addCategoryPopup.style.display = 'block';  // Mở popup "Thêm danh mục"
    });
    closeCategoryPopup.addEventListener('click', () => {
        addCategoryPopup.style.display = 'none';  // Đóng popup "Thêm danh mục"
    });

    // Đóng các popup khi nhấn ngoài vùng popup
    window.addEventListener('click', (event) => {
        if (event.target === addProductPopup) {
            addProductPopup.style.display = 'none';  // Đóng popup "Thêm sản phẩm"
        }
        if (event.target === addCategoryPopup) {
            addCategoryPopup.style.display = 'none';  // Đóng popup "Thêm danh mục"
        }
    });

    // Popup cho nút 'Xem chi tiết' (hiển thị chi tiết sản phẩm)
    const viewDetailButtons = document.querySelectorAll('.view-details');
    const orderDetailsPopup = document.getElementById('orderDetailsPopup');
    const closeOrderDetailsPopup = document.getElementById('closeOrderDetailsPopup');

    // Lặp qua tất cả các nút 'Xem chi tiết' và thêm sự kiện click
    viewDetailButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();  // Ngừng hành động mặc định

            // Lấy thông tin từ thuộc tính 'data-*' của nút nhấn
            const productName = this.getAttribute('data-product-name');
            const productImage = this.getAttribute('data-product-image');
            const productPrice = this.getAttribute('data-product-price');

            // Cập nhật thông tin vào popup chi tiết sản phẩm
            document.getElementById('productNameDetail').textContent = productName;
            document.getElementById('productImageDetail').src = productImage;
            document.getElementById('productPriceDetail').textContent = productPrice;

            orderDetailsPopup.style.display = 'block';  // Hiển thị popup chi tiết
        });
    });

    // Đóng popup chi tiết sản phẩm khi nhấn vào nút đóng (X)
    closeOrderDetailsPopup.addEventListener('click', function() {
        orderDetailsPopup.style.display = 'none';  // Đóng popup
    });

    // Đóng popup chi tiết khi nhấn ngoài vùng popup
    window.addEventListener('click', function(event) {
        if (event.target === orderDetailsPopup) {
            orderDetailsPopup.style.display = 'none';  // Đóng popup chi tiết khi click ngoài
        }
    });

    // Hàm JavaScript xử lý xóa sản phẩm
    function deleteProduct(productId) {
            // Hiển thị hộp thoại xác nhận
            if (confirm("Bạn có chắc chắn muốn xóa sản phẩm này?")) {
                // Nếu người dùng xác nhận, gửi form tương ứng với ID sản phẩm
                document.getElementById('deleteForm_' + productId).submit();
            }
        }

        function deleteOrder(orderId) {
        // Xác nhận xóa
        if (confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')) {
            // Nếu người dùng đồng ý xóa, form sẽ được submit
            document.getElementById('deleteForm_' + orderId).submit();
        }
    }
    
</script>

</body>
</html>
