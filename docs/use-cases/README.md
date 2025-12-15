# Use Case Documentation - We Go Platform

> **Mục đích:** Documentation chi tiết về flow của từng use case để vẽ sequence diagram

## 📁 Danh sách Files

### 1️⃣ **Authentication** (`01-authentication.md`)
- **UC-01:** Đăng ký tài khoản (Register Account)
- **UC-02:** Xác thực email (Verify Email)
- **UC-03:** Đăng nhập (Login)
- **UC-04:** Đăng xuất (Logout)

### 2️⃣ **Host Management** (`02-host-management.md`)
- **UC-05:** Đăng ký trở thành Host
- **UC-06:** Admin duyệt đơn đăng ký Host
- **UC-07:** Admin quản lý Host

### 3️⃣ **Listing & Booking** (`03-listing-booking.md`)
- **UC-08:** Host tạo Listing mới
- **UC-09:** Traveler tìm kiếm và đặt phòng
- **UC-10:** Thanh toán qua MoMo

### 4️⃣ **Review & Support** (`04-review-support.md`)
- **UC-11:** Đánh giá sau khi hoàn thành booking
- **UC-12:** User gửi yêu cầu hỗ trợ
- **UC-13:** Admin xử lý ticket hỗ trợ

### 5️⃣ **Admin & Trust Score** (`05-admin-trust-score.md`)
- **UC-14:** Admin quản lý Users
- **UC-15:** Admin quản lý Amenities
- **UC-16:** Trust Score System

---

## 📊 Cách sử dụng để vẽ Sequence Diagram

### Format của mỗi use case:

```markdown
## UC-XX: Tên Use Case
**Actor:** Người thực hiện

### Flow Diagram
```text
Actor (Browser)
  ↓ Hành động
View: path/to/view.php
  ↓ Nhận data
Controller: path/to/controller.php
  → function_name($params)
  ↓ Validate/Process
Model: path/to/model.php
  → model_function($params)
Database: table_name
  ↓ SQL Query
  ↓ Return result
View: path/to/view.php
  ← Display result
```

### Database Tables
Danh sách bảng database được sử dụng

### Files Involved
Danh sách file cụ thể trong codebase
```

---

## 🗂️ Cấu trúc MVC trong We Go

### View Layer
- **Location:** `view/user/{role}/`
- **Roles:** `admin/`, `host/`, `traveller/`
- **Responsibility:** Hiển thị UI, submit form

### Controller Layer
- **Location:** `controller/`
- **Files:** `cUser.php`, `cAdmin.php`, `cHost.php`, `cListing.php`, `cBooking.php`, `cPayment.php`, `cReview.php`, `cSupport.php`
- **Responsibility:** Nhận request, validate input, gọi Model, redirect

### Model Layer
- **Location:** `model/`
- **Files:** `mUser.php`, `mAdmin.php`, `mHost.php`, `mListing.php`, `mBooking.php`, `mPaymentMoMo.php`, `mReview.php`, `mSupport.php`, `mUserScore.php`, `mEmailPHPMailer.php`
- **Responsibility:** Database queries, business logic, external API calls

### Helper Layer
- **Location:** `helper/`
- **Files:** `auth.php`, `validator.php`, `file_upload.php`
- **Responsibility:** Authentication checks, input validation, file handling

---

## 🎨 Màu sắc gợi ý cho Sequence Diagram

| Component | Màu đề xuất | Lý do |
|-----------|-------------|-------|
| **Actor/Browser** | 🔵 Blue | Người dùng/client |
| **View** | 🟢 Green | Presentation layer |
| **Controller** | 🟡 Yellow | Business logic layer |
| **Model** | 🟠 Orange | Data access layer |
| **Database** | 🔴 Red | Persistence layer |
| **External API** | 🟣 Purple | MoMo, Email service |

---

## 🔄 Pattern chung trong hệ thống

### 1. PRG Pattern (Post/Redirect/Get)
```
POST request → Process → Store message in session → Redirect → GET page → Display message
```

### 2. Authentication Flow
```
View → helper/auth.php → Check session → Allow/Deny access
```

### 3. File Upload Flow
```
View (form) → Controller → Validate file → Move to public/uploads/ → Save path to DB
```

### 4. Email Notification Flow
```
Model operation → mEmailPHPMailer.php → Send email (async)
```

### 5. Trust Score Flow
```
User action → Controller → Model → mUserScore.php → Update score → Insert history
```

---

## 📦 Database Schema Reference

**Main Tables:**
- `user` - User accounts
- `host` - Host profiles
- `host_application` - Host registration requests
- `listing` - Property listings
- `listing_image` - Listing photos
- `listing_amenity` - Listing amenities (many-to-many)
- `amenity` - Available amenities
- `booking` - Booking records
- `payment_log` - Payment transactions
- `review` - User reviews
- `support_ticket` - Support requests
- `support_message` - Support conversation
- `user_score_history` - Trust score changes
- `admin_action_log` - Admin audit log

**Schema File:** `database/schema/we_go.sql`

---

## 🚀 Quick Reference

### Trust Score Points
| Action | Points |
|--------|--------|
| Verify email | +5 |
| Complete profile | +10 |
| Book place | +10 |
| Review place | +5 |
| Host approved | +20 |
| Complete booking | +15 |
| Cancel booking | -10 |
| Violation | -30 |
| Spam report | -50 |

### User Roles
- **User** - Basic traveler (can book)
- **Host** - Can list properties
- **Support** - Can manage support tickets
- **Manager** - Can manage users, hosts, listings
- **SuperAdmin** - Full system access

### Booking Status Flow
```
pending → confirmed → completed → reviewed
         ↓
      cancelled
```

### Host Status Flow
```
pending → approved ↔ suspended
         ↓
      rejected → (can reapply)
```

---

## 📝 Notes

### Bugs đã fix (Ngày 13/12/2024)
1. **Support Tickets không hiển thị**
   - **File:** `model/mAdmin.php` (dòng 610, 652)
   - **Lỗi:** Query SELECT các cột `guest_name`, `guest_email`, `guest_phone` không tồn tại
   - **Fix:** Xóa các cột đó khỏi SELECT statement

2. **Nested Git Repository**
   - **Issue:** `c:\xampp\htdocs\.git` và `c:\xampp\htdocs\PTUD\.git` xung đột
   - **Fix:** Xóa `.git` ở thư mục cha

3. **Migration files không cần thiết**
   - **Deleted:** 3 migration files (guest columns, payment_logs, pending status)
   - **Reason:** Schema đã có sẵn các thay đổi này

### Features đã implement
- ✅ PRG Pattern để tránh duplicate submission
- ✅ Trust Score System với automatic scoring
- ✅ MoMo Payment Integration (sandbox mode)
- ✅ Email notifications cho tất cả actions quan trọng
- ✅ Role-based access control
- ✅ Self-healing host registration (auto-create nếu thiếu)

---

**Last Updated:** December 15, 2025  
**Project:** We Go - Homestay Booking Platform  
**Repository:** `c:\xampp\htdocs\PTUD\`
