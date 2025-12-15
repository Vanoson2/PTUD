# Use Cases: Host Management

> Flow: View → Controller → Model → Database

---

## UC-05: Đăng ký trở thành Host
**Actor:** Verified User

### Flow Diagram
```
User (Browser)
  ↓ Fill host registration form
  ↓ Upload documents (ID card front/back, business license)
  ↓ Submit POST
View: view/user/host/register-host.php
  ↓ Receive form data + files
Helper: helper/auth.php
  → requireHost() - Auto-create pending host if needed
Controller: controller/cHost.php
  → cRegisterHost($userId, $data, $files)
  ↓ Validate input:
    - ID number: 9-12 digits
    - Phone: 10-11 digits  
    - Tax code: 10-13 digits (optional)
    - Address: required
    - Bank account: required
  ↓ Validate files:
    - MIME type: image/jpeg, image/png
    - Max size: 5MB each
    - Required: id_front, id_back
    - Optional: business_license
  ↓ Process file uploads
Controller: controller/cHost.php
  → processIdCardImages($files)
  ↓ Generate unique filename (user_id_type_timestamp.ext)
  ↓ Move to public/uploads/id_cards/
  ↓ Return file paths
Model: model/mHost.php
  → mCreateHostApplication($userId, $data)
Database: we_go.host_application
  ↓ INSERT INTO host_application (
      user_id, id_number, address, phone,
      bank_account, bank_name, tax_code,
      status='pending', created_at=NOW()
    )
  ↓ Get application_id
Model: model/mHost.php
  → mSaveHostDocument($applicationId, $docType, $filePath)
  ↓ For each document (id_front, id_back, business_license):
Database: we_go.host_document
  ↓ INSERT INTO host_document (
      application_id, doc_type, file_path, uploaded_at=NOW()
    )
Model: model/mHost.php
  → mCreatePendingHost($userId)
  ↓ Get user full_name
Database: we_go.host
  ↓ Check if host exists
  ↓ IF NOT EXISTS:
    INSERT INTO host (user_id, legal_name, tax_code, status='pending')
  ↓ ELSE:
    UPDATE host SET status='pending' (reactivate if was rejected)
View: view/user/host/register-host.php
  ↓ Set $_SESSION['is_host'] = true
  ↓ Redirect to my-listings.php
User (Browser)
  ← Display success message
  ← Can now create listings (with pending approval)
```

### Database Tables
- **host_application** (INSERT)
- **host_document** (INSERT)
- **host** (SELECT, INSERT/UPDATE)

### Files Involved
- **View:** `view/user/host/register-host.php`
- **Controller:** `controller/cHost.php`
- **Model:** `model/mHost.php`
- **Helper:** `helper/auth.php`

### File Upload Path
- **Location:** `public/uploads/id_cards/`
- **Naming:** `{user_id}_{doc_type}_{timestamp}.{ext}`
- **Example:** `123_id_front_1702654321.jpg`

---

## UC-06: Admin duyệt đơn đăng ký Host
**Actor:** Admin (Manager/SuperAdmin)

### Flow Diagram
```
Admin (Browser)
  ↓ View application list
View: view/user/admin/applications.php
Controller: controller/cAdmin.php
  → cGetAllHostApplications($page, $limit, $status)
Model: model/mAdmin.php
  → mGetAllHostApplications($page, $limit, $status)
Database: we_go.host_application + we_go.user (JOIN)
  ↓ SELECT ha.*, u.full_name, u.email, u.phone
    FROM host_application ha
    JOIN user u ON ha.user_id = u.user_id
    WHERE ha.status = ? (if filtered)
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
  ↓ Return list of applications

Admin clicks "View Detail"
  ↓ Open application detail page
View: view/user/admin/application-detail.php
  ↓ Load application data
Controller: controller/cAdmin.php
  → cGetApplicationDetail($applicationId)
Model: model/mAdmin.php
  → mGetApplicationDetail($applicationId)
Database: we_go.host_application + documents + user (JOIN)
  ↓ SELECT ha.*, u.*, 
      (SELECT GROUP_CONCAT(CONCAT(doc_type,'|',file_path)) 
       FROM host_document WHERE application_id=ha.application_id) as docs
    FROM host_application ha
    JOIN user u ON ha.user_id = u.user_id
    WHERE ha.application_id = ?
  ↓ Display application info + documents

Admin reviews and approves
  ↓ Click "Approve" button
  ↓ Submit POST (action='approve', application_id, admin_note)
View: view/user/admin/application-detail.php
Controller: controller/cAdmin.php
  → cApproveHostApplication($applicationId, $adminId, $adminNote)
Model: model/mAdmin.php
  → mApproveHostApplication($applicationId, $adminId, $adminNote)
Database: we_go.host_application
  ↓ UPDATE host_application SET
      status = 'approved',
      admin_id = ?,
      admin_note = ?,
      reviewed_at = NOW()
    WHERE application_id = ?
Model: model/mHost.php
  → mCreateHostFromApplication($applicationId)
Database: we_go.host_application
  ↓ SELECT * FROM host_application WHERE application_id = ?
Database: we_go.host
  ↓ SELECT host_id FROM host WHERE user_id = ?
  ↓ IF EXISTS:
    UPDATE host SET 
      status = 'approved',
      legal_name = ?,
      tax_code = ?,
      updated_at = NOW()
  ↓ ELSE:
    INSERT INTO host (
      user_id, legal_name, tax_code, 
      status='approved', created_at=NOW()
    )
Model: model/mEmailPHPMailer.php
  → sendHostApprovalEmail($userEmail, $userName)
  ↓ Send congratulations email
View: view/user/admin/application-detail.php
  ↓ Store success message in session (PRG pattern)
  ↓ Redirect to applications.php
Admin (Browser)
  ← Display success message
```

