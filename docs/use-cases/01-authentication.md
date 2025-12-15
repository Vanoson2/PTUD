# Use Cases: Authentication & User Management

> Flow: View → Controller → Model → Database

---

## UC-01: Đăng ký tài khoản mới
**Actor:** Guest User

### Flow Diagram
```
User (Browser)
  ↓ Fill form (email, phone, password, full_name)
  ↓ Submit POST
View: view/user/traveller/register.php
  ↓ Receive POST data
  ↓ Call controller
Controller: controller/cUser.php
  → cRegisterUser($email, $phone, $password, $fullName)
  ↓ Validate input:
    - Email format (filter_var)
    - Phone format (10-11 digits)
    - Password length (min 6 chars)
    - Full name (min 2 chars)
  ↓ Call model
Model: model/mUser.php
  → mRegisterUser($email, $phone, $password, $fullName)
  ↓ Hash password (password_hash with bcrypt)
  ↓ Escape data (real_escape_string)
Database: we_go.user
  ↓ INSERT INTO user (email, phone, password_hash, full_name, is_email_verified=0, status='active')
  ↓ Get user_id (insert_id)
Database: we_go.user_profile
  ↓ INSERT INTO user_profile (user_id, gender='unknown')
Model: model/mUser.php
  ↓ Generate 6-digit verification code
  ↓ Set expiry (NOW() + 15 minutes)
Database: we_go.user
  ↓ UPDATE user SET verify_version = ?, verification_code_expires = ?
Model: model/mEmailPHPMailer.php
  → sendVerificationEmail($email, $code, $fullName)
  ↓ Send email via SMTP
View: view/user/traveller/register.php
  ↓ Store user_id and email in session
  ↓ Redirect to verify-code.php?user_id=X&email=Y
User (Browser)
  ↓ Receive redirect
  ← Display verification page
```

### Database Tables
- **user** (INSERT, UPDATE)
- **user_profile** (INSERT)

### Files Involved
- **View:** `view/user/traveller/register.php`
- **Controller:** `controller/cUser.php`
- **Model:** `model/mUser.php`, `model/mEmailPHPMailer.php`

---

## UC-02: Xác thực email
**Actor:** Registered User

### Flow Diagram
```
User (Browser)
  ↓ Enter 6-digit code
  ↓ Submit POST (user_id, code)
View: view/user/traveller/verify-code.php
  ↓ Receive POST data
Controller: controller/cUser.php
  → cVerifyCode($userId, $code)
  ↓ Validate code (6 digits, numeric)
Model: model/mUser.php
  → mVerifyCode($userId, $code)
Database: we_go.user
  ↓ SELECT verify_version, verification_code_expires, is_email_verified
    FROM user WHERE user_id = ?
  ↓ Check conditions:
    1. is_email_verified == 0 (not verified yet)
    2. verify_version == code (code matches)
    3. verification_code_expires > NOW() (not expired)
  ↓ All checks pass
Database: we_go.user
  ↓ UPDATE user SET 
      is_email_verified = 1,
      verify_version = NULL,
      verification_code_expires = NULL
  ↓ Success
Model: model/mUserScore.php
  → mAddScoreByAction($userId, 'verify_email')
Database: we_go.score_config
  ↓ SELECT score_change FROM score_config 
    WHERE action_type = 'verify_email' AND is_active = 1
  ↓ Result: score_change = 5
Database: we_go.user
  ↓ SELECT trust_score FROM user WHERE user_id = ?
  ↓ Calculate: new_score = old_score + 5
  ↓ UPDATE user SET trust_score = new_score
Database: we_go.user_score_history
  ↓ INSERT INTO user_score_history 
      (user_id, score_change=5, old_score, new_score, 
       reason='Xác thực email thành công')
View: view/user/traveller/verify-code.php
  ↓ Set session variables:
    $_SESSION['user_id'] = user_id
    $_SESSION['user_email'] = email
    $_SESSION['user_name'] = full_name
    $_SESSION['is_email_verified'] = 1
  ↓ Redirect to index.php?verified=1
User (Browser)
  ↓ Logged in automatically
  ← Display home page with success message
```

### Database Tables
- **user** (SELECT, UPDATE)
- **score_config** (SELECT)
- **user_score_history** (INSERT)

### Files Involved
- **View:** `view/user/traveller/verify-code.php`
- **Controller:** `controller/cUser.php`
- **Model:** `model/mUser.php`, `model/mUserScore.php`

---

## UC-03: Đăng nhập
**Actor:** Registered User

