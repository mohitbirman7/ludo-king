/**
 * Ludo King - Core Game Engine
 * - Instant responsive dice roll (zero delay, parallel API fetch preserving admin overrides)
 * - Step-by-step snake rewind on token capture (no diagonal shortcuts, authentic sound per box)
 * - Persistent game state across page refreshes (localStorage)
 * - 100% authentic Ludo King rules, turns, and animations
 */

const STORAGE_KEY = 'ludo_game_state';

// Load setup session
let S = JSON.parse(sessionStorage.getItem('ludo') || 'null');

// If no sessionStorage, try restoring from localStorage saved game
if (!S) {
  try {
    const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
    if (saved && saved.session) {
      S = saved.session;
      sessionStorage.setItem('ludo', JSON.stringify(S));
    }
  } catch (e) {}
}

if (!S || !S.players || !S.players.length) {
  S = {
    players: [
      { color: 'blue', name: 'Player 1' },
      { color: 'red', name: 'Player 2' },
      { color: 'green', name: 'Player 3' },
      { color: 'yellow', name: 'Player 4' }
    ],
    mode: 'classic',
    oneOut: false,
    token: 0,
    theme: 'classic'
  };
  sessionStorage.setItem('ludo', JSON.stringify(S));
}

document.body.classList.add((S && S.theme) || 'classic');

const COL = ['red', 'green', 'yellow', 'blue'];
const ORDER = ['blue', 'red', 'green', 'yellow'];
const START = { red: 0, green: 13, yellow: 26, blue: 39 };
const SAFE = [0, 8, 13, 21, 26, 34, 39, 47];

const PATH = [
  [6,1],[6,2],[6,3],[6,4],[6,5],[5,6],[4,6],[3,6],[2,6],[1,6],[0,6],[0,7],[0,8],
  [1,8],[2,8],[3,8],[4,8],[5,8],[6,9],[6,10],[6,11],[6,12],[6,13],[6,14],[7,14],
  [8,14],[8,13],[8,12],[8,11],[8,10],[8,9],[9,8],[10,8],[11,8],[12,8],[13,8],[14,8],
  [14,7],[14,6],[13,6],[12,6],[11,6],[10,6],[9,6],[8,5],[8,4],[8,3],[8,2],[8,1],[8,0],[7,0],[6,0]
];

const LANE = {
  red:    [[7,1],[7,2],[7,3],[7,4],[7,5]],
  green:  [[1,7],[2,7],[3,7],[4,7],[5,7]],
  yellow: [[7,13],[7,12],[7,11],[7,10],[7,9]],
  blue:   [[13,7],[12,7],[11,7],[10,7],[9,7]]
};

const OFF = { red: [0,0], green: [0,9], yellow: [9,9], blue: [9,0] };
const SP = [[1.7, 1.7], [1.7, 3.3], [3.3, 1.7], [3.3, 3.3]];
const HC = { red: [7, 6.4], green: [6.4, 7], yellow: [7, 7.6], blue: [7.6, 7] };

const BASE = {};
COL.forEach(c => BASE[c] = SP.map(([r, k]) => [r + OFF[c][0], k + OFF[c][1]]));

const AREA = {
  red: '1/1/7/7',
  green: '1/10/7/16',
  yellow: '10/10/16/16',
  blue: '10/1/16/7'
};

const AR = {
  '7,0':  ['→', 'red'],
  '0,7':  ['↓', 'green'],
  '7,14': ['←', 'yellow'],
  '14,7': ['↑', 'blue']
};

const $ = s => document.querySelector(s);
const board = $('#board');

const P = S.players.sort((a, b) => ORDER.indexOf(a.color) - ORDER.indexOf(b.color));
const pk = {};
PATH.forEach((p, i) => pk[p] = i);
const lk = {};
COL.forEach(c => LANE[c].forEach(p => lk[p] = c));

// Render Board Bases
COL.forEach(c => {
  const b = document.createElement('div');
  b.className = 'base ' + c;
  b.style.gridArea = AREA[c];
  b.innerHTML = '<div class="inner"><div class="base-decor"></div></div>';
  const pl = P.find(p => p.color === c);
  if (pl) {
    const n = document.createElement('span');
    n.className = 'nm';
    n.textContent = pl.name;
    b.appendChild(n);
  }
  board.appendChild(b);
});

