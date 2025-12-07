function toggleDropdown(btn) {
    const dropdown = btn.closest(".dropdown");
    const menu = dropdown.querySelector(".dropdown-menu");
    menu.classList.toggle("show");
}

// Close dropdown when clicking outside
window.addEventListener("click", function(e) {
    if (!e.target.matches('.dropbtn')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

/* ======================
   TAB FUNCTIONALITY
====================== */
const tabs = document.querySelectorAll(".tab");
const tabContents = document.querySelectorAll(".tab-content");

tabs.forEach(tab => {
    tab.addEventListener("click", () => {
        // Remove active from all tabs
        tabs.forEach(t => t.classList.remove("active"));
        tab.classList.add("active");

        // Show corresponding tab content
        const target = tab.dataset.target;
        tabContents.forEach(content => content.classList.remove("active"));
        const targetContent = document.getElementById(target);
        if (targetContent) targetContent.classList.add("active");
    });
});

document.querySelectorAll('.edit-icon').forEach(icon => {
  icon.addEventListener('click', () => {
    const modalId = icon.dataset.modal;
    const modal = document.getElementById(modalId);
    if(modal) modal.style.display = 'block';
  });
});

document.querySelectorAll('.modal .close').forEach(span => {
  span.addEventListener('click', () => {
    span.closest('.modal').style.display = 'none';
  });
});

window.addEventListener('click', (e) => {
  if(e.target.classList.contains('modal')){
    e.target.style.display = 'none';
  }
});

// General modal opener
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.style.display = 'block';
  }
}

// Mobile menu toggle
function toggleMenu() {
  const nav = document.getElementById('navLinks');
  if (nav) {
    nav.classList.toggle('open');
  }
}

// Redirect to requests page
function goToSummary() {
  window.location.href = 'resident_request.php';
}

// Bind extra triggers once DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  const addFamilyBtn = document.getElementById('openAddFamily');
  if (addFamilyBtn) {
    addFamilyBtn.addEventListener('click', () => {
      openModal('addFamilyModal');
    });
  }

  document.querySelectorAll('[data-modal-target="photoModal"]').forEach(trigger => {
    trigger.addEventListener('click', () => openModal('photoModal'));
  });
});