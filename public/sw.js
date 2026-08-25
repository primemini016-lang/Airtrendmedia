// Airtrendmedia Service Worker — offline support, native app behavior
const CACHE_VERSION = 'airtrendmedia-v1';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const DYNAMIC_CACHE = `${CACHE_VERSION}-dynamic`;
const OFFLINE_URL = '/offline.html';

// Core assets to pre-cache
const PRECACHE_URLS = [
  '/',
  '/offline.html',
  '/css/app.css',
  '/manifest.json',
  'https://cdn.tailwindcss.com',
];

// Install: pre-cache core assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => cache.addAll(PRECACHE_URLS))
      .then(() => self.skipWaiting())
      .catch((err) => console.log('Pre-cache failed:', err))
  );
});

// Activate: clean old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys
          .filter((key) => key.startsWith('airtrendmedia-') && key !== STATIC_CACHE && key !== DYNAMIC_CACHE)
          .map((key) => caches.delete(key))
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch: cache-first for static, network-first for pages, fallback to offline
self.addEventListener('fetch', (event) => {
  const { request } = event;

  // Skip non-GET requests
  if (request.method !== 'GET') return;

  // Skip cross-origin requests (CDN, etc.) — let them pass through
  const url = new URL(request.url);
  if (url.origin !== location.origin) {
    // Cache CDN resources (tailwind)
    event.respondWith(
      caches.match(request).then((cached) => {
        return cached || fetch(request).then((response) => {
          if (response && response.status === 200) {
            const clone = response.clone();
            caches.open(DYNAMIC_CACHE).then((cache) => cache.put(request, clone));
          }
          return response;
        });
      })
    );
    return;
  }

  // For navigation requests: network-first, fallback to cache, then offline page
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((response) => {
          const clone = response.clone();
          caches.open(DYNAMIC_CACHE).then((cache) => cache.put(request, clone));
          return response;
        })
        .catch(() => {
          return caches.match(request).then((cached) => {
            return cached || caches.match(OFFLINE_URL);
          });
        })
    );
    return;
  }

  // For static assets: cache-first, fallback to network
  event.respondWith(
    caches.match(request).then((cached) => {
      if (cached) return cached;
      return fetch(request).then((response) => {
        if (response && response.status === 200 && response.type === 'basic') {
          const clone = response.clone();
          caches.open(DYNAMIC_CACHE).then((cache) => cache.put(request, clone));
        }
        return response;
      });
    })
  );
});

// Push notifications (Phoenix-style native notifications with images + slides)
self.addEventListener('push', (event) => {
  let data = {};
  try {
    data = event.data ? event.data.json() : {};
  } catch (e) {
    data = { title: 'Airtrendmedia', body: event.data ? event.data.text() : 'You have a new notification' };
  }

  const title = data.title || data.notification?.title || 'Airtrendmedia';
  const body = data.body || data.notification?.body || 'You have a new notification';
  const image = data.image || data.notification?.image;
  const link = data.link || data.data?.link || '/';

  const options = {
    body: body,
    icon: '/images/icon-192.png',
    badge: '/images/icon-192.png',
    vibrate: [100, 50, 100],
    data: { url: link, ...data.data },
    actions: [
      { action: 'open', title: 'Open' },
      { action: 'close', title: 'Dismiss' },
    ],
  };

  if (image) {
    options.image = image;
  }

  // Handle slides (Phoenix-style carousel notifications)
  if (data.slides && Array.isArray(data.slides) && data.slides.length > 0) {
    options.data.slides = data.slides;
    options.data.currentSlide = 0;
  }

  event.waitUntil(self.registration.showNotification(title, options));
});

// Notification click
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  if (event.action === 'close') return;

  const targetUrl = event.notification.data?.url || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if (client.url.includes(targetUrl) && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});

// Background sync (for sending queued actions when back online)
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-actions') {
    event.waitUntil(syncQueuedActions());
  }
});

async function syncQueuedActions() {
  // Process any queued actions from IndexedDB
  const cache = await caches.open(DYNAMIC_CACHE);
  // Placeholder for sync logic — the frontend will queue actions
}
