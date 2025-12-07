<?php
session_start();
require 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - Barangay San Vicente II</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <?php include 'favicon_links.php'; ?>
  <link rel="stylesheet" href="index.css?v=<?php echo filemtime(__DIR__ . '/index.css'); ?>">
  <style>
    body {
      overflow-y: auto !important;
      overflow-x: hidden !important;
      height: auto !important;
      min-height: 100vh;
    }
    .contact-page {
      padding-bottom: 60px;
    }
  </style>
</head>
<body class="contact-page-body">
  
<!--- NAVIGATION BAR --->
<nav>
  <div class="logo" onclick="window.location.href='index.php'" style="cursor: pointer;">
    <img src="images/logo.jpg" alt="logo">
    <h2>Barangay San Vicente II</h2>
  </div>

  <div class="menu-toggle" onclick="toggleMenu()">
    <span></span><span></span><span></span>
  </div>

  <div class="nav-links" id="navLinks">
    <a href="index.php#content">HOME</a>
    <a href="index.php#services">E-SERVICES</a>
    <a href="news-updates.php">NEWS & UPDATES</a>
    <a href="index.php#our-barangay">ABOUT US</a>
    <a href="contact.php">CONTACT</a>
  </div>

  <div class="nav-right">
    <div class="btns">
      <button class="btn get-started-btn" id="getStartedBtn">Login / Register</button>
    </div>
  </div>
</nav>

<!-- CONTACT PAGE -->
<section class="contact-page" style="margin-top: 60px; padding: 80px 60px;">
    <div class="contact-container" style="max-width: 1400px; margin: 0 auto;">
        <div class="contact-header">
            <h1 class="contact-title">Contact Information</h1>
            <p class="contact-subtitle">Get in touch with Barangay San Vicente II. We're here to serve you.</p>
        </div>
        
        <div class="contact-content-wrapper">
            <div class="contact-info-grid">
                <div class="contact-info-card">
                    <div class="contact-icon-wrapper">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <h3>Email</h3>
                    <p>info@barangaysanvicenteii.gov.ph</p>
                    <p>barangay.sanvicenteii@silang.gov.ph</p>
                </div>

                <div class="contact-info-card">
                    <div class="contact-icon-wrapper">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </div>
                    <h3>Phone</h3>
                    <p>0900-000-0000</p>
                    <p>(046) 123-4567</p>
                </div>

                <div class="contact-info-card">
                    <div class="contact-icon-wrapper">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <h3>Address</h3>
                    <p>Barangay San Vicente II</p>
                    <p>Municipality of Silang</p>
                    <p>Province of Cavite, Philippines</p>
                </div>

                <div class="contact-info-card">
                    <div class="contact-icon-wrapper">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <h3>Office Hours</h3>
                    <p>Monday - Friday: 8:00 AM - 5:00 PM</p>
                    <p>Saturday: 8:00 AM - 12:00 PM</p>
                    <p>Sunday: Closed</p>
                </div>
            </div>

            <div class="contact-map-wrapper">
                <h3>Location Map</h3>
                <div class="map-container">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3868.646687964016!2d120.9682551!3d14.2275865!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397cdb8e4a8b8c7%3A0x79c16a7b4a0b43ff!2sBarangay%20San%20Vicente%20II%2C%20Silang%2C%20Cavite!5e0!3m2!1sen!2sph!4v1696750000000!5m2!1sen!2sph"
                        width="100%"
                        height="450"
                        style="border:0; border-radius: 15px;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer-asym">
    <div class="footer-bottom">
        <p>&copy; 2025 Barangay San Vicente II. All rights reserved. | Municipality of Silang, Province of Cavite | Republic of the Philippines</p>
    </div>
</footer>

<script>
function toggleMenu() {
  document.getElementById("navLinks").classList.toggle("active");
}

document.getElementById("getStartedBtn").addEventListener("click", function() {
  window.location.href = "index.php#get-started";
});

// Clear old service worker caches so new CSS/JS load without hard refresh
if ('caches' in window) {
  caches.keys().then(function(keys) {
    keys.forEach(function(key) {
      if (key.startsWith('bsv-portal-') || key.startsWith('bsv-runtime-')) {
        caches.delete(key);
      }
    });
  });
}
</script>

</body>
</html>

