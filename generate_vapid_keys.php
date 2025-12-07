<?php
/**
 * VAPID Keys Generator
 * Run this file once to generate VAPID keys for push notifications
 * 
 * Usage: php generate_vapid_keys.php
 * Or visit this file in browser
 */

// Check if web-push library is available
$useLibrary = false;
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('\Minishlink\WebPush\VAPID')) {
        $useLibrary = true;
    }
}

if ($useLibrary) {
    // Use web-push-php library (recommended)
    try {
        $keys = \Minishlink\WebPush\VAPID::createVapidKeys();
        
        echo "=== VAPID Keys Generated Successfully ===\n\n";
        echo "Public Key:\n";
        echo $keys['publicKey'] . "\n\n";
        echo "Private Key:\n";
        echo $keys['privateKey'] . "\n\n";
        echo "=== Instructions ===\n";
        echo "1. Copy the Public Key to:\n";
        echo "   - push_notification_helper.php (VAPID_PUBLIC_KEY constant)\n";
        echo "   - sw.js (VAPID_PUBLIC_KEY variable)\n";
        echo "   - All notification pages (JavaScript)\n\n";
        echo "2. Copy the Private Key to:\n";
        echo "   - push_notification_helper.php (VAPID_PRIVATE_KEY constant)\n";
        echo "   - Keep this SECRET! Never expose it in client-side code.\n\n";
        echo "3. Update VAPID_SUBJECT in push_notification_helper.php with your contact email.\n";
        
    } catch (Exception $e) {
        echo "Error generating keys: " . $e->getMessage() . "\n";
        echo "Trying alternative method...\n\n";
        $useLibrary = false;
    }
}

if (!$useLibrary) {
    // Alternative: Use OpenSSL (if available)
    if (!function_exists('openssl_pkey_new')) {
        die("Error: OpenSSL extension is not available.\nPlease install web-push-php library:\ncomposer require minishlink/web-push\n");
    }

    echo "=== Generating VAPID Keys using OpenSSL ===\n\n";
    
    $config = [
        'curve_name' => 'prime256v1',
        'private_key_type' => OPENSSL_KEYTYPE_EC,
    ];

    $keyPair = openssl_pkey_new($config);
    
    if (!$keyPair) {
        die("Error: Failed to generate key pair.\n" . openssl_error_string() . "\n");
    }

    $privateKey = '';
    if (!openssl_pkey_export($keyPair, $privateKey)) {
        die("Error: Failed to export private key.\n");
    }

    $publicKeyDetails = openssl_pkey_get_details($keyPair);
    if (!$publicKeyDetails) {
        die("Error: Failed to get public key details.\n");
    }

    // Extract and encode keys
    $publicKeyPem = $publicKeyDetails['key'];
    $privateKeyPem = $privateKey;

    // Convert to base64url format (for VAPID)
    // Extract the key data from PEM format
    preg_match('/-----BEGIN PUBLIC KEY-----\s*(.+?)\s*-----END PUBLIC KEY-----/s', $publicKeyPem, $pubMatches);
    preg_match('/-----BEGIN EC PRIVATE KEY-----\s*(.+?)\s*-----END EC PRIVATE KEY-----/s', $privateKeyPem, $privMatches);
    
    if (empty($pubMatches[1]) || empty($privMatches[1])) {
        die("Error: Failed to parse keys.\n");
    }

    $publicKeyRaw = base64_decode(str_replace(["\n", "\r", " "], '', $pubMatches[1]));
    $privateKeyRaw = base64_decode(str_replace(["\n", "\r", " "], '', $privMatches[1]));

    // Extract the actual key bytes (skip ASN.1 structure)
    // For EC keys, we need to extract the raw key bytes
    $publicKeyBase64 = base64_encode($publicKeyRaw);
    $privateKeyBase64 = base64_encode($privateKeyRaw);

    echo "=== VAPID Keys Generated Successfully ===\n\n";
    echo "Public Key:\n";
    echo $publicKeyBase64 . "\n\n";
    echo "Private Key:\n";
    echo $privateKeyBase64 . "\n\n";
    echo "=== Instructions ===\n";
    echo "1. Copy the Public Key to:\n";
    echo "   - push_notification_helper.php (VAPID_PUBLIC_KEY constant)\n";
    echo "   - sw.js (VAPID_PUBLIC_KEY variable)\n";
    echo "   - All notification pages (JavaScript)\n\n";
    echo "2. Copy the Private Key to:\n";
    echo "   - push_notification_helper.php (VAPID_PRIVATE_KEY constant)\n";
    echo "   - Keep this SECRET! Never expose it in client-side code.\n\n";
    echo "3. Update VAPID_SUBJECT in push_notification_helper.php with your contact email.\n\n";
    echo "NOTE: For production, it's recommended to install web-push-php library:\n";
    echo "composer require minishlink/web-push\n";
    echo "This will ensure proper key format and encryption.\n";
}

// Save keys to a file (optional - for convenience)
if (isset($_GET['save']) || (php_sapi_name() === 'cli' && isset($argv[1]) && $argv[1] === '--save')) {
    $keysFile = __DIR__ . '/data/vapid_keys.json';
    $keysDir = dirname($keysFile);
    if (!is_dir($keysDir)) {
        mkdir($keysDir, 0777, true);
    }
    
    $keysData = [
        'publicKey' => $keys['publicKey'] ?? $publicKeyBase64 ?? '',
        'privateKey' => $keys['privateKey'] ?? $privateKeyBase64 ?? '',
        'generated_at' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents($keysFile, json_encode($keysData, JSON_PRETTY_PRINT));
    echo "\nKeys saved to: $keysFile\n";
    echo "WARNING: Keep this file secure! Do not commit to version control.\n";
}
?>

