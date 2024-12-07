<?php
// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "watchshop");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

session_start(); // Đảm bảo bắt đầu session để hiển thị thông báo

// Xử lý bảo mật session
session_regenerate_id(true);

// Lấy danh mục sản phẩm để hiển thị trong form
$danhMucs = [];
$sql = "SELECT * FROM danh_muc";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $danhMucs[] = $row;
    }
}

// Kiểm tra nếu có ID sản phẩm được truyền qua URL
if (isset($_GET['san_pham_id'])) {
    $sanPhamId = (int)$_GET['san_pham_id']; // Chuyển đổi ID sang kiểu số nguyên

    // Lấy thông tin sản phẩm từ cơ sở dữ liệu
    $sql = "SELECT * FROM sanpham WHERE san_pham_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $sanPhamId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc(); // Lấy thông tin sản phẩm

    // Kiểm tra xem sản phẩm có tồn tại không
    if (!$product) {
        echo "Sản phẩm không tồn tại.";
        exit;
    }
}

// Xử lý dữ liệu khi form được gửi đi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Làm sạch và kiểm tra dữ liệu đầu vào
    $san_pham_id = (int)$_POST['san_pham_id'];
    $ten = isset($_POST['ten']) ? filter_var($_POST['ten'], FILTER_SANITIZE_STRING) : '';
    $thuong_hieu = isset($_POST['thuong_hieu']) ? filter_var($_POST['thuong_hieu'], FILTER_SANITIZE_STRING) : '';
    // Giữ nguyên danh_muc_id của sản phẩm hiện tại nếu không có thay đổi
    $danh_muc_id = isset($_POST['danh_muc_id']) ? (int)$_POST['danh_muc_id'] : $product['danh_muc_id'];
    $gia = isset($_POST['gia']) ? filter_var($_POST['gia'], FILTER_VALIDATE_FLOAT) : 0;
    $mo_ta = isset($_POST['mo_ta']) ? filter_var($_POST['mo_ta'], FILTER_SANITIZE_STRING) : '';

    // Kiểm tra nếu có ảnh mới được tải lên
    if (isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] === UPLOAD_ERR_OK) {
        // Nếu có ảnh mới, tải lên và lưu đường dẫn
        $fileTmpPath = $_FILES['hinhanh']['tmp_name'];
        $fileName = $_FILES['hinhanh']['name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        // Kiểm tra loại file
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            die("Chỉ cho phép tải lên ảnh có định dạng jpg, jpeg, png, gif.");
        }

        // Kiểm tra kích thước file (giới hạn 5MB)
        if ($_FILES['hinhanh']['size'] > 5000000) {
            die("File quá lớn. Vui lòng tải lên ảnh nhỏ hơn 5MB.");
        }
 // Đường dẫn lưu ảnh (thêm tiền tố unique ID để tránh trùng tên)
 // $filePath = 'img/' . uniqid() . '.' . $fileExtension;

        $filePath = 'img/' . $fileName; // Sử dụng tên gốc của file
        move_uploaded_file($fileTmpPath, $filePath);
    } else {
        // Nếu không có ảnh mới, giữ nguyên ảnh cũ
        $filePath = $product['hinh_anh'];
    }

    // Cập nhật thông tin sản phẩm vào cơ sở dữ liệu
    $sql = "UPDATE sanpham SET ten = ?, thuong_hieu = ?, danh_muc_id = ?, gia = ?, mo_ta = ?, hinh_anh = ? WHERE san_pham_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Lỗi chuẩn bị câu lệnh: " . $conn->error);
        die("Có lỗi xảy ra. Vui lòng thử lại sau.");
    }

    $stmt->bind_param("sssdssi", $ten, $thuong_hieu, $danh_muc_id, $gia, $mo_ta, $filePath, $san_pham_id);

    if ($stmt->execute()) {
        // Thêm thông báo thành công vào session
        $_SESSION['success_message'] = "Cập nhật sản phẩm thành công!";
    } else {
        // Thêm thông báo lỗi vào session
        $_SESSION['error_message'] = "Cập nhật sản phẩm thất bại. Vui lòng thử lại.";
    }

    header("Location: admin.php"); // Chuyển hướng về trang quản lý sản phẩm
    exit;
}
?>
