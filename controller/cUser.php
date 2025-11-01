<?php 
include_once(__DIR__ . "/../model/mUser.php");
include_once(__DIR__ . "/../model/mEmailPHPMailer.php");

class cUser {
    
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
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự';
        } elseif (strlen($password) > 255) {
            $errors['password'] = 'Mật khẩu quá dài';
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
    
    public function cGetUserById($userId) {
        $mUser = new mUser();
        return $mUser->mGetUserById($userId);
    }
}
?>
