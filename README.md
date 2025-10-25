# 🏠 We Go - Hệ thống đặt chỗ ở du lịch

Hệ thống đặt phòng và quản lý chỗ ở du lịch tại Việt Nam, được xây dựng bằng PHP thuần (vanilla PHP) với kiến trúc MVC.

## 📋 Yêu cầu hệ thống

- **XAMPP** (hoặc tương đương):
  - PHP >= 8.0
  - MySQL/MariaDB >= 5.7
  - Apache Web Server
- Trình duyệt web hiện đại (Chrome, Firefox, Edge, Safari)

## 🚀 Hướng dẫn cài đặt

### Bước 1: Cài đặt XAMPP

1. Tải XAMPP từ [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Cài đặt XAMPP vào thư mục mặc định (thường là `C:\xampp`)
3. Khởi động **XAMPP Control Panel**

### Bước 2: Clone project

```bash
# Mở terminal/cmd và di chuyển vào thư mục htdocs của XAMPP
cd C:\xampp\htdocs

# Clone repository
git clone https://github.com/Vanoson2/PTUD.git Project_PTUD_Again

# Hoặc download ZIP và giải nén vào C:\xampp\htdocs\Project_PTUD_Again
```

### Bước 3: Khởi động Apache và MySQL

1. Mở **XAMPP Control Panel**
2. Click **Start** cho module **Apache**
3. Click **Start** cho module **MySQL**
4. Đảm bảo cả hai đều hiển thị màu xanh (đang chạy)

### Bước 4: Tạo database

#### Cách 1: Sử dụng phpMyAdmin (Khuyên dùng)

1. Truy cập [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click tab **SQL**
3. Mở file `we_go.sql` trong thư mục project
4. Copy toàn bộ nội dung và paste vào phpMyAdmin
5. Click **Go** để chạy script
6. Mở file `seed.sql` và làm tương tự để import dữ liệu mẫu

#### Cách 2: Sử dụng MySQL Command Line

```bash
# Mở terminal/cmd
cd C:\xampp\htdocs\Project_PTUD_Again

# Đăng nhập MySQL (password mặc định của XAMPP là rỗng)
C:\xampp\mysql\bin\mysql.exe -u root -p

# Tạo database
CREATE DATABASE we_go CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE we_go;

# Import schema
SOURCE we_go.sql;

# Import data mẫu
SOURCE seed.sql;

# Thoát
EXIT;
```

### Bước 5: Cấu hình kết nối database

Mở file `model/mConnect.php` và kiểm tra thông tin kết nối:

```php
<?php
class mConnect{
    public function mMoKetNoi(){
        $servername = "localhost";
        $username = "root";
        $password = "";  // Mặc định XAMPP không có password
        $dbname = "we_go";

        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    }
}
?>
```

**Lưu ý:** Nếu bạn đã đặt password cho MySQL trong XAMPP, hãy cập nhật giá trị `$password`.

### Bước 6: Chạy ứng dụng

1. Mở trình duyệt
2. Truy cập: [http://localhost/Project_PTUD_Again/index.php](http://localhost/Project_PTUD_Again/index.php)
3. Trang chủ sẽ hiển thị với form tìm kiếm và các địa điểm du lịch nổi tiếng

## 📁 Cấu trúc thư mục

```
Project_PTUD_Again/
├── controller/              # Controller layer (MVC)
│   ├── cListing.php        # Controller xử lý listing
│   └── cType&Amenties.php  # Controller xử lý loại chỗ ở và tiện nghi
├── model/                   # Model layer (MVC)
│   ├── mConnect.php        # Database connection
│   ├── mListing.php        # Model xử lý listing
│   └── mType.php           # Model xử lý loại chỗ ở và tiện nghi
├── view/                    # View layer (MVC)
│   ├── css/                # Stylesheets
│   │   ├── home.css        # Style trang chủ
│   │   ├── listListing.css # Style trang danh sách
│   │   └── style.css       # Global styles
│   ├── partials/           # Partial views
│   │   ├── header.php      # Header chung
│   │   └── footer.php      # Footer chung
│   └── user/
│       └── traveller/
│           └── listListings.php  # Trang danh sách chỗ ở
├── public/                  # Static assets
│   ├── img/                # Hình ảnh
│   ├── js/                 # JavaScript files
│   │   ├── autocomplete.js      # Tự động hoàn thành địa điểm
│   │   ├── date-picker.js       # Chọn ngày
│   │   ├── guestscounter.js     # Đếm số khách
│   │   └── listing-filter.js    # Lọc danh sách chỗ ở
│   └── video/              # Video files
├── data/                    # Data files
│   └── locations_vn.json   # Dữ liệu địa điểm VN
├── index.php               # Trang chủ
├── we_go.sql              # Database schema
├── seed.sql               # Dữ liệu mẫu
└── README.md              # File này
```

## 🎯 Tính năng chính

### 1. **Trang chủ (index.php)**
- Form tìm kiếm chỗ ở:
  - Tự động hoàn thành địa điểm (autocomplete)
  - Chọn ngày check-in/check-out với date picker
  - Chọn số lượng khách (1-10 người)
- Hiển thị các địa điểm du lịch nổi tiếng (Đà Nẵng, Nha Trang, Huế, Hà Nội)
- Click vào địa điểm để xem danh sách chỗ ở

### 2. **Trang danh sách (listListings.php)**
- Hiển thị kết quả tìm kiếm theo:
  - Địa điểm
  - Ngày check-in/check-out (lọc chỗ đã đặt)
  - Số khách (chỉ hiển thị chỗ có sức chứa phù hợp)
- Sidebar lọc realtime:
  - Loại chỗ ở (Khách sạn, Nhà nghỉ, Hostel, v.v.)
  - Khoảng giá (Dưới 500k, 500k-1tr, 1tr-1.5tr, Trên 1.5tr)
  - Đánh giá (1-5 sao)
  - Tiện nghi (Wifi, Điều hòa, Bếp, v.v.)
- Hiển thị thông tin chỗ ở:
  - Ảnh đại diện
  - Tên và mô tả
  - Địa chỉ
  - Sức chứa (số khách tối đa)
  - Điểm rating và số lượng đánh giá
  - Giá/đêm
- Sắp xếp: Ưu tiên chỗ có review, theo rating cao xuống thấp

### 3. **Tính năng nâng cao**
- **Lọc booking trùng lịch**: Tự động loại bỏ chỗ ở đã được đặt trong khoảng thời gian tìm kiếm
- **Lọc theo capacity**: Chỉ hiển thị chỗ ở có sức chứa >= số khách
- **Filter realtime**: Kết quả cập nhật ngay lập tức khi thay đổi filter (không reload trang)

## 🗄️ Database Schema

Database `we_go` bao gồm các bảng chính:

- **user**: Thông tin người dùng
- **host**: Thông tin chủ nhà
- **listing**: Thông tin chỗ ở
- **place_type**: Loại chỗ ở (Khách sạn, Nhà nghỉ, v.v.)
- **amenity**: Tiện nghi (Wifi, Điều hòa, v.v.)
- **listing_amenity**: Liên kết giữa chỗ ở và tiện nghi
- **listing_image**: Hình ảnh chỗ ở
- **bookings**: Đơn đặt phòng
- **review**: Đánh giá của khách
- **provinces, wards**: Đơn vị hành chính Việt Nam

## 🔧 Xử lý sự cố

### Lỗi: "Connection failed"
- Kiểm tra MySQL đã khởi động trong XAMPP Control Panel
- Kiểm tra thông tin trong `model/mConnect.php`
- Kiểm tra database `we_go` đã được tạo

### Lỗi: "Table doesn't exist"
- Import lại file `we_go.sql` và `seed.sql`
- Đảm bảo đã chọn đúng database `we_go`

### Lỗi: "Cannot GET /index.php"
- Kiểm tra Apache đã khởi động
- Kiểm tra đường dẫn: `http://localhost/Project_PTUD_Again/index.php`

### Lỗi: Không hiển thị tiếng Việt (hiển thị ???)
- Kiểm tra charset trong `model/mConnect.php`: `$conn->set_charset("utf8mb4");`
- Đảm bảo database sử dụng `utf8mb4_unicode_ci` collation

### Lỗi: JavaScript không hoạt động
- Mở Console trong trình duyệt (F12) để xem lỗi
- Đảm bảo các file JS trong `public/js/` tồn tại
- Clear cache trình duyệt (Ctrl + Shift + R)

## 🌐 Các URL quan trọng

- **Trang chủ**: http://localhost/Project_PTUD_Again/index.php
- **phpMyAdmin**: http://localhost/phpmyadmin
- **Tìm kiếm Đà Nẵng**: http://localhost/Project_PTUD_Again/view/user/traveller/listListings.php?location=Đà%20Nẵng&checkin=2025-10-26&checkout=2025-10-27&guests=1

## 📝 API Endpoints (Internal)

Project này sử dụng PHP server-side rendering, không có REST API public. Các controller xử lý logic:

- **cListing::cSearchListingsWithFilters()**: Tìm kiếm chỗ ở với filters
- **cListing::cGetListingAmenities()**: Lấy danh sách tiện nghi
- **cTypeAndAmenties::cGetAllTypes()**: Lấy tất cả loại chỗ ở
- **cTypeAndAmenties::cGetAllAmenities()**: Lấy tất cả tiện nghi

## 🛠️ Công nghệ sử dụng

- **Backend**: PHP 8.2.12 (vanilla PHP, no framework)
- **Database**: MariaDB 10.4.32
- **Frontend**: 
  - HTML5, CSS3
  - JavaScript (ES6+)
  - Bootstrap 5.3.2
  - FontAwesome 6.5.1
  - Flatpickr (date picker)
- **Architecture**: MVC (Model-View-Controller)

## 👥 Đóng góp

Project này được phát triển bởi nhóm sinh viên. Mọi đóng góp đều được chào đón!

## 📄 License

[Chưa có license - Educational project]

## 📧 Liên hệ

- **Repository**: https://github.com/Vanoson2/PTUD
- **Issues**: https://github.com/Vanoson2/PTUD/issues

---

**Lưu ý**: Đây là project học tập, không nên sử dụng trong môi trường production mà chưa có security audit.
