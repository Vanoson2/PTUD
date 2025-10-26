// Client-side validation for register form
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('registerForm');
  
  if (!form) return;
  
  // Toggle password visibility
  const toggleButtons = document.querySelectorAll('.toggle-password');
  toggleButtons.forEach(button => {
    button.addEventListener('click', function() {
      const targetId = this.getAttribute('data-target');
      const input = document.getElementById(targetId);
      
      if (input.type === 'password') {
        input.type = 'text';
        this.innerHTML = `
          <svg class="eye-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
            <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
          </svg>
        `;
      } else {
        input.type = 'password';
        this.innerHTML = `
          <svg class="eye-icon" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
          </svg>
        `;
      }
    });
  });
  
  // Real-time validation
  const emailInput = document.getElementById('email');
  const phoneInput = document.getElementById('phone');
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirm_password');
  const fullNameInput = document.getElementById('full_name');
  
  // Email validation
  if (emailInput) {
    emailInput.addEventListener('blur', function() {
      validateEmail(this);
    });
  }
  
  // Phone validation
  if (phoneInput) {
    phoneInput.addEventListener('input', function() {
      // Only allow numbers
      this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    phoneInput.addEventListener('blur', function() {
      validatePhone(this);
    });
  }
  
  // Password validation
  if (passwordInput) {
    passwordInput.addEventListener('blur', function() {
      validatePassword(this);
    });
    
    passwordInput.addEventListener('input', function() {
      // Also validate confirm password if it has value
      if (confirmPasswordInput && confirmPasswordInput.value) {
        validateConfirmPassword(confirmPasswordInput);
      }
    });
  }
  
  // Confirm password validation
  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('blur', function() {
      validateConfirmPassword(this);
    });
  }
  
  // Full name validation
  if (fullNameInput) {
    fullNameInput.addEventListener('blur', function() {
      validateFullName(this);
    });
  }
  
  // Form submission
  form.addEventListener('submit', function(e) {
    let isValid = true;
    
    // Validate all fields
    if (fullNameInput && !validateFullName(fullNameInput)) isValid = false;
    if (emailInput && !validateEmail(emailInput)) isValid = false;
    if (phoneInput && !validatePhone(phoneInput)) isValid = false;
    if (passwordInput && !validatePassword(passwordInput)) isValid = false;
    if (confirmPasswordInput && !validateConfirmPassword(confirmPasswordInput)) isValid = false;
    
    if (!isValid) {
      e.preventDefault();
      // Scroll to first error
      const firstError = form.querySelector('.is-invalid');
      if (firstError) {
        firstError.focus();
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  });
  
  // Validation functions
  function validateEmail(input) {
    const value = input.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    clearError(input);
    
    if (!value) {
      showError(input, 'Vui lòng nhập email');
      return false;
    }
    
    if (!emailRegex.test(value)) {
      showError(input, 'Email không hợp lệ');
      return false;
    }
    
    if (value.length > 190) {
      showError(input, 'Email quá dài (tối đa 190 ký tự)');
      return false;
    }
    
    return true;
  }
  
  function validatePhone(input) {
    const value = input.value.trim();
    const phoneRegex = /^[0-9]{10,11}$/;
    
    clearError(input);
    
    if (!value) {
      showError(input, 'Vui lòng nhập số điện thoại');
      return false;
    }
    
    if (!phoneRegex.test(value)) {
      showError(input, 'Số điện thoại không hợp lệ (10-11 chữ số)');
      return false;
    }
    
    return true;
  }
  
  function validatePassword(input) {
    const value = input.value;
    
    clearError(input);
    
    if (!value) {
      showError(input, 'Vui lòng nhập mật khẩu');
      return false;
    }
    
    if (value.length < 6) {
      showError(input, 'Mật khẩu phải có ít nhất 6 ký tự');
      return false;
    }
    
    if (value.length > 255) {
      showError(input, 'Mật khẩu quá dài');
      return false;
    }
    
    return true;
  }
  
  function validateConfirmPassword(input) {
    const value = input.value;
    const password = passwordInput.value;
    
    clearError(input);
    
    if (!value) {
      showError(input, 'Vui lòng xác nhận mật khẩu');
      return false;
    }
    
    if (value !== password) {
      showError(input, 'Mật khẩu xác nhận không khớp');
      return false;
    }
    
    return true;
  }
  
  function validateFullName(input) {
    const value = input.value.trim();
    
    clearError(input);
    
    if (!value) {
      showError(input, 'Vui lòng nhập họ tên');
      return false;
    }
    
    if (value.length > 150) {
      showError(input, 'Họ tên quá dài (tối đa 150 ký tự)');
      return false;
    }
    
    return true;
  }
  
  function showError(input, message) {
    input.classList.add('is-invalid');
    
    // Remove existing error message
    const existingError = input.parentElement.querySelector('.invalid-feedback');
    if (existingError) {
      existingError.remove();
    }
    
    // Add new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    
    if (input.parentElement.classList.contains('password-input-wrapper')) {
      input.parentElement.parentElement.appendChild(errorDiv);
    } else {
      input.parentElement.appendChild(errorDiv);
    }
  }
  
  function clearError(input) {
    input.classList.remove('is-invalid');
    
    // Remove error message
    const errorDiv = input.parentElement.querySelector('.invalid-feedback');
    if (errorDiv) {
      errorDiv.remove();
    }
    
    // Also check parent if it's a password wrapper
    if (input.parentElement.classList.contains('password-input-wrapper')) {
      const parentError = input.parentElement.parentElement.querySelector('.invalid-feedback');
      if (parentError) {
        parentError.remove();
      }
    }
  }
});
