<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Luxury Watches</title>
    <link rel="stylesheet" href="CSS/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <!-- Bootstrap CSS (for Carousel) -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <h1 class="logo">Luxury Watches</h1>
            <nav>
                <ul>
                    <li><a href="#">Trang chủ</a></li>
                    <li>
                        <a href="#">Sản phẩm</a>
                        <ul class="dropdown">
                            <li><a href="#1">Sản phẩm nổi bật</a></li>
                            <li><a href="#2">Đồng hồ nam</a></li>
                            <li><a href="#3">Đồng hồ nữ</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#">Tài khoản</a>
                        <ul class="dropdown">
                            <li><a href="cart.php">Giỏ hàng</a></li>
                            <li><a href="account.php">Tài khoản</a></li>
                            <li><a href="logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Liên hệ</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section with Carousel -->
    <section class="hero">
        <div id="demo" class="carousel slide" data-ride="carousel">
            <ul class="carousel-indicators">
                <li data-target="#demo" data-slide-to="0" class="active"></li>
                <li data-target="#demo" data-slide-to="1"></li>
                <li data-target="#demo" data-slide-to="2"></li>
            </ul>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="img/banner7.jpg" alt="Los Angeles" >
                    <div class="carousel-caption">
                 
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="img/banner2.jpg" alt="Chicago" width="400" height="300">
                    <div class="carousel-caption">
                     
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="img/bannez.jpg" alt="New York" width="400" height="300">
                    <div class="carousel-caption">
                       
                    </div>
                </div>
            </div>
            <a class="carousel-control-prev" href="#demo" data-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </a>
            <a class="carousel-control-next" href="#demo" data-slide="next">
                <span class="carousel-control-next-icon"></span>
            </a>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured">
        <div class="container">
            <h2 id="1">Sản phẩm nổi bật</h2>
            <div class="products">
            <?php
// Bước 1: Kết nối đến cơ sở dữ liệu MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "watchshop";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Bước 2: Truy vấn dữ liệu sản phẩm từ MySQL với điều kiện danh_muc_id = 3
$sql = "SELECT san_pham_id, ten, gia, hinh_anh FROM SanPham WHERE danh_muc_id = 3";
$result = $conn->query($sql);

// Bước 3: Hiển thị dữ liệu sản phẩm với form
if ($result->num_rows > 0) {
    echo '<div class="products">';
    while($row = $result->fetch_assoc()) {
        echo '<div class="product">';
        echo '<img src="' . $row["hinh_anh"] . '" alt="' . $row["ten"] . '">';
        echo '<h3>' . $row["ten"] . '</h3>';
        echo '<p>Giá: ' . number_format($row["gia"], 0, ',', '.') . ' VND</p>';
        
        // Bước 4: Tạo form cho nút "Xem chi tiết" và "Thêm vào giỏ hàng"
        echo '<div class="cart">';

        // Nút "Xem chi tiết" dẫn đến trang chi tiết sản phẩm với ID sản phẩm
        echo '<form action="product.php" method="GET" class="cart-form">';
        echo '<input type="hidden" name="id" value="' . $row["san_pham_id"] . '">';
        echo '<button type="submit">Xem chi tiết</button>';
        echo '</form>';
        
        // Nút "Thêm vào giỏ hàng" với form post tới giỏ hàng
       
        echo '<form method="POST" action="add_to_cart.php?id=' . $row['san_pham_id'] . '" class="cart-form">';
        echo '<input type="hidden" name="san_pham_id" value="' . $row["san_pham_id"] . '">';
        echo '<button type="submit" title="Thêm vào giỏ hàng"><i class="fa-solid fa-cart-shopping"></i></button>';
        echo '</form>';
        
        echo '</div>'; // Kết thúc cart
        echo '</div>'; // Kết thúc product
    }
    echo '</div>'; // Kết thúc products
} else {
    echo "Không có sản phẩm nào trong danh mục này!";
}

// Đóng kết nối
$conn->close();
?>



                    
                </div>
                    </div>
                </div>
            </div>
        </div>
        
    </section>
   <!-- Men's Watches Section -->
<section class="featured">
    <div class="container">
        <h2 id="2">Sản phẩm dành cho nam</h2>
        <?php
// Bước 1: Kết nối đến cơ sở dữ liệu MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "watchshop";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Bước 2: Truy vấn dữ liệu sản phẩm từ MySQL với điều kiện danh_muc_id = 1
$sql = "SELECT san_pham_id, ten, gia, hinh_anh FROM SanPham WHERE danh_muc_id = 1";
$result = $conn->query($sql);

