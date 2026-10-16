<?php require __DIR__.'/../config.php'; header('Content-Type: application/json'); header('Cache-Control: no-store');
if (empty($_SESSION['admin'])) { http_response_code(401); echo '{"error":"login"}'; exit; }
$C=['blue','red','green','yellow'];
function snap($pdo,$C){ $o=[]; foreach($C as $c){
  $s=$pdo->prepare("SELECT value FROM dice_override WHERE player=? AND used=0 ORDER BY id"); $s->execute([$c]); $q=array_map('intval',$s->fetchAll(PDO::FETCH_COLUMN));
  $s=$pdo->prepare("SELECT value FROM dice_override WHERE player=? AND used=1 ORDER BY id DESC LIMIT 8"); $s->execute([$c]); $l=array_map('intval',$s->fetchAll(PDO::FETCH_COLUMN));
  $o[$c]=['queue'=>$q,'last'=>$l]; } return $o; }
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!hash_equals($_SESSION['csrf']??'', $_SERVER['HTTP_X_CSRF']??'')) { http_response_code(403); echo '{"error":"csrf"}'; exit; }
  $in=json_decode(file_get_contents('php://input'),true)?:[]; $a=$in['action']??''; $p=$in['player']??'';
  $ok=in_array($p,$C,true);
  if ($a==='add' && $ok) { $v=(int)($in['value']??0);
    $n=$pdo->prepare("SELECT COUNT(*) FROM dice_override WHERE player=? AND used=0"); $n->execute([$p]);
    if ($v>=1 && $v<=6 && $n->fetchColumn()<50) $pdo->prepare("INSERT INTO dice_override (player,value) VALUES (?,?)")->execute([$p,$v]); }
  elseif ($a==='pop' && $ok) $pdo->prepare("DELETE FROM dice_override WHERE id=(SELECT id FROM dice_override WHERE player=? AND used=0 ORDER BY id DESC LIMIT 1)")->execute([$p]);
  elseif ($a==='clear' && $ok) $pdo->prepare("DELETE FROM dice_override WHERE player=? AND used=0")->execute([$p]);
  elseif ($a==='clear_all') $pdo->exec("DELETE FROM dice_override WHERE used=0");
  elseif ($a==='clear_history') $pdo->exec("DELETE FROM dice_override WHERE used=1");
}
echo json_encode(snap($pdo,$C));
