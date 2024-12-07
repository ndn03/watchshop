<?php
// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "watchshop");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý cập nhật vai trò
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
    $userId = $_POST['user_id'];
    $role = $_POST['role'];

    // Kiểm tra đầu vào
    if (!empty($userId)) {
        $sqlUpdateRole = "UPDATE users SET role = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sqlUpdateRole);
        $stmt->bind_param("ii", $role, $userId);

        if ($stmt->execute()) {
            echo "<script>alert('Cập nhật vai trò thành công!');</script>";
        } else {
            echo "<script>alert('Lỗi khi cập nhật vai trò: " . $conn->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Dữ liệu không hợp lệ.');</script>";
    }
}

// Chuyển hướng nếu không có lỗi
header("Location: admin.php");
exit();
?>
