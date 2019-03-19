(global => {
  'use strict';
  importScripts('/sw-toolbox/sw-toolbox.js');
  global.addEventListener('install', event => event.waitUntil(global.skipWaiting()));
  global.addEventListener('activate', event => event.waitUntil(global.clients.claim()));
  toolbox.precache([  '/images/offline.jpg', '/offline.html', 'list.php', 'settings.php','zense.js','zenseonoff.php','CSS/main.css' ]);

  toolbox.router.get('/images*', toolbox.cacheFirst);

  global.addEventListener('fetch', event => {
    if (event.request.mode === 'navigate') {
      event.respondWith(fetch(event.request).catch(() => caches.match('/offline.html')));
    }
   }
);

})(self);
