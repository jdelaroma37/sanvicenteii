<?php

session_start();

// Redirect if already logged in
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/admin_dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'resident') {
        header('Location: resident/resident_dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'worker') {
        header('Location: worker/worker_dashboard.php');
        exit;
    }
}

$logged_in=isset($_SESSION['user_id']);
$username = $logged_in ? $_SESSION['first_name'] : '';

// Check admin and worker limits
require 'config.php';

$admin_limit = 3; // Maximum 3 admins
$worker_limit = 15; // Maximum 15 workers (adjustable)

$admin_count = 0;
$worker_count = 0;
$admin_registration_allowed = true;
$worker_registration_allowed = true;

try {
    // Count all admins (not just active)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admins");
    $admin_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Count all workers with passwords (registered workers)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM barangay_workers WHERE password IS NOT NULL AND password != ''");
    $worker_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Check if registration is allowed
    $admin_registration_allowed = $admin_count < $admin_limit;
    $worker_registration_allowed = $worker_count < $worker_limit;
} catch (Exception $e) {
    // If tables don't exist yet, allow registration
    $admin_registration_allowed = true;
    $worker_registration_allowed = true;
}

// Load announcements
$announcements_file = __DIR__ . '/data/announcements/announcement.json';
$announcements = [];
$latest_announcement = null;

if (file_exists($announcements_file)) {
    $announcements = json_decode(file_get_contents($announcements_file), true) ?? [];
    if (!is_array($announcements)) {
        $announcements = [];
    }
    
    // Sort by date (newest first)
    usort($announcements, function($a, $b) {
        $dateA = $a['date_value'] ?? $a['created_at'] ?? '';
        $dateB = $b['date_value'] ?? $b['created_at'] ?? '';
        if ($dateA && $dateB) {
            return strcmp($dateB, $dateA);
        }
        return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
    });
    
    // Get the latest announcement
    if (!empty($announcements)) {
        $latest_announcement = $announcements[0];
    }
}

// Load programs and events (admin creates data/programs_events.json)
$programs_file = __DIR__ . '/data/programs_events.json';
$programs = [];
if (file_exists($programs_file)) {
    $programs = json_decode(file_get_contents($programs_file), true) ?? [];
    if (!is_array($programs)) {
        $programs = [];
    }
}

// Load projects (admin creates data/projects.json)
$projects_file = __DIR__ . '/data/projects.json';
$projects = [];
if (file_exists($projects_file)) {
    $projects = json_decode(file_get_contents($projects_file), true) ?? [];
    if (!is_array($projects)) {
        $projects = [];
    }
}

// Helper functions to normalize dates for the feed grid
$normalizeDate = function (?string $value): string {
    if (empty($value)) {
        return 'TBD';
    }
    $ts = strtotime($value);
    return $ts ? date('M d, Y', $ts) : $value;
};
$toTimestamp = function (?string $value): int {
    if (empty($value)) {
        return 0;
    }
    $ts = strtotime($value);
    return $ts ? $ts : 0;
};

// Build a single merged feed (announcements, programs/events, projects)
$community_feed = [];

foreach ($announcements as $ann) {
    $rawDate = $ann['date_value'] ?? $ann['date'] ?? $ann['created_at'] ?? '';
    $community_feed[] = [
        'id' => $ann['id'] ?? uniqid('ann_'),
        'label' => $ann['category'] ?? ($ann['type'] ?? 'Announcement'),
        'title' => $ann['title'] ?? 'Announcement',
        'description' => $ann['description'] ?? ($ann['content'] ?? ''),
        'image' => $ann['image'] ?? '',
        'meta' => $ann['type'] ?? 'Announcement',
        'extra' => '',
        'display_date' => $normalizeDate($rawDate),
        'timestamp' => $toTimestamp($rawDate),
    ];
}

foreach ($programs as $prog) {
    $rawDate = $prog['start_date'] ?? $prog['created_at'] ?? '';
    $timeLabel = '';
    if (!empty($prog['start_time'])) {
        $timeLabel = 'Starts ' . date('g:i A', strtotime($prog['start_time']));
    }
    $community_feed[] = [
        'id' => $prog['id'] ?? uniqid('prog_'),
        'label' => $prog['type'] ?? 'Program',
        'title' => $prog['title'] ?? 'Program / Event',
        'description' => $prog['description'] ?? '',
        'image' => $prog['image'] ?? '',
        'meta' => $prog['location'] ?? '',
        'extra' => $timeLabel,
        'display_date' => $normalizeDate($rawDate),
        'timestamp' => $toTimestamp($rawDate),
    ];
}

foreach ($projects as $proj) {
    $rawDate = $proj['start_date'] ?? $proj['created_at'] ?? '';
    $progress = isset($proj['progress']) ? ('Progress: ' . intval($proj['progress']) . '%') : '';
    $community_feed[] = [
        'id' => $proj['id'] ?? uniqid('proj_'),
        'label' => 'Project',
        'title' => $proj['title'] ?? 'Project',
        'description' => $proj['description'] ?? '',
        'image' => '',
        'meta' => !empty($proj['status']) ? ('Status: ' . $proj['status']) : '',
        'extra' => $progress,
        'display_date' => $normalizeDate($rawDate ?: ($proj['target_date'] ?? '')),
        'timestamp' => $toTimestamp($rawDate ?: ($proj['target_date'] ?? '')),
    ];
}

