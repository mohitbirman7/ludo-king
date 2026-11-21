# 👑 Ludo King — Full-Stack Web & PWA Game with Real-Time Telemetry & Admin Engine

> **College Project Submission & Viva Defense Documentation**  
> **Target Directory:** `C:\xampp\htdocs\ludo-king-main`  
> **Technology Stack:** HTML5 Canvas & DOM, CSS3 3D Transforms, Web Audio API, JavaScript (ES6+), PHP 8+, SQLite3 PDO, PWA Service Worker.

---

## 📌 Executive Summary & Abstract
This project is an authentic, production-grade recreation of the globally renowned mobile board game **Ludo King**. Built as a hybrid progressive web application (PWA) with full offline support, cross-device responsiveness, and real-time multiplayer capability, the application achieves a visual and physical fidelity indistinguishable from the official mobile app.

In addition to traditional **Local Pass 'N' Play** and **Online Multiplayer (Room Code)** modes, the system features a **Real-Time Administrative Telemetry & Control Engine**. This engine enables an administrator to monitor live matches in real time (tracking active turns, remaining countdown seconds, and roll telemetry) and remotely assign predetermined dice rolls to any player within a designated time window, seamlessly falling back to cryptographically secure random number generation when no administrative override is active.

---

## 🎯 Key Deficiencies Identified & Addressed (Deep Analysis)

| # | Previous Defect / Difference | Root Cause Analysis | Engineering Resolution Implemented |
|---|-----------------------------|--------------------|-------------------------------------|
| **1** | **Dice Roll Delay (Touch Latency)** | Online mode waited for network HTTP response before starting animation; local mode had 300ms mobile touch delay and main-thread interval polling. | **Zero-Delay Tumble Physics Engine**: Dice begins 3D tumble and rattle audio on the exact millisecond of touch (`0ms`); API resolution runs concurrently and settles smoothly on the target face. Added `touch-action: manipulation`. |
| **2** | **Lack of Turn Urgency / Timer** | Previous version had 5 static blinking dots without an actual countdown or auto-roll. | **Authentic 12s Circular Turn Timer**: SVG radial countdown ring with color transitions (Green $\to$ Amber $\to$ Pulsing Crimson), audible warning ticks in the final 3 seconds, and automatic dice roll on timeout. |
| **3** | **Pawn (Goti) Aesthetics & Movement** | Pawns were flat map-marker pin SVGs that slid linearly across tiles without depth. | **Authentic 3D Pawns**: Engineered custom multi-stop radial gradient SVG pawns with glossy spherical heads, neck collars, tapered conic bodies, and drop shadows. Pawns hop in parabolic arcs (`translateY(-14px)`) with authentic step sounds. |
| **4** | **Dice Visual Quality & Signature Details** | Dice cube had flat gray dots on all faces without Asian / Indian Ludo King identity. | **Ivory 3D Dice with Crimson 1 Pip**: Authentic rounded cube geometry with indented pips; Face 1 features the signature large royal crimson dot (`#e71d36`) with metallic sheen. |
| **5** | **Capture & Home Celebrations** | Previous captures were abrupt; no celebratory feedback on bonus turns. | **Strike Impact & Snake Rewind**: Comic impact flash, authentic snake retreat along each visited box, and animated "🎉 BONUS ROLL!" / "👑 GOTI HOME!" celebration banners. |
| **6** | **Admin Remote Dice Control Synchronization** | Admin panel was disconnected from game turn state; required guessing when a player was rolling. | **Real-Time Match Telemetry**: Implemented `game_live_status` table and 1s polling. Admin panel highlights the active player with pulsating gold borders, displays live seconds remaining, and features one-tap dice presets (1–6). |
| **7** | **Single-Screen Viva Presentation Barrier** | Demonstrating cheat features required switching browser tabs during presentation. | **Secret In-Game Cheat Drawer**: Discrete floating controller inside the game screen, triggered by pressing `'A'` on the keyboard or triple-tapping the top logo. |
| **8** | **Authentic Visual Layout & Pawns** | Previous layout differed from official mobile Ludo King UI. | **Exact Ludo King Screenshot Replication**: Circular golden back button `[ ↩ ]`, deep blue diagonal textured wallpaper, dual-box player cards (colored goti square + ivory dice box in gold frame), animated orange pointer arrow pointing at the active player, solid circular yard spots, outline safe stars `☆`, and teardrop pin pawns with 3D notched casino-chip pedestal bases. |
| **9** | **Multiplayer In-Game Voice Chat** | Players had no voice communication during online multiplayer games. | **Full WebRTC P2P Voice Engine**: Low-latency peer-to-peer audio calling via STUN servers. Includes Master Mic toggle (`🎙️`), Master Voice mute (`🔊`), individual per-player speaker mute (`🔊`/`🔇`) on remote cards, and real-time speech activity detection that pulses glowing green auras around active talkers. |
| **10** | **Capture & Open Sounds & Vibration** | Sound effects & vibrations did not match official mobile app. | **Authentic Sound & Haptic Feedback Engine**: Implemented `Snd.open()` chime with 60ms vibration on 6, `Snd.kill()` capture impact sound with intense multi-pulse vibration `[100, 50, 140]`, step sounds, and victory fanfare. |
| **11** | **Application Security & Anti-Brute-Force** | Admin login was vulnerable to dictionary attacks and client code inspection. | **Hardened Security Architecture**: Added IP/session rate-limiting (lockout after 5 failed attempts for 60 seconds), Bcrypt password hashing, session fixation regeneration, anti-inspection shields (`F12`, `Ctrl+Shift+I`, right-click blocking), and direct SQLite `.db` access prevention. |
| **12** | **15-Roll Sequence Queue Engine** | Setting dice values manually before every roll required tedious repetition. | **Multi-Roll Queue Engine (Up to 15 Rolls)**: Admin can pre-program an upcoming sequence of up to 15 rolls per player (`set_sequence` or `append_roll`) or use quick presets (`3x Sixes`, `5x Rush`, `15x Super Chain`). The system serves each queued number in FIFO order on successive turns, and seamlessly falls back to default cryptographic random rolling once the queue finishes. |
| **13** | **Admin Live Room Voice Eavesdropping** | Administrator could not monitor real-time voice communications in multi-room matches. | **Silent WebRTC Room Voice Monitor**: Admin Dashboard features a live audio receiver (`recvonly`) connecting to any active online room via STUN. Admin silently listens to player voice chats in real time with individual volume control, live speaking meters, and soundwave activity detection without broadcasting any audio to players. |

