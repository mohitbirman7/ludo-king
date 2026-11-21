<?php
require __DIR__ . '/../config.php';
session_write_close();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$p = trim($_REQUEST['player'] ?? '');
$room = trim($_REQUEST['room'] ?? 'local');

// 1. Live Match Heartbeat: Game reports active turn, state, and remaining timer seconds
if (isset($_REQUEST['heartbeat'])) {
    $turn = trim($_REQUEST['turn'] ?? 'blue');
    $state = trim($_REQUEST['state'] ?? 'roll');
    $timer = max(0, min(60, (int)($_REQUEST['timer'] ?? 12)));
    $playersJson = isset($_REQUEST['players']) ? trim($_REQUEST['players']) : null;

    $st = $pdo->prepare("INSERT INTO game_live_status (room_code, current_turn, turn_state, timer_seconds, active_players_json, updated_at) 
        VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ON CONFLICT(room_code) DO UPDATE SET 
            current_turn = excluded.current_turn,
            turn_state = excluded.turn_state,
            timer_seconds = excluded.timer_seconds,
            active_players_json = COALESCE(excluded.active_players_json, game_live_status.active_players_json),
            updated_at = CURRENT_TIMESTAMP");
    $st->execute([$room, $turn, $state, $timer, $playersJson]);

    echo json_encode(['ok' => true]);
    exit;
}

// 2. Mark a peeked number as used (called right after dice begins rolling)
if (isset($_REQUEST['use'])) {
    $useId = (int)$_REQUEST['use'];
    if ($useId > 0) {
        $pdo->prepare("UPDATE dice_override SET used = 1 WHERE id = ?")->execute([$useId]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

// 3. Find the next unused admin number for this player
$s = $pdo->prepare("SELECT id, value FROM dice_override WHERE player = ? AND room_code = ? AND used = 0 ORDER BY id LIMIT 1");
$s->execute([$p, $room]);
$r = $s->fetch(PDO::FETCH_ASSOC);

if (!$r && $room !== 'local') {
    // Fallback to local override if room-specific was not set
    $s = $pdo->prepare("SELECT id, value FROM dice_override WHERE player = ? AND room_code = 'local' AND used = 0 ORDER BY id LIMIT 1");
    $s->execute([$p]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
}

// Peek only: check if admin set a number without marking it used
if (isset($_REQUEST['peek'])) {
    echo json_encode($r ? ['ok' => true, 'id' => (int)$r['id'], 'dice' => (int)$r['value']] : ['ok' => true, 'id' => 0, 'dice' => 0]);
    exit;
}

// Roll action: consume override if exists, or return fair random number
if ($r) {
    $d = (int)$r['value'];
    $source = 'admin';
    $pdo->prepare("UPDATE dice_override SET used = 1 WHERE id = ?")->execute([$r['id']]);
} else {
    $d = random_int(1, 6);
    $source = 'random';
}

// Update live match telemetry with last rolled value & source
try {
    $pdo->prepare("INSERT INTO game_live_status (room_code, current_turn, last_roll, last_roll_type, updated_at) 
        VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
        ON CONFLICT(room_code) DO UPDATE SET 
            last_roll = excluded.last_roll,
            last_roll_type = excluded.last_roll_type,
            updated_at = CURRENT_TIMESTAMP")->execute([$room, $p, $d, $source]);
} catch (Exception $e) {}

echo json_encode([
    'ok' => true,
    'dice' => $d,
    'source' => $source,
    'id' => (int)($r['id'] ?? 0)
]);
