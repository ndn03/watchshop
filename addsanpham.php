<?php
// Kết nối cơ sở dữ liệu
$conn = new mysqli("localhost", "root", "", "watchshop");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

session_start(); // Bắt đầu session để hiển thị thông báo

// Lấy danh mục sản phẩm để hiển thị trong form
$danhMucs = [];
$sql = "SELECT * FROM danh_muc";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $danhMucs[] = $row;
    }
}

// Xử lý khi form được gửi đi (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Làm sạch và kiểm tra dữ liệu đầu vào
    $ten = isset($_POST['ten']) ? filter_var($_POST['ten'], FILTER_SANITIZE_STRING) : '';
    $thuong_hieu = isset($_POST['thuonghieu']) ? filter_var($_POST['thuonghieu'], FILTER_SANITIZE_STRING) : '';
    $danh_muc_id = isset($_POST['danh_muc_id']) ? (int)$_POST['danh_muc_id'] : 0;
    $gia = isset($_POST['price']) ? filter_var($_POST['price'], FILTER_VALIDATE_FLOAT) : 0;
    $mo_ta = isset($_POST['description']) ? filter_var($_POST['description'], FILTER_SANITIZE_STRING) : '';

    // Kiểm tra nếu có ảnh mới được tải lên
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Nếu có ảnh mới, tải lên và lưu đường dẫn
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        // Kiểm tra loại file
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            die("Chỉ cho phép tải lên ảnh có định dạng jpg, jpeg, png, gif.");
        }

        // Kiểm tra kích thước file (giới hạn 5MB)
        if ($_FILES['image']['size'] > 5000000) {
            die("File quá lớn. Vui lòng tải lên ảnh nhỏ hơn 5MB.");
        }

   
        $filePath = 'img/' . $fileName; // Sử dụng tên gốc của file

        move_uploaded_file($fileTmpPath, $filePath);
    } else {
        // Nếu không có ảnh mới, sử dụng giá trị null (không có ảnh)
        $filePath = null;
    }

    // Thêm thông tin sản phẩm vào cơ sở dữ liệu
    $sql = "INSERT INTO sanpham (ten, thuong_hieu, danh_muc_id, gia, mo_ta, hinh_anh) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Lỗi chuẩn bị câu lệnh: " . $conn->error);
    }

    $stmt->bind_param("sssdss", $ten, $thuong_hieu, $danh_muc_id, $gia, $mo_ta, $filePath);

    if ($stmt->execute()) {
        // Thêm thông báo thành công vào session
        $_SESSION['success_message'] = "Thêm sản phẩm thành công!";
    } else {
        // Thêm thông báo lỗi vào session
        $_SESSION['error_message'] = "Thêm sản phẩm thất bại. Vui lòng thử lại.";
    }

    // Chuyển hướng về trang quản lý sản phẩm (admin.php) sau khi thêm thành công
    header("Location: admin.php");
    exit;
}
?>