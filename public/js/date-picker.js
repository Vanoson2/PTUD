// Flatpickr initialization for VN booking form
(function() {
  const checkinEl = document.getElementById('checkin');
  const checkoutEl = document.getElementById('checkout');
  if (!checkinEl || !checkoutEl || typeof flatpickr === 'undefined') return;

  // Compute base dates
  const today = new Date();
  today.setHours(0,0,0,0);
  const maxCheckin = new Date(today);
  maxCheckin.setMonth(maxCheckin.getMonth() + 3);

  // Helper to add days
  function addDays(date, days){ const d = new Date(date); d.setDate(d.getDate() + days); return d; }

  // Locale
  const vnLocale = flatpickr.l10ns.vn || undefined;

  // Init check-in
  const fpCheckin = flatpickr(checkinEl, {
    locale: vnLocale,
    dateFormat: 'Y-m-d',        // value submitted to server (PHP)
    altInput: true,
    altFormat: 'd/m/Y',         // user-friendly display
    allowInput: false,          // prevent typing
    minDate: today,
    maxDate: maxCheckin,
    clickOpens: true,
    disableMobile: true,
    defaultDate: checkinEl.value || undefined,
    onChange: function(selectedDates) {
      const ci = selectedDates && selectedDates[0] ? selectedDates[0] : null;
      if (!ci) return;
      const minCo = addDays(ci, 1);
      const maxCo = addDays(ci, 30);
      fpCheckout.set('minDate', minCo);
      fpCheckout.set('maxDate', maxCo);
      // Adjust checkout if out of range
      const currentCo = fpCheckout.selectedDates && fpCheckout.selectedDates[0];
      if (!currentCo || currentCo <= ci || currentCo > maxCo) {
        fpCheckout.setDate(minCo, true);
      }
    }
  });

  // Derive initial constraints for checkout from current checkin value
  const initialCheckin = fpCheckin.selectedDates && fpCheckin.selectedDates[0] ? fpCheckin.selectedDates[0] : today;
  const initialMinCo = addDays(initialCheckin, 1);
  const initialMaxCo = addDays(initialCheckin, 30);

  // Init check-out
  const fpCheckout = flatpickr(checkoutEl, {
    locale: vnLocale,
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd/m/Y',
    allowInput: false,          // prevent typing
    minDate: initialMinCo,
    maxDate: initialMaxCo,
    clickOpens: true,
    disableMobile: true,
    defaultDate: checkoutEl.value || undefined
  });
})();
