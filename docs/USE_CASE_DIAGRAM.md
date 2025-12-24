# SƠ ĐỒ USE CASE HỆ THỐNG WE GO
## (Dựa trên code thực tế - Updated 24/12/2025)

---

## 🎭 ACTORS (Người dùng hệ thống)

| Actor | Mô tả | Quyền hạn |
|-------|-------|-----------|
| **Guest** | Khách chưa đăng ký | Browse listings, view detail, support |
| **User** | Người dùng đã đăng ký | Login, profile, book, review |
| **Traveler** | User đặt phòng | Book listing, pay, review |
| **Host** | Chủ nhà cho thuê | Create listings, manage bookings |
| **Admin** | Quản trị viên | Approve hosts, manage users, support |
| **System** | Hệ thống tự động | Cron jobs, auto-cancel, scoring |
| **MoMo** | Cổng thanh toán | Process payments, IPN callback |

---

## 📋 DANH SÁCH USE CASES (17 Use Cases)

### 🔐 MODULE 1: AUTHENTICATION & USER (cUser.php)

#### UC-01: Đăng ký tài khoản
- **Actor:** Guest
- **Function:** `cRegisterUser($email, $phone, $password, $confirmPassword, $fullName)`
- **Flow:**
  1. Guest điền form đăng ký
  2. Validate: email format, phone (10-11 digits), password match
  3. Hash password với bcrypt
  4. INSERT vào `user` table (status='active', is_email_verified=0)
  5. Tạo 6-digit verification code (expires 15 min)
  6. Gửi email verification code
  7. Auto-login và redirect to verify-code.php
- **Output:** User account created (pending verification)
- **Liên quan:** → UC-02 (verify email)

#### UC-02: Xác thực email
- **Actor:** User (unverified)
- **Function:** `cVerifyCode($userId, $code)`
- **Flow:**
  1. User nhập 6-digit code
  2. Check: code match + not expired + not verified yet
  3. UPDATE user: is_email_verified=1, clear verify_code
  4. **Trust Score:** +5 điểm (action: 'verify_email')
  5. Auto-login và redirect to home
- **Output:** Email verified, +5 trust score
- **Liên quan:** UC-01 → UC-02 → UC-03

#### UC-03: Đăng nhập
- **Actor:** User
- **Function:** `cLoginUser($emailOrPhone, $password)`
- **Flow:**
  1. User nhập email/phone + password
  2. SELECT user WHERE (email=? OR phone=?) AND status='active'
  3. Verify password với password_verify()
  4. Check trust_score >= 30 (nếu <30 → locked)
  5. Tạo session: user_id, email, full_name, trust_score
  6. Load is_host status nếu có
- **Output:** Session created, redirect to returnUrl or home
- **Điều kiện:** Email verified, account active, trust_score >= 30

#### UC-04: Quên mật khẩu
- **Actor:** User
- **Functions:** 
  - `cSendPasswordResetEmail($email)` 
  - `cVerifyResetToken($token)`
  - `cResetPassword($token, $newPassword, $confirmPassword)`
- **Flow:**
  1. User nhập email → Gửi reset link (token expires 1 hour)
  2. User click link → Verify token
  3. User nhập new password → Hash và UPDATE
- **Output:** Password changed, can login with new password

#### UC-05: Đổi mật khẩu
- **Actor:** Logged-in User
- **Function:** `cChangePassword($userId, $currentPassword, $newPassword, $confirmPassword)`
- **Flow:**
  1. Verify current password
  2. Validate new password (min 6 chars, match confirm)
  3. Hash và UPDATE password_hash
- **Output:** Password changed

#### UC-06: Cập nhật profile
- **Actor:** User
- **Function:** `cUpdateUserProfile($userId, $fullName, $job, $hobbies, $location, $gender)`
- **Flow:**
  1. UPDATE user: full_name
  2. UPDATE/INSERT user_profile: job, hobbies, location, gender
- **Output:** Profile updated

---

