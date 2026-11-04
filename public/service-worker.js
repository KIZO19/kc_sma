var STATIC_CACHE = 'kc-sma-static-v4';
var CDN_CACHE = 'kc-sma-cdn-v4';
var CDN_URLS = [
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/fonts/bootstrap-icons.woff2',
  'https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css',
  'https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css',
  'https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js',
  'https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js',
  'https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js',
  'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
  'https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js',
  'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js',
  'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css',
  'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
  'https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css',
  'https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js',
  'https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js',
  'https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js',
  'https://code.jquery.com/jquery-3.7.1.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js'
];
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
      cacheApplicationShell(),
      cacheCdnAssets()
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
    'responsive.css',
    'manifest.webmanifest',
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

function cacheCdnAssets() {
  return caches.open(CDN_CACHE).then(function (cache) {
    return Promise.allSettled(CDN_URLS.map(function (url) {
      var controller = new AbortController();
      var timeout = setTimeout(function () {
        controller.abort();
      }, 8000);

      return fetch(url, { mode: 'no-cors', signal: controller.signal }).then(function (response) {
        if (response.ok || response.type === 'opaque') {
          return cache.put(url, response);
        }
      }).finally(function () {
        clearTimeout(timeout);
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
