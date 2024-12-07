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
$san_pham_id = $_POST['san_pham_id']; // Lấy ID sản phẩm cần giảm số lượng

// Lấy giỏ hàng của người dùng
$sql = "SELECT gio_hang_id FROM giohang WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Không tìm thấy giỏ hàng cho user_id này.");
}
$row = $result->fetch_assoc();
$gio_hang_id = $row['gio_hang_id'];
$stmt->close();

// Kiểm tra số lượng sản phẩm trong giỏ hàng
$sql = "SELECT so_luong FROM chitietgiohang WHERE gio_hang_id = ? AND san_pham_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $gio_hang_id, $san_pham_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $so_luong = $row['so_luong'];

    if ($so_luong > 1) {
        // Giảm số lượng sản phẩm đi 1
        $sql = "UPDATE chitietgiohang SET so_luong = so_luong - 1 WHERE gio_hang_id = ? AND san_pham_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $gio_hang_id, $san_pham_id);
        $stmt->execute();
    } else {
        // Nếu số lượng là 1, xóa sản phẩm khỏi giỏ hàng
        $sql = "DELETE FROM chitietgiohang WHERE gio_hang_id = ? AND san_pham_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $gio_hang_id, $san_pham_id);
        $stmt->execute();
    }
}

$stmt->close();
$conn->close();

// Quay lại trang giỏ hàng
header("Location: cart.php");
exit();
?>