### 🏠 MODULE 2: HOST MANAGEMENT (cHost.php)

#### UC-07: Đăng ký trở thành Host
- **Actor:** Verified User
- **Function:** `cCreateHostApplication($userId, $businessName, $taxCode, $bankAccount, $bankName)`
- **Flow:**
  1. User điền form host registration
  2. Upload documents: ID card (front/back), business license (optional)
  3. Encrypt tax_code với AES-256-CBC
  4. INSERT host_application (status='pending')
  5. Save documents to host_document table
  6. **Auto-create pending host** → INSERT host (status='pending')
  7. Set session is_host=true → Can create listings ngay
- **Output:** Application pending, can create listings immediately
- **Liên quan:** → UC-08 (admin approve)

#### UC-08: Admin duyệt đơn Host (cAdmin.php)
- **Actor:** Admin
- **Functions:**
  - `cGetAllHostApplications($page, $limit, $status)`
  - `cApproveHostApplication($applicationId, $adminId, $note)`
  - `cRejectHostApplication($applicationId, $adminId, $note)`
- **Flow:**
  1. Admin view application list (pending/approved/rejected)
  2. Click detail → View documents, info
  3. **Approve:**
     - UPDATE host_application: status='approved'
     - UPDATE host: status='approved'
     - UPDATE all listings: status='approved' (was pending)
     - Send approval email
  4. **Reject:**
     - UPDATE host_application: status='rejected'
     - UPDATE host: status='rejected'
     - UPDATE all listings: status='rejected'
     - Send rejection email with reason
- **Output:** Host approved/rejected, listings visible/hidden

---

### 🏘️ MODULE 3: LISTING MANAGEMENT (cHost.php + cListing.php)

#### UC-09: Host tạo Listing
- **Actor:** Host (approved or pending)
- **Function:** `cCreateListing($hostId, $data)`
- **Flow:**
  1. Host điền form: title, description, address, ward, place_type
  2. Pricing: base_price, cleaning_fee
  3. Capacity: guest_count, bedroom_count, bed_count, bathroom_count
  4. Upload images (max 10, first = primary)
  5. Select amenities & services
  6. **Status logic:**
     - Host approved → Listing status='approved' (visible ngay)
     - Host pending → Listing status='pending' (invisible, chờ host được duyệt)
  7. INSERT listing + listing_image + listing_amenity + listing_service
- **Output:** Listing created (approved/pending based on host status)

#### UC-10: Host chỉnh sửa Listing
- **Actor:** Host
- **Function:** `cUpdateListing($listingId, $hostId, ...)`
- **Flow:**
  1. Host edit listing info
  2. Re-upload images (optional)
  3. Update amenities & services
  4. UPDATE listing table
  5. Re-insert amenities/services (DELETE old + INSERT new)
- **Output:** Listing updated

#### UC-11: Host xóa Listing
- **Actor:** Host
- **Function:** `cDeleteListing($listingId, $hostId)`
- **Flow:**
  1. Check: No active bookings (confirmed/completed)
  2. UPDATE listing: status='deleted'
- **Output:** Listing soft-deleted (not visible)

---

### 🔍 MODULE 4: SEARCH & BOOKING (cListing.php + cBooking.php)

#### UC-12: Traveler tìm kiếm Listing
- **Actor:** Guest/User
- **Function:** `cSearchListingsWithFilters($location, $checkin, $checkout, $guests)`
- **Flow:**
  1. Guest nhập: location, dates, guests
  2. Complex query:
     - WHERE address LIKE %location%
     - AND guest_count >= guests
     - AND status='approved'
     - AND NOT IN (bookings with overlapping dates)
  3. JOIN host, listing_image (primary), amenities
  4. Calculate total_price = base_price * nights
  5. Return available listings
- **Output:** List of available listings with prices
- **Liên quan:** → UC-13 (view detail) → UC-14 (book)