// Render 15x15 Track Cells
for (let r = 0; r < 15; r++) {
  for (let c = 0; c < 15; c++) {
    if ((r < 6 && (c < 6 || c > 8)) ||
        (r > 8 && (c < 6 || c > 8)) ||
        (r >= 6 && r <= 8 && c >= 6 && c <= 8)) {
      continue;
    }
    const d = document.createElement('div');
    d.className = 'c';
    d.style.gridArea = `${r + 1}/${c + 1}`;
    const i = pk[[r, c]];

    if (lk[[r, c]]) {
      d.classList.add(lk[[r, c]]);
    } else if (i !== undefined) {
      const sc = COL.find(x => START[x] === i);
      if (sc) {
        d.classList.add(sc);
      } else if (SAFE.includes(i)) {
        d.innerHTML = '<span class="star-icon">★</span>';
      }
    }

    const a = AR[r + ',' + c];
    if (a) {
      d.innerHTML = `<span class="ar-arrow">${a[0]}</span>`;
      d.classList.add('ar');
      d.style.color = { red: '#e8212c', green: '#0c8f43', yellow: '#e6b800', blue: '#1ea3f2' }[a[1]];
    }

    board.appendChild(d);
  }
}

// Center Finish Triangle
const ct = document.createElement('div');
ct.className = 'ctr';
ct.innerHTML = '<div class="ctr-crown">👑</div>';
board.appendChild(ct);

// Yard Base Slots
COL.forEach(c => BASE[c].forEach(([r, k]) => {
  const s = document.createElement('div');
  s.className = 'sl ' + c;
  s.style.left = (k * 100 / 15) + '%';
  s.style.top = (r * 100 / 15) + '%';
  board.appendChild(s);
}));

const slot = c => document.querySelector(`.slot[data-c="${c}"]`);
const dieOf = c => slot(c).querySelector('.die');

// Hide slots for inactive players & fill player card names/badges
document.querySelectorAll('.slot').forEach(s => {
  const pl = P.find(p => p.color === s.dataset.c);
  if (!pl) {
    s.classList.add('hide');
  } else {
    const nameEl = s.querySelector('.pn');
    if (nameEl) nameEl.textContent = pl.name;
  }
});

const PIPS = {
  1: [4],
  2: [0, 8],
  3: [0, 4, 8],
  4: [0, 2, 6, 8],
  5: [0, 2, 4, 6, 8],
  6: [0, 2, 3, 5, 6, 8]
};

const ORI = {
  1: [0, 0],
  6: [0, 180],
  2: [0, -90],
  5: [0, 90],
  3: [-90, 0],
  4: [90, 0]
};

function cube(el) {
  if (el.firstChild) return el.firstChild;
  const c = document.createElement('div');
  c.className = 'cube';
  c.innerHTML = [1, 2, 3, 4, 5, 6].map(n =>
    `<div class="f f${n}">${Array.from({ length: 9 }, (_, i) => `<i class="${PIPS[n].includes(i) ? 'on' : ''}"></i>`).join('')}</div>`
  ).join('');
  el.appendChild(c);
  return c;
}

function face(el, v) {
  const [x, y] = ORI[v] || [0, 0];
  cube(el).style.transform = `rotateX(${x}deg) rotateY(${y}deg)`;
  el.classList.add('show');
}

function clear(el) {
  el.classList.remove('show');
}

// ----------------------------------------------------
// STATE INITIALIZATION & PERSISTENCE
// ----------------------------------------------------
let cur = 0;
let dice = 0;
let state = 'roll'; // 'roll' | 'move' | 'over'
let busy = false;
let sixes = 0;
const rank = [];

// Try to restore existing game progress from localStorage
let restored = false;
try {
  const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
  if (saved && saved.players && saved.players.length === P.length) {
    // Check if player colors match
    const colorsMatch = P.every((p, i) => p.color === saved.players[i].color);
    if (colorsMatch) {
      cur = saved.cur ?? 0;
      dice = saved.dice ?? 0;
      state = saved.state ?? 'roll';
      sixes = saved.sixes ?? 0;
      if (Array.isArray(saved.rank)) {
        saved.rank.forEach(c => {
          const found = P.find(p => p.color === c);
          if (found && !rank.includes(found)) rank.push(found);
        });
      }
      P.forEach((p, i) => {
        p.pos = [...saved.players[i].pos];
        p.out = !!saved.players[i].out;
      });
      restored = true;
    }
  }
} catch (e) {}