// Latest items for the homepage grid (Instagram-style)
usort($community_feed, function($a, $b) {
    return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
});
$home_feed = array_slice($community_feed, 0, 9);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay San Vicente II</title>
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
  </style>
  <script>
    // Toggle hamburger menu for mobile
    function toggleMenu() {
      document.getElementById("navLinks").classList.toggle("active");
    }

    // Dropdown toggle for profile menu
    function toggleDropdown() {
      document.querySelector(".dropdown-menu").classList.toggle("show");
    }

    // Input Validation Functions
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
  </script>
</head>
<body>
  
<!--- NAVIGATION BAR --->
<nav>
  <div class="logo" onclick="window.location.href='http://localhost/kaloka/'" style="cursor: pointer;">
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

<!-- Main Content -->
<section class="content" id="content">

    <!-- HERO SECTION - SPLIT LAYOUT -->
    <section class="hero-split">
        <div class="hero-container">
            <!-- Left Side - Text Content -->
            <div class="hero-text-side">
                <span class="hero-tagline">Welcome to</span>
                <h1 class="hero-main-title">
                    Discover<br>
                    <span class="hero-highlight">Barangay San Vicente II</span>
                </h1>
                <p class="hero-description">
                    A peaceful and progressive community in the Municipality of Silang, Province of Cavite.<br>
                    Experience unity, public service, and a welcoming neighborhood.<br>
                    Lets build a beautiful environment within our barangay!
                </p>
                
                <!-- Buttons Below Text -->
                <div class="hero-cta-group">
                    <button class="hero-primary-btn" id="exploreBtn">
                        <span>Get Started</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <button class="hero-secondary-btn" onclick="window.open('https://www.google.com/maps/search/Barangay+San+Vicente+II,+Silang+Cavite/@14.2275865,120.9682551,17z', '_blank')">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span>View Location</span>
                    </button>
                </div>
            </div>

            <!-- Right Side - Programs & Reminders -->
            <div class="hero-programs-side">
                <div class="programs-header">
                    <h3>Programs & Reminders</h3>
                    <p>Stay updated with our regular schedules and advisories.</p>
                </div>
                <div class="programs-list">
                    <div class="program-card">
                        <div class="program-icon">🗓</div>
                        <div class="program-body">
                            <h4>Garbage Collection</h4>
                            <p>Tue - Fri - Sun at 7:00 AM</p>
                        </div>
                        <div class="program-dots"><span></span><span></span><span></span><span></span><span></span></div>
                    </div>
                    <div class="program-card">
                        <div class="program-icon">🔔</div>
                        <div class="program-body">
                            <h4>Curfew Advisory</h4>
                            <p>Observe local curfew hours for minors.</p>
                        </div>
                        <div class="program-dots"><span></span><span></span><span></span><span></span><span></span></div>
                    </div>
                    <div class="program-card">
                        <div class="program-icon">♻️</div>
                        <div class="program-body">
                            <h4>No Burning of Trash</h4>
                            <p>Avoid open burning. Violations may be penalized.</p>
                        </div>
                        <div class="program-dots"><span></span><span></span><span></span><span></span><span></span></div>
                    </div>
                    <div class="program-card">
                        <div class="program-icon">🧹</div>
                        <div class="program-body">
                            <h4>Clean-Up Drive</h4>
                            <p>Join our monthly barangay clean-up event.</p>
                        </div>
                        <div class="program-dots"><span></span><span></span><span></span><span></span><span></span></div>
                    </div>
                    <div class="program-card">
                        <div class="program-icon">🪪</div>
                        <div class="program-body">
                            <h4>Barangay Clearance Reminder</h4>
                            <p>Bring valid ID & proof of residency.</p>
                        </div>
                        <div class="program-dots"><span></span><span></span><span></span><span></span><span></span></div>
                    </div>
                </div>
            </div>
        </div>
    </section> <!-- END HERO -->

</section> <!-- END CONTENT -->

<!-- SERVICE BOXES - GRID LAYOUT -->
<section class="services" id="services">
    <div class="services-header">
        <h1 class="services-title">E-Services / Online Transactions</h1>
        <p class="services-subtitle">Request barangay documents and services online. Save time and avoid long queues.</p>
    </div>

    <div class="services-grid-container">
        <!--1.-->
        <div class="service-card">
            <div class="service-icon-wrapper">
                <svg class="service-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
            </div>
            <h3 class="service-card-title">Barangay Clearance</h3>
            <p class="service-card-desc">Usually for employment, business, or other legal requirements.</p>
            <button class="service-card-btn" id="loginBtn">Proceed</button>
        </div>

        <!--2.-->
        <div class="service-card">
            <div class="service-icon-wrapper">
                <svg class="service-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <h3 class="service-card-title">Certificate of Residency</h3>
            <p class="service-card-desc">Proof that you officially reside in the barangay.</p>
            <button class="service-card-btn" id="loginBtn">Proceed</button>
        </div>

        <!--3.-->
        <div class="service-card">
            <div class="service-icon-wrapper">
                <svg class="service-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
            </div>
            <h3 class="service-card-title">Certificate of Indigency</h3>
            <p class="service-card-desc">Proof of indigency for government assistance.</p>
            <button class="service-card-btn" id="loginBtn">Proceed</button>
        </div>

        <!--4.-->
        <div class="service-card">
            <div class="service-icon-wrapper">
                <svg class="service-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="8" width="18" height="4" rx="1"/>
                    <path d="M12 8v13"/>
                    <path d="M8 8V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
            </div>
            <h3 class="service-card-title">Barangay Business Clearance</h3>
            <p class="service-card-desc">Authorization to operate your business within the barangay.</p>
            <button class="service-card-btn" id="loginBtn">Proceed</button>
        </div>

        <!--5.-->
        <div class="service-card">
            <div class="service-icon-wrapper">
                <svg class="service-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <h3 class="service-card-title">Business Permit</h3>
            <p class="service-card-desc">Official authorization for business operation within the barangay.</p>
            <button class="service-card-btn" id="loginBtn">Proceed</button>
        </div>

        <!--6.-->
        <div class="service-card">
            <div class="service-icon-wrapper">
                <svg class="service-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <h3 class="service-card-title">Certificate of Good Moral Character</h3>
            <p class="service-card-desc">Proof of good standing within the community.</p>
            <button class="service-card-btn" id="loginBtn">Proceed</button>
        </div>

        <!--7.-->
        <div class="service-card">
            <div class="service-icon-wrapper">
                <svg class="service-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <h3 class="service-card-title">Certificate of Ownership</h3>
            <p class="service-card-desc">Proof of property ownership within the barangay.</p>
            <button class="service-card-btn" id="loginBtn">Proceed</button>
        </div>

        <!--8.-->
        <div class="service-card">
            <div class="service-icon-wrapper">
                <svg class="service-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
            </div>
            <h3 class="service-card-title">Certificate for Solo Parents / Senior Citizen / PWD</h3>
            <p class="service-card-desc">To support benefits or government assistance eligibility.</p>
            <button class="service-card-btn" id="loginBtn">Proceed</button>
        </div>

        <!--9.-->
        <div class="service-card">
            <div class="service-icon-wrapper">
                <svg class="service-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="12" y1="18" x2="12" y2="12"/>
                    <line x1="9" y1="15" x2="15" y2="15"/>
                </svg>
            </div>
            <h3 class="service-card-title">Incident Report</h3>
            <p class="service-card-desc">Record and request for official incident reports.</p>
            <button class="service-card-btn" id="loginBtn">Proceed</button>
        </div>
    </div>
