# Use Cases: Listing & Booking

> Flow: View → Controller → Model → Database

---

## UC-08: Host tạo Listing mới
**Actor:** Approved Host

### Flow Diagram
```
Host (Browser)
  ↓ Click "Create New Listing"
View: view/user/host/add-listing.php
  ↓ Fill form:
    - Basic: title, description, address
    - Capacity: guest_count, bedroom_count, bed_count, bathroom_count
    - Pricing: base_price, cleaning_fee
    - Amenities: checkboxes (wifi, kitchen, pool, parking, etc.)
    - Images: Multiple file upload (up to 10 images)
  ↓ Submit POST
Controller: controller/cListing.php
  → cCreateListing($hostId, $data, $files)
  ↓ Validate input:
    - title: 10-200 chars
    - description: 50-2000 chars
    - base_price: > 0
    - guest_count, bedroom_count: > 0
  ↓ Process images
Controller: controller/cListing.php
  → processListingImages($files)
  ↓ For each image:
    - Validate MIME type (jpeg, png, webp)
    - Max size: 5MB
    - Generate unique filename
    - Move to public/uploads/listings/
  ↓ Return array of paths
Model: model/mListing.php
  → mCreateListing($hostId, $data)
Database: we_go.listing
  ↓ INSERT INTO listing (
      host_id, title, description, address,
      latitude, longitude, guest_count,
      bedroom_count, bed_count, bathroom_count,
      base_price, cleaning_fee,
      status='pending', created_at=NOW()
    )
  ↓ Get listing_id
Model: model/mListing.php
  → mSaveListingImages($listingId, $imagePaths)
  ↓ For each image (up to 10):
Database: we_go.listing_image
  ↓ INSERT INTO listing_image (
      listing_id, image_url, is_primary,
      uploaded_at=NOW()
    )
    (First image: is_primary=1, rest=0)
Model: model/mListing.php
  → mSaveListingAmenities($listingId, $amenityIds)
  ↓ For each selected amenity:
Database: we_go.listing_amenity
  ↓ INSERT INTO listing_amenity (listing_id, amenity_id)
View: view/user/host/add-listing.php
  ↓ Store success message (PRG pattern)
  ↓ Redirect to my-listings.php
Host (Browser)
  ← Display success message
  ← Listing awaits admin approval
```

### Database Tables
- **listing** (INSERT)
- **listing_image** (INSERT multiple)
- **listing_amenity** (INSERT multiple)

### Files Involved
- **View:** `view/user/host/add-listing.php`
- **Controller:** `controller/cListing.php`
- **Model:** `model/mListing.php`

### Image Upload
- **Path:** `public/uploads/listings/`
- **Naming:** `listing_{listing_id}_{timestamp}_{random}.{ext}`
- **Max:** 10 images per listing
- **Primary:** First uploaded image is featured image

---

## UC-09: Traveler tìm kiếm và đặt phòng
**Actor:** Verified User (Traveler)

### Flow Diagram - Part 1: Search
```
Traveler (Browser)
  ↓ Enter search criteria
View: view/user/traveller/search-results.php
  ↓ Form: destination, check_in, check_out, guests
  ↓ Submit GET request
Controller: controller/cListing.php
  → cSearchListings($filters)
  ↓ Validate dates:
    - check_in >= today
    - check_out > check_in
  ↓ Calculate nights
Model: model/mListing.php
  → mSearchAvailableListings($filters)
Database: Complex query with availability check
  ↓ SELECT l.*, h.legal_name as host_name,
      li.image_url as primary_image,
      (l.base_price * nights) as total_price
    FROM listing l
    JOIN host h ON l.host_id = h.host_id
    LEFT JOIN listing_image li ON l.listing_id = li.listing_id AND li.is_primary=1
    WHERE l.status = 'approved'
      AND l.guest_count >= ?
      AND l.address LIKE ?
      AND l.listing_id NOT IN (
        -- Exclude listings with overlapping bookings
        SELECT listing_id FROM booking
        WHERE status IN ('confirmed', 'completed')
          AND NOT (check_out < ? OR check_in > ?)
      )
    ORDER BY l.created_at DESC
  ↓ Return available listings
View: view/user/traveller/search-results.php
  ← Display listing cards with price
```

