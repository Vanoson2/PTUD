// Date validation for booking form
(function() {
  const form = document.getElementById('searchForm');
  const checkinInput = document.getElementById('checkin');
  const checkoutInput = document.getElementById('checkout');
  const locationInput = document.getElementById('locationInput');

  if (!form || !checkinInput || !checkoutInput) return;

  // Calculate max date (3 months from today)
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  const maxDate = new Date();
  maxDate.setMonth(maxDate.getMonth() + 3);
  maxDate.setHours(0, 0, 0, 0);
  
  const todayStr = today.toISOString().split('T')[0];
  const maxDateStr = maxDate.toISOString().split('T')[0];

  // Update min/max dynamically
  checkinInput.setAttribute('min', todayStr);
  checkinInput.setAttribute('max', maxDateStr);

  // When check-in changes, update check-out min
  checkinInput.addEventListener('change', function() {
    const checkinDate = new Date(this.value);
    checkinDate.setHours(0, 0, 0, 0);
    
    // Check-out must be at least next day
    const minCheckout = new Date(checkinDate);
    minCheckout.setDate(minCheckout.getDate() + 1);
    
    // Check-out max is 30 days from check-in
    const maxCheckout = new Date(checkinDate);
    maxCheckout.setDate(maxCheckout.getDate() + 30);
    
    const minCheckoutStr = minCheckout.toISOString().split('T')[0];
    const maxCheckoutStr = maxCheckout.toISOString().split('T')[0];
    
    checkoutInput.setAttribute('min', minCheckoutStr);
    checkoutInput.setAttribute('max', maxCheckoutStr);
    
    // If checkout is now invalid, reset it
    const currentCheckout = new Date(checkoutInput.value);
    if (currentCheckout <= checkinDate || currentCheckout > maxCheckout) {
      checkoutInput.value = minCheckoutStr;
    }
  });

  // Form validation on submit
  form.addEventListener('submit', function(e) {
    const errors = [];
    
    // Validate location
    if (!locationInput.value.trim()) {
      errors.push('Vui lòng chọn địa điểm');
    }
    
    // Validate check-in
    const checkinValue = checkinInput.value;
    if (!checkinValue) {
      errors.push('Vui lòng chọn ngày check-in');
    } else {
      const checkinDate = new Date(checkinValue);
      checkinDate.setHours(0, 0, 0, 0);
      
      if (checkinDate < today) {
        errors.push('Ngày check-in không được trước ngày hôm nay');
      } else if (checkinDate > maxDate) {
        errors.push('Ngày check-in chỉ được đặt trong vòng 3 tháng kể từ hôm nay');
      }
    }
    
    // Validate check-out
    const checkoutValue = checkoutInput.value;
    if (!checkoutValue) {
      errors.push('Vui lòng chọn ngày check-out');
    } else if (checkinValue) {
      const checkinDate = new Date(checkinValue);
      const checkoutDate = new Date(checkoutValue);
      checkinDate.setHours(0, 0, 0, 0);
      checkoutDate.setHours(0, 0, 0, 0);
      
      if (checkoutDate <= checkinDate) {
        errors.push('Ngày check-out phải sau ngày check-in');
      } else {
        // Calculate nights
        const daysDiff = Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24));
        
        if (daysDiff > 30) {
          errors.push('Tổng số ngày lưu trú tối đa là 30 ngày');
        }
      }
    }
    
    // If there are errors, prevent submit and show alert
    if (errors.length > 0) {
      e.preventDefault();
      alert(errors.join('\n'));
      return false;
    }
    
    return true;
  });

  // Trigger check-in change to set initial checkout constraints
  if (checkinInput.value) {
    checkinInput.dispatchEvent(new Event('change'));
  }
})();
