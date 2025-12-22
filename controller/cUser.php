<?php 
include_once(__DIR__ . "/../model/mUser.php");
include_once(__DIR__ . "/../model/mEmailPHPMailer.php");
include_once(__DIR__ . "/../model/mUserScore.php");

class cUser {
    
    // ===== XÁC THỰC & ĐĂNG KÝ =====
    
    // Đăng ký user mới
    // Validate email, phone, password, gọi Model để tạo user và gửi email xác thực
    // string $email - Email
    // string $phone - Số điện thoại
    // string $password - Mật khẩu
    // string $confirmPassword - Xác nhận mật khẩu
    // string $fullName - Họ tên
    // return array - ['success' => bool, 'errors' => array, 'user_id' => int]
    public function cRegisterUser($email, $phone, $password, $confirmPassword, $fullName) {
        $errors = [];
        
        // Validate email
        if (empty($email)) {
            $errors['email'] = 'Vui lòng nhập email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ';
        } elseif (strlen($email) > 190) {
            $errors['email'] = 'Email quá dài (tối đa 190 ký tự)';
        }
        
        // Validate phone
        if (empty($phone)) {
            $errors['phone'] = 'Vui lòng nhập số điện thoại';
        } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
            $errors['phone'] = 'Số điện thoại không hợp lệ (10-11 chữ số)';
        }
        
        // Validate password
        if (empty($password)) {
            $errors['password'] = 'Vui lòng nhập mật khẩu';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 8 ký tự';
        } elseif (strlen($password) > 255) {
            $errors['password'] = 'Mật khẩu quá dài';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 1 chữ hoa';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 1 chữ thường';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 1 chữ số';
        }
        
        // Validate confirm password
        if (empty($confirmPassword)) {
            $errors['confirm_password'] = 'Vui lòng xác nhận mật khẩu';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp';
        }
        
        // Validate full name
        if (empty($fullName)) {
            $errors['full_name'] = 'Vui lòng nhập họ tên';
        } elseif (strlen($fullName) > 150) {
            $errors['full_name'] = 'Họ tên quá dài (tối đa 150 ký tự)';
        }
        
