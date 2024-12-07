<?php
// Kết nối cơ sở dữ liệu

$servername = "localhost";
$username = "root";
$password = ""; // Thay bằng mật khẩu của bạn
$dbname = "watchshop"; // Tên cơ sở dữ liệu

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $san_pham_id = $_POST['san_pham_id'];
    $ten = $_POST['ten'];
    $thuonghieu = $_POST['thuonghieu'];
    $danhmucid = $_POST['danhmucid'];
    $gia = $_POST['gia'];
    $mota = $_POST['mota'];
    
    // Xử lý ảnh nếu có
    $hinh_anh = $_FILES['hinhanh']['name'];
    if ($hinh_anh) {
        $upload_dir = 'uploads/';
        $upload_file = $upload_dir . basename($hinh_anh);
        move_uploaded_file($_FILES['hinhanh']['tmp_name'], $upload_file);
    } else {
        // Nếu không có ảnh mới, giữ nguyên ảnh cũ
        $sql = "SELECT hinh_anh FROM sanpham WHERE san_pham_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $san_pham_id);
        $stmt->execute();
        $stmt->bind_result($old_image);
        $stmt->fetch();
        $hinh_anh = $old_image;
    }

    // Cập nhật thông tin sản phẩm
    $sql = "UPDATE sanpham 
            SET ten = ?, thuong_hieu = ?, danh_muc_id = ?, gia = ?, mo_ta = ?, hinh_anh = ? 
            WHERE san_pham_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiidssi", $ten, $thuonghieu, $danhmucid, $gia, $mota, $hinh_anh, $san_pham_id);
    if ($stmt->execute()) {
        header("Location: admin.php");  // Điều hướng lại trang admin sau khi cập nhật
    } else {
        echo "Cập nhật sản phẩm thất bại!";
    }
}
?>
