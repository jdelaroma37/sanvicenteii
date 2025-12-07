<?php
/**
 * Push Notification Helper
 * Sends push notifications to users
 */

require_once __DIR__ . '/config.php';

// VAPID Keys - Generate these using: https://web-push-codelab.glitch.me/
// Or use: composer require minishlink/web-push
define('VAPID_PUBLIC_KEY', 'YOUR_VAPID_PUBLIC_KEY_HERE');
define('VAPID_PRIVATE_KEY', 'YOUR_VAPID_PRIVATE_KEY_HERE');
define('VAPID_SUBJECT', 'mailto:your-email@example.com'); // Your contact email

/**
 * Send push notification to a user
 * 
 * @param string $user_type - 'resident', 'admin', or 'worker'
 * @param int $user_id - User ID
 * @param string $title - Notification title
 * @param string $message - Notification message
 * @param array $options - Additional options (url, icon, tag, etc.)
 * @return bool - Returns true if sent successfully
 */
function sendPushNotification($user_type, $user_id, $title, $message, $options = []) {
    $subscriptionsDir = __DIR__ . '/data/push_subscriptions';
    $subscriptionFile = $subscriptionsDir . '/' . $user_type . '_subscriptions.json';
    
    if (!file_exists($subscriptionFile)) {
        return false;
    }

    $subscriptions = json_decode(file_get_contents($subscriptionFile), true) ?? [];
    
    // Find subscription for this user
    $userSubscription = null;
    foreach ($subscriptions as $sub) {
        if ($sub['user_id'] == $user_id && $sub['user_type'] == $user_type) {
            $userSubscription = $sub;
            break;
        }
    }

    if (!$userSubscription) {
        return false;
    }

    // Prepare notification payload
    $payload = json_encode([
        'title' => $title,
        'message' => $message,
        'icon' => $options['icon'] ?? '/images/logo.jpg',
        'badge' => $options['badge'] ?? '/images/logo.jpg',
        'tag' => $options['tag'] ?? 'notification',
        'url' => $options['url'] ?? '/',
        'id' => $options['id'] ?? null,
        'type' => $options['type'] ?? 'general'
    ]);

    // Send push notification using Web Push Protocol
    return sendWebPush(
        $userSubscription['endpoint'],
        $payload,
        $userSubscription['keys']['p256dh'],
        $userSubscription['keys']['auth']
    );
}

/**
 * Send push notification to all users of a type
 * 
 * @param string $user_type - 'resident', 'admin', or 'worker'
 * @param string $title - Notification title
 * @param string $message - Notification message
 * @param array $options - Additional options
 * @return int - Number of notifications sent
 */
function sendPushNotificationToAll($user_type, $title, $message, $options = []) {
    $subscriptionsDir = __DIR__ . '/data/push_subscriptions';
    $subscriptionFile = $subscriptionsDir . '/' . $user_type . '_subscriptions.json';
    
    if (!file_exists($subscriptionFile)) {
        return 0;
    }

    $subscriptions = json_decode(file_get_contents($subscriptionFile), true) ?? [];
    $sentCount = 0;

    $payload = json_encode([
        'title' => $title,
        'message' => $message,
        'icon' => $options['icon'] ?? '/images/logo.jpg',
        'badge' => $options['badge'] ?? '/images/logo.jpg',
        'tag' => $options['tag'] ?? 'notification',
        'url' => $options['url'] ?? '/',
        'id' => $options['id'] ?? null,
        'type' => $options['type'] ?? 'general'
    ]);

    foreach ($subscriptions as $sub) {
        if ($sub['user_type'] == $user_type) {
            if (sendWebPush(
                $sub['endpoint'],
                $payload,
                $sub['keys']['p256dh'],
                $sub['keys']['auth']
            )) {
                $sentCount++;
            }
        }
    }

    return $sentCount;
}

/**
 * Send Web Push using Web Push Protocol
 * This is a simplified version. For production, use a library like web-push-php
 * 
 * @param string $endpoint - Push service endpoint
 * @param string $payload - JSON payload
 * @param string $p256dh - User public key
 * @param string $auth - User auth secret
 * @return bool
 */
function sendWebPush($endpoint, $payload, $p256dh, $auth) {
    // Check if web-push library is available
    if (class_exists('\Minishlink\WebPush\WebPush')) {
        return sendWebPushWithLibrary($endpoint, $payload, $p256dh, $auth);
    }
    
    // Fallback: Use cURL (simplified - for production, use proper encryption)
    // NOTE: This is a basic implementation. For production, use the web-push-php library
    // which handles encryption properly.
    
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
            'TTL: 86400'
        ],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Success codes: 201 (Created), 204 (No Content)
    return in_array($httpCode, [201, 204]);
}

/**
 * Send Web Push using web-push-php library (recommended)
 */
function sendWebPushWithLibrary($endpoint, $payload, $p256dh, $auth) {
    try {
        $auth = [
            'VAPID' => [
                'subject' => VAPID_SUBJECT,
                'publicKey' => VAPID_PUBLIC_KEY,
                'privateKey' => VAPID_PRIVATE_KEY,
            ],
        ];

        $webPush = new \Minishlink\WebPush\WebPush($auth);
        
        $subscription = \Minishlink\WebPush\Subscription::create([
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => $p256dh,
                'auth' => $auth,
            ],
        ]);

        $result = $webPush->sendOneNotification($subscription, $payload);
        
        return $result->isSuccess();
    } catch (Exception $e) {
        error_log('Push notification error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Generate VAPID keys (run once to generate keys)
 * Save the output to config or environment variables
 */
function generateVAPIDKeys() {
    if (!function_exists('openssl_pkey_new')) {
        return false;
    }

    $keyPair = openssl_pkey_new([
        'curve_name' => 'prime256v1',
        'private_key_type' => OPENSSL_KEYTYPE_EC,
    ]);

    if (!$keyPair) {
        return false;
    }

    $privateKey = '';
    openssl_pkey_export($keyPair, $privateKey);

    $publicKeyDetails = openssl_pkey_get_details($keyPair);
    $publicKey = $publicKeyDetails['key'];

    // Extract base64 encoded keys
    preg_match('/-----BEGIN PUBLIC KEY-----\s*(.+?)\s*-----END PUBLIC KEY-----/s', $publicKey, $matches);
    $publicKeyBase64 = base64_encode(base64_decode(str_replace(["\n", "\r"], '', $matches[1] ?? '')));

    preg_match('/-----BEGIN EC PRIVATE KEY-----\s*(.+?)\s*-----END EC PRIVATE KEY-----/s', $privateKey, $matches);
    $privateKeyBase64 = base64_encode(base64_decode(str_replace(["\n", "\r"], '', $matches[1] ?? '')));

    return [
        'publicKey' => $publicKeyBase64,
        'privateKey' => $privateKeyBase64
    ];
}
?>

