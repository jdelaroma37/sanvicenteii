/* ============================
      MOBILE MENU
============================ */
function toggleMenu() {
  document.getElementById("navLinks").classList.toggle("active");
}

/* ============================
      INPUT VALIDATION
============================ */
// Allow only letters, spaces, hyphens, and apostrophes (for names)
function allowLettersOnly(event) {
  const char = String.fromCharCode(event.which || event.keyCode);
  // Allow letters, space, hyphen, apostrophe, and backspace/delete
  if (!/[A-Za-z\s'-]/.test(char) && !event.ctrlKey && !event.metaKey) {
    // Allow special keys: backspace, delete, tab, escape, enter, etc.
    const specialKeys = [8, 9, 27, 13, 46, 37, 38, 39, 40]; // backspace, tab, escape, enter, delete, arrow keys
    if (specialKeys.indexOf(event.keyCode) !== -1) {
      return true;
    }
    event.preventDefault();
    return false;
  }
  return true;
}

// Allow only numbers
function allowNumbersOnly(event) {
  const char = String.fromCharCode(event.which || event.keyCode);
  // Allow numbers and special keys
  if (!/[0-9]/.test(char) && !event.ctrlKey && !event.metaKey) {
    // Allow special keys: backspace, delete, tab, escape, enter, etc.
    const specialKeys = [8, 9, 27, 13, 46, 37, 38, 39, 40]; // backspace, tab, escape, enter, delete, arrow keys
    if (specialKeys.indexOf(event.keyCode) !== -1) {
      return true;
    }
    event.preventDefault();
    return false;
  }
  return true;
}

// Additional validation on input event to clean pasted content
document.addEventListener('DOMContentLoaded', function() {
  // Clean pasted content for name fields
  const nameFields = document.querySelectorAll('input[name="first_name"], input[name="middle_name"], input[name="last_name"], input[name="suffix"], input[name="place_of_birth"], input[name="nationality"], input[name="religion"], input[name="municipality"], input[name="city_province"], input[name="father_name"], input[name="mother_name"], input[name="guardian_name"], input[name="display_name"]');
  
  nameFields.forEach(field => {
    field.addEventListener('input', function(e) {
      // Remove any non-letter characters (except spaces, hyphens, apostrophes)
      this.value = this.value.replace(/[^A-Za-z\s'-]/g, '');
    });
    
    field.addEventListener('paste', function(e) {
      e.preventDefault();
      const pastedText = (e.clipboardData || window.clipboardData).getData('text');
      // Clean pasted text
      const cleanedText = pastedText.replace(/[^A-Za-z\s'-]/g, '');
      this.value = cleanedText;
    });
  });
  
  // Clean pasted content for number fields
  const numberFields = document.querySelectorAll('input[name="guardian_contact"], input[name="contact_number"], input[name="precinct_number"]');
  
  numberFields.forEach(field => {
    field.addEventListener('input', function(e) {
      // Remove any non-number characters
      this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    field.addEventListener('paste', function(e) {
      e.preventDefault();
      const pastedText = (e.clipboardData || window.clipboardData).getData('text');
      // Clean pasted text - numbers only
      const cleanedText = pastedText.replace(/[^0-9]/g, '');
      this.value = cleanedText;
    });
  });
});

/* ============================
      MODAL ELEMENTS
============================ */
const getStartedModal = document.getElementById("getStartedModal");
const loginModal = document.getElementById("loginModal");
const registerModal = document.getElementById("registerModal");
const adminLoginModal = document.getElementById("adminLoginModal");
const registrationSuccessModal = document.getElementById("registrationSuccessModal");
const registrationSuccessText = document.getElementById("registrationSuccessText");
const closeRegistrationSuccess = document.getElementById("closeRegistrationSuccess");
const registrationSuccessLoginBtn = document.getElementById("registrationSuccessLogin");
const registrationSuccessDismissBtn = document.getElementById("registrationSuccessDismiss");
const registerForm = document.getElementById("registerForm");
const registerStorageKey = "registerFormDraft";

const getStartedBtn = document.getElementById("getStartedBtn");
const loginBtns = document.querySelectorAll("#loginBtn, .loginBtn");
const registerBtns = document.querySelectorAll("#registerBtn, .registerBtn");
const feedbackLoginBtn = document.getElementById("feedbackLoginBtn");

const closeGetStarted = document.getElementById("closeGetStarted");
const closeLogin = document.getElementById("closeLogin");
const closeRegister = document.getElementById("closeRegister");
const closeAdminLogin = document.getElementById("closeAdminLogin");

const openAdminLogin = document.getElementById("openAdminLogin");
const openResidentLogin = document.getElementById("openResidentLogin");
const openRegister = document.getElementById("openRegister");
const forgotPassword = document.getElementById("forgotPassword");

// Choice buttons
const residentLoginChoice = document.getElementById("residentLoginChoice");
const adminLoginChoice = document.getElementById("adminLoginChoice");
const residentRegisterChoice = document.getElementById("residentRegisterChoice");
const adminRegisterChoice = document.getElementById("adminRegisterChoice");
const workerLoginChoice = document.getElementById("workerLoginChoice");
const workerRegisterChoice = document.getElementById("workerRegisterChoice");

/* ============================
   REGISTER FORM DRAFT HELPERS
============================ */
function applyRegisterTab(tabId) {
  const targetTab = tabId || "basic";
  if (typeof window.showTab === "function") {
    window.showTab(targetTab);
  } else {
    document.querySelectorAll(".tab-content").forEach((tab) => {
      tab.classList.toggle("active", tab.id === targetTab);
    });
    updateStepIndicator(targetTab);
  }
}

function persistRegisterState(forceTabId) {
  if (!registerForm) return;
  const formData = new FormData(registerForm);
  const data = {};

  formData.forEach((value, key) => {
    if (key === "photo") return; // avoid storing binary blobs
    data[key] = value;
  });

  const activeTabId =
    forceTabId ||
    document.querySelector(".tab-content.active")?.id ||
    "basic";

  sessionStorage.setItem(
    registerStorageKey,
    JSON.stringify({ data, activeTabId })
  );
}

function restoreRegisterState() {
  if (!registerForm) return;
  const raw = sessionStorage.getItem(registerStorageKey);
  if (!raw) {
    applyRegisterTab("basic");
    return;
  }

  try {
    const { data, activeTabId } = JSON.parse(raw);
    Object.keys(data || {}).forEach((key) => {
      const field = registerForm.querySelector(`[name="${key}"]`);
      if (field && field.type !== "file") {
        field.value = data[key];
      }
    });
    applyRegisterTab(activeTabId || "basic");
  } catch (err) {
    applyRegisterTab("basic");
  }
}

function bindRegisterDraftListeners() {
  if (!registerForm) return;
  registerForm.querySelectorAll("input, select").forEach((input) => {
    ["input", "change"].forEach((evt) =>
      input.addEventListener(evt, () => persistRegisterState())
    );
  });
}

bindRegisterDraftListeners();
restoreRegisterState();

/* ============================
      MODAL HELPERS
============================ */
function openModal(modal) {
  if (!modal) return;
  modal.style.display = "flex";
  modal.classList.add("active");
  document.body.style.overflow = "hidden";
}

function closeModal(modal) {
  if (!modal) return;
  modal.style.display = "none";
  modal.classList.remove("active");
  document.body.style.overflow = "";
}

/* ============================
   GET STARTED DEEP LINKING
============================ */
function shouldOpenGetStarted() {
  const hash = (window.location.hash || "").toLowerCase();
  const params = new URLSearchParams(window.location.search);
  return (
    hash === "#get-started" ||
    hash === "#getstarted" ||
    params.get("open") === "get-started" ||
    params.get("modal") === "get-started"
  );
}

function openGetStartedIfNeeded() {
  if (!getStartedModal) return;
  if (shouldOpenGetStarted()) {
    openModal(getStartedModal);
  }
}

/* ============================
      OPEN MODALS
============================ */
// Get Started Button
if (getStartedBtn) {
  getStartedBtn.addEventListener("click", (e) => {
    e.preventDefault();
    openModal(getStartedModal);
  });
}

// Explore Services Button (Hero) - Open Get Started Modal
const exploreBtn = document.getElementById("exploreBtn");
if (exploreBtn) {
  exploreBtn.addEventListener("click", (e) => {
    e.preventDefault();
    openModal(getStartedModal);
  });
}

// Get Started Button in About Section
const getStartedAboutBtn = document.getElementById("getStartedAboutBtn");
if (getStartedAboutBtn) {
  getStartedAboutBtn.addEventListener("click", (e) => {
    e.preventDefault();
    openModal(getStartedModal);
  });
}

// Choice buttons
if (residentLoginChoice) {
  residentLoginChoice.addEventListener("click", () => {
    closeModal(getStartedModal);
    openModal(loginModal);
  });
}

if (adminLoginChoice) {
  adminLoginChoice.addEventListener("click", () => {
    closeModal(getStartedModal);
    window.location.href = "admin/admin_login.php";
  });
}

if (residentRegisterChoice) {
  residentRegisterChoice.addEventListener("click", () => {
    closeModal(getStartedModal);
    openModal(registerModal);
    restoreRegisterState();
  });
}

if (adminRegisterChoice) {
  adminRegisterChoice.addEventListener("click", () => {
    closeModal(getStartedModal);
    window.location.href = "admin/admin_register.php";
  });
}

if (workerLoginChoice) {
  workerLoginChoice.addEventListener("click", () => {
    closeModal(getStartedModal);
    window.location.href = "worker/worker_login.php";
  });
}

if (workerRegisterChoice) {
  workerRegisterChoice.addEventListener("click", () => {
    closeModal(getStartedModal);
    window.location.href = "worker/worker_register.php";
  });
}

loginBtns.forEach((btn) =>
  btn.addEventListener("click", (e) => {
    e.preventDefault();
    openModal(loginModal);
  })
);

registerBtns.forEach((btn) =>
  btn.addEventListener("click", (e) => {
    e.preventDefault();
    openModal(registerModal);
    restoreRegisterState();
  })
);

if (feedbackLoginBtn) {
  feedbackLoginBtn.addEventListener("click", (e) => {
    e.preventDefault();
    openModal(loginModal);
  });
}

/* ============================
      CLOSE MODALS
============================ */
if (closeGetStarted) closeGetStarted.addEventListener("click", () => closeModal(getStartedModal));
if (closeLogin) closeLogin.addEventListener("click", () => closeModal(loginModal));
if (closeRegister)
  closeRegister.addEventListener("click", () => closeModal(registerModal));
if (closeAdminLogin)
  closeAdminLogin.addEventListener("click", () => closeModal(adminLoginModal));
if (closeRegistrationSuccess)
  closeRegistrationSuccess.addEventListener("click", () =>
    closeModal(registrationSuccessModal)
  );
if (registrationSuccessDismissBtn)
  registrationSuccessDismissBtn.addEventListener("click", () =>
    closeModal(registrationSuccessModal)
  );

/* ============================
   SWITCH BETWEEN LOGIN TYPES
============================ */
if (openAdminLogin) {
  openAdminLogin.addEventListener("click", (e) => {
    e.preventDefault();
    closeModal(loginModal);
    openModal(adminLoginModal);
  });
}

if (openResidentLogin) {
  openResidentLogin.addEventListener("click", (e) => {
    e.preventDefault();
    closeModal(adminLoginModal);
    openModal(loginModal);
  });
}

if (openRegister) {
  openRegister.addEventListener("click", (e) => {
    e.preventDefault();
    closeModal(loginModal);
    openModal(registerModal);
    restoreRegisterState();
  });
}

if (forgotPassword) {
  forgotPassword.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    // This forgotPassword element is only in the resident login modal
    // Directly redirect to resident forgot password page
    window.location.href = "resident/resident_forgot_password.php";
    return false;
  });
}

if (registrationSuccessLoginBtn) {
  registrationSuccessLoginBtn.addEventListener("click", (e) => {
    e.preventDefault();
    closeModal(registrationSuccessModal);
    openModal(loginModal);
  });
}

/* ============================
  CLOSE WHEN CLICKING OUTSIDE
============================ */
window.addEventListener("click", (e) => {
  if (e.target === getStartedModal) closeModal(getStartedModal);
  if (e.target === loginModal) closeModal(loginModal);
  // Keep the registration modal open to avoid losing progress
  if (e.target === registerModal) return;
  if (e.target === adminLoginModal) closeModal(adminLoginModal);
  if (e.target === registrationSuccessModal)
    closeModal(registrationSuccessModal);
});

/* ============================
        STEP PROGRESS
============================ */
function updateStepIndicator(activeTabId) {
  const steps = document.querySelectorAll(".step-item");
  steps.forEach((step) => {
    step.classList.remove("active");
    if (step.dataset.tab === activeTabId) step.classList.add("active");
  });
}

/* ============================
      NEXT + BACK BUTTONS
============================ */
document.addEventListener("DOMContentLoaded", () => {
  const nextBtns = document.querySelectorAll(".next-btn");
  const backBtns = document.querySelectorAll(".back-btn");
  const tabs = document.querySelectorAll(".tab-content");
  const serverMessage = document.getElementById("serverMessage");
  const registerSubmitBtn = registerForm?.querySelector(".submit-btn");
  let registerSubmitting = false;

  /* ---------- SHOW TAB ---------- */
  window.showTab = function (id) {
    tabs.forEach((tab) => tab.classList.remove("active"));
    document.getElementById(id).classList.add("active");

    updateStepIndicator(id);
  };

  /* ---------- NEXT ---------- */
  nextBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const nextTab = btn.getAttribute("data-next");
      showTab(nextTab);
      persistRegisterState(nextTab);
    });
  });

  /* ---------- BACK ---------- */
  backBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const prevTab = btn.getAttribute("data-prev");
      showTab(prevTab);
      persistRegisterState(prevTab);
    });
  });

  /* ---------- AUTO DISABLE NEXT WHEN EMPTY ---------- */
  tabs.forEach((tab) => {
    const nextBtn = tab.querySelector(".next-btn");
    if (!nextBtn) return;

    const inputs = tab.querySelectorAll("input, select");

    function checkInputs() {
      let allFilled = true;

      inputs.forEach((input) => {
        const name = input.getAttribute("name");

        if (name === "middle_name" || name === "suffix") return; // optional

        if (input.tagName === "SELECT" && !input.value.trim()) {
          allFilled = false;
        } else if (input.hasAttribute("required") && !input.value.trim()) {
          allFilled = false;
        }
      });

      nextBtn.disabled = !allFilled;
    }

    inputs.forEach((input) => {
      input.addEventListener("input", checkInputs);
      input.addEventListener("change", checkInputs);
    });

    checkInputs();
  });

  updateStepIndicator("basic"); // first step active
  restoreRegisterState();

  if (registerForm && registerSubmitBtn) {
    registerForm.addEventListener("submit", (e) => {
      if (registerSubmitting) {
        e.preventDefault();
        return;
      }
      registerSubmitting = true;
      persistRegisterState();
      registerSubmitBtn.disabled = true;
      registerSubmitBtn.textContent = "Registering...";
    });
  }

  const toggleButtons = document.querySelectorAll(".toggle-password");
  toggleButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const targetId = btn.getAttribute("data-target");
      const input = document.getElementById(targetId);
      if (!input) return;

      const shouldShow = input.type === "password";
      input.type = shouldShow ? "text" : "password";
      btn.textContent = shouldShow ? "Hide" : "Show";
      btn.setAttribute("aria-pressed", shouldShow ? "true" : "false");
      btn.setAttribute(
        "aria-label",
        `${shouldShow ? "Hide" : "Show"} password`
      );
    });
  });

  if (serverMessage?.dataset.message) {
    const rawMessage = serverMessage.dataset.message;
    const messageText = rawMessage.toLowerCase();
    if (messageText.includes("registration successful")) {
      sessionStorage.removeItem(registerStorageKey);
      if (registrationSuccessText)
        registrationSuccessText.textContent = rawMessage;
      if (registrationSuccessModal) openModal(registrationSuccessModal);
    }
  }

  // Auto-open Get Started when arriving with a hash/query
  openGetStartedIfNeeded();
});

