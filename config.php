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

// Check if room_code and created_at columns exist in dice_override, if not add them
$cols = $pdo->query("PRAGMA table_info(dice_override)")->fetchAll(PDO::FETCH_ASSOC);
$colNames = array_column($cols, 'name');
if (!in_array('room_code', $colNames, true)) {
    $pdo->exec("ALTER TABLE dice_override ADD COLUMN room_code TEXT DEFAULT 'local'");
}
if (!in_array('created_at', $colNames, true)) {
    $pdo->exec("ALTER TABLE dice_override ADD COLUMN created_at DATETIME DEFAULT NULL");
    $pdo->exec("UPDATE dice_override SET created_at = datetime('now') WHERE created_at IS NULL");
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
    roll_seq INTEGER DEFAULT 0,
    move_seq INTEGER DEFAULT 0,
    last_move_json TEXT DEFAULT NULL,
    winner TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Migrate online_rooms columns if missing
$roomCols = $pdo->query("PRAGMA table_info(online_rooms)")->fetchAll(PDO::FETCH_ASSOC);
$roomColNames = array_column($roomCols, 'name');
if (!in_array('roll_seq', $roomColNames, true)) {
    $pdo->exec("ALTER TABLE online_rooms ADD COLUMN roll_seq INTEGER DEFAULT 0");
}
if (!in_array('move_seq', $roomColNames, true)) {
    $pdo->exec("ALTER TABLE online_rooms ADD COLUMN move_seq INTEGER DEFAULT 0");
}
if (!in_array('last_move_json', $roomColNames, true)) {
    $pdo->exec("ALTER TABLE online_rooms ADD COLUMN last_move_json TEXT DEFAULT NULL");
}

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
    // Default fallback: admin / mohit@1760
    $pdo->prepare("INSERT INTO admins (username,password_hash) VALUES (?,?)")->execute(['admin', password_hash('mohit@1760', PASSWORD_DEFAULT)]);
}

/**
 * Auto-cleanup function:
 * Automatically purges match and dice data older than 1 day (or finished/inactive rooms).
 * Cascades to players and tokens, keeping the SQLite database small, fast, and free of obsolete data.
 */
function purge_old_ludo_data($pdo, $force = false) {
    try {
        if ($force) {
            // Force clean: delete finished rooms or rooms inactive for > 4 hours
            $pdo->exec("DELETE FROM online_rooms WHERE updated_at < datetime('now', '-4 hours') OR status = 'finished' OR created_at < datetime('now', '-1 day')");
        } else {
            // Standard auto-clean: delete rooms older than 1 day (24 hours) or finished > 6 hours
            $pdo->exec("DELETE FROM online_rooms WHERE updated_at < datetime('now', '-1 day') OR created_at < datetime('now', '-1 day') OR (status = 'finished' AND updated_at < datetime('now', '-6 hours'))");
        }

        // Cascade delete orphaned players & tokens for purged rooms
        $pdo->exec("DELETE FROM online_players WHERE room_code NOT IN (SELECT room_code FROM online_rooms)");
        $pdo->exec("DELETE FROM online_tokens WHERE room_code NOT IN (SELECT room_code FROM online_rooms)");

        // Purge dice overrides:
        // 1. Any override for a room that no longer exists
        $pdo->exec("DELETE FROM dice_override WHERE room_code != 'local' AND room_code NOT IN (SELECT room_code FROM online_rooms)");
        // 2. Used overrides older than 1 day
        $pdo->exec("DELETE FROM dice_override WHERE used = 1 AND (created_at < datetime('now', '-1 day') OR created_at IS NULL)");
        // 3. Keep maximum 30 past roll history records so the table never bloats
        $pdo->exec("DELETE FROM dice_override WHERE used = 1 AND id NOT IN (SELECT id FROM dice_override WHERE used = 1 ORDER BY id DESC LIMIT 30)");
    } catch (Exception $e) {}
}

// Automatically invoke purge during routine access
purge_old_ludo_data($pdo);