</section>


<!-- OUR BARANGAY SECTION -->
<section id="our-barangay">
    <div class="barangay-wireframe-container">
        <div class="barangay-hero-banner">
            <div class="barangay-hero-overlay">
                <span class="barangay-chip">Barangay Hall</span>
                <h1 class="barangay-hero-title">Barangay San Vicente II</h1>
                <p class="barangay-hero-subtitle">Municipality of Silang, Province of Cavite</p>
                <p class="barangay-hero-desc">
                    Welcome to our community—serving with unity, safety, and good governance.
                </p>
            </div>
        </div>

        <!-- Bento grid content cloned from Barangay Dashboard (soft blue palette) -->
        <div class="brgy-bento-grid">
            <!-- Left tall: overview + vision/mission -->
            <div class="brgy-bento-left">
                <h2 class="brgy-bento-title">Barangay San Vicente II</h2>

                <div class="brgy-section">
                    <div class="brgy-section-header">
                        <span>Location</span>
                    </div>
                    <div class="brgy-section-body">
                        <p><strong>Bayan/Lungsod:</strong> Silang</p>
                        <p><strong>Lalawigan:</strong> Cavite</p>
                        <p><strong>Rehiyon:</strong> IV-A</p>
                        <p><strong>Distrito:</strong> District V</p>
                    </div>
                </div>

                <div class="brgy-section">
                    <div class="brgy-section-header"><span>Vision</span></div>
                    <div class="brgy-section-body">
                        Barangay San Vicente II aims to become one of the progressive barangays in Silang, Cavite with civilized, God-fearing citizens with excellent social knowledge, maintaining economic and social environmental conditions for the community that is free from poverty, pollution, and crimes under decent and strong leadership.
                    </div>
                </div>

                <div class="brgy-section">
                    <div class="brgy-section-header"><span>Mission</span></div>
                    <div class="brgy-section-body">
                        To help the constituents in every possible way for the problems of the Barangay. And to develop basic social services for the citizens and to provide social, economic, environmental, and infrastructure assistance under the spirit of transparency and good governance.
                    </div>
                </div>

                <div class="brgy-section">
                    <div class="brgy-section-header"><span>Goal</span></div>
                    <div class="brgy-section-body">
                        To have preparation for possible calamities and disasters. And to be responsible for our environment and teach residents what they should do during disasters.
                    </div>
                </div>

                <div class="brgy-section">
                    <div class="brgy-section-header"><span>Objectives</span></div>
                    <div class="brgy-section-body">
                        To help in providing information on proper preparation for calamities that may come to the residents of Barangay San Vicente II, and to give sufficient knowledge to the residents about disasters.
                    </div>
                </div>
            </div>

            <!-- Right top: key stats -->
            <div class="brgy-bento-card">
                <div class="brgy-card-header">
                    <span>Key Statistics</span>
                </div>
                <div class="brgy-stats-grid">
                    <div class="brgy-stat">
                        <div class="brgy-stat-value">5,140</div>
                        <div class="brgy-stat-label">Population</div>
                    </div>
                    <div class="brgy-stat">
                        <div class="brgy-stat-value">91.35</div>
                        <div class="brgy-stat-label">Hectares</div>
                    </div>
                    <div class="brgy-stat">
                        <div class="brgy-stat-value">1,717</div>
                        <div class="brgy-stat-label">Households</div>
                    </div>
                </div>
                <div class="brgy-stats-grid" style="margin-top:20px;">
                    <div class="brgy-stat">
                        <div class="brgy-stat-value">7</div>
                        <div class="brgy-stat-label">Puroks</div>
                    </div>
                    <div class="brgy-stat">
                        <div class="brgy-stat-value">2</div>
                        <div class="brgy-stat-label">Sitios</div>
                    </div>
                    <div class="brgy-stat">
                        <div class="brgy-stat-value">14</div>
                        <div class="brgy-stat-label">Precincts</div>
                    </div>
                </div>
            </div>

            <!-- Right middle: boundaries / classification -->
            <div class="brgy-bento-card">
                <div class="brgy-section">
                    <div class="brgy-section-header"><span>Boundaries</span></div>
                    <ul class="brgy-list">
                        <li><strong>Hilaga (North):</strong> Barangay Biga 1</li>
                        <li><strong>Timog (South):</strong> Barangay San Miguel 1</li>
                        <li><strong>Silangan (East):</strong> Barangay San Vicente 1</li>
                        <li><strong>Kanluran (West):</strong> Barangay Paligawan</li>
                    </ul>
                </div>
                <div class="brgy-section">
                    <div class="brgy-section-header"><span>Land Classification</span></div>
                    <div class="brgy-badges">
                        <span class="brgy-badge">Residensyal: 85 ha</span>
                        <span class="brgy-badge">Pang-Komersiyo: 2 ha</span>
                        <span class="brgy-badge">Agrikultura: 1.15 ha</span>
                        <span class="brgy-badge">Idle Land: 1.20 ha</span>
                        <span class="brgy-badge">Pang Turismo: 2 ha</span>
                    </div>
                </div>
                <div class="brgy-section">
                    <div class="brgy-section-header"><span>Classification</span></div>
                    <div class="brgy-badges">
                        <span class="brgy-badge accent">Rural</span>
                    </div>
                </div>
            </div>

            <!-- Right bottom: quick facts -->
            <div class="brgy-bento-card">
                <div class="brgy-card-header small">
                    <span>Quick Facts</span>
                </div>
                <div class="brgy-quick-grid">
                    <div>
                        <div class="brgy-quick-label">Registered Voters</div>
                        <div class="brgy-quick-value">2,444</div>
                    </div>
                    <div>
                        <div class="brgy-quick-label">Distance from Town</div>
                        <div class="brgy-quick-value">~300 km</div>
                    </div>
                    <div>
                        <div class="brgy-quick-label">Hotline</div>
                        <div class="brgy-quick-value">0905-880-0153</div>
                    </div>
                </div>
            </div>

            <!-- Summary cards -->
            <div class="brgy-summary-grid">
                <div class="brgy-summary-card">
                    <div class="brgy-summary-title">Population by Gender</div>
                    <ul class="brgy-list">
                        <li><strong>Female:</strong> 2,573 (33 with disabilities)</li>
                        <li><strong>Male:</strong> 2,567 (45 with disabilities)</li>
                        <li><strong>Total with Disabilities:</strong> 78</li>
                    </ul>
                </div>
                <div class="brgy-summary-card">
                    <div class="brgy-summary-title">Age Distribution</div>
                    <ul class="brgy-list">
                        <li><strong>0-11 months:</strong> 441</li>
                        <li><strong>1-17 years:</strong> 1,062</li>
                        <li><strong>18-59 years:</strong> 3,292</li>
                        <li><strong>60+ years:</strong> 345</li>
                    </ul>
                </div>
                <div class="brgy-summary-card">
                    <div class="brgy-summary-title">Religion</div>
                    <ul class="brgy-list">
                        <li><strong>Romano Katoliko:</strong> 80%</li>
                        <li><strong>Iglesia ni Cristo:</strong> 10%</li>
                        <li><strong>Protestante:</strong> 4%</li>
                        <li><strong>Baptist:</strong> 2%</li>
                        <li><strong>Saksi ni Jehova:</strong> 2%</li>
                        <li><strong>Christian Born Again:</strong> 1%</li>
                        <li><strong>Iba pa:</strong> 1%</li>
                    </ul>
                </div>
                <div class="brgy-summary-card">
                    <div class="brgy-summary-title">History & Culture</div>
                    <ul class="brgy-list">
                        <li><strong>Original Name:</strong> Brgy. Canario</li>
                        <li><strong>Patron Saint:</strong> San Vicente Ferrer y Miguel</li>
                        <li><strong>Pista:</strong> Feb 1-3 (with Silang), Apr 5 (Patron Saint)</li>
                        <li><strong>Established:</strong> 1991 (divided from San Vicente 1)</li>
                    </ul>
                </div>
                <div class="brgy-summary-card">
                    <div class="brgy-summary-title">Infrastructure</div>
                    <ul class="brgy-list">
                        <li><strong>Electricity:</strong> 1,717 households</li>
                        <li><strong>Water (Level 3):</strong> 1,717 households</li>
                        <li><strong>Garbage Collection:</strong> 1,717 households</li>
                        <li><strong>Landline:</strong> 300 households</li>
                        <li><strong>Cable:</strong> 700 households</li>
                        <li><strong>Transportation:</strong> 90% coverage</li>
                    </ul>
                </div>
                <div class="brgy-summary-card">
                    <div class="brgy-summary-title">Services</div>
                    <ul class="brgy-list">
                        <li><strong>Health Center:</strong> 20 sq.m. (Tue, Wed, Thu)</li>
                        <li><strong>Nutrition Center:</strong> 20 sq.m. (Wed, Fri)</li>
                        <li><strong>Day Care:</strong> RIC-CC (30 students)</li>
                        <li><strong>Schools:</strong> 2 Private (Casa Real, PLO Global)</li>
                    </ul>
                </div>
            </div>

            <!-- Officials grid (soft blue) -->
            <div class="brgy-officials-bento">
                <div class="officials-card officials-primary">
                    <h3 class="officials-heading">Punong Barangay</h3>
                    <div class="officials-grid single">
                        <div id="pbContainer"></div>
                    </div>
                </div>
                <div class="officials-card">
                    <h3 class="officials-heading">Barangay Officials</h3>
                    <div class="kagawad-wrapper wireframe">
                        <div id="kagawadRowA" class="kagawad-row"></div>
                        <div id="kagawadRowB" class="kagawad-row"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- LIGHTBOX -->
