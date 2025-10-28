// Booking confirmation page JavaScript
document.addEventListener('DOMContentLoaded', function() {
  const serviceCheckboxes = document.querySelectorAll('.service-item');
  const servicesBreakdown = document.getElementById('servicesBreakdown');
  const totalAmountElement = document.getElementById('totalAmount');
  const subtotalElement = document.getElementById('subtotalAmount');
  
  // Calculate total when service checkboxes change
  function updateTotal() {
    let subtotal = listingPrice * nights;
    let servicesTotal = 0;
    let servicesHtml = '';
    
    serviceCheckboxes.forEach(checkbox => {
      if (checkbox.checked) {
        const price = parseFloat(checkbox.dataset.price);
        const label = checkbox.closest('.service-checkbox').querySelector('label');
        const name = label ? label.textContent.trim() : 'Service';
        servicesTotal += price;
        servicesHtml += `
          <div class="d-flex justify-content-between mb-2 text-muted small">
            <span>${name}</span>
            <span>${formatNumber(price)}</span>
          </div>
        `;
      }
    });
    
    servicesBreakdown.innerHTML = servicesHtml;
    const total = subtotal + servicesTotal;
    totalAmountElement.textContent = formatNumber(total);
  }
  
  // Format number with thousand separator (no decimals)
  function formatNumber(num) {
    // Round to integer first
    const rounded = Math.round(num);
    // Add thousand separators
    return rounded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ' VND';
  }
  
  // Add event listeners
  serviceCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateTotal);
  });
  
  // Form submission
  const bookingForm = document.getElementById('bookingForm');
  if (bookingForm) {
    bookingForm.addEventListener('submit', function(e) {
      const agreeTerms = document.getElementById('agreeTerms');
      if (!agreeTerms.checked) {
        e.preventDefault();
        alert('Vui lòng đồng ý với các điều khoản và điều kiện');
        return false;
      }
      
      // Show loading state
      const submitBtn = bookingForm.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
    });
  }
});
