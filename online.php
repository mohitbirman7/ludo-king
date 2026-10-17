<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
$v = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover,user-scalable=no">
  <title>Play with Friends - Ludo King</title>
  <link rel="icon" type="image/png" href="assets/icons/icon-192.png?v=<?=$v?>">
  <link href="https://fonts.googleapis.com/css2?family=Lilita+One&family=Roboto:wght@700;900&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; user-select: none; }
    html {
      margin: 0; padding: 0; width: 100%; height: 100%;
    }
    body {
      margin: 0; padding: 0; height: 100%; width: 100%; overflow: hidden;
      background: radial-gradient(circle at 50% 35%, #1852b4 0%, #0a2d75 55%, #05163e 100%);
      font-family: 'Roboto', system-ui, -apple-system, sans-serif; color: #ffffff;
      display: flex; justify-content: center; align-items: center;
    }
    body::before {
      content: ""; position: fixed; inset: 0; opacity: 0.15; pointer-events: none;
      background-image: radial-gradient(#ffffff 1px, transparent 1px);
      background-size: 32px 32px;
    }
    .stage {
      position: relative; width: 100%; max-width: 440px; height: 100dvh; max-height: 100dvh;
      display: flex; flex-direction: column; justify-content: space-between; align-items: center;
      padding-top: max(env(safe-area-inset-top, 0px), 32px);
      padding-bottom: max(env(safe-area-inset-bottom, 0px), 10px);
      padding-left: max(env(safe-area-inset-left, 0px), 10px);
      padding-right: max(env(safe-area-inset-right, 0px), 10px);
      z-index: 1;
    }
    /* Lobby Screen Card */
    #lobby-screen {
      position: relative; width: min(92vw, 390px); margin: auto;
      background: linear-gradient(180deg, #10449e 0%, #072b6a 100%);
      border: 3px solid #ffd700; border-radius: 20px;
      padding: 22px 18px 18px; text-align: center;
      box-shadow: 0 12px 35px rgba(0, 0, 0, 0.75), inset 0 1px 0 rgba(255, 255, 255, 0.3);
      z-index: 10;
    }
    .crown { font-size: 34px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6)); margin-bottom: 2px; }
    h1 {
      margin: 0 0 4px; font-size: 24px; font-weight: 900; color: #ffd700;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
    }
    p.sub { margin: 0 0 16px; font-size: 12.5px; color: #c0d5ff; }

    /* Navigation Tabs: Create Room / Join Room */
    .tab-bar {
      display: flex; gap: 8px; margin-bottom: 16px; background: rgba(0, 15, 50, 0.45);
      padding: 4px; border-radius: 14px; border: 1.5px solid #3c6dbd;
    }
    .tab-btn {
      flex: 1; padding: 10px 4px; border: none; border-radius: 10px;
      font: 900 14px 'Roboto', sans-serif; cursor: pointer; color: #a4c2f4;
      background: transparent; transition: all 0.2s ease;
    }
    .tab-btn.active {
      background: linear-gradient(180deg, #ffb300, #ff6f00);
      color: #ffffff; box-shadow: 0 3px 8px rgba(255, 111, 0, 0.5);
    }

    .form-group { text-align: left; margin-bottom: 12px; }
    label { display: block; font-size: 11.5px; font-weight: 900; color: #ffd700; margin-bottom: 4px; text-shadow: 0 1px 2px rgba(0,0,0,0.8); }
    input {
      width: 100%; padding: 11px 13px; border-radius: 10px;
      border: 2px solid #3c6dbd; background: #082153; color: #ffffff;
      font-size: 15px; outline: none; transition: border-color 0.2s;
    }
    input:focus { border-color: #ffd700; }
    .code-input {
      font-size: 24px; font-weight: 900; letter-spacing: 6px; text-align: center; color: #ffd700;
    }

    /* Player Count Options (2, 3, 4) */
    .counts { display: flex; gap: 8px; margin-bottom: 14px; }
    .c-btn {
      flex: 1; padding: 10px 0; border: 2px solid #3c6dbd; border-radius: 10px;
      background: #082153; color: #ffffff; font-weight: 900; font-size: 14px; cursor: pointer;
    }
    .c-btn.active {
      border-color: #ffd700; background: linear-gradient(180deg, #1852b4, #0c2d6e);
      box-shadow: 0 0 10px rgba(255, 215, 0, 0.4); color: #ffd700;
    }

    .btn-gold {
      width: 100%; padding: 12px; border-radius: 25px; border: 2.5px solid #ffffff;
      background: linear-gradient(180deg, #ffb300, #ff6f00); color: #ffffff;
      font: 900 15px 'Roboto', sans-serif; cursor: pointer;
      box-shadow: 0 5px 15px rgba(255, 111, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.4);
    }
    .btn-gold:active { transform: scale(0.97); }

    /* Waiting Lobby Display */
    .room-badge {
      background: rgba(0, 0, 0, 0.4); border: 2px dashed #ffd700;
      border-radius: 14px; padding: 12px; margin-bottom: 14px;
    }
    .room-code-label { font-size: 11px; color: #aac4ff; font-weight: 700; }
    .room-code-val { font-size: 32px; font-weight: 900; color: #ffd700; letter-spacing: 4px; margin: 4px 0; }
    .copy-btn {
      background: #1852b4; border: 1.5px solid #ffd700; color: #ffffff;
      padding: 5px 12px; border-radius: 15px; font-size: 11.5px; font-weight: 900; cursor: pointer;
    }

    /* Player Roster */
    .player-roster {
      text-align: left; background: rgba(0, 15, 50, 0.5); border-radius: 12px;
      padding: 10px 12px; margin-bottom: 14px; border: 1px solid rgba(255, 215, 0, 0.3);
      max-height: 160px; overflow-y: auto;
    }
    .roster-item {
      display: flex; justify-content: space-between; align-items: center;
      padding: 7px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .roster-item:last-child { border-bottom: none; }
    .r-name { font-weight: 700; font-size: 13.5px; }
    .r-badge {
      font-size: 10.5px; font-weight: 900; padding: 2px 7px; border-radius: 10px;
    }
    .r-badge.accepted { background: #2e7d32; color: #ffffff; }
    .r-badge.pending { background: #f57f17; color: #ffffff; }
    .btn-accept {
      background: #2e7d32; border: 1.5px solid #81c784; color: #ffffff;
      padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 900; cursor: pointer;
    }

    /* Top Game Header & Board Layout */
    #game-screen { display: none; width: 100%; height: 100%; flex-direction: column; justify-content: space-between; align-items: center; }
    .top-bar {
      display: flex; justify-content: space-between; align-items: center;
      width: min(92vw, 48vh, 390px); padding: 4px 2px; margin: 0 auto 4px;
    }
    .room-pill {
      background: rgba(0, 20, 60, 0.7); border: 1.5px solid #ffd700; border-radius: 12px;
      padding: 4px 12px; font-size: 12px; font-weight: 900; color: #ffd700; letter-spacing: 1px;
    }
    .btn-icon {
      width: clamp(34px, 4.8vh, 38px); height: clamp(34px, 4.8vh, 38px); border-radius: 50%; border: 1.5px solid #ffd700;
      background: linear-gradient(180deg, #1852b4, #0c2d6e); color: #ffffff;
      font-size: clamp(14px, 2.2vh, 17px); cursor: pointer; display: flex; align-items: center; justify-content: center;
      box-shadow: 0 3px 6px rgba(0, 0, 0, 0.4);
    }
    .btn-icon:active { transform: scale(0.93); }

    /* Player Cards & Board Styles */
    .rowS {
      display: flex; justify-content: space-between; align-items: center;
      width: min(92vw, 48vh, 390px); margin: 0 auto; padding: 2px 0;
    }
    .slot {
      position: relative; display: flex; align-items: center; justify-content: space-between;
      width: 44%; height: clamp(52px, 7.8vh, 64px); padding: 4px 6px;
      background: linear-gradient(180deg, #0e3d93 0%, #06235b 100%);
      border: 2.5px solid #ffcc00; border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.45); z-index: 5;
    }
    .slot.r { flex-direction: row-reverse; }
    .pb {
      position: relative; height: 100%; display: flex; align-items: center; gap: 6px; padding: 0;
      background: transparent; border: none; box-shadow: none; flex: 1; min-width: 0;
    }
    .slot.r .pb { flex-direction: row-reverse; text-align: right; }
    .av-wrap { position: relative; width: 30px; height: 36px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    .av-svg { width: 24px; height: 32px; filter: drop-shadow(0 2px 2px rgba(0,0,0,0.4)); }
    .p-info { display: flex; flex-direction: column; overflow: hidden; flex: 1; min-width: 0; }
    .pn {
      font-size: clamp(10px, 2.6vw, 12px); font-weight: 900; color: #ffffff;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .db {
      position: relative; width: clamp(42px, 6.2vh, 50px); height: clamp(42px, 6.2vh, 50px);
      background: radial-gradient(circle at 45% 45%, #ffffff 0%, #f6ece0 65%, #ded0bd 100%);
      border: 2px solid #bba38a; border-radius: 10px; display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.35); cursor: pointer; flex-shrink: 0;
    }
    .dots-timer { position: absolute; bottom: 2px; left: 0; right: 0; display: flex; justify-content: center; gap: 2px; }
    .dots-timer i { display: block; width: 3.5px; height: 3.5px; border-radius: 50%; background: #998a77; }
    .slot.act .dots-timer i { background: #22c55e; box-shadow: 0 0 4px #22c55e; }
    .slot.act { border-color: #ffe600; box-shadow: 0 0 14px rgba(255, 230, 0, 0.8); }
    .slot.act .db { border-color: #ffd700; box-shadow: 0 0 12px #ffe600; }
    .slot.act.my-turn .db::before {
      content: ""; position: absolute; top: -23px; left: 50%; transform: translateX(-50%);
      width: 18px; height: 21px; background: linear-gradient(180deg, #ffd700, #ff8c00);
      clip-path: polygon(30% 0%, 70% 0%, 70% 55%, 100% 55%, 50% 100%, 0% 55%, 30% 55%);
      filter: drop-shadow(0 2px 3px rgba(0,0,0,0.7)); animation: promptBounceDown 0.6s infinite alternate ease-in-out;
      pointer-events: none; z-index: 25;
    }
    @keyframes promptBounceDown { 0% { transform: translate(-50%, 0); } 100% { transform: translate(-50%, -6px); } }

    /* 3D Dice Cube Styles */
    .die { width: clamp(34px, 5vh, 42px); height: clamp(34px, 5vh, 42px); position: relative; visibility: hidden; perspective: 260px; isolation: isolate; }
    .die.show { visibility: visible; }
    .die.big { z-index: 30; will-change: transform; }
    .cube { position: absolute; inset: 0; transform-style: preserve-3d; will-change: transform; }
    .f {
      position: absolute; inset: 0; border-radius: 20%; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(3, 1fr);
      padding: 10%; backface-visibility: hidden; background: linear-gradient(145deg, #ffffff 0%, #f4eee2 55%, #dfd5be 100%);
      border: 1px solid rgba(0, 0, 0, 0.15); box-shadow: inset 0 -3px 0 rgba(0, 0, 0, 0.15), inset 0 2px 2px rgba(255, 255, 255, 0.95);
    }
    .f1 { transform: translateZ(21px); }
    .f6 { transform: rotateY(180deg) translateZ(21px); }
    .f2 { transform: rotateY(90deg) translateZ(21px); }
    .f5 { transform: rotateY(-90deg) translateZ(21px); }
    .f3 { transform: rotateX(90deg) translateZ(21px); }
    .f4 { transform: rotateX(-90deg) translateZ(21px); }
    .f i { width: 82%; height: 82%; margin: auto; border-radius: 50%; }
    .f i.on { background: radial-gradient(circle at 35% 35%, #424242 0%, #151515 85%); box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.9), 0 1px 1px rgba(255, 255, 255, 0.3); }

    /* Central Board */
    #board-container { width: 100%; display: flex; justify-content: center; align-items: center; margin: auto 0; padding: 2px 0; }
    #board {
      position: relative; width: min(92vw, 48vh, 390px); height: min(92vw, 48vh, 390px); aspect-ratio: 1 / 1;
      display: grid; grid-template-columns: repeat(15, minmax(0, 1fr)); grid-template-rows: repeat(15, minmax(0, 1fr));
      background: #ffffff; border: 3.5px solid #d4a72c; border-radius: 8px;
      box-shadow: 0 0 0 2px #5a3c08, 0 8px 24px rgba(0, 0, 0, 0.65);
      overflow: visible;
    }
    .base { position: relative; display: flex; align-items: center; justify-content: center; min-width: 0; min-height: 0; }
    .base.red { background: linear-gradient(135deg, #e71d36, #b80c23); border-top-left-radius: 5px; }
    .base.green { background: linear-gradient(135deg, #00b050, #007a36); border-top-right-radius: 5px; }
    .base.yellow { background: linear-gradient(135deg, #ffc000, #d49500); border-bottom-right-radius: 5px; }
    .base.blue { background: linear-gradient(135deg, #0088ff, #005bb5); border-bottom-left-radius: 5px; }
    .base .inner { width: 66.6%; height: 66.6%; background: #ffffff; border-radius: 8px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); }
    .sl {
      position: absolute; width: 6.67%; height: 6.67%; border-radius: 50%; border: 2px solid var(--k);
      background: radial-gradient(circle, #ffffff 0%, #ffffff 32%, var(--k) 35%, var(--k) 100%);
    }
    .sl.red { --k: #e71d36; } .sl.green { --k: #00b050; } .sl.yellow { --k: #ffc000; } .sl.blue { --k: #0088ff; }
    .c { background: #ffffff; border: 0.5px solid #d0d7de; display: flex; align-items: center; justify-content: center; position: relative; min-width: 0; min-height: 0; }
    .c.red { background: linear-gradient(135deg, #e71d36, #c51128); }
    .c.green { background: linear-gradient(135deg, #00b050, #00883d); }
    .c.yellow { background: linear-gradient(135deg, #ffc000, #e0a300); }
    .c.blue { background: linear-gradient(135deg, #0088ff, #006bce); }
    .star-icon { font-size: clamp(14px, 3.8vw, 19px); color: #8c9ba5; font-weight: 900; }
    .ctr {
      grid-area: 7 / 7 / 10 / 10;
      background: conic-gradient(from -45deg, #00b050 0 25%, #ffc000 0 50%, #0088ff 0 75%, #e71d36 0);
      display: flex; align-items: center; justify-content: center;
    }
    .ctr-crown { font-size: clamp(18px, 4.6vw, 26px); filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6)); }

    /* Token styling */
    .tk {
      position: absolute; width: 6.67%; height: 6.67%; z-index: 10; display: flex; align-items: center;
      justify-content: center; pointer-events: none; transition: left 0.16s linear, top 0.16s linear;
      will-change: left, top, transform;
    }
    .tk-inner { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
    .tk svg { width: 95%; height: 125%; margin-top: -28%; filter: drop-shadow(0 3px 3px rgba(0,0,0,0.55)); transform-origin: 50% 90%; overflow: visible; }
    .tk.red { --k: #e71d36; } .tk.green { --k: #00b050; } .tk.yellow { --k: #ffc000; } .tk.blue { --k: #0088ff; }
    .tk.go { z-index: 16; pointer-events: auto; cursor: pointer; }
    .tk.go .tk-inner { animation: tokenHop 0.45s infinite alternate cubic-bezier(0.2, 0.8, 0.4, 1); filter: drop-shadow(0 0 6px #ffffff) drop-shadow(0 0 12px #ffe600); }
    @keyframes tokenHop { 0% { transform: translateY(0); } 100% { transform: translateY(-32%); } }

    .foot-bar {
      width: min(92vw, 48vh, 390px); padding: 8px 12px; background: rgba(0, 15, 50, 0.7);
      border: 1.5px solid #ffd700; border-radius: 12px; text-align: center;
      font-size: 13px; font-weight: 900; color: #ffd700;
    }
  </style>
</head>
<body>
  <!-- Token Pin SVG Definition -->
  <svg width="0" height="0" style="position:absolute">
    <defs>
      <symbol id="pn" viewBox="0 0 40 52">
        <path d="M20 3C10 3 4 10 4 18c0 10 11 20 16 31 5-11 16-21 16-31C36 10 30 3 20 3z" fill="#f0f3f6" stroke="#4a5568" stroke-width="1.6"/>
        <circle cx="20" cy="18" r="8.5" fill="var(--k)" stroke="#ffffff" stroke-width="2"/>
        <circle cx="17.5" cy="15.5" r="2.5" fill="#ffffff" opacity="0.6"/>
      </symbol>
    </defs>
  </svg>

  <div class="stage">
    <!-- 1. LOBBY SCREEN (CREATE OR JOIN ROOM) -->
    <div id="lobby-screen">
      <div class="crown">👑</div>
      <h1>Play With Friends</h1>
      <p class="sub">Create a room or join your friends with a 6-digit code!</p>

      <div class="tab-bar">
        <button class="tab-btn active" id="tab-create" onclick="switchTab('create')">Create Room</button>
        <button class="tab-btn" id="tab-join" onclick="switchTab('join')">Join Room</button>
      </div>

      <!-- CREATE ROOM SECTION -->
      <div id="sec-create">
        <div class="form-group">
          <label>Your Name</label>
          <input id="c-name" value="Player 1" placeholder="Enter your name" maxlength="12">
        </div>
        <div class="form-group">
          <label>Select Players</label>
          <div class="counts">
            <button class="c-btn" onclick="selectCount(2, this)">2 Players</button>
            <button class="c-btn" onclick="selectCount(3, this)">3 Players</button>
            <button class="c-btn active" onclick="selectCount(4, this)">4 Players</button>
          </div>
        </div>
        <button class="btn-gold" onclick="doCreateRoom()">Generate Room Code</button>
      </div>

      <!-- JOIN ROOM SECTION -->
      <div id="sec-join" style="display:none;">
        <div class="form-group">
          <label>Your Name</label>
          <input id="j-name" value="Friend" placeholder="Enter your name" maxlength="12">
        </div>
        <div class="form-group">
          <label>6-Digit Room Code</label>
          <input id="j-code" class="code-input" placeholder="000000" maxlength="6" pattern="[0-9]*">
        </div>
        <button class="btn-gold" onclick="doJoinRoom()">Send Join Request</button>
      </div>

      <!-- LOBBY WAITING ROOM (After creating or requesting) -->
      <div id="sec-waiting" style="display:none;">
        <div class="room-badge">
          <div class="room-code-label">ROOM CODE</div>
          <div class="room-code-val" id="disp-room-code">------</div>
          <button class="copy-btn" onclick="copyCode()">📋 Copy Code</button>
        </div>

        <label style="text-align:left;">Players in Lobby (<span id="player-count">1</span>)</label>
        <div class="player-roster" id="roster-list">
          <!-- Filled dynamically -->
        </div>

        <div id="host-controls" style="display:none;">
          <button class="btn-gold" id="btn-start-game" onclick="doStartGame()">🚀 Start Online Game</button>
        </div>
        <div id="guest-controls" style="display:none;">
          <p id="guest-status-msg" style="font-size:13px;color:#ffd700;font-weight:700;">Waiting for Host to accept request...</p>
        </div>
      </div>

      <div style="margin-top:16px;">
        <a href="index.php" style="color:#aac4ff;font-size:13px;text-decoration:none;font-weight:700;">← Back to Home</a>
      </div>
    </div>

    <!-- 2. LIVE ONLINE GAME SCREEN -->
    <div id="game-screen">
      <div class="top-bar">
        <button class="btn-icon" onclick="location.href='index.php'" title="Exit Game">🚪</button>
        <div class="room-pill">ROOM: <span id="game-room-code">------</span></div>
        <button class="btn-icon" id="btn-mute" onclick="toggleMute()" title="Mute">🔊</button>
      </div>

      <!-- Top Player Cards -->
      <div class="rowS">
        <div class="slot l" id="slot-red" data-c="red">
          <div class="pb">
            <div class="av-wrap"><svg class="av-svg" style="--k:#e8212c"><use href="#pn"/></svg></div>
            <div class="p-info"><span class="pn" id="name-red">Waiting...</span></div>
          </div>
          <div class="db" title="Roll Dice"><div class="die show" id="die-red"></div><div class="dots-timer"><i></i><i></i><i></i><i></i><i></i></div></div>
        </div>
        <div class="slot r" id="slot-green" data-c="green">
          <div class="pb">
            <div class="av-wrap"><svg class="av-svg" style="--k:#0c8f43"><use href="#pn"/></svg></div>
            <div class="p-info"><span class="pn" id="name-green">Waiting...</span></div>
          </div>
          <div class="db" title="Roll Dice"><div class="die show" id="die-green"></div><div class="dots-timer"><i></i><i></i><i></i><i></i><i></i></div></div>
        </div>
      </div>

      <!-- Board -->
      <div id="board-container"><div id="board"></div></div>

      <!-- Bottom Player Cards -->
      <div class="rowS">
        <div class="slot l" id="slot-blue" data-c="blue">
          <div class="pb">
            <div class="av-wrap"><svg class="av-svg" style="--k:#1ea3f2"><use href="#pn"/></svg></div>
            <div class="p-info"><span class="pn" id="name-blue">Waiting...</span></div>
          </div>
          <div class="db" title="Roll Dice"><div class="die show" id="die-blue"></div><div class="dots-timer"><i></i><i></i><i></i><i></i><i></i></div></div>
        </div>
        <div class="slot r" id="slot-yellow" data-c="yellow">
          <div class="pb">
            <div class="av-wrap"><svg class="av-svg" style="--k:#fdd414"><use href="#pn"/></svg></div>
            <div class="p-info"><span class="pn" id="name-yellow">Waiting...</span></div>
          </div>
          <div class="db" title="Roll Dice"><div class="die show" id="die-yellow"></div><div class="dots-timer"><i></i><i></i><i></i><i></i><i></i></div></div>
        </div>
      </div>

      <!-- Turn & Status Bar -->
      <div class="foot-bar" id="game-status-bar">Connecting to room...</div>
    </div>
  </div>

  <script src="assets/js/security.js?v=<?=$v?>"></script>
  <script src="assets/js/sound.js?v=<?=$v?>"></script>
  <script>
    let myCode = '';
    let myToken = '';
    let myColor = '';
    let isHost = false;
    let selectedPlayerCount = 4;
    let pollInterval = null;
    let boardBuilt = false;
    let lastDiceVal = 0;
    let currentTurnColor = 'blue';

    function switchTab(t) {
      Snd.click();
      document.getElementById('sec-create').style.display = t === 'create' ? 'block' : 'none';
      document.getElementById('sec-join').style.display = t === 'join' ? 'block' : 'none';
      document.getElementById('tab-create').classList.toggle('active', t === 'create');
      document.getElementById('tab-join').classList.toggle('active', t === 'join');
    }

    function selectCount(n, el) {
      Snd.click();
      selectedPlayerCount = n;
      document.querySelectorAll('.c-btn').forEach(b => b.classList.remove('active'));
      el.classList.add('active');
    }

    async function doCreateRoom() {
      Snd.unlock();
      Snd.click();
      const name = document.getElementById('c-name').value.trim() || 'Host';
      const fd = new FormData();
      fd.append('action', 'create_room');
      fd.append('name', name);
      fd.append('players', selectedPlayerCount);

      const res = await fetch('api/online.php', { method: 'POST', body: fd }).then(r => r.json());
      if (res.ok) {
        myCode = res.room_code;
        myToken = res.token;
        myColor = res.color;
        isHost = true;
        showWaitingLobby();
      } else {
        alert(res.error || 'Failed to create room.');
      }
    }

    async function doJoinRoom() {
      Snd.unlock();
      Snd.click();
      const name = document.getElementById('j-name').value.trim() || 'Friend';
      const code = document.getElementById('j-code').value.trim();
      if (code.length !== 6) {
        alert('Please enter a valid 6-digit room code.');
        return;
      }

      const fd = new FormData();
      fd.append('action', 'join_room');
      fd.append('room_code', code);
      fd.append('name', name);

      const res = await fetch('api/online.php', { method: 'POST', body: fd }).then(r => r.json());
      if (res.ok) {
        myCode = res.room_code;
        myToken = res.token;
        myColor = res.color;
        isHost = false;
        showWaitingLobby();
      } else {
        alert(res.error || 'Failed to join room.');
      }
    }

    function showWaitingLobby() {
      document.getElementById('sec-create').style.display = 'none';
      document.getElementById('sec-join').style.display = 'none';
      document.querySelector('.tab-bar').style.display = 'none';
      document.getElementById('sec-waiting').style.display = 'block';
      document.getElementById('disp-room-code').textContent = myCode;
      document.getElementById('game-room-code').textContent = myCode;

      if (isHost) {
        document.getElementById('host-controls').style.display = 'block';
      } else {
        document.getElementById('guest-controls').style.display = 'block';
      }

      pollLobby();
      pollInterval = setInterval(pollLobby, 800);
    }

    function copyCode() {
      Snd.click();
      navigator.clipboard.writeText(myCode);
      alert('Room code ' + myCode + ' copied to clipboard!');
    }

    async function pollLobby() {
      try {
        const res = await fetch(`api/online.php?action=room_status&code=${myCode}&token=${myToken}`).then(r => r.json());
        if (!res.ok) return;

        if (res.room.status === 'active') {
          clearInterval(pollInterval);
          launchOnlineGame();
          return;
        }

        const rosterEl = document.getElementById('roster-list');
        rosterEl.innerHTML = '';
        let acceptedCnt = 0;

        res.players.forEach(p => {
          if (p.status === 'accepted') acceptedCnt++;
          const d = document.createElement('div');
          d.className = 'roster-item';
          let rightHtml = '';
          if (p.status === 'accepted') {
            rightHtml = `<span class="r-badge accepted">✓ Ready (${p.color.toUpperCase()})</span>`;
          } else {
            if (isHost) {
              rightHtml = `<button class="btn-accept" onclick="acceptGuest(${p.id})">Accept Request</button>`;
            } else {
              rightHtml = `<span class="r-badge pending">Pending Host</span>`;
            }
          }
          d.innerHTML = `<span class="r-name">${escapeHtml(p.player_name)}</span> ${rightHtml}`;
          rosterEl.appendChild(d);
        });

        document.getElementById('player-count').textContent = `${acceptedCnt} / ${res.room.max_players}`;

        if (!isHost) {
          const gMsg = document.getElementById('guest-status-msg');
          if (res.my_status === 'accepted') {
            gMsg.textContent = '🎉 Host accepted your request! Waiting for host to start game...';
            gMsg.style.color = '#4caf50';
          } else {
            gMsg.textContent = '⏳ Waiting for Host to accept your request...';
            gMsg.style.color = '#ffd700';
          }
        }
      } catch (e) {}
    }

    async function acceptGuest(playerId) {
      Snd.click();
      const fd = new FormData();
      fd.append('action', 'accept_player');
      fd.append('room_code', myCode);
      fd.append('token', myToken);
      fd.append('player_id', playerId);
      await fetch('api/online.php', { method: 'POST', body: fd });
      pollLobby();
    }

    async function doStartGame() {
      Snd.click();
      const fd = new FormData();
      fd.append('action', 'start_game');
      fd.append('room_code', myCode);
      fd.append('token', myToken);
      const res = await fetch('api/online.php', { method: 'POST', body: fd }).then(r => r.json());
      if (res.ok) {
        clearInterval(pollInterval);
        launchOnlineGame();
      } else {
        alert(res.error || 'Cannot start game.');
      }
    }

    // ----------------------------------------------------
    // IN-GAME BOARD LOGIC
    // ----------------------------------------------------
    function launchOnlineGame() {
      document.getElementById('lobby-screen').style.display = 'none';
      const gs = document.getElementById('game-screen');
      gs.style.display = 'flex';
      buildOnlineBoard();
      Snd.start();
      pollGameState();
      pollInterval = setInterval(pollGameState, 700);
    }

    const COL = ['red', 'green', 'yellow', 'blue'];
    const PATH = [
      [6,1],[6,2],[6,3],[6,4],[6,5],[5,6],[4,6],[3,6],[2,6],[1,6],[0,6],[0,7],[0,8],
      [1,8],[2,8],[3,8],[4,8],[5,8],[6,9],[6,10],[6,11],[6,12],[6,13],[6,14],[7,14],
      [8,14],[8,13],[8,12],[8,11],[8,10],[8,9],[9,8],[10,8],[11,8],[12,8],[13,8],[14,8],
      [14,7],[14,6],[13,6],[12,6],[11,6],[10,6],[9,6],[8,5],[8,4],[8,3],[8,2],[8,1],[8,0],[7,0],[6,0]
    ];
    const LANE = {
      red: [[7,1],[7,2],[7,3],[7,4],[7,5]],
      green: [[1,7],[2,7],[3,7],[4,7],[5,7]],
      yellow: [[7,13],[7,12],[7,11],[7,10],[7,9]],
      blue: [[13,7],[12,7],[11,7],[10,7],[9,7]]
    };
    const OFF = { red: [0,0], green: [0,9], yellow: [9,9], blue: [9,0] };
    const SP = [[1.7, 1.7], [1.7, 3.3], [3.3, 1.7], [3.3, 3.3]];
    const BASE_YARDS = {};
    COL.forEach(c => BASE_YARDS[c] = SP.map(([r, k]) => [r + OFF[c][0], k + OFF[c][1]]));

    function buildOnlineBoard() {
      if (boardBuilt) return;
      boardBuilt = true;
      const b = document.getElementById('board');
      b.innerHTML = '';

      // Bases
      const AREA = { red: '1/1/7/7', green: '1/10/7/16', yellow: '10/10/16/16', blue: '10/1/16/7' };
      COL.forEach(c => {
        const bs = document.createElement('div');
        bs.className = `base ${c}`;
        bs.style.gridArea = AREA[c];
        bs.innerHTML = '<div class="inner"></div>';
        b.appendChild(bs);
      });

      // Grid Cells
      const lk = {};
      COL.forEach(c => LANE[c].forEach(p => lk[p] = c));
      const START_T = { red: '6,1', green: '1,8', yellow: '8,13', blue: '13,6' };
      const STARS = [[8,2],[2,6],[6,12],[12,8]];

      for (let r = 0; r < 15; r++) {
        for (let k = 0; k < 15; k++) {
          if ((r<6&&k<6)||(r<6&&k>8)||(r>8&&k>8)||(r>8&&k<6)||(r>=6&&r<=8&&k>=6&&k<=8)) continue;
          const cell = document.createElement('div');
          cell.className = 'c';
          cell.style.gridArea = `${r+1}/${k+1}`;
          const key = `${r},${k}`;
          if (lk[key]) cell.classList.add(lk[key]);
          Object.entries(START_T).forEach(([c, st]) => { if (st === key) cell.classList.add(c); });
          if (STARS.some(([sr, sk]) => sr === r && sk === k)) cell.innerHTML = '<span class="star-icon">★</span>';
          b.appendChild(cell);
        }
      }

      // Center
      const ct = document.createElement('div');
      ct.className = 'ctr';
      ct.innerHTML = '<div class="ctr-crown">👑</div>';
      b.appendChild(ct);

      // Yard Slots
      COL.forEach(c => BASE_YARDS[c].forEach(([r, k]) => {
        const s = document.createElement('div');
        s.className = `sl ${c}`;
        s.style.left = (k * 100 / 15) + '%';
        s.style.top = (r * 100 / 15) + '%';
        b.appendChild(s);
      }));

      // Initialize 3D dice cubes in each slot
      COL.forEach(c => {
        const dEl = document.getElementById(`die-${c}`);
        if (dEl) face(dEl, 1);
      });

      // Bind Dice click for my turn
      document.querySelectorAll('.slot').forEach(s => {
        s.querySelector('.db').onclick = () => {
          if (s.dataset.c === myColor && currentTurnColor === myColor) {
            handleDiceRoll();
          }
        };
      });
    }

    // 3D Dice Helper Functions
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
      if (!el) return;
      const [x, y] = ORI[v] || [0, 0];
      cube(el).style.transform = `rotateX(${x}deg) rotateY(${y}deg)`;
      el.classList.add('show');
    }

    async function tumble(el, down, d) {
      if (!el) return;
      el.classList.add('big');
      el.classList.add('show');
      const c = cube(el);
      const s = down ? 1 : -1;
      const [fx, fy] = ORI[d] || [0, 0];

      c.style.transform = `rotateX(${fx}deg) rotateY(${fy}deg)`;

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

    let isRolling = false;
    let lastAnimatedRoll = { red: 0, green: 0, yellow: 0, blue: 0 };

    async function handleDiceRoll() {
      if (isRolling || currentTurnColor !== myColor) return;
      isRolling = true;
      Snd.unlock();
      Snd.dice();

      const el = document.getElementById(`die-${myColor}`);
      const isTopRow = (myColor === 'red' || myColor === 'green');

      const fd = new FormData();
      fd.append('action', 'roll_dice');
      fd.append('room_code', myCode);
      fd.append('token', myToken);
      fd.append('color', myColor);

      try {
        const res = await fetch('api/online.php', { method: 'POST', body: fd }).then(r => r.json());
        if (res.ok) {
          const d = +res.dice;
          lastAnimatedRoll[myColor] = d;
          await tumble(el, isTopRow, d);
          face(el, d);
          pollGameState();
        } else {
          alert(res.error || 'Cannot roll dice.');
        }
      } catch (e) {}
      isRolling = false;
    }

    async function handleTokenClick(tokenIndex) {
      Snd.move();
      const fd = new FormData();
      fd.append('action', 'move_token');
      fd.append('room_code', myCode);
      fd.append('token', myToken);
      fd.append('token_index', tokenIndex);

      const res = await fetch('api/online.php', { method: 'POST', body: fd }).then(r => r.json());
      if (res.ok) {
        if (res.kill) {
          Snd.kill(); // Authentic snake hiss!
        }
        pollGameState();
      } else {
        alert(res.error || 'Cannot move token.');
      }
    }

    async function pollGameState() {
      try {
        const res = await fetch(`api/online.php?action=game_state&code=${myCode}`).then(r => r.json());
        if (!res.ok) return;

        currentTurnColor = res.room.turn_color;
        const diceVal = parseInt(res.room.dice_value) || 0;
        const diceState = res.room.dice_state;

        // Update player names
        res.players.forEach(p => {
          const el = document.getElementById(`name-${p.color}`);
          if (el) el.textContent = p.player_name;
        });

        // Update active slot highlighting
        COL.forEach(c => {
          const s = document.getElementById(`slot-${c}`);
          if (!s) return;
          const isTurn = (c === currentTurnColor);
          s.classList.toggle('act', isTurn);
          s.classList.toggle('my-turn', isTurn && (c === myColor) && (diceState === 'roll'));
        });

        // 3D Dice Roll Animation sync across all screens
        if (diceVal > 0 && diceState === 'move') {
          if (lastAnimatedRoll[currentTurnColor] !== diceVal) {
            lastAnimatedRoll[currentTurnColor] = diceVal;
            const turnDie = document.getElementById(`die-${currentTurnColor}`);
            if (turnDie) {
              if (currentTurnColor !== myColor) {
                Snd.dice();
                const isTop = (currentTurnColor === 'red' || currentTurnColor === 'green');
                tumble(turnDie, isTop, diceVal);
              } else {
                face(turnDie, diceVal);
              }
            }
          }
        } else if (diceState === 'roll') {
          lastAnimatedRoll[currentTurnColor] = 0;
        }

        // Update status bar
        const statusEl = document.getElementById('game-status-bar');
        if (res.room.status === 'finished') {
          statusEl.textContent = `🏆 ${res.room.winner.toUpperCase()} WON THE GAME!`;
          statusEl.style.color = '#ffd700';
          clearInterval(pollInterval);
          return;
        }

        if (currentTurnColor === myColor) {
          if (diceState === 'roll') {
            statusEl.textContent = '👉 YOUR TURN: Tap Dice to Roll!';
          } else {
            statusEl.textContent = `👉 Rolled a ${diceVal}! Tap glowing goti to move!`;
          }
          statusEl.style.color = '#22c55e';
        } else {
          statusEl.textContent = `Waiting for ${currentTurnColor.toUpperCase()} to ${diceState === 'roll' ? 'roll dice' : 'move'}...`;
          statusEl.style.color = '#ffd700';
        }

        // Render Tokens on Board
        renderOnlineTokens(res.tokens, diceVal, diceState);
      } catch (e) {}
    }

    const HC = { red: [7, 6.4], green: [6.4, 7], yellow: [7, 7.6], blue: [7.6, 7] };
    const START_OFFSET = { red: 0, green: 13, yellow: 26, blue: 39 };

    function renderOnlineTokens(tokens, diceVal, diceState) {
      const b = document.getElementById('board');
      document.querySelectorAll('.tk').forEach(t => t.remove());

      const activeTokens = [];

      tokens.forEach(tk => {
        const c = tk.color;
        const idx = tk.token_index;
        const pos = parseInt(tk.pos);

        let [r, k] = [-1, -1];
        if (pos === -1) {
          [r, k] = BASE_YARDS[c][idx];
        } else if (pos >= 0 && pos <= 50) {
          const abs = (START_OFFSET[c] + pos) % 52;
          [r, k] = PATH[abs];
        } else if (pos >= 51 && pos <= 55) {
          [r, k] = LANE[c][pos - 51];
        } else if (pos === 56) {
          [r, k] = HC[c];
        }

        if (r === -1) return;

        const el = document.createElement('div');
        el.className = `tk ${c}`;
        el.style.left = (k * 100 / 15) + '%';
        el.style.top = (r * 100 / 15) + '%';
        el.innerHTML = `<div class="tk-inner"><svg viewBox="0 0 40 52"><use href="#pn"/></svg></div>`;

        // Movable if my turn, dice rolled, and token can legally move
        const isMyTurn = (c === myColor && currentTurnColor === myColor && diceState === 'move');
        if (isMyTurn) {
          const canOpen = (pos === -1 && diceVal === 6);
          const canMoveTrack = (pos >= 0 && (pos + diceVal) <= 56);
          if (canOpen || canMoveTrack) {
            el.classList.add('go');
            el.onclick = () => handleTokenClick(idx);
          }
        }

        b.appendChild(el);
        tk.el = el;
        tk.r = r;
        tk.k = k;
        activeTokens.push(tk);
      });

      // Multi-token stacking and collision layout (fans out gotis sharing a cell)
      const cellMap = {};
      activeTokens.forEach(tk => {
        const pos = parseInt(tk.pos);
        if (pos >= 0 && pos < 56) {
          const key = `${tk.r},${tk.k}`;
          (cellMap[key] = cellMap[key] || []).push(tk.el);
        }
      });
      Object.values(cellMap).forEach(group => {
        if (group.length > 1) {
          const n = group.length;
          group.forEach((el, j) => {
            const x = (j - (n - 1) / 2) * 24;
            el.style.transform = `translateX(${x}%) scale(${n > 2 ? 0.74 : 0.84})`;
          });
        }
      });
    }

    function toggleMute() {
      const muted = Snd.toggle();
      document.getElementById('btn-mute').textContent = muted ? '🔇' : '🔊';
    }

    function escapeHtml(str) {
      return (str || '').replace(/[&<>'"]/g, tag => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
      }[tag] || tag));
    }
  </script>
</body>
</html>
