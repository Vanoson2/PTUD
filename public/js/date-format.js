// Date format handler: display dd/mm/yyyy, submit ISO yyyy-mm-dd
(function() {
  'use strict';
  
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.search-form');
    const displayInputs = document.querySelectorAll('.date-display');
    
    // Helper functions
    function toISO(ddmmyyyy) {
      const parts = (ddmmyyyy || '').split('/');
      if (parts.length !== 3) return '';
      const [d, m, y] = parts;
      if (d.length !== 2 || m.length !== 2 || y.length !== 4) return '';
      return `${y}-${m}-${d}`;
    }
    
    function toDisplay(iso) {
      const parts = (iso || '').split('-');
      if (parts.length !== 3) return '';
      return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    
    // Setup each date input
    displayInputs.forEach(function(display) {
      const isoField = document.getElementById(display.id + '_iso');
      
      // Format as user types
      display.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
        if (value.length > 8) value = value.slice(0, 8);
        
        if (value.length >= 5) {
          value = value.slice(0, 2) + '/' + value.slice(2, 4) + '/' + value.slice(4);
        } else if (value.length >= 3) {
          value = value.slice(0, 2) + '/' + value.slice(2);
        }
        
        e.target.value = value;
        if (isoField) isoField.value = toISO(value);
      });
      
      // Open native date picker on click/focus
      function openNativePicker() {
        const hidden = document.createElement('input');
        hidden.type = 'date';
        hidden.style.position = 'absolute';
        hidden.style.opacity = '0';
        hidden.style.pointerEvents = 'none';
        
        // Set current value if available
        const currentISO = isoField && isoField.value ? isoField.value : toISO(display.value);
        if (currentISO) hidden.value = currentISO;
        
        document.body.appendChild(hidden);
        
        hidden.addEventListener('change', function() {
          if (hidden.value) {
            const displayValue = toDisplay(hidden.value);
            display.value = displayValue;
            if (isoField) isoField.value = hidden.value;
          }
          document.body.removeChild(hidden);
        });
        
        // Try to open picker
        if (typeof hidden.showPicker === 'function') {
          hidden.showPicker();
        } else {
          hidden.focus();
        }
      }
      
      display.addEventListener('focus', openNativePicker);
      display.addEventListener('click', openNativePicker);
    });
    
    // Validate on form submit
    if (form) {
      form.addEventListener('submit', function(e) {
        let isValid = true;
        
        displayInputs.forEach(function(display) {
          const isoField = document.getElementById(display.id + '_iso');
          const iso = toISO(display.value);
          
          if (isoField) isoField.value = iso;
          
          if (display.value && !iso) {
            isValid = false;
            display.classList.add('is-invalid');
          } else {
            display.classList.remove('is-invalid');
          }
        });
        
        if (!isValid) {
          e.preventDefault();
          alert('Vui lòng nhập ngày hợp lệ theo định dạng dd/mm/yyyy');
        }
      });
    }
  });
})();
