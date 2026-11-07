const V = 'ludo-king-v5';
const PRE = [
  "assets/css/admin.css",
  "assets/css/game.css",
  "assets/css/home.css",
  "assets/css/setup.css",
  "assets/icons/icon-192.png",
  "assets/icons/icon-512.png",
  "assets/icons/icon-512-maskable.png",
  "assets/img/ui/logo.png",
  "assets/js/game.js",
  "assets/js/home.js",
  "assets/js/setup.js",
  "assets/js/sound.js",
  "assets/js/pwa.js",
  "assets/snd/bgm.wav",
  "assets/snd/move.wav",
  "assets/snd/snake_step.wav",
  "assets/snd/kill.wav",
  "assets/snd/home.wav",
  "assets/snd/win.wav",
  "assets/snd/click.wav",
  "assets/snd/dice.wav",
  "assets/snd/dice.mp3",
  "assets/snd/start.mp3",
  "index.php",
  "setup.php",
  "game.php",
  "manifest.json"
];

self.addEventListener('install', e => {
  self.skipWaiting();
  e.waitUntil(
    caches.open(V)
      .then(c => Promise.all(PRE.map(u => c.add(u).catch(() => {}))))
  );
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys()
      .then(k => Promise.all(k.filter(x => x !== V).map(x => caches.delete(x))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', e => {
  const r = e.request, u = new URL(r.url);
  if (r.method !== 'GET' || u.origin !== location.origin || u.pathname.includes('/api/') || u.pathname.includes('/admin/')) return;
  
  // For HTML / PHP documents: always fetch latest from network
  if (r.mode === 'navigate' || u.pathname.endsWith('.php')) {
    e.respondWith(
      fetch(r).then(x => {
        const c = x.clone();
        caches.open(V).then(h => h.put(r, c));
        return x;
      }).catch(() => caches.match(r, { ignoreSearch: true }).then(x => x || caches.match('index.php')))
    );
    return;
  }

  // Network first for CSS & JS to prevent stale styles
  if (u.pathname.endsWith('.css') || u.pathname.endsWith('.js')) {
    e.respondWith(
      fetch(r).then(x => {
        if (x.ok) {
          const c = x.clone();
          caches.open(V).then(k => k.put(r, c));
        }
        return x;
      }).catch(() => caches.match(r))
    );
    return;
  }

  e.respondWith(
    caches.match(r).then(h => {
      const n = fetch(r).then(x => {
        if (x.ok) {
          const c = x.clone();
          caches.open(V).then(k => k.put(r, c));
        }
        return x;
      }).catch(() => h);
      return h || n;
    })
  );
});
