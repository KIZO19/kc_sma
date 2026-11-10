var STATIC_CACHE = 'kc-sma-static-v9';
var CDN_CACHE = 'kc-sma-cdn-v5';
var CDN_HOSTS = [
  'cdn.jsdelivr.net',
  'cdn.datatables.net',
  'code.jquery.com',
  'cdnjs.cloudflare.com',
  'unpkg.com',
  'api.qrserver.com'
];

self.addEventListener('install', function (event) {
  event.waitUntil(
    Promise.all([
      self.skipWaiting(),
      cacheApplicationShell()
    ])
  );
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(keys.map(function (key) {
        if (
          key.indexOf('kc-sma-static-') === 0 && key !== STATIC_CACHE
          || key.indexOf('kc-sma-cdn-') === 0 && key !== CDN_CACHE
        ) {
          return caches.delete(key);
        }
      }));
    }).then(function () {
      return self.clients.claim();
    })
  );
});

function cacheApplicationShell() {
  var assetRoot = new URL('assets/', self.location.href);
  var assets = [
    'adminlte.min.css',
    'adminlte.min.js',
    'app-datagrid.css',
    'app-datagrid.js',
    'live-notifications.js',
    'pwa-install.js',
    'floating-buttons.js',
    'ai-chat.css',
    'ai-chat.js',
    'responsive.css',
    'theme.css',
    '../manifest.php',
    'favicon.svg',
    'icons/kc-sma-192.png',
    'icons/kc-sma-512.png',
    'icons/kc-sma-maskable-512.png'
  ];

  return caches.open(STATIC_CACHE).then(function (cache) {
    return Promise.all(assets.map(function (asset) {
      var url = new URL(asset, assetRoot);
      return fetch(url).then(function (response) {
        if (!response.ok) {
          throw new Error('Unable to precache ' + url.pathname);
        }
        return cache.put(url, response);
      });
    }));
  });
}

function isApplicationStaticAsset(url) {
  return url.origin === self.location.origin
    && (url.pathname.indexOf('/assets/') !== -1 || url.pathname.indexOf('/vendor/') !== -1);
}

function isCdnAsset(url) {
  return CDN_HOSTS.indexOf(url.hostname) !== -1;
}

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  const targetUrl = new URL(
    event.notification.data && event.notification.data.url || self.registration.scope,
    self.registration.scope
  );
  if (targetUrl.origin !== self.location.origin) {
    return;
  }

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clients) {
      for (const client of clients) {
        if (new URL(client.url).origin === targetUrl.origin) {
          return client.navigate(targetUrl.href).then(function (navigatedClient) {
            return navigatedClient ? navigatedClient.focus() : client.focus();
          });
        }
      }
      return self.clients.openWindow(targetUrl.href);
    })
  );
});

self.addEventListener('push', function (event) {
  var payload = {};
  try {
    payload = event.data ? event.data.json() : {};
  } catch (error) {
    payload = { body: event.data ? event.data.text() : '' };
  }

  var title = typeof payload.title === 'string' && payload.title !== ''
    ? payload.title
    : 'Nouvelle notification KC_SMA';
  var targetUrl = new URL(
    typeof payload.url === 'string' ? payload.url : self.registration.scope,
    self.registration.scope
  );
  if (targetUrl.origin !== self.location.origin) {
    targetUrl = new URL(self.registration.scope);
  }

  event.waitUntil(self.registration.showNotification(title, {
    body: typeof payload.body === 'string' ? payload.body : '',
    icon: new URL('assets/icons/kc-sma-192.png', self.registration.scope).href,
    badge: new URL('assets/icons/kc-sma-192.png', self.registration.scope).href,
    tag: typeof payload.tag === 'string' ? payload.tag : 'kc-sma-push',
    renotify: true,
    silent: false,
    data: { url: targetUrl.href }
  }));
});

self.addEventListener('fetch', function (event) {
  if (event.request.method !== 'GET') {
    return;
  }

  var requestUrl = new URL(event.request.url);
  if (isApplicationStaticAsset(requestUrl)) {
    var staticNetworkRequest = fetch(event.request).then(function (response) {
      if (response.ok) {
        return caches.open(STATIC_CACHE).then(function (cache) {
          return cache.put(event.request, response.clone()).then(function () {
            return response;
          });
        });
      }
      return response;
    });
    event.waitUntil(staticNetworkRequest.then(function () {}, function () {}));
    event.respondWith(
      caches.open(STATIC_CACHE).then(function (cache) {
        return cache.match(event.request).then(function (cachedResponse) {
          return cachedResponse || staticNetworkRequest;
        });
      })
    );
    return;
  }

  if (isCdnAsset(requestUrl)) {
    var cdnNetworkRequest = fetch(event.request).then(function (response) {
      if (response.ok || response.type === 'opaque') {
        return caches.open(CDN_CACHE).then(function (cache) {
          return cache.put(event.request, response.clone()).then(function () {
            return response;
          });
        });
      }
      return response;
    });
    event.waitUntil(cdnNetworkRequest.then(function () {}, function () {}));
    event.respondWith(
      caches.open(CDN_CACHE).then(function (cache) {
        return cache.match(event.request).then(function (cachedResponse) {
          return cachedResponse || cdnNetworkRequest;
        });
      })
    );
    return;
  }

  if (requestUrl.origin !== self.location.origin) {
    return;
  }

  event.respondWith(
    fetch(event.request).catch(function () {
      if (event.request.mode === 'navigate') {
        return new Response(
          '<!doctype html><html lang="fr"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Hors connexion</title><body><h1>Connexion indisponible</h1><p>Vérifiez votre connexion Internet puis réessayez.</p></body></html>',
          { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
        );
      }

      return Response.error();
    })
  );
});
