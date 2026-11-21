/**
 * Original Ludo King Sound & Music Engine
 * - Plays 100% authentic Ludo King audio assets (.mp3)
 * - Seamless looping original BGM
 * - Web Audio API fallback for zero-latency playback
 */
const Snd = (() => {
  let ctx = null;
  let muted = localStorage.getItem('ludo_muted') === '1';
  let bgmAudio = null;
  let bgmStarted = false;
  const audioCache = {};

  const SFX_FILES = ['click', 'dice', 'move', 'snake_step', 'kill', 'home', 'win', 'start', 'snake_hiss'];

  // Preload authentic sound files (WAV for crisp kill & snake retreat, MP3 for music/fx)
  SFX_FILES.forEach(name => {
    try {
      const ext = (name === 'kill' || name === 'snake_step' || name === 'snake_hiss') ? '.wav' : '.mp3';
      const a = new Audio('assets/snd/' + name + ext + '?v=' + Date.now());
      a.preload = 'auto';
      audioCache[name] = a;
    } catch (e) {}
  });

  // Setup authentic Ludo King BGM
  try {
    bgmAudio = new Audio('assets/snd/bgm.mp3');
    bgmAudio.loop = true;
    bgmAudio.volume = 0.35;
  } catch (e) {}

  function getCtx() {
    if (!ctx) {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (AudioCtx) ctx = new AudioCtx();
    }
    if (ctx && ctx.state === 'suspended') {
      ctx.resume().catch(() => {});
    }
    return ctx;
  }

  function unlock() {
    getCtx();
    if (!muted && !bgmStarted) {
      playBgm();
    }
  }

  function playBgm() {
    if (muted || !bgmAudio) return;
    bgmStarted = true;
    bgmAudio.play().catch(() => {
      bgmStarted = false;
    });
  }

  function stopBgm() {
    if (bgmAudio) {
      bgmAudio.pause();
      bgmAudio.currentTime = 0;
    }
    bgmStarted = false;
  }

  // Zero-latency playback: decode every sound ahead of time into a Web Audio buffer
  const bufCache = {};
  function preDecode() {
    try {
      const AC = window.AudioContext || window.webkitAudioContext;
      if (!AC) return;
      if (!ctx) ctx = new AC();
      SFX_FILES.forEach(name => {
        const ext = (name === 'kill' || name === 'snake_step' || name === 'snake_hiss') ? '.wav' : '.mp3';
        fetch('assets/snd/' + name + ext)
          .then(r => r.arrayBuffer())
          .then(ab => new Promise((res, rej) => ctx.decodeAudioData(ab, res, rej)))
          .then(buf => { bufCache[name] = buf; })
          .catch(() => {});
      });
    } catch (e) {}
  }
  preDecode();

  function playFile(name, vol = 1.0) {
    if (muted) return true;
    const buf = bufCache[name];
    if (buf && ctx && ctx.state === 'running') {
      try {
        const src = ctx.createBufferSource();
        const g = ctx.createGain();
        src.buffer = buf;
        g.gain.value = Math.min(1.0, Math.max(0.0, vol));
        src.connect(g);
        g.connect(ctx.destination);
        src.start(0);
        return true;
      } catch (e) {}
    }
    const a = audioCache[name];
    if (a) {
      try {
        const c = a.cloneNode();
        c.volume = Math.min(1.0, Math.max(0.0, vol));
        c.play().catch(() => {});
        return true;
      } catch (e) {}
    }
    return false;
  }

  // Synthesizer fallback
  function synthTone(freq, dur, type = 'sine', vol = 0.2, startOffset = 0, freqEnd = null) {
    if (muted) return;
    try {
      const ac = getCtx();
      if (!ac) return;
      const osc = ac.createOscillator();
      const gain = ac.createGain();
      const t = ac.currentTime + startOffset;
      osc.type = type;
      osc.frequency.setValueAtTime(freq, t);
      if (freqEnd) {
        osc.frequency.exponentialRampToValueAtTime(Math.max(1, freqEnd), t + dur);
      }
      gain.gain.setValueAtTime(vol, t);
      gain.gain.exponentialRampToValueAtTime(0.0001, t + dur);
      osc.connect(gain);
      gain.connect(ac.destination);
      osc.start(t);
      osc.stop(t + dur);
    } catch (e) {}
  }

  // Unlock on first interaction
  const startOnGesture = () => {
    unlock();
    window.removeEventListener('click', startOnGesture);
    window.removeEventListener('touchstart', startOnGesture);
  };
  window.addEventListener('click', startOnGesture, { once: true });
  window.addEventListener('touchstart', startOnGesture, { once: true });

  return {
    unlock,
    isMuted: () => muted,
    toggle: () => {
      muted = !muted;
      localStorage.setItem('ludo_muted', muted ? '1' : '0');
      if (muted) {
        if (bgmAudio) bgmAudio.pause();
      } else {
        unlock();
        if (bgmAudio) bgmAudio.play().catch(() => {});
      }
      return muted;
    },
    vibrate: (pattern = 40) => {
      try {
        if (!muted && navigator.vibrate) {
          navigator.vibrate(pattern);
        }
      } catch (e) {}
    },
    click: () => {
      unlock();
      if (!playFile('click', 0.8)) {
        synthTone(880, 0.04, 'square', 0.15);
      }
    },
    dice: () => {
      unlock();
      try { if (!muted && navigator.vibrate) navigator.vibrate(35); } catch (e) {}
      if (!playFile('dice', 1.0)) {
        synthTone(500, 0.2, 'sawtooth', 0.25);
      }
    },
    move: () => {
      unlock();
      if (!playFile('move', 0.95)) {
        synthTone(520, 0.07, 'sine', 0.35, 0, 780);
      }
    },
    open: () => {
      // Signature Goti Open / Release sound when exiting yard on 6
      unlock();
      try { if (!muted && navigator.vibrate) navigator.vibrate(60); } catch (e) {}
      if (!playFile('start', 1.0)) {
        // Bright cheerful upward pop/ding arpeggio
        [587, 880, 1174].forEach((f, i) => synthTone(f, 0.12, 'triangle', 0.3, i * 0.05, f * 1.2));
      } else {
        // Layer with quick pop
        synthTone(600, 0.08, 'sine', 0.25, 0, 1100);
      }
    },
    retreatStep: (stepIndex = 1, totalSteps = 10) => {
      if (muted) return;
      playFile('snake_step', 0.85);

      try {
        const ac = getCtx();
        if (ac) {
          const ratio = Math.max(0.1, Math.min(1.0, stepIndex / Math.max(1, totalSteps)));
          const startF = 380 + ratio * 850;
          const osc = ac.createOscillator();
          const gain = ac.createGain();
          const t = ac.currentTime;
          osc.type = 'sawtooth';
          osc.frequency.setValueAtTime(startF, t);
          osc.frequency.exponentialRampToValueAtTime(startF * 0.6, t + 0.04);
          gain.gain.setValueAtTime(0.08, t);
          gain.gain.exponentialRampToValueAtTime(0.001, t + 0.04);
          osc.connect(gain);
          gain.connect(ac.destination);
          osc.start(t);
          osc.stop(t + 0.04);
        }
      } catch (e) {}
    },
    kill: () => {
      // Signature Goti Capture / Kill Sound & Impact Vibration
      unlock();
      try { if (!muted && navigator.vibrate) navigator.vibrate([100, 50, 140]); } catch (e) {}
      
      // Play kill / crash sound
      const played = playFile('kill', 1.0) || playFile('snake_hiss', 1.0);
      // Layer with low-frequency punch impact
      synthTone(120, 0.18, 'sawtooth', 0.5, 0, 40);
      synthTone(320, 0.12, 'triangle', 0.35, 0.04, 180);
    },
    home: () => {
      unlock();
      try { if (!muted && navigator.vibrate) navigator.vibrate([60, 40, 60, 40, 120]); } catch (e) {}
      if (!playFile('home', 1.0)) {
        [523, 659, 784, 1046, 1318].forEach((f, i) => synthTone(f, 0.22, 'triangle', 0.25, i * 0.08));
      }
    },
    win: () => {
      unlock();
      if (!playFile('win', 1.0)) {
        [523, 523, 659, 784, 1046].forEach((f, i) => synthTone(f, 0.3, 'triangle', 0.35, i * 0.15));
      }
    },
    start: () => {
      unlock();
      playFile('start', 0.9);
    },
    tick: () => {
      if (muted) return;
      synthTone(950, 0.05, 'sine', 0.18, 0, 800);
    },
    bonus: () => {
      if (muted) return;
      try { if (navigator.vibrate) navigator.vibrate([40, 30, 70]); } catch (e) {}
      [659, 880, 1046, 1318].forEach((f, i) => synthTone(f, 0.14, 'triangle', 0.22, i * 0.06));
    }
  };
})();
