<?php
require __DIR__ . '/../config.php';

if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$locked = (bool)getenv('ADMIN_PASS');
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['np']) && !$locked) {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $msg = 'Bad request';
    } else {
        $s = $pdo->prepare("SELECT password_hash FROM admins WHERE username = ?");
        $s->execute([$_SESSION['admin']]);
        $h = $s->fetchColumn();

        if (!password_verify($_POST['cp'] ?? '', (string)$h)) {
            $msg = 'Current password is wrong';
        } elseif (strlen($_POST['np']) < 4) {
            $msg = 'New password must be at least 4 characters';
        } else {
            $pdo->prepare("UPDATE admins SET password_hash = ? WHERE username = ?")
                ->execute([password_hash($_POST['np'], PASSWORD_DEFAULT), $_SESSION['admin']]);
            $msg = 'Password changed successfully!';
        }
    }
}

$P = [
    ['blue', 'Player 1', 'Blue', '#1ea3f2'],
    ['red', 'Player 2', 'Red', '#e8212c'],
    ['green', 'Player 3', 'Green', '#0c8f43'],
    ['yellow', 'Player 4', 'Yellow', '#fdd414']
];
$v = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
  <meta name="theme-color" content="#0b0e17">
  <title>Ludo King - Live Admin Controller & Voice Monitor</title>
  <link href="https://fonts.googleapis.com/css2?family=Lilita+One&family=Roboto:wght@500;700;900&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
    body {
      margin: 0; padding: 0; background: #0b0e17; color: #f0f4fc;
      font-family: 'Roboto', system-ui, -apple-system, sans-serif;
      min-height: 100vh;
    }
    .bar {
      display: flex; justify-content: space-between; align-items: center;
      padding: 12px 18px; background: linear-gradient(180deg, #161b2b 0%, #0f1320 100%);
      border-bottom: 1.5px solid #ffd700; position: sticky; top: 0; z-index: 100;
      box-shadow: 0 4px 15px rgba(0,0,0,0.5);
    }
    .bar-title {
      font-size: 16px; font-weight: 900; color: #ffd700; display: flex; align-items: center; gap: 8px;
      letter-spacing: 0.5px;
    }
    .bar-links a {
      color: #93b5ff; text-decoration: none; font-size: 13px; font-weight: 700;
      margin-left: 12px; transition: color 0.2s;
    }
    .bar-links a:hover { color: #ffd700; }
    .bar-links a.logout { color: #ff6b6b; }

    .wrap {
      max-width: 680px; margin: 0 auto; padding: 14px 14px 60px;
      display: flex; flex-direction: column; gap: 14px;
    }

    /* Live Telemetry Banner */
    .telemetry-card {
      background: linear-gradient(135deg, #141b2d 0%, #0d1220 100%);
      border: 2px solid #2d3752; border-radius: 14px; padding: 14px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.4); position: relative; overflow: hidden;
    }
    .telemetry-card.match-active {
      border-color: #22c55e;
      box-shadow: 0 0 16px rgba(34,197,94,0.3);
    }
    .telemetry-header {
      display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;
    }
    .telemetry-badge {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 900;
      letter-spacing: 0.5px;
    }
    .badge-live { background: rgba(34,197,94,0.2); color: #22c55e; border: 1px solid #22c55e; }
    .badge-live::before {
      content: ""; width: 7px; height: 7px; border-radius: 50%; background: #22c55e;
      box-shadow: 0 0 6px #22c55e; animation: pulseDot 1s infinite alternate;
    }
    .badge-idle { background: rgba(148,163,184,0.15); color: #94a3b8; border: 1px solid #475569; }
    @keyframes pulseDot { 0% { opacity: 0.4; transform: scale(0.8); } 100% { opacity: 1; transform: scale(1.2); } }

    .telemetry-turn {
      font-size: 18px; font-weight: 900; color: #ffffff; display: flex; align-items: center; gap: 8px;
      margin-bottom: 6px;
    }
    .turn-color-pill {
      padding: 2px 10px; border-radius: 6px; font-size: 13px; font-weight: 900; text-transform: uppercase;
      color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.8);
    }
    .pill-blue { background: #1ea3f2; }
    .pill-red { background: #e8212c; }
    .pill-green { background: #0c8f43; }
    .pill-yellow { background: #fdd414; color: #111 !important; text-shadow: none !important; }

    .timer-bar-wrap {
      width: 100%; height: 6px; background: #1e263d; border-radius: 3px; overflow: hidden; margin-top: 8px;
    }
    .timer-bar-fill {
      height: 100%; background: #22c55e; transition: width 0.3s linear, background-color 0.3s;
    }

    /* Target Match Switcher */
    .room-selector-bar {
      background: #141824; border: 1.5px solid #ffd700; border-radius: 12px;
      padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
    }
    .room-title { font-size: 13px; font-weight: 900; color: #ffd700; }
    .room-select {
      background: #0a0d14; color: #ffffff; border: 1.5px solid #3c6dbd; padding: 6px 12px;
      border-radius: 8px; font-size: 13px; font-weight: 700; outline: none; cursor: pointer;
    }

    /* 🎧 Live Room Voice Monitor Card */
    .voice-monitor-card {
      background: linear-gradient(135deg, #101c36 0%, #0a1122 100%);
      border: 2px solid #3b82f6; border-radius: 14px; padding: 14px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.45); display: flex; flex-direction: column; gap: 10px;
    }
    .vm-head { display: flex; justify-content: space-between; align-items: center; }
    .vm-title { font-size: 15px; font-weight: 900; color: #60a5fa; display: flex; align-items: center; gap: 8px; }
    .vm-status-badge {
      font-size: 11px; font-weight: 900; padding: 2px 8px; border-radius: 12px;
    }
    .vm-status-badge.live { background: #15803d; color: #86efac; border: 1px solid #22c55e; }
    .vm-status-badge.off { background: #334155; color: #94a3b8; }
    .vm-ctrls { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .vm-btn {
      padding: 7px 14px; border-radius: 20px; border: 1.5px solid #3b82f6;
      background: #1d4ed8; color: #fff; font-size: 12px; font-weight: 900; cursor: pointer;
      display: flex; align-items: center; gap: 6px; transition: all 0.2s;
    }
    .vm-btn:hover { background: #2563eb; }
    .vm-btn.stop { background: #b91c1c; border-color: #ef4444; }
    .vm-vol { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #93c5fd; }
    .vm-vol input { width: 80px; accent-color: #3b82f6; cursor: pointer; }

    /* Player speech visualizer indicators inside monitor */
    .vm-roster {
      display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 8px; margin-top: 4px;
    }
    .vm-speaker-box {
      background: #090e1a; border: 1px solid #1e293b; border-radius: 8px; padding: 8px 10px;
      display: flex; align-items: center; justify-content: space-between; font-size: 12px;
    }
    .vm-speaker-box.talking {
      border-color: #22c55e; box-shadow: 0 0 10px rgba(34, 197, 94, 0.4);
      background: rgba(34, 197, 94, 0.1);
    }
    .vm-speaker-name { font-weight: 900; }
    .vm-wave { font-size: 11px; color: #22c55e; font-weight: 900; display: none; }
    .vm-speaker-box.talking .vm-wave { display: inline-block; animation: waveBlink 0.5s infinite alternate; }

    /* Player Dice Cards */
    .card {
      background: #131724; border-radius: 14px; padding: 14px;
      border: 2px solid #23293d; position: relative; transition: all 0.25s ease;
      display: flex; flex-direction: column; gap: 10px;
    }
    .c-blue { border-left: 6px solid #1ea3f2; }
    .c-red { border-left: 6px solid #e8212c; }
    .c-green { border-left: 6px solid #0c8f43; }
    .c-yellow { border-left: 6px solid #fdd414; }

    .card.is-active-turn {
      border-color: #ffd700;
      box-shadow: 0 0 20px rgba(255, 215, 0, 0.45);
      animation: pulseActiveCard 1.2s infinite alternate ease-in-out;
    }
    @keyframes pulseActiveCard {
      0% { transform: scale(1); box-shadow: 0 0 14px rgba(255, 215, 0, 0.3); }
      100% { transform: scale(1.015); box-shadow: 0 0 24px rgba(255, 215, 0, 0.65); }
    }

    .card-top { display: flex; justify-content: space-between; align-items: center; }
    .card-top h3 {
      margin: 0; font-size: 16px; font-weight: 900; display: flex; align-items: center; gap: 8px;
    }
    .turn-now-badge {
      background: #22c55e; color: #fff; font-size: 10.5px; font-weight: 900;
      padding: 2px 8px; border-radius: 10px; letter-spacing: 0.5px;
      animation: pulseDot 0.8s infinite alternate;
    }

    /* Multi-Roll Queue Status Display */
    .queue-status-box {
      background: #0b0e17; border-radius: 8px; padding: 10px 12px;
      border: 1px solid #242c42; display: flex; flex-direction: column; gap: 6px;
    }
    .queue-header {
      display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 700; color: #8fa0c0;
    }
    .queue-list {
      display: flex; gap: 5px; flex-wrap: wrap; align-items: center; min-height: 28px;
    }
    .queue-pip {
      display: inline-flex; align-items: center; justify-content: center;
      width: 28px; height: 28px; border-radius: 6px; font-weight: 900; font-size: 14px;
      background: linear-gradient(180deg, #ffb300, #ff6f00); color: #fff;
      border: 1px solid #ffd700; box-shadow: 0 2px 4px rgba(0,0,0,0.4);
    }
    .queue-pip.first {
      border: 2px solid #ffffff; box-shadow: 0 0 8px #ffd700; transform: scale(1.08);
    }
    .queue-empty-msg {
      font-size: 12px; color: #8292ab; font-weight: 700; display: flex; align-items: center; gap: 6px;
    }

    /* 6 Realistic Dice Buttons */
    .dice-grid {
      display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px;
    }
    .btn-dice {
      aspect-ratio: 1 / 1; border-radius: 10px; border: 2px solid #ded3be;
      background: radial-gradient(circle at 40% 40%, #ffffff 0%, #f4eee2 65%, #ded0bd 100%);
      color: #111; font-family: 'Lilita One', system-ui, sans-serif; font-size: 22px; font-weight: 900;
      cursor: pointer; display: flex; align-items: center; justify-content: center;
      box-shadow: 0 3px 6px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.8);
      transition: transform 0.1s, box-shadow 0.1s, border-color 0.1s;
    }
    .btn-dice:active { transform: scale(0.92); }
    .btn-dice.d1 { color: #e71d36; }
    .btn-dice:hover { border-color: #ffd700; box-shadow: 0 0 10px rgba(255, 215, 0, 0.6); }

    /* Queue Action & Preset Buttons */
    .action-row {
      display: flex; gap: 6px; flex-wrap: wrap;
    }
    .btn-act {
      flex: 1; padding: 7px 8px; border-radius: 7px; border: 1px solid #36415d;
      background: #1b2133; color: #d0d8ea; font-size: 11.5px; font-weight: 700;
      cursor: pointer; transition: all 0.15s; white-space: nowrap; text-align: center;
    }
    .btn-act:hover { background: #262e47; color: #fff; }
    .btn-act:active { transform: scale(0.95); }
    .btn-act.gold {
      background: linear-gradient(180deg, #ffb300, #ff6f00); color: #fff; border-color: #ffd700;
      box-shadow: 0 2px 6px rgba(255,111,0,0.4);
    }
    .btn-act.cancel {
      background: #7f1d1d; color: #fca5a5; border-color: #b91c1c;
    }

    /* Custom Sequence Input Bar */
    .seq-input-bar {
      display: flex; gap: 6px; align-items: center; margin-top: 2px;
    }
    .seq-input {
      flex: 1; padding: 7px 10px; border-radius: 7px; border: 1px solid #3b4868;
      background: #090e1a; color: #ffffff; font-size: 13px; font-weight: 900;
      letter-spacing: 1px; outline: none;
    }
    .seq-input:focus { border-color: #ffd700; }

    .last-rolled-line {
      font-size: 11.5px; color: #8191aa; display: flex; align-items: center; gap: 6px;
    }
    .roll-tag {
      display: inline-block; padding: 1px 5px; border-radius: 4px; font-size: 11px; font-weight: 900;
      background: #20273a; color: #d6e0f2;
    }

    /* Rooms Card */
    .rooms-card {
      background: #131724; border-radius: 14px; padding: 14px; border: 1px solid #23293d;
    }
    .rooms-table { width: 100%; border-collapse: collapse; font-size: 12.5px; text-align: left; }
    .rooms-table th { color: #8ab4f8; padding: 6px 8px; border-bottom: 1px solid #2d3345; }
    .rooms-table td { padding: 8px 8px; border-bottom: 1px solid #1a2030; }
    .badge-act { background: #1e7e34; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 900; }
    .badge-wait { background: #b26a00; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 900; }

    /* Toast Notification */
    #toast {
      position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
      background: linear-gradient(180deg, #ffb300, #ff6f00); color: #ffffff;
      padding: 9px 18px; border-radius: 25px; font-weight: 900; font-size: 13.5px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.6); z-index: 1000; pointer-events: none;
      transition: opacity 0.3s, transform 0.3s; opacity: 0;
    }
    #toast.show { opacity: 1; transform: translate(-50%, -6px); }
  </style>
</head>
<body>
  <div class="bar">
    <div class="bar-title">
      <span>👑</span> LUDO KING CONTROLLER
    </div>
    <div class="bar-links">
      <a href="../game.php" target="_blank">🎮 Open Game</a>
      <a href="../online.php" target="_blank">🌐 Friends Online</a>
      <a href="logout.php" class="logout">Logout</a>
    </div>
  </div>

  <div class="wrap">
    <!-- Live Telemetry Status Banner -->
    <div class="telemetry-card" id="telemetry-box">
      <div class="telemetry-header">
        <span class="telemetry-badge badge-idle" id="telemetry-status-badge">Checking Game...</span>
        <span id="telemetry-target-title" style="font-size:12px;color:#8ab4f8;font-weight:700;">Local Game</span>
      </div>
      <div class="telemetry-turn" id="telemetry-turn-line">
        Waiting for match to begin...
      </div>
      <div style="font-size:12px;color:#8ab4f8;display:flex;justify-content:space-between;margin-top:4px;">
        <span id="telemetry-sub-status">Open game in another tab or phone to connect.</span>
        <span id="telemetry-timer-text" style="font-weight:900;color:#ffd700;"></span>
      </div>
      <div class="timer-bar-wrap">
        <div class="timer-bar-fill" id="telemetry-timer-bar" style="width: 100%;"></div>
      </div>
    </div>

    <!-- Active Room Selector -->
    <div class="room-selector-bar">
      <div>
        <span class="room-title">🎯 CONTROLLING MATCH:</span>
        <span id="active-target-label" style="font-weight:900;color:#ffffff;margin-left:6px;">Local Pass N Play</span>
      </div>
      <div>
        <select id="room-dropdown" class="room-select" onchange="onRoomChange(this.value)">
          <option value="local">Local (Pass N Play)</option>
        </select>
      </div>
    </div>

    <!-- 🎧 LIVE ROOM VOICE EAVESDROP / LISTENER CARD -->
    <div class="voice-monitor-card">
      <div class="vm-head">
        <div class="vm-title">
          <span>🎧</span> Live Room Voice Monitor (Eavesdrop Room)
        </div>
        <span class="vm-status-badge off" id="vm-badge">IDLE (NOT LISTENING)</span>
      </div>
      <p style="margin:0;font-size:12px;color:#93c5fd;">
        Admin can silently listen to the live voice chat of any active multiplayer room in real time.
      </p>
      <div class="vm-ctrls">
        <select id="vm-room-select" class="room-select" style="min-width:140px;">
          <option value="">-- Select Room --</option>
        </select>
        <button class="vm-btn" id="vm-start-btn" onclick="startEavesdropping()">
          <span>🎧</span> Listen to Room
        </button>
        <button class="vm-btn stop" id="vm-stop-btn" onclick="stopEavesdropping()" style="display:none;">
          <span>⏹️</span> Stop Listening
        </button>
        <div class="vm-vol">
          <span>🔊</span>
          <input type="range" id="vm-vol-slider" min="0" max="1" step="0.05" value="1" oninput="setVoiceVolume(this.value)">
        </div>
      </div>

      <!-- Real-time speaking visualizer per player in monitored room -->
      <div class="vm-roster" id="vm-roster-box">
        <div class="vm-speaker-box" id="vm-spk-blue"><span class="vm-speaker-name" style="color:#1ea3f2;">Blue: Idle</span><span class="vm-wave">(( 🎙️ ))</span></div>
        <div class="vm-speaker-box" id="vm-spk-red"><span class="vm-speaker-name" style="color:#e8212c;">Red: Idle</span><span class="vm-wave">(( 🎙️ ))</span></div>
        <div class="vm-speaker-box" id="vm-spk-green"><span class="vm-speaker-name" style="color:#0c8f43;">Green: Idle</span><span class="vm-wave">(( 🎙️ ))</span></div>
        <div class="vm-speaker-box" id="vm-spk-yellow"><span class="vm-speaker-name" style="color:#fdd414;">Yellow: Idle</span><span class="vm-wave">(( 🎙️ ))</span></div>
      </div>
    </div>

    <!-- 4 Player Cards with 15-Roll Queue Controllers -->
    <?php foreach($P as [$c, $n, $label, $hex]): ?>
      <div class="card c-<?=$c?>" id="card-<?=$c?>">
        <div class="card-top">
          <h3>
            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:<?=$hex?>;"></span>
            <?=$n?> <small style="color:#8191aa;font-weight:400;">(<?=$label?>)</small>
          </h3>
          <span class="turn-now-badge" id="badge-turn-<?=$c?>" style="display:none;">👉 ROLLING NOW</span>
        </div>

        <!-- Multi-Roll Queue Status Box (Up to 15 Rolls) -->
        <div class="queue-status-box">
          <div class="queue-header">
            <span>👑 Upcoming Queue (<span id="queue-count-<?=$c?>">0</span>/15 rolls remaining):</span>
            <button onclick="clearPlayerOverride('<?=$c?>')" style="background:none;border:none;color:#ff6b6b;cursor:pointer;font-weight:900;font-size:11.5px;">✕ Clear Queue</button>
          </div>
          <div class="queue-list" id="queue-list-<?=$c?>">
            <span class="queue-empty-msg">🎲 Queue empty ➔ Fair Random numbers active</span>
          </div>
        </div>

        <!-- 6 Instant Dice Buttons: Tap to set NEXT or Add to queue -->
        <div class="dice-grid">
          <?php for($i = 1; $i <= 6; $i++): ?>
            <button class="btn-dice <?=$i===1?'d1':''?>" 
                    data-p="<?=$c?>" data-v="<?=$i?>"
                    onclick="handleDiceButtonClick('<?=$c?>', <?=$i?>)"
                    title="Force <?=$i?>">
              <?=$i?>
            </button>
          <?php endfor; ?>
        </div>

        <!-- Fast Sequence Presets & Queue Builders -->
        <div class="action-row">
          <button class="btn-act gold" onclick="applyPresetSequence('<?=$c?>', [6,6,6])" title="Assign three 6s in sequence">
            ⚡ 3x Sixes (6, 6, 6)
          </button>
          <button class="btn-act" onclick="applyPresetSequence('<?=$c?>', [6,6,6,5,6])" title="Assign five rolls in sequence">
            🎯 5x Rush (6,6,6,5,6)
          </button>
          <button class="btn-act" onclick="applyPresetSequence('<?=$c?>', [6,6,6,5,4,6,6,3,5,6,2,4,6,5,6])" title="Set full 15-roll custom sequence">
            🚀 15x Super Chain
          </button>
        </div>

        <!-- Custom Comma-Separated Sequence Input -->
        <div class="seq-input-bar">
          <input class="seq-input" id="seq-input-<?=$c?>" placeholder="Custom e.g. 6,6,5,4,6" maxlength="45">
          <button class="btn-act" onclick="applyCustomSequenceInput('<?=$c?>')" style="max-width:110px;">
            Set Sequence
          </button>
        </div>

        <!-- Last Rolled Feed -->
        <div class="last-rolled-line">
          <span>Recent rolls:</span>
          <span id="history-<?=$c?>">-</span>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Live Online Multiplayer Rooms Management -->
    <div class="rooms-card">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
        <h3 style="margin:0;font-size:15px;color:#ffd700;">🌐 Live Online Multiplayer Rooms</h3>
        <button type="button" class="btn-act" onclick="purgeOldRooms()" style="padding:4px 10px; font-size:11px;">🧹 Clean Inactive</button>
      </div>
      <table class="rooms-table">
        <thead>
          <tr>
            <th>Room</th>
            <th>Host</th>
            <th>Players</th>
            <th>Status</th>
            <th>Turn</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="rooms-tbody">
          <tr><td colspan="6" style="text-align:center;color:#64748b;">No active rooms.</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Password Change Form -->
    <?php if(!$locked): ?>
      <form method="post" class="card" style="margin-top:6px;">
        <h3 style="margin:0;font-size:15px;color:#ffd700;">Change Admin Password</h3>
        <?php if($msg) echo "<p style='margin:0;font-size:12px;color:".($msg==='Password changed successfully!'?'#22c55e':'#ef4444')."'>".htmlspecialchars($msg)."</p>"; ?>
        <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
        <input name="cp" type="password" placeholder="Current password" style="padding:8px 12px;background:#0d111a;border:1px solid #333;color:#fff;border-radius:6px;" required>
        <input name="np" type="password" placeholder="New password" style="padding:8px 12px;background:#0d111a;border:1px solid #333;color:#fff;border-radius:6px;" required>
        <button style="padding:8px;background:#1852b4;color:#fff;border:none;border-radius:6px;font-weight:700;cursor:pointer;">Update Password</button>
      </form>
    <?php endif; ?>
  </div>

  <div id="toast">✅ Number Assigned!</div>

  <script src="../assets/js/security.js?v=<?=$v?>"></script>
  <script>
    const CSRF = <?=json_encode($_SESSION['csrf'])?>;
    const CL = ['blue', 'red', 'green', 'yellow'];
    let selectedRoom = 'local';
    let busy = false;

    function toast(text) {
      const t = document.getElementById('toast');
      t.textContent = text;
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 2200);
    }

    async function api(payload) {
      const url = `queue.php?room=${encodeURIComponent(selectedRoom)}`;
      try {
        const r = await fetch(url, {
          method: payload ? 'POST' : 'GET',
          headers: { 'Content-Type': 'application/json', 'X-CSRF': CSRF },
          body: payload ? JSON.stringify({ ...payload, room: selectedRoom }) : undefined,
          cache: 'no-store'
        });
        if (r.status === 401) { location.href = 'login.php'; return null; }
        if (!r.ok) return null;
        return await r.json();
      } catch (e) {
        return null;
      }
    }

    // 1-Tap dice click: if queue is empty, sets next roll; if queue already has numbers, appends up to 15!
    async function handleDiceButtonClick(player, value) {
      busy = true;
      const res = await api({ action: 'append_roll', player, value });
      busy = false;
      draw(res);
      toast(`✅ Added ${value} to ${player.toUpperCase()}'s queue!`);
    }

    // Apply preset sequence (e.g. [6, 6, 6] or 15 rolls)
    async function applyPresetSequence(player, sequence) {
      busy = true;
      const res = await api({ action: 'set_sequence', player, sequence });
      busy = false;
      draw(res);
      toast(`🚀 ${sequence.length}-Roll sequence assigned to ${player.toUpperCase()}!`);
    }

    // Apply custom comma-separated sequence
    async function applyCustomSequenceInput(player) {
      const inp = document.getElementById(`seq-input-${player}`);
      const val = inp.value.trim();
      if (!val) return;
      const nums = val.split(/[\s,]+/).map(x => parseInt(x, 10)).filter(x => x >= 1 && x <= 6);
      if (!nums.length) {
        alert('Please enter valid dice numbers (1 to 6) separated by commas.');
        return;
      }
      const seq = nums.slice(0, 15);
      await applyPresetSequence(player, seq);
      inp.value = '';
    }

    async function clearPlayerOverride(player) {
      busy = true;
      const res = await api({ action: 'clear', player });
      busy = false;
      draw(res);
      toast(`🎲 ${player.toUpperCase()} queue cleared ➔ Returning to Fair Random.`);
    }

    function onRoomChange(code) {
      selectedRoom = code;
      document.getElementById('active-target-label').textContent = (code === 'local') ? 'Local Pass N Play' : `Online Room: ${code}`;
      document.getElementById('telemetry-target-title').textContent = (code === 'local') ? 'Local Match' : `Online Room ${code}`;
      poll();
    }

    function draw(data) {
      if (!data) return;

      const live = data.live || {};
      const colors = data.colors || {};

      // 1. Update Live Telemetry Box
      const telemetryBox = document.getElementById('telemetry-box');
      const statusBadge = document.getElementById('telemetry-status-badge');
      const turnLine = document.getElementById('telemetry-turn-line');
      const subStatus = document.getElementById('telemetry-sub-status');
      const timerText = document.getElementById('telemetry-timer-text');
      const timerBar = document.getElementById('telemetry-timer-bar');

      if (live.is_active) {
        telemetryBox.classList.add('match-active');
        statusBadge.className = 'telemetry-badge badge-live';
        statusBadge.textContent = 'LIVE MATCH RUNNING';

        const curTurn = live.current_turn || 'blue';
        const curState = live.turn_state || 'roll';
        const timerSec = live.timer_seconds !== undefined ? live.timer_seconds : 12;

        turnLine.innerHTML = `👉 Active Turn: <span class="turn-color-pill pill-${curTurn}">${curTurn.toUpperCase()}</span>`;
        subStatus.textContent = (curState === 'roll') ? `Waiting for ${curTurn} to roll dice...` : `Player moving token...`;
        timerText.textContent = `${timerSec}s left`;

        const pct = Math.max(0, Math.min(100, (timerSec / 12) * 100));
        timerBar.style.width = pct + '%';
        timerBar.style.backgroundColor = (timerSec <= 3) ? '#ef4444' : (timerSec <= 6 ? '#f59e0b' : '#22c55e');
      } else {
        telemetryBox.classList.remove('match-active');
        statusBadge.className = 'telemetry-badge badge-idle';
        statusBadge.textContent = 'WAITING FOR GAME';
        turnLine.textContent = 'Game is not currently open in another tab.';
        subStatus.textContent = 'Open game.php to start live telemetry.';
        timerText.textContent = '';
        timerBar.style.width = '0%';
      }

      // 2. Update Each Player Card & Upcoming Rolls Queue
      CL.forEach(c => {
        const d = colors[c] || { queue: [], last: [] };
        const cardEl = document.getElementById('card-' + c);
        const turnBadge = document.getElementById('badge-turn-' + c);
        const queueCountEl = document.getElementById('queue-count-' + c);
        const queueListEl = document.getElementById('queue-list-' + c);
        const histEl = document.getElementById('history-' + c);

        // Turn highlight
        const isCurrentTurn = live.is_active && (live.current_turn === c);
        cardEl.classList.toggle('is-active-turn', isCurrentTurn);
        if (turnBadge) {
          turnBadge.style.display = isCurrentTurn ? 'inline-block' : 'none';
          if (isCurrentTurn && live.timer_seconds !== undefined) {
            turnBadge.textContent = `👉 ROLLING NOW (${live.timer_seconds}s)`;
          }
        }

        // Render Multi-Roll Queue
        const q = d.queue || [];
        queueCountEl.textContent = q.length;

        if (q.length > 0) {
          queueListEl.innerHTML = q.map((v, idx) => 
            `<span class="queue-pip ${idx === 0 ? 'first' : ''}" title="Roll #${idx+1}: ${v}">${v}</span>`
          ).join('');
        } else {
          queueListEl.innerHTML = `<span class="queue-empty-msg">🎲 Queue completed ➔ Automatic Fair Random active</span>`;
        }

        // Roll History
        if (d.last && d.last.length) {
          histEl.innerHTML = d.last.map(v => `<span class="roll-tag">${v}</span>`).join(' ');
        } else {
          histEl.textContent = '-';
        }
      });

      // 3. Update Rooms List Dropdown & Table
      const rooms = data.rooms || [];
      const sel = document.getElementById('room-dropdown');
      const vmSel = document.getElementById('vm-room-select');
      const curVal = sel.value;
      const curVmVal = vmSel.value;

      sel.innerHTML = '<option value="local">Local (Pass N Play)</option>';
      vmSel.innerHTML = '<option value="">-- Select Room --</option>';

      const tbody = document.getElementById('rooms-tbody');
      if (rooms.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#64748b;">No active online rooms yet.</td></tr>';
      } else {
        tbody.innerHTML = '';
        rooms.forEach(rm => {
          const opt = document.createElement('option');
          opt.value = rm.room_code;
          opt.textContent = `Room ${rm.room_code} (${rm.host_name})`;
          sel.appendChild(opt);

          const vmOpt = document.createElement('option');
          vmOpt.value = rm.room_code;
          vmOpt.textContent = `Room ${rm.room_code} (${rm.host_name})`;
          vmSel.appendChild(vmOpt);

          const tr = document.createElement('tr');
          const isSel = (rm.room_code === selectedRoom);
          tr.innerHTML = `
            <td><b>${rm.room_code}</b></td>
            <td>${escapeHtml(rm.host_name)}</td>
            <td>${rm.max_players}P</td>
            <td><span class="${rm.status === 'active' ? 'badge-act' : 'badge-wait'}">${rm.status.toUpperCase()}</span></td>
            <td><b style="color:${rm.turn_color}">${rm.turn_color.toUpperCase()}</b></td>
            <td>
              <button class="btn-act ${isSel ? 'gold' : ''}" onclick="selectRoomFromTable('${rm.room_code}')" style="padding:2px 7px;font-size:11px;">
                ${isSel ? '✓ Controlled' : 'Control'}
              </button>
              <button class="btn-act" onclick="quickListenRoom('${rm.room_code}')" style="padding:2px 7px;font-size:11px;margin-left:4px;" title="Listen to room voice chat">
                🎧 Listen
              </button>
              <button onclick="deleteSingleRoom('${rm.room_code}')" style="background:#7f1d1d;border:none;color:#fca5a5;padding:2px 6px;border-radius:4px;cursor:pointer;font-size:11px;margin-left:4px;">🗑️</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }
      sel.value = selectedRoom;
      if (curVmVal) vmSel.value = curVmVal;
    }

    function selectRoomFromTable(code) {
      document.getElementById('room-dropdown').value = code;
      onRoomChange(code);
    }

    function quickListenRoom(code) {
      document.getElementById('vm-room-select').value = code;
      startEavesdropping();
    }

    async function purgeOldRooms() {
      if (!confirm('Erase all inactive and test rooms?')) return;
      busy = true;
      draw(await api({ action: 'clear_old_rooms' }));
      busy = false;
    }

    async function deleteSingleRoom(code) {
      if (!confirm(`Delete Room ${code}?`)) return;
      busy = true;
      if (selectedRoom === code) onRoomChange('local');
      draw(await api({ action: 'delete_room', target_room: code }));
      busy = false;
    }

    async function poll() {
      if (!busy && !document.hidden) {
        draw(await api());
      }
    }

    // ----------------------------------------------------
    // 🎧 WEBRTC LIVE VOICE EAVESDROPPING / LISTENER ENGINE
    // ----------------------------------------------------
    let listeningRoom = null;
    let vmPeerConnections = {};
    let vmAudioElements = {};
    let vmAnalysers = {};
    let vmAudioCtx = null;
    let vmSignalsInterval = null;
    let vmSpeakingInterval = null;
    let currentVoiceVolume = 1.0;

    const rtcConfig = {
      iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
        { urls: 'stun:stun2.l.google.com:19302' }
      ]
    };

    function setVoiceVolume(v) {
      currentVoiceVolume = parseFloat(v);
      Object.values(vmAudioElements).forEach(a => {
        if (a) a.volume = currentVoiceVolume;
      });
    }

    async function startEavesdropping() {
      const roomSel = document.getElementById('vm-room-select');
      const code = roomSel.value;
      if (!code) {
        alert('Please select an online room to listen to.');
        return;
      }

      stopEavesdropping();
      listeningRoom = code;

      const AudioContextClass = window.AudioContext || window.webkitAudioContext;
      if (AudioContextClass) vmAudioCtx = new AudioContextClass();

      document.getElementById('vm-badge').className = 'vm-status-badge live';
      document.getElementById('vm-badge').textContent = `🔴 LISTENING (ROOM: ${code})`;
      document.getElementById('vm-start-btn').style.display = 'none';
      document.getElementById('vm-stop-btn').style.display = 'inline-flex';

      // Fetch active players in this room
      try {
        const pRes = await fetch(`../api/online.php?action=admin_room_players&room_code=${code}`).then(r => r.json());
        if (pRes && pRes.players) {
          pRes.players.forEach(p => {
            const spkBox = document.getElementById(`vm-spk-${p.color}`);
            if (spkBox) {
              spkBox.querySelector('.vm-speaker-name').textContent = `${p.player_name} (${p.color.toUpperCase()})`;
            }
            createAdminPeerOffer(p.color);
          });
        }
      } catch(e) {}

      // Start voice signals polling for Admin
      vmSignalsInterval = setInterval(pollAdminVoiceSignals, 750);
      vmSpeakingInterval = setInterval(monitorAdminSpeakingLevels, 120);
      toast(`🎧 Connected to Room ${code} voice chat!`);
    }

    function stopEavesdropping() {
      if (!listeningRoom) return;
      clearInterval(vmSignalsInterval);
      clearInterval(vmSpeakingInterval);

      Object.values(vmPeerConnections).forEach(pc => pc.close());
      Object.values(vmAudioElements).forEach(a => a.remove());

      vmPeerConnections = {};
      vmAudioElements = {};
      vmAnalysers = {};
      listeningRoom = null;

      document.getElementById('vm-badge').className = 'vm-status-badge off';
      document.getElementById('vm-badge').textContent = 'IDLE (NOT LISTENING)';
      document.getElementById('vm-start-btn').style.display = 'inline-flex';
      document.getElementById('vm-stop-btn').style.display = 'none';

      CL.forEach(c => {
        const spkBox = document.getElementById(`vm-spk-${c}`);
        if (spkBox) {
          spkBox.classList.remove('talking');
          spkBox.querySelector('.vm-speaker-name').textContent = `${c.toUpperCase()}: Idle`;
        }
      });
      toast('⏹️ Disconnected from room voice chat.');
    }

    function getOrCreateAdminPC(color) {
      if (vmPeerConnections[color]) return vmPeerConnections[color];

      const pc = new RTCPeerConnection(rtcConfig);

      // Offer to receive audio from player without sending any mic track
      pc.addTransceiver('audio', { direction: 'recvonly' });

      pc.onicecandidate = (event) => {
        if (event.candidate) {
          sendAdminVoiceSignal(color, 'candidate', JSON.stringify(event.candidate));
        }
      };

      pc.ontrack = (event) => {
        const stream = event.streams[0];
        let audio = vmAudioElements[color];
        if (!audio) {
          audio = document.createElement('audio');
          audio.autoplay = true;
          audio.playsInline = true;
          audio.volume = currentVoiceVolume;
          document.body.appendChild(audio);
          vmAudioElements[color] = audio;
        }
        audio.srcObject = stream;

        if (vmAudioCtx) {
          try {
            const src = vmAudioCtx.createMediaStreamSource(stream);
            const an = vmAudioCtx.createAnalyser();
            an.fftSize = 64;
            src.connect(an);
            vmAnalysers[color] = an;
          } catch(e) {}
        }
      };

      vmPeerConnections[color] = pc;
      return pc;
    }

    async function createAdminPeerOffer(color) {
      try {
        const pc = getOrCreateAdminPC(color);
        const offer = await pc.createOffer({ offerToReceiveAudio: true });
        await pc.setLocalDescription(offer);
        sendAdminVoiceSignal(color, 'offer', JSON.stringify(offer));
      } catch(e) {}
    }

    async function sendAdminVoiceSignal(toColor, type, data) {
      if (!listeningRoom) return;
      const fd = new FormData();
      fd.append('action', 'send_voice_signal');
      fd.append('room_code', listeningRoom);
      fd.append('token', 'admin');
      fd.append('to_color', toColor);
      fd.append('signal_type', type);
      fd.append('signal_data', data);
      await fetch('../api/online.php', { method: 'POST', body: fd }).catch(() => {});
    }

    async function pollAdminVoiceSignals() {
      if (!listeningRoom) return;
      try {
        const res = await fetch(`../api/online.php?action=get_voice_signals&room_code=${listeningRoom}&token=admin`).then(r => r.json());
        if (!res.ok || !res.signals) return;

        for (const sig of res.signals) {
          const fromColor = sig.from_color;
          const pc = getOrCreateAdminPC(fromColor);

          if (sig.signal_type === 'answer') {
            if (pc.signalingState !== 'stable') {
              await pc.setRemoteDescription(new RTCSessionDescription(JSON.parse(sig.signal_data)));
            }
          } else if (sig.signal_type === 'candidate') {
            try {
              await pc.addIceCandidate(new RTCIceCandidate(JSON.parse(sig.signal_data)));
            } catch(e) {}
          }
        }
      } catch(e) {}
    }

    function monitorAdminSpeakingLevels() {
      CL.forEach(c => {
        const an = vmAnalysers[c];
        const spkBox = document.getElementById(`vm-spk-${c}`);
        if (!spkBox) return;

        if (an) {
          const buf = new Uint8Array(an.frequencyBinCount);
          an.getByteFrequencyData(buf);
          let sum = 0;
          for (let i = 0; i < buf.length; i++) sum += buf[i];
          const avg = sum / buf.length;
          const isSpeaking = avg > 14;
          spkBox.classList.toggle('talking', isSpeaking);
        } else {
          spkBox.classList.remove('talking');
        }
      });
    }

    function escapeHtml(str) {
      return (str || '').replace(/[&<>'"]/g, tag => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
      }[tag] || tag));
    }

    poll();
    setInterval(poll, 1000);
  </script>
</body>
</html>
