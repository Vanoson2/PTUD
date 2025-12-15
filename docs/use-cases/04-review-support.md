# Use Cases: Review & Support System

> Flow: View → Controller → Model → Database

---

## UC-11: Đánh giá sau khi hoàn thành booking
**Actor:** Traveler (completed booking)

### Flow Diagram
```
Traveler (Browser)
  ↓ After check-out date
  ↓ View "My Bookings"
View: view/user/traveller/bookings.php
Controller: controller/cBooking.php
  → cGetUserBookings($userId, $status)
Model: model/mBooking.php
  → mGetUserBookings($userId, $status)
Database: we_go.booking + listing + host (JOIN)
  ↓ SELECT b.*, l.title, l.address,
      h.legal_name as host_name,
      li.image_url as listing_image,
      (SELECT COUNT(*) FROM review WHERE booking_id=b.booking_id) as has_review
    FROM booking b
    JOIN listing l ON b.listing_id = l.listing_id
    JOIN host h ON l.host_id = h.host_id
    LEFT JOIN listing_image li ON l.listing_id=li.listing_id AND li.is_primary=1
    WHERE b.user_id = ? AND b.status = 'completed'
    ORDER BY b.check_out DESC
  ↓ Return bookings (with has_review flag)
View: view/user/traveller/bookings.php
  ← Display "Write Review" button if has_review=0

Traveler clicks "Write Review"
  ↓ Open review form
View: view/user/traveller/write-review.php
  ↓ Display:
    - Booking details (listing, dates)
    - Rating sliders (1-5 stars):
      • Cleanliness
      • Accuracy
      • Communication
      • Location
      • Check-in
      • Value
    - Comment textarea
  ↓ Fill form and submit POST
Controller: controller/cReview.php
  → cSubmitReview($userId, $bookingId, $data)
  ↓ Validate:
    - User is traveler of this booking
    - Booking status = 'completed'
    - No existing review for this booking
    - All ratings: 1-5
    - Comment: 10-500 chars
Model: model/mBooking.php
  → mGetBookingById($bookingId)
Database: we_go.booking
  ↓ SELECT * FROM booking WHERE booking_id = ?
  ↓ Verify user_id matches & status='completed'
Model: model/mReview.php
  → mCheckExistingReview($userId, $bookingId)
Database: we_go.review
  ↓ SELECT COUNT(*) FROM review 
    WHERE user_id = ? AND booking_id = ?
  ↓ If COUNT > 0 → Error (already reviewed)
Model: model/mReview.php
  → mCreateReview($userId, $listingId, $bookingId, $data)
  ↓ Calculate overall_rating (average of 6 ratings)
Database: we_go.review
  ↓ INSERT INTO review (
      user_id, listing_id, booking_id,
      cleanliness_rating, accuracy_rating,
      communication_rating, location_rating,
      checkin_rating, value_rating,
      overall_rating, comment,
      status='active', created_at=NOW()
    )
  ↓ Get review_id
Model: model/mListing.php
  → mUpdateListingRating($listingId)
Database: we_go.review
  ↓ Calculate averages:
    SELECT AVG(overall_rating) as avg_rating,
           COUNT(*) as review_count
    FROM review
    WHERE listing_id = ? AND status='active'
Database: we_go.listing
  ↓ UPDATE listing SET
      average_rating = ?,
      review_count = ?
    WHERE listing_id = ?
Model: model/mUserScore.php
  → mAddScoreByAction($userId, 'review_place')
Database: we_go.user_score_history + user
  ↓ INSERT INTO user_score_history (
      user_id, action='review_place', score_change=5
    )
  ↓ UPDATE user SET trust_score = trust_score + 5
Model: model/mEmailPHPMailer.php
  → sendNewReviewNotificationEmail($hostEmail, $reviewData)
  ← Notify host about new review
View: view/user/traveller/write-review.php
  ↓ Store success message (PRG pattern)
  ↓ Redirect to bookings.php
Traveler (Browser)
  ← Display success message
  ← Review appears on listing page
```

### Database Tables
- **booking** (SELECT for validation)
- **review** (SELECT for duplicate check, INSERT)
- **listing** (UPDATE average_rating & review_count)
- **user_score_history** (INSERT)
- **user** (UPDATE trust_score)

### Files Involved
- **View:** 
  - `view/user/traveller/bookings.php`
  - `view/user/traveller/write-review.php`
- **Controller:** `controller/cReview.php`
- **Model:** 
  - `model/mReview.php`
  - `model/mBooking.php`
  - `model/mListing.php`
  - `model/mUserScore.php`
  - `model/mEmailPHPMailer.php`

