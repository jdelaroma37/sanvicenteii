// PWA Service Worker Registration
// This script registers the service worker for offline functionality

(function() {
  'use strict';

  // Check if service workers are supported
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      // Get the root path dynamically
      const rootPath = window.location.pathname.split('/').slice(0, -1).join('/') || '';
      const swPath = rootPath + '/sw.js';
      
      navigator.serviceWorker.register(swPath)
        .then(function(registration) {
          console.log('[PWA] Service Worker registered successfully:', registration.scope);
          
          // Check for updates periodically
          setInterval(function() {
            registration.update();
          }, 60000); // Check every minute
          
          // Handle updates
          registration.addEventListener('updatefound', function() {
            const newWorker = registration.installing;
            console.log('[PWA] New service worker found, installing...');
            
            newWorker.addEventListener('statechange', function() {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                // New service worker available, show update notification
                if (confirm('A new version of the app is available. Reload to update?')) {
                  window.location.reload();
                }
              }
            });
          });
        })
        .catch(function(error) {
          console.error('[PWA] Service Worker registration failed:', error);
        });
      
      // Listen for service worker messages
      navigator.serviceWorker.addEventListener('message', function(event) {
        console.log('[PWA] Message from service worker:', event.data);
      });
      
      // Check if app is running in standalone mode (installed)
      if (window.matchMedia('(display-mode: standalone)').matches || 
          window.navigator.standalone === true) {
        console.log('[PWA] App is running in standalone mode');
        document.body.classList.add('pwa-standalone');
      }
      
      // Listen for online/offline events
      window.addEventListener('online', function() {
        console.log('[PWA] App is online');
        showNotification('Connection restored', 'You are back online');
      });
      
      window.addEventListener('offline', function() {
        console.log('[PWA] App is offline');
        showNotification('You are offline', 'Some features may be limited');
      });
      
      // Show notification helper
      function showNotification(title, message) {
        // You can customize this to show a toast notification
        if ('Notification' in window && Notification.permission === 'granted') {
          new Notification(title, {
            body: message,
            icon: rootPath + '/favicon_io/android-chrome-192x192.png',
            badge: rootPath + '/favicon_io/favicon-32x32.png'
          });
        }
      }
      
      // Request notification permission on first load
      if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission().then(function(permission) {
          console.log('[PWA] Notification permission:', permission);
        });
      }
    });
  } else {
    console.warn('[PWA] Service Workers are not supported in this browser');
  }
  
  // Install prompt handling
  let deferredPrompt;
  const installButton = document.getElementById('installPWA');
  
  window.addEventListener('beforeinstallprompt', function(e) {
    console.log('[PWA] Install prompt available');
    // Prevent the mini-infobar from appearing
    e.preventDefault();
    // Stash the event so it can be triggered later
    deferredPrompt = e;
    
    // Show install button if it exists
    if (installButton) {
      installButton.style.display = 'block';
      installButton.addEventListener('click', function() {
        // Show the install prompt
        deferredPrompt.prompt();
        // Wait for the user to respond
        deferredPrompt.userChoice.then(function(choiceResult) {
          if (choiceResult.outcome === 'accepted') {
            console.log('[PWA] User accepted the install prompt');
          } else {
            console.log('[PWA] User dismissed the install prompt');
          }
          deferredPrompt = null;
          if (installButton) {
            installButton.style.display = 'none';
          }
        });
      });
    }
  });
  
  // Track if app was installed
  window.addEventListener('appinstalled', function() {
    console.log('[PWA] App was installed');
    deferredPrompt = null;
    if (installButton) {
      installButton.style.display = 'none';
    }
  });
})();

