<?php
require __DIR__ . '/../config.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');

if (empty($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'login']);
    exit;
}

$C = ['blue', 'red', 'green', 'yellow'];

function snap($pdo, $C, $room = 'local') {
    $o = [];
    foreach ($C as $c) {
        $s = $pdo->prepare("SELECT value FROM dice_override WHERE room_code = ? AND player = ? AND used = 0 ORDER BY id");
        $s->execute([$room, $c]);
        $q = array_map('intval', $s->fetchAll(PDO::FETCH_COLUMN));

        $s = $pdo->prepare("SELECT value FROM dice_override WHERE room_code = ? AND player = ? AND used = 1 ORDER BY id DESC LIMIT 8");
        $s->execute([$room, $c]);
        $l = array_map('intval', $s->fetchAll(PDO::FETCH_COLUMN));

        $o[$c] = ['queue' => $q, 'last' => $l];
    }

    // Also fetch list of active online rooms (only recent from last 24 hours)
    $rSt = $pdo->query("SELECT room_code, host_name, max_players, status, turn_color, updated_at FROM online_rooms WHERE updated_at >= datetime('now', '-1 day') ORDER BY id DESC LIMIT 15");
    $rooms = $rSt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'current_room' => $room,
        'colors' => $o,
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

    if ($a === 'add' && $ok) {
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
        }
    }
}

echo json_encode(snap($pdo, $C, $room));
