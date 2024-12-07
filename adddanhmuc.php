<?php
// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "watchshop");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

session_start(); // Bắt đầu session để hiển thị thông báo

// Xử lý khi form thêm danh mục mới được gửi đi (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        // Làm sạch và kiểm tra dữ liệu đầu vào
        $ten_danh_muc = isset($_POST['category-name']) ? filter_var($_POST['category-name'], FILTER_SANITIZE_STRING) : '';

        // Kiểm tra nếu tên danh mục không rỗng
        if (!empty($ten_danh_muc)) {
            // Thêm danh mục vào cơ sở dữ liệu
            $sql = "INSERT INTO danhmuc (ten_danh_muc) VALUES (?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                die("Lỗi chuẩn bị câu lệnh: " . $conn->error);
            }

            $stmt->bind_param("s", $ten_danh_muc);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Thêm danh mục thành công!";
            } else {
                $_SESSION['error_message'] = "Thêm danh mục thất bại. Vui lòng thử lại.";
            }
        } else {
            $_SESSION['error_message'] = "Tên danh mục không được để trống.";
        }
    }

    // Xử lý chỉnh sửa danh mục
    if (isset($_POST['edit_category'])) {
        $danh_muc_id = $_POST['danh_muc_id'];
        $ten_danh_muc = $_POST['category-name'];

        if (!empty($ten_danh_muc)) {
            $sql = "UPDATE danhmuc SET ten_danh_muc = ? WHERE danh_muc_id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("si", $ten_danh_muc, $danh_muc_id);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Chỉnh sửa danh mục thành công!";
                } else {
                    $_SESSION['error_message'] = "Chỉnh sửa danh mục thất bại. Vui lòng thử lại.";
                }
            }
        } else {
            $_SESSION['error_message'] = "Tên danh mục không được để trống.";
        }
    }

    // Xử lý xóa danh mục
    if (isset($_POST['delete_category'])) {
        $danh_muc_id = $_POST['danh_muc_id'];

        // Xóa danh mục khỏi cơ sở dữ liệu
        $sql = "DELETE FROM danhmuc WHERE danh_muc_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $danh_muc_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Xóa danh mục thành công!";
            } else {
                $_SESSION['error_message'] = "Xóa danh mục thất bại. Vui lòng thử lại.";
            }
        }
    }

    // Chuyển hướng lại trang sau khi xử lý
    header("Location: admin.php"); // hoặc tên trang của bạn
    exit;
}

// Lấy tất cả các danh mục hiện có từ cơ sở dữ liệu
$sql = "SELECT * FROM danhmuc";
$result = $conn->query($sql);

if ($result) {
    $danhMucs = [];
    while ($row = $result->fetch_assoc()) {
        $danhMucs[] = $row;
    }
}
?>
