# Use Cases: Admin Management & Trust Score

> Flow: View → Controller → Model → Database

---

## UC-14: Admin quản lý Users
**Actor:** Admin (Manager/SuperAdmin)

### Flow Diagram
```
Admin (Browser)
  ↓ View user management page
View: view/user/admin/users.php
Controller: controller/cAdmin.php
  → cGetAllUsers($page, $limit, $search, $role)
Model: model/mAdmin.php
  → mGetAllUsers($page, $limit, $search, $role)
Database: we_go.user
  ↓ SELECT user_id, full_name, email, phone,
      role, status, trust_score, is_email_verified,
      created_at,
      (SELECT COUNT(*) FROM booking WHERE user_id=u.user_id) as booking_count
    FROM user u
    WHERE (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)
      AND (role = ? OR ? IS NULL)
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
  ↓ Return user list with stats
View: view/user/admin/users.php
  ← Display user table:
    - Avatar, Name, Email, Phone
    - Role badge (SuperAdmin, Manager, etc.)
    - Status badge (active, suspended, banned)
    - Trust score (color-coded)
    - Booking count
    - Actions: View, Edit, Suspend/Ban

Admin clicks "View User"
  ↓ View user detail
View: view/user/admin/user-detail.php
Controller: controller/cAdmin.php
  → cGetUserDetail($userId)
Model: model/mAdmin.php
  → mGetUserDetail($userId)
Database: we_go.user + related tables (JOIN)
  ↓ SELECT u.*,
      (SELECT COUNT(*) FROM booking WHERE user_id=u.user_id) as total_bookings,
      (SELECT COUNT(*) FROM review WHERE user_id=u.user_id) as total_reviews,
      (SELECT COUNT(*) FROM support_ticket WHERE user_id=u.user_id) as total_tickets
    FROM user u
    WHERE u.user_id = ?
Model: model/mUserScore.php
  → mGetUserScoreHistory($userId)
Database: we_go.user_score_history
  ↓ SELECT * FROM user_score_history
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 20
  ↓ Return recent score changes
View: view/user/admin/user-detail.php
  ← Display:
    - User profile (avatar, info, verification status)
    - Statistics (bookings, reviews, tickets)
    - Trust score breakdown
    - Recent activities
    - Account actions

Admin suspends user
  ↓ Click "Suspend Account"
  ↓ Enter reason and submit POST
Controller: controller/cAdmin.php
  → cSuspendUser($userId, $adminId, $reason)
  ↓ Validate reason: 10-500 chars
Model: model/mAdmin.php
  → mSuspendUser($userId, $adminId, $reason)
Database: we_go.user
  ↓ UPDATE user SET
      status = 'suspended',
      updated_at = NOW()
    WHERE user_id = ?
Database: we_go.admin_action_log
  ↓ INSERT INTO admin_action_log (
      admin_id, action_type='suspend_user',
      target_user_id=?, reason, created_at=NOW()
    )
Model: model/mEmailPHPMailer.php
  → sendAccountSuspensionEmail($userEmail, $reason)
  ← Notify user about suspension
View: view/user/admin/user-detail.php
  ↓ Redirect with message (PRG pattern)
Admin (Browser)
  ← Display success message
  ← User cannot log in until reactivated
```

### Database Tables
- **user** (SELECT, UPDATE)
- **booking** (COUNT for stats)
- **review** (COUNT for stats)
- **support_ticket** (COUNT for stats)
- **user_score_history** (SELECT for breakdown)
- **admin_action_log** (INSERT)

### Files Involved
- **View:** 
  - `view/user/admin/users.php`
  - `view/user/admin/user-detail.php`
- **Controller:** `controller/cAdmin.php`
- **Model:** 
  - `model/mAdmin.php`
  - `model/mUserScore.php`
  - `model/mEmailPHPMailer.php`