### Review Rating Breakdown
| Category | Weight | Description |
|----------|--------|-------------|
| Cleanliness | 1/6 | How clean was the place? |
| Accuracy | 1/6 | Did it match the listing? |
| Communication | 1/6 | How responsive was host? |
| Location | 1/6 | Was location convenient? |
| Check-in | 1/6 | Was check-in smooth? |
| Value | 1/6 | Was it worth the price? |
| **Overall** | **Avg** | **Average of 6 ratings** |

### Trust Score Impact
- **+5 points** for writing a review
- Encourages engagement
- No penalty for negative reviews (honest feedback valued)

---

## UC-12: User gửi yêu cầu hỗ trợ
**Actor:** Any User (Guest or Logged-in)

### Flow Diagram
```
User (Browser)
  ↓ Click "Support" link in footer
View: view/user/support.php
  ↓ Display support form:
    - Subject (dropdown):
      • Account issue
      • Booking problem
      • Payment issue
      • Technical problem
      • Other
    - Message (textarea)
    - Email (if guest)
    - Phone (optional)
  ↓ Fill form and submit POST
Controller: controller/cSupport.php
  → cCreateSupportTicket($data)
  ↓ Validate:
    - Subject: required, valid option
    - Message: 20-1000 chars
    - Email: valid format (if provided)
    - Phone: 10-11 digits (if provided)
  ↓ Determine user:
    - If logged in: user_id from session
    - If guest: user_id = NULL
Model: model/mSupport.php
  → mCreateSupportTicket($userId, $data)
Database: we_go.support_ticket
  ↓ INSERT INTO support_ticket (
      user_id,        -- NULL if guest
      subject,
      message,
      status='pending',
      priority='normal',
      created_at=NOW()
    )
  ↓ Get ticket_id
Model: model/mEmailPHPMailer.php
  → sendSupportTicketCreatedEmail($userEmail, $ticketId)
  ← Send confirmation email
  → sendAdminNotificationEmail($adminEmail, $ticketData)
  ← Notify admin team
View: view/user/support.php
  ↓ Store success message (PRG pattern)
  ↓ Display ticket_id
  ↓ "We'll respond within 24 hours"
User (Browser)
  ← Display confirmation
  ← Ticket ID for tracking
```

### Database Tables
- **support_ticket** (INSERT)

### Files Involved
- **View:** `view/user/support.php`
- **Controller:** `controller/cSupport.php`
- **Model:** 
  - `model/mSupport.php`
  - `model/mEmailPHPMailer.php`

### Support Ticket Priorities
| Priority | Auto-assigned When | Response Time |
|----------|-------------------|---------------|
| low | Subject: "Other" | 48 hours |
| normal | Most subjects (default) | 24 hours |
| high | "Payment issue", "Booking problem" | 12 hours |
| urgent | Keyword: "urgent", "emergency" | 4 hours |

---

## UC-13: Admin xử lý ticket hỗ trợ
**Actor:** Admin (Support/Manager/SuperAdmin)