/* ============================
   BASIC TAB REQUIRED FIELDS
============================ */
const basicRequired = [
  "first_name",
  "last_name",
  "gender",
  "date_of_birth",
  "place_of_birth",
];

function checkBasicFields() {
  let filled = true;

  basicRequired.forEach((id) => {
    const input = document.querySelector(`[name="${id}"]`);
    if (!input || input.value.trim() === "") {
      filled = false;
    }
  });

  const nextBtn = document.querySelector('.next-btn[data-next="address"]');
  if (nextBtn) nextBtn.disabled = !filled;
}

basicRequired.forEach((id) => {
  const input = document.querySelector(`[name="${id}"]`);
  if (input) {
    input.addEventListener("input", checkBasicFields);
  }
});

checkBasicFields();

document.querySelectorAll(".tab-btn").forEach((button) => {
  button.addEventListener("click", function () {

    const tabId = this.getAttribute("data-tab");

    // Only affect MVM paragraphs
    document.querySelectorAll(".mvm-tab-content p").forEach((p) => {
      p.style.display = "none";
    });

    document.getElementById(tabId).style.display = "block";

    document.querySelectorAll(".tab-btn").forEach((btn) => {
      btn.classList.remove("active");
    });
    this.classList.add("active");
  });
});
 
document.addEventListener("DOMContentLoaded", () => {
  const pbContainer = document.getElementById("pbContainer");
  const kagawadRowA = document.getElementById("kagawadRowA");
  const kagawadRowB = document.getElementById("kagawadRowB");

  const officials = JSON.parse(localStorage.getItem("officials")) || {
    punongBarangay: [],
    kagawad: []
  };

  renderAll();

  function renderAll() {
    renderSection(pbContainer, officials.punongBarangay);
    renderSection(kagawadRowA, officials.kagawad.slice(0, 4));
    renderSection(kagawadRowB, officials.kagawad.slice(4, 8));
  }

  function renderSection(container, list) {
    container.innerHTML = "";

    if (!list.length) {
      const p = document.createElement("p");
      p.textContent = "No officials added yet.";
      p.style.color = "#888";
      container.appendChild(p);
      return;
    }

    list.forEach(off => {
      const card = document.createElement("div");
      card.classList.add("official-card");

      if (container.id === "pbContainer") {
        card.classList.add("punong-barangay-card");
      }

      card.innerHTML = `
        <img src="${off.photo || 'images/profile.jpg'}" alt="${off.name}">
        <h4>${off.name}</h4>
        <p>${off.position}</p>
      `;

      container.appendChild(card);
    });
  }
});

