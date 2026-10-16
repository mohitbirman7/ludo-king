<?php
require __DIR__.'/../config.php';
header('Content-Type: application/json');
$p = $_GET['player'] ?? '';
$s = $pdo->prepare("SELECT id,value FROM dice_override WHERE player=? AND used=0 ORDER BY id LIMIT 1");
$s->execute([$p]);
$r = $s->fetch(PDO::FETCH_ASSOC);
if ($r) { $d = (int)$r['value']; $pdo->prepare("UPDATE dice_override SET used=1 WHERE id=?")->execute([$r['id']]); }
else $d = random_int(1, 6);
echo json_encode(['dice' => $d]);