<div id="lightbox" onclick="closeLightbox()">
    <img id="lightbox-img" src="" alt="Expanded Image">
</div>

<!-- NEWS & ANNOUNCEMENTS SECTION (hidden on home) -->
<section class="announcements-section" id="announcements" style="display:none;" aria-hidden="true">
    <div class="announcements-container">
        <div class="feed-header">
            <div>
                <h1 class="announcements-title">Community Updates</h1>
                <p class="announcements-subtitle">News, announcements, programs, events, and projects.</p>
            </div>
        </div>
        
        <?php if (!empty($home_feed)): ?>
            <div class="ig-grid">
                <?php foreach ($home_feed as $item): ?>
                    <?php 
                        $desc = $item['description'] ?? '';
                        $short_desc = strlen($desc) > 180 ? substr($desc, 0, 180) . '...' : $desc;
                        $meta_line = trim(($item['meta'] ?? '') . ' ' . ($item['extra'] ?? ''));
                    ?>
                    <article class="ig-card">
                        <div class="ig-card-top">
                            <div class="ig-user">
                                <span class="ig-username"><?php echo htmlspecialchars($item['label'] ?? 'Community'); ?></span>
                                <span class="ig-subtext"><?php echo htmlspecialchars($item['display_date'] ?? ''); ?></span>
                            </div>
                        </div>

                        <div class="ig-media">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php else: ?>
                                <div class="ig-media-placeholder">
                                    <span></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="ig-actions">
                            <div class="ig-actions-left" aria-hidden="true">
                                <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 21s-6.5-4.35-9-8.5C1 8.5 3.5 5 7 5c2.1 0 3.4 1.2 4 2 0.6-0.8 1.9-2 4-2 3.5 0 6 3.5 4 7.5-2.5 4.15-9 8.5-9 8.5z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 11.5c0-5-4-9-9-9s-9 4-9 9c0 4.11 2.92 7.53 6.79 8.39-.09-.71-.17-1.8.04-2.58l1.18-4.99s-.3-.61-.3-1.51c0-1.41.82-2.46 1.85-2.46.87 0 1.29.65 1.29 1.43 0 .87-.56 2.18-.85 3.4-.24 1 .51 1.82 1.51 1.82 1.82 0 3.22-1.92 3.22-4.69 0-2.45-1.76-4.16-4.27-4.16-2.91 0-4.63 2.18-4.63 4.42 0 .88.34 1.82.77 2.33.09.11.1.2.08.3l-.31 1.26c-.05.2-.16.25-.38.15-1.4-.65-2.27-2.71-2.27-4.36 0-3.55 2.57-6.81 7.41-6.81 3.89 0 6.91 2.77 6.91 6.48 0 3.86-2.43 6.97-5.8 6.97-1.13 0-2.2-.59-2.57-1.29l-.7 2.65c-.25.97-.93 2.18-1.39 2.92.99.3 2.04.47 3.13.47 5 0 9-4 9-9z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 2 11 13" stroke-linecap="round" stroke-linejoin="round"/><polyline points="22 2 15 22 11 13 2 9 22 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div class="ig-actions-right" aria-hidden="true">
                                <svg class="ig-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M19 21 12 16 5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                        </div>

                        <div class="ig-likes">Barangay updates • <?php echo htmlspecialchars($item['label'] ?? ''); ?></div>
                        <div class="ig-caption">
                            <span class="ig-username"><?php echo htmlspecialchars($item['title']); ?></span>
                            <?php echo htmlspecialchars($short_desc); ?>
                        </div>
                        <?php if (!empty($meta_line)): ?>
                            <div class="ig-meta-line"><?php echo htmlspecialchars($meta_line); ?></div>
                        <?php endif; ?>
                        <div class="ig-time"><?php echo htmlspecialchars($item['display_date'] ?? ''); ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-announcements">
                <p>No updates at this time. Check back soon!</p>
                <a class="feed-btn" href="admin/admin_announcement_management.php">Add content in Admin</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CONTACT SECTION -->