if (!restored) {
  P.forEach(p => {
    p.pos = S.oneOut ? [0, -1, -1, -1] : [-1, -1, -1, -1];
    p.out = false;
  });
}

function saveGameState() {
  if (state === 'over') {
    localStorage.removeItem(STORAGE_KEY);
    return;
  }
  try {
    const payload = {
      session: S,
      cur,
      dice,
      state,
      sixes,
      rank: rank.map(p => p.color),
      players: P.map(p => ({
        color: p.color,
        name: p.name,
        pos: [...p.pos],
        out: !!p.out
      }))
    };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
  } catch (e) {}
}

const team = c => S.mode === 'team' ? (ORDER.indexOf(c) % 2) : c;
const need = S.mode === 'quick' ? 1 : 4;
const done = p => p.pos.filter(x => x === 56).length >= need;
const fin = p => p.pos.every(x => x === 56);
const mateOf = p => P.find(o => o !== p && team(o.color) === team(p.color));
const ctrl = p => {
  if (S.mode === 'team' && fin(p)) {
    const m = mateOf(p);
    if (m && !fin(m)) return m;
  }
  return p;
};

function cell(p, i) {
  const q = p.pos[i], k = p.color;
  if (q < 0) return BASE[k][i];
  if (q <= 50) return PATH[(START[k] + q) % 52];
  if (q < 56) return LANE[k][q - 51];
  return HC[k];
}

function place(p, i) {
  const [r, c] = cell(p, i);
  const t = p.el[i];
  t.style.left = (c * 100 / 15) + '%';
  t.style.top = (r * 100 / 15) + '%';
}

function layout() {
  const g = {};
  P.forEach(p => p.pos.forEach((q, i) => {
    if (q >= 0 && q < 56) {
      const k = cell(p, i) + '';
      (g[k] = g[k] || []).push([p, i]);
    }
  }));

  P.forEach(p => p.el.forEach(t => { t.style.transform = ''; }));

  Object.values(g).forEach(a => {
    if (a.length < 2) return;
    a.forEach(([p, i], j) => {
      const t = p.el[i];
      const n = a.length;
      const x = (j - (n - 1) / 2) * 26;
      t.style.transform = `translateX(${x}%) scale(${n > 2 ? 0.72 : 0.82})`;
    });
  });
}

// Create Tokens
P.forEach(p => {
  p.el = [];
  for (let i = 0; i < 4; i++) {
    const t = document.createElement('div');
    t.className = 'tk ' + p.color;
    t.innerHTML = `
      <div class="tk-inner">
        <svg viewBox="0 0 40 52"><use href="#pn"/></svg>
      </div>`;
    t.onclick = () => pick(i, p);
    board.appendChild(t);
    p.el.push(t);
    place(p, i);
  }
});
layout();

const sleep = ms => new Promise(r => setTimeout(r, ms));

function ui() {
  document.querySelectorAll('.slot').forEach(s => {
    const isCur = s.dataset.c === P[cur].color;
    const act = isCur && (state === 'roll' || state === 'move');
    s.classList.toggle('act', act);
    if (!isCur) clear(s.querySelector('.die'));
  });

  const curSlot = slot(P[cur].color);
  if (curSlot) {
    if (state === 'roll') {
      face(dieOf(P[cur].color), dice || 1);
      $('#msg').textContent = `${P[cur].name}'s Turn - Roll Dice!`;
    } else if (state === 'move') {
      face(dieOf(P[cur].color), dice);
      $('#msg').textContent = 'Tap a glowing token to move';
    }
  }
  saveGameState();
}

function movable(p, d) {
  const m = [];
  p.pos.forEach((q, i) => {
    if (q < 0 ? d === 6 : q + d <= 56) m.push(i);
  });
  return m;
}