#### UC-13: View Listing Detail
- **Actor:** Guest/User
- **Function:** `cGetListingDetail($listingId)`
- **Flow:**
  1. SELECT listing JOIN host, images, amenities, services
  2. Load reviews với avg ratings
  3. Get booked dates (calendar unavailability)
  4. Display host info, map, amenities
- **Output:** Full listing information
- **Liên quan:** UC-12 → UC-13 → UC-14

#### UC-14: Traveler đặt phòng
- **Actor:** Verified User (as Traveler)
- **Function:** `cCreateBooking($userId, $listingId, $checkin, $checkout, $guests, $services)`
- **Flow:**
  1. User select dates + guests + optional services
  2. Calculate: 
     - nights = diff(checkout, checkin)
     - base_amount = listing.base_price * nights
     - service_fee = SUM(selected services)
     - cleaning_fee = listing.cleaning_fee
     - total_amount = base_amount + service_fee + cleaning_fee
  3. INSERT booking:
     - status='pending'
     - payment_status='unpaid'
     - created_at=NOW()
     - **expires_at=NOW() + 10 minutes** ⏱️
  4. Generate unique booking code (BK + DATE + RANDOM)
  5. Redirect to retry-payment.php
- **Output:** Booking created (pending, expires 10 min)
- **Liên quan:** → UC-15 (payment)

---

### 💳 MODULE 5: PAYMENT (cPayment.php + MoMoHelper.php)

#### UC-15: Thanh toán qua MoMo
- **Actor:** Traveler
- **Functions:**
  - `cInitiateMoMoPayment($bookingId, $amount, ...)`
  - `cProcessMoMoIPN($ipnData)`
- **Flow:**
  
  **Part 1: User initiates payment**
  1. User click "Thanh toán ngay"
  2. Call MoMoHelper->createPayment():
     - amount (integer, no decimals)
     - orderId = "WEGO_{bookingId}_{timestamp}"
     - **orderExpireTime = 10** (10 minutes) ✅ Fixed!
     - returnUrl, ipnUrl, extraData
  3. Generate signature (HMAC SHA256)
  4. POST to MoMo API
  5. MoMo returns payUrl
  6. Redirect user to MoMo payment page
  
  **Part 2: MoMo IPN Callback (async)**
  7. MoMo sends IPN to `payment/momo-ipn.php`
  8. Verify signature
  9. **If resultCode = 0 (success):**
     - UPDATE booking: status='confirmed', payment_status='paid'
     - INSERT payment_transaction
     - INSERT invoice
     - **Trust Score:** +10 điểm (action: 'complete_booking')
     - Send confirmation email to user & host
  10. **If resultCode != 0 (failed):**
     - UPDATE booking: payment_status='failed'
     - Log error
  
  **Part 3: User returns from MoMo**
  11. MoMo redirects to `payment/momo-return.php`
  12. Display success/error message
  13. Button: "Xem booking" → my-bookings.php

- **Output:** 
  - Success → Booking confirmed, paid, +10 trust score
  - Failed → Booking still pending (can retry)
- **Liên quan:** UC-14 → UC-15 → UC-16 (auto-cancel if timeout)

#### UC-16: System hủy booking hết hạn (Auto)
- **Actor:** System (Cron job)
- **File:** `helper/cancel-expired-bookings.php`
- **Flow:**
  1. Run every minute (cron: `* * * * *`)
  2. SELECT bookings WHERE:
     - status='pending'
     - payment_status IN ('unpaid', 'pending')
     - expires_at < NOW()
  3. For each expired booking:
     - UPDATE booking: status='cancelled', cancel_reason='payment_timeout'
     - **NO trust score penalty** (timeout không phải lỗi user)
  4. Log cancelled bookings
- **Output:** Expired bookings auto-cancelled
- **Special:** NO penalty for timeout (fair policy)

---

### ⭐ MODULE 6: REVIEW (cReview.php)

