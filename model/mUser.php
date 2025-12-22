<?php 
include_once(__DIR__ . "/mConnect.php");

class mUser {
    
    // Kiểm tra email đã tồn tại trong hệ thống chưa
    // string $email - Email cần kiểm tra
    // return bool - true nếu email đã tồn tại
    public function mCheckEmailExists($email) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if ($conn) {
            $email = $conn->real_escape_string($email);
            $sql = "SELECT user_id FROM user WHERE email = '$email' LIMIT 1";
            $result = $conn->query($sql);
            
            $exists = ($result && $result->num_rows > 0);
            $p->mDongKetNoi($conn);
            return $exists;
        }
        return false;
    }
    
    // Kiểm tra số điện thoại đã tồn tại trong hệ thống chưa
    // string $phone - Số điện thoại cần kiểm tra
    // return bool - true nếu phone đã tồn tại
    public function mCheckPhoneExists($phone) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if ($conn) {
            $phone = $conn->real_escape_string($phone);
            $sql = "SELECT user_id FROM user WHERE phone = '$phone' LIMIT 1";
            $result = $conn->query($sql);
            
            $exists = ($result && $result->num_rows > 0);
            $p->mDongKetNoi($conn);
            return $exists;
        }
        return false;
    }
    
    // Đăng ký tài khoản user mới
    // Hash password bằng bcrypt, khởi tạo trust_score = 100
    // Tự động tạo mã xác thực email 6 số
    // string $email - Email (unique)
    // string $phone - Số điện thoại (unique)
    // string $password - Mật khẩu (plain text, sẽ hash bcrypt)
    // string $fullName - Họ tên
    // return array - ['success' => bool, 'message' => string, 'user_id' => int, 'verification_code' => string]
    public function mRegisterUser($email, $phone, $password, $fullName) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database',
                'user_id' => null
            ];
        }
        
        // Hash mật khẩu
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        // Escape dữ liệu
        $email = $conn->real_escape_string($email);
        $phone = $conn->real_escape_string($phone);
        $fullName = $conn->real_escape_string($fullName);
        
        // Insert vào bảng user
        $sql = "INSERT INTO user (email, phone, password_hash, full_name, is_email_verified, status) 
                VALUES ('$email', '$phone', '$passwordHash', '$fullName', 0, 'active')";
        
        if ($conn->query($sql)) {
            $userId = $conn->insert_id;
            
            // Tạo user_profile mặc định
            $sqlProfile = "INSERT INTO user_profile (user_id, gender) 
                          VALUES ($userId, 'unknown')";
            $conn->query($sqlProfile);
            
            $p->mDongKetNoi($conn);
            return [
                'success' => true,
                'message' => 'Đăng ký thành công',
                'user_id' => $userId
            ];
        } else {
            $error = $conn->error;
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Lỗi database: ' . $error,
                'user_id' => null
            ];
        }
    }
    
    // Lấy thông tin user theo email
    // string $email - Email user
    // return array|null - Thông tin user hoặc null nếu không tìm thấy
    public function mGetUserByEmail($email) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if ($conn) {
            $email = $conn->real_escape_string($email);
            $sql = "SELECT u.*, up.job, up.hobbies, up.location, up.gender 
                    FROM user u
                    LEFT JOIN user_profile up ON u.user_id = up.user_id
                    WHERE u.email = '$email' 
                    LIMIT 1";
            
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $p->mDongKetNoi($conn);
                return $user;
            }
            
            $p->mDongKetNoi($conn);
        }
        return null;
    }
    
    // Tạo mã xác thực email 6 chữ số
    // Lưu vào bảng email_verification với thời hạn 15 phút
    // int $userId - ID user cần tạo mã xác thực
    // return string|null - Mã 6 số hoặc null nếu lỗi
    public function mGenerateVerifyCode($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if ($conn) {
            // Tạo mã 6 số ngẫu nhiên
            $verifyCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Thời gian hết hạn: 15 phút
            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Lưu mã vào database
            $sql = "UPDATE user 
                    SET verify_version = '$verifyCode',
                        verification_code_expires = '$expiresAt',
                        updated_at = CURRENT_TIMESTAMP
                    WHERE user_id = $userId";
            
            if ($conn->query($sql)) {
                $p->mDongKetNoi($conn);
                return $verifyCode;
            }
            
            $p->mDongKetNoi($conn);
        }
        return false;
    }
    
    // Xác thực mã code email
    // Kiểm tra code đúng và còn hạn (15 phút)
    // Cập nhật is_email_verified = 1 và thêm +5 điểm tín nhiệm
    // int $userId - ID user
    // string $code - Mã 6 số cần xác thực
    // return array - ['success' => bool, 'message' => string]
    public function mVerifyCode($userId, $code) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database'
            ];
        }
        
        $code = $conn->real_escape_string($code);
        
        // Lấy thông tin user
        $sql = "SELECT verify_version, verification_code_expires, is_email_verified 
                FROM user 
                WHERE user_id = $userId LIMIT 1";
        $result = $conn->query($sql);
        
        if (!$result || $result->num_rows === 0) {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Người dùng không tồn tại'
            ];
        }
        
        $user = $result->fetch_assoc();
        
        // Kiểm tra đã verify chưa
        if ($user['is_email_verified'] == 1) {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Email đã được xác thực trước đó'
            ];
        }
        
        // Kiểm tra mã có đúng không
        if ($user['verify_version'] != $code) {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Mã xác thực không đúng'
            ];
        }
        
        // Kiểm tra mã có hết hạn chưa
        if (strtotime($user['verification_code_expires']) < time()) {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Mã xác thực đã hết hạn. Vui lòng gửi lại mã mới'
            ];
        }
        
        // Update is_email_verified = 1 và xóa mã
        $updateSql = "UPDATE user 
                      SET is_email_verified = 1, 
                          verify_version = NULL,
                          verification_code_expires = NULL,
                          updated_at = CURRENT_TIMESTAMP
                      WHERE user_id = $userId";
        
        $updateResult = $conn->query($updateSql);
        
        if ($updateResult) {
            $p->mDongKetNoi($conn);
            
            // Cộng điểm tín nhiệm +5 khi verify email thành công
            include_once(__DIR__ . "/mUserScore.php");
            $mUserScore = new mUserScore();
            $mUserScore->mAddScoreByAction($userId, 'verify_email', 'Xác thực email lần đầu', 'verification', null);
            
            return [
                'success' => true,
                'message' => 'Xác thực email thành công!'
            ];
        }
        
        $p->mDongKetNoi($conn);
        return [
            'success' => false,
            'message' => 'Có lỗi xảy ra khi cập nhật thông tin'
        ];
    }
    
    // Lấy thông tin user theo ID
    // Join với user_profile để lấy thêm thông tin chi tiết
    // int $userId - ID user cần lấy
    // return array|null - Thông tin user kèm profile hoặc null
    public function mGetUserById($userId) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if ($conn) {
            $sql = "SELECT u.*, up.job, up.hobbies, up.location, up.gender 
                    FROM user u
                    LEFT JOIN user_profile up ON u.user_id = up.user_id
                    WHERE u.user_id = $userId 
                    LIMIT 1";
            
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $p->mDongKetNoi($conn);
                return $user;
            }
            
            $p->mDongKetNoi($conn);
        }
        return null;
    }
    
    // Đăng nhập user bằng email hoặc số điện thoại
    // Kiểm tra account lock, verify password bcrypt
    // Hiển thị cảnh báo nếu trust_score thấp (30-49)
    // string $emailOrPhone - Email hoặc SĐT
    // string $password - Mật khẩu (plain text)
    // return array - ['success' => bool, 'message' => string, 'user' => array, 'has_warning' => bool, 'trust_score' => int]
    public function mLoginUser($emailOrPhone, $password) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return [
                'success' => false,
                'message' => 'Không thể kết nối database',
                'user' => null
            ];
        }
        
        // Escape dữ liệu
        $emailOrPhone = $conn->real_escape_string($emailOrPhone);
        
        // Tìm user theo email hoặc phone
        $sql = "SELECT u.*, up.job, up.hobbies, up.location, up.gender 
                FROM user u
                LEFT JOIN user_profile up ON u.user_id = up.user_id
                WHERE u.email = '$emailOrPhone' OR u.phone = '$emailOrPhone'
                LIMIT 1";
        
        $result = $conn->query($sql);
        
        if (!$result || $result->num_rows === 0) {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Email hoặc số điện thoại không tồn tại',
                'user' => null
            ];
        }
        
        $user = $result->fetch_assoc();
        
        // Kiểm tra tài khoản có bị khóa không
        if ($user['status'] === 'locked') {
            $p->mDongKetNoi($conn);
            
            // Kiểm tra lý do khóa (điểm tín nhiệm thấp)
            $trustScore = $user['trust_score'] ?? 100;
            $lockMessage = 'Tài khoản của bạn đã bị khóa.';
            
            if ($trustScore < 30) {
                $lockMessage = 'Tài khoản của bạn đã bị khóa do điểm tín nhiệm quá thấp (' . $trustScore . '/100). Vui lòng liên hệ hỗ trợ để được mở khóa.';
            } else {
                $lockMessage .= ' Vui lòng liên hệ hỗ trợ.';
            }
            
            return [
                'success' => false,
                'message' => $lockMessage,
                'user' => null,
                'locked_reason' => 'low_trust_score',
                'trust_score' => $trustScore
            ];
        }
            
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $p->mDongKetNoi($conn);
            return [
                'success' => false,
                'message' => 'Mật khẩu không đúng',
                'user' => null
            ];
        }
        
        $p->mDongKetNoi($conn);
        
        // Kiểm tra điểm tín nhiệm để cảnh báo
        $trustScore = $user['trust_score'] ?? 100;
        $hasWarning = ($trustScore >= 30 && $trustScore < 50);
        $warningMessage = null;
        $warningLevel = null;
        
        if ($hasWarning) {
            $warningMessage = 'Cảnh báo: Điểm tín nhiệm của bạn đang ở mức thấp (' . $trustScore . '/150). Tài khoản sẽ bị khóa nếu điểm xuống dưới 30. Vui lòng cẩn thận trong các hoạt động!';
            $warningLevel = 'warning';
        }
        
        // Đăng nhập thành công
        return [
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'has_warning' => $hasWarning,
            'warning_message' => $warningMessage,
            'warning_level' => $warningLevel,
            'trust_score' => $trustScore
        ];
    }
    
    // Cập nhật thông tin profile user
    // Update bảng user (full_name) và user_profile (job, hobbies, location, gender)
    // int $userId - ID user
    // string $fullName - Họ tên mới
    // string $job - Nghề nghiệp
    // string $hobbies - Sở thích
    // string $location - Địa chỉ
    // string $gender - Giới tính (male/female/other)
    // return array - ['success' => bool, 'message' => string]
    public function mUpdateProfile($userId, $fullName, $job, $hobbies, $location, $gender) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        // Escape dữ liệu
        $fullName = $conn->real_escape_string($fullName);
        $job = $conn->real_escape_string($job);
        $hobbies = $conn->real_escape_string($hobbies);
        $location = $conn->real_escape_string($location);
        $gender = $conn->real_escape_string($gender);
        
        // Update user table
        $sqlUser = "UPDATE user SET full_name = '$fullName' WHERE user_id = $userId";
        $conn->query($sqlUser);
        
        // Update user_profile table
        $sqlProfile = "UPDATE user_profile 
                      SET job = '$job', 
                          hobbies = '$hobbies', 
                          location = '$location', 
                          gender = '$gender',
                          updated_at = CURRENT_TIMESTAMP
                      WHERE user_id = $userId";
        
        if ($conn->query($sqlProfile)) {
            $p->mDongKetNoi($conn);
            return ['success' => true, 'message' => 'Cập nhật thông tin thành công'];
        } else {
            $p->mDongKetNoi($conn);
            return ['success' => false, 'message' => 'Lỗi khi cập nhật thông tin'];
        }
    }
    
    // Đổi mật khẩu user
    // Verify mật khẩu cũ, hash mật khẩu mới bằng bcrypt
    // int $userId - ID user
    // string $currentPassword - Mật khẩu hiện tại (plain text)
    // string $newPassword - Mật khẩu mới (plain text)
    // return array - ['success' => bool, 'message' => string]
    public function mChangePassword($userId, $currentPassword, $newPassword) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        // Get current password hash
        $userId = (int)$userId;
        $sql = "SELECT password FROM user WHERE user_id = $userId";
        $result = $conn->query($sql);
        
        if (!$result || $result->num_rows === 0) {
            $p->mDongKetNoi($conn);
            return ['success' => false, 'message' => 'Không tìm thấy người dùng'];
        }
        
        $user = $result->fetch_assoc();
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            $p->mDongKetNoi($conn);
            return ['success' => false, 'message' => 'Mật khẩu hiện tại không đúng'];
        }
        
        // Hash new password
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $newPasswordHash = $conn->real_escape_string($newPasswordHash);
        
        // Update password
        $updateSql = "UPDATE user SET password = '$newPasswordHash', updated_at = NOW() WHERE user_id = $userId";
        
        if ($conn->query($updateSql)) {
            $p->mDongKetNoi($conn);
            return ['success' => true, 'message' => 'Đổi mật khẩu thành công'];
        } else {
            $p->mDongKetNoi($conn);
            return ['success' => false, 'message' => 'Lỗi khi cập nhật mật khẩu'];
        }
    }

    // Tạo token reset password
    // Token ngẫu nhiên 64 ký tự, thời hạn 1 giờ
    // string $email - Email user cần reset
    // return array - ['success' => bool, 'message' => string, 'token' => string, 'user_id' => int]
    public function mGeneratePasswordResetToken($email) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        $email = $conn->real_escape_string($email);
        
        // Check if user exists and is verified
        $sql = "SELECT user_id, reset_version FROM user WHERE email = '$email' AND is_email_verified = 1 LIMIT 1";
        $result = $conn->query($sql);
        
        if ($result->num_rows === 0) {
            $p->mDongKetNoi($conn);
            return ['success' => false, 'message' => 'Email không tồn tại hoặc chưa được xác thực'];
        }
        
        $user = $result->fetch_assoc();
        $userId = $user['user_id'];
        $resetVersion = $user['reset_version'] + 1; // Increment reset version
        
        // Generate reset token using user_id and reset_version
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
        
        // Update reset version and store token with expiry in verification_code_expires
        $updateSql = "UPDATE user SET 
                     reset_version = $resetVersion,
                     verification_code_expires = '$expiresAt'
                     WHERE user_id = $userId";
        
        if ($conn->query($updateSql)) {
            $p->mDongKetNoi($conn);
            return [
                'success' => true, 
                'token' => $token . '_' . $userId . '_' . $resetVersion,
                'user_id' => $userId,
                'message' => 'Token được tạo thành công'
            ];
        } else {
            $p->mDongKetNoi($conn);
            return ['success' => false, 'message' => 'Lỗi khi tạo token'];
        }
    }

    // Xác thực token reset password
    // Kiểm tra token có tồn tại và còn hạn không (1 giờ)
    // string $token - Token reset password
    // return array - ['success' => bool, 'message' => string, 'user' => array]
    public function mVerifyResetToken($token) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        // Parse token: randomstring_userid_version
        $parts = explode('_', $token);
        if (count($parts) !== 3) {
            $p->mDongKetNoi($conn);
            return ['success' => false, 'message' => 'Token không hợp lệ'];
        }
        
        $randomString = $parts[0];
        $userId = intval($parts[1]);
        $resetVersion = intval($parts[2]);
        
        $now = date('Y-m-d H:i:s');
        
        // Check if token matches and not expired
        $sql = "SELECT user_id, email, full_name, reset_version, verification_code_expires
                FROM user 
                WHERE user_id = $userId
                AND reset_version = $resetVersion
                AND verification_code_expires > '$now'
                AND is_email_verified = 1
                LIMIT 1";
        
        $result = $conn->query($sql);
        
        if ($result->num_rows === 0) {
            $p->mDongKetNoi($conn);
            return ['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn'];
        }
        
        $user = $result->fetch_assoc();
        $p->mDongKetNoi($conn);
        
        return [
            'success' => true,
            'user' => $user,
            'message' => 'Token hợp lệ'
        ];
    }

    // Reset mật khẩu bằng token
    // Verify token còn hạn, hash mật khẩu mới, xóa token
    // string $token - Token reset
    // string $newPassword - Mật khẩu mới (plain text)
    // return array - ['success' => bool, 'message' => string]
    public function mResetPasswordWithToken($token, $newPassword) {
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        // Parse token: randomstring_userid_version
        $parts = explode('_', $token);
        if (count($parts) !== 3) {
            $p->mDongKetNoi($conn);
            return ['success' => false, 'message' => 'Token không hợp lệ'];
        }
        
        $userId = intval($parts[1]);
        $resetVersion = intval($parts[2]);
        $now = date('Y-m-d H:i:s');
        
        // Verify token first
        $sql = "SELECT user_id, reset_version
                FROM user 
                WHERE user_id = $userId
                AND reset_version = $resetVersion
                AND verification_code_expires > '$now'
                LIMIT 1";
        
        $result = $conn->query($sql);
        
        if ($result->num_rows === 0) {
            $p->mDongKetNoi($conn);
            return ['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn'];
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password, increment reset_version again, and clear expiry
        $newResetVersion = $resetVersion + 1;
        $updateSql = "UPDATE user SET 
                     password_hash = '$hashedPassword',
                     reset_version = $newResetVersion,
                     verification_code_expires = NULL
                     WHERE user_id = $userId";
        
        if ($conn->query($updateSql)) {
            $p->mDongKetNoi($conn);
            return ['success' => true, 'message' => 'Đặt lại mật khẩu thành công'];
        } else {
            $p->mDongKetNoi($conn);
            return ['success' => false, 'message' => 'Lỗi khi cập nhật mật khẩu'];
        }
    }

    // Gửi lại mã xác thực email
    // Tạo mã mới và gửi email
    // int $userId - ID user
    // string $email - Email nhận mã
    // return array - ['success' => bool, 'message' => string, 'code' => string]
    public function mResendVerificationCode($userId, $email) {
        // Generate new code
        $newCode = $this->mGenerateVerifyCode($userId);
        
        if (!$newCode) {
            return ['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại.'];
        }

        // Get user for full name
        $user = $this->mGetUserById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Người dùng không tồn tại'];
        }

        // Send email
        include_once __DIR__ . '/mEmailPHPMailer.php';
        $mEmail = new mEmailPHPMailer();
        $emailSent = $mEmail->sendVerificationCode($email, $user['full_name'], $newCode);
        
        if (!$emailSent) {
            return ['success' => false, 'message' => 'Không thể gửi mã. Vui lòng thử lại sau.'];
        }

        return ['success' => true, 'message' => 'Đã gửi mã xác thực'];
    }
}
?>