### Flow Diagram - Part 2: View Detail & Confirm
```
Traveler clicks listing
  ↓ View listing detail
View: view/user/traveller/listing-detail.php
Controller: controller/cListing.php
  → cGetListingDetail($listingId)
Model: model/mListing.php
  → mGetListingById($listingId)
Database: we_go.listing + images + amenities + host (JOIN)
  ↓ SELECT l.*, h.legal_name, h.user_id as host_user_id,
      GROUP_CONCAT(DISTINCT li.image_url) as images,
      GROUP_CONCAT(DISTINCT a.amenity_name) as amenities
    FROM listing l
    JOIN host h ON l.host_id = h.host_id
    LEFT JOIN listing_image li ON l.listing_id = li.listing_id
    LEFT JOIN listing_amenity la ON l.listing_id = la.listing_id
    LEFT JOIN amenity a ON la.amenity_id = a.amenity_id
    WHERE l.listing_id = ?
    GROUP BY l.listing_id
Model: model/mListing.php
  → mGetListingReviews($listingId)
Database: we_go.review + user (JOIN)
  ↓ SELECT r.*, u.full_name, u.avatar
    FROM review r
    JOIN user u ON r.user_id = u.user_id
    WHERE r.listing_id = ? AND r.status = 'active'
    ORDER BY r.created_at DESC
    LIMIT 5
  ↓ Calculate average rating
View: view/user/traveller/listing-detail.php
  ← Display: images, description, amenities, price, reviews

Traveler clicks "Book Now"
  ↓ Redirected to booking confirmation
View: view/user/traveller/confirm-booking.php
  ↓ Display booking summary:
    - Listing info
    - Dates: check_in, check_out, nights
    - Price breakdown:
      • Base price × nights
      • Cleaning fee
      • Service fee (5%)
      • Total
  ↓ Fill guest info (if needed)
  ↓ Click "Confirm Booking"
  ↓ Submit POST (listing_id, check_in, check_out, guests, guest_info)
Controller: controller/cBooking.php
  → cCreateBooking($userId, $data)
  ↓ Verify availability again
Model: model/mBooking.php
  → mCheckListingAvailability($listingId, $checkIn, $checkOut)
Database: we_go.booking
  ↓ SELECT COUNT(*) FROM booking
    WHERE listing_id = ?
      AND status IN ('confirmed', 'completed')
      AND NOT (check_out < ? OR check_in > ?)
  ↓ If COUNT > 0 → Return error (unavailable)
  ↓ Calculate total_price
Model: model/mBooking.php
  → mCreateBooking($userId, $listingId, $data)
Database: we_go.booking
  ↓ INSERT INTO booking (
      user_id, listing_id, check_in, check_out,
      guest_count, total_price, status='pending',
      created_at=NOW()
    )
  ↓ Get booking_id
View: view/user/traveller/confirm-booking.php
  ↓ Redirect to payment page (PRG pattern)
  ↓ Pass booking_id
```

### Database Tables
- **listing** (SELECT with complex filters)
- **listing_image** (SELECT for JOIN)
- **listing_amenity** (SELECT for JOIN)
- **amenity** (SELECT for JOIN)
- **booking** (SELECT for availability check, INSERT)
- **review** (SELECT for ratings)
- **host** (SELECT for JOIN)

### Files Involved
- **View:** 
  - `view/user/traveller/search-results.php`
  - `view/user/traveller/listing-detail.php`
  - `view/user/traveller/confirm-booking.php`
- **Controller:** 
  - `controller/cListing.php`
  - `controller/cBooking.php`
- **Model:** 
  - `model/mListing.php`
  - `model/mBooking.php`

### Availability Logic
**Algorithm:**
```sql
NOT IN (
  SELECT listing_id FROM booking
  WHERE status IN ('confirmed', 'completed')
    AND NOT (check_out < check_in_search OR check_in > check_out_search)
)
```
**Explanation:**
- Exclude listings with overlapping bookings
- Only consider confirmed/completed bookings (ignore cancelled)
- Overlap check: NOT (booking ends before search OR booking starts after search)

---

## UC-10: Thanh toán qua MoMo
**Actor:** Traveler with Pending Booking