### User Status Transitions
```
[active] ← Normal user (default)
  ↓ Admin suspends
[suspended] ← Temporary ban (can appeal)
  ↓ Admin reactivates OR bans permanently
  ├─→ [active] ← Reactivated
  └─→ [banned] ← Permanent ban (cannot reactivate)
```

### User Roles & Permissions
| Role | Can Book | Can Host | Can Manage | Can Delete |
|------|----------|----------|------------|------------|
| User | ✅ Yes | ❌ No | ❌ No | ❌ No |
| Host | ✅ Yes | ✅ Yes | ❌ No | ❌ No |
| Support | ✅ Yes | ❌ No | ⚠️ Tickets only | ❌ No |
| Manager | ✅ Yes | ✅ Yes | ⚠️ Most features | ❌ No |
| SuperAdmin | ✅ Yes | ✅ Yes | ✅ Everything | ✅ Yes |

---

## UC-15: Admin quản lý Amenities
**Actor:** Admin (Manager/SuperAdmin)

### Flow Diagram
```
Admin (Browser)
  ↓ View amenities management page
View: view/user/admin/amenities.php
Controller: controller/cAdmin.php
  → cGetAllAmenities()
Model: model/mAdmin.php
  → mGetAllAmenities()
Database: we_go.amenity
  ↓ SELECT a.*,
      COUNT(la.listing_id) as usage_count
    FROM amenity a
    LEFT JOIN listing_amenity la ON a.amenity_id = la.amenity_id
    GROUP BY a.amenity_id
    ORDER BY a.category, a.amenity_name
  ↓ Return amenities with usage stats
View: view/user/admin/amenities.php
  ← Display amenity table:
    - Icon, Name, Category
    - Usage count (how many listings use it)
    - Actions: Edit, Delete

Admin adds new amenity
  ↓ Click "Add New Amenity"
  ↓ Fill form:
    - Name: "Swimming Pool"
    - Category: "Facilities"
    - Icon: "fa-swimming-pool"
    - Description: "Private pool for guests"
  ↓ Submit POST
Controller: controller/cAdmin.php
  → cCreateAmenity($data)
  ↓ Validate:
    - Name: 2-50 chars, unique
    - Category: required
    - Icon: valid Font Awesome class
Model: model/mAdmin.php
  → mCreateAmenity($data)
Database: we_go.amenity
  ↓ INSERT INTO amenity (
      amenity_name, category, icon_class,
      description, created_at=NOW()
    )
View: view/user/admin/amenities.php
  ↓ Redirect with message (PRG pattern)
Admin (Browser)
  ← Display success message
  ← New amenity available for hosts

Admin deletes amenity
  ↓ Click "Delete" (only if usage_count=0)
  ↓ Confirm deletion
Controller: controller/cAdmin.php
  → cDeleteAmenity($amenityId)
  ↓ Check if amenity is used
Model: model/mAdmin.php
  → mCheckAmenityUsage($amenityId)
Database: we_go.listing_amenity
  ↓ SELECT COUNT(*) FROM listing_amenity
    WHERE amenity_id = ?
  ↓ If COUNT > 0 → Error (cannot delete in-use amenity)
Model: model/mAdmin.php
  → mDeleteAmenity($amenityId)
Database: we_go.amenity
  ↓ DELETE FROM amenity WHERE amenity_id = ?
View: view/user/admin/amenities.php
  ↓ Redirect with message (PRG pattern)
Admin (Browser)
  ← Display success message
```

### Database Tables
- **amenity** (SELECT, INSERT, DELETE)
- **listing_amenity** (COUNT for usage check)

### Files Involved
- **View:** `view/user/admin/amenities.php`
- **Controller:** `controller/cAdmin.php`
- **Model:** `model/mAdmin.php`

### Amenity Categories
- **Basic:** Wifi, Air Conditioning, Heating, Kitchen
- **Facilities:** Pool, Gym, Parking, Garden
- **Safety:** Smoke Detector, Fire Extinguisher, First Aid Kit
- **Entertainment:** TV, Netflix, Board Games

