<?php
session_start();

// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "watchshop";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name_user = $_POST['name_user'];
$dia_chi = $_POST['dia_chi'];
$dien_thoai = $_POST['dien_thoai'];
$payment_method = $_POST['payment_method'];

// Tính tổng tiền từ giỏ hàng
$sql = "SELECT SUM(GH.so_luong * SP.gia) AS tong_tien 
        FROM chitietgiohang GH 
        INNER JOIN sanpham SP ON GH.san_pham_id = SP.san_pham_id 
        WHERE GH.gio_hang_id = (SELECT gio_hang_id FROM giohang WHERE user_id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$tong_tien = $row['tong_tien'] ?? 0; // Đặt giá trị mặc định là 0 nếu không có kết quả
$stmt->close();

// Kiểm tra nếu giỏ hàng trống
if ($tong_tien == 0) {
    echo "Giỏ hàng của bạn đang trống. Không thể đặt hàng.";
    exit;
}

// Thiết lập trạng thái đơn hàng mặc định
$trang_thai_id = 1; // Mã trạng thái cho "Đang xử lý"

// Thêm đơn hàng vào bảng donhang
$sql = "INSERT INTO donhang (user_id, name_user, tong_tien, phuong_thuc_thanh_toan_id, dia_chi, so_dien_thoai, trang_thai_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isdissi", $user_id, $name_user, $tong_tien, $payment_method, $dia_chi, $dien_thoai, $trang_thai_id);

if ($stmt->execute()) {
    $don_hang_id = $stmt->insert_id;

    // Lấy chi tiết sản phẩm từ giỏ hàng và thêm vào bảng chitietdonhang
    $sql = "SELECT GH.san_pham_id, GH.so_luong, SP.gia 
            FROM chitietgiohang GH 
            INNER JOIN sanpham SP ON GH.san_pham_id = SP.san_pham_id 
            WHERE GH.gio_hang_id = (SELECT gio_hang_id FROM giohang WHERE user_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $san_pham_id = $row['san_pham_id'];
        $so_luong = $row['so_luong'];
        $gia_tai_thoi_diem = $row['gia'];

        // Thêm chi tiết đơn hàng
        $sql_chitiet = "INSERT INTO chitietdonhang (don_hang_id, san_pham_id, so_luong, gia_tai_thoi_diem) 
                        VALUES (?, ?, ?, ?)";
        $stmt_chitiet = $conn->prepare($sql_chitiet);
        $stmt_chitiet->bind_param("iiid", $don_hang_id, $san_pham_id, $so_luong, $gia_tai_thoi_diem);
        $stmt_chitiet->execute();
        $stmt_chitiet->close();
    }
    
    // Xóa giỏ hàng của người dùng sau khi đặt hàng thành công
    $sql = "DELETE FROM chitietgiohang WHERE gio_hang_id = (SELECT gio_hang_id FROM giohang WHERE user_id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Chuyển hướng đến trang order.php với don_hang_id
    header("Location: order.php?don_hang_id=" . $don_hang_id);
    exit;
} else {
    echo "Lỗi: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