// LIGHTBOX FUNCTIONALITY
function openLightbox(img) {
  document.getElementById('lightbox-img').src = img.src;
  document.getElementById('lightbox').style.display = 'flex';
}

function closeLightbox() {
  document.getElementById('lightbox').style.display = 'none';
}

/* ============================
   SCROLL ANIMATIONS
============================ */
document.addEventListener('DOMContentLoaded', () => {
  // Intersection Observer for scroll animations
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animated');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  // Observe all elements with animate-on-scroll class
  document.querySelectorAll('.animate-on-scroll').forEach(el => {
    observer.observe(el);
  });

  // Barangay Section - Tab Switching (Modern)
  const tabBtnsModern = document.querySelectorAll('.tab-btn-modern');
  const tabContentsModern = document.querySelectorAll('.mvm-tab-content-modern p');
  
  tabBtnsModern.forEach(btn => {
    btn.addEventListener('click', () => {
      const tabId = btn.getAttribute('data-tab');
      
      // Remove active from all buttons and contents
      tabBtnsModern.forEach(b => b.classList.remove('active'));
      tabContentsModern.forEach(c => c.classList.remove('active'));
      
      // Add active to clicked button and corresponding content
      btn.classList.add('active');
      const content = document.getElementById(`${tabId}-modern`);
      if (content) {
        content.classList.add('active');
      }
    });
  });

  // Full Page Navigation Arrows
  const navArrowPrev = document.getElementById('navArrowPrev');
  const navArrowNext = document.getElementById('navArrowNext');
  
  // Section navigation system - show one section at a time
  const sections = [
    { id: 'content', element: document.getElementById('content') },
    { id: 'services', element: document.querySelector('.services') },
    { id: 'our-barangay', element: document.getElementById('our-barangay') },
    { id: 'about', element: document.querySelector('.cta-section-asym') }
  ];
  
  let currentSectionIndex = 0;
  
  // Initialize - show only home section
  function initializeSections() {
    sections.forEach((section, index) => {
      if (section.element) {
        if (index === 0) {
          section.element.style.display = 'block';
          section.element.classList.add('active');
          section.element.classList.remove('hidden');
        } else {
          section.element.style.display = 'none';
          section.element.classList.remove('active');
          section.element.classList.add('hidden');
        }
      }
    });
    
    // Also hide announcements and contact sections
    const announcementsSection = document.querySelector('.announcements-section');
    const contactSection = document.querySelector('.contact-section');
    if (announcementsSection) announcementsSection.style.display = 'none';
    if (contactSection) contactSection.style.display = 'none';
  }
  
  function navigateToSection(index) {
    if (index >= 0 && index < sections.length && sections[index].element) {
      // Hide all sections first
      sections.forEach((section) => {
        if (section.element) {
          section.element.classList.remove('active');
          section.element.classList.add('hidden');
          section.element.style.display = 'none';
        }
      });
      
      // Show target section
      currentSectionIndex = index;
      const newSection = sections[currentSectionIndex];
      if (newSection.element) {
        newSection.element.style.display = 'block';
        newSection.element.classList.remove('hidden');
        setTimeout(() => {
          newSection.element.classList.add('active');
          // Scroll to top after showing
          window.scrollTo({ top: 0, behavior: 'instant' });
        }, 20);
      }
    }
  }
  
  // Helper: smooth scroll to hash if element exists
  function smoothScrollHash(hash) {
    const map = {
      '#content': 0,
      '#services': 1,
      '#our-barangay': 2,
      '#about': 3,
      '#about-section': 3
    };
    const targetIndex = map[hash];
    if (typeof targetIndex === 'number') {
      navigateToSection(targetIndex);
      return true;
    }
    return false;
  }

  // Initialize on load - prefer smooth scroll if target exists
  function initOnLoad() {
    const hash = window.location.hash;
    if (hash && smoothScrollHash(hash)) {
      return;
    }
    initializeSections();
  }
  
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOnLoad);
  } else {
    initOnLoad();
  }
  
  // Navigation links - smooth scroll (avoid flicker between sections)
  document.querySelectorAll('a[href*="#content"], a[href*="#services"], a[href*="#our-barangay"], a[href*="#about"]').forEach(link => {
    link.addEventListener('click', (e) => {
      const href = link.getAttribute('href');
      const currentPage = window.location.pathname.split('/').pop() || 'index.php';

      const hashMatch = href.match(/#(.+)$/);
      if (!hashMatch) return;
      const hash = '#' + hashMatch[1];

      // If link points to a different page, allow normal navigation
      if (href.includes('index.php') && currentPage !== 'index.php') {
        return;
      }

      // Same page: use navigateToSection to show only target section
      if (currentPage === 'index.php' || currentPage === '') {
        if (smoothScrollHash(hash)) {
          e.preventDefault();
        }

        // Close mobile menu if open
        const navLinks = document.getElementById("navLinks");
        if (navLinks && navLinks.classList.contains("active")) {
          navLinks.classList.remove("active");
        }
      }
    });
  });

  // Barangay "View all information" button toggle
  const viewAllBtn = document.querySelector('.barangay-view-all-btn');
  const expandedSections = document.getElementById('barangayExpanded');
  
  if (viewAllBtn && expandedSections) {
    viewAllBtn.addEventListener('click', () => {
      expandedSections.classList.toggle('active');
      if (expandedSections.classList.contains('active')) {
        viewAllBtn.textContent = 'Hide information';
      } else {
        viewAllBtn.textContent = 'View all information';
      }
    });
  }

  // Smooth scrolling for navigation links (only on index.php)
  const currentPage = window.location.pathname.split('/').pop() || 'index.php';
  if (currentPage === 'index.php' || currentPage === '') {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      
      // Skip if it's just #
      if (href === '#' || href === '#content') {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        // Close mobile menu if open
        const navLinks = document.getElementById("navLinks");
        if (navLinks && navLinks.classList.contains("active")) {
          navLinks.classList.remove("active");
        }
        return;
      }
      
      // If hash belongs to paged sections, let navigation handler handle it
      if (['#content', '#services', '#our-barangay', '#about', '#about-section'].includes(href)) {
        e.preventDefault();
        return;
      }
      
      // Find the target section
      let targetElement = null;
      if (href === '#services') {
          targetElement = document.querySelector('#services');
      } else if (href === '#our-barangay') {
        targetElement = document.querySelector('#our-barangay');
      } else if (href === '#about' || href === '#about-section') {
        targetElement = document.querySelector('.cta-section-asym');
      } else if (href === '#contact' || href === '#contact-section') {
        targetElement = document.querySelector('.contact-info-section');
      } else {
        targetElement = document.querySelector(href);
      }
      
      if (targetElement) {
        e.preventDefault();
        const offsetTop = targetElement.offsetTop - 60; // Account for fixed navbar
        window.scrollTo({
          top: offsetTop,
          behavior: 'smooth'
        });
      }
      
      // Close mobile menu if open
      const navLinks = document.getElementById("navLinks");
      if (navLinks && navLinks.classList.contains("active")) {
        navLinks.classList.remove("active");
      }
    });
    });
  }
  
  // Handle hash on page load (for when navigating from other pages)
  window.addEventListener('load', function() {
    const hash = window.location.hash;
    if (shouldOpenGetStarted()) {
      openGetStartedIfNeeded();
      return;
    }
    if (hash && smoothScrollHash(hash)) return;
    initializeSections();
  });
  
  // Also handle hash change with smooth scroll to avoid flicker
  window.addEventListener('hashchange', function() {
    const hash = window.location.hash;
    if (shouldOpenGetStarted()) {
      openGetStartedIfNeeded();
      return;
    }
    if (hash && smoothScrollHash(hash)) return;
  });
  
  // Handle logo click to scroll to top
  const logo = document.querySelector('.logo');
  if (logo) {
    logo.addEventListener('click', function(e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
  
  // Handle newsletter form submission
  const newsletterForm = document.getElementById('newsletterForm');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const email = this.querySelector('.contact-info-input').value;
      if (email) {
        alert('Thank you for subscribing! We will send updates to ' + email);
        this.querySelector('.contact-info-input').value = '';
      }
    });
  }
});

