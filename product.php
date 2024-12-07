<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Sản Phẩm</title>
    <link rel="stylesheet" href="CSS/product.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>

    <!-- Header -->
    <header>
        <div class="container">
            <h1 class="logo">Luxury Watches</h1>
            <nav>
                <ul>
                    <li><a href="Main.php">Trang chủ</a></li>
                    <li>
                        <a href="#">Tài khoản</a>
                        <ul class="dropdown">
                            <li><a href="cart.php">Giỏ hàng</a></li>
                            <li><a href="account.php">Thông tin đơn hàng</a></li>
                            <li><a href="main2.html">Đăng xuất</a></li>
                        </ul>
                    </li>
                   
                </ul>
            </nav>
        </div>
    </header>
   <! <?php
    // Bước 1: Kết nối đến cơ sở dữ liệu MySQL
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "watchshop";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    
    // Bước 2: Lấy sản phẩm ID từ URL
    if (isset($_GET['id'])) {
        $san_pham_id = $_GET['id'];
    
        // Bước 3: Truy vấn sản phẩm từ cơ sở dữ liệu
        $sql = "SELECT * FROM SanPham WHERE san_pham_id = $san_pham_id";
        $result = $conn->query($sql);   
    
        // Bước 4: Kiểm tra và hiển thị thông tin sản phẩm
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Bước 5: Hiển thị sản phẩm trong HTML
            ?> -->
            <h1>THÔNG TIN SẢN PHẨM</h1>
            <div class="product-info-container">
            <div class="product-image">
                <!-- Hiển thị hình ảnh sản phẩm -->
                <img src="<?php echo htmlspecialchars($row['hinh_anh']); ?>" 
                     alt="<?php echo htmlspecialchars($row['ten']); ?>" 
                     onerror="this.src='img/default.jpg';">
            </div>
    
                <div class="product-details">
                    <!-- Hiển thị tên sản phẩm -->
                    <h2><?php echo $row['ten']; ?></h2>
    
                    <!-- Hiển thị giá sản phẩm -->
                    <p class="product-price"><?php echo number_format($row['gia'], 0, ',', '.'); ?> VNĐ</p>
    
                    <!-- Hiển thị mô tả sản phẩm -->
                    <div class="product-specs">
                        <h3>Đặc điểm nổi bật:</h3>
                        <ul>
                            <?php
                            // Sử dụng mô tả từ cột "mo_ta" trong cơ sở dữ liệu
                            $specs = explode("\n", $row['mo_ta']); // Chia mô tả theo dòng
                            foreach ($specs as $spec) {
                                echo "<li>" . htmlspecialchars($spec) . "</li>";
                            }
                            ?>
                        </ul>
                    </div>
    
                    <!-- Nút "Thêm vào giỏ hàng" -->
                    <form method="POST" action="add_to_cart.php">
                        <input type="hidden" name="san_pham_id" value="<?php echo $row['san_pham_id']; ?>">
                        <input type="hidden" name="ten" value="<?php echo $row['ten']; ?>">
                        <input type="hidden" name="gia" value="<?php echo $row['gia']; ?>">
                        <button type="submit" class="add-to-cart-btn">Thêm vào giỏ hàng</button>
                    </form>
                </div>
            </div>
            <?php
        } else {
            echo "Không tìm thấy sản phẩm!";
        }
    } else {
        echo "Không có sản phẩm được chọn!";
    }
    
    // Đóng kết nối
    $conn->close();
    ?>
    
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
                        <li><a href="logout.php.php">Đăng xuất</a></li>
                        
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
