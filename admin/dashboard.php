<?php require __DIR__.'/../config.php'; if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(16));
$locked=(bool)getenv('ADMIN_PASS'); $msg='';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['np']) && !$locked) {
  if (!hash_equals($_SESSION['csrf'], $_POST['csrf']??'')) $msg='Bad request';
  else { $s=$pdo->prepare("SELECT password_hash FROM admins WHERE id=?"); $s->execute([$_SESSION['admin']]); $h=$s->fetchColumn();
    if (!password_verify($_POST['cp']??'', (string)$h)) $msg='Current password is wrong';
    elseif (strlen($_POST['np'])<6) $msg='New password must be at least 6 characters';
    else { $pdo->prepare("UPDATE admins SET password_hash=? WHERE id=?")->execute([password_hash($_POST['np'],PASSWORD_DEFAULT),$_SESSION['admin']]); $msg='Password changed'; } } }
$P=[['blue','Player 1','Blue'],['red','Player 2','Red'],['green','Player 3','Green'],['yellow','Player 4','Yellow']]; ?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#14161f"><title>Dice control</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body>
<div class="bar"><b>Dice control</b><span><a href="../index.php" target="_blank">Game</a> &nbsp; <a href="logout.php">Log out</a></span></div>
<div class="wrap">
<p class="note">Jis player ka number set karna ho uske neeche 1-6 dabao. Numbers us player ke agle rolls me isi order me aayenge (pehla = NEXT), phir random. Khali ho to random.</p>
<?php foreach($P as [$c,$n,$l]): ?>
<div class="card c-<?=$c?>"><h3><?=$n?> <small><?=$l?></small></h3>
<div class="q" id="q-<?=$c?>"></div>
<div class="dg"><?php for($i=1;$i<=6;$i++) echo "<button data-p='$c' data-a='add' data-v='$i'>$i</button>"; ?></div>
<div class="ac"><button class="g" data-p="<?=$c?>" data-a="pop">Undo last</button><button class="g" data-p="<?=$c?>" data-a="clear">Clear</button></div>
<div class="lr">Last rolled: <span id="l-<?=$c?>">-</span></div></div>
<?php endforeach; ?>
<div class="card"><div class="ac"><button class="g" data-a="clear_all">Clear all numbers</button><button class="g" data-a="clear_history">Clear history</button></div></div>
<?php if(!$locked): ?><form method="post" class="card"><h3>Change password</h3><?php if($msg) echo "<p class='".($msg==='Password changed'?'ok':'err')."'>".htmlspecialchars($msg)."</p>"; ?>
<input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>"><input name="cp" type="password" placeholder="Current password" required><input name="np" type="password" placeholder="New password (6+)" required><button>Save password</button></form><?php endif; ?>
</div>
<script>
const CSRF=<?=json_encode($_SESSION['csrf'])?>,CL=['blue','red','green','yellow'];let busy=false;
async function api(b){const r=await fetch('queue.php',{method:b?'POST':'GET',headers:{'Content-Type':'application/json','X-CSRF':CSRF},body:b?JSON.stringify(b):undefined,cache:'no-store'});
 if(r.status===401){location.href='login.php';return null}if(!r.ok)return null;return r.json()}
function draw(s){if(!s)return;CL.forEach(c=>{const d=s[c];document.getElementById('q-'+c).innerHTML=d.queue.length?d.queue.map((v,i)=>`<i class="chip${i?'':' nx'}">${v}${i?'':'<em>NEXT</em>'}</i>`).join(''):'<span class="rnd">Random (no numbers set)</span>';
 document.getElementById('l-'+c).textContent=d.last.length?d.last.join(' '):'-'})}
document.addEventListener('click',async e=>{const b=e.target.closest('button[data-a]');if(!b)return;busy=true;b.disabled=true;
 draw(await api({action:b.dataset.a,player:b.dataset.p,value:+b.dataset.v||0}));b.disabled=false;busy=false});
async function poll(){if(!busy&&!document.hidden)draw(await api())}poll();setInterval(poll,3000);
</script></body></html>
