// Listing Filter - Lọc danh sách chỗ ở theo sidebar filters
(function() {
  'use strict';

  // Lấy tất cả listing card wrappers
  const listingCardWrappers = document.querySelectorAll('.listing-card-link');
  
  // Tự động tick các checkbox tiện nghi từ URL
  function autoCheckAmenitiesFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const amenityParam = urlParams.get('amenity');
    
    if (amenityParam) {
      // Split amenity IDs (có thể là "11,12" hoặc "10")
      const amenityIds = amenityParam.split(',');
      
      // Tick các checkbox tương ứng
      amenityIds.forEach(amenityId => {
        const checkbox = document.querySelector(`input[name="amenities[]"][value="${amenityId.trim()}"]`);
        if (checkbox) {
          checkbox.checked = true;
        }
      });
      
      // Trigger filter để áp dụng ngay
      setTimeout(() => {
        filterListings();
      }, 100);
    }
  }
  
  // Hàm lọc listings
  function filterListings() {
    // Lấy các filter đã chọn
    const selectedTypes = Array.from(document.querySelectorAll('input[name="type[]"]:checked'))
      .map(input => input.value);
    
    const selectedPrice = document.querySelector('input[name="price"]:checked')?.value || '';
    
    const selectedRatings = Array.from(document.querySelectorAll('input[name="rating[]"]:checked'))
      .map(input => parseInt(input.value));
    
    const selectedAmenities = Array.from(document.querySelectorAll('input[name="amenities[]"]:checked'))
      .map(input => input.value);
    
    let visibleCount = 0;
    const grid = document.querySelector('.listings-grid');
    const wrappersArray = Array.from(listingCardWrappers);
    const visibleWrappers = [];
    const hiddenWrappers = [];
    
    // Phân loại các wrapper thành visible và hidden
    wrappersArray.forEach(wrapper => {
      const card = wrapper.querySelector('.listing-card');
      if (!card) return;
      
      let shouldShow = true;
      
      // Lấy thông tin từ data attributes
      const listingType = card.dataset.placeTypeId;
      const listingPrice = parseFloat(card.dataset.price);
      const listingRating = parseFloat(card.dataset.rating);
      const listingAmenities = card.dataset.amenities ? card.dataset.amenities.split(',') : [];
      
      // Filter theo loại chỗ ở
      if (selectedTypes.length > 0 && !selectedTypes.includes(listingType)) {
        shouldShow = false;
      }
      
      // Filter theo giá
      if (selectedPrice && shouldShow) {
        if (selectedPrice === '0-500000' && listingPrice >= 500000) {
          shouldShow = false;
        } else if (selectedPrice === '500000-1000000' && (listingPrice < 500000 || listingPrice >= 1000000)) {
          shouldShow = false;
        } else if (selectedPrice === '1000000-1500000' && (listingPrice < 1000000 || listingPrice >= 1500000)) {
          shouldShow = false;
        } else if (selectedPrice === '1500000+' && listingPrice < 1500000) {
          shouldShow = false;
        }
      }
      
      // Filter theo rating (chỉ hiển thị nếu rating >= giá trị đã chọn)
      if (selectedRatings.length > 0 && shouldShow) {
        const minRating = Math.min(...selectedRatings);
        const maxRating = Math.max(...selectedRatings);
        
        // Nếu listing chưa có rating (rating = 0), không hiển thị khi filter rating
        if (listingRating === 0 || listingRating < minRating || listingRating > maxRating) {
          shouldShow = false;
        }
      }
      
      // Filter theo tiện nghi (listing phải có TẤT CẢ tiện nghi đã chọn)
      if (selectedAmenities.length > 0 && shouldShow) {
        const hasAllAmenities = selectedAmenities.every(amenityId => 
          listingAmenities.includes(amenityId)
        );
        if (!hasAllAmenities) {
          shouldShow = false;
        }
      }
      
      // Phân loại wrapper
      if (shouldShow) {
        visibleWrappers.push(wrapper);
        visibleCount++;
      } else {
        hiddenWrappers.push(wrapper);
      }
    });
    
    // Sắp xếp lại: visible wrappers trước, hidden wrappers sau
    visibleWrappers.forEach(wrapper => {
      wrapper.style.display = '';
      wrapper.style.order = '0';
      grid.appendChild(wrapper);
    });
    
    hiddenWrappers.forEach(wrapper => {
      wrapper.style.display = 'none';
      wrapper.style.order = '999';
    });
    
    // Cập nhật số lượng kết quả
    updateResultCount(visibleCount);
    
    // Hiển thị thông báo nếu không có kết quả
    updateNoResultsMessage(visibleCount);
  }
  
  // Cập nhật số lượng kết quả
  function updateResultCount(count) {
    const resultCountElement = document.querySelector('.search-title');
    if (resultCountElement) {
      const locationText = resultCountElement.textContent.split('tại')[1] || '';
      resultCountElement.textContent = `${count}+ chỗ ở tại${locationText}`;
    }
  }
  
  // Hiển thị/ẩn thông báo không có kết quả
  function updateNoResultsMessage(count) {
    const grid = document.querySelector('.listings-grid');
    let noResultsMsg = grid.querySelector('.no-results-message');
    
    if (count === 0) {
      if (!noResultsMsg) {
        noResultsMsg = document.createElement('div');
        noResultsMsg.className = 'no-results-message';
        noResultsMsg.style.cssText = 'grid-column: 1/-1; text-align: center; padding: 3rem;';
        noResultsMsg.innerHTML = `
          <p style="font-size: 1.25rem; color: #6b7280;">Không tìm thấy chỗ ở phù hợp với bộ lọc của bạn</p>
          <p style="color: #9ca3af; margin-top: 0.5rem;">Thử thay đổi bộ lọc để xem thêm kết quả</p>
        `;
        grid.appendChild(noResultsMsg);
      }
    } else {
      if (noResultsMsg) {
        noResultsMsg.remove();
      }
    }
  }
  
  // Gắn sự kiện change cho tất cả filter inputs
  function attachFilterListeners() {
    // Loại chỗ ở checkboxes
    document.querySelectorAll('input[name="type[]"]').forEach(input => {
      input.addEventListener('change', filterListings);
    });
    
    // Giá radio buttons
    document.querySelectorAll('input[name="price"]').forEach(input => {
      input.addEventListener('change', filterListings);
    });
    
    // Rating checkboxes
    document.querySelectorAll('input[name="rating[]"]').forEach(input => {
      input.addEventListener('change', filterListings);
    });
    
    // Amenities checkboxes
    document.querySelectorAll('input[name="amenities[]"]').forEach(input => {
      input.addEventListener('change', filterListings);
    });
  }
  
  // Khởi tạo khi DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      attachFilterListeners();
      autoCheckAmenitiesFromURL();
    });
  } else {
    attachFilterListeners();
    autoCheckAmenitiesFromURL();
  }
})();
