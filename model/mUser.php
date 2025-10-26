<?php 
include_once(__DIR__ . "/mConnect.php");

class mUser {
    
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
        if ($user['verify_version'] !== $code) {
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
        
        if ($conn->query($updateSql)) {
            $p->mDongKetNoi($conn);
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
            return [
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.',
                'user' => null
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
        
        // Đăng nhập thành công
        return [
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'user' => $user
        ];
    }
    
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
}
?>
