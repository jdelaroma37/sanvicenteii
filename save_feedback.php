<?php
// Always set timezone first
date_default_timezone_set('Asia/Manila');

// Ensure the feedback directory exists
$feedbackDir = __DIR__ . '/data/feedbacks';
if (!is_dir($feedbackDir)) {
    mkdir($feedbackDir, 0777, true);
}

$feedbackFile = $feedbackDir . '/feedback.json';

// Get form inputs safely
$fullname = htmlspecialchars(trim($_POST['fullname'] ?? ''), ENT_QUOTES, 'UTF-8');
$subject  = htmlspecialchars(trim($_POST['subject'] ?? ''), ENT_QUOTES, 'UTF-8');
$message  = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');

if ($fullname && $subject && $message) {

    // Load existing feedbacks
    $feedbacks = file_exists($feedbackFile)
        ? json_decode(file_get_contents($feedbackFile), true)
        : [];

    // Create new feedback entry
    $newFeedback = [
        'fullname' => $fullname,
        'subject'  => $subject,
        'message'  => $message,
        'date'     => date('F d, Y h:i A'), // Manila time
        'read'     => false
    ];

    // Add new feedback
    $feedbacks[] = $newFeedback;

    // Save back to file
    file_put_contents($feedbackFile, json_encode($feedbacks, JSON_PRETTY_PRINT));

    // Redirect with success message
    header("Location: resident/resident_dashboard.php?success=1");
    exit;

} else {

    header("Location: resident/resident_dashboard.php?error=1");
    exit;
}
?>
