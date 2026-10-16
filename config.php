<?php
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax']);
session_start();
$dd = getenv('DATA_DIR') ?: __DIR__.'/data';
if (!is_dir($dd)) @mkdir($dd, 0775, true);
$pdo = new PDO('sqlite:'.$dd.'/ludo.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("PRAGMA busy_timeout=4000");
$pdo->exec("CREATE TABLE IF NOT EXISTS dice_override (id INTEGER PRIMARY KEY AUTOINCREMENT, player TEXT, value INTEGER, used INTEGER DEFAULT 0)");
$pdo->exec("CREATE TABLE IF NOT EXISTS admins (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password_hash TEXT)");
$envPass = getenv('ADMIN_PASS');
if ($envPass) {
  // Render: login ADMIN_USER / ADMIN_PASS environment variables se banta hai (restart ke baad bhi same rehta hai)
  $envUser = getenv('ADMIN_USER') ?: 'admin';
  $s = $pdo->prepare("SELECT id,password_hash FROM admins WHERE username=?"); $s->execute([$envUser]); $r = $s->fetch(PDO::FETCH_ASSOC);
  if (!$r) $pdo->prepare("INSERT INTO admins (username,password_hash) VALUES (?,?)")->execute([$envUser, password_hash($envPass, PASSWORD_DEFAULT)]);
  elseif (!password_verify($envPass, $r['password_hash'])) $pdo->prepare("UPDATE admins SET password_hash=? WHERE id=?")->execute([password_hash($envPass, PASSWORD_DEFAULT), $r['id']]);
  $pdo->prepare("DELETE FROM admins WHERE username<>?")->execute([$envUser]);
} elseif (!$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn()) {
  // Default login: admin / admin123  (dashboard me jaake turant badal lena)
  $pdo->prepare("INSERT INTO admins (username,password_hash) VALUES (?,?)")->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT)]);
}