<section class="contact-section" id="contact">
    <div class="contact-container">
        <div class="contact-header">
            <h1 class="contact-title">Contact Information</h1>
            <p class="contact-subtitle">Get in touch with Barangay San Vicente II</p>
        </div>
        <div class="contact-content">
            <div class="contact-info">
                <div class="contact-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <span>info@barangaysanvicenteii.gov.ph</span>
                </div>
                <div class="contact-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    <span>0900-000-0000</span>
                </div>
                <div class="contact-item">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span>Barangay San Vicente II, Silang, Cavite</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT SECTION -->
<section class="cta-section-asym" id="about-section">
    <div class="about-container">
        <h1 class="about-title">Forms / Feedback Section</h1>
        
        <div class="about-content-layout">
            <!-- LEFT COLUMN - Main Content -->
            <div class="about-main-content">
                <div class="about-testimonial">
                    <div class="about-profile">
                        <div class="about-avatar">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <div class="about-user-info">
                            <h3 class="about-user-name">Barangay San Vicente II</h3>
                            <p class="about-user-location">Silang, Cavite, Philippines</p>
                        </div>
                    </div>
                    
                    <div class="about-rating">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    
                    <h2 class="about-review-title">Your Digital Gateway to Barangay Services</h2>
                    
                    <p class="about-review-text">
                        Welcome to the Barangay San Vicente II Portal - a comprehensive web-based system designed to modernize and streamline barangay operations. Our portal provides residents with convenient 24/7 access to essential barangay services, eliminating the need for long queues and multiple visits to the barangay hall.
                    </p>
                    
                    <p class="about-review-text">
                        <strong>What You Can Do:</strong> Request barangay clearances, certificates of residency, business permits, and other official documents online. Track your requests in real-time, receive instant notifications, and download approved documents directly from your dashboard. Access important announcements, view barangay officials, explore hazard maps, and stay connected with your community.
                    </p>
                    
                    <p class="about-review-text">
                        <strong>Key Benefits:</strong> Save time with online document requests, reduce physical visits to the barangay hall, get instant updates on your requests, access your documents anytime, anywhere, and stay informed with real-time announcements and notifications. Our secure system ensures your personal information is protected while providing transparent and efficient service delivery.
                    </p>
                </div>
            </div>
            
            <!-- RIGHT COLUMN - Media/Features -->
            <div class="about-media-column">
                <!-- Feature Gallery -->
                <div class="about-media-card about-gallery">
                    <div class="about-media-placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <div class="about-dots">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                    <p class="about-media-caption">Portal Features & Services</p>
                </div>
                
                <!-- Video/Info Card -->
                <div class="about-media-card about-video">
                    <div class="about-media-placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="12" r="10"/>
                            <polygon points="10 8 16 12 10 16" fill="white"/>
                        </svg>
                    </div>
                    <p class="about-media-caption">How It Works</p>
                </div>
                
                <!-- CTA Button -->
                <button class="about-cta-btn" id="getStartedAboutBtn">Get Started Today</button>
            </div>
        </div>
        
        <!-- Features Grid -->
        <div class="about-features-grid">
            <div class="about-feature-item">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                <h4>Document Requests</h4>
                <p>Request clearances, certificates, and permits online</p>
            </div>
            
            <div class="about-feature-item">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <h4>Real-time Notifications</h4>
                <p>Get instant updates on your requests and announcements</p>
            </div>
            
            <div class="about-feature-item">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                <h4>Track Requests</h4>
                <p>Monitor the status of your requests in real-time</p>
            </div>
            
            <div class="about-feature-item">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                <h4>Download Documents</h4>
                <p>Access and download approved documents anytime</p>
            </div>
            
            <div class="about-feature-item">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <h4>Announcements</h4>
                <p>Stay updated with barangay news and events</p>
            </div>
            
            <div class="about-feature-item">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <h4>Barangay Officials</h4>
                <p>View information about your barangay leaders</p>
            </div>
        </div>
    </div>
