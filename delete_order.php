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

// Lấy user_id từ session
$user_id = $_SESSION['user_id'];  

// Kiểm tra xem có tham số order_id trong URL hay không
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Kiểm tra xem đơn hàng này có phải của người dùng hiện tại không
    $sql = "SELECT don_hang_id FROM DonHang WHERE don_hang_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $order_id, $user_id);  // Kiểm tra đơn hàng của người dùng hiện tại
    $stmt->execute();
    $result = $stmt->get_result();

    // Nếu tìm thấy đơn hàng của người dùng, thực hiện xóa
    if ($result->num_rows > 0) {
        // Tiến hành xóa đơn hàng
        $delete_sql = "DELETE FROM DonHang WHERE don_hang_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $order_id);
        if ($delete_stmt->execute()) {
            // Sau khi xóa thành công, chuyển hướng người dùng về trang danh sách đơn hàng
            header("Location: your_orders.php?status=success");  // Điều hướng trở lại trang đơn hàng
            exit;
        } else {
            echo "Có lỗi xảy ra khi xóa đơn hàng.";
        }
        $delete_stmt->close();
    } else {
        echo "Đơn hàng không tồn tại hoặc bạn không có quyền xóa.";
    }

    $stmt->close();
} else {
    echo "Không có mã đơn hàng để xóa.";
}

// Đóng kết nối
$conn->close();
?>
