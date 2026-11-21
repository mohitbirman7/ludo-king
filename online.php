<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
$v = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover,user-scalable=no">
  <title>Play with Friends - Ludo King</title>
  <link rel="icon" type="image/png" href="assets/icons/icon-192.png?v=<?=$v?>">
  <link rel="apple-touch-icon" href="assets/icons/icon-192.png?v=<?=$v?>">
  <link href="https://fonts.googleapis.com/css2?family=Lilita+One&family=Roboto:wght@700;900&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; user-select: none; }
    html, body {
      margin: 0; padding: 0; height: 100%; width: 100%; overflow: hidden;
      font-family: 'Roboto', 'Lilita One', system-ui, -apple-system, sans-serif; color: #ffffff;
      touch-action: manipulation;
    }
    body {
      display: flex; justify-content: center; align-items: center;
      background: #09204e;
      background-image: 
        radial-gradient(circle at 50% 35%, #1852b4 0%, #0a2d75 55%, #05163e 100%),
        repeating-linear-gradient(45deg, rgba(255,255,255,0.04) 0, rgba(255,255,255,0.04) 30px, transparent 30px, transparent 60px),
        repeating-linear-gradient(-45deg, rgba(0,0,0,0.18) 0, rgba(0,0,0,0.18) 30px, transparent 30px, transparent 60px);
      background-size: 100% 100%, 60px 60px, 60px 60px;
    }

    .stage {
      position: relative; width: 100%; max-width: 440px; height: 100dvh; max-height: 100dvh;
      display: flex; flex-direction: column; justify-content: space-between; align-items: center;
      padding-top: max(env(safe-area-inset-top, 0px), 12px);
      padding-bottom: max(env(safe-area-inset-bottom, 0px), 10px);
      padding-left: max(env(safe-area-inset-left, 0px), 8px);
      padding-right: max(env(safe-area-inset-right, 0px), 8px);
      z-index: 1;
    }

    /* ---------------- LOBBY CARD STYLES ---------------- */
    #lobby-screen {
      position: relative; width: min(92vw, 390px); margin: auto;
      background: linear-gradient(180deg, #10449e 0%, #072b6a 100%);
      border: 3px solid #ffd700; border-radius: 20px;
      padding: 22px 18px 18px; text-align: center;
      box-shadow: 0 12px 35px rgba(0, 0, 0, 0.75), inset 0 1px 0 rgba(255, 255, 255, 0.3);
      z-index: 10;
    }
    .crown { font-size: 34px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6)); margin-bottom: 2px; }
    h1 {
      margin: 0 0 4px; font-size: 24px; font-weight: 900; color: #ffd700;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
    }
    p.sub { margin: 0 0 16px; font-size: 12.5px; color: #c0d5ff; }

    .tab-bar {
      display: flex; gap: 8px; margin-bottom: 16px; background: rgba(0, 15, 50, 0.45);
      padding: 4px; border-radius: 14px; border: 1.5px solid #3c6dbd;
    }
    .tab-btn {
      flex: 1; padding: 10px 4px; border: none; border-radius: 10px;
      font: 900 14px 'Roboto', sans-serif; cursor: pointer; color: #a4c2f4;
      background: transparent; transition: all 0.2s ease;
    }
    .tab-btn.active {
      background: linear-gradient(180deg, #ffb300, #ff6f00);
      color: #ffffff; box-shadow: 0 3px 8px rgba(255, 111, 0, 0.5);
    }

    .form-group { text-align: left; margin-bottom: 12px; }
    label { display: block; font-size: 11.5px; font-weight: 900; color: #ffd700; margin-bottom: 4px; text-shadow: 0 1px 2px rgba(0,0,0,0.8); }
    input {
      width: 100%; padding: 11px 13px; border-radius: 10px;
      border: 2px solid #3c6dbd; background: #082153; color: #ffffff;
      font-size: 15px; outline: none; transition: border-color 0.2s;
    }
    input:focus { border-color: #ffd700; }
    .code-input {
      font-size: 24px; font-weight: 900; letter-spacing: 6px; text-align: center; color: #ffd700;
    }

    .counts { display: flex; gap: 8px; margin-bottom: 14px; }
    .c-btn {
      flex: 1; padding: 10px 0; border: 2px solid #3c6dbd; border-radius: 10px;
      background: #082153; color: #ffffff; font-weight: 900; font-size: 14px; cursor: pointer;
    }
    .c-btn.active {
      border-color: #ffd700; background: linear-gradient(180deg, #1852b4, #0c2d6e);
      box-shadow: 0 0 10px rgba(255, 215, 0, 0.4); color: #ffd700;
    }

    .btn-gold {
      width: 100%; padding: 12px; border-radius: 25px; border: 2.5px solid #ffffff;
      background: linear-gradient(180deg, #ffb300, #ff6f00); color: #ffffff;
      font: 900 15px 'Roboto', sans-serif; cursor: pointer;
      box-shadow: 0 5px 15px rgba(255, 111, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.4);
    }
    .btn-gold:active { transform: scale(0.97); }

    .room-badge {
      background: rgba(0, 0, 0, 0.4); border: 2px dashed #ffd700;
      border-radius: 14px; padding: 12px; margin-bottom: 14px;
    }
    .room-code-label { font-size: 11px; color: #aac4ff; font-weight: 700; }
    .room-code-val { font-size: 32px; font-weight: 900; color: #ffd700; letter-spacing: 4px; margin: 4px 0; }
    .copy-btn {
      background: #1852b4; border: 1.5px solid #ffd700; color: #ffffff;
      padding: 5px 12px; border-radius: 15px; font-size: 11.5px; font-weight: 900; cursor: pointer;
    }

    .player-roster {
      text-align: left; background: rgba(0, 15, 50, 0.5); border-radius: 12px;
      padding: 10px 12px; margin-bottom: 14px; border: 1px solid rgba(255, 215, 0, 0.3);
      max-height: 160px; overflow-y: auto;
    }
    .roster-item {
      display: flex; justify-content: space-between; align-items: center;
      padding: 7px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .roster-item:last-child { border-bottom: none; }
    .r-name { font-weight: 700; font-size: 13.5px; }
    .r-badge { font-size: 10.5px; font-weight: 900; padding: 2px 7px; border-radius: 10px; }
    .r-badge.accepted { background: #2e7d32; color: #ffffff; }
    .r-badge.pending { background: #f57f17; color: #ffffff; }
    .btn-accept {
      background: #2e7d32; border: 1.5px solid #81c784; color: #ffffff;
      padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 900; cursor: pointer;
    }

    /* ---------------- IN-GAME HEADER & VOICE CHAT BAR ---------------- */
    #game-screen {
      display: none; width: 100%; height: 100%;
      flex-direction: column; justify-content: space-between; align-items: center;
    }
    .top-nav {
      display: flex; justify-content: space-between; align-items: center;
      width: min(94vw, 48vh, 395px); margin: 0 auto 4px; position: relative; height: 46px;
    }
    .top-back-btn {
      width: 44px; height: 44px; border-radius: 50%;
      border: 3px solid #ffcc00;
      background: radial-gradient(circle at 40% 35%, #f6b100 0%, #c48200 65%, #6e4700 100%);
      color: #111e38; font-size: 24px; font-weight: 900;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.5), inset 0 2px 2px rgba(255,255,255,0.6);
      cursor: pointer; z-index: 20; touch-action: manipulation;
    }
    .top-back-btn:active { transform: scale(0.92); }

    .room-pill {
      background: rgba(0, 20, 60, 0.75); border: 2px solid #ffd700; border-radius: 14px;
      padding: 5px 12px; font-size: 12px; font-weight: 900; color: #ffd700; letter-spacing: 1px;
    }

    /* Voice Chat Controls in Header */
    .voice-ctrls {
      display: flex; align-items: center; gap: 6px; z-index: 20;
    }
    .btn-v {
      display: flex; align-items: center; gap: 4px; padding: 6px 10px; border-radius: 16px;
      border: 1.5px solid #ffd700; background: linear-gradient(180deg, #1852b4, #0c2d6e);
      color: #ffffff; font-size: 11px; font-weight: 900; cursor: pointer;
      box-shadow: 0 3px 6px rgba(0, 0, 0, 0.4); transition: all 0.2s;
    }
    .btn-v:active { transform: scale(0.94); }
    .btn-v.mic-live {
      border-color: #22c55e; background: linear-gradient(180deg, #15803d, #14532d);
      box-shadow: 0 0 10px rgba(34, 197, 94, 0.8);
    }
    .btn-v.mic-off {
      border-color: #ef4444; background: linear-gradient(180deg, #b91c1c, #7f1d1d);
      box-shadow: 0 0 8px rgba(239, 68, 68, 0.6);
    }
    .btn-v.deafen-on {
      border-color: #f97316; background: linear-gradient(180deg, #c2410c, #7c2d12);
    }
    .btn-v-icon {
      width: 36px; height: 36px; border-radius: 50%; border: 1.5px solid #ffd700;
      background: linear-gradient(180deg, #1852b4, #0c2d6e); color: #ffffff;
      font-size: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center;
      box-shadow: 0 3px 6px rgba(0, 0, 0, 0.4);
    }

    /* ---------------- PLAYER CARDS (EXACT SCREENSHOT LAYOUT) ---------------- */
    .rowS {
      display: flex; justify-content: space-between; align-items: flex-end;
      width: min(94vw, 48vh, 395px); margin: 0 auto; padding: 2px 0;
    }
    .rowS.bottom-row { align-items: flex-start; }

    .p-wrap {
      display: flex; flex-direction: column; align-items: center; position: relative; width: 45%;
    }
    .p-meta {
      display: flex; align-items: center; justify-content: space-between; width: 100%;
      margin-bottom: 3px; padding: 0 4px;
    }
    .rowS.bottom-row .p-meta { margin-bottom: 0; margin-top: 3px; }
    .p-name-label {
      font-size: 14px; font-weight: 900; color: #ffffff;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.9), 0 0 4px rgba(0,0,0,0.8);
      letter-spacing: 0.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    /* Per-player Speaker Mute Button */
    .peer-speaker-btn {
      background: rgba(0, 15, 50, 0.7); border: 1.5px solid #ffd700; border-radius: 12px;
      color: #ffffff; font-size: 11px; padding: 2px 6px; cursor: pointer;
      display: flex; align-items: center; justify-content: center; line-height: 1;
    }
    .peer-speaker-btn.is-muted { background: #b91c1c; border-color: #ff8a80; }
    .peer-speaker-btn:active { transform: scale(0.9); }

    /* Speaking Waves Indicator */
    .speaking-wave {
      display: none; font-size: 11px; color: #22c55e; font-weight: 900;
      animation: waveBlink 0.5s infinite alternate; margin-left: 4px;
    }
    .speaking-wave.active { display: inline-block; }
    @keyframes waveBlink { 0% { opacity: 0.4; } 100% { opacity: 1; } }

    /* Dual-Box Card (Goti Icon + Dice Box with Gold Border) */
    .slot {
      position: relative; display: flex; align-items: center; gap: 0;
      padding: 3px;
      background: linear-gradient(180deg, #184b96 0%, #0d2e66 100%);
      border: 3px solid #f6b100; border-radius: 12px;
      box-shadow: 0 5px 12px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.35);
      z-index: 5; transition: transform 0.2s, box-shadow 0.2s;
      touch-action: manipulation; cursor: pointer;
    }
    .slot.slot-rev { flex-direction: row-reverse; }

    .slot.act {
      border-color: #ffe600;
      box-shadow: 0 0 16px rgba(255, 230, 0, 0.9), 0 5px 12px rgba(0, 0, 0, 0.5);
      animation: pulseSlot 0.9s infinite alternate ease-in-out;
    }
    @keyframes pulseSlot { 0% { transform: scale(1); } 100% { transform: scale(1.03); } }

    /* Speaking green aura around active talker */
    .slot.is-speaking {
      border-color: #22c55e !important;
      box-shadow: 0 0 18px rgba(34, 197, 94, 0.95), 0 5px 12px rgba(0,0,0,0.5) !important;
    }
    .slot.is-speaking .p-box-goti {
      box-shadow: 0 0 0 3px #22c55e, 0 0 14px rgba(34, 197, 94, 0.9) !important;
    }

    /* Avatar / Goti Box inside Player Card */
    .p-box-goti {
      position: relative;
      width: clamp(40px, 6.2vh, 48px); height: clamp(40px, 6.2vh, 48px);
      border-radius: 8px; border: 2px solid #ffffff;
      display: flex; align-items: center; justify-content: center;
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); flex-shrink: 0;
    }
    .p-box-goti.bg-blue { background: radial-gradient(circle at 40% 40%, #1ea3f2, #0d5c99); }
    .p-box-goti.bg-red { background: radial-gradient(circle at 40% 40%, #e8212c, #8f0d15); }
    .p-box-goti.bg-green { background: radial-gradient(circle at 40% 40%, #0c8f43, #055425); }
    .p-box-goti.bg-yellow { background: radial-gradient(circle at 40% 40%, #fdd414, #b59200); }
    .p-box-goti svg { width: 30px; height: 40px; filter: drop-shadow(0 2px 3px rgba(0,0,0,0.4)); }

    /* Dice Box inside Player Card */
    .db {
      position: relative; width: clamp(44px, 6.6vh, 52px); height: clamp(40px, 6.2vh, 48px);
      background: radial-gradient(circle at 45% 45%, #ffffff 0%, #f5eee4 65%, #dfd5c4 100%);
      border: 2px solid #ecd8c2; border-radius: 8px; margin-left: 2px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3), 0 2px 4px rgba(0, 0, 0, 0.2);
      cursor: pointer; flex-shrink: 0; touch-action: manipulation;
    }
    .slot.slot-rev .db { margin-left: 0; margin-right: 2px; }

    /* Bouncing Orange Arrow Pointing at Active Dice (Exact from Screenshot) */
    .prompt-arrow {
      position: absolute; width: 0; height: 0;
      pointer-events: none; z-index: 25; display: none;
    }
    .slot.act .prompt-arrow { display: block; }

    .prompt-arrow.arrow-left {
      right: -24px; top: 50%; transform: translateY(-50%);
      border-top: 11px solid transparent;
      border-bottom: 11px solid transparent;
      border-right: 18px solid #ff9800;
      filter: drop-shadow(0 2px 3px rgba(0,0,0,0.6));
      animation: arrowBounceLeft 0.6s infinite alternate ease-in-out;
    }
    @keyframes arrowBounceLeft {
      0% { transform: translate(0, -50%); }
      100% { transform: translate(-6px, -50%); }
    }

    .prompt-arrow.arrow-right {
      left: -24px; top: 50%; transform: translateY(-50%);
      border-top: 11px solid transparent;
      border-bottom: 11px solid transparent;
      border-left: 18px solid #ff9800;
      filter: drop-shadow(0 2px 3px rgba(0,0,0,0.6));
      animation: arrowBounceRight 0.6s infinite alternate ease-in-out;
    }
    @keyframes arrowBounceRight {
      0% { transform: translate(0, -50%); }
      100% { transform: translate(6px, -50%); }
    }

    /* ---------------- 3D DICE CUBE ---------------- */
    .die { width: clamp(34px, 5.2vh, 42px); height: clamp(34px, 5.2vh, 42px); position: relative; visibility: hidden; perspective: 260px; isolation: isolate; }
    .die.show { visibility: visible; }
    .die.big { z-index: 30; will-change: transform; }
    .cube { position: absolute; inset: 0; transform-style: preserve-3d; will-change: transform; }
    .f {
      position: absolute; inset: 0; border-radius: 20%; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(3, 1fr);
      padding: 10%; backface-visibility: hidden; background: linear-gradient(145deg, #ffffff 0%, #f7f0e4 55%, #e2d8c3 100%);
      border: 1px solid rgba(0, 0, 0, 0.15); box-shadow: inset 0 -3px 0 rgba(0, 0, 0, 0.15), inset 0 2px 2px rgba(255, 255, 255, 0.95);
    }
    .f1 { transform: translateZ(21px); }
    .f6 { transform: rotateY(180deg) translateZ(21px); }
    .f2 { transform: rotateY(90deg) translateZ(21px); }
    .f5 { transform: rotateY(-90deg) translateZ(21px); }
    .f3 { transform: rotateX(90deg) translateZ(21px); }
    .f4 { transform: rotateX(-90deg) translateZ(21px); }
    .f i { width: 82%; height: 82%; margin: auto; border-radius: 50%; }
    .f i.on {
      background: radial-gradient(circle at 35% 35%, #3a3a3a 0%, #111111 85%);
      box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.9), 0 1px 1px rgba(255, 255, 255, 0.3);
    }
    .f1 i.on {
      background: radial-gradient(circle at 35% 35%, #ff2b3d 0%, #b80c23 85%) !important;
      box-shadow: inset 0 1px 1.5px rgba(0,0,0,0.7), 0 0 5px rgba(232, 33, 44, 0.7) !important;
      width: 95% !important; height: 95% !important;
    }

    /* ---------------- AUTHENTIC LUDO KING BOARD ---------------- */
    #board-container { width: 100%; display: flex; justify-content: center; align-items: center; margin: auto 0; padding: 2px 0; }
    #board {
      position: relative; width: min(94vw, 48vh, 395px); height: min(94vw, 48vh, 395px);
      aspect-ratio: 1 / 1;
      display: grid; grid-template-columns: repeat(15, minmax(0, 1fr)); grid-template-rows: repeat(15, minmax(0, 1fr));
      background: #ffffff; border: 4px solid #000000; border-radius: 4px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.75);
      overflow: visible;
    }

    .base { position: relative; display: flex; align-items: center; justify-content: center; min-width: 0; min-height: 0; border: 1.5px solid #000; }
    .base.red { background: #e50914; }
    .base.green { background: #009933; }
    .base.yellow { background: #ffcc00; }
    .base.blue { background: #0077dd; }
    .base .inner {
      width: 66.6%; height: 66.6%; background: #ffffff; border: 1.5px solid #000000;
      box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.25); position: relative;
    }

    /* 4 Solid Colored Circular Slots inside Yard */
    .sl {
      position: absolute; width: 6.67%; height: 6.67%; border-radius: 50%;
      border: 2px solid rgba(0,0,0,0.25);
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.4), 0 2px 4px rgba(0,0,0,0.25);
      transform: scale(0.95);
    }
    .sl.red    { background: #e50914; }
    .sl.green  { background: #009933; }
    .sl.yellow { background: #ffcc00; }
    .sl.blue   { background: #0077dd; }

    /* Track Grid Cells */
    .c { background: #ffffff; border: 0.5px solid #000000; display: flex; align-items: center; justify-content: center; position: relative; min-width: 0; min-height: 0; }
    .c.red    { background: #e50914; }
    .c.green  { background: #009933; }
    .c.yellow { background: #ffcc00; }
    .c.blue   { background: #0077dd; }

    /* Outline Stars & Track Arrows */
    .star-icon { font-size: clamp(17px, 4.4vw, 23px); color: #111111; font-weight: 900; line-height: 1; }
    .ar-arrow { font-size: clamp(14px, 3.8vw, 19px); font-weight: 900; }

    /* Center Triangles */
    .ctr {
      grid-area: 7 / 7 / 10 / 10;
      background: conic-gradient(from -45deg, #009933 0 25%, #ffcc00 0 50%, #0077dd 0 75%, #e50914 0);
      border: 1px solid #000; position: relative;
    }

    /* ---------------- 3D GOTI (PAWN) WITH NOTCHED PEDESTAL ---------------- */
    .tk {
      position: absolute; width: 6.67%; height: 6.67%; z-index: 10; display: flex; align-items: center;
      justify-content: center; pointer-events: none; transition: left 0.16s linear, top 0.16s linear;
      will-change: left, top, transform; touch-action: manipulation;
    }
    .tk-inner { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
    .tk svg { width: 100%; height: 135%; margin-top: -32%; filter: drop-shadow(0 3px 3px rgba(0, 0, 0, 0.6)); transform-origin: 50% 90%; overflow: visible; }
    
    .tk.red    { --k: #e50914; --k-dark: #630509; }
    .tk.green  { --k: #009933; --k-dark: #024517; }
    .tk.yellow { --k: #ffcc00; --k-dark: #7a6100; }
    .tk.blue   { --k: #0077dd; --k-dark: #033c6e; }

    .tk.go { z-index: 18; pointer-events: auto; cursor: pointer; }
    .tk.go::before {
      content: ""; position: absolute; inset: -4px; border-radius: 50%;
      border: 2px solid #ffd700; background: radial-gradient(circle, rgba(255,215,0,0.4) 0%, transparent 70%);
      animation: haloPulse 0.9s infinite alternate cubic-bezier(0.4, 0, 0.2, 1);
      pointer-events: none;
    }
    @keyframes haloPulse { 0% { transform: scale(0.85); opacity: 0.5; } 100% { transform: scale(1.35); opacity: 1; } }
    .tk.go .tk-inner {
      animation: authenticHop 0.45s infinite alternate cubic-bezier(0.25, 0.8, 0.5, 1);
      filter: drop-shadow(0 0 6px #ffffff) drop-shadow(0 0 10px #ffd700);
    }
    @keyframes authenticHop { 0% { transform: translateY(0) scale(1); } 100% { transform: translateY(-36%) scale(1.14); } }
    .tk.retreating { transition: left 0.04s linear, top 0.04s linear !important; opacity: 0.9; filter: brightness(1.2) drop-shadow(0 0 8px #ff4757); }

    /* Footer Status Bar */
    .foot-bar {
      width: min(94vw, 48vh, 395px); padding: 7px 12px; background: rgba(0, 15, 50, 0.75);
      border: 1.5px solid #ffd700; border-radius: 12px; text-align: center;
      font-size: 13px; font-weight: 900; color: #ffd700;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.45);
    }

    /* Bonus Roll Banner */
    .bonus-banner {
      position: fixed; left: 50%; top: 40%; transform: translate(-50%, -50%);
      background: linear-gradient(180deg, #ffb300, #ff6f00); border: 3px solid #ffffff;
      border-radius: 30px; padding: 10px 24px; color: #ffffff;
      font-family: 'Lilita One', 'Roboto', sans-serif; font-size: 22px; font-weight: 900;
      box-shadow: 0 8px 25px rgba(0,0,0,0.7), 0 0 15px rgba(255,215,0,0.7);
      z-index: 80; pointer-events: none;
      animation: bonusPop 1.3s forwards cubic-bezier(0.18, 0.89, 0.32, 1.28);
    }
    @keyframes bonusPop {
      0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
      25% { opacity: 1; transform: translate(-50%, -50%) scale(1.15); }
      75% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
      100% { opacity: 0; transform: translate(-50%, -60%) scale(0.8); visibility: hidden; }
    }

    /* Modal dialog */
    .modal { position: fixed; inset: 0; background: rgba(0, 5, 20, 0.78); display: flex; align-items: center; justify-content: center; z-index: 100; backdrop-filter: blur(4px); }
    .modal[hidden] { display: none; }
    .box {
      width: min(88vw, 320px); padding: 24px 20px; background: linear-gradient(180deg, #103b8c 0%, #061e4e 100%);
      border: 3px solid #ffd700; border-radius: 18px; text-align: center;
      box-shadow: 0 12px 35px rgba(0, 0, 0, 0.8), inset 0 1px 0 rgba(255, 255, 255, 0.2);
    }
    .pause-title { font-size: 20px; font-weight: 900; color: #ffd700; margin-bottom: 16px; }
    .gold {
      display: block; width: 100%; margin: 8px 0; padding: 11px; border-radius: 25px; border: 2.5px solid #ffd700;
      background: linear-gradient(180deg, #ffb300, #ff6f00); color: #ffffff; font: 900 15px 'Roboto', sans-serif;
      cursor: pointer; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
    }
    .gold.quit-btn { background: linear-gradient(180deg, #e53935, #b71c1c); border-color: #ff8a80; }
    .gold:active { transform: scale(0.96); }

    /* In-game Toast */
    .toast-pop {
      position: fixed; top: 62px; left: 50%; transform: translateX(-50%);
      background: rgba(10, 25, 55, 0.95); border: 2px solid #ffd700; border-radius: 20px;
      padding: 6px 16px; color: #ffd700; font-size: 12px; font-weight: 900;
      z-index: 90; pointer-events: none; transition: opacity 0.3s;
      box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    }
  </style>
</head>
<body>
  <!-- Authentic 3D Ludo King Goti SVG Definition (Matching User Screenshot) -->
  <svg width="0" height="0" style="position:absolute">
    <defs>
      <symbol id="pn" viewBox="0 0 46 64">
        <!-- Circular soft drop shadow -->
        <ellipse cx="23" cy="58" rx="16" ry="4.5" fill="rgba(0,0,0,0.4)"/>
        
        <!-- 3D Notched Casino Chip / Cog Pedestal Base -->
        <g class="pedestal">
          <ellipse cx="23" cy="53" rx="15" ry="5.5" fill="#111111"/>
          <ellipse cx="23" cy="52" rx="14" ry="4.5" fill="var(--k-dark, #0d3b66)"/>
          <path d="M11 50 L13 54 M16 48 L17 53 M23 47.5 L23 53 M29 48 L30 53 M34 50 L35 54" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" opacity="0.85"/>
          <ellipse cx="23" cy="50" rx="11" ry="3.5" fill="var(--k)"/>
          <ellipse cx="23" cy="49.5" rx="8" ry="2.2" fill="#ffffff" opacity="0.4"/>
        </g>

        <!-- Teardrop Pin Goti Body with White Outline Border -->
        <path d="M23 48 C16 38, 7 28, 7 19 C7 9, 14 2, 23 2 C32 2, 39 9, 39 19 C39 28, 30 38, 23 48 Z" 
              fill="var(--k)" 
              stroke="#ffffff" 
              stroke-width="2.8" 
              stroke-linejoin="round"/>

        <!-- Inner Center Gem -->
        <circle cx="23" cy="18.5" r="9" fill="rgba(0,0,0,0.15)"/>
        <circle cx="23" cy="18.5" r="8" fill="var(--k)"/>

        <!-- Specular Highlight on Head -->
        <ellipse cx="18" cy="12" rx="4.5" ry="2.5" transform="rotate(-35 18 12)" fill="#ffffff" opacity="0.85"/>
      </symbol>
    </defs>
  </svg>

  <div class="stage">
    <!-- 1. LOBBY SCREEN (CREATE OR JOIN ROOM) -->
    <div id="lobby-screen">
      <div class="crown">👑</div>
      <h1>Play With Friends</h1>
      <p class="sub">Create a room or join friends with a 6-digit code & voice chat!</p>

      <div class="tab-bar">
        <button class="tab-btn active" id="tab-create" onclick="switchTab('create')">Create Room</button>
        <button class="tab-btn" id="tab-join" onclick="switchTab('join')">Join Room</button>
      </div>

      <!-- CREATE ROOM SECTION -->
      <div id="sec-create">
        <div class="form-group">
          <label>Your Name</label>
          <input id="c-name" value="Player 1" placeholder="Enter your name" maxlength="12">
        </div>
        <div class="form-group">
          <label>Select Players</label>
          <div class="counts">
            <button class="c-btn" onclick="selectCount(2, this)">2 Players</button>
            <button class="c-btn" onclick="selectCount(3, this)">3 Players</button>
            <button class="c-btn active" onclick="selectCount(4, this)">4 Players</button>
          </div>
        </div>
        <button class="btn-gold" onclick="doCreateRoom()">Generate Room Code</button>
      </div>

      <!-- JOIN ROOM SECTION -->
      <div id="sec-join" style="display:none;">
        <div class="form-group">
          <label>Your Name</label>
          <input id="j-name" value="Friend" placeholder="Enter your name" maxlength="12">
        </div>
        <div class="form-group">
          <label>6-Digit Room Code</label>
          <input id="j-code" class="code-input" placeholder="000000" maxlength="6" pattern="[0-9]*">
        </div>
        <button class="btn-gold" onclick="doJoinRoom()">Send Join Request</button>
      </div>

      <!-- LOBBY WAITING ROOM -->
      <div id="sec-waiting" style="display:none;">
        <div class="room-badge">
          <div class="room-code-label">ROOM CODE</div>
          <div class="room-code-val" id="disp-room-code">------</div>
          <button class="copy-btn" onclick="copyCode()">📋 Copy Code</button>
        </div>

        <label style="text-align:left;">Players in Lobby (<span id="player-count">1</span>)</label>
        <div class="player-roster" id="roster-list"></div>

        <div id="host-controls" style="display:none;">
          <button class="btn-gold" id="btn-start-game" onclick="doStartGame()">🚀 Start Online Game</button>
        </div>
        <div id="guest-controls" style="display:none;">
          <p id="guest-status-msg" style="font-size:13px;color:#ffd700;font-weight:700;">Waiting for Host to accept request...</p>
        </div>
      </div>

      <div style="margin-top:16px;">
        <a href="index.php" style="color:#aac4ff;font-size:13px;text-decoration:none;font-weight:700;">← Back to Home</a>
      </div>
    </div>

    <!-- 2. LIVE ONLINE GAME SCREEN (SCREENSHOT MATCH + VOICE CONTROLS) -->
    <div id="game-screen">
      <!-- Top Navigation & Voice Chat Bar -->
      <div class="top-nav">
        <button class="top-back-btn" onclick="confirmExitGame()" title="Quit Game">↩</button>
        <div class="room-pill">ROOM: <span id="game-room-code">------</span></div>
        <div class="voice-ctrls">
          <button class="btn-v mic-live" id="btn-mic" onclick="toggleLocalMic()" title="Mic On / Mute">
            <span id="mic-icon">🎙️</span> <span id="mic-label">MIC ON</span>
          </button>
          <button class="btn-v" id="btn-deafen" onclick="toggleMasterVoice()" title="Voice Chat Sound">
            <span id="deafen-icon">🔊</span> <span id="deafen-label">VOICE</span>
          </button>
          <button class="btn-v-icon" id="btn-sfx" onclick="toggleSfx()" title="Sound FX">🔔</button>
        </div>
      </div>

      <!-- Top Player Cards Row (Red on Left, Green on Right) -->
      <div class="rowS">
        <!-- Player Red (Top Left) -->
        <div class="p-wrap" id="wrap-red">
          <div class="p-meta">
            <span class="p-name-label" id="name-red">Player 2</span>
            <div style="display:flex;align-items:center;gap:4px;">
              <button class="peer-speaker-btn" id="spk-red" onclick="togglePeerMute('red')" title="Mute/Unmute player">🔊</button>
              <span class="speaking-wave" id="wave-red">🎙️</span>
            </div>
          </div>
          <div class="slot" id="slot-red" data-c="red">
            <div class="p-box-goti bg-red">
              <svg style="--k:#e8212c"><use href="#pn"/></svg>
            </div>
            <div class="db" title="Roll Dice">
              <div class="die show" id="die-red"></div>
            </div>
            <div class="prompt-arrow arrow-left"></div>
          </div>
        </div>

        <!-- Player Green (Top Right) -->
        <div class="p-wrap" id="wrap-green">
          <div class="p-meta">
            <div style="display:flex;align-items:center;gap:4px;">
              <button class="peer-speaker-btn" id="spk-green" onclick="togglePeerMute('green')" title="Mute/Unmute player">🔊</button>
              <span class="speaking-wave" id="wave-green">🎙️</span>
            </div>
            <span class="p-name-label" id="name-green">Player 3</span>
          </div>
          <div class="slot slot-rev" id="slot-green" data-c="green">
            <div class="p-box-goti bg-green">
              <svg style="--k:#0c8f43"><use href="#pn"/></svg>
            </div>
            <div class="db" title="Roll Dice">
              <div class="die show" id="die-green"></div>
            </div>
            <div class="prompt-arrow arrow-right"></div>
          </div>
        </div>
      </div>

      <!-- Center Board -->
      <div id="board-container">
        <div id="board"></div>
      </div>

      <!-- Bottom Player Cards Row (Blue on Left, Yellow on Right) -->
      <div class="rowS bottom-row">
        <!-- Player Blue (Bottom Left) -->
        <div class="p-wrap" id="wrap-blue">
          <div class="slot" id="slot-blue" data-c="blue">
            <div class="p-box-goti bg-blue">
              <svg style="--k:#1ea3f2"><use href="#pn"/></svg>
            </div>
            <div class="db" title="Roll Dice">
              <div class="die show" id="die-blue"></div>
            </div>
            <div class="prompt-arrow arrow-left"></div>
          </div>
          <div class="p-meta">
            <span class="p-name-label" id="name-blue">You</span>
            <div style="display:flex;align-items:center;gap:4px;">
              <button class="peer-speaker-btn" id="spk-blue" onclick="togglePeerMute('blue')" style="display:none;" title="Mute/Unmute player">🔊</button>
              <span class="speaking-wave" id="wave-blue">🎙️</span>
            </div>
          </div>
        </div>

        <!-- Player Yellow (Bottom Right) -->
        <div class="p-wrap" id="wrap-yellow">
          <div class="slot slot-rev" id="slot-yellow" data-c="yellow">
            <div class="p-box-goti bg-yellow">
              <svg style="--k:#fdd414"><use href="#pn"/></svg>
            </div>
            <div class="db" title="Roll Dice">
              <div class="die show" id="die-yellow"></div>
            </div>
            <div class="prompt-arrow arrow-right"></div>
          </div>
          <div class="p-meta">
            <div style="display:flex;align-items:center;gap:4px;">
              <button class="peer-speaker-btn" id="spk-yellow" onclick="togglePeerMute('yellow')" title="Mute/Unmute player">🔊</button>
              <span class="speaking-wave" id="wave-yellow">🎙️</span>
            </div>
            <span class="p-name-label" id="name-yellow">Player 4</span>
          </div>
        </div>
      </div>

      <!-- Turn & Status Bar -->
      <div class="foot-bar" id="game-status-bar">Connecting to room...</div>
    </div>
  </div>

  <!-- Exit Game Confirmation Modal -->
  <div class="modal" id="modal-exit" hidden>
    <div class="box">
      <div class="pause-title">Leave Match?</div>
      <p style="font-size:13.5px;color:#c0d5ff;margin:0 0 16px;">Are you sure you want to quit this online game?</p>
      <button class="gold quit-btn" onclick="location.href='index.php'">Leave Game</button>
      <button class="gold" onclick="closeExitModal()">Continue Playing</button>
    </div>
  </div>

  <div id="toast-el" class="toast-pop" style="display:none;"></div>

  <script src="assets/js/security.js?v=<?=$v?>"></script>
  <script src="assets/js/sound.js?v=<?=$v?>"></script>
  <script>
    let myCode = '';
    let myToken = '';
    let myColor = '';
    let isHost = false;
    let selectedPlayerCount = 4;
    let pollInterval = null;
    let boardBuilt = false;
    let currentTurnColor = 'blue';

    function showToast(msg) {
      const el = document.getElementById('toast-el');
      el.textContent = msg;
      el.style.display = 'block';
      clearTimeout(el._timer);
      el._timer = setTimeout(() => { el.style.display = 'none'; }, 2400);
    }

    function confirmExitGame() {
      document.getElementById('modal-exit').hidden = false;
    }
    function closeExitModal() {
      document.getElementById('modal-exit').hidden = true;
    }

    function switchTab(t) {
      Snd.click();
      document.getElementById('sec-create').style.display = t === 'create' ? 'block' : 'none';
      document.getElementById('sec-join').style.display = t === 'join' ? 'block' : 'none';
      document.getElementById('tab-create').classList.toggle('active', t === 'create');
      document.getElementById('tab-join').classList.toggle('active', t === 'join');
    }

    function selectCount(n, el) {
      Snd.click();
      selectedPlayerCount = n;
      document.querySelectorAll('.c-btn').forEach(b => b.classList.remove('active'));
      el.classList.add('active');
    }

    async function doCreateRoom() {
      Snd.unlock();
      Snd.click();
      const name = document.getElementById('c-name').value.trim() || 'Host';
      const fd = new FormData();
      fd.append('action', 'create_room');
      fd.append('name', name);
      fd.append('players', selectedPlayerCount);

      const res = await fetch('api/online.php', { method: 'POST', body: fd }).then(r => r.json());
      if (res.ok) {
        myCode = res.room_code;
        myToken = res.token;
        myColor = res.color;
        isHost = true;
        showWaitingLobby();
      } else {
        alert(res.error || 'Failed to create room.');
      }
    }

    async function doJoinRoom() {
      Snd.unlock();
      Snd.click();
      const name = document.getElementById('j-name').value.trim() || 'Friend';
      const code = document.getElementById('j-code').value.trim();
      if (code.length !== 6) {
        alert('Please enter a valid 6-digit room code.');
        return;
      }

      const fd = new FormData();
      fd.append('action', 'join_room');
      fd.append('room_code', code);
      fd.append('name', name);

      const res = await fetch('api/online.php', { method: 'POST', body: fd }).then(r => r.json());
      if (res.ok) {
        myCode = res.room_code;
        myToken = res.token;
        myColor = res.color;
        isHost = false;
        showWaitingLobby();
      } else {
        alert(res.error || 'Failed to join room.');
      }
    }

    function showWaitingLobby() {
      document.getElementById('sec-create').style.display = 'none';
      document.getElementById('sec-join').style.display = 'none';
      document.querySelector('.tab-bar').style.display = 'none';
      document.getElementById('sec-waiting').style.display = 'block';
      document.getElementById('disp-room-code').textContent = myCode;
      document.getElementById('game-room-code').textContent = myCode;

      if (isHost) {
        document.getElementById('host-controls').style.display = 'block';
      } else {
        document.getElementById('guest-controls').style.display = 'block';
      }

      pollLobby();
      pollInterval = setInterval(pollLobby, 800);
    }

    function copyCode() {
      Snd.click();
      navigator.clipboard.writeText(myCode);
      showToast('Room code ' + myCode + ' copied!');
    }

    async function pollLobby() {
      try {
        const res = await fetch(`api/online.php?action=room_status&code=${myCode}&token=${myToken}`).then(r => r.json());
        if (!res.ok) return;

        if (res.room.status === 'active') {
          clearInterval(pollInterval);
          launchOnlineGame();
          return;
        }

        const rosterEl = document.getElementById('roster-list');
        rosterEl.innerHTML = '';
        let acceptedCnt = 0;

        res.players.forEach(p => {
          if (p.status === 'accepted') acceptedCnt++;
          const d = document.createElement('div');
          d.className = 'roster-item';
          let rightHtml = '';
          if (p.status === 'accepted') {
            rightHtml = `<span class="r-badge accepted">✓ Ready (${p.color.toUpperCase()})</span>`;
          } else {
            if (isHost) {
              rightHtml = `<button class="btn-accept" onclick="acceptGuest(${p.id})">Accept Request</button>`;
            } else {
              rightHtml = `<span class="r-badge pending">Pending Host</span>`;
            }
          }
          d.innerHTML = `<span class="r-name">${escapeHtml(p.player_name)}</span> ${rightHtml}`;
          rosterEl.appendChild(d);
        });

        document.getElementById('player-count').textContent = `${acceptedCnt} / ${res.room.max_players}`;

        if (!isHost) {
          const gMsg = document.getElementById('guest-status-msg');
          if (res.my_status === 'accepted') {
            gMsg.textContent = '🎉 Host accepted your request! Waiting for game to start...';
            gMsg.style.color = '#4caf50';
          } else {
            gMsg.textContent = '⏳ Waiting for Host to accept your request...';
            gMsg.style.color = '#ffd700';
          }
        }
      } catch (e) {}
    }

    async function acceptGuest(playerId) {
      Snd.click();
      const fd = new FormData();
      fd.append('action', 'accept_player');
      fd.append('room_code', myCode);
      fd.append('token', myToken);
      fd.append('player_id', playerId);
      await fetch('api/online.php', { method: 'POST', body: fd });
      pollLobby();
    }

    async function doStartGame() {
      Snd.click();
      const fd = new FormData();
      fd.append('action', 'start_game');
      fd.append('room_code', myCode);
      fd.append('token', myToken);
      const res = await fetch('api/online.php', { method: 'POST', body: fd }).then(r => r.json());
      if (res.ok) {
        clearInterval(pollInterval);
        launchOnlineGame();
      } else {
        alert(res.error || 'Cannot start game.');
      }
    }

    // ----------------------------------------------------
    // IN-GAME BOARD LOGIC & RENDERING
    // ----------------------------------------------------
    function launchOnlineGame() {
      document.getElementById('lobby-screen').style.display = 'none';
      const gs = document.getElementById('game-screen');
      gs.style.display = 'flex';
      buildOnlineBoard();
      Snd.start();
      initVoiceChat();
      pollGameState();
      pollInterval = setInterval(pollGameState, 700);
    }

    const COL = ['red', 'green', 'yellow', 'blue'];
    const PATH = [
      [6,1],[6,2],[6,3],[6,4],[6,5],[5,6],[4,6],[3,6],[2,6],[1,6],[0,6],[0,7],[0,8],
      [1,8],[2,8],[3,8],[4,8],[5,8],[6,9],[6,10],[6,11],[6,12],[6,13],[6,14],[7,14],
      [8,14],[8,13],[8,12],[8,11],[8,10],[8,9],[9,8],[10,8],[11,8],[12,8],[13,8],[14,8],
      [14,7],[14,6],[13,6],[12,6],[11,6],[10,6],[9,6],[8,5],[8,4],[8,3],[8,2],[8,1],[8,0],[7,0],[6,0]
    ];
    const LANE = {
      red: [[7,1],[7,2],[7,3],[7,4],[7,5]],
      green: [[1,7],[2,7],[3,7],[4,7],[5,7]],
      yellow: [[7,13],[7,12],[7,11],[7,10],[7,9]],
      blue: [[13,7],[12,7],[11,7],[10,7],[9,7]]
    };
    const OFF = { red: [0,0], green: [0,9], yellow: [9,9], blue: [9,0] };
    const SP = [[1.7, 1.7], [1.7, 3.3], [3.3, 1.7], [3.3, 3.3]];
    const BASE_YARDS = {};
    COL.forEach(c => BASE_YARDS[c] = SP.map(([r, k]) => [r + OFF[c][0], k + OFF[c][1]]));

    const HC = { red: [7, 6.4], green: [6.4, 7], yellow: [7, 7.6], blue: [7.6, 7] };
    const START_OFFSET = { red: 0, green: 13, yellow: 26, blue: 39 };

    const tokenEls = { red: [], green: [], yellow: [], blue: [] };
    const tokenPos = {
      red: [-1, -1, -1, -1],
      green: [-1, -1, -1, -1],
      yellow: [-1, -1, -1, -1],
      blue: [-1, -1, -1, -1]
    };

    const sleep = ms => new Promise(r => setTimeout(r, ms));
    let isAnimatingMove = false;
    let lastSeenRollSeq = 0;
    let lastSeenMoveSeq = 0;
    let firstStateLoaded = false;

    function getCoords(c, idx, pos) {
      if (pos < 0) return BASE_YARDS[c][idx];
      if (pos <= 50) {
        const abs = (START_OFFSET[c] + pos) % 52;
        return PATH[abs];
      }
      if (pos < 56) {
        return LANE[c][pos - 51];
      }
      return HC[c];
    }

    function placeOnlineToken(c, i) {
      const pos = tokenPos[c][i];
      const [r, k] = getCoords(c, i, pos);
      const el = tokenEls[c][i];
      if (el) {
        el.style.left = (k * 100 / 15) + '%';
        el.style.top = (r * 100 / 15) + '%';
      }
    }

    function layoutOnlineTokens() {
      const g = {};
      COL.forEach(c => {
        for (let i = 0; i < 4; i++) {
          const q = tokenPos[c][i];
          if (q >= 0 && q < 56) {
            const [r, k] = getCoords(c, i, q);
            const key = `${r},${k}`;
            (g[key] = g[key] || []).push(tokenEls[c][i]);
          }
        }
      });

      COL.forEach(c => {
        for (let i = 0; i < 4; i++) {
          if (tokenEls[c][i]) tokenEls[c][i].style.transform = '';
        }
      });

      Object.values(g).forEach(group => {
        if (group.length > 1) {
          const n = group.length;
          group.forEach((el, j) => {
            const x = (j - (n - 1) / 2) * 24;
            el.style.transform = `translateX(${x}%) scale(${n > 2 ? 0.74 : 0.84})`;
          });
        }
      });
    }

    function hop(el) {
      const inner = el.querySelector('.tk-inner') || el.firstChild;
      if (inner) {
        inner.animate([
          { transform: 'translateY(0) scale(1)' },
          { transform: 'translateY(-36%) scale(1.14)', offset: 0.5 },
          { transform: 'translateY(0) scale(1)' }
        ], { duration: 170 });
      }
    }

    function buildOnlineBoard() {
      if (boardBuilt) return;
      boardBuilt = true;
      const b = document.getElementById('board');
      b.innerHTML = '';

      // Bases
      const AREA = { red: '1/1/7/7', green: '1/10/7/16', yellow: '10/10/16/16', blue: '10/1/16/7' };
      COL.forEach(c => {
        const bs = document.createElement('div');
        bs.className = `base ${c}`;
        bs.style.gridArea = AREA[c];
        bs.innerHTML = '<div class="inner"></div>';
        b.appendChild(bs);
      });

      // Grid Cells
      const lk = {};
      COL.forEach(c => LANE[c].forEach(p => lk[p] = c));
      const START_T = { red: '6,1', green: '1,8', yellow: '8,13', blue: '13,6' };
      const STARS = [[8,2],[2,6],[6,12],[12,8]];
      const ARROWS = {
        '7,0': { dir: '→', c: '#e50914' },
        '0,7': { dir: '↓', c: '#009933' },
        '7,14': { dir: '←', c: '#ffcc00' },
        '14,7': { dir: '↑', c: '#0077dd' }
      };

      for (let r = 0; r < 15; r++) {
        for (let k = 0; k < 15; k++) {
          if ((r<6&&k<6)||(r<6&&k>8)||(r>8&&k>8)||(r>8&&k<6)||(r>=6&&r<=8&&k>=6&&k<=8)) continue;
          const cell = document.createElement('div');
          cell.className = 'c';
          cell.style.gridArea = `${r+1}/${k+1}`;
          const key = `${r},${k}`;
          if (lk[key]) cell.classList.add(lk[key]);
          Object.entries(START_T).forEach(([c, st]) => { if (st === key) cell.classList.add(c); });
          
          if (STARS.some(([sr, sk]) => sr === r && sk === k)) {
            cell.innerHTML = '<span class="star-icon">☆</span>';
          } else if (ARROWS[key]) {
            cell.innerHTML = `<span class="ar-arrow" style="color:${ARROWS[key].c}">${ARROWS[key].dir}</span>`;
          }
          b.appendChild(cell);
        }
      }

      // Center
      const ct = document.createElement('div');
      ct.className = 'ctr';
      b.appendChild(ct);

      // Yard Slots
      COL.forEach(c => BASE_YARDS[c].forEach(([r, k]) => {
        const s = document.createElement('div');
        s.className = `sl ${c}`;
        s.style.left = (k * 100 / 15) + '%';
        s.style.top = (r * 100 / 15) + '%';
        b.appendChild(s);
      }));

      // Pre-create persistent goti elements
      COL.forEach(c => {
        tokenEls[c] = [];
        for (let i = 0; i < 4; i++) {
          const t = document.createElement('div');
          t.className = `tk ${c}`;
          t.innerHTML = `<div class="tk-inner"><svg viewBox="0 0 46 64"><use href="#pn"/></svg></div>`;
          b.appendChild(t);
          tokenEls[c].push(t);
          placeOnlineToken(c, i);
        }
      });
      layoutOnlineTokens();

      // Initialize 3D dice cubes in each slot
      COL.forEach(c => {
        const dEl = document.getElementById(`die-${c}`);
        if (dEl) face(dEl, 1);
      });

      // Bind Dice click for my turn
      document.querySelectorAll('.slot').forEach(s => {
        s.querySelector('.db').onclick = () => {
          if (s.dataset.c === myColor && currentTurnColor === myColor) {
            handleDiceRoll();
          }
        };
      });

      // Mark "You" on bottom card and hide speaker button for myself
      const mySpk = document.getElementById(`spk-${myColor}`);
      if (mySpk) mySpk.style.display = 'none';
    }

    // ----------------------------------------------------
    // 3D DICE LOGIC & STEP-BY-STEP ANIMATIONS
    // ----------------------------------------------------
    const PIPS = {
      1: [4],
      2: [0, 8],
      3: [0, 4, 8],
      4: [0, 2, 6, 8],
      5: [0, 2, 4, 6, 8],
      6: [0, 2, 3, 5, 6, 8]
    };
    const ORI = {
      1: [0, 0],
      6: [0, 180],
      2: [0, -90],
      5: [0, 90],
      3: [-90, 0],
      4: [90, 0]
    };

    function cube(el) {
      if (el.firstChild) return el.firstChild;
      const c = document.createElement('div');
      c.className = 'cube';
      c.innerHTML = [1, 2, 3, 4, 5, 6].map(n =>
        `<div class="f f${n}">${Array.from({ length: 9 }, (_, i) => `<i class="${PIPS[n].includes(i) ? 'on' : ''}"></i>`).join('')}</div>`
      ).join('');
      el.appendChild(c);
      return c;
    }

    function face(el, v) {
      if (!el) return;
      const [x, y] = ORI[v] || [0, 0];
      cube(el).style.transform = `rotateX(${x}deg) rotateY(${y}deg)`;
      el.classList.add('show');
    }

    async function tumble(el, down, d) {
      if (!el) return;
      el.classList.add('big', 'show');
      const c = cube(el);
      const s = down ? 1 : -1;
      const [fx, fy] = ORI[d] || [0, 0];

      c.style.transform = `rotateX(${fx}deg) rotateY(${fy}deg)`;

      const t0 = `rotateX(${fx - 720}deg) rotateY(${fy - 1080}deg) rotateZ(0deg)`;
      const tm = `rotateX(${fx - 360}deg) rotateY(${fy - 540}deg) rotateZ(20deg)`;
      const t1 = `rotateX(${fx}deg) rotateY(${fy}deg) rotateZ(0deg)`;

      const a1 = c.animate([{ transform: t0 }, { transform: tm, offset: 0.5 }, { transform: t1 }], {
        duration: 520,
        easing: 'cubic-bezier(.22,.75,.35,1)'
      });
      const a2 = el.animate([
        { transform: 'translate3d(0,0,0) scale(1)' },
        { transform: `translate3d(2px,${s * 2}px,0) scale(1.22)`, offset: 0.35 },
        { transform: `translate3d(-1px,${s * 3}px,0) scale(1.26)`, offset: 0.65 },
        { transform: 'translate3d(0,0,0) scale(1)' }
      ], {
        duration: 520,
        easing: 'ease-in-out'
      });

      await Promise.all([a1.finished, a2.finished]).catch(() => {});
      el.classList.remove('big');
      face(el, d);
    }

    function popBonusBanner(msg) {
      const b = document.createElement('div');
      b.className = 'bonus-banner';
      b.textContent = msg || 'EXTRA TURN! 🎉';
      document.body.appendChild(b);
      setTimeout(() => b.remove(), 1300);
    }

    // Move animation with Snd.open(), Snd.kill(), and step vibrations
    async function animateMoveSequence(move) {
      if (!move) return;
      isAnimatingMove = true;
      const c = move.color;
      const idx = move.token_index;
      const fromPos = move.from_pos;
      const toPos = move.to_pos;
      const tEl = tokenEls[c][idx];
      if (tEl) tEl.style.zIndex = 16;

      // 1. Goti opening from base on 6
      if (fromPos < 0 && toPos === 0) {
        tokenPos[c][idx] = 0;
        Snd.open(); // Authentic open chime & 60ms vibration!
        if (tEl) hop(tEl);
        placeOnlineToken(c, idx);
        layoutOnlineTokens();
        await sleep(220);
      } else if (toPos > fromPos) {
        // Step by step on track
        for (let s = fromPos + 1; s <= toPos; s++) {
          tokenPos[c][idx] = s;
          Snd.move();
          if (tEl) hop(tEl);
          placeOnlineToken(c, idx);
          layoutOnlineTokens();
          await sleep(180);
        }
      }
      if (tEl) tEl.style.zIndex = '';

      // 2. Capture & Snake Retreat
      if (move.killed) {
        const vic = move.killed; // { color, token_index, from_pos }
        const victimEl = tokenEls[vic.color][vic.token_index];
        if (victimEl) {
          victimEl.style.zIndex = 15;
          victimEl.classList.add('retreating');
          Snd.kill(); // Authentic capture impact & intense vibration!
          await sleep(100);

          for (let backPos = vic.from_pos - 1; backPos >= 0; backPos--) {
            tokenPos[vic.color][vic.token_index] = backPos;
            placeOnlineToken(vic.color, vic.token_index);
            layoutOnlineTokens();
            Snd.retreatStep(backPos, vic.from_pos);
            await sleep(38);
          }
          tokenPos[vic.color][vic.token_index] = -1;
          placeOnlineToken(vic.color, vic.token_index);
          layoutOnlineTokens();
          victimEl.style.zIndex = '';
          victimEl.classList.remove('retreating');
        }
      }
      isAnimatingMove = false;
    }

    let isRolling = false;

    async function handleDiceRoll() {
      if (isRolling || isAnimatingMove || currentTurnColor !== myColor) return;
      isRolling = true;
      Snd.unlock();
      Snd.dice();

      const el = document.getElementById(`die-${myColor}`);
      const isTopRow = (myColor === 'red' || myColor === 'green');

      // Zero latency tumble animation
      el.classList.add('big', 'show');
      const c = cube(el);
      const s = isTopRow ? 1 : -1;
      const a1 = c.animate([
        { transform: 'rotateX(0deg) rotateY(0deg) rotateZ(0deg)' },
        { transform: 'rotateX(360deg) rotateY(720deg) rotateZ(45deg)', offset: 0.5 },
        { transform: 'rotateX(720deg) rotateY(1440deg) rotateZ(0deg)' }
      ], { duration: 440, easing: 'cubic-bezier(.22,.75,.35,1)' });

      const a2 = el.animate([
        { transform: 'translate3d(0,0,0) scale(1)' },
        { transform: `translate3d(2px,${s * 2}px,0) scale(1.22)`, offset: 0.35 },
        { transform: `translate3d(-1px,${s * 3}px,0) scale(1.26)`, offset: 0.65 },
        { transform: 'translate3d(0,0,0) scale(1)' }
      ], { duration: 440, easing: 'ease-in-out' });

      const spinPromise = Promise.all([a1.finished, a2.finished]).catch(() => {});

      const fd = new FormData();
      fd.append('action', 'roll_dice');
      fd.append('room_code', myCode);
      fd.append('token', myToken);
      fd.append('color', myColor);

      try {
        const fetchPromise = fetch('api/online.php', { method: 'POST', body: fd }).then(r => r.json());
        const [res] = await Promise.all([fetchPromise, spinPromise]);

        el.classList.remove('big');

        if (res && res.ok) {
          const d = +res.dice;
          if (res.roll_seq) lastSeenRollSeq = res.roll_seq;
          face(el, d);
          if (d === 6) {
            Snd.bonus();
            popBonusBanner('ROLLED 6! EXTRA TURN! 🎲');
          }
          pollGameState();
        } else {
          el.classList.remove('big');
          showToast((res && res.error) || 'Cannot roll dice.');
        }
      } catch (e) {
        el.classList.remove('big');
      }
      isRolling = false;
    }

    async function handleTokenClick(tokenIndex) {
      if (isAnimatingMove) return;
      COL.forEach(c => {
        for (let i = 0; i < 4; i++) {
          if (tokenEls[c][i]) {
            tokenEls[c][i].classList.remove('go');
            tokenEls[c][i].onclick = null;
          }
        }
      });

      const fd = new FormData();
      fd.append('action', 'move_token');
      fd.append('room_code', myCode);
      fd.append('token', myToken);
      fd.append('token_index', tokenIndex);

      try {
        const res = await fetch('api/online.php', { method: 'POST', body: fd }).then(r => r.json());
        if (res.ok && res.last_move) {
          lastSeenMoveSeq = res.last_move.seq;
          await animateMoveSequence(res.last_move);
          if (res.kill) {
            popBonusBanner('CAPTURED! EXTRA TURN! ⚔️');
          }
          pollGameState();
        } else if (!res.ok) {
          showToast(res.error || 'Cannot move goti.');
          pollGameState();
        }
      } catch (e) {}
    }

    async function pollGameState() {
      if (isAnimatingMove) return;
      try {
        const res = await fetch(`api/online.php?action=game_state&code=${myCode}`).then(r => r.json());
        if (!res.ok) return;

        const prevTurn = currentTurnColor;
        currentTurnColor = res.room.turn_color;
        const diceVal = parseInt(res.room.dice_value) || 0;
        const diceState = res.room.dice_state;
        const serverRollSeq = parseInt(res.room.roll_seq) || 0;
        const serverMoveSeq = parseInt(res.room.move_seq) || 0;

        if (prevTurn !== currentTurnColor && currentTurnColor === myColor) {
          Snd.turn();
        }

        // Update player names
        res.players.forEach(p => {
          const el = document.getElementById(`name-${p.color}`);
          if (el) {
            el.textContent = (p.color === myColor) ? `${p.player_name} (You)` : p.player_name;
          }
        });

        // Update active slot highlighting & bouncing arrow
        COL.forEach(c => {
          const s = document.getElementById(`slot-${c}`);
          if (!s) return;
          const isTurn = (c === currentTurnColor);
          s.classList.toggle('act', isTurn);
        });

        // First state load
        if (!firstStateLoaded) {
          firstStateLoaded = true;
          lastSeenRollSeq = serverRollSeq;
          lastSeenMoveSeq = serverMoveSeq;
          res.tokens.forEach(tk => {
            tokenPos[tk.color][tk.token_index] = parseInt(tk.pos);
            placeOnlineToken(tk.color, tk.token_index);
          });
          layoutOnlineTokens();
          if (diceVal > 0) {
            const turnDie = document.getElementById(`die-${currentTurnColor}`);
            if (turnDie) face(turnDie, diceVal);
          }
        }

        // 3D Dice Roll Sync across room
        if (serverRollSeq > lastSeenRollSeq) {
          lastSeenRollSeq = serverRollSeq;
          const turnDie = document.getElementById(`die-${currentTurnColor}`);
          if (turnDie && diceVal > 0) {
            if (currentTurnColor !== myColor) {
              Snd.dice();
              const isTop = (currentTurnColor === 'red' || currentTurnColor === 'green');
              tumble(turnDie, isTop, diceVal);
            } else {
              face(turnDie, diceVal);
            }
          }
        } else if (diceVal > 0) {
          const turnDie = document.getElementById(`die-${currentTurnColor}`);
          if (turnDie) face(turnDie, diceVal);
        }

        // Move Sync across room
        if (serverMoveSeq > lastSeenMoveSeq && res.room.last_move) {
          lastSeenMoveSeq = serverMoveSeq;
          await animateMoveSequence(res.room.last_move);
        }

        // Reconcile token positions
        if (!isAnimatingMove) {
          res.tokens.forEach(tk => {
            tokenPos[tk.color][tk.token_index] = parseInt(tk.pos);
            placeOnlineToken(tk.color, tk.token_index);
          });
          layoutOnlineTokens();
        }

        // Update status bar
        const statusEl = document.getElementById('game-status-bar');
        if (res.room.status === 'finished') {
          statusEl.textContent = `🏆 ${res.room.winner.toUpperCase()} WON THE GAME!`;
          statusEl.style.color = '#ffd700';
          Snd.win();
          clearInterval(pollInterval);
          return;
        }

        if (currentTurnColor === myColor) {
          if (diceState === 'roll') {
            statusEl.textContent = '👉 YOUR TURN: Tap Dice to Roll!';
          } else {
            statusEl.textContent = `👉 Rolled ${diceVal}! Tap glowing goti to move!`;
          }
          statusEl.style.color = '#22c55e';
        } else {
          statusEl.textContent = `Waiting for ${currentTurnColor.toUpperCase()} to ${diceState === 'roll' ? 'roll dice' : 'move'}...`;
          statusEl.style.color = '#ffd700';
        }

        // Highlight movable gotis on my turn
        COL.forEach(c => {
          for (let i = 0; i < 4; i++) {
            const el = tokenEls[c][i];
            if (el) {
              el.classList.remove('go');
              el.onclick = null;
            }
          }
        });

        if (!isAnimatingMove && currentTurnColor === myColor && diceState === 'move' && !isRolling) {
          for (let i = 0; i < 4; i++) {
            const pos = tokenPos[myColor][i];
            const canOpen = (pos === -1 && diceVal === 6);
            const canMoveTrack = (pos >= 0 && (pos + diceVal) <= 56);
            if (canOpen || canMoveTrack) {
              const el = tokenEls[myColor][i];
              if (el) {
                el.classList.add('go');
                const tokenIdx = i;
                el.onclick = () => handleTokenClick(tokenIdx);
              }
            }
          }
        }
      } catch (e) {}
    }

    // ----------------------------------------------------
    // WEBRTC P2P VOICE CHAT & INDIVIDUAL SPEAKER CONTROLS
    // ----------------------------------------------------
    let localVoiceStream = null;
    let isLocalMicMuted = false;
    let isMasterVoiceDeafened = false;
    const peerMuted = { red: false, green: false, yellow: false, blue: false };
    const peerConnections = {};
    const peerAudioEls = {};
    const peerAnalysers = {};
    let voiceAudioCtx = null;
    let localAnalyser = null;
    let voiceSignalsPoll = null;

    const rtcConfig = {
      iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
        { urls: 'stun:stun2.l.google.com:19302' }
      ]
    };

    async function initVoiceChat() {
      try {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
          showToast('Voice Chat: Mic not supported in this browser.');
          return;
        }

        // Initialize AudioContext
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (AudioContextClass) {
          voiceAudioCtx = new AudioContextClass();
        }

        // Acquire microphone stream
        try {
          localVoiceStream = await navigator.mediaDevices.getUserMedia({
            audio: { echoCancellation: true, noiseSuppression: true, autoGainControl: true },
            video: false
          });
        } catch (micErr) {
          showToast('Voice Chat: Mic permission denied. Voice listening only.');
        }

        // Setup local audio analyzer for speech pulse
        if (localVoiceStream && voiceAudioCtx) {
          try {
            const src = voiceAudioCtx.createMediaStreamSource(localVoiceStream);
            localAnalyser = voiceAudioCtx.createAnalyser();
            localAnalyser.fftSize = 64;
            src.connect(localAnalyser);
          } catch(e) {}
        }

        // Setup P2P connection to other colors
        COL.forEach(c => {
          if (c !== myColor) {
            setupPeerAudioElement(c);
            // Lexicographical ordering decides who initiates WebRTC offer
            if (COL.indexOf(myColor) < COL.indexOf(c)) {
              createPeerOffer(c);
            }
          }
        });

        // Start voice signal polling
        if (!voiceSignalsPoll) {
          voiceSignalsPoll = setInterval(pollVoiceSignals, 750);
        }

        // Start speaking level monitor loop
        setInterval(monitorSpeakingLevels, 120);

        showToast('🎙️ Voice Chat Connected!');
      } catch (err) {
        console.warn('Voice chat init notice:', err);
      }
    }

    function setupPeerAudioElement(c) {
      if (!peerAudioEls[c]) {
        const audio = document.createElement('audio');
        audio.autoplay = true;
        audio.playsInline = true;
        document.body.appendChild(audio);
        peerAudioEls[c] = audio;
      }
    }

    function getOrCreatePeerConnection(peerColor) {
      if (peerConnections[peerColor]) return peerConnections[peerColor];

      const pc = new RTCPeerConnection(rtcConfig);

      // Attach our local mic stream if available
      if (localVoiceStream) {
        localVoiceStream.getTracks().forEach(track => {
          pc.addTrack(track, localVoiceStream);
        });
      }

      // Handle ICE candidates
      pc.onicecandidate = (event) => {
        if (event.candidate) {
          sendVoiceSignal(peerColor, 'candidate', JSON.stringify(event.candidate));
        }
      };

      // Handle incoming remote audio track
      pc.ontrack = (event) => {
        if (!COL.includes(peerColor)) return;
        setupPeerAudioElement(peerColor);
        const remoteStream = event.streams[0];
        peerAudioEls[peerColor].srcObject = remoteStream;
        peerAudioEls[peerColor].muted = isMasterVoiceDeafened || !!peerMuted[peerColor];

        // Attach analyser node to monitor speaking volume
        if (voiceAudioCtx) {
          try {
            const src = voiceAudioCtx.createMediaStreamSource(remoteStream);
            const an = voiceAudioCtx.createAnalyser();
            an.fftSize = 64;
            src.connect(an);
            peerAnalysers[peerColor] = an;
          } catch (e) {}
        }
      };

      peerConnections[peerColor] = pc;
      return pc;
    }

    async function createPeerOffer(peerColor) {
      try {
        const pc = getOrCreatePeerConnection(peerColor);
        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        sendVoiceSignal(peerColor, 'offer', JSON.stringify(offer));
      } catch (e) {}
    }

    async function sendVoiceSignal(toColor, type, data) {
      const fd = new FormData();
      fd.append('action', 'send_voice_signal');
      fd.append('room_code', myCode);
      fd.append('token', myToken);
      fd.append('to_color', toColor);
      fd.append('signal_type', type);
      fd.append('signal_data', data);
      await fetch('api/online.php', { method: 'POST', body: fd }).catch(() => {});
    }

    async function pollVoiceSignals() {
      try {
        const res = await fetch(`api/online.php?action=get_voice_signals&room_code=${myCode}&token=${myToken}`).then(r => r.json());
        if (!res.ok || !res.signals) return;

        for (const sig of res.signals) {
          const fromColor = sig.from_color;
          const pc = getOrCreatePeerConnection(fromColor);

          if (sig.signal_type === 'offer') {
            await pc.setRemoteDescription(new RTCSessionDescription(JSON.parse(sig.signal_data)));
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            sendVoiceSignal(fromColor, 'answer', JSON.stringify(answer));
          } else if (sig.signal_type === 'answer') {
            if (pc.signalingState !== 'stable') {
              await pc.setRemoteDescription(new RTCSessionDescription(JSON.parse(sig.signal_data)));
            }
          } else if (sig.signal_type === 'candidate') {
            try {
              await pc.addIceCandidate(new RTCIceCandidate(JSON.parse(sig.signal_data)));
            } catch (e) {}
          }
        }
      } catch (e) {}
    }

    // Toggle player's own mic (mute / unmute)
    function toggleLocalMic() {
      Snd.click();
      isLocalMicMuted = !isLocalMicMuted;
      if (localVoiceStream) {
        localVoiceStream.getAudioTracks().forEach(t => t.enabled = !isLocalMicMuted);
      }
      const b = document.getElementById('btn-mic');
      const icon = document.getElementById('mic-icon');
      const lbl = document.getElementById('mic-label');

      if (isLocalMicMuted) {
        b.className = 'btn-v mic-off';
        icon.textContent = '🔇';
        lbl.textContent = 'MUTED';
        showToast('Microphone Muted');
      } else {
        b.className = 'btn-v mic-live';
        icon.textContent = '🎙️';
        lbl.textContent = 'MIC ON';
        showToast('Microphone Live');
      }
    }

    // Master Voice Sound Toggle (Deafen all incoming voice audio)
    function toggleMasterVoice() {
      Snd.click();
      isMasterVoiceDeafened = !isMasterVoiceDeafened;
      Object.keys(peerAudioEls).forEach(c => {
        peerAudioEls[c].muted = isMasterVoiceDeafened || !!peerMuted[c];
      });

      const b = document.getElementById('btn-deafen');
      const icon = document.getElementById('deafen-icon');
      const lbl = document.getElementById('deafen-label');

      if (isMasterVoiceDeafened) {
        b.className = 'btn-v deafen-on';
        icon.textContent = '🔇';
        lbl.textContent = 'DEAFENED';
        showToast('Voice Chat Audio Turned Off');
      } else {
        b.className = 'btn-v';
        icon.textContent = '🔊';
        lbl.textContent = 'VOICE';
        showToast('Voice Chat Audio Turned On');
      }
    }

    // Individual per-player speaker mute toggle
    function togglePeerMute(peerColor) {
      Snd.click();
      peerMuted[peerColor] = !peerMuted[peerColor];
      if (peerAudioEls[peerColor]) {
        peerAudioEls[peerColor].muted = isMasterVoiceDeafened || peerMuted[peerColor];
      }
      const spkBtn = document.getElementById(`spk-${peerColor}`);
      if (spkBtn) {
        spkBtn.textContent = peerMuted[peerColor] ? '🔇' : '🔊';
        spkBtn.classList.toggle('is-muted', peerMuted[peerColor]);
      }
      const nameEl = document.getElementById(`name-${peerColor}`);
      const pName = nameEl ? nameEl.textContent : peerColor.toUpperCase();
      showToast(peerMuted[peerColor] ? `Muted ${pName}` : `Unmuted ${pName}`);
    }

    // Sound FX Mute toggle
    function toggleSfx() {
      const muted = Snd.toggle();
      document.getElementById('btn-sfx').textContent = muted ? '🔕' : '🔔';
      showToast(muted ? 'Sound Effects Muted' : 'Sound Effects Unmuted');
    }

    // Speech Volume Detection (animates green aura on player's card when speaking)
    function monitorSpeakingLevels() {
      // 1. Local mic level
      if (localAnalyser && !isLocalMicMuted) {
        const buf = new Uint8Array(localAnalyser.frequencyBinCount);
        localAnalyser.getByteFrequencyData(buf);
        let sum = 0;
        for (let i = 0; i < buf.length; i++) sum += buf[i];
        const avg = sum / buf.length;
        const isSpeaking = avg > 14;

        const slot = document.getElementById(`slot-${myColor}`);
        const wave = document.getElementById(`wave-${myColor}`);
        if (slot) slot.classList.toggle('is-speaking', isSpeaking);
        if (wave) wave.classList.toggle('active', isSpeaking);
      } else {
        const slot = document.getElementById(`slot-${myColor}`);
        const wave = document.getElementById(`wave-${myColor}`);
        if (slot) slot.classList.remove('is-speaking');
        if (wave) wave.classList.remove('active');
      }

      // 2. Remote peer levels
      Object.keys(peerAnalysers).forEach(c => {
        const an = peerAnalysers[c];
        if (an && !peerMuted[c] && !isMasterVoiceDeafened) {
          const buf = new Uint8Array(an.frequencyBinCount);
          an.getByteFrequencyData(buf);
          let sum = 0;
          for (let i = 0; i < buf.length; i++) sum += buf[i];
          const avg = sum / buf.length;
          const isSpeaking = avg > 14;

          const slot = document.getElementById(`slot-${c}`);
          const wave = document.getElementById(`wave-${c}`);
          if (slot) slot.classList.toggle('is-speaking', isSpeaking);
          if (wave) wave.classList.toggle('active', isSpeaking);
        } else {
          const slot = document.getElementById(`slot-${c}`);
          const wave = document.getElementById(`wave-${c}`);
          if (slot) slot.classList.remove('is-speaking');
          if (wave) wave.classList.remove('active');
        }
      });
    }

    function escapeHtml(str) {
      return (str || '').replace(/[&<>'"]/g, tag => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
      }[tag] || tag));
    }
  </script>
</body>
</html>
