// Toggle Search Form on List Page
document.addEventListener('DOMContentLoaded', function() {
  const toggleBtn = document.getElementById('toggleSearchForm');
  const formContainer = document.getElementById('searchFormContainer');
  
  if (toggleBtn && formContainer) {
    toggleBtn.addEventListener('click', function() {
      if (formContainer.style.display === 'none') {
        formContainer.style.display = 'block';
        toggleBtn.classList.add('active');
      } else {
        formContainer.style.display = 'none';
        toggleBtn.classList.remove('active');
      }
    });
  }
});