### Flow Diagram
```
User (Browser)
  ↓ Enter email and password
  ↓ Submit POST
View: view/user/traveller/login.php
  ↓ Receive POST data
Controller: controller/cUser.php
  → cLoginUser($email, $password)
  ↓ Validate not empty
Model: model/mUser.php
  → mGetUserByEmail($email)
Database: we_go.user
  ↓ SELECT * FROM user 
    WHERE email = ? AND status = 'active'
  ↓ Found user
Model: model/mUser.php
  ↓ Verify password (password_verify)
  ↓ Password correct
  ↓ Check is_email_verified
  ↓ If not verified → Redirect to verify-code.php
  ↓ Verified → Continue
Model: model/mHost.php
  → mIsUserHost($userId)
Database: we_go.host
  ↓ SELECT host_id FROM host 
    WHERE user_id = ? AND status IN ('pending', 'approved')
  ↓ Result: is_host = true/false
View: view/user/traveller/login.php
  ↓ Set session variables:
    $_SESSION['user_id'] = user_id
    $_SESSION['user_email'] = email
    $_SESSION['user_name'] = full_name
    $_SESSION['user_phone'] = phone
    $_SESSION['is_email_verified'] = 1
    $_SESSION['is_host'] = is_host
  ↓ Redirect to index.php or previous page
User (Browser)
  ↓ Logged in
  ← Display home page
```

### Database Tables
- **user** (SELECT)
- **host** (SELECT)

### Files Involved
- **View:** `view/user/traveller/login.php`
- **Controller:** `controller/cUser.php`
- **Model:** `model/mUser.php`, `model/mHost.php`

---

## UC-04: Quên mật khẩu
**Actor:** Registered User

### Flow Diagram
```
User (Browser)
  ↓ Enter email
  ↓ Submit POST
View: view/user/traveller/forgot-password.php
  ↓ Receive email
Controller: controller/cUser.php
  → cRequestPasswordReset($email)
Model: model/mUser.php
  → mGetUserByEmail($email)
Database: we_go.user
  ↓ SELECT user_id, full_name FROM user WHERE email = ?
  ↓ Found user
Model: model/mUser.php
  ↓ Generate 6-digit reset code
  ↓ Set expiry (NOW() + 15 minutes)
Database: we_go.user
  ↓ UPDATE user SET 
      reset_version = ?,
      reset_code_expires = ?
    WHERE user_id = ?
Model: model/mEmailPHPMailer.php
  → sendPasswordResetEmail($email, $code, $fullName)
View: view/user/traveller/forgot-password.php
  ↓ Redirect to reset-password.php?email=X
User (Browser)
  ← Display reset code form
```

### Database Tables
- **user** (SELECT, UPDATE)

### Files Involved
- **View:** `view/user/traveller/forgot-password.php`, `view/user/traveller/reset-password.php`
- **Controller:** `controller/cUser.php`
- **Model:** `model/mUser.php`, `model/mEmailPHPMailer.php`

---

## Authentication Helper Functions

### requireLogin()
**Location:** `helper/auth.php`

**Purpose:** Ensure user is logged in

**Flow:**
```
Check session_status()
  ↓ If not active → session_start()
Check $_SESSION['user_id']
  ↓ If not set:
    - Store current URL in $_SESSION['redirect_after_login']
    - Redirect to login.php
    - exit()
  ↓ If set:
    - return (continue execution)
```

### requireHost()
**Location:** `helper/auth.php`

**Purpose:** Ensure user is a host

**Flow:**
```
Call requireLogin() first
Check $_SESSION['is_host']
  ↓ If TRUE → return (authorized)
  ↓ If FALSE → Check database
Model: model/mHost.php
  → mGetHostByUserId($userId)
Database: we_go.host
  ↓ SELECT host_id FROM host 
    WHERE user_id = ? AND status IN ('pending','approved')
  ↓ If found:
    - Set $_SESSION['is_host'] = true
    - return (authorized)
  ↓ If not found: Auto-create pending host (self-healing)
Model: model/mHost.php
  → mCreatePendingHost($userId)
  ↓ Get user full_name
Database: we_go.host
  ↓ INSERT INTO host (user_id, legal_name, status='pending')
  ↓ Set $_SESSION['is_host'] = true
  ↓ return (authorized)
```

---

## Security Notes

### Password Hashing
- Algorithm: bcrypt (PASSWORD_BCRYPT)
- Function: `password_hash($password, PASSWORD_BCRYPT)`
- Verification: `password_verify($password, $hash)`

### Session Management
- Session regenerate ID on login: `session_regenerate_id(true)`
- Session destroy on logout
- Session timeout check

### Input Validation
- Email: `filter_var($email, FILTER_VALIDATE_EMAIL)`
- Phone: Regex `/^[0-9]{10,11}$/`
- Password: Minimum 6 characters
- All database input: `mysqli_real_escape_string()`

### CSRF Protection
- Not implemented yet (TODO)

---

**File:** `01-authentication.md`  
**Module:** Authentication & User Management  
**Last Updated:** December 15, 2025