</section>












<!---------------------------MODALS HERE------------------------------>

<!-- GET STARTED MODAL -->
<div id="getStartedModal" class="modal">
  <div class="modal-content choice-modal">
    <span class="close" id="closeGetStarted">&times;</span>
    <h2>Get Started</h2>
    <p class="modal-subtitle">Choose an option to continue</p>
    
    <div class="choice-grid">
      <!-- Row 1: Login Options -->
      <div class="choice-card" id="residentLoginChoice">
        <div class="choice-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </div>
        <h3>Login as Resident</h3>
        <p>Access your resident account</p>
      </div>
      
      <div class="choice-card" id="adminLoginChoice">
        <div class="choice-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
        </div>
        <h3>Login as Admin</h3>
        <p>Access admin dashboard</p>
      </div>
      
      <div class="choice-card" id="workerLoginChoice">
        <div class="choice-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </div>
        <h3>Login as Worker</h3>
        <p>Access worker dashboard</p>
      </div>
      
      <!-- Row 2: Register Options -->
      <div class="choice-card" id="residentRegisterChoice">
        <div class="choice-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="12" y1="18" x2="12" y2="12"></line>
            <line x1="9" y1="15" x2="15" y2="15"></line>
          </svg>
        </div>
        <h3>Register as Resident</h3>
        <p>Create a new resident account</p>
      </div>
      
      <?php if ($admin_registration_allowed): ?>
      <div class="choice-card" id="adminRegisterChoice">
        <div class="choice-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="16"></line>
            <line x1="8" y1="12" x2="16" y2="12"></line>
          </svg>
        </div>
        <h3>Register as Admin</h3>
        <p>Create a new admin account</p>
      </div>
      <?php else: ?>
      <div class="choice-card choice-card-disabled" title="Maximum number of admins reached (<?php echo $admin_limit; ?> admins limit)">
        <div class="choice-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="16"></line>
            <line x1="8" y1="12" x2="16" y2="12"></line>
          </svg>
        </div>
        <h3>Register as Admin</h3>
        <p>Maximum admins reached (<?php echo $admin_limit; ?>)</p>
      </div>
      <?php endif; ?>
      
      <?php if ($worker_registration_allowed): ?>
      <div class="choice-card" id="workerRegisterChoice">
        <div class="choice-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
          </svg>
        </div>
        <h3>Register as Worker</h3>
        <p>Create a new worker account</p>
      </div>
      <?php else: ?>
      <div class="choice-card choice-card-disabled" title="Maximum number of workers reached (<?php echo $worker_limit; ?> workers limit)">
        <div class="choice-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
          </svg>
        </div>
        <h3>Register as Worker</h3>
        <p>Maximum workers reached (<?php echo $worker_limit; ?>)</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- RESIDENT LOGIN MODAL -->
<div id="loginModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeLogin">&times;</span>
    <h2>Resident Login</h2>

    <form id="residentLoginForm" action="login.php" method="post" onsubmit="this.submit(); return true;">
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <a href="#" id="forgotPassword" class="forgot-link">Forgot Password?</a>
      <button type="submit" name="login_submit" id="loginSubmitBtn">Login</button>
    </form>

    <p class="no-account">
      Don't have an account? <a href="#" id="openRegister">Register</a>
    </p>
    <p class="no-account" style="margin-top: 10px;">
      <a href="#" id="openAdminLogin">Login as Admin</a>
    </p>
  </div>
