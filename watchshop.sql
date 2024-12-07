CREATE DATABASE watchshop;

USE watchshop;

CREATE TABLE DanhMuc (
    danh_muc_id INT AUTO_INCREMENT PRIMARY KEY,
    ten_danh_muc VARCHAR(100) NOT NULL
);

CREATE TABLE PhuongThucThanhToan (
    phuong_thuc_thanh_toan_id INT AUTO_INCREMENT PRIMARY KEY,
    ten_phuong_thuc VARCHAR(50) NOT NULL
);

CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name_user VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    mat_khau VARCHAR(255) NOT NULL,
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
    role TINYINT NOT NULL DEFAULT 0
);

CREATE TABLE SanPham (
    san_pham_id INT AUTO_INCREMENT PRIMARY KEY,
    ten VARCHAR(100) NOT NULL,
    thuong_hieu VARCHAR(50),
    danh_muc_id INT NOT NULL,
    gia DECIMAL(10, 2) NOT NULL CHECK (gia >= 0),
    gioi_tinh ENUM('Nam', 'Nữ'),
    mo_ta TEXT,
    hinh_anh VARCHAR(255),
    FOREIGN KEY (danh_muc_id) REFERENCES DanhMuc(danh_muc_id) ON DELETE CASCADE
);

CREATE TABLE DonHang (
    don_hang_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ngay_dat DATETIME DEFAULT CURRENT_TIMESTAMP,
    tong_tien DECIMAL(10, 2) NOT NULL CHECK (tong_tien >= 0),
    trang_thai VARCHAR(50),
    phuong_thuc_thanh_toan_id INT,
    dia_chi VARCHAR(255),
    so_dien_thoai VARCHAR(15),
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (phuong_thuc_thanh_toan_id) REFERENCES PhuongThucThanhToan(phuong_thuc_thanh_toan_id)
);

CREATE TABLE ChiTietDonHang (
    chi_tiet_don_hang_id INT AUTO_INCREMENT PRIMARY KEY,
    don_hang_id INT NOT NULL,
    san_pham_id INT NOT NULL,
    so_luong INT NOT NULL CHECK (so_luong > 0),
    gia_tai_thoi_diem DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (don_hang_id) REFERENCES DonHang(don_hang_id) ON DELETE CASCADE,
    FOREIGN KEY (san_pham_id) REFERENCES SanPham(san_pham_id) ON DELETE CASCADE
);

CREATE TABLE GioHang (
    gio_hang_id INT AUTO_INCREMENT PRIMARY KEY, 
    user_id INT NOT NULL,                       
    tong_tien DECIMAL(10, 2) DEFAULT 0.00 CHECK (tong_tien >= 0), 
    san_pham_id INT DEFAULT NULL,              
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE, 
    FOREIGN KEY (san_pham_id) REFERENCES sanpham(san_pham_id) -- Khóa ngoại san_pham_id
);

CREATE TABLE ChiTietGioHang (
    chi_tiet_gio_hang_id INT AUTO_INCREMENT PRIMARY KEY,
    gio_hang_id INT NOT NULL,
    san_pham_id INT NOT NULL,
    so_luong INT NOT NULL CHECK (so_luong > 0),
      gia DECIMAL(15,2) DEFAULT NULL  
    FOREIGN KEY (gio_hang_id) REFERENCES GioHang(gio_hang_id) ON DELETE CASCADE,
    FOREIGN KEY (san_pham_id) REFERENCES SanPham(san_pham_id) ON DELETE CASCADE
);
CREATE TABLE trangthai (
    trang_thai_id INT(11) NOT NULL AUTO_INCREMENT,
    ten_trang_thai VARCHAR(50) NOT NULL,
    PRIMARY KEY (trang_thai_id)
)




SELECT * FROM DanhMuc;
SELECT * FROM PhuongThucThanhToan;
SELECT * FROM Users;
SELECT * FROM SanPham;
SELECT * FROM DonHang;
SELECT * FROM Admin;
SELECT * FROM ChiTietDonHang;
SELECT * FROM ChiTietGioHang;
SELECT * FROM GioHang;


INSERT INTO DanhMuc (ten_danh_muc) VALUES 
('Đồng hồ nam'),
('Đồng hồ nữ'),
('Sản phẩm nổi bật');

INSERT INTO PhuongThucThanhToan (ten_phuong_thuc) VALUES 
('Thẻ tín dụng'),
('Chuyển khoản ngân hàng'),
('Tiền mặt');

INSERT INTO Users (name_user, email, user_name, mat_khau, role) VALUES
('ADMIN', 'admin@gmail.com', 'admin', '123', 1),  -- Admin
('Nguyen Van A', 'nguyenvana@gmail.com', 'nguyenvana', '1234', 0),
('Tran Thi B', 'tranthib@gmail.com', 'tranthib', '1234', 0)


