<?php
session_start();
require 'config.php';

// Load announcements
$announcements_file = __DIR__ . '/data/announcements/announcement.json';
$announcements = [];

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

// Helpers for the feed grid
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

// Merge all content types into one grid-friendly array
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

usort($community_feed, function($a, $b) {
    return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>News & Updates - Barangay San Vicente II</title>
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
</head>
<body>
  
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

<!-- NEWS & UPDATES PAGE -->
<section class="announcements-page" style="margin-top: 60px; padding: 80px 60px; min-height: calc(100vh - 60px);">
    <div class="announcements-container">
        <div class="feed-header" style="margin-top: 10px;">
            <div>
                <h1 class="announcements-title" style="font-size: 42px;">Community Updates</h1>
                <p class="announcements-subtitle">All news, announcements, programs, events, and projects.</p>
            </div>
        </div>

        <?php if (!empty($community_feed)): ?>
            <div class="ig-grid">
                <?php foreach ($community_feed as $item): ?>
                    <?php 
                        $desc = $item['description'] ?? '';
                        $short_desc = strlen($desc) > 200 ? substr($desc, 0, 200) . '...' : $desc;
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
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: #5a9fd4; margin-bottom: 20px;">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <p>No updates yet. Add content from the Admin panel to populate this feed.</p>
                <a class="feed-btn" href="admin/admin_announcement_management.php">Go to Admin</a>
            </div>
        <?php endif; ?>
    </div>
</section>

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

