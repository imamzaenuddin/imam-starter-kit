import './bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/themes/bootstrap-5.css';

/**
 * SweetAlert2 Bootstrap 5 mixin — digunakan di seluruh aplikasi.
 * Expose sebagai window.Swal agar bisa dipakai di Blade / Alpine inline.
 */
window.Swal = Swal.mixin({
  theme: 'bootstrap-5',
  buttonsStyling: false,
  customClass: {
    confirmButton: 'btn btn-danger me-2',
    cancelButton: 'btn btn-outline-secondary',
  },
  reverseButtons: true,
});

/**
 * Initialize mobile menu toggle functionality
 */
function initMenuToggle() {
  const menuToggle = document.querySelector('.layout-menu-toggle');
  const layoutWrapper = document.querySelector('.layout-wrapper');
  const layoutMenu = document.querySelector('.layout-menu');

  if (!menuToggle || menuToggle.dataset.initialized) {
    return;
  }

  // Mark as initialized to prevent duplicate listeners
  menuToggle.dataset.initialized = 'true';

  menuToggle.addEventListener('click', function (e) {
    e.preventDefault();

    // Toggle the menu expanded class
    if (layoutWrapper) {
      const isExpanded = layoutWrapper.classList.contains('layout-menu-expanded');

      if (!isExpanded) {
        // Add transitioning class for animation
        layoutWrapper.classList.add('layout-transitioning');

        // Use setTimeout to ensure browser registers the initial state
        setTimeout(() => {
          layoutWrapper.classList.add('layout-menu-expanded');
        }, 10);

        // Remove transitioning class after animation completes
        setTimeout(() => {
          layoutWrapper.classList.remove('layout-transitioning');
        }, 400);
      } else {
        // Add transitioning class for animation
        layoutWrapper.classList.add('layout-transitioning');
        layoutWrapper.classList.remove('layout-menu-expanded');

        // Remove transitioning class after animation completes
        setTimeout(() => {
          layoutWrapper.classList.remove('layout-transitioning');
        }, 400);
      }
    }
  });
}

// Close menu when clicking outside on mobile
function handleOutsideClick(e) {
  if (window.innerWidth < 1200) {
    const layoutWrapper = document.querySelector('.layout-wrapper');
    const layoutMenu = document.querySelector('.layout-menu');
    const menuToggle = document.querySelector('.layout-menu-toggle');

    const isClickInside = layoutMenu?.contains(e.target) || menuToggle?.contains(e.target);
    if (!isClickInside && layoutWrapper?.classList.contains('layout-menu-expanded')) {
      // Add transitioning class for animation
      layoutWrapper.classList.add('layout-transitioning');
      layoutWrapper.classList.remove('layout-menu-expanded');

      // Remove transitioning class after animation completes
      setTimeout(() => {
        layoutWrapper.classList.remove('layout-transitioning');
      }, 400);
    }
  }
}

// Handle window resize - remove expanded class and overlay on desktop
function handleResize() {
  if (window.innerWidth >= 1200) {
    const layoutWrapper = document.querySelector('.layout-wrapper');
    layoutWrapper?.classList.remove('layout-menu-expanded');
    document.querySelector('.layout-overlay')?.remove();
  }
}


// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
  initMenuToggle();
  document.addEventListener('click', handleOutsideClick);
  window.addEventListener('resize', handleResize);
});

// Re-initialize after Livewire navigation
document.addEventListener('livewire:navigated', function () {
  initMenuToggle();
});
