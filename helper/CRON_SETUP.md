# Setup Cron Job để tự động hủy booking hết hạn

## Cách hoạt động

Hệ thống sẽ:
1. Khi user tạo booking → `status = 'pending'`, `expires_at = NOW() + 10 phút`
2. Phòng bị "khóa tạm thời" trong 10 phút (không cho người khác đặt)
3. Nếu thanh toán thành công → `status = 'confirmed'`, phòng được đặt
4. Nếu KHÔNG thanh toán trong 10 phút → Cron job tự động hủy booking, "mở khóa" phòng

## Windows - Task Scheduler Setup

### Bước 1: Tạo Batch File
Tạo file `run-cron.bat` trong thư mục project:

```batch
@echo off
cd /d "C:\xampp\htdocs\PTUD(Version 2)-TichHopMoMo"
C:\xampp\php\php.exe helper\cancel-expired-bookings.php >> logs\cron.log 2>&1
```

### Bước 2: Mở Task Scheduler
1. Nhấn `Win + R` → gõ `taskschd.msc` → Enter
2. Click "Create Basic Task..."
3. Name: "Cancel Expired Bookings"
4. Trigger: Daily → Start time: 00:00 → Recur every 1 day
5. Action: Start a program
6. Program/script: Browse đến file `run-cron.bat`
7. Finish

### Bước 3: Cấu hình chạy mỗi phút
1. Trong Task Scheduler, tìm task vừa tạo
2. Right-click → Properties
3. Tab "Triggers" → Edit
4. Tick "Repeat task every" → chọn "1 minute"
5. Duration: "Indefinitely"
6. OK

## Linux/Ubuntu - Crontab Setup

```bash
# Edit crontab
crontab -e

# Thêm dòng này (chạy mỗi phút)
* * * * * php /path/to/PTUD/helper/cancel-expired-bookings.php >> /path/to/PTUD/logs/cron.log 2>&1
```

## Chạy thủ công để test

### Windows:
```powershell
cd "C:\xampp\htdocs\PTUD(Version 2)-TichHopMoMo"
php helper\cancel-expired-bookings.php
```

### Linux:
```bash
cd /path/to/PTUD
php helper/cancel-expired-bookings.php
```

## Chạy qua Web Browser (Alternative - không khuyến khích)

Truy cập URL:
```
http://localhost/view/view/user/traveller/../../../helper/cancel-expired-bookings.php?secret_key=your_secret_key_here_12345
```

⚠️ **Lưu ý bảo mật**: Đổi `secret_key` trong file `cancel-expired-bookings.php` trước khi deploy production!

## Kiểm tra Log

Xem file `logs/cron.log` để theo dõi:
```bash
tail -f logs/cron.log
```

Hoặc trên Windows:
```powershell
Get-Content logs\cron.log -Wait
```

## Monitoring

Cron job sẽ log:
- ✓ Số booking hết hạn tìm thấy
- ✓ Số booking đã cancel thành công
- ✗ Số lỗi nếu có

Example output:
```
[2025-12-11 10:05:00] Starting expired bookings cleanup...
Found 3 expired booking(s). Cancelling...
  ✓ Cancelled booking #324 (BK20251211ABC123)
  ✓ Cancelled booking #325 (BK20251211DEF456)
  ✓ Cancelled booking #326 (BK20251211GHI789)

Summary:
  - Total expired: 3
  - Cancelled: 3
  - Errors: 0
[2025-12-11 10:05:01] Cleanup completed.
```
