/**
 * Ludo King Home Page Interaction Logic
 */

document.addEventListener('DOMContentLoaded', () => {
  const soundBtn = document.getElementById('home-sound-btn');
  if (soundBtn && typeof Snd !== 'undefined') {
    soundBtn.textContent = Snd.isMuted() ? '🔇' : '🔊';
    soundBtn.onclick = () => {
      const isMuted = Snd.toggle();
      soundBtn.textContent = isMuted ? '🔇' : '🔊';
    };
  }

  const passBtn = document.getElementById('pass');
  if (passBtn) {
    passBtn.addEventListener('click', () => {
      if (typeof Snd !== 'undefined') Snd.click();
    });
  }
});