// Bước 3: Hiển thị dữ liệu sản phẩm với form
if ($result->num_rows > 0) {
    echo '<div class="products">';
    while($row = $result->fetch_assoc()) {
        echo '<div class="product">';
        echo '<img src="' . $row["hinh_anh"] . '" alt="' . $row["ten"] . '">';
        echo '<h3>' . $row["ten"] . '</h3>';
        echo '<p>Giá: ' . number_format($row["gia"], 0, ',', '.') . ' VND</p>';
        
        // Bước 4: Tạo form cho nút "Xem chi tiết" và "Thêm vào giỏ hàng"
        echo '<div class="cart">';

        // Nút "Xem chi tiết" dẫn đến trang chi tiết sản phẩm với ID sản phẩm
        echo '<form action="product.php" method="GET" class="cart-form">';
        echo '<input type="hidden" name="id" value="' . $row["san_pham_id"] . '">';
        echo '<button type="submit">Xem chi tiết</button>';
        echo '</form>';
        
        // Nút "Thêm vào giỏ hàng" với form post tới giỏ hàng
       
        echo '<form method="POST" action="add_to_cart.php?id=' . $row['san_pham_id'] . '" class="cart-form">';
        echo '<input type="hidden" name="san_pham_id" value="' . $row["san_pham_id"] . '">';
        echo '<button type="submit" title="Thêm vào giỏ hàng"><i class="fa-solid fa-cart-shopping"></i></button>';
        echo '</form>';
        
        echo '</div>'; // Kết thúc cart
        echo '</div>'; // Kết thúc product
    }
    echo '</div>'; // Kết thúc products
} else {
    echo "Không có sản phẩm nào trong danh mục này!";
}

// Đóng kết nối
$conn->close();
?>
        </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Men's Watches Section -->
<section class="featured">
    <div class="container">
        <h2 id="3">Sản phẩm dành cho nữ</h2>
        <div class="products">
        <?php
// Bước 1: Kết nối đến cơ sở dữ liệu MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "watchshop";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Bước 2: Truy vấn dữ liệu sản phẩm từ MySQL với điều kiện danh_muc_id = 2
$sql = "SELECT san_pham_id, ten, gia, hinh_anh FROM SanPham WHERE danh_muc_id = 2";
$result = $conn->query($sql);

// Bước 3: Hiển thị dữ liệu sản phẩm với form
if ($result->num_rows > 0) {
    echo '<div class="products">';
    while($row = $result->fetch_assoc()) {
        echo '<div class="product">';
        echo '<img src="' . $row["hinh_anh"] . '" alt="' . $row["ten"] . '">';
        echo '<h3>' . $row["ten"] . '</h3>';
        echo '<p>Giá: ' . number_format($row["gia"], 0, ',', '.') . ' VND</p>';
        
        // Bước 4: Tạo form cho nút "Xem chi tiết" và "Thêm vào giỏ hàng"
        echo '<div class="cart">';

        // Nút "Xem chi tiết" dẫn đến trang chi tiết sản phẩm với ID sản phẩm
        echo '<form action="product.php" method="GET" class="cart-form">';
        echo '<input type="hidden" name="id" value="' . $row["san_pham_id"] . '">';
        echo '<button type="submit">Xem chi tiết</button>';
        echo '</form>';
        
        // Nút "Thêm vào giỏ hàng" với form post tới giỏ hàng
       
        echo '<form method="POST" action="add_to_cart.php?id=' . $row['san_pham_id'] . '" class="cart-form">';
        echo '<input type="hidden" name="san_pham_id" value="' . $row["san_pham_id"] . '">';
        echo '<button type="submit" title="Thêm vào giỏ hàng"><i class="fa-solid fa-cart-shopping"></i></button>';
        echo '</form>';
        
        echo '</div>'; // Kết thúc cart
        echo '</div>'; // Kết thúc product
    }
    echo '</div>'; // Kết thúc products
} else {
    echo "Không có sản phẩm nào trong danh mục này!";
}

// Đóng kết nối
$conn->close();
?>
            </div>
                </div>
            </div>
           
        </div>
    </div>
</section>


            
    
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
                        <li><a href="cart.php">Giỏ hàng</a></li>
                
                        <li><a href="logout.php">Đăng xuất</a></li>
                        
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Luxury Watches. Tất cả các quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS (for Carousel functionality) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
