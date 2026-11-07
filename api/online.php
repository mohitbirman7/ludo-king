<?php
/**
 * Ludo King - Online Multiplayer Real-time Engine
 * - Isolated multi-room architecture (supports 5+ concurrent matches with zero data overlap)
 * - 6-digit room code generation & join requests with Host acceptance
 * - Full integration with Admin Panel dice overrides per room
 * - Complete server-side anti-cheat verification and prepared statements
 */
require __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$action = trim($_REQUEST['action'] ?? '');
$room_code = trim($_REQUEST['room_code'] ?? $_REQUEST['code'] ?? '');
$token = trim($_REQUEST['token'] ?? '');

$ORDER = ['blue', 'red', 'green', 'yellow'];
$SAFE_POS = [0, 8, 13, 21, 26, 34, 39, 47];
$START_OFFSET = ['red' => 0, 'green' => 13, 'yellow' => 26, 'blue' => 39];

try {
    switch ($action) {
        case 'create_room':
            $host_name = htmlspecialchars(trim($_POST['name'] ?? 'Host'), ENT_QUOTES, 'UTF-8');
            $max_players = max(2, min(4, (int)($_POST['players'] ?? 4)));

            // Generate unique 6-digit numeric room code
            do {
                $code = sprintf('%06d', random_int(100000, 999999));
                $st = $pdo->prepare("SELECT id FROM online_rooms WHERE room_code = ?");
                $st->execute([$code]);
            } while ($st->fetch());

            $host_token = bin2hex(random_bytes(16));

            $pdo->beginTransaction();

            $ins = $pdo->prepare("INSERT INTO online_rooms 
                (room_code, host_name, host_token, max_players, status, turn_color, dice_state) 
                VALUES (?, ?, ?, ?, 'waiting', 'blue', 'roll')");
            $ins->execute([$code, $host_name, $host_token, $max_players]);

            // Host is Player 0 (Blue)
            $pIns = $pdo->prepare("INSERT INTO online_players 
                (room_code, player_name, player_token, color, seat_index, status) 
                VALUES (?, ?, ?, 'blue', 0, 'accepted')");
            $pIns->execute([$code, $host_name, $host_token]);

            // Initialize 4 tokens for Blue
            $tIns = $pdo->prepare("INSERT INTO online_tokens (room_code, color, token_index, pos) VALUES (?, 'blue', ?, -1)");
            for ($i = 0; $i < 4; $i++) {
                $tIns->execute([$code, $i]);
            }

            $pdo->commit();

            echo json_encode([
                'ok' => true,
                'room_code' => $code,
                'token' => $host_token,
                'color' => 'blue',
                'is_host' => true
            ]);
            break;

        case 'join_room':
            $guest_name = htmlspecialchars(trim($_POST['name'] ?? 'Friend'), ENT_QUOTES, 'UTF-8');
            
            $st = $pdo->prepare("SELECT * FROM online_rooms WHERE room_code = ?");
            $st->execute([$room_code]);
            $room = $st->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                echo json_encode(['ok' => false, 'error' => 'Invalid Room Code. Please check and try again.']);
                exit;
            }

            if ($room['status'] !== 'waiting') {
                echo json_encode(['ok' => false, 'error' => 'Game has already started in this room.']);
                exit;
            }

            // Count accepted players
            $cntSt = $pdo->prepare("SELECT COUNT(*) FROM online_players WHERE room_code = ? AND status = 'accepted'");
            $cntSt->execute([$room_code]);
            $acceptedCount = (int)$cntSt->fetchColumn();

            if ($acceptedCount >= (int)$room['max_players']) {
                echo json_encode(['ok' => false, 'error' => 'Room is already full.']);
                exit;
            }

            // Assign seat color based on player count
            // 2 Players: Blue vs Green
            // 3 Players: Blue, Red, Green
            // 4 Players: Blue, Red, Green, Yellow
            $colorPool = ['blue', 'red', 'green', 'yellow'];
            if ((int)$room['max_players'] === 2) {
                $colorPool = ['blue', 'green'];
            }

            // Find used colors
            $uSt = $pdo->prepare("SELECT color FROM online_players WHERE room_code = ?");
            $uSt->execute([$room_code]);
            $usedColors = $uSt->fetchAll(PDO::FETCH_COLUMN);

            $assignedColor = null;
            foreach ($colorPool as $c) {
                if (!in_array($c, $usedColors, true)) {
                    $assignedColor = $c;
                    break;
                }
            }

            if (!$assignedColor) {
                echo json_encode(['ok' => false, 'error' => 'No seats available.']);
                exit;
            }

            $guest_token = bin2hex(random_bytes(16));
            $seat_index = array_search($assignedColor, $ORDER, true);

            $pIns = $pdo->prepare("INSERT INTO online_players 
                (room_code, player_name, player_token, color, seat_index, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')");
            $pIns->execute([$room_code, $guest_name, $guest_token, $assignedColor, $seat_index]);

            echo json_encode([
                'ok' => true,
                'room_code' => $room_code,
                'token' => $guest_token,
                'color' => $assignedColor,
                'is_host' => false,
                'status' => 'pending'
            ]);
            break;

        case 'room_status':
            $st = $pdo->prepare("SELECT * FROM online_rooms WHERE room_code = ?");
            $st->execute([$room_code]);
            $room = $st->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                echo json_encode(['ok' => false, 'error' => 'Room not found.']);
                exit;
            }

            $pSt = $pdo->prepare("SELECT id, player_name, color, seat_index, status FROM online_players WHERE room_code = ? ORDER BY id ASC");
            $pSt->execute([$room_code]);
            $players = $pSt->fetchAll(PDO::FETCH_ASSOC);

            // Check if current user is host
            $is_host = ($token !== '' && $token === $room['host_token']);

            // Find this user's player record
            $myRecord = null;
            if ($token !== '') {
                $mSt = $pdo->prepare("SELECT id, player_name, color, status FROM online_players WHERE room_code = ? AND player_token = ?");
                $mSt->execute([$room_code, $token]);
                $myRecord = $mSt->fetch(PDO::FETCH_ASSOC);
            }

            echo json_encode([
                'ok' => true,
                'room' => [
                    'code' => $room['room_code'],
                    'status' => $room['status'],
                    'max_players' => (int)$room['max_players'],
                    'host_name' => $room['host_name']
                ],
                'is_host' => $is_host,
                'my_status' => $myRecord ? $myRecord['status'] : null,
                'my_color' => $myRecord ? $myRecord['color'] : null,
                'players' => $players
            ]);
            break;

        case 'accept_player':
            // Host accepts a pending join request
            $player_id = (int)($_POST['player_id'] ?? 0);

            $rSt = $pdo->prepare("SELECT host_token FROM online_rooms WHERE room_code = ?");
            $rSt->execute([$room_code]);
            $hostToken = $rSt->fetchColumn();

            if (!$hostToken || $hostToken !== $token) {
                echo json_encode(['ok' => false, 'error' => 'Unauthorized. Only host can accept players.']);
                exit;
            }

            $pSt = $pdo->prepare("SELECT color FROM online_players WHERE id = ? AND room_code = ?");
            $pSt->execute([$player_id, $room_code]);
            $pColor = $pSt->fetchColumn();

            if (!$pColor) {
                echo json_encode(['ok' => false, 'error' => 'Player not found.']);
                exit;
            }

            $pdo->beginTransaction();

            $up = $pdo->prepare("UPDATE online_players SET status = 'accepted' WHERE id = ?");
            $up->execute([$player_id]);

            // Initialize tokens for accepted player
            $tIns = $pdo->prepare("INSERT OR IGNORE INTO online_tokens (room_code, color, token_index, pos) VALUES (?, ?, ?, -1)");
            for ($i = 0; $i < 4; $i++) {
                $tIns->execute([$room_code, $pColor, $i]);
            }

            $pdo->commit();

            echo json_encode(['ok' => true]);
            break;

        case 'start_game':
            $rSt = $pdo->prepare("SELECT host_token FROM online_rooms WHERE room_code = ?");
            $rSt->execute([$room_code]);
            $hostToken = $rSt->fetchColumn();

            if (!$hostToken || $hostToken !== $token) {
                echo json_encode(['ok' => false, 'error' => 'Unauthorized. Only host can start game.']);
                exit;
            }

            $cntSt = $pdo->prepare("SELECT COUNT(*) FROM online_players WHERE room_code = ? AND status = 'accepted'");
            $cntSt->execute([$room_code]);
            $accCount = (int)$cntSt->fetchColumn();

            if ($accCount < 2) {
                echo json_encode(['ok' => false, 'error' => 'At least 2 players must be accepted to start!']);
                exit;
            }

            $up = $pdo->prepare("UPDATE online_rooms SET status = 'active', turn_color = 'blue', dice_state = 'roll', dice_value = 0, updated_at = CURRENT_TIMESTAMP WHERE room_code = ?");
            $up->execute([$room_code]);

            echo json_encode(['ok' => true]);
            break;

        case 'game_state':
            $rSt = $pdo->prepare("SELECT * FROM online_rooms WHERE room_code = ?");
            $rSt->execute([$room_code]);
            $room = $rSt->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                echo json_encode(['ok' => false, 'error' => 'Room not found.']);
                exit;
            }

            $pSt = $pdo->prepare("SELECT player_name, color, seat_index FROM online_players WHERE room_code = ? AND status = 'accepted' ORDER BY seat_index ASC");
            $pSt->execute([$room_code]);
            $players = $pSt->fetchAll(PDO::FETCH_ASSOC);

            $tSt = $pdo->prepare("SELECT color, token_index, pos FROM online_tokens WHERE room_code = ? ORDER BY color, token_index");
            $tSt->execute([$room_code]);
            $tokens = $tSt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'ok' => true,
                'room' => [
                    'code' => $room['room_code'],
                    'status' => $room['status'],
                    'turn_color' => $room['turn_color'],
                    'dice_value' => (int)$room['dice_value'],
                    'dice_state' => $room['dice_state'],
                    'roll_seq' => (int)($room['roll_seq'] ?? 0),
                    'move_seq' => (int)($room['move_seq'] ?? 0),
                    'last_move' => !empty($room['last_move_json']) ? json_decode($room['last_move_json'], true) : null,
                    'winner' => $room['winner']
                ],
                'players' => $players,
                'tokens' => $tokens
            ]);
            break;

        case 'roll_dice':
            $rSt = $pdo->prepare("SELECT * FROM online_rooms WHERE room_code = ?");
            $rSt->execute([$room_code]);
            $room = $rSt->fetch(PDO::FETCH_ASSOC);

            if (!$room || $room['status'] !== 'active') {
                echo json_encode(['ok' => false, 'error' => 'Game is not active.']);
                exit;
            }

            if ($room['dice_state'] !== 'roll') {
                echo json_encode(['ok' => false, 'error' => 'Dice already rolled. Move a token!']);
                exit;
            }

            // Verify player token matches current turn_color
            $pSt = $pdo->prepare("SELECT color FROM online_players WHERE room_code = ? AND player_token = ? AND status = 'accepted'");
            $pSt->execute([$room_code, $token]);
            $myColor = $pSt->fetchColumn();

            if (!$myColor || $myColor !== $room['turn_color']) {
                echo json_encode(['ok' => false, 'error' => 'Not your turn!']);
                exit;
            }

            // CHECK ADMIN OVERRIDE FOR THIS ROOM & COLOR!
            $d = null;
            $ovSt = $pdo->prepare("SELECT id, value FROM dice_override WHERE room_code = ? AND player = ? AND used = 0 ORDER BY id LIMIT 1");
            $ovSt->execute([$room_code, $myColor]);
            $ov = $ovSt->fetch(PDO::FETCH_ASSOC);

            if (!$ov) {
                // Check general local override as fallback
                $ovSt = $pdo->prepare("SELECT id, value FROM dice_override WHERE room_code = 'local' AND player = ? AND used = 0 ORDER BY id LIMIT 1");
                $ovSt->execute([$myColor]);
                $ov = $ovSt->fetch(PDO::FETCH_ASSOC);
            }

            if ($ov) {
                $d = (int)$ov['value'];
                $pdo->prepare("UPDATE dice_override SET used = 1 WHERE id = ?")->execute([$ov['id']]);
            } else {
                $d = random_int(1, 6);
            }

            // Check if player has any possible moves
            $tokSt = $pdo->prepare("SELECT token_index, pos FROM online_tokens WHERE room_code = ? AND color = ?");
            $tokSt->execute([$room_code, $myColor]);
            $myTokens = $tokSt->fetchAll(PDO::FETCH_ASSOC);

            $hasMove = false;
            foreach ($myTokens as $tk) {
                $pos = (int)$tk['pos'];
                if ($pos === -1 && $d === 6) { $hasMove = true; break; }
                if ($pos >= 0 && ($pos + $d) <= 56) { $hasMove = true; break; }
            }

            $rollSeq = (int)($room['roll_seq'] ?? 0) + 1;

            if ($hasMove) {
                // Player must now pick a token
                $pdo->prepare("UPDATE online_rooms SET dice_value = ?, dice_state = 'move', roll_seq = ?, updated_at = CURRENT_TIMESTAMP WHERE room_code = ?")
                    ->execute([$d, $rollSeq, $room_code]);
                echo json_encode(['ok' => true, 'dice' => $d, 'state' => 'move', 'roll_seq' => $rollSeq, 'turn' => $myColor]);
            } else {
                // No valid moves! Turn passes to next player (unless rolled 6)
                $nextColor = getNextTurnColor($pdo, $room_code, $myColor);
                $pdo->prepare("UPDATE online_rooms SET dice_value = ?, dice_state = 'roll', roll_seq = ?, turn_color = ?, updated_at = CURRENT_TIMESTAMP WHERE room_code = ?")
                    ->execute([$d, $rollSeq, $nextColor, $room_code]);
                echo json_encode(['ok' => true, 'dice' => $d, 'state' => 'pass', 'roll_seq' => $rollSeq, 'next_turn' => $nextColor, 'turn' => $myColor]);
            }
            break;

        case 'move_token':
            $token_index = (int)($_POST['token_index'] ?? -1);

            $rSt = $pdo->prepare("SELECT * FROM online_rooms WHERE room_code = ?");
            $rSt->execute([$room_code]);
            $room = $rSt->fetch(PDO::FETCH_ASSOC);

            if (!$room || $room['status'] !== 'active' || $room['dice_state'] !== 'move') {
                echo json_encode(['ok' => false, 'error' => 'Cannot move at this time.']);
                exit;
            }

            $pSt = $pdo->prepare("SELECT color FROM online_players WHERE room_code = ? AND player_token = ? AND status = 'accepted'");
            $pSt->execute([$room_code, $token]);
            $myColor = $pSt->fetchColumn();

            if (!$myColor || $myColor !== $room['turn_color']) {
                echo json_encode(['ok' => false, 'error' => 'Not your turn!']);
                exit;
            }

            $d = (int)$room['dice_value'];

            // Get current token position
            $tkSt = $pdo->prepare("SELECT pos FROM online_tokens WHERE room_code = ? AND color = ? AND token_index = ?");
            $tkSt->execute([$room_code, $myColor, $token_index]);
            $currentPos = $tkSt->fetchColumn();

            if ($currentPos === false) {
                echo json_encode(['ok' => false, 'error' => 'Token not found.']);
                exit;
            }

            $currentPos = (int)$currentPos;
            $newPos = $currentPos;

            if ($currentPos === -1) {
                if ($d !== 6) {
                    echo json_encode(['ok' => false, 'error' => 'Need a 6 to open token from yard!']);
                    exit;
                }
                $newPos = 0;
            } else {
                $newPos = $currentPos + $d;
                if ($newPos > 56) {
                    echo json_encode(['ok' => false, 'error' => 'Cannot overshoot home!']);
                    exit;
                }
            }

            $pdo->beginTransaction();

            // Update token position
            $upTk = $pdo->prepare("UPDATE online_tokens SET pos = ? WHERE room_code = ? AND color = ? AND token_index = ?");
            $upTk->execute([$newPos, $room_code, $myColor, $token_index]);

            // Check for capture (kill) on track (positions 0 to 50)
            $isKill = false;
            $killedTarget = null;
            if ($newPos >= 0 && $newPos <= 50) {
                $myAbs = ($START_OFFSET[$myColor] + $newPos) % 52;
                if (!in_array($myAbs, $SAFE_POS, true)) {
                    // Check other players' tokens on this absolute square
                    $oppSt = $pdo->prepare("SELECT color, token_index, pos FROM online_tokens WHERE room_code = ? AND color <> ? AND pos >= 0 AND pos <= 50");
                    $oppSt->execute([$room_code, $myColor]);
                    $oppTokens = $oppSt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($oppTokens as $ot) {
                        $oppAbs = ($START_OFFSET[$ot['color']] + (int)$ot['pos']) % 52;
                        if ($oppAbs === $myAbs) {
                            // KILL OPPONENT TOKEN!
                            $isKill = true;
                            $killedTarget = [
                                'color' => $ot['color'],
                                'token_index' => (int)$ot['token_index'],
                                'from_pos' => (int)$ot['pos']
                            ];
                            $pdo->prepare("UPDATE online_tokens SET pos = -1 WHERE room_code = ? AND color = ? AND token_index = ?")
                                ->execute([$room_code, $ot['color'], $ot['token_index']]);
                        }
                    }
                }
            }

            $moveSeq = (int)($room['move_seq'] ?? 0) + 1;
            $lastMove = [
                'seq' => $moveSeq,
                'color' => $myColor,
                'token_index' => $token_index,
                'from_pos' => $currentPos,
                'to_pos' => $newPos,
                'killed' => $killedTarget
            ];
            $lastMoveJson = json_encode($lastMove);

            // Check for win (all 4 tokens at 56)
            $finSt = $pdo->prepare("SELECT COUNT(*) FROM online_tokens WHERE room_code = ? AND color = ? AND pos = 56");
            $finSt->execute([$room_code, $myColor]);
            $isWinner = ((int)$finSt->fetchColumn() === 4);

            if ($isWinner) {
                $pdo->prepare("UPDATE online_rooms SET status = 'finished', winner = ?, move_seq = ?, last_move_json = ?, updated_at = CURRENT_TIMESTAMP WHERE room_code = ?")
                    ->execute([$myColor, $moveSeq, $lastMoveJson, $room_code]);
                $pdo->commit();
                echo json_encode(['ok' => true, 'kill' => $isKill, 'winner' => $myColor, 'last_move' => $lastMove]);
                exit;
            }

            // Turn progression: player gets another turn if rolled 6 or killed opponent
            $bonusRoll = ($d === 6 || $isKill);
            if ($bonusRoll) {
                $pdo->prepare("UPDATE online_rooms SET dice_state = 'roll', move_seq = ?, last_move_json = ?, updated_at = CURRENT_TIMESTAMP WHERE room_code = ?")
                    ->execute([$moveSeq, $lastMoveJson, $room_code]);
                $pdo->commit();
                echo json_encode(['ok' => true, 'kill' => $isKill, 'bonus' => true, 'turn' => $myColor, 'last_move' => $lastMove]);
            } else {
                $nextColor = getNextTurnColor($pdo, $room_code, $myColor);
                $pdo->prepare("UPDATE online_rooms SET dice_state = 'roll', turn_color = ?, move_seq = ?, last_move_json = ?, updated_at = CURRENT_TIMESTAMP WHERE room_code = ?")
                    ->execute([$nextColor, $moveSeq, $lastMoveJson, $room_code]);
                $pdo->commit();
                echo json_encode(['ok' => true, 'kill' => $isKill, 'bonus' => false, 'turn' => $nextColor, 'last_move' => $lastMove]);
            }
            break;

        default:
            echo json_encode(['ok' => false, 'error' => 'Unknown action.']);
            break;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['ok' => false, 'error' => 'Server Error: ' . $e->getMessage()]);
}

/**
 * Helper to advance turn in ORDER among active accepted players
 */
function getNextTurnColor($pdo, $room_code, $currentColor) {
    global $ORDER;
    $pSt = $pdo->prepare("SELECT color FROM online_players WHERE room_code = ? AND status = 'accepted'");
    $pSt->execute([$room_code]);
    $activeColors = $pSt->fetchAll(PDO::FETCH_COLUMN);

    $idx = array_search($currentColor, $ORDER, true);
    for ($i = 1; $i <= 4; $i++) {
        $next = $ORDER[($idx + $i) % 4];
        if (in_array($next, $activeColors, true)) {
            return $next;
        }
    }
    return $currentColor;
}
