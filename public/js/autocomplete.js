// Location Autocomplete from local JSON data
(function() {
  const locationInput = document.getElementById('locationInput');
  if (!locationInput) return;

  let autocompleteList = null;
  let currentFocus = -1;
  let debounceTimer = null;

  // Create autocomplete container
  function createAutocompleteList() {
    if (autocompleteList) return autocompleteList;
    
    autocompleteList = document.createElement('div');
    autocompleteList.className = 'autocomplete-list';
    autocompleteList.id = 'autocomplete-list';
    locationInput.parentNode.appendChild(autocompleteList);
    return autocompleteList;
  }

  // Close autocomplete list
  function closeAutocompleteList() {
    if (autocompleteList) {
      autocompleteList.innerHTML = '';
      autocompleteList.style.display = 'none';
    }
    currentFocus = -1;
  }

  // Fetch suggestions from list
  function fetchSuggestions(query) {
    if (query.length < 1) {
      closeAutocompleteList();
      return;
    }
    
    fetch(`./controller/autocomplete.php?q=${encodeURIComponent(query)}`)
      .then(response => response.json())
      .then(data => {
        closeAutocompleteList();
        
        if (!data.suggestions || data.suggestions.length === 0) {
          return;
        }

        const list = createAutocompleteList();
        list.innerHTML = '';
        list.style.display = 'block';

        data.suggestions.forEach((suggestion, index) => {
          const item = document.createElement('div');
          item.className = 'autocomplete-item';
          item.textContent = suggestion;
          
          // Click handler
          item.addEventListener('click', function() {
            locationInput.value = suggestion;
            closeAutocompleteList();
          });

          list.appendChild(item);
        });
      })
      .catch(error => {
        console.error('Autocomplete error:', error);
        closeAutocompleteList();
      });
  }

  // Input event listener with debounce
  locationInput.addEventListener('input', function(e) {
    clearTimeout(debounceTimer);
    const query = e.target.value;
    
    debounceTimer = setTimeout(() => {
      fetchSuggestions(query);
    }, 200); // faster 200ms debounce
  });

  // Keyboard navigation
  locationInput.addEventListener('keydown', function(e) {
    if (!autocompleteList) return;
    
    const items = autocompleteList.getElementsByClassName('autocomplete-item');
    
    if (e.keyCode === 40) { // Arrow Down
      e.preventDefault();
      currentFocus++;
      addActive(items);
    } else if (e.keyCode === 38) { // Arrow Up
      e.preventDefault();
      currentFocus--;
      addActive(items);
    } else if (e.keyCode === 13) { // Enter
      e.preventDefault();
      if (currentFocus > -1 && items[currentFocus]) {
        items[currentFocus].click();
      }
    } else if (e.keyCode === 27) { // Escape
      closeAutocompleteList();
    }
  });

  // Add active class to current item
  function addActive(items) {
    if (!items || items.length === 0) return;
    
    removeActive(items);
    
    if (currentFocus >= items.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = items.length - 1;
    
    items[currentFocus].classList.add('autocomplete-active');
  }

  // Remove active class from all items
  function removeActive(items) {
    for (let i = 0; i < items.length; i++) {
      items[i].classList.remove('autocomplete-active');
    }
  }

  // Close autocomplete when clicking outside
  document.addEventListener('click', function(e) {
    if (e.target !== locationInput) {
      closeAutocompleteList();
    }
  });

  // Focus event - reopen if has value
  locationInput.addEventListener('focus', function() {
    if (this.value.length >= 1) {
      fetchSuggestions(this.value);
    }
  });
})();