### OR: Admin rejects
```
Admin clicks "Reject"
  ↓ Submit POST (action='reject', application_id, reject_reason)
Controller: controller/cAdmin.php
  → cRejectHostApplication($applicationId, $adminId, $rejectReason)
Model: model/mAdmin.php
  → mRejectHostApplication($applicationId, $adminId, $rejectReason)
Database: we_go.host_application
  ↓ UPDATE host_application SET
      status = 'rejected',
      admin_id = ?,
      admin_note = ?,
      reviewed_at = NOW()
    WHERE application_id = ?
Database: we_go.host
  ↓ UPDATE host SET status = 'rejected' WHERE user_id = ?
Model: model/mEmailPHPMailer.php
  → sendHostRejectionEmail($userEmail, $userName, $reason)
  ↓ Redirect with message (PRG)
```

### Database Tables
- **host_application** (SELECT, UPDATE)
- **host_document** (SELECT)
- **host** (SELECT, INSERT/UPDATE)
- **user** (SELECT for JOIN)

### Files Involved
- **View:** `view/user/admin/applications.php`, `view/user/admin/application-detail.php`
- **Controller:** `controller/cAdmin.php`
- **Model:** `model/mAdmin.php`, `model/mHost.php`, `model/mEmailPHPMailer.php`

---

## UC-07: Admin quản lý Host
**Actor:** Admin (Manager/SuperAdmin)

### Flow Diagram
```
Admin (Browser)
  ↓ View host list
View: view/user/admin/hosts.php
Controller: controller/cAdmin.php
  → cGetAllHosts($page, $limit, $search)
Model: model/mAdmin.php
  → mGetAllHosts($page, $limit, $search)
Database: we_go.host + we_go.user (JOIN)
  ↓ SELECT h.*, u.full_name, u.email, u.phone,
      COUNT(l.listing_id) as listing_count
    FROM host h
    JOIN user u ON h.user_id = u.user_id
    LEFT JOIN listing l ON h.host_id = l.host_id
    WHERE (u.full_name LIKE ? OR u.email LIKE ?)
    GROUP BY h.host_id
    ORDER BY h.created_at DESC
    LIMIT ? OFFSET ?
  ↓ Return host list with stats

Admin suspends/activates host
  ↓ Click "Suspend" or "Activate"
  ↓ Submit POST (action='toggle_status', host_id)
View: view/user/admin/hosts.php
Controller: controller/cAdmin.php
  → cToggleHostStatus($hostId)
Model: model/mAdmin.php
  → mToggleHostStatus($hostId)
Database: we_go.host
  ↓ SELECT status FROM host WHERE host_id = ?
  ↓ Toggle: approved ↔ suspended
  ↓ UPDATE host SET status = ?, updated_at = NOW()
    WHERE host_id = ?
  ↓ Store message in session
  ↓ Redirect (PRG pattern)
Admin (Browser)
  ← Display updated status
```

### Database Tables
- **host** (SELECT, UPDATE)
- **user** (SELECT for JOIN)
- **listing** (COUNT for stats)

### Files Involved
- **View:** `view/user/admin/hosts.php`
- **Controller:** `controller/cAdmin.php`
- **Model:** `model/mAdmin.php`

---

## Host Status Flow

### Status Transitions
```
[No Application]
  ↓ User registers as host
[pending] ← Initial status (can create listings)
  ↓ Admin reviews
  ├─→ [approved] ← Fully approved (listings go live)
  └─→ [rejected] ← Rejected (cannot create listings)
        ↓ User can re-apply
      [pending] ← Resubmit

[approved]
  ↓ Admin suspends
[suspended] ← Temporary ban (listings hidden)
  ↓ Admin reactivates
[approved]
```

### Host Permissions by Status

| Status | Can Create Listings | Listings Visible | Can Manage Bookings |
|--------|---------------------|------------------|---------------------|
| pending | ✅ Yes | ❌ No | ✅ Yes (if any) |
| approved | ✅ Yes | ✅ Yes | ✅ Yes |
| rejected | ❌ No | ❌ No | ✅ Yes (existing only) |
| suspended | ❌ No | ❌ No | ✅ Yes (existing only) |

---

## Self-Healing Host Feature

**Location:** `helper/auth.php → requireHost()`

**Problem:** Session says `is_host = false` but database has host record

**Solution:** Auto-create or reactivate host record

**Flow:**
```
requireHost() called
  ↓ Check $_SESSION['is_host']
  ↓ If FALSE → Check database
Model: model/mHost.php → mGetHostByUserId()
  ↓ SELECT * FROM host WHERE user_id = ?
  ↓ If FOUND:
    - Set $_SESSION['is_host'] = true
    - return (fixed!)
  ↓ If NOT FOUND:
    - Auto-create pending host
    - INSERT INTO host (user_id, legal_name, status='pending')
    - Set $_SESSION['is_host'] = true
    - return (created!)
```

**Benefit:** User never gets stuck. System self-heals missing data.

---

**File:** `02-host-management.md`  
**Module:** Host Management  
**Last Updated:** December 15, 2025