---

## UC-16: Trust Score System
**Background Process:** Automatic scoring based on user actions

### Trust Score Actions & Points

| Action | Score Change | Triggered When | Model Function |
|--------|--------------|----------------|----------------|
| verify_email | **+5** | Email verified | `mAddScoreByAction()` |
| complete_profile | **+10** | Avatar + bio added | `mAddScoreByAction()` |
| book_place | **+10** | Booking confirmed | `mAddScoreByAction()` |
| review_place | **+5** | Review submitted | `mAddScoreByAction()` |
| host_approved | **+20** | Host application approved | `mAddScoreByAction()` |
| complete_booking | **+15** | Booking completed | `mAddScoreByAction()` |
| cancel_booking | **-10** | Booking cancelled by user | `mDeductScoreByAction()` |
| violation | **-30** | Rule violation reported | `mDeductScoreByAction()` |
| spam_report | **-50** | Spam/abuse confirmed | `mDeductScoreByAction()` |

### Flow Diagram - Automatic Scoring
```
User Action Occurs
  ↓ (e.g., User verifies email)
Controller: Any relevant controller
  → Calls scoring function after main action
Model: model/mUserScore.php
  → mAddScoreByAction($userId, $action)
  ↓ Get action configuration
Database: we_go.score_action_config (optional table)
  ↓ SELECT score_change, description
    FROM score_action_config
    WHERE action_type = ?
  ↓ (OR) Hardcoded in mUserScore.php
Model: model/mUserScore.php
  → mUpdateUserScore($userId, $scoreChange, $action)
Database: we_go.user_score_history
  ↓ INSERT INTO user_score_history (
      user_id, action, score_change,
      previous_score, new_score,
      created_at=NOW()
    )
Database: we_go.user
  ↓ UPDATE user SET
      trust_score = trust_score + ?,
      updated_at = NOW()
    WHERE user_id = ?
  ↓ Score updated immediately
```

### Trust Score Display Logic
```php
// In view files
if ($trust_score >= 80) {
    $badge = '<span class="badge badge-success">Trusted</span>';
    $color = 'green';
} elseif ($trust_score >= 50) {
    $badge = '<span class="badge badge-info">Verified</span>';
    $color = 'blue';
} elseif ($trust_score >= 20) {
    $badge = '<span class="badge badge-warning">New</span>';
    $color = 'orange';
} else {
    $badge = '<span class="badge badge-danger">Unverified</span>';
    $color = 'red';
}
```

### Trust Score Badges
| Score Range | Badge | Color | Meaning |
|-------------|-------|-------|---------|
| 80-100 | Trusted | Green | Highly reliable user |
| 50-79 | Verified | Blue | Active, verified user |
| 20-49 | New | Orange | New or inactive user |
| 0-19 | Unverified | Red | Unverified or problematic |

### Database Tables
- **user** (UPDATE trust_score)
- **user_score_history** (INSERT)
- **score_action_config** (SELECT - optional)

### Files Involved
- **Model:** `model/mUserScore.php`
- **Helper:** `helper/score_display.php` (optional)
- **Called From:** Multiple controllers (cUser, cBooking, cReview, cHost, cAdmin)

### Score Calculation Example
```
New User:
  Initial: 0 points

After Registration Flow:
  Email verified: +5 → 5 points (Unverified)
  Complete profile: +10 → 15 points (Unverified)
  
After First Booking:
  Book place: +10 → 25 points (New)
  Complete booking: +15 → 40 points (New)
  Review place: +5 → 45 points (New)

After Becoming Host:
  Host approved: +20 → 65 points (Verified)

After Multiple Activities:
  5 more bookings: +5×(10+15+5) = +150 → 215 points (Trusted)
```

