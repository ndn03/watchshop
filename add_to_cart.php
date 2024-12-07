<?php
session_start();  // Bắt đầu phiên làm việc của người dùng (session)

// Kiểm tra đăng nhập của người dùng
if (!isset($_SESSION['user_id'])) {  // Nếu không có session người dùng, tức là chưa đăng nhập
    header("Location: login.php");  // Chuyển hướng tới trang đăng nhập
    exit;  // Dừng việc thực thi mã sau khi chuyển hướng
}

// Kiểm tra sự tồn tại của ID sản phẩm trong POST request
if (!isset($_POST['san_pham_id'])) {  // Nếu không có ID sản phẩm trong dữ liệu POST
    header("Location: main.php");  // Chuyển hướng về trang chính
    exit;
}

$user_id = $_SESSION['user_id'];  // Lấy ID người dùng từ session
$san_pham_id = $_POST['san_pham_id'];  // Lấy ID sản phẩm từ POST request

// Kết nối tới cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "watchshop";

// Tạo kết nối đến cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);  // Nếu kết nối thất bại, dừng thực thi và hiển thị lỗi
}

// Kiểm tra xem giỏ hàng đã tồn tại cho người dùng chưa
$sql = "SELECT gio_hang_id, tong_tien FROM giohang WHERE user_id = ?";  // Lấy thông tin giỏ hàng của người dùng
$stmt = $conn->prepare($sql);  // Chuẩn bị câu lệnh SQL
$stmt->bind_param("i", $user_id);  // Liên kết tham số user_id với câu lệnh SQL
$stmt->execute();  // Thực thi câu lệnh SQL
$stmt->store_result();  // Lưu kết quả truy vấn

if ($stmt->num_rows == 0) {  // Nếu không có giỏ hàng cho người dùng này
    $stmt->close();  // Đóng kết nối với câu lệnh SQL
    $sql = "INSERT INTO giohang (user_id, tong_tien) VALUES (?, 0)";  // Tạo giỏ hàng mới với tổng tiền ban đầu là 0
    $stmt = $conn->prepare($sql);  // Chuẩn bị câu lệnh SQL
    $stmt->bind_param("i", $user_id);  // Liên kết tham số user_id với câu lệnh SQL
    $stmt->execute();  // Thực thi câu lệnh SQL
    $gio_hang_id = $stmt->insert_id;  // Lấy ID của giỏ hàng vừa tạo
} else {  // Nếu giỏ hàng đã tồn tại
    $stmt->bind_result($gio_hang_id, $current_tong_tien);  // Liên kết kết quả truy vấn với biến
    $stmt->fetch();  // Lấy kết quả vào biến
    $stmt->close();  // Đóng kết nối với câu lệnh SQL
}

// Lấy giá của sản phẩm từ bảng `sanpham`
$sql = "SELECT gia FROM sanpham WHERE san_pham_id = ?";  // Truy vấn giá của sản phẩm
$stmt = $conn->prepare($sql);  // Chuẩn bị câu lệnh SQL
$stmt->bind_param("i", $san_pham_id);  // Liên kết tham số san_pham_id với câu lệnh SQL
$stmt->execute();  // Thực thi câu lệnh SQL
$stmt->bind_result($gia);  // Liên kết kết quả truy vấn với biến gia
$stmt->fetch();  // Lấy giá sản phẩm
$stmt->close();  // Đóng kết nối với câu lệnh SQL

if (is_null($gia)) {  // Nếu sản phẩm không tồn tại (gia = NULL)
    echo "Sản phẩm không tồn tại.";  // Thông báo sản phẩm không tồn tại
    exit;  // Dừng việc thực thi mã
}

// Kiểm tra xem sản phẩm đã có trong chi tiết giỏ hàng chưa
$sql = "SELECT so_luong FROM chitietgiohang WHERE gio_hang_id = ? AND san_pham_id = ?";  // Truy vấn số lượng sản phẩm trong giỏ hàng
$stmt = $conn->prepare($sql);  // Chuẩn bị câu lệnh SQL
$stmt->bind_param("ii", $gio_hang_id, $san_pham_id);  // Liên kết tham số gio_hang_id và san_pham_id với câu lệnh SQL
$stmt->execute();  // Thực thi câu lệnh SQL
$stmt->store_result();  // Lưu kết quả truy vấn

if ($stmt->num_rows > 0) {  // Nếu sản phẩm đã có trong giỏ hàng
    $stmt->bind_result($so_luong);  // Liên kết kết quả truy vấn với biến so_luong
    $stmt->fetch();  // Lấy số lượng sản phẩm
    $so_luong += 1;  // Tăng số lượng sản phẩm lên 1
    $stmt->close();  // Đóng kết nối với câu lệnh SQL

    // Cập nhật số lượng và giá của sản phẩm trong chi tiết giỏ hàng
    $sql = "UPDATE chitietgiohang SET so_luong = ?, gia = ? WHERE gio_hang_id = ? AND san_pham_id = ?";
    $stmt = $conn->prepare($sql);  // Chuẩn bị câu lệnh SQL
    $stmt->bind_param("idii", $so_luong, $gia, $gio_hang_id, $san_pham_id);  // Liên kết tham số với câu lệnh SQL
    $stmt->execute();  // Thực thi câu lệnh SQL
} else {  // Nếu sản phẩm chưa có trong giỏ hàng
    $stmt->close();  // Đóng kết nối với câu lệnh SQL
    $sql = "INSERT INTO chitietgiohang (gio_hang_id, san_pham_id, so_luong, gia) VALUES (?, ?, 1, ?)";  // Thêm sản phẩm vào chi tiết giỏ hàng
    $stmt = $conn->prepare($sql);  // Chuẩn bị câu lệnh SQL
    $stmt->bind_param("iid", $gio_hang_id, $san_pham_id, $gia);  // Liên kết tham số với câu lệnh SQL
    $stmt->execute();  // Thực thi câu lệnh SQL
}

// Cập nhật tổng tiền của giỏ hàng
$new_tong_tien = $current_tong_tien + $gia;  // Cập nhật tổng tiền mới bằng tổng tiền cũ cộng với giá sản phẩm
$sql = "UPDATE giohang SET tong_tien = ? WHERE gio_hang_id = ?";  // Cập nhật tổng tiền giỏ hàng
$stmt = $conn->prepare($sql);  // Chuẩn bị câu lệnh SQL
$stmt->bind_param("di", $new_tong_tien, $gio_hang_id);  // Liên kết tham số với câu lệnh SQL
$stmt->execute();  // Thực thi câu lệnh SQL

$stmt->close();  // Đóng kết nối với câu lệnh SQL
$conn->close();  // Đóng kết nối cơ sở dữ liệu

// Chuyển hướng người dùng đến trang giỏ hàng
header("Location: cart.php");  // Chuyển hướng đến trang giỏ hàng
exit;  // Dừng việc thực thi mã
?>
