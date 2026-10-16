/**
 * Ludo King - PWA & App Download Manager
 * - Shows Download / Install prompt EVERY TIME user lands on home page as requested
 * - Handles both Native PWA prompt, Android APK / Add to Home screen, and iOS Safari
 * - Automatically purges stale service worker caches to guarantee latest CSS & assets
 */

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    // Purge old caches and force update service worker
    if ('caches' in window) {
      caches.keys().then(keys => {
        keys.forEach(k => {
          if (k !== 'ludo-king-v4') {
            caches.delete(k);
          }
        });
      });
    }

    navigator.serviceWorker.getRegistrations().then(regs => {
      regs.forEach(reg => reg.update());
    });

    navigator.serviceWorker.register('sw.js').catch(() => {});
  });
}

(function () {
  let deferredPrompt = null;
  let installModal = null;

  const ua = navigator.userAgent;
  const isIOS = /iphone|ipad|ipod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  const isInApp = /FBAN|FBAV|Instagram|WhatsApp|Line\/|Snapchat/i.test(ua);

  window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    deferredPrompt = e;
  });

  window.addEventListener('appinstalled', () => {
    closeModal();
  });

  function closeModal() {
    if (installModal) {
      installModal.remove();
      installModal = null;
    }
  }

  function showDownloadModal() {
    if (installModal) return;

    installModal = document.createElement('div');
    installModal.className = 'install-modal-backdrop';
    installModal.innerHTML = `
      <div class="install-dialog">
        <button class="install-close" id="install-close-btn" aria-label="Close">✕</button>
        <div class="install-logo-wrap">
          <img src="assets/icons/icon-192.png?v=${Date.now()}" class="install-app-icon" alt="Ludo King Logo">
          <span class="install-badge">OFFICIAL</span>
        </div>
        <h2 class="install-title">Ludo King™</h2>
        <p class="install-sub">Play offline, full-screen like a native mobile app!</p>
        
        <div class="install-features">
          <div class="feat-item"><span>⚡</span> Instant Load</div>
          <div class="feat-item"><span>🎲</span> 100% Free</div>
          <div class="feat-item"><span>🏆</span> HD Gameplay</div>
        </div>

        <button id="btn-do-install" class="btn-install-gold">
          <span class="dl-icon">📥</span> Download / Install App
        </button>

        <p id="install-help" class="install-instructions" style="display:none;"></p>

        <button id="btn-skip-install" class="btn-play-browser">
          Continue to Browser Game
        </button>
      </div>
    `;

    document.body.appendChild(installModal);

    // Event Listeners
    document.getElementById('install-close-btn').onclick = closeModal;
    document.getElementById('btn-skip-install').onclick = closeModal;

    document.getElementById('btn-do-install').onclick = async () => {
      const helpEl = document.getElementById('install-help');
      
      // 1. Immediately initiate real APK download
      try {
        const dlLink = document.createElement('a');
        dlLink.href = 'download.php';
        dlLink.setAttribute('download', 'LudoKing.apk');
        document.body.appendChild(dlLink);
        dlLink.click();
        dlLink.remove();
      } catch (err) {
        window.location.href = 'download.php';
      }

      // 2. Also prompt native PWA installation if supported
      if (deferredPrompt) {
        try {
          deferredPrompt.prompt();
          const choice = await deferredPrompt.userChoice.catch(() => ({}));
          deferredPrompt = null;
          if (choice && choice.outcome === 'accepted') {
            closeModal();
            return;
          }
        } catch (e) {}
      }

      // 3. Show clear confirmation & fallback link
      helpEl.style.display = 'block';
      helpEl.innerHTML = '🎉 <b>Download Started!</b><br>If download didn\'t start automatically, <a href="download.php" download="LudoKing.apk" style="color:#ffd700;font-weight:900;text-decoration:underline;">Click Here to Download</a>.';
      
      setTimeout(() => {
        closeModal();
      }, 4000);
    };
  }

  // Expose to window for floating button
  window.triggerAppDownload = showDownloadModal;

  // Show EVERY TIME on Home Page as requested by the user
  window.addEventListener('load', () => {
    const isHomePage = !!document.getElementById('home-screen') || !!document.getElementById('pass');
    if (isHomePage) {
      setTimeout(showDownloadModal, 450);
    }
  });
})();
