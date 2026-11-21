<?php
require __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

if (empty($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'login']);
    exit;
}

$C = ['blue', 'red', 'green', 'yellow'];

function snap($pdo, $C, $room = 'local') {
    $o = [];
    foreach ($C as $c) {
        $s = $pdo->prepare("SELECT value FROM dice_override WHERE room_code = ? AND player = ? AND used = 0 ORDER BY id ASC");
        $s->execute([$room, $c]);
        $q = array_map('intval', $s->fetchAll(PDO::FETCH_COLUMN));

        $s = $pdo->prepare("SELECT value FROM dice_override WHERE room_code = ? AND player = ? AND used = 1 ORDER BY id DESC LIMIT 8");
        $s->execute([$room, $c]);
        $l = array_map('intval', $s->fetchAll(PDO::FETCH_COLUMN));

        $o[$c] = [
            'queue' => $q,
            'last' => $l,
            'forced_next' => !empty($q) ? $q[0] : null
        ];
    }

    // Live Match Telemetry Status
    $liveSt = $pdo->prepare("SELECT current_turn, turn_state, timer_seconds, last_roll, last_roll_type, active_players_json, updated_at FROM game_live_status WHERE room_code = ?");
    $liveSt->execute([$room]);
    $liveRow = $liveSt->fetch(PDO::FETCH_ASSOC);

    $isActive = false;
    if ($liveRow && !empty($liveRow['updated_at'])) {
        $secondsSinceHeartbeat = time() - strtotime($liveRow['updated_at']);
        if ($secondsSinceHeartbeat <= 20) {
            $isActive = true;
        }
    }

    $liveData = [
        'is_active' => $isActive,
        'current_turn' => $liveRow['current_turn'] ?? 'blue',
        'turn_state' => $liveRow['turn_state'] ?? 'roll',
        'timer_seconds' => isset($liveRow['timer_seconds']) ? (int)$liveRow['timer_seconds'] : 12,
        'last_roll' => (int)($liveRow['last_roll'] ?? 0),
        'last_roll_type' => $liveRow['last_roll_type'] ?? 'random',
        'active_players' => !empty($liveRow['active_players_json']) ? json_decode($liveRow['active_players_json'], true) : null
    ];

    // Online Rooms (recent from last 24h)
    $rSt = $pdo->query("SELECT room_code, host_name, max_players, status, turn_color, updated_at FROM online_rooms WHERE updated_at >= datetime('now', '-1 day') ORDER BY id DESC LIMIT 15");
    $rooms = $rSt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'current_room' => $room,
        'colors' => $o,
        'live' => $liveData,
        'rooms' => $rooms
    ];
}

$room = trim($_GET['room'] ?? 'local');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_SERVER['HTTP_X_CSRF'] ?? '')) {
        http_response_code(403);
        echo json_encode(['error' => 'csrf']);
        exit;
    }

    $in = json_decode(file_get_contents('php://input'), true) ?: [];
    $a = $in['action'] ?? '';
    $p = $in['player'] ?? '';
    $room = trim($in['room'] ?? $room ?: 'local');
    $ok = in_array($p, $C, true);

    if ($a === 'set_next' && $ok) {
        // Immediate single override: clear previous pending and set this number as NEXT roll
        $v = max(1, min(6, (int)($in['value'] ?? 0)));
        if ($v >= 1 && $v <= 6) {
            $pdo->prepare("DELETE FROM dice_override WHERE room_code = ? AND player = ? AND used = 0")->execute([$room, $p]);
            $pdo->prepare("INSERT INTO dice_override (room_code, player, value) VALUES (?, ?, ?)")->execute([$room, $p, $v]);
        }
    } elseif ($a === 'set_sequence' && $ok) {
        // Set upcoming sequence of up to 15 rolls
        $seq = (array)($in['sequence'] ?? []);
        $pdo->prepare("DELETE FROM dice_override WHERE room_code = ? AND player = ? AND used = 0")->execute([$room, $p]);
        $ins = $pdo->prepare("INSERT INTO dice_override (room_code, player, value) VALUES (?, ?, ?)");
        $count = 0;
        foreach ($seq as $val) {
            $v = (int)$val;
            if ($v >= 1 && $v <= 6 && $count < 15) {
                $ins->execute([$room, $p, $v]);
                $count++;
            }
        }
    } elseif ($a === 'append_roll' && $ok) {
        // Append a single roll to the upcoming queue (up to 15 rolls max)
        $v = max(1, min(6, (int)($in['value'] ?? 0)));
        $nSt = $pdo->prepare("SELECT COUNT(*) FROM dice_override WHERE room_code = ? AND player = ? AND used = 0");
        $nSt->execute([$room, $p]);
        $currCount = (int)$nSt->fetchColumn();
        if ($v >= 1 && $v <= 6 && $currCount < 15) {
            $pdo->prepare("INSERT INTO dice_override (room_code, player, value) VALUES (?, ?, ?)")->execute([$room, $p, $v]);
        }
    } elseif ($a === 'add' && $ok) {
        $v = (int)($in['value'] ?? 0);
        $n = $pdo->prepare("SELECT COUNT(*) FROM dice_override WHERE room_code = ? AND player = ? AND used = 0");
        $n->execute([$room, $p]);
        if ($v >= 1 && $v <= 6 && $n->fetchColumn() < 50) {
            $pdo->prepare("INSERT INTO dice_override (room_code, player, value) VALUES (?, ?, ?)")->execute([$room, $p, $v]);
        }
    } elseif ($a === 'pop' && $ok) {
        $pdo->prepare("DELETE FROM dice_override WHERE id = (SELECT id FROM dice_override WHERE room_code = ? AND player = ? AND used = 0 ORDER BY id DESC LIMIT 1)")->execute([$room, $p]);
    } elseif ($a === 'clear' && $ok) {
        $pdo->prepare("DELETE FROM dice_override WHERE room_code = ? AND player = ? AND used = 0")->execute([$room, $p]);
    } elseif ($a === 'clear_all') {
        $pdo->prepare("DELETE FROM dice_override WHERE room_code = ? AND used = 0")->execute([$room]);
    } elseif ($a === 'clear_history') {
        $pdo->prepare("DELETE FROM dice_override WHERE room_code = ? AND used = 1")->execute([$room]);
    } elseif ($a === 'clear_old_rooms') {
        purge_old_ludo_data($pdo, true);
    } elseif ($a === 'delete_room') {
        $target = trim($in['target_room'] ?? '');
        if ($target && $target !== 'local') {
            $pdo->prepare("DELETE FROM online_rooms WHERE room_code = ?")->execute([$target]);
            $pdo->prepare("DELETE FROM online_players WHERE room_code = ?")->execute([$target]);
            $pdo->prepare("DELETE FROM online_tokens WHERE room_code = ?")->execute([$target]);
            $pdo->prepare("DELETE FROM dice_override WHERE room_code = ?")->execute([$target]);
            $pdo->prepare("DELETE FROM game_live_status WHERE room_code = ?")->execute([$target]);
        }
    }
}

echo json_encode(snap($pdo, $C, $room));
