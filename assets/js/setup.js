const $ = s => document.querySelector(s);
const CHK = '<svg viewBox="0 0 40 34"><path d="M6 17 15 26 34 6" fill="none" stroke="#4a2c00" stroke-width="11" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 17 15 26 34 6" fill="none" stroke="#ffc400" stroke-width="6.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';

document.querySelectorAll('.rad').forEach(r => r.innerHTML = CHK);
const ORDER = ['blue', 'red', 'green', 'yellow'];
const SHOW = { 2: ['blue', 'green'], 3: ['blue', 'red', 'green'], 4: ORDER };
let TOKENS = [{ id: 'pin' }, { id: 'coin' }, { id: 'hammer', lock: 1 }, { id: 'spinner', lock: 1 }, { id: 'agent', lock: 1 }];
const THEMES = [['classic', 'Classic theme'], ['night', 'Night theme'], ['forest', 'Forest theme']];
let tok = 'pin', mode = 'classic', n = 4, th = 0, step = 1;

function toast(t) {
  const e = $('#toast');
  e.textContent = t;
  e.hidden = false;
  clearTimeout(toast.t);
  toast.t = setTimeout(() => e.hidden = true, 1300);
}

function drawToks(anim) {
  $('#toks').innerHTML = TOKENS.map((t, i) =>
    `<button class="tk1 ${t.lock ? 'lk' : ''}" data-id="${t.id}" style="left:${G_SLOTS[i] / 7.28}%">
      <img src="assets/img/ui/${t.id}.png" alt="">
      ${t.id === tok && !t.lock ? `<span class="bx" ${anim ? '' : 'style="animation:none"'}>${CHK}</span>` : ''}
    </button>`
  ).join('');

  document.querySelectorAll('.tk1').forEach(e => e.onclick = () => {
    Snd.click();
    if (e.classList.contains('lk')) {
      toast('This token is locked');
      return;
    }
    tok = e.dataset.id;
    drawToks(true);
  });
}

$('#tl').onclick = () => { Snd.click(); TOKENS.unshift(TOKENS.pop()); drawToks(); };
$('#tr').onclick = () => { Snd.click(); TOKENS.push(TOKENS.shift()); drawToks(); };
$('#theme').onclick = () => { Snd.click(); th = (th + 1) % THEMES.length; toast(THEMES[th][1]); };

document.querySelectorAll('.mrow').forEach(r => r.onclick = () => {
  Snd.click();
  mode = r.dataset.m;
  document.querySelectorAll('.rad').forEach(x => x.classList.remove('on'));
  const rad = r.querySelector('.rad');
  void rad.offsetWidth;
  rad.classList.add('on');
  const p = r.querySelector('.pill');
  p.classList.remove('press');
  void p.offsetWidth;
  p.classList.add('press');
  if (mode === 'team') n = 4;
});

$('#help').onclick = () => { Snd.click(); $('#hp').hidden = false; };
$('#hpx').onclick = () => { Snd.click(); $('#hp').hidden = true; };

function rows() {
  const old = {};
  document.querySelectorAll('#rows .row').forEach(r => old[r.dataset.c] = r.querySelector('input').value);
  $('#rows').innerHTML = ORDER.map((c, i) =>
    `<div class="row ${SHOW[n].includes(c) ? '' : 'off'}" data-c="${c}">
      <span class="pin ${c}"></span>
      <input maxlength="12" placeholder="Player ${i + 1}" value="${old[c] || ''}" ${SHOW[n].includes(c) ? '' : 'disabled'}>
    </div>`
  ).join('');
}

function counts() {
  document.querySelectorAll('#counts button').forEach(b => {
    b.classList.toggle('act', +b.dataset.n === n);
    b.disabled = (mode === 'team' && b.dataset.n !== '4');
  });
  rows();
}

document.querySelectorAll('#counts button').forEach(b => b.onclick = () => {
  Snd.click();
  n = +b.dataset.n;
  counts();
});

$('#next').onclick = () => {
  Snd.unlock();
  Snd.click();
  step = 2;
  $('#s1').hidden = true;
  $('#s2').hidden = false;
  counts();
};

$('#back').onclick = () => {
  Snd.click();
  if (step === 2) {
    step = 1;
    $('#s2').hidden = true;
    $('#s1').hidden = false;
  } else {
    location.href = 'index.php';
  }
};

// Resume Saved Game
const loadBtn = $('#load-saved-btn');
if (loadBtn) {
  loadBtn.onclick = () => {
    Snd.click();
    try {
      const saved = JSON.parse(localStorage.getItem('ludo_game_state') || 'null');
      if (saved && saved.session) {
        sessionStorage.setItem('ludo', JSON.stringify(saved.session));
        location.href = 'game.php';
      } else {
        toast('No saved game found!');
      }
    } catch (e) {
      toast('No saved game found!');
    }
  };
}

function start(one) {
  Snd.unlock();
  // Clear any existing stored game to start fresh
  localStorage.removeItem('ludo_game_state');
  const pl = [...document.querySelectorAll('#rows .row:not(.off)')].map(r => ({
    color: r.dataset.c,
    name: r.querySelector('input').value.trim() || r.querySelector('input').placeholder
  }));
  sessionStorage.setItem('ludo', JSON.stringify({
    players: pl,
    mode,
    oneOut: one,
    token: tok,
    theme: THEMES[th][0]
  }));
  location.href = 'game.php';
}

$('#play').onclick = () => start(false);
$('#play1').onclick = () => { Snd.click(); $('#tip').hidden = false; };
$('#tipx').onclick = () => { Snd.click(); $('#tip').hidden = true; };
$('#ok').onclick = () => start(true);

drawToks(false);
window.scrollTo(0, 0);