-- Insert products
INSERT INTO SanPham (ten, thuong_hieu, danh_muc_id, gia, gioi_tinh, mo_ta, hinh_anh) VALUES 
('Đồng hồ Jacques JL97I', 'Jacques Lemans', 1, 111111, 'Nam', 
'Thông số kỹ thuật:\nĐường kính mặt: 36 mm\nChống nước: 5 ATM\nChất liệu mặt: Kính cứng\nNăng lượng sử dụng: Điện tử\nSize dây: 19 mm\nChất liệu dây: Dây da\nChất liệu vỏ: Thép không gỉ mạ PVD\nChế độ bảo hành: Bảo hành quốc tế 02 năm', 
'img/dongho1.jpg'),
('Đồng hồ Atlantic AT-528', 'Atlantic', 1, 222222, 'Nam', 
'Thông số kỹ thuật:\nĐường kính mặt: 42 mm\nChống nước: 5 ATM\nChất liệu mặt: Sapphire\nNăng lượng sử dụng: Cơ tự động\nSize dây: 21 mm\nChất liệu dây: Dây da\nChất liệu vỏ: Thép không gỉ\nChế độ bảo hành: Bảo hành quốc tế 02 năm', 
'img/dongho2.jpg'),
('Đồng hồ Epos Swiss E-34', 'Epos', 1, 333333, 'Nam', 
'Thông số kỹ thuật:\nĐường kính mặt: 43 mm\nChống nước: 5 ATM\nChất liệu mặt: Sapphire\nNăng lượng sử dụng: Cơ thủ công\nSize dây: 20 mm\nChất liệu dây: Dây da\nChất liệu vỏ: Stainless Steel\nChế độ bảo hành: Bảo hành quốc tế 02 năm', 
'img/dongho3.jpg'),
('Đồng hồ Epos Swiss E-92', 'Epos', 1, 444444, 'Nam', 
'Thông số kỹ thuật:\nĐường kính mặt: 41 mm\nChống nước: 5 ATM\nChất liệu mặt: Sapphire\nNăng lượng sử dụng: Cơ tự động\nSize dây: 20 mm\nChất liệu dây: Dây da\nChất liệu vỏ: Thép không gỉ\nChế độ bảo hành: Bảo hành quốc tế 02 năm', 
'img/dongho4.jpg'),
('Đồng hồ Diamond D23', 'Diamond D', 2, 555555, 'Nữ', 
'Thông số kỹ thuật:\nĐường kính mặt: 32 mm\nChống nước: 10 ATM\nChất liệu mặt: Sapphire\nNăng lượng sử dụng: Cơ tự động\nSize dây: 18.8 mm\nChất liệu dây: Dây da\nChất liệu vỏ: Thép không gỉ\nChế độ bảo hành: Bảo hành quốc tế 03 năm', 
'img/donghonu1.jpg'),
('Đồng hồ Diamond D24', 'Diamond D', 2, 666666, 'Nữ', 
'Thông số kỹ thuật:\nĐường kính mặt: 32 mm\nChống nước: 10 ATM\nChất liệu mặt: Sapphire\nNăng lượng sử dụng: Cơ tự động\nSize dây: 19 mm\nChất liệu dây: Dây da\nChất liệu vỏ: Thép không gỉ\nChế độ bảo hành: Bảo hành quốc tế 03 năm', 
'img/donghonu2.jpg'),
('Đồng hồ Diamond D25', 'Diamond D', 2, 444444, 'Nữ', 
'Thông số kỹ thuật:\nĐường kính mặt: 32 mm\nChống nước: 10 ATM\nChất liệu mặt: Sapphire\nNăng lượng sử dụng: Cơ tự động\nSize dây: 18 mm\nChất liệu dây: Dây da\nChất liệu vỏ: Thép không gỉ\nChế độ bảo hành: Bảo hành quốc tế 03 năm', 
'img/donghonu3.jpg'),
('Tiffany Blue L04', 'Diamond D', 2, 777777, 'Nữ', 
'Thông số kỹ thuật:\nĐường kính mặt: 32 mm\nChống nước: 10 ATM\nChất liệu mặt: Sapphire\nNăng lượng sử dụng: Cơ tự động\nSize dây: 19 mm\nChất liệu dây: Dây da\nChất liệu vỏ: Thép không gỉ\nChế độ bảo hành: Bảo hành quốc tế 03 năm', 
'img/donghonu4.jpg'),
('Đồng hồ Epos Swiss A-95', 'Epos', 3, 3333333, 'Nam', 
'Thông số kỹ thuật:\nĐường kính mặt: 42 mm\nChống nước: 5 ATM\nChất liệu mặt: Sapphire\nNăng lượng sử dụng: Cơ tự động\nSize dây: 21 mm\nChất liệu dây: Dây da\nChất liệu vỏ: Thép không gỉ\nChế độ bảo hành: Bảo hành quốc tế 03 năm', 
'img/dongho10.jpg'),
('Đồng hồ Diamond A-92', 'Diamond D', 3, 444444, 'Nữ', 
'Thông số kỹ thuật:\nĐường kính mặt: 32.5 mm\nChống nước: 10 ATM\nChất liệu mặt: Sapphire\nNăng lượng sử dụng: Pin\nSize dây: 15 mm\nChất liệu dây: Dây da\nChất liệu vỏ: Thép không gỉ\nChế độ bảo hành: Bảo hành quốc tế 03 năm', 
'img/donghonu6.jpg'),
('Đồng hồ Epos Swiss F88', 'Epos', 3, 555555, 'Nam', 
'Thông số kỹ thuật:\nĐường kính mặt: 42 mm\nChống nước: 5 ATM\nChất liệu mặt: Sapphire\nNăng lượng sử dụng: Cơ thủ công\nSize dây: 21 mm\nChất liệu dây: Dây da\nChất liệu vỏ: Thép không gỉ\nChế độ bảo hành: Bảo hành quốc tế 03 năm', 
'img/dongho9.jpg');