</div>

<!-- ADMIN LOGIN MODAL -->
<div id="adminLoginModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeAdminLogin">&times;</span>
    <h2>Admin Login</h2>

    <form action="admin_login_handler.php" method="post">
      <input type="text" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <a href="admin/admin_forgot_password.php" class="forgot-link">Forgot Password?</a>
      <button type="submit">Login</button>
    </form>

    <p class="no-account">
      <a href="admin/admin_register.php">Create Admin Account</a>
    </p>
    <p class="no-account" style="margin-top: 10px;">
      <a href="#" id="openResidentLogin">Login as Resident</a>
    </p>
  </div>
</div>

<!-- REGISTER MODAL -->
<div id="registerModal" class="modal">
  <div class="modal-content large-modal">
    <span class="close" id="closeRegister">&times;</span>
    <h2 style="align-items: center;">Resident Registration</h2>
<br>
    
<!---Progress Identificator--->
 <div class="step-progress">
  <div class="step-item active" data-tab="basic">
    <div class="circle">1</div>
    <p>Personal</p>
  </div>

  <div class="step-line"></div>

  <div class="step-item" data-tab="address">
    <div class="circle">2</div>
    <p>Residency</p>
  </div>

  <div class="step-line"></div>

  <div class="step-item" data-tab="other">
    <div class="circle">3</div>
    <p>Others</p>
  </div>

  <div class="step-line"></div>

  <div class="step-item" data-tab="account">
    <div class="circle">4</div>
    <p>Account</p>
  </div>
</div>

 <!-- ===== FORM ===== -->
    <form id="registerForm" action="register.php" method="post" enctype="multipart/form-data">

      <!-- BASIC -->
      <div class="tab-content active" id="basic">
        <h3>Personal Information</h3>

        <label>Photo</label>
        <input type="file" name="photo" accept="image/*" required/>

        <label>First Name</label>
        <input type="text" name="first_name" placeholder="First Name" pattern="[A-Za-z\s'-]+" title="Only letters, spaces, hyphens, and apostrophes are allowed" onkeypress="return allowLettersOnly(event)" required />

        <label>Middle Name</label>
        <input type="text" name="middle_name" placeholder="Middle Name" pattern="[A-Za-z\s'-]+" title="Only letters, spaces, hyphens, and apostrophes are allowed" onkeypress="return allowLettersOnly(event)" />

        <label>Last Name</label>
        <input type="text" name="last_name" placeholder="Last Name" pattern="[A-Za-z\s'-]+" title="Only letters, spaces, hyphens, and apostrophes are allowed" onkeypress="return allowLettersOnly(event)" required />

        <label>Suffix</label>
        <input type="text" name="suffix" placeholder="Suffix (if any)" pattern="[A-Za-z\s'-]+" title="Only letters, spaces, hyphens, and apostrophes are allowed" onkeypress="return allowLettersOnly(event)" />

        <label>Gender</label>
        <select name="gender" required>
          <option value="">Select</option>
          <option>Female</option>
          <option>Male</option>
          <option>Other</option>
        </select>

        <label>Civil Status</label>
        <select name="civil_status" required>
          <option value="">Select</option>
          <option>Single</option>
          <option>Married</option>
          <option>Widowed</option>
          <option>Separated</option>
        </select>

        <label>Date of Birth</label>
        <input type="date" name="date_of_birth" required />

        <label>Place of Birth</label>
        <input type="text" name="place_of_birth" placeholder="Place of Birth" pattern="[A-Za-z\s'-]+" title="Only letters, spaces, hyphens, and apostrophes are allowed" onkeypress="return allowLettersOnly(event)" required />

        <label>Nationality</label>
        <input type="text" name="nationality" placeholder="Nationality" pattern="[A-Za-z\s'-]+" title="Only letters, spaces, hyphens, and apostrophes are allowed" onkeypress="return allowLettersOnly(event)" required />

        <label>Religion</label>
        <input type="text" name="religion" placeholder="Religion" pattern="[A-Za-z\s'-]+" title="Only letters, spaces, hyphens, and apostrophes are allowed" onkeypress="return allowLettersOnly(event)" />

        <button type="button" class="next-btn" data-next="address">Next ➜</button>
      </div>

      <!-- ADDRESS -->
      <div class="tab-content" id="address">
        <h3>Residency Information</h3>

        <label>Home Number</label>
        <input type="text" name="home_number" placeholder="Home Number" required />

        <label>Street</label>
        <input type="text" name="street" placeholder="Street" required />

        <label>Barangay</label>
        <input type="text" name="barangay" value="San Vicente II" readonly />

        <label>Municipality</label>
        <input type="text" name="municipality" placeholder="Municipality" pattern="[A-Za-z\s'-]+" title="Only letters, spaces, hyphens, and apostrophes are allowed" onkeypress="return allowLettersOnly(event)" required />

        <label>City / Province</label>
        <input type="text" name="city_province" placeholder="City / Province" pattern="[A-Za-z\s'-]+" title="Only letters, spaces, hyphens, and apostrophes are allowed" onkeypress="return allowLettersOnly(event)" required />

        <label>Household Head</label>
        <select name="household_head" required>
          <option value="">Select</option>
          <option>Yes</option>
          <option>No</option>
        </select>
        <small style="color: #666; font-size: 12px; display: block; margin-top: -10px; margin-bottom: 15px;">Are you the head of your household?</small>

        <div class="nav-btns">
          <button type="button" class="back-btn" data-prev="basic">⬅ Back</button>
          <button type="button" class="next-btn" data-next="other">Next ➜</button>
        </div>
      </div>

      <!-- OTHER INFO -->
      <div class="tab-content" id="other">
        <h3>Other Information</h3>

        <label>Voter</label>
        <select name="voter" required>
          <option value="">Select</option>
          <option>Yes</option>
          <option>No</option>
        </select>

        <label>Voter in this Barangay?</label>
        <select name="voter_in_barangay" required>
          <option value="">Select</option>
          <option>Yes</option>
          <option>No</option>
        </select>

        <label>Precint No.</label>
        <input type="text" name="precinct_number" placeholder="Precinct Number" pattern="[0-9]+" title="Only numbers are allowed" onkeypress="return allowNumbersOnly(event)" />

        <label>Senior Citizen</label>
        <select name="senior_citizen" required>
          <option value="">Select</option>
          <option>Yes</option>
          <option>No</option>
        </select>

        <label>Youth Member (15-30 y/o)</label>
        <select name="youth_member" required>
          <option value="">Select</option>
          <option>Yes</option>
          <option>No</option>
        </select>

        <label>Solo Parent</label>
        <select name="solo_parent" required>
          <option value="">Select</option>
          <option>Yes</option>
          <option>No</option>
        </select>

        <label>Father's Name</label>
        <input type="text" name="father_name" placeholder="Father's Name" pattern="[A-Za-z\s'-]+" title="Only letters, spaces, hyphens, and apostrophes are allowed" onkeypress="return allowLettersOnly(event)" />

        <label>Mother's Name</label>
        <input type="text" name="mother_name" placeholder="Mother's Name" pattern="[A-Za-z\s'-]+" title="Only letters, spaces, hyphens, and apostrophes are allowed" onkeypress="return allowLettersOnly(event)" />

        <label>Guardian</label>
        <input type="text" name="guardian_name" placeholder="Guardian" pattern="[A-Za-z\s'-]+" title="Only letters, spaces, hyphens, and apostrophes are allowed" onkeypress="return allowLettersOnly(event)" required />

        <label>Guardian Contact Number</label>
        <input type="text" name="guardian_contact" placeholder="Guardian Contact Number" pattern="[0-9]+" title="Only numbers are allowed" onkeypress="return allowNumbersOnly(event)" required />

        <div class="nav-btns">
          <button class="back-btn" type="button" data-prev="address">⬅ Back</button>
          <button class="next-btn" type="button" data-next="account">Next ➜</button>
        </div>
      </div>

      <!-- ACCOUNT -->
      <div class="tab-content" id="account">
        <h3>Account Information</h3>

        <label>Name</label>
        <input type="text" name="display_name" placeholder="Name" pattern="[A-Za-z\s'-]+" title="Only letters, spaces, hyphens, and apostrophes are allowed" onkeypress="return allowLettersOnly(event)" required />

        <label>Email</label>
        <input type="email" name="email" placeholder="Email" required />

        <label>Contact Number</label>
        <input type="text" name="contact_number" placeholder="Contact Number" pattern="[0-9]+" title="Only numbers are allowed" onkeypress="return allowNumbersOnly(event)" required />

        <label>Password</label>
        <div class="input-with-toggle">
          <input type="password" id="registerPassword" name="password" placeholder="Password" required />
          <button type="button" class="toggle-password" data-target="registerPassword">Show</button>
        </div>

        <label>Confirm Password</label>
        <div class="input-with-toggle">
          <input type="password" id="registerConfirmPassword" name="confirm_password" placeholder="Confirm Password" required />
          <button type="button" class="toggle-password" data-target="registerConfirmPassword">Show</button>
        </div>

        <div class="nav-btns">
          <button class="back-btn" type="button" data-prev="other">⬅ Back</button>
          <button class="submit-btn" type="submit">Register</button>
        </div>
      </div>

    </form>
  </div>
