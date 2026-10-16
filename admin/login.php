<?php require __DIR__.'/../config.php'; $err='';
if (!empty($_SESSION['admin'])) { header('Location: dashboard.php'); exit; }
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $s=$pdo->prepare("SELECT * FROM admins WHERE username=?"); $s->execute([$_POST['u']??'']); $a=$s->fetch(PDO::FETCH_ASSOC);
  if ($a && password_verify($_POST['p']??'', $a['password_hash'])) { session_regenerate_id(true); $_SESSION['admin']=$a['id']; header('Location: dashboard.php'); exit; }
  usleep(800000); $err='Wrong username or password';
} ?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin login</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body>
<form method="post" class="box"><h1>Admin login</h1><?php if($err) echo "<p class='err'>".htmlspecialchars($err)."</p>"; ?>
<input name="u" placeholder="Username" autocomplete="username" required><input name="p" type="password" placeholder="Password" autocomplete="current-password" required><button>Log in</button></form></body></html>
