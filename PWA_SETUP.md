# PWA Setup Guide - Barangay San Vicente II Portal

## Overview
Ang application ay naka-configure na bilang Progressive Web App (PWA), na nagbibigay ng:
- **Offline functionality** - Pwede gamitin kahit walang internet
- **App-like experience** - Pwede i-install sa phone/tablet
- **Fast loading** - Cached resources para sa mabilis na loading

## Features

### ✅ Installed Features
1. **Service Worker** (`sw.js`) - Handles offline caching
2. **Web App Manifest** (`favicon_io/site.webmanifest`) - App configuration
3. **PWA Registration Script** (`pwa-register.js`) - Auto-registers service worker
4. **PWA Meta Tags** - Added to all pages via `favicon_links.php`

### 📱 Installation

#### Para sa Users:
1. **Mobile (Android/iPhone):**
   - Buksan ang website sa browser
   - May lalabas na prompt na "Add to Home Screen" o "Install App"
   - Pindutin ang "Install" o "Add"
   - Makikita na ang app sa home screen

2. **Desktop (Chrome/Edge):**
   - Buksan ang website
   - Hanapin ang install icon sa address bar (tulad ng puzzle piece)
   - Pindutin ang "Install"
   - Mag-oopen ang app sa standalone window

### 🔧 Configuration

#### Para sa Developers:

**1. Update Service Worker Paths:**
Kung iba ang deployment path mo, i-update ang `BASE_PATH` sa `sw.js`:
```javascript
const BASE_PATH = self.location.pathname.replace('/sw.js', '');
```
Ang service worker ay automatic na mag-detect ng base path, pero pwede mo rin i-hardcode kung kailangan.

**2. Update Manifest:**
I-edit ang `favicon_io/site.webmanifest` para sa:
- App name at description
- Theme colors
- Start URL at scope
- Icons paths

**3. Cache Strategy:**
Ang service worker ay gumagamit ng:
- **Cache First** - Para sa static assets (CSS, JS, images)
- **Network First** - Para sa dynamic content (PHP pages)
- **Offline Fallback** - Shows cached version kapag offline

### 📋 Testing

1. **Test Offline Mode:**
   - Open DevTools (F12)
   - Go to Application tab > Service Workers
   - Check "Offline" checkbox
   - Refresh page - dapat may cached version

2. **Test Installation:**
   - Open website
   - Check console for PWA registration messages
   - Look for install prompt

3. **Test Cache:**
   - Open DevTools > Application > Cache Storage
   - Makikita mo ang cached files
   - Pwede mo i-clear ang cache kung kailangan

### 🐛 Troubleshooting

**Service Worker hindi nag-register:**
- Check console for errors
- Ensure `sw.js` is accessible (try opening `/sw.js` directly)
- Check HTTPS requirement (service workers need HTTPS, except localhost)

**App hindi ma-install:**
- Check manifest file validity
- Ensure icons are accessible
- Check browser console for errors
- Verify manifest is linked in HTML

**Offline mode hindi gumagana:**
- Clear cache and re-register service worker
- Check if assets are being cached (DevTools > Application > Cache)
- Verify service worker is active (DevTools > Application > Service Workers)

### 📝 Notes

- **HTTPS Required:** Service workers need HTTPS sa production (except localhost)
- **Browser Support:** Supported sa modern browsers (Chrome, Firefox, Edge, Safari)
- **Cache Updates:** Service worker auto-updates every minute
- **Storage:** Cache storage limit depends sa browser (usually 50MB+)

### 🚀 Production Deployment

1. **Enable HTTPS:**
   - Service workers require HTTPS (except localhost)
   - Get SSL certificate for your domain

2. **Update Paths:**
   - Update all hardcoded paths sa service worker
   - Update manifest start_url at scope

3. **Test Thoroughly:**
   - Test offline functionality
   - Test installation on different devices
   - Test cache behavior

4. **Monitor:**
   - Check service worker registration
   - Monitor cache usage
   - Check for errors sa console

### 📚 Additional Resources

- [MDN: Progressive Web Apps](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)
- [Web.dev: PWA Guide](https://web.dev/progressive-web-apps/)
- [Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)

---

**Last Updated:** 2025
**Version:** 1.0.0