#### UC-17: Đánh giá sau booking hoàn thành
- **Actor:** Traveler (completed booking)
- **Function:** `cSubmitReview($userId, $listingId, $bookingId, $rating, $comment, $filesData)`
- **Flow:**
  1. User click "Write Review" on completed booking
  2. Rate 6 criteria (1-5 stars each):
     - Cleanliness
     - Accuracy
     - Communication
     - Location
     - Check-in
     - Value
  3. Calculate overall_rating = AVG(6 ratings)
  4. Write comment (10-500 chars)
  5. Upload review images (optional, max 5)
  6. INSERT review (status='active')
  7. UPDATE listing:
     - average_rating = AVG(all reviews)
     - review_count = COUNT(reviews)
  8. **Trust Score:** +5 điểm (action: 'review_place')
  9. Send email to host (new review notification)
- **Output:** Review published, listing rating updated, +5 trust score
- **Điều kiện:** Booking status='completed', user_id matches, no existing review

---

### 🎫 MODULE 7: SUPPORT SYSTEM (cSupport.php)

#### UC-18: User gửi yêu cầu hỗ trợ
- **Actor:** User hoặc Guest
- **Functions:**
  - Guest: `cCreateGuestTicket($guestName, $guestEmail, $guestPhone, $title, $content, $category, $priority)`
  - User: `cCreateTicket($userId, $title, $content, $category, $priority)`
- **Flow:**
  1. Fill support form:
     - Category: booking, payment, listing, account, khac
     - Priority: low, normal, high, urgent
     - Title + Content
  2. INSERT support_ticket (status='open')
  3. INSERT support_message (sender_type='user')
  4. Send confirmation email (ticket created)
- **Output:** Ticket created, status='open'
- **Liên quan:** → UC-19 (admin reply)

#### UC-19: Admin xử lý Support Ticket
- **Actor:** Admin
- **Functions:**
  - `cAdminGetAllTickets($status, $category, $priority, $search)`
  - `cAdminReplyTicket($ticketId, $adminId, $content)`
  - `cAdminUpdateStatus($ticketId, $status)`
- **Flow:**
  1. Admin view ticket list (filter by status/category)
  2. Click ticket → View conversation
  3. Reply message:
     - INSERT support_message (sender_type='admin')
     - UPDATE ticket: status='in_progress'
     - **First admin reply:** Send email notification to user
  4. Mark resolved:
     - UPDATE ticket: status='resolved', resolved_at=NOW()
     - Send resolution email
- **Output:** Ticket resolved, user notified

---

### 👨‍💼 MODULE 8: ADMIN MANAGEMENT (cAdmin.php)

#### UC-20: Admin quản lý Users
- **Actor:** Admin
- **Functions:**
  - `cGetAllUsers($page, $limit, $search, $role)`
  - `cGetUserDetail($userId)`
  - `cSuspendUser($userId, $adminId, $reason)`
  - `cBanUser($userId, $adminId, $reason)`
- **Flow:**
  1. View user list (filter: role, status, trust score)
  2. Click user → View detail:
     - Profile info, verification status
     - Statistics: bookings, reviews, tickets
     - Trust score history
  3. **Suspend account:**
     - UPDATE user: status='suspended'
     - Send suspension email with reason
     - User cannot login (temporary)
  4. **Ban account:**
     - UPDATE user: status='banned'
     - Send ban email
     - User cannot login (permanent)
- **Output:** User suspended/banned

#### UC-21: Admin quản lý Listings
- **Actor:** Admin
- **Function:** `cGetAllListings($page, $limit, $status, $search)`
- **Flow:**
  1. View all listings (approved/pending/rejected)
  2. Approve/Reject pending listings
  3. Suspend active listings (policy violation)
- **Output:** Listing status changed

#### UC-22: Admin quản lý Amenities & Services
- **Actor:** Admin (cType&Amenties.php)
- **Functions:**
  - `cGetAllAmenities()`, `cInsertAmenity()`, `cUpdateAmenity()`, `cDeleteAmenity()`
  - `cGetAllServices()`, `cInsertService()`, `cUpdateService()`, `cDeleteService()`
