<?php
require __DIR__.'/../config.php';
header('Content-Type: application/json');

$p = trim($_GET['player'] ?? '');
$room = trim($_GET['room'] ?? 'local');

// Check room-specific override first
$s = $pdo->prepare("SELECT id, value FROM dice_override WHERE player=? AND room_code=? AND used=0 ORDER BY id LIMIT 1");
$s->execute([$p, $room]);
$r = $s->fetch(PDO::FETCH_ASSOC);

if (!$r && $room !== 'local') {
    // Fallback to general override
    $s = $pdo->prepare("SELECT id, value FROM dice_override WHERE player=? AND room_code='local' AND used=0 ORDER BY id LIMIT 1");
    $s->execute([$p]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
}

if ($r) {
    $d = (int)$r['value'];
    $pdo->prepare("UPDATE dice_override SET used=1 WHERE id=?")->execute([$r['id']]);
} else {
    $d = random_int(1, 6);
}

echo json_encode(['dice' => $d]);
