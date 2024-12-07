<?php
session_start();  // Bắt đầu phiên làm việc của người dùng (session)

// Kết nối cơ sở dữ liệu
$servername = "localhost";  // Địa chỉ máy chủ cơ sở dữ liệu
$username = "root";  // Tên người dùng cơ sở dữ liệu
$password = "";  // Mật khẩu cơ sở dữ liệu (trong trường hợp này là rỗng)
$dbname = "watchshop";  // Tên cơ sở dữ liệu

// Tạo kết nối tới cơ sở dữ liệu MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);  // Nếu kết nối thất bại, dừng thực thi và hiển thị thông báo lỗi
}

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {  // Nếu session không có `user_id`, tức là người dùng chưa đăng nhập
    header("Location: login.php");  // Chuyển hướng người dùng tới trang đăng nhập
    exit;  // Dừng thực thi mã
}

$user_id = $_SESSION['user_id'];  // Lấy `user_id` từ session của người dùng
$message = "";  // Khởi tạo một biến để lưu thông báo cho người dùng

// Kiểm tra xem có phải là yêu cầu POST (gửi dữ liệu từ biểu mẫu)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Lọc và kiểm tra dữ liệu đầu vào từ biểu mẫu
    $mat_khau = $_POST['mat_khau'];  // Mật khẩu hiện tại
    $mat_khau_moi = $_POST['mat_khau_moi'];  // Mật khẩu mới
    $xac_nhan_mat_khau = $_POST['xac_nhan_mat_khau'];  // Mật khẩu xác nhận

    // Kiểm tra mật khẩu mới và xác nhận mật khẩu có khớp không
    if ($mat_khau_moi !== $xac_nhan_mat_khau) {
        $message = "Mật khẩu mới và xác nhận mật khẩu không khớp.";  // Hiển thị thông báo lỗi nếu mật khẩu mới và xác nhận không khớp
    } else {
        // Kiểm tra mật khẩu hiện tại
        $stmt = $conn->prepare("SELECT mat_khau FROM Users WHERE user_id = ?");  // Chuẩn bị câu lệnh SQL để lấy mật khẩu hiện tại của người dùng
        $stmt->bind_param("i", $user_id);  // Liên kết tham số `user_id` với câu lệnh SQL
        $stmt->execute();  // Thực thi câu lệnh SQL
        $stmt->bind_result($current_password);  // Liên kết kết quả với biến `$current_password`
        $stmt->fetch();  // Lấy kết quả đầu tiên từ truy vấn
        $stmt->close();  // Đóng câu lệnh SQL

        // Kiểm tra mật khẩu hiện tại
        if ($mat_khau !== $current_password) {  // Nếu mật khẩu hiện tại không đúng
            $message = "Mật khẩu hiện tại không đúng.";  // Hiển thị thông báo lỗi
        } else {
            // Cập nhật mật khẩu mới (không mã hóa mật khẩu trong ví dụ này, nên khuyến cáo mã hóa mật khẩu trước khi lưu)
            $stmt = $conn->prepare("UPDATE Users SET mat_khau = ? WHERE user_id = ?");  // Chuẩn bị câu lệnh SQL để cập nhật mật khẩu mới
            $stmt->bind_param("si", $mat_khau_moi, $user_id);  // Liên kết tham số `mat_khau_moi` và `user_id` với câu lệnh SQL

            if ($stmt->execute()) {  // Thực thi câu lệnh SQL và kiểm tra kết quả
                $message = "Mật khẩu đã được cập nhật thành công.";  // Nếu thành công, hiển thị thông báo thành công
            } else {
                $message = "Lỗi khi cập nhật mật khẩu. Vui lòng thử lại.";  // Nếu có lỗi, hiển thị thông báo lỗi
            }
            $stmt->close();  // Đóng câu lệnh SQL
        }
    }
}

$conn->close();  // Đóng kết nối cơ sở dữ liệu

// Sử dụng JavaScript để hiển thị thông báo và chuyển hướng người dùng về trang `account.php` sau 2 giây
echo "<script>
    alert('$message');  // Hiển thị thông báo dưới dạng pop-up
    window.location.href = 'account.php';  // Chuyển hướng người dùng đến trang account.php
</script>";
exit;  // Dừng thực thi mã
?>
