<?php
// Get the root URL path dynamically
$root = str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(__DIR__));
?>

<!-- PWA META TAGS -->
<meta name="theme-color" content="#2C5F8D">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="BSV II Portal">
<meta name="mobile-web-app-capable" content="yes">
<meta name="msapplication-TileColor" content="#2C5F8D">
<meta name="msapplication-TileImage" content="<?php echo $root; ?>/favicon_io/android-chrome-192x192.png">

<!-- FAVICON SET -->
<link rel="icon" href="<?php echo $root; ?>/favicon_io/favicon.ico" type="image/x-icon">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $root; ?>/favicon_io/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $root; ?>/favicon_io/favicon-16x16.png">
<link rel="apple-touch-icon" href="<?php echo $root; ?>/favicon_io/apple-touch-icon.png">
<link rel="manifest" href="<?php echo $root; ?>/favicon_io/site.webmanifest">
