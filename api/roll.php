<?php
require __DIR__.'/../config.php';
session_write_close();
header('Content-Type: application/json');
header('Cache-Control: no-store');

$p = trim($_GET['player'] ?? '');
$room = trim($_GET['room'] ?? 'local');

// A) Mark a peeked number as used (called right after the dice is rolled)
if (isset($_GET['use'])) {
    $pdo->prepare("UPDATE dice_override SET used=1 WHERE id=? AND player=?")
        ->execute([(int)$_GET['use'], $p]);
    echo json_encode(['ok' => true]);
    exit;
}

// B) Find the next unused admin number for this player
$s = $pdo->prepare("SELECT id, value FROM dice_override WHERE player=? AND room_code=? AND used=0 ORDER BY id LIMIT 1");
$s->execute([$p, $room]);
$r = $s->fetch(PDO::FETCH_ASSOC);

if (!$r && $room !== 'local') {
    // Fallback to general override
    $s = $pdo->prepare("SELECT id, value FROM dice_override WHERE player=? AND room_code='local' AND used=0 ORDER BY id LIMIT 1");
    $s->execute([$p]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
}

// Peek only: show the number but do NOT mark it used
if (isset($_GET['peek'])) {
    echo json_encode($r ? ['id' => (int)$r['id'], 'dice' => (int)$r['value']] : ['id' => 0, 'dice' => 0]);
    exit;
}

// Original behaviour (kept as is)
if ($r) {
    $d = (int)$r['value'];
    $pdo->prepare("UPDATE dice_override SET used=1 WHERE id=?")->execute([$r['id']]);
} else {
    $d = random_int(1, 6);
}

echo json_encode(['dice' => $d]);
