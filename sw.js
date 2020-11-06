self.addEventListener('install', function(event) {
  var offlinePage = new Request('offline.html');
  event.waitUntil(
    fetch(offlinePage).then(function(response) {
      return caches.open('buteo-offline').then(function(cache) {
          return cache.addAll(
         [
          'sw.js',
          '/offline.html',
          '/jquery.min.js',
          '/images/offline.jpg',
          '/images/ajax-loader.png',
          '/images/light_off-100.png',
          '/images/light_on-100.png',
          '/images/uniudtag-med-skygge-off-100.png',
          '/images/uniudtag-med-skygge-on-100.png',
          '/images/zensehome-stikkontakt-off-100.png',
          '/images/zensehome-stikkontakt-on-100.png',
          '/images/list.png',
          '/images/save.png',
          '/images/undo.png',
          '/images/settings.png'
         ]
         );

      });
  }));
});

self.addEventListener('fetch', function(event) {
if (event.request.mode === 'navigate') {
  return event.respondWith(
    fetch(event.request).catch(() => caches.match('offline.html'))
  );
}
});

self.addEventListener('refreshOffline', function(response) {
  return caches.open('buteo-offline').then(function(cache) {
    return cache.put(offlinePage, response);
  });
});