- **Flow:** CRUD operations for amenities/services
- **Output:** Amenities/Services updated

---

### 📊 MODULE 9: REPORTS & REVENUE (cReport.php + cRevenue.php)

#### UC-23: Host xem báo cáo doanh thu
- **Actor:** Host
- **Functions (cRevenue.php):**
  - `cGetHostTotalRevenue($hostId, $startDate, $endDate)`
  - `cGetRevenueByListing($hostId, $startDate, $endDate)`
  - `cGetMonthlyRevenue($hostId, $year)`
  - `cGetBookingStatistics($hostId)`
- **Flow:**
  1. Host view dashboard
  2. See total revenue, revenue by listing
  3. Monthly revenue chart
  4. Booking statistics (count by status)
- **Output:** Revenue reports displayed

#### UC-24: Admin xem báo cáo hệ thống
- **Actor:** Admin
- **Functions (cReport.php):**
  - `cGetSystemOverview()`
  - `cGetSystemRevenueByMonth()`
  - `cGetTopHosts($limit)`
  - `cGetNewListingsByMonth()`
  - `cGetNewUsersByMonth()`
  - `cGetListingsByProvince($limit)`
- **Flow:**
  1. Admin view dashboard
  2. System overview: total users, hosts, listings, revenue
  3. Charts: monthly revenue, new users, new listings
  4. Top hosts by revenue
  5. Listings distribution by province
- **Output:** System analytics displayed

---

### 🏆 MODULE 10: TRUST SCORE SYSTEM (cUser.php + Auto)

#### UC-25: Trust Score Tự động
- **Actor:** System (triggered by actions)
- **Function:** `cAddScoreByAction($userId, $actionType, $relatedType, $relatedId)`
- **Score Rules (table: score_config):**

| Action | Score Change | Trigger |
|--------|--------------|---------|
| verify_email | +5 | UC-02 |
| complete_booking | +10 | UC-15 (payment success) |
| review_place | +5 | UC-17 |
| cancel_booking | -5 | Cancel unpaid >24h before |
| late_cancel_booking | -10 | Cancel unpaid <24h before |
| cancel_paid_booking | -15 | Cancel paid >24h before |
| late_cancel_paid_booking | -25 | Cancel paid <24h before |

- **Auto-lock:** trust_score < 30 → status='locked'
- **Warning:** 30 ≤ trust_score < 50 → Show warning

---

## 🔗 MỐI QUAN HỆ USE CASES

### Dependencies (Phụ thuộc - must complete first)
```
UC-01 (Register) ──► UC-02 (Verify) ──► UC-03 (Login)
                                            │
                          ┌─────────────────┴─────────────────┐
                          ▼                                   ▼
                    UC-07 (Become Host)              UC-12 (Search)
                          │                                   │
                          ▼                                   ▼
                    UC-08 (Approve)                    UC-13 (Detail)
                          │                                   │
                          ▼                                   ▼
                    UC-09 (Create Listing)             UC-14 (Book)
                          │                                   │
                          └──────► UC-12 (Search) ◄───────────┘
                                        │
                                        ▼
                                  UC-15 (Payment)
                                        │
                                        ▼
                                  UC-17 (Review)
```

### Extends (Mở rộng - optional)
```
UC-14 (Book) ──extends──► UC-15 (Payment)
UC-15 (Complete) ──extends──► UC-17 (Review)
Any UC ──extends──► UC-18 (Support)
```

### Includes (Bao gồm - always triggered)
```
UC-02 ──includes──► UC-25 (+5 trust)
UC-15 ──includes──► UC-25 (+10 trust)
UC-17 ──includes──► UC-25 (+5 trust)
Cancel Booking ──includes──► UC-25 (-5/-10/-15/-25 trust)
UC-16 ──includes──► Auto-cancel (NO penalty)
```

