<?php
/**
 * Push Subscription Registration Endpoint
 * Handles push subscription registration for residents, admins, and workers
 */

session_start();
require_once 'config.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['role'])) {
    // Check for admin or worker session
    if (!isset($_SESSION['admin_id']) && !isset($_SESSION['worker_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }
}

// Get user info
$user_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? $_SESSION['worker_id'] ?? null;
$user_role = $_SESSION['role'] ?? 'unknown';
$user_type = 'resident';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        $user_type = 'admin';
        $user_id = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;
    } elseif ($_SESSION['role'] === 'worker') {
        $user_type = 'worker';
        $user_id = $_SESSION['worker_id'] ?? $_SESSION['user_id'] ?? null;
    }
}

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID not found']);
    exit;
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Register subscription
    if (!isset($input['subscription']) || !isset($input['subscription']['endpoint'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid subscription data']);
        exit;
    }

    $subscription = $input['subscription'];
    
    // Store subscription
    $subscriptionsDir = __DIR__ . '/data/push_subscriptions';
    if (!is_dir($subscriptionsDir)) {
        mkdir($subscriptionsDir, 0777, true);
    }

    $subscriptionFile = $subscriptionsDir . '/' . $user_type . '_subscriptions.json';
    
    // Load existing subscriptions
    $subscriptions = [];
    if (file_exists($subscriptionFile)) {
        $subscriptions = json_decode(file_get_contents($subscriptionFile), true) ?? [];
    }

    // Remove old subscription for this user if exists
    $subscriptions = array_filter($subscriptions, function($sub) use ($user_id) {
        return $sub['user_id'] != $user_id;
    });
    $subscriptions = array_values($subscriptions);

    // Add new subscription
    $subscriptions[] = [
        'user_id' => $user_id,
        'user_type' => $user_type,
        'endpoint' => $subscription['endpoint'],
        'keys' => [
            'p256dh' => $subscription['keys']['p256dh'] ?? '',
            'auth' => $subscription['keys']['auth'] ?? ''
        ],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Save subscriptions
    file_put_contents($subscriptionFile, json_encode($subscriptions, JSON_PRETTY_PRINT));

    echo json_encode([
        'success' => true,
        'message' => 'Push subscription registered successfully'
    ]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Unregister subscription
    $subscriptionsDir = __DIR__ . '/data/push_subscriptions';
    $subscriptionFile = $subscriptionsDir . '/' . $user_type . '_subscriptions.json';
    
    if (file_exists($subscriptionFile)) {
        $subscriptions = json_decode(file_get_contents($subscriptionFile), true) ?? [];
        
        // Remove subscription for this user
        $subscriptions = array_filter($subscriptions, function($sub) use ($user_id) {
            return $sub['user_id'] != $user_id;
        });
        $subscriptions = array_values($subscriptions);
        
        file_put_contents($subscriptionFile, json_encode($subscriptions, JSON_PRETTY_PRINT));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Push subscription unregistered successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