        // Nếu có lỗi validation, return ngay
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'message' => 'Vui lòng kiểm tra lại thông tin',
                'user_id' => null
            ];
        }
        
        // Kiểm tra email đã tồn tại
        $mUser = new mUser();
        if ($mUser->mCheckEmailExists($email)) {
            $errors['email'] = 'Email đã được sử dụng';
            return [
                'success' => false,
                'errors' => $errors,
                'message' => 'Email đã được sử dụng',
                'user_id' => null
            ];
        }
        
        // Kiểm tra phone đã tồn tại
        if ($mUser->mCheckPhoneExists($phone)) {
            $errors['phone'] = 'Số điện thoại đã được sử dụng';
            return [
                'success' => false,
                'errors' => $errors,
                'message' => 'Số điện thoại đã được sử dụng',
                'user_id' => null
            ];
        }
        
        // Thực hiện đăng ký
        $result = $mUser->mRegisterUser($email, $phone, $password, $fullName);
        
        if ($result['success']) {
            $userId = $result['user_id'];
            
            // Gửi email xác thực với mã 6 số
            $emailSent = false;
            try {
                // Tạo mã xác thực 6 số
                $verifyCode = $mUser->mGenerateVerifyCode($userId);
                
                if ($verifyCode) {
                    // Gửi email bằng EmailHelper
                    require_once(__DIR__ . '/../helper/EmailHelper.php');
                    $emailHelper = new EmailHelper();
                    $emailSent = $emailHelper->sendVerificationCode($email, $fullName, $verifyCode);
                }
            } catch (Exception $e) {
                // Log error nhưng không fail registration
                error_log('Failed to send verification email: ' . $e->getMessage());
            }
            
            $message = 'Đăng ký thành công! ';
            if ($emailSent) {
                $message .= 'Chúng tôi đã gửi mã xác thực 6 số đến ' . $email . '. Vui lòng kiểm tra email và nhập mã để xác thực tài khoản.';
            } else {
                $message .= 'Tuy nhiên, không thể gửi email xác thực. Bạn vẫn có thể đăng nhập nhưng nên xác thực email sau.';
            }
            
            return [
                'success' => true,
                'errors' => [],
                'message' => $message,
                'user_id' => $userId,
                'email_sent' => $emailSent
            ];
        } else {
            return [
                'success' => false,
                'errors' => ['general' => $result['message']],
                'message' => $result['message'],
                'user_id' => null,
                'email_sent' => false
            ];
        }
    }
    
    // Đăng nhập user
    // Validate input, gọi Model để verify và trả về thông tin user
    // string $emailOrPhone - Email hoặc SĐT
    // string $password - Mật khẩu
    // return array - ['success' => bool, 'message' => string, 'user' => array, 'has_warning' => bool]
    public function cLoginUser($emailOrPhone, $password) {
        $errors = [];
        
        // Validate email/phone
        if (empty($emailOrPhone)) {
            $errors['email_or_phone'] = 'Vui lòng nhập email hoặc số điện thoại';
        }
        
        // Validate password
        if (empty($password)) {
            $errors['password'] = 'Vui lòng nhập mật khẩu';
        }
        
        // Nếu có lỗi validation, return ngay
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'message' => 'Vui lòng điền đầy đủ thông tin',
                'user' => null
            ];
        }
        
        // Gọi model để xác thực
        $mUser = new mUser();
        $result = $mUser->mLoginUser($emailOrPhone, $password);
        
        if ($result['success']) {
            return [
                'success' => true,
                'errors' => [],
                'message' => $result['message'],
                'user' => $result['user']
            ];
        } else {
            return [
                'success' => false,
                'errors' => ['general' => $result['message']],
                'message' => $result['message'],
                'user' => null
            ];
        }
    }
    
    // Xác thực mã code email
    // Validate mã 6 số và cập nhật trạng thái xác thực
    // int $userId - ID user
    // string $code - Mã xác thực 6 số
    // return array - ['success' => bool, 'message' => string, 'user' => array|null]
    public function cVerifyCode($userId, $code) {
        // Validate code
        if (empty($code)) {
            return ['success' => false, 'message' => 'Vui lòng nhập mã xác thực'];
        }
        
        $code = trim($code);
        
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return ['success' => false, 'message' => 'Mã xác thực phải là 6 chữ số'];
        }

        // Call Model to verify
        $mUser = new mUser();
        $result = $mUser->mVerifyCode($userId, $code);
        
        if ($result['success']) {
            // Get user data for auto-login
            $user = $mUser->mGetUserById($userId);
            $result['user'] = $user;
        }
        
        return $result;
    }

    // Gửi lại mã xác thực email
    // Tạo mã mới và gửi email cho user
    // int $userId - ID user
    // string $email - Email user
    // return array - ['success' => bool, 'message' => string]
    public function cResendVerificationCode($userId, $email) {
        if (empty($userId) || empty($email)) {
            return ['success' => false, 'message' => 'Thông tin không hợp lệ'];
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email không hợp lệ'];
        }

        $mUser = new mUser();
        return $mUser->mResendVerificationCode($userId, $email);
    }

    // ===== QUẢN LÝ TÀI KHOẢN & THÔNG TIN =====
    
    public function cGetUserById($userId) {
        $mUser = new mUser();
        return $mUser->mGetUserById($userId);
    }
    
    // Lấy thông tin profile user theo ID
    // int $userId - ID user
    // return array|null - Dữ liệu user hoặc null nếu không tìm thấy
    public function cGetUserProfile($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            return null;
        }

        $mUser = new mUser();
        return $mUser->mGetUserById($userId);
    }

    // Cập nhật thông tin profile user
    // Cho phép user cập nhật họ tên, nghề nghiệp, sở thích, địa chỉ, giới tính
    // int $userId - ID user
    // string $fullName - Họ tên (bắt buộc, tối đa 150 ký tự)
    // string|null $job - Nghề nghiệp
    // string|null $hobbies - Sở thích
    // string|null $location - Địa chỉ
    // string|null $gender - Giới tính
    // return array - ['success' => bool, 'message' => string]
    public function cUpdateUserProfile($userId, $fullName, $job = null, $hobbies = null, $location = null, $gender = null) {
        // Validation
        if (!is_numeric($userId) || $userId <= 0) {
            return ['success' => false, 'message' => 'User ID không hợp lệ'];
        }

        if (empty($fullName)) {
            return ['success' => false, 'message' => 'Vui lòng nhập họ tên'];
        }

        if (strlen($fullName) > 150) {
            return ['success' => false, 'message' => 'Họ tên quá dài (tối đa 150 ký tự)'];
        }

        $mUser = new mUser();
        return $mUser->mUpdateProfile($userId, $fullName, $job, $hobbies, $location, $gender);
    }
    
    // Kiểm tra trạng thái tài khoản user (locked/active)
    // Dùng cho security middleware để verify trước khi cho phép truy cập
    // int $userId - ID user cần kiểm tra
    // return array - ['status' => string, 'is_locked' => bool]
    public function cCheckUserAccountStatus($userId) {
        $mUser = new mUser();
        $user = $mUser->mGetUserById($userId);
        
        if (!$user) {
            return ['status' => 'not_found', 'is_locked' => true];
        }
        
        return [
            'status' => $user['status'], // 'active' or 'locked'
            'is_locked' => ($user['status'] === 'locked')
        ];
    }

    // ===== QUẢN LÝ MẬT KHẨU =====
    
    // Đổi mật khẩu user
    // Validate độ mạnh mật khẩu: tối thiểu 8 ký tự, có chữ hoa, chữ thường, số
    // int $userId - ID user
    // string $currentPassword - Mật khẩu hiện tại
    // string $newPassword - Mật khẩu mới
    // string $confirmPassword - Xác nhận mật khẩu mới
    // return array - ['success' => bool, 'message' => string]
    public function cChangePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
        // Validate dữ liệu đầu vào
        if (!is_numeric($userId) || $userId <= 0) {
            return ['success' => false, 'message' => 'User ID không hợp lệ'];
        }

        if (empty($currentPassword)) {
            return ['success' => false, 'message' => 'Vui lòng nhập mật khẩu hiện tại'];
        }

        if (empty($newPassword)) {
            return ['success' => false, 'message' => 'Vui lòng nhập mật khẩu mới'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 8 ký tự'];
        }

        if (strlen($newPassword) > 255) {
            return ['success' => false, 'message' => 'Mật khẩu mới quá dài'];
        }

        // Validate độ mạnh mật khẩu: phải có chữ hoa, chữ thường, và số
        if (!preg_match('/[A-Z]/', $newPassword)) {
            return ['success' => false, 'message' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa'];
        }

        if (!preg_match('/[a-z]/', $newPassword)) {
            return ['success' => false, 'message' => 'Mật khẩu phải chứa ít nhất 1 chữ thường'];
        }

        if (!preg_match('/[0-9]/', $newPassword)) {
            return ['success' => false, 'message' => 'Mật khẩu phải chứa ít nhất 1 chữ số'];
        }

        if (empty($confirmPassword)) {
            return ['success' => false, 'message' => 'Vui lòng xác nhận mật khẩu mới'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'Mật khẩu xác nhận không khớp'];
        }

        $mUser = new mUser();
        
        // Verify current password
        $user = $mUser->mGetUserById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Người dùng không tồn tại'];
        }

        if (!password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Mật khẩu hiện tại không đúng'];
        }

        // Update password - Model sẽ verify lại currentPassword
        return $mUser->mChangePassword($userId, $currentPassword, $newPassword);
    }

    // Gửi email reset password
    // Tạo token và gửi link reset qua email
    // string $email - Email user
    // return array - ['success' => bool, 'message' => string]
    public function cSendPasswordResetEmail($email) {
        // Validate email
        if (empty($email)) {
            return ['success' => false, 'message' => 'Vui lòng nhập email'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email không hợp lệ'];
        }
        
        // Generate reset token
        $mUser = new mUser();
        $result = $mUser->mGeneratePasswordResetToken($email);
        
        if (!$result['success']) {
            // Return success message anyway for security (don't reveal if email exists)
            return [
                'success' => true, 
                'message' => 'Nếu email của bạn tồn tại trong hệ thống, chúng tôi sẽ gửi link đặt lại mật khẩu đến email của bạn.'
            ];
        }
        
        $token = $result['token'];
        
        // Create reset link
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $resetLink = $protocol . '://' . $host . '/view/user/traveller/reset-password.php?token=' . $token;
        
        // Send email
        $mEmail = new mEmailPHPMailer();
        $subject = 'Đặt lại mật khẩu - WEGO';
        $body = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Đặt lại mật khẩu</h2>
            <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản WEGO của mình.</p>
            <p>Vui lòng click vào link bên dưới để đặt lại mật khẩu:</p>
            <p><a href='$resetLink' style='background-color: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Đặt lại mật khẩu</a></p>
            <p>Hoặc copy link sau vào trình duyệt:</p>
            <p>$resetLink</p>
            <p><strong>Link này sẽ hết hạn sau 1 giờ.</strong></p>
            <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
            <br>
            <p>Trân trọng,<br>Đội ngũ WEGO</p>
        </body>
        </html>
        ";
        
        $emailResult = $mEmail->sendEmail($email, $subject, $body);
        
        if ($emailResult === true) {
            return [
                'success' => true,
                'message' => 'Link đặt lại mật khẩu đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Có lỗi khi gửi email. Vui lòng thử lại sau.'
            ];
        }
    }

    // Xác thực token reset password
    // string $token - Token reset
    // return array - ['success' => bool, 'message' => string, 'user' => array]
    public function cVerifyResetToken($token) {
        if (empty($token)) {
            return ['success' => false, 'message' => 'Token không hợp lệ'];
        }
        
        $mUser = new mUser();
        return $mUser->mVerifyResetToken($token);
    }

    // Reset mật khẩu bằng token
    // Validate password, gọi Model để đổi mật khẩu
    // string $token - Token reset
    // string $newPassword - Mật khẩu mới
    // string $confirmPassword - Xác nhận mật khẩu
    // return array - ['success' => bool, 'message' => string]
    public function cResetPassword($token, $newPassword, $confirmPassword) {
        // Validate inputs
        if (empty($token)) {
            return ['success' => false, 'message' => 'Token không hợp lệ'];
        }
        
        if (empty($newPassword)) {
            return ['success' => false, 'message' => 'Vui lòng nhập mật khẩu mới'];
        }
        
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 8 ký tự'];
        }

        if (strlen($newPassword) > 255) {
            return ['success' => false, 'message' => 'Mật khẩu quá dài'];
        }

        // Validate password strength
        if (!preg_match('/[A-Z]/', $newPassword)) {
            return ['success' => false, 'message' => 'Mật khẩu phải chứa ít nhất 1 chữ hoa'];
        }

        if (!preg_match('/[a-z]/', $newPassword)) {
            return ['success' => false, 'message' => 'Mật khẩu phải chứa ít nhất 1 chữ thường'];
        }

        if (!preg_match('/[0-9]/', $newPassword)) {
            return ['success' => false, 'message' => 'Mật khẩu phải chứa ít nhất 1 chữ số'];
        }
        
        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'Mật khẩu xác nhận không khớp'];
        }
        
        // Reset password
        $mUser = new mUser();
        return $mUser->mResetPasswordWithToken($token, $newPassword);
    }

    // ===== QUẢN LÝ ĐIỂM TÍN NHIỆM =====

    // Lấy thông tin điểm tín nhiệm của user
    // int $userId - ID user
    // return array - ['success' => bool, 'data' => array, 'message' => string]
    public function cGetUserScore($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            return ['success' => false, 'message' => 'User ID không hợp lệ'];
        }

        $mUserScore = new mUserScore();
        $scoreData = $mUserScore->mGetUserScore($userId);

        if (!$scoreData) {
            return ['success' => false, 'message' => 'Không tìm thấy thông tin user'];
        }

        // Lấy level badge
        $level = $mUserScore->mGetUserLevel($scoreData['trust_score']);

        return [
            'success' => true,
            'data' => [
                'trust_score' => $scoreData['trust_score'],
                'is_verified' => $scoreData['is_verified'],
                'verified_phone' => $scoreData['verified_phone'],
                'verified_id' => $scoreData['verified_id'],
                'level' => $level
            ]
        ];
    }

    // Lấy lịch sử thay đổi điểm tín nhiệm
    // int $userId - ID user
    // int $limit - Số lượng bản ghi (mặc định 20)
    // return array - ['success' => bool, 'data' => array]
    public function cGetScoreHistory($userId, $limit = 20) {
        if (!is_numeric($userId) || $userId <= 0) {
            return ['success' => false, 'message' => 'User ID không hợp lệ'];
        }

        $mUserScore = new mUserScore();
        $history = $mUserScore->mGetScoreHistory($userId, $limit);

        return [
            'success' => true,
            'data' => $history
        ];
    }

    // Admin cập nhật điểm user thủ công
    // Cho phép admin thêm/trừ điểm với lý do cụ thể
    // int $userId - ID user
    // int $scoreChange - Điểm thay đổi (+/-)
    // string $reason - Lý do thay đổi
    // int $adminId - ID admin thực hiện
    // return array - ['success' => bool, 'message' => string]
    public function cUpdateUserScore($userId, $scoreChange, $reason, $adminId) {
        if (!is_numeric($userId) || $userId <= 0) {
            return ['success' => false, 'message' => 'User ID không hợp lệ'];
        }

        if (!is_numeric($scoreChange)) {
            return ['success' => false, 'message' => 'Điểm thay đổi không hợp lệ'];
        }

        if (empty($reason)) {
            return ['success' => false, 'message' => 'Vui lòng nhập lý do'];
        }

        if (!is_numeric($adminId) || $adminId <= 0) {
            return ['success' => false, 'message' => 'Admin ID không hợp lệ'];
        }

        $mUserScore = new mUserScore();
        return $mUserScore->mUpdateUserScore($userId, $scoreChange, $reason, null, null, $adminId);
    }

    // Lấy gợi ý cải thiện điểm tín nhiệm cho user
    // Phân tích trạng thái hiện tại và đưa ra các gợi ý hành động
    // int $userId - ID user
    // return array - ['success' => bool, 'data' => array]
    public function cGetImprovementSuggestions($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            return ['success' => false, 'message' => 'User ID không hợp lệ'];
        }

        $mUserScore = new mUserScore();
        $suggestions = $mUserScore->mGetImprovementSuggestions($userId);

        return [
            'success' => true,
            'data' => $suggestions
        ];
    }

    // Cập nhật trạng thái xác thực (email/phone/ID)
    // Admin hoặc hệ thống cập nhật trạng thái đã xác thực
    // int $userId - ID user
    // string $type - Loại xác thực: 'email', 'phone', 'id'
    // bool $status - Trạng thái xác thực
    // return array - ['success' => bool, 'message' => string]
    public function cUpdateVerificationStatus($userId, $type, $status) {
        if (!is_numeric($userId) || $userId <= 0) {
            return ['success' => false, 'message' => 'User ID không hợp lệ'];
        }

        $validTypes = ['email', 'phone', 'id'];
        if (!in_array($type, $validTypes)) {
            return ['success' => false, 'message' => 'Loại xác thực không hợp lệ'];
        }

        $mUserScore = new mUserScore();
        return $mUserScore->mUpdateVerificationStatus($userId, $type, $status);
    }

    // Tự động cộng điểm theo hành động user
    // Được gọi từ các controller khác khi user thực hiện hành động
    // int $userId - ID user
    // string $actionType - Loại hành động: 'complete_booking', 'receive_5_star', 'cancel_booking'
    // string|null $relatedType - Loại liên quan: 'booking', 'review'
    // int|null $relatedId - ID liên quan
    // return array - ['success' => bool, 'message' => string]
    public function cAddScoreByAction($userId, $actionType, $relatedType = null, $relatedId = null) {
        if (!is_numeric($userId) || $userId <= 0) {
            return ['success' => false, 'message' => 'User ID không hợp lệ'];
        }

        if (empty($actionType)) {
            return ['success' => false, 'message' => 'Action type không được để trống'];
        }

        $mUserScore = new mUserScore();
        return $mUserScore->mAddScoreByAction($userId, $actionType, $relatedType, $relatedId);
    }
}
?>