async function tumble(el, down, d) {
  el.classList.add('big');
  el.classList.add('show');
  const c = cube(el);
  const s = down ? 1 : -1;
  const [fx, fy] = ORI[d] || [0, 0];

  // Set target transform on element immediately so when animation finishes it stays on d
  c.style.transform = `rotateX(${fx}deg) rotateY(${fy}deg)`;

  // Full 3D rotation ending EXACTLY on [fx, fy]
  const t0 = `rotateX(${fx - 720}deg) rotateY(${fy - 1080}deg) rotateZ(0deg)`;
  const tm = `rotateX(${fx - 360}deg) rotateY(${fy - 540}deg) rotateZ(20deg)`;
  const t1 = `rotateX(${fx}deg) rotateY(${fy}deg) rotateZ(0deg)`;

  const a1 = c.animate([{ transform: t0 }, { transform: tm, offset: 0.5 }, { transform: t1 }], {
    duration: 620,
    easing: 'cubic-bezier(.22,.75,.35,1)'
  });
  const a2 = el.animate([
    { transform: 'translate3d(0,0,0) scale(1)' },
    { transform: `translate3d(2px,${s * 2}px,0) scale(1.22)`, offset: 0.35 },
    { transform: `translate3d(-1px,${s * 3}px,0) scale(1.26)`, offset: 0.65 },
    { transform: 'translate3d(0,0,0) scale(1)' }
  ], {
    duration: 620,
    easing: 'ease-in-out'
  });

  await Promise.all([a1.finished, a2.finished]).catch(() => {});
  el.classList.remove('big');
  face(el, d);
}

// ----------------------------------------------------
// INSTANT DICE ROLL (Zero Delay + Lands Directly on d without 1-flash)
// ----------------------------------------------------
document.querySelectorAll('.slot').forEach(s => {
  const db = s.querySelector('.db');
  if (!db) return;
  db.onclick = async () => {
    if (state !== 'roll' || busy || s.dataset.c !== P[cur].color) return;
    busy = true;
    Snd.unlock();
    s.classList.remove('act');

    const el = s.querySelector('.die');
    const isTopRow = s.closest('.rowS') === document.querySelector('.rowS');

    // 1. Play dice sound instantly
    Snd.dice();

    // 2. Fetch admin override with micro-timeout fallback
    const rollFetch = fetch('api/roll.php?player=' + encodeURIComponent(P[cur].color), { cache: 'no-store' })
      .then(r => r.json())
      .then(j => +j.dice)
      .catch(() => 0);
    const timeoutPromise = new Promise(r => setTimeout(() => r(0), 80));

    let fetchedDice = await Promise.race([rollFetch, timeoutPromise]);
    let d = fetchedDice;
    if (!(d >= 1 && d <= 6)) {
      d = 1 + (Math.random() * 6 | 0);
    }

    // 3. Tumble animation lands directly on d (No flash of 1!)
    await tumble(el, isTopRow, d);
    dice = d;

    sixes = (d === 6) ? sixes + 1 : 0;
    saveGameState();

    if (sixes >= 3) {
      sixes = 0;
      $('#msg').textContent = 'Three 6s in a row - Turn lost!';
      await sleep(1100);
      busy = false;
      next();
      return;
    }

    const A = ctrl(P[cur]);
    const m = movable(A, d);
    busy = false;

    if (!m.length) {
      $('#msg').textContent = 'No move possible';
      await sleep(800);
      next();
      return;
    }

    state = 'move';
    $('#msg').textContent = 'Tap a glowing token';
    m.forEach(i => A.el[i].classList.add('go'));
    saveGameState();

    // If only one token can move, auto-select it smoothly
    if (m.length === 1) {
      await sleep(300);
      pick(m[0], A);
    }
  };
});

function hop(t) {
  const inner = t.querySelector('.tk-inner') || t.firstChild;
  if (inner) {
    inner.animate([
      { transform: 'translateY(0) scale(1)' },
      { transform: 'translateY(-42%) scale(1.18)', offset: 0.5 },
      { transform: 'translateY(0) scale(1)' }
    ], { duration: 170 });
  }
}