### Flow Diagram
```
Traveler (Browser)
  ↓ On payment page after creating booking
View: view/user/traveller/payment.php
  ↓ Display:
    - Booking details
    - Total amount
    - Payment method options
  ↓ Select "MoMo Wallet"
  ↓ Click "Pay Now"
  ↓ Submit POST (booking_id, payment_method='momo')
Controller: controller/cPayment.php
  → cInitiateMoMoPayment($bookingId)
Model: model/mBooking.php
  → mGetBookingById($bookingId)
Database: we_go.booking + listing (JOIN)
  ↓ SELECT b.*, l.title as listing_title
    FROM booking b
    JOIN listing l ON b.listing_id = l.listing_id
    WHERE b.booking_id = ? AND b.status = 'pending'
  ↓ Verify booking is pending
Model: model/mPaymentMoMo.php
  → createMoMoPaymentRequest($bookingId, $amount, $orderInfo)
  ↓ Prepare MoMo API request:
    - partnerCode: from config
    - orderId: "WEGO_{booking_id}_{timestamp}"
    - amount: total_price
    - orderInfo: "Payment for {listing_title}"
    - redirectUrl: /payment-return.php
    - ipnUrl: /payment-ipn.php (webhook)
    - requestType: "captureWallet"
    - signature: HMAC-SHA256
  ↓ Send POST to https://test-payment.momo.vn/v2/gateway/api/create
MoMo API
  ← Response: payUrl, qrCodeUrl
Model: model/mPaymentLog.php
  → mCreatePaymentLog($bookingId, $data)
Database: we_go.payment_log
  ↓ INSERT INTO payment_log (
      booking_id, payment_method='momo',
      transaction_id=orderId, amount,
      status='pending', request_data=JSON,
      created_at=NOW()
    )
View: view/user/traveller/payment.php
  ↓ Redirect user to MoMo payUrl
Traveler (MoMo App)
  ↓ Opens MoMo payment page
  ↓ Confirms payment
MoMo Server
  ↓ Sends IPN webhook to /payment-ipn.php
Controller: controller/cPayment.php
  → cHandleMoMoIPN($_POST)
  ↓ Verify signature (HMAC-SHA256)
  ↓ Check resultCode:
    - 0 = Success
    - Other = Failed
Model: model/mPaymentLog.php
  → mUpdatePaymentStatus($orderId, $status, $response)
Database: we_go.payment_log
  ↓ UPDATE payment_log SET
      status = ?,
      response_data = ?,
      completed_at = NOW()
    WHERE transaction_id = ?
  ↓ If SUCCESS:
Model: model/mBooking.php
    → mConfirmBooking($bookingId)
Database: we_go.booking
    ↓ UPDATE booking SET
        status = 'confirmed',
        confirmed_at = NOW()
      WHERE booking_id = ?
Model: model/mUserScore.php
    → mAddScoreByAction($userId, 'book_place')
Database: we_go.user_score_history + user
    ↓ INSERT INTO user_score_history (
        user_id, action='book_place', score_change=10
      )
    ↓ UPDATE user SET trust_score = trust_score + 10
Model: model/mEmailPHPMailer.php
    → sendBookingConfirmationEmail($userEmail, $bookingDetails)
    ← Send confirmation email to traveler
    → sendNewBookingNotificationEmail($hostEmail, $bookingDetails)
    ← Send notification email to host
MoMo Server
  ← Return 200 OK (acknowledge webhook)

Traveler redirected to /payment-return.php
  ↓ Check payment status
View: view/user/traveller/payment-return.php
Controller: controller/cPayment.php
  → cHandleMoMoReturn($_GET)
  ↓ Verify signature
  ↓ Check resultCode
Model: model/mBooking.php
  → mGetBookingById($bookingId)
Database: we_go.booking
  ↓ SELECT * FROM booking WHERE booking_id = ?
  ↓ Verify status = 'confirmed'
View: view/user/traveller/payment-return.php
  ← Display success message
  ← Show booking confirmation
  ← Link to "My Bookings"
```

### Database Tables
- **booking** (SELECT, UPDATE to confirmed)
- **payment_log** (INSERT, UPDATE)
- **user_score_history** (INSERT)
- **user** (UPDATE trust_score)

### Files Involved
- **View:** 
  - `view/user/traveller/payment.php`
  - `view/user/traveller/payment-return.php`
- **Controller:** `controller/cPayment.php`
- **Model:** 
  - `model/mPaymentMoMo.php`
  - `model/mPaymentLog.php`
  - `model/mBooking.php`
  - `model/mUserScore.php`
  - `model/mEmailPHPMailer.php`

### MoMo Integration Details

**Config Location:** `config/config.php`
```php
'momo' => [
    'partnerCode' => 'MOMO_PARTNER_CODE',
    'accessKey' => 'MOMO_ACCESS_KEY',
    'secretKey' => 'MOMO_SECRET_KEY',
    'endpoint' => 'https://test-payment.momo.vn/v2/gateway/api/create'
]
```

**Signature Calculation:**
```php
$rawHash = "accessKey=" . $accessKey 
         . "&amount=" . $amount
         . "&extraData=" . $extraData
         . "&ipnUrl=" . $ipnUrl
         . "&orderId=" . $orderId
         . "&orderInfo=" . $orderInfo
         . "&partnerCode=" . $partnerCode
         . "&redirectUrl=" . $redirectUrl
         . "&requestId=" . $requestId
         . "&requestType=" . $requestType;
$signature = hash_hmac("sha256", $rawHash, $secretKey);
```

**MoMo Result Codes:**
- `0` - Success
- `1006` - Transaction failed
- `1017` - User cancelled
- `9000` - System error

### Payment Status Flow
```
[No Booking]
  ↓ Create booking
[pending] ← Awaiting payment
  ↓ User pays via MoMo
  ├─→ [confirmed] ← Payment success (+10 trust score)
  └─→ [pending] ← Payment failed/cancelled (can retry)

[confirmed]
  ↓ Check-out date passed
[completed] ← Host marks as completed
  ↓ Review period
[reviewed] ← Both parties reviewed
```

---

**File:** `03-listing-booking.md`  
**Module:** Listing & Booking  
**Last Updated:** December 15, 2025
