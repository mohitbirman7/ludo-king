<?php
require __DIR__ . '/config.php';

$msg = '';
$err = '';
$v = time();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Please enter a valid email address.';
    } elseif (strlen($password) < 4) {
        $err = 'Password must be at least 4 characters long.';
    } elseif ($password !== $confirm) {
        $err = 'Passwords do not match. Please re-enter.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Check if admin already exists
        $s = $pdo->prepare("SELECT id FROM admins WHERE LOWER(username) = LOWER(?)");
        $s->execute([$email]);
        $existing = $s->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $up = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
            $up->execute([$hash, $existing['id']]);
        } else {
            $ins = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
            $ins->execute([$email, $hash]);
        }

        // Auto-login into admin session
        $_SESSION['admin'] = $email;
        $msg = 'Account created successfully! Redirecting to Admin Dashboard...';
        header("Refresh: 1.5; url=admin/dashboard.php");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover,user-scalable=no">
  <title>Create Account - Ludo King</title>
  <link rel="icon" type="image/png" href="assets/icons/icon-192.png?v=<?=$v?>">
  <style>
    * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; user-select: none; }
    html {
      margin: 0; padding: 0; width: 100%; height: 100%;
    }
    body {
      margin: 0; padding: 0; width: 100%; min-height: 100%;
      background: radial-gradient(circle at 50% 35%, #1852b4 0%, #0a2d75 55%, #05163e 100%);
      font-family: system-ui, -apple-system, sans-serif; color: #ffffff;
      display: flex; justify-content: center; align-items: center;
    }
    body::before {
      content: ""; position: fixed; inset: 0; opacity: 0.15; pointer-events: none;
      background-image: radial-gradient(#ffffff 1px, transparent 1px);
      background-size: 32px 32px;
    }
    .card {
      position: relative; width: min(92vw, 380px);
      background: linear-gradient(180deg, #10449e 0%, #072b6a 100%);
      border: 3px solid #ffd700; border-radius: 20px;
      padding: 28px 24px 22px; text-align: center;
      box-shadow: 0 12px 35px rgba(0, 0, 0, 0.75), inset 0 1px 0 rgba(255, 255, 255, 0.3);
      z-index: 10;
    }
    .crown { font-size: 38px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6)); margin-bottom: 2px; }
    h1 {
      margin: 0 0 6px; font-size: 26px; font-weight: 900; color: #ffd700;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8); letter-spacing: 0.5px;
    }
    p.sub { margin: 0 0 20px; font-size: 13px; color: #c0d5ff; }
    .alert {
      padding: 10px 12px; border-radius: 10px; font-size: 13px; font-weight: 700; margin-bottom: 16px;
    }
    .alert.err { background: rgba(229, 57, 53, 0.25); border: 1.5px solid #ff5252; color: #ff8a80; }
    .alert.suc { background: rgba(76, 175, 80, 0.25); border: 1.5px solid #4caf50; color: #a5d6a7; }
    .form-group { text-align: left; margin-bottom: 14px; }
    label { display: block; font-size: 12px; font-weight: 900; color: #ffd700; margin-bottom: 5px; text-shadow: 0 1px 2px rgba(0,0,0,0.8); }
    input {
      width: 100%; padding: 12px 14px; border-radius: 10px;
      border: 2px solid #3c6dbd; background: #082153; color: #ffffff;
      font-size: 15px; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    input:focus { border-color: #ffd700; box-shadow: 0 0 10px rgba(255, 215, 0, 0.4); }
    .btn-submit {
      width: 100%; margin-top: 8px; padding: 13px; border-radius: 25px;
      border: 2.5px solid #ffffff; background: linear-gradient(180deg, #ffb300, #ff6f00);
      color: #ffffff; font-size: 16px; font-weight: 900; cursor: pointer;
      box-shadow: 0 5px 15px rgba(255, 111, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.5);
    }
    .btn-submit:active { transform: scale(0.97); }
    .links { margin-top: 18px; font-size: 13px; display: flex; justify-content: space-between; }
    .links a { color: #ffd700; text-decoration: none; font-weight: 700; }
    .links a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="card">
    <div class="crown">👑</div>
    <h1>Create Account</h1>
    <p class="sub">Set up your Admin Account for Ludo King</p>

    <?php if ($err): ?>
      <div class="alert err">⚠️ <?=htmlspecialchars($err)?></div>
    <?php elseif ($msg): ?>
      <div class="alert suc">✅ <?=htmlspecialchars($msg)?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" required placeholder="yourname@gmail.com" value="<?=htmlspecialchars($_POST['email'] ?? '')?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required placeholder="Enter new password">
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required placeholder="Re-enter password">
      </div>
      <button type="submit" class="btn-submit">Generate Account & Go to Admin</button>
    </form>

    <div class="links">
      <a href="index.php">← Back to Game</a>
      <a href="admin/login.php">Admin Login →</a>
    </div>
  </div>

  <script src="assets/js/security.js?v=<?=$v?>"></script>
</body>
</html>
