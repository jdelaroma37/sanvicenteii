# Push Notifications Setup Guide

## Overview
This system now supports true push notifications for residents, admins, and workers. Push notifications work even when the browser tab is closed.

## Setup Instructions

### Step 1: Generate VAPID Keys

1. **Option A: Using web-push-php library (Recommended)**
   ```bash
   composer require minishlink/web-push
   ```
   Then visit: `http://your-domain/generate_vapid_keys.php`

2. **Option B: Using OpenSSL**
   Visit: `http://your-domain/generate_vapid_keys.php`

3. **Option C: Online Generator**
   Visit: https://web-push-codelab.glitch.me/

### Step 2: Configure VAPID Keys

After generating keys, update the following files:

1. **sw.js** (Service Worker)
   - Replace `YOUR_VAPID_PUBLIC_KEY_HERE` with your VAPID public key

2. **push_notification_helper.php**
   - Replace `YOUR_VAPID_PUBLIC_KEY_HERE` with your VAPID public key
   - Replace `YOUR_VAPID_PRIVATE_KEY_HERE` with your VAPID private key
   - Update `VAPID_SUBJECT` with your contact email (e.g., `mailto:admin@barangay.com`)

3. **Notification Pages** (JavaScript)
   - `resident/res_notification.php` - Replace `YOUR_VAPID_PUBLIC_KEY_HERE`
   - `admin/admin_notifications.php` - Replace `YOUR_VAPID_PUBLIC_KEY_HERE`
   - `worker/worker_notifications.php` - Replace `YOUR_VAPID_PUBLIC_KEY_HERE`

### Step 3: Install web-push-php Library (Recommended)

For production, install the web-push-php library for proper encryption:

```bash
composer require minishlink/web-push
```

This ensures proper encryption and handling of push notifications.

### Step 4: Test Push Notifications

1. **Enable Push Notifications:**
   - Log in as resident/admin/worker
   - Go to Notifications page
   - Click "Enable Push Notifications" or check the "Enable Push Notifications" checkbox
   - Allow browser notification permission

2. **Test Sending:**
   - Create a notification using the helper functions
   - The push notification should be sent automatically

## Files Created/Modified

### New Files:
- `sw.js` - Service Worker for handling push notifications
- `push_subscribe.php` - Endpoint for registering/unregistering push subscriptions
- `push_notification_helper.php` - Helper functions for sending push notifications
- `generate_vapid_keys.php` - Tool to generate VAPID keys

### Modified Files:
- `resident/res_notification.php` - Added push subscription UI and JavaScript
- `admin/admin_notifications.php` - Added push subscription UI and JavaScript
- `worker/worker_notifications.php` - Added push subscription JavaScript
- `resident/notification_helper.php` - Added push notification sending
- `admin/admin_notification_helper.php` - Added push notification sending

## How It Works

1. **Registration:**
   - User enables push notifications on their notification page
   - Service worker is registered
   - Browser creates a push subscription
   - Subscription is sent to server and stored

2. **Sending:**
   - When a notification is created using helper functions
   - System checks if user has push enabled
   - Push notification is sent via Web Push Protocol
   - Service worker receives and displays the notification

3. **Receiving:**
   - Service worker receives push event
   - Notification is displayed even if browser is closed
   - User can click notification to open the app

## Browser Support

Push notifications work on:
- Chrome/Edge (Windows, Android, macOS)
- Firefox (Windows, Android, macOS)
- Safari (macOS 16.4+, iOS 16.4+)
- Opera

## Security Notes

- **Never expose VAPID private key** in client-side code
- Keep VAPID keys secure and don't commit them to version control
- Use HTTPS in production (required for push notifications)

## Troubleshooting

1. **Notifications not appearing:**
   - Check browser notification permissions
   - Verify VAPID keys are correctly set
   - Check browser console for errors
   - Ensure service worker is registered

2. **Service Worker not registering:**
   - Ensure `sw.js` is accessible at root level
   - Check browser console for errors
   - Verify HTTPS is used (or localhost for development)

3. **Push subscription failing:**
   - Verify VAPID public key is correct
   - Check that keys are in correct format (base64url)
   - Ensure web-push-php library is installed for production

## Usage Examples

### Sending to Resident:
```php
require_once 'resident/notification_helper.php';

createResidentNotification(
    'request_done',
    'Request Completed',
    'Your request for Barangay Clearance has been completed.',
    [
        'resident_id' => 123,
        'request_id' => 'req_456',
        'link' => 'resident/resident_request.php'
    ]
);
```

### Sending to Admin:
```php
require_once 'admin/admin_notification_helper.php';

createAdminNotification(
    'new_walkin_request',
    'New Walk-In Request',
    'A new walk-in request has been submitted.',
    'all',
    [
        'request_id' => 'req_789',
        'link' => 'admin/admin_walkin_request.php'
    ]
);
```

## Notes

- Push notifications work even when browser is closed
- Notifications are stored in `data/push_subscriptions/` directory
- Each user type (resident, admin, worker) has separate subscription storage
- The system automatically sends push notifications when using helper functions