---

## 🏗️ System Architecture & Database Schema

### 1. Database Architecture (`data/ludo.db`)
Implemented via SQLite3 PDO with WAL mode and busy timeouts:

- **`dice_override`**:
  - `id` (INTEGER PRIMARY KEY)
  - `room_code` (TEXT: `'local'` or 6-digit online code)
  - `player` (TEXT: `'blue'`, `'red'`, `'green'`, `'yellow'`)
  - `value` (INTEGER: 1 to 6)
  - `used` (INTEGER: 0 = Pending, 1 = Consumed)
  - `created_at` (DATETIME)
- **`game_live_status`**:
  - `room_code` (TEXT PRIMARY KEY)
  - `current_turn` (TEXT)
  - `turn_state` (TEXT: `'roll'` | `'move'`)
  - `timer_seconds` (INTEGER: 12 to 0)
  - `last_roll` (INTEGER)
  - `last_roll_type` (TEXT: `'admin'` | `'random'`)
  - `updated_at` (DATETIME)
- **`online_rooms`**: Room code, host token, player limits, game state, sequences.
- **`online_players`**: Player tokens, seat indices, colors, connection statuses.
- **`online_tokens`**: Isolated coordinate tracking per room and color.
- **`admins`**: Salted Bcrypt password hashes for administrative login.

---

## 🎮 Game Rules & Authentic Mechanics

1. **Yard Release:** Pawns remain locked inside their corner yards until a player rolls a **6** (or in quick One-Out mode).
2. **Bonus Turn Triggers:**
   - Rolling a **6** awards an immediate extra roll.
   - Capturing an opponent pawn awards an immediate extra roll.
   - Moving a pawn into the Center Home awards an immediate extra roll.
3. **Three Sixes Penalty Rule:** Rolling three consecutive sixes forfeits the third roll; the turn passes to the next player immediately.
4. **8 Safe Havens:** 4 colored starting squares with arrows and 4 designated star squares (`SAFE = [0, 8, 13, 21, 26, 34, 39, 47]`). Pawns on safe squares cannot be captured.
5. **Team Up & Quick Modes:** Full support for 2-Player, 3-Player, 4-Player, 2v2 Team Up, and 1-Pawn Quick finish modes.

---

## 🕹️ Admin Control Panel & Viva Demonstration Guide

### A. Accessing the Remote Admin Panel
1. Open your browser and navigate to:  
   `http://localhost/ludo-king-main/admin/dashboard.php`
2. **Default Credentials:**
   - **Username:** `admin`
   - **Password:** `mohit@1760`
3. The Admin Dashboard will display the **Live Telemetry Banner**:
   - Status: `🟢 LIVE MATCH RUNNING`
   - Active Turn: Displays the active color (e.g., `🔴 RED`)
   - Countdown: Real-time progress bar reflecting the player's 12-second turn timer.

### B. Forcing a Dice Roll Remotely
1. Observe whose turn is active or coming next on the screen.
2. Under that player's card, click any dice number (**1, 2, 3, 4, 5, or 6**).
3. The card immediately displays `👑 FORCED NEXT: [Number]`.
4. When the player in the game taps their dice (or when their 12-second timer expires), the dice **guarantees that exact assigned number**.
5. The admin card updates to `Recent rolls: [Number (Admin)]`.
6. If the admin chooses not to assign any number, the card displays `🎲 Random (Fair Play)`, and standard random numbers (`random_int(1, 6)`) are used without interruption.

### C. Using the Secret In-Game Cheat Bar (Single-Screen Presentation)
For college viva demonstrations on a single laptop screen or projector:
1. In `game.php`, press the **`A`** key on your keyboard  
   *(or triple-tap the top header **👑 LUDO KING**)*.
2. A sleek, semi-transparent cheat controller slides down from the top.
3. Tap any number (`1` to `6`) for the active player.
4. An unobtrusive confirmation message appears: `✅ Next roll for RED set to 6`.
5. Roll the dice — the designated number appears seamlessly!
6. Click **`✕`** to hide the cheat bar completely before professors look.

---

## 🚀 Installation & Local Deployment (XAMPP)

1. Ensure **XAMPP** is installed at `C:\xampp`.
2. Start the **Apache** web server via the XAMPP Control Panel.
3. Place this project folder at:  
   `C:\xampp\htdocs\ludo-king-main`
4. Access the game in any browser:
   - **Home Screen:** `http://localhost/ludo-king-main/index.php`
   - **Pass N Play:** `http://localhost/ludo-king-main/setup.php`
   - **Online Multiplayer:** `http://localhost/ludo-king-main/online.php`
   - **Admin Controller:** `http://localhost/ludo-king-main/admin/dashboard.php`
5. **Mobile / PWA Installation:**
   - On Android Chrome or iOS Safari, navigate to `http://<your-pc-ip>/ludo-king-main/`.
   - Tap the **Install App** button or use browser menu "Add to Home Screen" to install it as a native standalone app!
