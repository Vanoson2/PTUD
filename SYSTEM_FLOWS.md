# 🔄 CÁC LUỒNG HỆ THỐNG - WEGO

## Mục lục
1. [Luồng Đăng ký & Xác thực User](#1-luồng-đăng-ký--xác-thực-user)
2. [Luồng Đăng ký Host](#2-luồng-đăng-ký-host)
3. [Luồng Đăng Listing](#3-luồng-đăng-listing-host)
4. [Luồng Đặt phòng (Booking)](#4-luồng-đặt-phòng-booking)
5. [Luồng Đánh giá (Review)](#5-luồng-đánh-giá-review)
6. [Luồng Hỗ trợ (Support Ticket)](#6-luồng-hỗ-trợ-support-ticket)
7. [Luồng Quản lý Doanh thu](#7-luồng-quản-lý-doanh-thu)
8. [Luồng Điểm Tín nhiệm (Trust Score)](#8-luồng-điểm-tín-nhiệm-trust-score)

---

## 1. Luồng Đăng ký & Xác thực User

### Mô tả
User đăng ký tài khoản mới, nhận mã xác thực 6 số qua email và kích hoạt tài khoản.

### Flow Chart
```
┌─────────────────────────────┐
│ User nhập form đăng ký      │
│ (email, phone, password)    │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ view/user/traveller/        │
│ register.php                │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cUser->cRegisterUser()      │
│ • Validate email format     │
│ • Validate phone (10-11 số) │
│ • Validate password (min 8) │
│ • Kiểm tra duplicate        │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mUser->mRegisterUser()      │
│ • Hash password             │
│ • INSERT vào users table    │
│ • Trả về user_id            │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mUser->mGenerateVerifyCode()│
│ • Tạo mã 6 số ngẫu nhiên    │
│ • Lưu vào users table       │
│ • Expire sau 15 phút        │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ EmailHelper->               │
│ sendVerificationCode()      │
│ • Gửi email qua PHPMailer   │
│ • Template: mã 6 số         │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ User nhận email & nhập mã   │
│ view/user/traveller/        │
│ verify-email.php            │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cUser->cVerifyCode()        │
│ • Validate mã 6 số          │
│ • Kiểm tra expire           │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mUser->mVerifyCode()        │
│ • So sánh mã                │
│ • UPDATE is_verified = 1    │
│ • Xóa verify_code           │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ ✅ Hoàn thành               │
│ User có thể đăng nhập       │
└─────────────────────────────┘
```

### Files liên quan
- **View**: `view/user/traveller/register.php`, `view/user/traveller/verify-email.php`
- **Controller**: `controller/cUser.php`
- **Model**: `model/mUser.php`
- **Helper**: `helper/EmailHelper.php`
- **Database**: `users` table

### Validation Rules
- **Email**: Format hợp lệ, unique, max 190 ký tự
- **Phone**: 10-11 số, unique
- **Password**: Min 8 ký tự, có chữ hoa, chữ thường, số
- **Full Name**: Max 150 ký tự

---

## 2. Luồng Đăng ký Host

### Mô tả
User đã đăng nhập muốn trở thành Host, gửi đơn đăng ký với thông tin pháp lý, Admin duyệt hoặc từ chối.

### Flow Chart
```
┌─────────────────────────────┐
│ User (đã đăng nhập)         │
│ muốn trở thành Host         │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ view/user/traveller/        │
│ host-register.php           │
│ Form: legal_name, tax_id,   │
│ address, documents          │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cHost->cRegisterHost()      │
│ • Validate thông tin        │
│ • Upload documents          │
│ • Kiểm tra user đã là Host? │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mHost->mRegisterHost()      │
│ • INSERT vào                │
│   host_applications table   │
│ • status = 'pending'        │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Admin xem đơn               │
│ view/admin/                 │
│ host-applications.php       │
└──────────┬──────────────────┘
           │
      ┌────┴────┐
      │         │
      ▼         ▼
┌─────────┐ ┌─────────┐
│ Duyệt   │ │ Từ chối │
└────┬────┘ └────┬────┘
     │           │
     ▼           ▼
┌─────────────────────────────┐ ┌─────────────────────────────┐
│ cAdmin->                    │ │ cAdmin->                    │
│ cApproveHostApplication()   │ │ cRejectHostApplication()    │
│ • Validate status = pending │ │ • Nhập lý do từ chối        │
└──────────┬──────────────────┘ └──────────┬──────────────────┘
           │                               │
           ▼                               ▼
┌─────────────────────────────┐ ┌─────────────────────────────┐
│ mAdmin->                    │ │ mAdmin->                    │
│ mApproveHostApplication()   │ │ mRejectHostApplication()    │
│ • UPDATE status = approved  │ │ • UPDATE status = rejected  │
└──────────┬──────────────────┘ │ • Lưu rejection_reason      │
           │                    └──────────┬──────────────────┘
           ▼                               │
┌─────────────────────────────┐           │
│ mHost->                     │           │
│ mCreateHostFromApplication()│           │
│ • INSERT vào hosts table    │           │
│ • Link với user_id          │           │
└──────────┬──────────────────┘           │
           │                               │
           ▼                               ▼
┌─────────────────────────────┐ ┌─────────────────────────────┐
│ ✅ User trở thành Host      │ │ ❌ Đơn bị từ chối           │
│ Có thể đăng listing         │ │ Thông báo lý do cho user    │
└─────────────────────────────┘ └─────────────────────────────┘
```

### Files liên quan
- **View**: `view/user/traveller/host-register.php`, `view/admin/host-applications.php`
- **Controller**: `controller/cHost.php`, `controller/cAdmin.php`
- **Model**: `model/mHost.php`, `model/mAdmin.php`
- **Database**: `host_applications`, `hosts`

### Thông tin cần thiết
- **Legal Name**: Tên pháp lý (cá nhân/doanh nghiệp)
- **Tax ID**: Mã số thuế
- **Address**: Địa chỉ liên hệ
- **Documents**: Giấy tờ pháp lý (CMND, giấy phép KD)

---

## 3. Luồng Đăng Listing (Host)

### Mô tả
Host tạo listing (phòng/nhà cho thuê), thêm ảnh, amenities, services, submit để Admin duyệt.

### Flow Chart
```
┌─────────────────────────────┐
│ Host đăng nhập              │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ view/user/host/             │
│ create-listing.php          │
│ Form: title, description,   │
│ type, price, location, etc. │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cHost->cCreateListing()     │
│ • Validate dữ liệu          │
│ • Validate giá > 0          │
│ • Validate max_guests >= 1  │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mHost->mCreateListing()     │
│ • INSERT vào listings table │
│ • status = 'draft'          │
│ • Trả về listing_id         │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Host thêm ảnh               │
│ view/user/host/             │
│ edit-listing.php            │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cHost->cUploadListingImage()│
│ • Validate file image       │
│ • Resize ảnh                │
│ • Upload lên server         │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mHost->mInsertListingImage()│
│ • INSERT vào listing_images │
│ • Lưu đường dẫn ảnh         │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Host thêm Amenities         │
│ (WiFi, TV, điều hòa...)     │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cHost->                     │
│ cAddAmenityToListing()      │
│ • Validate amenity_id       │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mHost->                     │
│ mAddAmenityToListing()      │
│ • INSERT vào                │
│   listing_amenities table   │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Host thêm Services          │
│ (Đón sân bay, ăn sáng...)   │
│ cHost->cAddServiceToListing()│
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Host submit listing         │
│ cHost->cSubmitListing()     │
│ • Validate đủ thông tin     │
│ • Phải có ít nhất 1 ảnh     │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mHost->mUpdateListingStatus()│
│ • UPDATE status:            │
│   draft → pending           │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Admin xem & duyệt           │
│ view/admin/listings.php     │
└──────────┬──────────────────┘
           │
      ┌────┴────┐
      │         │
      ▼         ▼
┌─────────┐ ┌─────────┐
│ Duyệt   │ │ Từ chối │
└────┬────┘ └────┬────┘
     │           │
     ▼           ▼
┌─────────────────────────────┐ ┌─────────────────────────────┐
│ cAdmin->cApproveListing()   │ │ cAdmin->cRejectListing()    │
│ • Status: pending → active  │ │ • Status: pending → rejected│
└──────────┬──────────────────┘ │ • Lưu lý do từ chối         │
           │                    └──────────┬──────────────────┘
           ▼                               │
┌─────────────────────────────┐           │
│ ✅ Listing công khai        │           │
│ User có thể tìm & đặt phòng │           │
└─────────────────────────────┘           │
                                          ▼
                               ┌─────────────────────────────┐
                               │ ❌ Listing bị từ chối       │
                               │ Host nhận thông báo lý do   │
                               └─────────────────────────────┘
```

### Files liên quan
- **View**: `view/user/host/create-listing.php`, `view/user/host/edit-listing.php`, `view/admin/listings.php`
- **Controller**: `controller/cHost.php`, `controller/cAdmin.php`
- **Model**: `model/mHost.php`, `model/mListing.php`, `model/mAdmin.php`
- **Database**: `listings`, `listing_images`, `listing_amenities`, `listing_services`

### Trạng thái Listing
- **draft**: Bản nháp (chưa submit)
- **pending**: Chờ Admin duyệt
- **active**: Đã duyệt, hiển thị công khai
- **rejected**: Bị từ chối
- **inactive**: Host tạm ẩn

---

## 4. Luồng Đặt phòng (Booking)

### Mô tả
User tìm kiếm phòng, chọn ngày, thanh toán qua MoMo, hoàn tất booking.

### Flow Chart
```
┌─────────────────────────────┐
│ User tìm kiếm phòng         │
│ view/user/traveller/        │
│ search.php                  │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cListing->                  │
│ cSearchListingsWithFilters()│
│ • Filter theo location      │
│ • Filter theo price range   │
│ • Filter theo amenities     │
│ • Filter theo ngày          │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mListing->mSearchListings() │
│ • Query listings table      │
│ • JOIN với amenities        │
│ • Kiểm tra availability     │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Hiển thị danh sách phòng    │
│ User chọn phòng             │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ view/user/traveller/        │
│ listing-detail.php          │
│ cListing->cGetListingDetail()│
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ User chọn ngày check-in/out │
│ User chọn số khách          │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cBooking->                  │
│ cValidateBookingDates()     │
│ • Kiểm tra ngày hợp lệ      │
│ • Kiểm tra phòng trống      │
│ • Kiểm tra số khách <= max  │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ User chọn services (option) │
│ (Đón sân bay, ăn sáng...)   │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ User click "Đặt phòng"      │
│ cBooking->cCreateBooking()  │
│ • Validate tất cả dữ liệu   │
│ • Tính tổng tiền            │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mBooking->mCreateBooking()  │
│ • INSERT vào bookings table │
│ • status = 'pending'        │
│ • Trả về booking_id         │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Redirect sang thanh toán    │
│ view/user/traveller/        │
│ payment.php                 │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cPayment->                  │
│ cInitiateMoMoPayment()      │
│ • Tạo request MoMo          │
│ • Generate signature        │
│ • Return payment URL        │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ User thanh toán trên app    │
│ MoMo                        │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ MoMo gọi callback IPN       │
│ view/payment/momo-ipn.php   │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cPayment->cHandleMoMoIPN()  │
│ • Verify signature          │
│ • Kiểm tra resultCode       │
└──────────┬──────────────────┘
           │
      ┌────┴────┐
      │         │
      ▼         ▼
┌─────────┐ ┌─────────┐
│ Success │ │ Failed  │
└────┬────┘ └────┬────┘
     │           │
     ▼           ▼
┌─────────────────────────────┐ ┌─────────────────────────────┐
│ mBooking->                  │ │ mBooking->                  │
│ mUpdateBookingStatus()      │ │ mUpdateBookingStatus()      │
│ • Status: pending →         │ │ • Status: pending →         │
│   confirmed                 │ │   cancelled                 │
└──────────┬──────────────────┘ └──────────┬──────────────────┘
           │                               │
           ▼                               │
┌─────────────────────────────┐           │
│ mPayment->mCreatePayment()  │           │
│ • Lưu thông tin thanh toán  │           │
│ • payment_method = 'momo'   │           │
│ • transaction_id từ MoMo    │           │
└──────────┬──────────────────┘           │
           │                               │
           ▼                               │
┌─────────────────────────────┐           │
│ cUser->cAddScoreByAction()  │           │
│ • Action: 'complete_booking'│           │
│ • Cộng +5 điểm tín nhiệm    │           │
└──────────┬──────────────────┘           │
           │                               │
           ▼                               ▼
┌─────────────────────────────┐ ┌─────────────────────────────┐
│ ✅ Booking hoàn tất         │ │ ❌ Thanh toán thất bại      │
│ Email xác nhận gửi đến user │ │ Thông báo lỗi cho user      │
└─────────────────────────────┘ └─────────────────────────────┘
```

### Files liên quan
- **View**: `view/user/traveller/search.php`, `view/user/traveller/listing-detail.php`, `view/user/traveller/payment.php`, `view/payment/momo-ipn.php`
- **Controller**: `controller/cListing.php`, `controller/cBooking.php`, `controller/cPayment.php`
- **Model**: `model/mListing.php`, `model/mBooking.php`, `model/mPayment.php`
- **Database**: `bookings`, `payments`, `booking_services`

### Trạng thái Booking
- **pending**: Chờ thanh toán
- **confirmed**: Đã thanh toán, xác nhận
- **cancelled**: Đã hủy
- **completed**: Đã hoàn thành (sau check-out)

### Thanh toán MoMo
- **API Endpoint**: `https://test-payment.momo.vn/v2/gateway/api/create`
- **IPN Callback**: `view/payment/momo-ipn.php`
- **Signature**: HMAC SHA256

---

## 5. Luồng Đánh giá (Review)

### Mô tả
User hoàn thành chuyến đi, viết review, rating, upload ảnh. Nếu rating 5 sao thì cộng điểm tín nhiệm.

### Flow Chart
```
┌─────────────────────────────┐
│ User hoàn thành chuyến đi   │
│ Booking status = completed  │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ view/user/traveller/        │
│ my-bookings.php             │
│ Hiển thị nút "Đánh giá"     │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ User click "Đánh giá"       │
│ view/user/traveller/        │
│ write-review.php            │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ User nhập:                  │
│ • Rating (1-5 sao)          │
│ • Comment                   │
│ • Upload ảnh (optional)     │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cReview->cSubmitReview()    │
│ • Validate rating (1-5)     │
│ • Validate comment length   │
│ • Kiểm tra đã review chưa   │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mReview->mInsertReview()    │
│ • INSERT vào reviews table  │
│ • Lưu user_id, listing_id   │
│ • Lưu booking_id            │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Có ảnh?                     │
└──────────┬──────────────────┘
           │
      ┌────┴────┐
      │         │
     Có       Không
      │         │
      ▼         │
┌─────────────────────────────┐
│ cReview->                   │
│ cProcessReviewImages()      │
│ • Upload ảnh lên server     │
│ • Resize ảnh                │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mReview->                   │
│ mInsertReviewImages()       │
│ • INSERT vào review_images  │
└──────────┬──────────────────┘
           │
           ├─────────────────┐
           ▼                 │
┌─────────────────────────────┐
│ Rating = 5 sao?             │
└──────────┬──────────────────┘
           │
      ┌────┴────┐
      │         │
     Có       Không
      │         │
      ▼         │
┌─────────────────────────────┐
│ cUser->cAddScoreByAction()  │
│ • Action: 'receive_5_star'  │
│ • Cộng +3 điểm tín nhiệm    │
└──────────┬──────────────────┘
           │
           ├─────────────────┘
           ▼
┌─────────────────────────────┐
│ ✅ Review hoàn tất          │
│ Hiển thị trên listing       │
│ Cập nhật average rating     │
└─────────────────────────────┘
```

### Files liên quan
- **View**: `view/user/traveller/my-bookings.php`, `view/user/traveller/write-review.php`
- **Controller**: `controller/cReview.php`, `controller/cUser.php`
- **Model**: `model/mReview.php`, `model/mUserScore.php`
- **Database**: `reviews`, `review_images`

### Validation Rules
- **Rating**: 1-5 (bắt buộc)
- **Comment**: Max 500 ký tự (bắt buộc)
- **Images**: Tối đa 5 ảnh, mỗi ảnh max 5MB
- **Rule**: 1 booking chỉ được review 1 lần

---

## 6. Luồng Hỗ trợ (Support Ticket)

### Mô tả
User hoặc Guest tạo ticket hỗ trợ, Admin xem và trả lời qua hệ thống. Email tự động gửi khi Admin reply lần đầu.

### Flow Chart
```
┌─────────────────────────────┐
│ Ai cần hỗ trợ?              │
└──────────┬──────────────────┘
           │
      ┌────┴────┐
      │         │
      ▼         ▼
┌─────────┐ ┌─────────┐
│  User   │ │  Guest  │
│ (có TK) │ │(chưa TK)│
└────┬────┘ └────┬────┘
     │           │
     ▼           ▼
┌─────────────────────────────┐ ┌─────────────────────────────┐
│ view/user/traveller/        │ │ view/guest/                 │
│ support.php                 │ │ guest-support.php           │
│ Form: subject, category,    │ │ Form: name, email, subject, │
│       description           │ │       category, description │
└──────────┬──────────────────┘ └──────────┬──────────────────┘
           │                               │
           ▼                               ▼
┌─────────────────────────────┐ ┌─────────────────────────────┐
│ cSupport->cCreateTicket()   │ │ cSupport->                  │
│ • Validate subject          │ │ cCreateGuestTicket()        │
│ • Validate category         │ │ • Validate email            │
│ • Validate description      │ │ • Validate thông tin        │
└──────────┬──────────────────┘ └──────────┬──────────────────┘
           │                               │
           ▼                               ▼
┌─────────────────────────────┐ ┌─────────────────────────────┐
│ mSupport->mCreateTicket()   │ │ mSupport->                  │
│ • INSERT với user_id        │ │ mCreateGuestTicket()        │
│ • status = 'open'           │ │ • INSERT với guest_email    │
│ • priority = 'normal'       │ │ • status = 'open'           │
└──────────┬──────────────────┘ └──────────┬──────────────────┘
           │                               │
           └───────────┬───────────────────┘
                       │
                       ▼
┌─────────────────────────────┐
│ Admin xem ticket            │
│ view/admin/                 │
│ support-tickets.php         │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Admin click vào ticket      │
│ view/admin/                 │
│ support-detail.php          │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cAdmin->                    │
│ cGetTicketMessages()        │
│ • Lấy lịch sử tin nhắn      │
│ • Hiển thị conversation     │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Admin nhập reply            │
│ cAdmin->cReplyToTicket()    │
│ • Validate content          │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mAdmin->mReplyToTicket()    │
│ • INSERT vào ticket_messages│
│ • sender_type = 'admin'     │
│ • sender_id = admin_id      │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Kiểm tra: Là reply đầu?     │
│ (Kiểm tra có tin nhắn admin │
│  nào trước đó chưa)         │
└──────────┬──────────────────┘
           │
      ┌────┴────┐
      │         │
     Có       Không
      │         │
      ▼         │
┌─────────────────────────────┐
│ Gửi email thông báo         │
│ EmailHelper->sendEmail()    │
│ • Subject: "Admin đã reply" │
│ • Gửi đến user/guest email  │
└──────────┬──────────────────┘
           │
           ├─────────────────┘
           ▼
┌─────────────────────────────┐
│ Admin cập nhật status       │
│ cAdmin->                    │
│ cUpdateTicketStatus()       │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Chọn status mới:            │
│ • open → in_progress        │
│ • in_progress → resolved    │
│ • resolved → closed         │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ ✅ Ticket được xử lý        │
│ User/Guest nhận thông báo   │
└─────────────────────────────┘
```

### Files liên quan
- **View**: 
  - User: `view/user/traveller/support.php`
  - Guest: `view/guest/guest-support.php`
  - Admin: `view/admin/support-tickets.php`, `view/admin/support-detail.php`
- **Controller**: `controller/cSupport.php`, `controller/cAdmin.php`
- **Model**: `model/mSupport.php`, `model/mAdmin.php`
- **Database**: `support_tickets`, `ticket_messages`

### Categories
- **dat_phong**: Vấn đề về đặt phòng
- **tai_khoan**: Vấn đề về tài khoản
- **nha_cung_cap**: Vấn đề về host/listing
- **de_xuat_dich_vu**: Đề xuất dịch vụ mới
- **khac**: Khác

### Status Flow
```
open → in_progress → resolved → closed
```

### Email Logic
- **Chỉ gửi email khi Admin reply lần đầu tiên**
- Logic kiểm tra: Query `ticket_messages` WHERE `sender_type = 'admin'`
- Nếu không có record nào → Gửi email
- Nếu đã có → Không gửi (tránh spam)

---

## 7. Luồng Quản lý Doanh thu

### Mô tả
Host xem doanh thu của mình, Admin xem doanh thu toàn hệ thống.

### Flow Chart - Host Revenue

```
┌─────────────────────────────┐
│ Host đăng nhập              │
│ view/user/host/revenue.php  │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cRevenue->                  │
│ cGetHostRevenueByMonth()    │
│ • Lấy doanh thu theo tháng  │
│ • Input: host_id, tháng, năm│
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mRevenue->                  │
│ mGetHostRevenueByMonth()    │
│ • Query bookings table      │
│ • JOIN với payments         │
│ • WHERE host_id = ?         │
│ • WHERE status = confirmed  │
│ • GROUP BY month            │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cRevenue->                  │
│ cGetHostTopListings()       │
│ • Top 5 listing doanh thu   │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cRevenue->                  │
│ cGetHostBookingsByStatus()  │
│ • Thống kê booking theo     │
│   status (confirmed, cancel)│
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cRevenue->                  │
│ cGetHostRatingsDistribution()│
│ • Phân bố rating (1-5 sao)  │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ 📊 Hiển thị Dashboard       │
│ • Biểu đồ doanh thu         │
│ • Bảng top listings         │
│ • Chart booking status      │
│ • Chart rating distribution │
└─────────────────────────────┘
```

### Flow Chart - Admin System Revenue

```
┌─────────────────────────────┐
│ Admin đăng nhập             │
│ view/admin/revenue.php      │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cRevenue->                  │
│ cGetSystemOverview()        │
│ • Tổng doanh thu            │
│ • Tổng bookings             │
│ • Tổng users                │
│ • Tổng hosts                │
│ • Tổng listings             │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mRevenue->                  │
│ mGetSystemOverview()        │
│ • Query từ nhiều bảng       │
│ • Aggregate data            │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cRevenue->                  │
│ cGetSystemRevenueByMonth()  │
│ • Doanh thu hệ thống/tháng  │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cReport->cGetTopHosts()     │
│ • Top 10 host doanh thu cao │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cReport->                   │
│ cGetNewUsersByMonth()       │
│ • Số user đăng ký mới/tháng │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cReport->                   │
│ cGetNewListingsByMonth()    │
│ • Số listing tạo mới/tháng  │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ 📊 Hiển thị Dashboard Admin │
│ • Overview cards            │
│ • Biểu đồ doanh thu         │
│ • Bảng top hosts            │
│ • Chart user growth         │
│ • Chart listing growth      │
└─────────────────────────────┘
```

### Files liên quan
- **View**: 
  - Host: `view/user/host/revenue.php`
  - Admin: `view/admin/revenue.php`, `view/admin/reports.php`
- **Controller**: `controller/cRevenue.php`, `controller/cReport.php`
- **Model**: `model/mRevenue.php`, `model/mReport.php`
- **Database**: `bookings`, `payments`, `listings`, `users`, `hosts`

### Metrics
**Host:**
- Doanh thu theo tháng
- Top listings theo doanh thu
- Số booking theo status
- Phân bố rating

**Admin:**
- Tổng doanh thu hệ thống
- Tổng users/hosts/listings/bookings
- Doanh thu theo tháng
- Top hosts
- User growth
- Listing growth
- Doanh thu theo tỉnh thành

---

## 8. Luồng Điểm Tín nhiệm (Trust Score)

### Mô tả
Hệ thống tự động cộng/trừ điểm tín nhiệm dựa trên hành động của user. Điểm càng cao → độ tin cậy càng lớn.

### Flow Chart

```
┌─────────────────────────────┐
│ User thực hiện hành động    │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────────────────────┐
│ Các hành động trigger cộng/trừ điểm:        │
│ 1. Hoàn thành booking → +5 điểm             │
│ 2. Nhận review 5 sao → +3 điểm              │
│ 3. Hủy booking → -10 điểm                   │
│ 4. Xác thực email → +5 điểm                 │
│ 5. Xác thực phone → +5 điểm                 │
│ 6. Xác thực CMND → +10 điểm                 │
└──────────┬──────────────────────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ Ví dụ: User hoàn thành      │
│ booking thành công          │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cUser->cAddScoreByAction()  │
│ • userId = ?                │
│ • actionType =              │
│   'complete_booking'        │
│ • relatedType = 'booking'   │
│ • relatedId = booking_id    │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mUserScore->                │
│ mAddScoreByAction()         │
│ • Map action → điểm số      │
│ • complete_booking = +5     │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mUserScore->                │
│ mUpdateUserScore()          │
│ • UPDATE users table        │
│ • trust_score = score + 5   │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mUserScore->                │
│ mInsertScoreHistory()       │
│ • INSERT vào                │
│   user_score_history        │
│ • Log: +5, reason, action   │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ ✅ Điểm được cập nhật       │
└─────────────────────────────┘


┌─────────────────────────────┐
│ User xem điểm tín nhiệm     │
│ view/user/traveller/        │
│ trust-score.php             │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cUser->cGetUserScore()      │
│ • Lấy điểm hiện tại         │
│ • Lấy trạng thái xác thực   │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mUserScore->mGetUserScore() │
│ • Query users table         │
│ • Return: trust_score,      │
│   is_verified, verified_*   │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mUserScore->mGetUserLevel() │
│ • 0-49: Beginner 🌱         │
│ • 50-99: Regular 🌟         │
│ • 100-199: Trusted ⭐⭐     │
│ • 200+: Elite 👑            │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cUser->cGetScoreHistory()   │
│ • Lấy 20 record gần nhất    │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mUserScore->                │
│ mGetScoreHistory()          │
│ • Query user_score_history  │
│ • ORDER BY created_at DESC  │
│ • LIMIT 20                  │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ cUser->                     │
│ cGetImprovementSuggestions()│
│ • Phân tích điểm hiện tại   │
│ • Đưa ra gợi ý cải thiện    │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ mUserScore->                │
│ mGetImprovementSuggestions()│
│ • Kiểm tra verified status  │
│ • Gợi ý:                    │
│   - Xác thực email/phone/ID │
│   - Hoàn thành booking      │
│   - Nhận review tốt         │
└──────────┬──────────────────┘
           │
           ▼
┌─────────────────────────────┐
│ 📊 Hiển thị Trust Score     │
│ • Badge level hiện tại      │
│ • Điểm số chi tiết          │
│ • Lịch sử thay đổi          │
│ • Gợi ý cải thiện           │
│ • Progress bar tới level kế │
└─────────────────────────────┘
```

### Files liên quan
- **View**: `view/user/traveller/trust-score.php`
- **Controller**: `controller/cUser.php`
- **Model**: `model/mUserScore.php`
- **Database**: `users` (trust_score column), `user_score_history`

### Scoring Rules
| Hành động | Điểm số | Related Type |
|-----------|---------|--------------|
| Complete booking | +5 | booking |
| Receive 5-star review | +3 | review |
| Cancel booking | -10 | booking |
| Email verified | +5 | verification |
| Phone verified | +5 | verification |
| ID verified | +10 | verification |
| Admin bonus | +/- X | admin_action |

### Trust Levels
| Level | Score Range | Badge | Benefits |
|-------|-------------|-------|----------|
| Beginner | 0-49 | 🌱 | Cơ bản |
| Regular | 50-99 | 🌟 | Ưu tiên support |
| Trusted | 100-199 | ⭐⭐ | Giảm giá 5%, ưu tiên booking |
| Elite | 200+ | 👑 | VIP support, giảm 10% |

### Admin Manual Adjustment
Admin có thể thủ công cộng/trừ điểm:
```
cUser->cUpdateUserScore(userId, scoreChange, reason, adminId)
```
Use cases:
- Thưởng user tích cực: +20
- Phạt hành vi xấu: -50
- Điều chỉnh lỗi hệ thống

---

## 📂 Cấu trúc Database

### Core Tables
- **users**: Thông tin user, trust_score
- **hosts**: Thông tin host
- **listings**: Phòng/nhà cho thuê
- **bookings**: Đơn đặt phòng
- **payments**: Thanh toán
- **reviews**: Đánh giá
- **support_tickets**: Ticket hỗ trợ
- **user_score_history**: Lịch sử điểm

### Relationship
```
users 1---* bookings
hosts 1---* listings
listings 1---* bookings
bookings 1---1 payments
bookings 1---* reviews
users 1---* support_tickets
users 1---* user_score_history
```

---

## 🔐 Security & Validation

### Authentication
- **User**: Session-based, `$_SESSION['user_id']`
- **Host**: Kiểm tra `hosts` table có `user_id`
- **Admin**: Session-based, `$_SESSION['admin_id']`

### Middleware
- **requireLogin()**: Kiểm tra user đã login
- **requireHost()**: Kiểm tra user là host
- **requireAdmin()**: Kiểm tra admin đã login
- **checkAccountStatus()**: Kiểm tra tài khoản bị khóa

### Input Validation
- **Controller layer**: Validate input format, length, type
- **Model layer**: Prepare statements (PDO) → prevent SQL injection
- **File upload**: Validate file type, size, extension

---

## 📧 Email System

### PHPMailer Configuration
- **SMTP Host**: `smtp.gmail.com`
- **Port**: 587 (TLS)
- **From**: Email hệ thống WeGo

### Email Templates
1. **Verification Code**: Mã 6 số khi đăng ký
2. **Password Reset**: Link reset password
3. **Booking Confirmation**: Xác nhận đặt phòng
4. **Admin Reply**: Thông báo admin đã reply ticket

### Helper Class
`helper/EmailHelper.php`:
- `sendVerificationCode()`
- `sendPasswordReset()`
- `sendBookingConfirmation()`
- `sendEmail()` (generic)

---

## 📱 Payment Integration

### MoMo API
- **Environment**: Test
- **API Version**: v2
- **Endpoint**: `https://test-payment.momo.vn/v2/gateway/api/create`

### Payment Flow
1. User click "Thanh toán"
2. Backend tạo MoMo payment request
3. User scan QR hoặc thanh toán trên app
4. MoMo gọi IPN callback
5. Backend verify signature & update booking

### Security
- **Signature**: HMAC SHA256
- **Partner Code**: Unique per merchant
- **Access Key + Secret Key**: Bảo mật

---

## 📊 Reporting & Analytics

### Host Reports
- Doanh thu theo tháng
- Top listings
- Booking statistics
- Rating distribution

### Admin Reports
- System overview (users, hosts, listings, bookings, revenue)
- Revenue by month
- Top hosts
- New users/listings by month
- Revenue by province

### Charts
- Line chart: Revenue trend
- Bar chart: Booking status
- Pie chart: Rating distribution
- Area chart: User growth

---

## 🎯 Kết luận

Hệ thống WeGo bao gồm **8 luồng chính** hoạt động độc lập nhưng liên kết chặt chẽ:

1. ✅ **Đăng ký & Xác thực** → Tạo tài khoản an toàn
2. 🏠 **Đăng ký Host** → Kiểm duyệt người cho thuê
3. 📝 **Đăng Listing** → Quản lý phòng/nhà
4. 💳 **Đặt phòng** → Thanh toán MoMo tích hợp
5. ⭐ **Đánh giá** → Xây dựng uy tín
6. 💬 **Hỗ trợ** → Chăm sóc khách hàng
7. 💰 **Doanh thu** → Báo cáo & thống kê
8. 🏆 **Điểm tín nhiệm** → Gamification

Tất cả đều tuân thủ kiến trúc **MVC**:
- **View** → Giao diện người dùng
- **Controller** → Logic xử lý & validation
- **Model** → Tương tác database

---

*Document này được tạo tự động bởi GitHub Copilot*  
*Last updated: December 22, 2025*