</div>



















<!-- REGISTRATION SUCCESS MODAL -->
<div id="registrationSuccessModal" class="modal success-modal">
  <div class="modal-content success-modal-content">
    <span class="close" id="closeRegistrationSuccess">&times;</span>
    <div class="success-icon">✓</div>
    <h2>Registration Complete</h2>
    <p id="registrationSuccessText" class="success-message">Registration successful. You can now log in.</p>
    <div class="success-actions">
      <button type="button" id="registrationSuccessLogin">Log in</button>
      <button type="button" class="ghost-btn" id="registrationSuccessDismiss">Close</button>
    </div>
  </div>
</div>

<?php

if (isset($_SESSION['reg_errors'])) {

  $errors = $_SESSION['reg_errors'];
  
  echo'<div id="serverMessage" data-message="' . htmlspecialchars(implode('|', $errors)) . '"></div>'; 
  
  unset($_SESSION['reg_errors']);

}

if (isset($_SESSION['reg_success'])) {
  
  echo'<div id="serverMessage" data-message="' . htmlspecialchars($_SESSION['reg_success']) . '"></div>';
  
  unset($_SESSION['reg_success']);

}

if (isset($_SESSION['login_error'])) {
  
  echo'<div id="serverMessage" data-message="' . htmlspecialchars($_SESSION['login_error']) . '"></div>';
  
  unset($_SESSION['login_error']);

}

if (isset($_SESSION['admin_login_error'])) {
  
  echo'<div id="serverMessage" data-message="' . htmlspecialchars($_SESSION['admin_login_error']) . '"></div>';
  
  unset($_SESSION['admin_login_error']);

}

?>








<script>
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
<script src="index.js"></script>
<script src="pwa-register.js"></script>

</body>
</html>