// ----------------------------------------------------
// TOKEN PICK & STEP-BY-STEP SNAKE REWIND ON CAPTURE
// ----------------------------------------------------
async function pick(i, owner) {
  if (state !== 'move' || busy) return;
  const me = P[cur];
  const p = ctrl(me);
  if (owner && owner !== p) return;
  if (!p.el[i].classList.contains('go')) return;

  busy = true;
  p.el.forEach(t => t.classList.remove('go'));
  const t = p.el[i];
  t.style.zIndex = 15;

  // Move out of yard into start square
  if (p.pos[i] < 0) {
    p.pos[i] = 0;
    Snd.move();
    hop(t);
    place(p, i);
    layout();
    await sleep(240);
  } else {
    // Step forward tile-by-tile
    for (let s = 0; s < dice; s++) {
      p.pos[i]++;
      Snd.move();
      hop(t);
      place(p, i);
      layout();
      await sleep(180);
    }
  }

  t.style.zIndex = '';
  let extra = (dice === 6);
  const q = p.pos[i];

  // Capture check: only on main track (<=50), not safe tiles, not teammates
  if (q <= 50) {
    const abs = (START[p.color] + q) % 52;
    if (!SAFE.includes(abs)) {
      for (const o of P) {
        if (o === p || team(o.color) === team(p.color)) continue;
        for (let j = 0; j < 4; j++) {
          const oq = o.pos[j];
          if (oq >= 0 && oq <= 50 && (START[o.color] + oq) % 52 === abs) {
            // CAPTURE OCCURRED!
            extra = true;
            Snd.kill();
            t.classList.add('strike');
            setTimeout(() => t.classList.remove('strike'), 400);

            // Step-by-step SNAKE REWIND: token retreats through every box it arrived from!
            const victim = o.el[j];
            victim.style.zIndex = 14;
            victim.classList.add('retreating');

            for (let backPos = oq - 1; backPos >= 0; backPos--) {
              o.pos[j] = backPos;
              place(o, j);
              layout();
              Snd.retreatStep(backPos, oq); // Signature snake slither sound on each tile
              await sleep(38);
            }

            // Return to yard slot (-1)
            o.pos[j] = -1;
            place(o, j);
            layout();
            Snd.click(); // Soft drop back into yard slot
            victim.classList.remove('retreating');
            victim.style.zIndex = '';
            await sleep(140);
          }
        }
      }
    }
  }

  // Token reached home center finish!
  if (q === 56) {
    Snd.home();
    extra = true;
    t.classList.add('home-celebrate');
  }

  layout();
  saveGameState();
  await sleep(260);

  // Victory Conditions
  if (S.mode === 'team') {
    if (P.filter(o => team(o.color) === team(p.color)).every(fin)) {
      end(team(p.color));
      return;
    }
  } else if (S.mode === 'quick') {
    if (done(p)) {
      rank.push(p);
      end();
      return;
    }
  } else if (done(p) && !p.out) {
    p.out = true;
    rank.push(p);
    if (P.filter(o => !o.out).length <= 1) {
      end();
      return;
    }
  }

  busy = false;
  state = 'roll';
  saveGameState();

  if (extra && !me.out) {
    ui();
  } else {
    next();
  }
}

function next() {
  sixes = 0;
  do {
    cur = (cur + 1) % P.length;
  } while (P[cur].out);
  state = 'roll';
  ui();
}

const HEX = { red: '#e8212c', green: '#0c8f43', yellow: '#fdd414', blue: '#1ea3f2' };
const av = c => `<svg class="av" style="--k:${HEX[c]}"><use href="#pn"/></svg>`;
const row = (p, w, r) => `
  <div class="wr">
    ${r === undefined ? '' : r ? `<span class="cr r${r}">${r}</span>` : '<span class="cr none"></span>'}
    ${av(p.color)}
    <span class="wn">${p.name.replace(/</g, '&lt;')}</span>
    <b class="${w ? 'wnr' : 'los'}">${w ? '🏆 Winner' : 'Loser'}</b>
  </div>`;

function end(wt) {
  state = 'over';
  saveGameState();
  Snd.win();
  let h = '';
  if (S.mode === 'team') {
    [wt, 1 - wt].forEach(t => {
      h += `<div class="wt">Team ${t + 1}</div>` +
           P.filter(o => team(o.color) === t).map(o => row(o, t === wt)).join('');
    });
  } else {
    rank.forEach((p, k) => h += row(p, true, k + 1));
    P.filter(o => !rank.includes(o)).forEach(p => h += row(p, false, 0));
  }
  $('#wrows').innerHTML = h;
  $('#win').hidden = false;
  confetti();
}

