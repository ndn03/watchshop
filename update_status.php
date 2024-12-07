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

// Kiểm tra dữ liệu từ form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['don_hang_id'], $_POST['trang_thai_id'])) {
    $donHangId = intval($_POST['don_hang_id']);
    $trangThaiId = intval($_POST['trang_thai_id']);

    // Cập nhật trạng thái đơn hàng
    $stmt = $conn->prepare("UPDATE DonHang SET trang_thai_id = ? WHERE don_hang_id = ?");
    $stmt->bind_param("ii", $trangThaiId, $donHangId);

    if ($stmt->execute()) {
        echo "Cập nhật trạng thái thành công.";
    } else {
        echo "Lỗi: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Dữ liệu không hợp lệ.";
}

$conn->close();

// Quay lại trang danh sách đơn hàng
header("Location: admin.php");
exit;
?>