### Flow Diagram
```
Admin (Browser)
  ↓ View support tickets dashboard
View: view/user/admin/support.php
Controller: controller/cAdmin.php
  → cGetAllSupportTickets($page, $limit, $status)
Model: model/mAdmin.php
  → mGetAllSupportTickets($page, $limit, $status)
Database: we_go.support_ticket + user (LEFT JOIN)
  ↓ SELECT st.*, 
      u.full_name, u.email
    FROM support_ticket st
    LEFT JOIN user u ON st.user_id = u.user_id
    WHERE st.status = ? (if filtered)
    ORDER BY 
      FIELD(st.priority, 'urgent', 'high', 'normal', 'low'),
      st.created_at ASC
    LIMIT ? OFFSET ?
  ↓ Return tickets sorted by priority & age
View: view/user/admin/support.php
  ← Display ticket list:
    - Priority badge (urgent=red, high=orange)
    - Subject & preview
    - User name (or "Guest")
    - Created time
    - Status badge

Admin clicks ticket
  ↓ View ticket detail
View: view/user/admin/support-detail.php
Controller: controller/cAdmin.php
  → cGetTicketDetail($ticketId)
Model: model/mAdmin.php
  → mGetTicketDetail($ticketId)
Database: we_go.support_ticket + user (LEFT JOIN)
  ↓ SELECT st.*, 
      u.full_name, u.email, u.phone
    FROM support_ticket st
    LEFT JOIN user u ON st.user_id = u.user_id
    WHERE st.ticket_id = ?
Model: model/mSupport.php
  → mGetTicketMessages($ticketId)
Database: we_go.support_message
  ↓ SELECT sm.*, 
      u.full_name as sender_name,
      u.avatar as sender_avatar
    FROM support_message sm
    JOIN user u ON sm.sender_id = u.user_id
    WHERE sm.ticket_id = ?
    ORDER BY sm.created_at ASC
  ↓ Return conversation thread
View: view/user/admin/support-detail.php
  ← Display:
    - Ticket info (subject, message, user)
    - Conversation thread
    - Reply form
    - Action buttons: Close, Escalate

Admin replies
  ↓ Type message and submit POST
Controller: controller/cAdmin.php
  → cReplyToTicket($ticketId, $adminId, $message)
  ↓ Validate message: 1-2000 chars
Model: model/mSupport.php
  → mAddTicketMessage($ticketId, $senderId, $message, $isAdmin)
Database: we_go.support_message
  ↓ INSERT INTO support_message (
      ticket_id, sender_id, message,
      is_admin_reply=1, created_at=NOW()
    )
Database: we_go.support_ticket
  ↓ UPDATE support_ticket SET
      status = 'in_progress',
      last_reply_at = NOW(),
      last_reply_by = ?
    WHERE ticket_id = ?
Model: model/mEmailPHPMailer.php
  → sendTicketReplyEmail($userEmail, $ticketId, $message)
  ← Notify user about reply
View: view/user/admin/support-detail.php
  ↓ Redirect to same page (PRG pattern)
  ← Display updated conversation

Admin closes ticket
  ↓ Click "Close Ticket"
  ↓ Submit POST (action='close', resolution_note)
Controller: controller/cAdmin.php
  → cCloseTicket($ticketId, $adminId, $resolutionNote)
Model: model/mSupport.php
  → mCloseTicket($ticketId, $adminId, $resolutionNote)
Database: we_go.support_ticket
  ↓ UPDATE support_ticket SET
      status = 'resolved',
      resolved_by = ?,
      resolved_at = NOW(),
      resolution_note = ?
    WHERE ticket_id = ?
Model: model/mEmailPHPMailer.php
  → sendTicketClosedEmail($userEmail, $ticketId, $resolutionNote)
  ← Notify user ticket closed
View: view/user/admin/support-detail.php
  ↓ Redirect to support.php (PRG pattern)
Admin (Browser)
  ← Display success message
  ← Ticket removed from pending list
```

### Database Tables
- **support_ticket** (SELECT, UPDATE)
- **support_message** (SELECT, INSERT)
- **user** (SELECT for JOIN)

### Files Involved
- **View:** 
  - `view/user/admin/support.php`
  - `view/user/admin/support-detail.php`
- **Controller:** `controller/cAdmin.php`
- **Model:** 
  - `model/mAdmin.php`
  - `model/mSupport.php`
  - `model/mEmailPHPMailer.php`

### Support Ticket Status Flow
```
[No Ticket]
  ↓ User submits support request
[pending] ← Awaiting admin response (yellow badge)
  ↓ Admin replies
[in_progress] ← Admin is handling (blue badge)
  ↓ Admin closes
[resolved] ← Issue resolved (green badge)
  ↓ (Optional) User reopens
[in_progress] ← Back to active
```

### Priority Sorting Algorithm
```sql
ORDER BY 
  FIELD(priority, 'urgent', 'high', 'normal', 'low'),
  created_at ASC
```
**Result:** urgent first, then high, then normal, then low. Within each priority, oldest first.

### Bug Fix History (Dec 13, 2024)
**Original Query (BROKEN):**
```sql
SELECT st.*, u.full_name, u.email,
       st.guest_name, st.guest_email, st.guest_phone  -- ❌ Columns don't exist
FROM support_ticket st
LEFT JOIN user u ON st.user_id = u.user_id
```
**Error:** `Unknown column 'st.guest_name' in 'field list'`

**Fixed Query:**
```sql
SELECT st.*, u.full_name, u.email
FROM support_ticket st
LEFT JOIN user u ON st.user_id = u.user_id
```
**Reason:** 
- Schema defines guest columns but database table doesn't have them
- Migrations were never applied
- Fixed by removing non-existent columns from SELECT

**Files Modified:**
- `model/mAdmin.php` lines 610, 652

**Result:** Support tickets now display correctly (20 tickets visible)

---

**File:** `04-review-support.md`  
**Module:** Review & Support System  
**Last Updated:** December 15, 2025