function confetti() {
  const cv = document.createElement('canvas');
  cv.id = 'cf';
  document.body.appendChild(cv);
  const x = cv.getContext('2d');
  cv.width = innerWidth;
  cv.height = innerHeight;
  const cs = ['#e8212c', '#fdd414', '#1ea3f2', '#0c8f43', '#fff', '#ff9a1a'];
  const ps = Array.from({ length: 140 }, () => ({
    x: Math.random() * cv.width,
    y: -Math.random() * cv.height * 0.6,
    w: 5 + Math.random() * 6,
    h: 8 + Math.random() * 8,
    vy: 2 + Math.random() * 3.5,
    vx: (Math.random() - 0.5) * 2,
    r: Math.random() * 6,
    vr: (Math.random() - 0.5) * 0.3,
    c: cs[Math.random() * 6 | 0]
  }));
  let n = 0;
  (function f() {
    x.clearRect(0, 0, cv.width, cv.height);
    ps.forEach(p => {
      p.x += p.vx;
      p.y += p.vy;
      p.r += p.vr;
      if (p.y > cv.height) p.y = -10;
      x.save();
      x.translate(p.x, p.y);
      x.rotate(p.r);
      x.fillStyle = p.c;
      x.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
      x.restore();
    });
    if (++n < 420) requestAnimationFrame(f);
    else cv.remove();
  })();
}

// Controls & Buttons
const muteBtn = $('#mute');
if (muteBtn) {
  muteBtn.textContent = Snd.isMuted() ? '🔇' : '🔊';
  muteBtn.onclick = () => {
    const isM = Snd.toggle();
    muteBtn.textContent = isM ? '🔇' : '🔊';
    const ws = $('#wsnd');
    if (ws) ws.textContent = isM ? '🔇' : '🔊';
  };
}

const wsnd = $('#wsnd');
if (wsnd) {
  wsnd.textContent = Snd.isMuted() ? '🔇' : '🔊';
  wsnd.onclick = () => {
    const isM = Snd.toggle();
    wsnd.textContent = isM ? '🔇' : '🔊';
    if (muteBtn) muteBtn.textContent = isM ? '🔇' : '🔊';
  };
}

const wshare = $('#wshare');
if (wshare) {
  wshare.onclick = () => {
    const t = 'I just played Ludo King! Can you beat my score?';
    if (navigator.share) navigator.share({ text: t }).catch(() => {});
    else if (navigator.clipboard) navigator.clipboard.writeText(t);
  };
}

const replayBtn = $('#replay');
if (replayBtn) {
  replayBtn.onclick = () => {
    localStorage.removeItem(STORAGE_KEY);
    location.reload();
  };
}

const menuBtn = $('#menu');
const topMenuBtn = $('#top-menu-btn');
const resumeBtn = $('#resume');
if (menuBtn) menuBtn.onclick = () => { $('#mn').hidden = false; };
if (topMenuBtn) topMenuBtn.onclick = () => { $('#mn').hidden = false; };
if (resumeBtn) resumeBtn.onclick = () => { $('#mn').hidden = true; };

const topSoundBtn = $('#top-sound-btn');
if (topSoundBtn) {
  topSoundBtn.textContent = Snd.isMuted() ? '🔇' : '🔊';
  topSoundBtn.onclick = () => {
    const isM = Snd.toggle();
    topSoundBtn.textContent = isM ? '🔇' : '🔊';
    if (muteBtn) muteBtn.textContent = isM ? '🔇' : '🔊';
    if (wsnd) wsnd.textContent = isM ? '🔇' : '🔊';
  };
}

const quitBtn = $('#quit');
if (quitBtn) {
  quitBtn.onclick = () => {
    localStorage.removeItem(STORAGE_KEY);
    location.href = 'index.php';
  };
}

// Restore movable tokens highlight if refreshing during 'move' state
if (state === 'move') {
  const A = ctrl(P[cur]);
  const m = movable(A, dice);
  m.forEach(i => A.el[i].classList.add('go'));
}

ui();
if (!restored) {
  Snd.start();
}
