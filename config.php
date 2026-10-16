<?php
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax']);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Response Headers (Anti-Sniffing, Anti-Clickjacking, Anti-XSS)
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

$dd = getenv('DATA_DIR') ?: __DIR__.'/data';
if (!is_dir($dd)) @mkdir($dd, 0775, true);

// Protect data directory from direct web download
$htaccess = $dd . '/.htaccess';
if (!file_exists($htaccess)) {
    @file_put_contents($htaccess, "Deny from all\n");
}

$pdo = new PDO('sqlite:'.$dd.'/ludo.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("PRAGMA busy_timeout=4000");

// 1. Dice Override Table (Supports both local pass-n-play and multi-room online games)
$pdo->exec("CREATE TABLE IF NOT EXISTS dice_override (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_code TEXT DEFAULT 'local',
    player TEXT,
    value INTEGER,
    used INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Check if room_code column exists in dice_override, if not add it
$cols = $pdo->query("PRAGMA table_info(dice_override)")->fetchAll(PDO::FETCH_ASSOC);
$hasRoom = false;
foreach ($cols as $c) {
    if ($c['name'] === 'room_code') { $hasRoom = true; break; }
}
if (!$hasRoom) {
    $pdo->exec("ALTER TABLE dice_override ADD COLUMN room_code TEXT DEFAULT 'local'");
}

// 2. Admins Table (Stores email/username and password_hash)
$pdo->exec("CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    password_hash TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 3. Online Rooms Table (Multi-room isolation, supports 5+ concurrent games without data collision)
$pdo->exec("CREATE TABLE IF NOT EXISTS online_rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_code TEXT UNIQUE,
    host_name TEXT,
    host_token TEXT,
    max_players INTEGER DEFAULT 4,
    status TEXT DEFAULT 'waiting',
    turn_color TEXT DEFAULT 'blue',
    turn_index INTEGER DEFAULT 0,
    dice_value INTEGER DEFAULT 0,
    dice_state TEXT DEFAULT 'roll',
    winner TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 4. Online Players Table (Seats & tokens per room)
$pdo->exec("CREATE TABLE IF NOT EXISTS online_players (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_code TEXT,
    player_name TEXT,
    player_token TEXT,
    color TEXT,
    seat_index INTEGER,
    status TEXT DEFAULT 'pending',
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(room_code, color),
    UNIQUE(room_code, player_token)
)");

// 5. Online Token Positions Table
$pdo->exec("CREATE TABLE IF NOT EXISTS online_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_code TEXT,
    color TEXT,
    token_index INTEGER,
    pos INTEGER DEFAULT -1,
    UNIQUE(room_code, color, token_index)
)");

// Admin credential synchronization
$envPass = getenv('ADMIN_PASS');
if ($envPass) {
    $envUser = getenv('ADMIN_USER') ?: 'admin';
    $s = $pdo->prepare("SELECT id, password_hash FROM admins WHERE username=?");
    $s->execute([$envUser]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    if (!$r) {
        $pdo->prepare("INSERT INTO admins (username,password_hash) VALUES (?,?)")->execute([$envUser, password_hash($envPass, PASSWORD_DEFAULT)]);
    } elseif (!password_verify($envPass, $r['password_hash'])) {
        $pdo->prepare("UPDATE admins SET password_hash=? WHERE id=?")->execute([password_hash($envPass, PASSWORD_DEFAULT), $r['id']]);
    }
} elseif (!$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn()) {
    // Default fallback: admin / admin123
    $pdo->prepare("INSERT INTO admins (username,password_hash) VALUES (?,?)")->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT)]);
}
