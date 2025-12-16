# Hướng dẫn cài đặt trên Hosting

## 1. Tạo các thư mục cần thiết

Trước khi chạy website, cần tạo các thư mục sau và cấp quyền ghi:

```bash
# Tạo thư mục uploads
mkdir -p public/uploads/id_cards
mkdir -p public/uploads/host
mkdir -p public/uploads/listings
mkdir -p public/uploads/reviews

# Cấp quyền ghi cho PHP (chmod 755 hoặc 775)
chmod 755 public/uploads
chmod 755 public/uploads/id_cards
chmod 755 public/uploads/host
chmod 755 public/uploads/listings
chmod 755 public/uploads/reviews
```

## 2. Cấp quyền qua cPanel File Manager

Nếu dùng cPanel:

1. Vào **File Manager**
2. Navigate đến thư mục `public/uploads/`
3. Tạo các thư mục:
   - `id_cards`
   - `host`
   - `listings`
   - `reviews`
4. Click chuột phải vào mỗi thư mục → **Change Permissions**
5. Set permissions: **755** (rwxr-xr-x) hoặc **775** (rwxrwxr-x)

## 3. Kiểm tra quyền

```bash
ls -la public/uploads/
```

Output mong muốn:
```
drwxr-xr-x  id_cards
drwxr-xr-x  host
drwxr-xr-x  listings
drwxr-xr-x  reviews
```

## 4. Import Database

1. Tạo database mới trên hosting
2. Import file: `database/schema/we_go.sql`
3. (Optional) Import seed data: `database/seeds/admin_superadmin_users.sql`

## 5. Cấu hình Database

Sửa file `model/mConnect.php`:
```php
private $servername = "localhost";  // Thường là localhost
private $username = "your_db_username";
private $password = "your_db_password";
private $dbname = "your_db_name";
```

## 6. Kiểm tra PHP Extensions

Đảm bảo hosting có các extension:
- `mysqli`
- `gd` hoặc `imagick` (xử lý ảnh)
- `fileinfo` (kiểm tra MIME type)
- `mbstring` (xử lý Unicode)
- `json`
- `curl` (cho MoMo API)

## 7. Cấu hình MoMo (Production)

Sửa file `helper/MoMoHelper.php`:
```php
// Chuyển từ sandbox sang production
private $endpoint = 'https://payment.momo.vn/v2/gateway/api/create';
```

Lấy thông tin từ MoMo Business:
- `accessKey`
- `secretKey`
- `partnerCode`

## 8. Test Upload

Sau khi setup xong, test upload bằng cách:
1. Đăng ký tài khoản mới
2. Đăng ký làm host (upload CMND + giấy phép)
3. Kiểm tra file có được lưu vào `public/uploads/id_cards/` không

## 9. Bảo mật

### Bảo vệ thư mục uploads
Tạo file `.htaccess` trong `public/uploads/`:

```apache
# Chặn thực thi PHP trong thư mục uploads
<Files *.php>
    deny from all
</Files>

# Chỉ cho phép truy cập file ảnh
<FilesMatch "\.(jpg|jpeg|png|gif)$">
    Allow from all
</FilesMatch>
```

### Giới hạn upload size
Trong `php.ini` hoặc `.htaccess`:
```
php_value upload_max_filesize 10M
php_value post_max_size 10M
```

## 10. Cron Job (Tự động hủy booking hết hạn)

Setup cron job chạy mỗi phút:
```bash
* * * * * php /path/to/project/helper/cancel-expired-bookings.php
```

Hoặc dùng web-based cron:
```
https://yourdomain.com/helper/cancel-expired-bookings.php?secret_key=your_secret_key_here_12345
```

## 11. Kiểm tra lỗi

Nếu gặp lỗi HTTP 500, bật error reporting:

Tạo file `debug.php` tạm thời:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP Version: " . phpversion() . "<br>";
echo "Upload dir writable: " . (is_writable('public/uploads/id_cards') ? 'YES' : 'NO') . "<br>";
echo "Extensions: " . implode(', ', get_loaded_extensions());
```

Truy cập `https://yourdomain.com/debug.php` để xem thông tin.
**XÓA FILE NÀY SAU KHI DEBUG XONG!**

## 12. Troubleshooting

### Lỗi: "Cannot create directory"
→ Tạo thư mục thủ công và cấp quyền 755

### Lỗi: "Failed to move uploaded file"
→ Kiểm tra quyền ghi của thư mục

### Lỗi: "Class not found"
→ Kiểm tra đường dẫn require_once

### Ảnh không hiển thị
→ Kiểm tra đường dẫn file_url trong database (phải có `/` ở đầu)