/* ============================
   CURVED CAROUSEL (STATIC - NO SCROLLING)
============================ */
document.addEventListener('DOMContentLoaded', () => {
  const carousel = document.getElementById('curvedCarousel');
  const items = carousel?.querySelectorAll('.carousel-item');
  
  if (!carousel || !items || items.length === 0) return;
  
  const totalItems = items.length;
  const angleStep = 360 / totalItems;
  
  // Responsive radius
  function getRadius() {
    const width = window.innerWidth;
    if (width <= 768) return 200;
    if (width <= 1024) return 240;
    return 280;
  }
  
  let radius = getRadius();
  
  // Update radius on resize
  window.addEventListener('resize', () => {
    radius = getRadius();
    updateCarousel();
  });
  
  // Position items in a curved path (static - all visible)
  function updateCarousel() {
    items.forEach((item, index) => {
      const angle = index * angleStep;
      const radian = (angle * Math.PI) / 180;
      
      // Calculate position on curved path
      const x = Math.sin(radian) * radius;
      const z = -Math.cos(radian) * radius;
      const rotationY = angle;
      
      // Apply 3D transforms
      item.style.transform = `
        translateX(${x}px) 
        translateZ(${z}px) 
        rotateY(${rotationY}deg)
      `;
      
      // Scale and opacity based on position (center items more prominent)
      const centerIndex = Math.floor(totalItems / 2);
      const distanceFromCenter = Math.abs(index - centerIndex);
      const maxDistance = Math.ceil(totalItems / 2);
      const scale = 1 - (distanceFromCenter / maxDistance) * 0.3;
      const opacity = 1 - (distanceFromCenter / maxDistance) * 0.2;
      
      item.style.opacity = opacity;
      item.style.transform += ` scale(${scale})`;
      
      // Update z-index for proper stacking
      item.style.zIndex = totalItems - distanceFromCenter;
    });
  }
  
  // Initialize
  updateCarousel();
});