### Associations (Actor → Use Case)
```
Guest ────────── UC-01, UC-12, UC-13, UC-18
User ─────────── UC-03, UC-04, UC-05, UC-06, UC-12, UC-13, UC-14, UC-17, UC-18
Traveler ──────── UC-14, UC-15, UC-17
Host ──────────── UC-07, UC-09, UC-10, UC-11, UC-23
Admin ─────────── UC-08, UC-19, UC-20, UC-21, UC-22, UC-24
System ────────── UC-16, UC-25
MoMo ──────────── UC-15 (IPN callback)
```

---

## 📊 BẢNG TỔNG HỢP (25 Use Cases)

| ID | Use Case | Actor | Controller | Priority | Status |
|----|----------|-------|------------|----------|--------|
| UC-01 | Đăng ký | Guest | cUser | ⭐⭐⭐ | ✅ |
| UC-02 | Verify Email | User | cUser | ⭐⭐⭐ | ✅ |
| UC-03 | Login | User | cUser | ⭐⭐⭐ | ✅ |
| UC-04 | Reset Password | User | cUser | ⭐⭐ | ✅ |
| UC-05 | Change Password | User | cUser | ⭐⭐ | ✅ |
| UC-06 | Update Profile | User | cUser | ⭐⭐ | ✅ |
| UC-07 | Become Host | User | cHost | ⭐⭐⭐ | ✅ |
| UC-08 | Approve Host | Admin | cAdmin | ⭐⭐⭐ | ✅ |
| UC-09 | Create Listing | Host | cHost | ⭐⭐⭐ | ✅ |
| UC-10 | Edit Listing | Host | cHost | ⭐⭐ | ✅ |
| UC-11 | Delete Listing | Host | cHost | ⭐⭐ | ✅ |
| UC-12 | Search Listings | Guest/User | cListing | ⭐⭐⭐ | ✅ |
| UC-13 | View Detail | Guest/User | cListing | ⭐⭐⭐ | ✅ |
| UC-14 | Book Listing | Traveler | cBooking | ⭐⭐⭐ | ✅ |
| UC-15 | MoMo Payment | Traveler | cPayment | ⭐⭐⭐ | ✅ |
| UC-16 | Auto Cancel | System | Cron | ⭐⭐⭐ | ✅ |
| UC-17 | Write Review | Traveler | cReview | ⭐⭐ | ✅ |
| UC-18 | Create Ticket | User/Guest | cSupport | ⭐⭐ | ✅ |
| UC-19 | Handle Ticket | Admin | cSupport | ⭐⭐ | ✅ |
| UC-20 | Manage Users | Admin | cAdmin | ⭐⭐ | ✅ |
| UC-21 | Manage Listings | Admin | cAdmin | ⭐⭐ | ✅ |
| UC-22 | Manage Amenities | Admin | cType&Amenties | ⭐ | ✅ |
| UC-23 | Host Revenue | Host | cRevenue | ⭐⭐ | ✅ |
| UC-24 | System Reports | Admin | cReport | ⭐⭐ | ✅ |
| UC-25 | Trust Score | System | cUser | ⭐⭐⭐ | ✅ |

---

## 🎯 NOTES QUAN TRỌNG

### 1. Trust Score Penalties (4-tier system)
- **Unpaid booking:**
  - >24h: -5 điểm
  - <24h: -10 điểm
- **Paid booking:**
  - >24h: -15 điểm
  - <24h: -25 điểm
- **Timeout:** NO penalty (fair policy)

### 2. MoMo Integration
- **Timeout:** 10 minutes (fixed from bug)
- **IPN:** Async callback for payment status
- **Return URL:** Sync redirect for user display

### 3. Host Approval
- **Pending host CAN create listings** (invisible until host approved)
- **Approved host** → All listings become visible
- **Rejected host** → All listings hidden

### 4. Booking Expiration
- **Cron job:** Runs every minute
- **Auto-cancel:** Pending + unpaid + expires_at < NOW()
- **NO penalty** for timeout cancellations

---

**Generated:** 24/12/2025  
**Based on:** Actual codebase (controllers + models)  
**Total Use Cases:** 25  
**Modules:** 10
