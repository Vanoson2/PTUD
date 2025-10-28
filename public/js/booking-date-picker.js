// Booking Date Picker Modal for Detail Listing Page
document.addEventListener('DOMContentLoaded', function() {
  const dateModal = document.getElementById('dateModal');
  const openDateModal = document.getElementById('openDateModal');
  const closeDateModal = document.getElementById('closeDateModal');
  const clearDates = document.getElementById('clearDates');
  const doneDates = document.getElementById('doneDates');
  
  const hiddenCheckin = document.getElementById('hiddenCheckin');
  const hiddenCheckout = document.getElementById('hiddenCheckout');
  const displayCheckin = document.getElementById('displayCheckin');
  const displayCheckout = document.getElementById('displayCheckout');
  const modalCheckinValue = document.getElementById('modalCheckinValue');
  const modalCheckoutValue = document.getElementById('modalCheckoutValue');
  
  const bookingGuestsInput = document.getElementById('bookingGuestsInput');
  const minusBtn = document.querySelector('.btn-booking-guest.minus');
  const plusBtn = document.querySelector('.btn-booking-guest.plus');
  
  let selectedCheckin = hiddenCheckin.value || null;
  let selectedCheckout = hiddenCheckout.value || null;
  let selectingCheckout = false;
  let currentMonthOffset = 0; // Track which months we're showing
  
  // Parse booked dates to get disabled date ranges (store as YYYY-MM-DD strings to avoid timezone issues)
  const disabledDates = [];
  if (typeof bookedDatesData !== 'undefined' && bookedDatesData.length > 0) {
    bookedDatesData.forEach(booking => {
      const start = String(booking.check_in);
      const end = String(booking.check_out);
      disabledDates.push({ from: start, to: end });
    });
  }
  
  // Open modal
  openDateModal.addEventListener('click', function() {
    dateModal.classList.add('active');
    document.body.style.overflow = 'hidden';
    renderCalendar();
  });
  
  // Close modal
  function closeModal() {
    dateModal.classList.remove('active');
    document.body.style.overflow = '';
  }
  
  closeDateModal.addEventListener('click', closeModal);
  doneDates.addEventListener('click', closeModal);
  
  // Click outside to close
  dateModal.addEventListener('click', function(e) {
    if (e.target === dateModal) {
      closeModal();
    }
  });
  
  // Clear dates
  clearDates.addEventListener('click', function() {
    selectedCheckin = null;
    selectedCheckout = null;
    selectingCheckout = false;
    updateDateDisplays();
    renderCalendar();
  });
  
  // Update date displays
  function updateDateDisplays() {
    const checkinText = selectedCheckin ? formatDate(selectedCheckin) : 'Thêm ngày';
    const checkoutText = selectedCheckout ? formatDate(selectedCheckout) : 'Thêm ngày';
    
    displayCheckin.textContent = checkinText;
    displayCheckout.textContent = checkoutText;
    modalCheckinValue.textContent = checkinText;
    modalCheckoutValue.textContent = checkoutText;
    
    hiddenCheckin.value = selectedCheckin || '';
    hiddenCheckout.value = selectedCheckout || '';
    
    // Update tabs
    document.querySelectorAll('.date-tab').forEach(tab => {
      tab.classList.remove('active');
    });
    if (!selectedCheckin || (!selectedCheckout && selectingCheckout)) {
      document.querySelector('.date-tab[data-tab="' + (selectingCheckout ? 'checkout' : 'checkin') + '"]').classList.add('active');
    }
    
    // Update price calculation
    updatePriceDisplay();
  }
  
  // Update price display
  function updatePriceDisplay() {
    if (selectedCheckin && selectedCheckout) {
      // Parse dates without timezone conversion
      const [y1, m1, d1] = selectedCheckin.split('-').map(Number);
      const [y2, m2, d2] = selectedCheckout.split('-').map(Number);
      const checkin = new Date(y1, m1 - 1, d1);
      const checkout = new Date(y2, m2 - 1, d2);
      const nights = Math.round((checkout - checkin) / (1000 * 60 * 60 * 24));
      const total = pricePerNight * nights;
      
      // Update price breakdown
      document.getElementById('priceText').textContent = `${formatNumber(pricePerNight)}₫ x ${nights} đêm`;
      document.getElementById('priceAmount').textContent = `${formatNumber(total)}₫`;
      document.getElementById('totalPrice').textContent = `${formatNumber(total)}₫`;
      
      // Show summary
      document.getElementById('bookingSummary').style.display = 'block';
    } else {
      // Hide summary if dates not selected
      document.getElementById('bookingSummary').style.display = 'none';
    }
  }
  
  // Format number with thousand separator
  function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }
  
  // Format date as dd/mm/yyyy from YYYY-MM-DD string
  function formatDate(dateStr) {
    if (!dateStr) return 'Thêm ngày';
    const parts = dateStr.split('-');
    const year = parts[0];
    const month = parts[1].padStart(2, '0');
    const day = parts[2].padStart(2, '0');
    return `${day}/${month}/${year}`;
  }
  
  // Check if date is disabled (booked)
  function isDateDisabled(date) {
    // Build YYYY-MM-DD string without timezone conversion
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    const dateStr = `${y}-${m}-${d}`;

    for (let range of disabledDates) {
      const fromStr = range.from; // already YYYY-MM-DD
      const toStr = range.to;     // already YYYY-MM-DD
      if (dateStr >= fromStr && dateStr <= toStr) {
        return true;
      }
    }
    return false;
  }
  
  // Check if date is in past
  function isPastDate(date) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return date < today;
  }
  
  // Render calendar
  function renderCalendar() {
    const container = document.getElementById('calendarContainer');
    container.innerHTML = '';
    
    const today = new Date();
    
    // Add navigation buttons
    const navDiv = document.createElement('div');
    navDiv.className = 'calendar-nav';
    navDiv.innerHTML = `
      <button class="btn-prev-month" ${currentMonthOffset <= 0 ? 'disabled' : ''}>
        <i class="fa-solid fa-chevron-left"></i>
      </button>
      <button class="btn-next-month">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    `;
    container.appendChild(navDiv);
    
    // Add month container
    const monthsContainer = document.createElement('div');
    monthsContainer.className = 'calendar-months-wrapper';
    
    // Render 2 months
    for (let i = 0; i < 2; i++) {
      const month = new Date(today.getFullYear(), today.getMonth() + currentMonthOffset + i, 1);
      const calendar = createMonthCalendar(month);
      monthsContainer.appendChild(calendar);
    }
    
    container.appendChild(monthsContainer);
    
    // Add event listeners for navigation
    const prevBtn = navDiv.querySelector('.btn-prev-month');
    const nextBtn = navDiv.querySelector('.btn-next-month');
    
    if (prevBtn) {
      prevBtn.addEventListener('click', function() {
        if (currentMonthOffset > 0) {
          currentMonthOffset--;
          renderCalendar();
        }
      });
    }
    
    if (nextBtn) {
      nextBtn.addEventListener('click', function() {
        currentMonthOffset++;
        renderCalendar();
      });
    }
  }
  
  // Create month calendar
  function createMonthCalendar(date) {
    const monthDiv = document.createElement('div');
    monthDiv.className = 'calendar-month';
    
    // Month header
    const monthHeader = document.createElement('div');
    monthHeader.className = 'calendar-month-header';
    const monthNames = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                        'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
    monthHeader.textContent = `${monthNames[date.getMonth()]} năm ${date.getFullYear()}`;
    monthDiv.appendChild(monthHeader);
    
    // Weekday headers
    const weekdaysDiv = document.createElement('div');
    weekdaysDiv.className = 'calendar-weekdays';
    const weekdays = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
    weekdays.forEach(day => {
      const dayDiv = document.createElement('div');
      dayDiv.className = 'calendar-weekday';
      dayDiv.textContent = day;
      weekdaysDiv.appendChild(dayDiv);
    });
    monthDiv.appendChild(weekdaysDiv);
    
    // Days grid
    const daysDiv = document.createElement('div');
    daysDiv.className = 'calendar-days';
    
    const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    
    // Get first day of week (Monday = 1, Sunday = 7)
    let firstDayOfWeek = firstDay.getDay();
    firstDayOfWeek = firstDayOfWeek === 0 ? 7 : firstDayOfWeek;
    
    // Empty cells before first day
    for (let i = 1; i < firstDayOfWeek; i++) {
      const emptyDiv = document.createElement('div');
      emptyDiv.className = 'calendar-day empty';
      daysDiv.appendChild(emptyDiv);
    }
    
    // Days
    for (let day = 1; day <= lastDay.getDate(); day++) {
      const currentDate = new Date(date.getFullYear(), date.getMonth(), day);
      // Format date as YYYY-MM-DD without timezone conversion
      const year = currentDate.getFullYear();
      const month = String(currentDate.getMonth() + 1).padStart(2, '0');
      const dayStr = String(currentDate.getDate()).padStart(2, '0');
      const dateStr = `${year}-${month}-${dayStr}`;
      
      const dayDiv = document.createElement('div');
      dayDiv.className = 'calendar-day';
      dayDiv.textContent = day;
      dayDiv.dataset.date = dateStr;
      
      // Check if past
      if (isPastDate(currentDate)) {
        dayDiv.classList.add('disabled', 'past');
      }
      // Check if booked
      else if (isDateDisabled(currentDate)) {
        dayDiv.classList.add('disabled', 'booked');
      }
      // Check if selected
      else {
        if (dateStr === selectedCheckin) {
          dayDiv.classList.add('selected', 'checkin');
        }
        if (dateStr === selectedCheckout) {
          dayDiv.classList.add('selected', 'checkout');
        }
        if (selectedCheckin && selectedCheckout && dateStr > selectedCheckin && dateStr < selectedCheckout) {
          dayDiv.classList.add('in-range');
        }
        
        // Click handler
        dayDiv.addEventListener('click', function() {
          handleDateClick(dateStr);
        });
      }
      
      daysDiv.appendChild(dayDiv);
    }
    
    monthDiv.appendChild(daysDiv);
    return monthDiv;
  }
  
  // Handle date click
  function handleDateClick(dateStr) {
    if (!selectedCheckin || (selectedCheckin && selectedCheckout)) {
      // Selecting checkin
      selectedCheckin = dateStr;
      selectedCheckout = null;
      selectingCheckout = true;
    } else {
      // Selecting checkout
      if (dateStr > selectedCheckin) {
        // Check if there are any booked dates in between
        let hasBlockedDates = false;
        // Build Date objects from components to iterate through the range (local time)
        const [sy, sm, sd] = selectedCheckin.split('-').map(Number);
        const [ey, em, ed] = dateStr.split('-').map(Number);
        const start = new Date(sy, sm - 1, sd);
        const end = new Date(ey, em - 1, ed);
        
        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
          if (isDateDisabled(d) && `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}` !== selectedCheckin) {
            hasBlockedDates = true;
            break;
          }
        }
        
        if (!hasBlockedDates) {
          selectedCheckout = dateStr;
          selectingCheckout = false;
        } else {
          alert('Không thể chọn khoảng thời gian này vì có ngày đã được đặt');
          return;
        }
      } else {
        // If clicked before checkin, reset
        selectedCheckin = dateStr;
        selectedCheckout = null;
      }
    }
    
    updateDateDisplays();
    renderCalendar();
  }
  
  // Guest counter handlers
  function updateGuestButtons() {
    const currentGuests = parseInt(bookingGuestsInput.value);
    minusBtn.disabled = currentGuests <= 1;
    plusBtn.disabled = currentGuests >= listingCapacity;
  }
  
  minusBtn.addEventListener('click', function() {
    let currentValue = parseInt(bookingGuestsInput.value);
    if (currentValue > 1) {
      bookingGuestsInput.value = currentValue - 1;
      updateGuestButtons();
    }
  });
  
  plusBtn.addEventListener('click', function() {
    let currentValue = parseInt(bookingGuestsInput.value);
    if (currentValue < listingCapacity) {
      bookingGuestsInput.value = currentValue + 1;
      updateGuestButtons();
    }
  });
  
  // Form validation
  document.getElementById('bookingForm').addEventListener('submit', function(e) {
    if (!selectedCheckin || !selectedCheckout) {
      e.preventDefault();
      alert('Vui lòng chọn ngày nhận phòng và trả phòng');
      return false;
    }
    
    const guests = parseInt(bookingGuestsInput.value);
    if (guests < 1 || guests > listingCapacity) {
      e.preventDefault();
      alert(`Số khách phải từ 1 đến ${listingCapacity}`);
      return false;
    }
  });
  
  // Initialize
  updateGuestButtons();
  if (selectedCheckin && selectedCheckout) {
    updateDateDisplays();
  } else {
    // Hide summary initially if no dates selected
    const bookingSummary = document.getElementById('bookingSummary');
    if (bookingSummary && (!selectedCheckin || !selectedCheckout)) {
      bookingSummary.style.display = 'none';
    }
  }
});