/* ============================
   VISION/MISSION CAROUSEL
============================ */
document.addEventListener('DOMContentLoaded', () => {
  const carousel = document.getElementById('visionMissionCarousel');
  if (!carousel) return;
  
  const items = carousel.querySelectorAll('.vm-carousel-item');
  const indicators = document.querySelectorAll('.vm-indicator');
  let currentIndex = 0;
  let autoPlayInterval;
  
  // Function to show specific slide
  function showSlide(index) {
    // Remove active class from all items and indicators
    items.forEach((item, i) => {
      if (i === index) {
        item.classList.add('active');
      } else {
        item.classList.remove('active');
      }
    });
    
    indicators.forEach((indicator, i) => {
      if (i === index) {
        indicator.classList.add('active');
      } else {
        indicator.classList.remove('active');
      }
    });
    
    currentIndex = index;
  }
  
  // Function to go to next slide
  function nextSlide() {
    const nextIndex = (currentIndex + 1) % items.length;
    showSlide(nextIndex);
  }
  
  // Function to go to previous slide
  function prevSlide() {
    const prevIndex = (currentIndex - 1 + items.length) % items.length;
    showSlide(prevIndex);
  }
  
  // Auto-play carousel (change slide every 5 seconds)
  function startAutoPlay() {
    autoPlayInterval = setInterval(() => {
      nextSlide();
    }, 5000);
  }
  
  function stopAutoPlay() {
    if (autoPlayInterval) {
      clearInterval(autoPlayInterval);
    }
  }
  
  // Add click event listeners to indicators
  indicators.forEach((indicator, index) => {
    indicator.addEventListener('click', () => {
      showSlide(index);
      stopAutoPlay();
      startAutoPlay(); // Restart autoplay after manual navigation
    });
  });
  
  // Pause autoplay on hover, resume on mouse leave
  carousel.addEventListener('mouseenter', stopAutoPlay);
  carousel.addEventListener('mouseleave', startAutoPlay);
  
  // Initialize autoplay
  startAutoPlay();
  
  // Initialize first slide
  showSlide(0);
});