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
    ['blue', 'Player 1', 'Blue'],
    ['red', 'Player 2', 'Red'],
    ['green', 'Player 3', 'Green'],
    ['yellow', 'Player 4', 'Yellow']
];
$v = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="theme-color" content="#14161f">
  <title>Ludo King - Admin Control Panel</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
  <style>
    .room-selector-bar {
      background: #1e2230; border: 1.5px solid #ffd700; border-radius: 12px;
      padding: 12px 16px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
    }
    .room-title { font-size: 14px; font-weight: 900; color: #ffd700; }
    .room-select {
      background: #0d111a; color: #ffffff; border: 1.5px solid #3c6dbd; padding: 6px 12px;
      border-radius: 8px; font-size: 14px; font-weight: 700; outline: none; cursor: pointer;
    }
    .room-select:focus { border-color: #ffd700; }
    .rooms-card {
      background: #1b1e2a; border-radius: 12px; padding: 14px; margin-top: 16px; border: 1px solid #2d3345;
    }
    .rooms-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
    .rooms-table th { color: #8ab4f8; padding: 8px; border-bottom: 1px solid #2d3345; }
    .rooms-table td { padding: 8px; border-bottom: 1px solid #242938; }
    .badge-act { background: #1e7e34; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 900; }
    .badge-wait { background: #b26a00; color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 900; }
    .btn-select-room {
      background: #1852b4; color: #fff; border: 1px solid #ffd700; padding: 3px 8px; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 700;
    }
  </style>
</head>
<body>
  <div class="bar">
    <b>👑 Ludo King Admin Control</b>
    <span>
      <a href="../online.php" target="_blank">Play Friends</a> &nbsp;
      <a href="../index.php" target="_blank">Home</a> &nbsp;
      <a href="logout.php">Log Out</a>
    </span>
  </div>

  <div class="wrap">
    <!-- Active Room Selector for Multi-room Dice Control -->
    <div class="room-selector-bar">
      <div>
        <span class="room-title">🎯 CONTROLLING MATCH:</span>
        <span id="active-target-label" style="font-weight:900;color:#ffffff;margin-left:6px;">Local Pass N Play</span>
      </div>
      <div>
        <label style="font-size:12px;color:#8ab4f8;margin-right:6px;">Switch Room:</label>
        <select id="room-dropdown" class="room-select" onchange="onRoomChange(this.value)">
          <option value="local">Local (Pass N Play)</option>
        </select>
      </div>
    </div>

    <p class="note">Jis player ka number set karna ho uske neeche 1-6 dabao. Wo number agle rolls me order me aayega. Khali ho to fair random chalega.</p>

    <!-- Player Dice Cards -->
    <?php foreach($P as [$c,$n,$l]): ?>
      <div class="card c-<?=$c?>">
        <h3><?=$n?> <small><?=$l?></small></h3>
        <div class="q" id="q-<?=$c?>"></div>
        <div class="dg">
          <?php for($i=1;$i<=6;$i++) echo "<button data-p='$c' data-a='add' data-v='$i'>$i</button>"; ?>
        </div>
        <div class="ac">
          <button class="g" data-p="<?=$c?>" data-a="pop">Undo last</button>
          <button class="g" data-p="<?=$c?>" data-a="clear">Clear</button>
        </div>
        <div class="lr">Last rolled: <span id="l-<?=$c?>">-</span></div>
      </div>
    <?php endforeach; ?>

    <div class="card">
      <div class="ac">
        <button class="g" data-a="clear_all">Clear all numbers</button>
        <button class="g" data-a="clear_history">Clear history</button>
      </div>
    </div>

    <!-- Active Online Rooms List -->
    <div class="rooms-card">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
        <h3 style="margin:0;color:#ffd700;">🌐 Live Online Multiplayer Rooms (Last 24h)</h3>
        <button type="button" class="g" onclick="purgeOldRooms()" style="padding:5px 12px; font-size:12px; background:linear-gradient(180deg,#7b1fa2,#4a148c); border:1px solid #ba68c8; color:#fff; border-radius:6px; cursor:pointer; font-weight:700;">🧹 Clear Old / Inactive Rooms</button>
      </div>
      <table class="rooms-table">
        <thead>
          <tr>
            <th>Room Code</th>
            <th>Host Name</th>
            <th>Players</th>
            <th>Status</th>
            <th>Current Turn</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="rooms-tbody">
          <tr><td colspan="6" style="text-align:center;color:#888;">No active online rooms yet.</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Password Management -->
    <?php if(!$locked): ?>
      <form method="post" class="card" style="margin-top:16px;">
        <h3>Change Admin Password</h3>
        <?php if($msg) echo "<p class='".($msg==='Password changed successfully!'?'ok':'err')."'>".htmlspecialchars($msg)."</p>"; ?>
        <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
        <input name="cp" type="password" placeholder="Current password" required>
        <input name="np" type="password" placeholder="New password" required>
        <button>Update Password</button>
      </form>
    <?php endif; ?>
  </div>

  <script src="../assets/js/security.js?v=<?=$v?>"></script>
  <script>
    const CSRF = <?=json_encode($_SESSION['csrf'])?>;
    const CL = ['blue','red','green','yellow'];
    let selectedRoom = 'local';
    let busy = false;

    async function api(payload) {
      const url = `queue.php?room=${encodeURIComponent(selectedRoom)}`;
      const r = await fetch(url, {
        method: payload ? 'POST' : 'GET',
        headers: { 'Content-Type': 'application/json', 'X-CSRF': CSRF },
        body: payload ? JSON.stringify({ ...payload, room: selectedRoom }) : undefined,
        cache: 'no-store'
      });
      if (r.status === 401) { location.href = 'login.php'; return null; }
      if (!r.ok) return null;
      return r.json();
    }

    function onRoomChange(code) {
      selectedRoom = code;
      document.getElementById('active-target-label').textContent = code === 'local' ? 'Local Pass N Play' : `Online Room: ${code}`;
      poll();
    }

    function draw(data) {
      if (!data) return;
      const colors = data.colors;
      CL.forEach(c => {
        const d = colors[c];
        document.getElementById('q-' + c).innerHTML = d.queue.length
          ? d.queue.map((v, i) => `<i class="chip${i ? '' : ' nx'}">${v}${i ? '' : '<em>NEXT</em>'}</i>`).join('')
          : '<span class="rnd">Random (no numbers set)</span>';
        document.getElementById('l-' + c).textContent = d.last.length ? d.last.join(' ') : '-';
      });

      // Update rooms dropdown & table
      const rooms = data.rooms || [];
      const sel = document.getElementById('room-dropdown');
      const curVal = sel.value;
      sel.innerHTML = '<option value="local">Local (Pass N Play)</option>';
      
      const tbody = document.getElementById('rooms-tbody');
      if (rooms.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No active online rooms yet.</td></tr>';
      } else {
        tbody.innerHTML = '';
        rooms.forEach(rm => {
          const opt = document.createElement('option');
          opt.value = rm.room_code;
          opt.textContent = `Room ${rm.room_code} (${rm.host_name})`;
          sel.appendChild(opt);

          const tr = document.createElement('tr');
          const isSel = (rm.room_code === selectedRoom);
          tr.innerHTML = `
            <td><b>${rm.room_code}</b></td>
            <td>${escapeHtml(rm.host_name)}</td>
            <td>${rm.max_players} Players</td>
            <td><span class="${rm.status === 'active' ? 'badge-act' : 'badge-wait'}">${rm.status.toUpperCase()}</span></td>
            <td><b style="color:${rm.turn_color}">${rm.turn_color.toUpperCase()}</b></td>
            <td>
              <button class="btn-select-room" onclick="selectRoomFromTable('${rm.room_code}')">
                ${isSel ? '✓ Controlled' : 'Control Dice'}
              </button>
              <button onclick="deleteSingleRoom('${rm.room_code}')" style="background:#b71c1c; border:1px solid #ef5350; color:#fff; padding:3px 7px; border-radius:6px; cursor:pointer; font-size:11px; margin-left:4px; font-weight:700;" title="Delete this room">🗑️</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }
      sel.value = selectedRoom;
    }

    function selectRoomFromTable(code) {
      document.getElementById('room-dropdown').value = code;
      onRoomChange(code);
    }

    async function purgeOldRooms() {
      if (!confirm('Erase all inactive and older test rooms?')) return;
      busy = true;
      draw(await api({ action: 'clear_old_rooms' }));
      busy = false;
    }

    async function deleteSingleRoom(code) {
      if (!confirm(`Delete Room ${code} and erase its data?`)) return;
      busy = true;
      if (selectedRoom === code) {
        onRoomChange('local');
      }
      draw(await api({ action: 'delete_room', target_room: code }));
      busy = false;
    }

    document.addEventListener('click', async e => {
      const b = e.target.closest('button[data-a]');
      if (!b) return;
      busy = true;
      b.disabled = true;
      draw(await api({ action: b.dataset.a, player: b.dataset.p, value: +b.dataset.v || 0 }));
      b.disabled = false;
      busy = false;
    });

    async function poll() {
      if (!busy && !document.hidden) {
        draw(await api());
      }
    }

    function escapeHtml(str) {
      return (str || '').replace(/[&<>'"]/g, tag => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
      }[tag] || tag));
    }

    poll();
    setInterval(poll, 3000);
  </script>
</body>
</html>