### Score History Query
```sql
-- Get user's score history
SELECT * FROM user_score_history
WHERE user_id = ?
ORDER BY created_at DESC;

-- Get score breakdown by action
SELECT action, SUM(score_change) as total_points, COUNT(*) as count
FROM user_score_history
WHERE user_id = ?
GROUP BY action
ORDER BY total_points DESC;
```

### Admin Override (Manual Adjustment)
```
Admin (Browser)
  ↓ On user-detail.php
  ↓ Click "Adjust Score"
  ↓ Enter adjustment (+/- points) and reason
Controller: controller/cAdmin.php
  → cAdjustUserScore($userId, $adminId, $adjustment, $reason)
Model: model/mUserScore.php
  → mAdminAdjustScore($userId, $adminId, $adjustment, $reason)
Database: we_go.user_score_history
  ↓ INSERT INTO user_score_history (
      user_id, action='admin_adjustment',
      score_change=?, admin_note=?, created_at=NOW()
    )
Database: we_go.user
  ↓ UPDATE user SET trust_score = trust_score + ?
Database: we_go.admin_action_log
  ↓ INSERT INTO admin_action_log (
      admin_id, action_type='adjust_score',
      target_user_id=?, reason, created_at=NOW()
    )
```

---

## System Architecture Summary

### MVC Pattern Implementation

**Directory Structure:**
```
PTUD/
├── view/
│   └── user/
│       ├── admin/          # Admin panel views
│       ├── host/           # Host management views
│       └── traveller/      # Traveler booking views
├── controller/
│   ├── cUser.php           # User authentication
│   ├── cAdmin.php          # Admin operations
│   ├── cHost.php           # Host management
│   ├── cListing.php        # Listing CRUD
│   ├── cBooking.php        # Booking flow
│   ├── cPayment.php        # Payment processing
│   ├── cReview.php         # Review system
│   └── cSupport.php        # Support tickets
├── model/
│   ├── mUser.php           # User data access
│   ├── mAdmin.php          # Admin queries
│   ├── mHost.php           # Host queries
│   ├── mListing.php        # Listing queries
│   ├── mBooking.php        # Booking queries
│   ├── mPaymentMoMo.php    # MoMo integration
│   ├── mPaymentLog.php     # Payment logging
│   ├── mReview.php         # Review queries
│   ├── mSupport.php        # Support queries
│   ├── mUserScore.php      # Trust score logic
│   └── mEmailPHPMailer.php # Email sending
├── helper/
│   ├── auth.php            # Authentication helpers
│   ├── validator.php       # Input validation
│   └── file_upload.php     # File handling
├── public/
│   └── uploads/
│       ├── avatars/        # User avatars
│       ├── id_cards/       # Host ID documents
│       └── listings/       # Listing images
└── database/
    └── schema/
        └── we_go.sql       # Database schema
```

### Request Flow Pattern (PRG - Post/Redirect/Get)
```
User submits form (POST)
  ↓
Controller validates & processes
  ↓
Model updates database
  ↓
Store success/error message in $_SESSION
  ↓
Redirect to GET page (header("Location: ..."))
  ↓
View displays message from session
  ↓
Clear session message
```

**Benefit:** Prevents duplicate submissions on page refresh

### Database Design Principles
- **Foreign Keys:** Maintain referential integrity
- **LEFT JOIN:** Handle NULL relationships (guests, optional fields)
- **Indexes:** On frequently queried columns (user_id, listing_id, status)
- **ENUM:** For fixed options (status, role, priority)
- **Timestamps:** created_at, updated_at for audit trail

### Security Measures
1. **Password Hashing:** `password_hash()` with bcrypt
2. **SQL Injection Prevention:** PDO prepared statements
3. **XSS Prevention:** `htmlspecialchars()` in views
4. **CSRF Protection:** Session tokens on forms
5. **File Upload Validation:** MIME type + size checks
6. **Role-Based Access Control:** `requireAdmin()`, `requireHost()`

---

**File:** `05-admin-trust-score.md`  
**Module:** Admin Management & Trust Score System  
**Last Updated:** December 15, 2025
