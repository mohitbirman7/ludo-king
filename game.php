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
  <link rel="manifest" href="manifest.json?v=<?=$v?>">
  <meta name="theme-color" content="#0a2466">
  <link rel="apple-touch-icon" href="assets/icons/icon-192.png?v=<?=$v?>">
  <link rel="icon" type="image/png" href="assets/icons/icon-192.png?v=<?=$v?>">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <title>Ludo King</title>
  <link href="https://fonts.googleapis.com/css2?family=Lilita+One&family=Roboto:wght@700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/game.css?v=<?=$v?>">

  <!-- INLINE CRITICAL GAME CSS (Ensures zero-delay rendering without external stylesheet dependency) -->
  <style>
  * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; user-select: none; }
  html, body {
    margin: 0; padding: 0; height: 100%; width: 100%; overflow: hidden;
    font-family: 'Roboto', 'Lilita One', system-ui, -apple-system, sans-serif; color: #ffffff;
  }
  body {
    display: flex; justify-content: center; align-items: center;
    background: radial-gradient(circle at 50% 35%, #1852b4 0%, #0a2d75 55%, #05163e 100%);
  }
  body::before {
    content: ""; position: fixed; inset: 0; opacity: 0.18; pointer-events: none;
    background-image: radial-gradient(#ffffff 1px, transparent 1px), repeating-linear-gradient(45deg, rgba(255,255,255,0.06) 0, rgba(255,255,255,0.06) 20px, transparent 20px, transparent 40px);
    background-size: 40px 40px;
  }
  body.night { background: radial-gradient(circle at 50% 35%, #3a1c71 0%, #1a0b36 60%, #090214 100%); }
  body.forest { background: radial-gradient(circle at 50% 35%, #1b663e 0%, #0b3d22 60%, #041b0e 100%); }
  .stage {
    position: relative; width: 100%; max-width: 440px; height: 100dvh; max-height: 100dvh;
    display: flex; flex-direction: column; justify-content: space-between; align-items: center;
    padding-top: max(env(safe-area-inset-top, 0px), 32px);
    padding-bottom: max(env(safe-area-inset-bottom, 0px), 10px);
    padding-left: max(env(safe-area-inset-left, 0px), 10px);
    padding-right: max(env(safe-area-inset-right, 0px), 10px);
    z-index: 1;
  }
  .top-nav {
    display: flex; justify-content: space-between; align-items: center;
    width: min(92vw, 48vh, 390px); margin: 0 auto 4px; padding: 0 2px;
  }
  .top-brand {
    font: 900 clamp(13px, 3.6vw, 16px) 'Lilita One', 'Roboto', sans-serif;
    color: #ffd700; letter-spacing: 1.5px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 10px rgba(255, 215, 0, 0.4);
    background: rgba(0, 15, 50, 0.55); border: 1.5px solid #ffd700;
    padding: 3px 14px; border-radius: 20px;
  }
  .top-btn {
    width: clamp(34px, 4.8vh, 38px); height: clamp(34px, 4.8vh, 38px);
    border-radius: 50%; border: 1.5px solid #ffd700;
    background: linear-gradient(180deg, #1852b4, #0c2d6e); color: #ffffff;
    font-size: clamp(14px, 2.2vh, 17px); cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.4);
  }
  .top-btn:active { transform: scale(0.93); }
  .rowS {
    display: flex; justify-content: space-between; align-items: center;
    width: min(92vw, 48vh, 390px); margin: 0 auto; padding: 2px 0;
  }
  .slot {
    position: relative; display: flex; align-items: center; justify-content: space-between;
    width: 44%; height: clamp(52px, 7.8vh, 64px); padding: 4px 6px;
    background: linear-gradient(180deg, #0e3d93 0%, #06235b 100%);
    border: 2.5px solid #ffcc00; border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.25);
    z-index: 5; transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  }
  .slot.r { flex-direction: row-reverse; }
  .slot.hide { visibility: hidden; pointer-events: none; }
  .pb {
    position: relative; height: 100%; display: flex; align-items: center; gap: 6px; padding: 0;
    background: transparent; border: none; box-shadow: none; flex: 1; min-width: 0;
  }
  .slot.r .pb { flex-direction: row-reverse; text-align: right; }
  .av-wrap { position: relative; width: 32px; height: 38px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
  .av-svg { width: 26px; height: 34px; filter: drop-shadow(0 2px 2px rgba(0, 0, 0, 0.4)); }
  .lvl {
    position: absolute; bottom: -4px; left: 50%; transform: translateX(-50%);
    background: #f5b301; color: #3e2200; font-size: 8.5px; font-weight: 900;
    padding: 0 4px; border-radius: 4px; border: 1px solid #ffffff; white-space: nowrap; line-height: 1.1;
  }
  .p-info { display: flex; flex-direction: column; overflow: hidden; flex: 1; min-width: 0; }
  .pn {
    font-size: clamp(10.5px, 2.7vw, 12.5px); font-weight: 900; color: #ffffff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.9); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  }
  .db {
    position: relative; width: clamp(42px, 6.2vh, 50px); height: clamp(42px, 6.2vh, 50px);
    background: radial-gradient(circle at 45% 45%, #ffffff 0%, #f6ece0 65%, #ded0bd 100%);
    border: 2px solid #bba38a; border-radius: 10px; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.35), 0 2px 4px rgba(0, 0, 0, 0.25);
    cursor: pointer; flex-shrink: 0;
  }
  .dots-timer { position: absolute; bottom: 2px; left: 0; right: 0; display: flex; justify-content: center; gap: 2.5px; }
  .dots-timer i { display: block; width: 3.5px; height: 3.5px; border-radius: 50%; background: #998a77; transition: background 0.2s; }
  .slot.act .dots-timer i { background: #22c55e; box-shadow: 0 0 4px #22c55e; animation: timerDot 1s infinite alternate; }
  .dots-timer i:nth-child(2) { animation-delay: 0.15s; }
  .dots-timer i:nth-child(3) { animation-delay: 0.3s; }
  .dots-timer i:nth-child(4) { animation-delay: 0.45s; }
  .dots-timer i:nth-child(5) { animation-delay: 0.6s; }
  @keyframes timerDot { 0% { opacity: 0.3; } 100% { opacity: 1; transform: scale(1.2); } }
  .slot.act {
    border-color: #ffe600;
    box-shadow: 0 0 14px rgba(255, 230, 0, 0.8), 0 4px 10px rgba(0, 0, 0, 0.5);
    animation: pulseSlot 0.9s infinite alternate ease-in-out;
  }
  .slot.act .db {
    border-color: #ffd700;
    box-shadow: 0 0 12px #ffe600, inset 0 2px 4px rgba(0, 0, 0, 0.25);
  }
  @keyframes pulseSlot { 0% { transform: scale(1); } 100% { transform: scale(1.03); } }
  .slot.act .db::before {
    content: ""; position: absolute; top: -23px; left: 50%; transform: translateX(-50%);
    width: 18px; height: 21px; background: linear-gradient(180deg, #ffd700, #ff8c00);
    clip-path: polygon(30% 0%, 70% 0%, 70% 55%, 100% 55%, 50% 100%, 0% 55%, 30% 55%);
    filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.7)); animation: promptBounceDown 0.6s infinite alternate ease-in-out;
    pointer-events: none; z-index: 25;
  }
  @keyframes promptBounceDown { 0% { transform: translate(-50%, 0); } 100% { transform: translate(-50%, -6px); } }
  .die { width: clamp(34px, 5.2vh, 42px); height: clamp(34px, 5.2vh, 42px); position: relative; visibility: hidden; perspective: 260px; isolation: isolate; }
  .die.show { visibility: visible; }
  .die.big { z-index: 30; will-change: transform; }
  .cube { position: absolute; inset: 0; transform-style: preserve-3d; will-change: transform; }
  .f {
    position: absolute; inset: 0; border-radius: 20%; display: grid; grid-template-columns: repeat(3, 1fr); grid-template-rows: repeat(3, 1fr);
    padding: 10%; backface-visibility: hidden; background: linear-gradient(145deg, #ffffff 0%, #f4eee2 55%, #dfd5be 100%);
    border: 1px solid rgba(0, 0, 0, 0.15); box-shadow: inset 0 -3px 0 rgba(0, 0, 0, 0.15), inset 0 2px 2px rgba(255, 255, 255, 0.95);
  }
  .f1 { transform: translateZ(21px); }
  .f6 { transform: rotateY(180deg) translateZ(21px); }
  .f2 { transform: rotateY(90deg) translateZ(21px); }
  .f5 { transform: rotateY(-90deg) translateZ(21px); }
  .f3 { transform: rotateX(90deg) translateZ(21px); }
  .f4 { transform: rotateX(-90deg) translateZ(21px); }
  .f i { width: 82%; height: 82%; margin: auto; border-radius: 50%; }
  .f i.on { background: radial-gradient(circle at 35% 35%, #424242 0%, #151515 85%); box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.9), 0 1px 1px rgba(255, 255, 255, 0.3); }
  #board-container { width: 100%; display: flex; justify-content: center; align-items: center; margin: auto 0; padding: 2px 0; }
  #board {
    position: relative; width: min(92vw, 48vh, 390px); height: min(92vw, 48vh, 390px);
    aspect-ratio: 1 / 1;
    display: grid; grid-template-columns: repeat(15, minmax(0, 1fr)); grid-template-rows: repeat(15, minmax(0, 1fr));
    background: #ffffff; border: 3.5px solid #d4a72c; border-radius: 8px;
    box-shadow: 0 0 0 2px #5a3c08, 0 8px 24px rgba(0, 0, 0, 0.65);
    overflow: visible;
  }
  .base { position: relative; display: flex; align-items: center; justify-content: center; min-width: 0; min-height: 0; }
  .base.red { background: linear-gradient(135deg, #e71d36 0%, #b80c23 100%); border-top-left-radius: 5px; }
  .base.green { background: linear-gradient(135deg, #00b050 0%, #007a36 100%); border-top-right-radius: 5px; }
  .base.yellow { background: linear-gradient(135deg, #ffc000 0%, #d49500 100%); border-bottom-right-radius: 5px; }
  .base.blue { background: linear-gradient(135deg, #0088ff 0%, #005bb5 100%); border-bottom-left-radius: 5px; }
  .base .inner {
    width: 66.6%; height: 66.6%; background: #ffffff; border-radius: 8px;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(0, 0, 0, 0.15); position: relative;
  }
  .base .nm { position: absolute; font-weight: 900; font-size: clamp(9px, 2.6vw, 13px); color: #ffffff; white-space: nowrap; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8); }
  .base.red .nm    { left: 7px; top: 50%; transform: translate(-50%, -50%) rotate(-90deg); }
  .base.green .nm  { top: 5px; left: 50%; transform: translateX(-50%) rotate(180deg); }
  .base.yellow .nm { right: 7px; top: 50%; transform: translate(50%, -50%) rotate(90deg); }
  .base.blue .nm   { bottom: 5px; left: 50%; transform: translateX(-50%); }
  .sl {
    position: absolute; width: 6.67%; height: 6.67%; border-radius: 50%; border: 2px solid var(--k);
    background: radial-gradient(circle, #ffffff 0%, #ffffff 32%, var(--k) 35%, var(--k) 100%);
    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.35); transform: scale(0.92);
  }
  .sl.red    { --k: #e71d36; }
  .sl.green  { --k: #00b050; }
  .sl.yellow { --k: #ffc000; }
  .sl.blue   { --k: #0088ff; }
  .c { background: #ffffff; border: 0.5px solid #d0d7de; display: flex; align-items: center; justify-content: center; position: relative; min-width: 0; min-height: 0; }
  .c.red    { background: linear-gradient(135deg, #e71d36, #c51128); }
  .c.green  { background: linear-gradient(135deg, #00b050, #00883d); }
  .c.yellow { background: linear-gradient(135deg, #ffc000, #e0a300); }
  .c.blue   { background: linear-gradient(135deg, #0088ff, #006bce); }
  .star-icon { font-size: clamp(14px, 3.8vw, 19px); color: #8c9ba5; filter: drop-shadow(0 1px 1px rgba(0,0,0,0.25)); font-weight: 900; }
  .ar-arrow { font-size: clamp(13px, 3.6vw, 18px); font-weight: 900; filter: drop-shadow(0 1px 1px rgba(0,0,0,0.3)); }
  .ctr {
    grid-area: 7 / 7 / 10 / 10;
    background: conic-gradient(from -45deg, #00b050 0 25%, #ffc000 0 50%, #0088ff 0 75%, #e71d36 0);
    display: flex; align-items: center; justify-content: center; position: relative;
  }
  .ctr-crown { font-size: clamp(18px, 4.6vw, 26px); filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.6)); animation: crownShine 2s infinite ease-in-out; }
  @keyframes crownShine { 0%, 100% { transform: scale(1); filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6)); } 50% { transform: scale(1.15); filter: drop-shadow(0 0 8px #ffd700); } }
  .tk {
    position: absolute; width: 6.67%; height: 6.67%; z-index: 10; display: flex; align-items: center;
    justify-content: center; pointer-events: none; transition: left 0.16s linear, top 0.16s linear;
    will-change: left, top, transform;
  }
  .tk-inner { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
  .tk svg { width: 95%; height: 125%; margin-top: -28%; filter: drop-shadow(0 3px 3px rgba(0, 0, 0, 0.55)); transform-origin: 50% 90%; overflow: visible; }
  .tk.red    { --k: #e71d36; }
  .tk.green  { --k: #00b050; }
  .tk.yellow { --k: #ffc000; }
  .tk.blue   { --k: #0088ff; }
  .tk.go { z-index: 16; pointer-events: auto; cursor: pointer; }
  .tk.go .tk-inner { animation: tokenHop 0.45s infinite alternate cubic-bezier(0.2, 0.8, 0.4, 1); filter: drop-shadow(0 0 6px #ffffff) drop-shadow(0 0 12px #ffe600); }
  @keyframes tokenHop { 0% { transform: translateY(0) scale(1); } 100% { transform: translateY(-32%) scale(1.15); } }
  .tk.strike { animation: strikeImpact 0.35s ease-out; }
  @keyframes strikeImpact { 0% { transform: scale(1); } 40% { transform: scale(1.4) rotate(15deg); } 100% { transform: scale(1) rotate(0deg); } }
  .tk.retreating { transition: left 0.04s linear, top 0.04s linear !important; opacity: 0.9; filter: brightness(1.2) drop-shadow(0 0 8px #ff4757); }
  .tk.home-celebrate { animation: homeSpin 0.7s ease-out; }
  @keyframes homeSpin { 0% { transform: scale(1) rotate(0); } 50% { transform: scale(1.4) rotate(180deg); } 100% { transform: scale(1) rotate(360deg); } }
  .foot { display: flex; justify-content: space-between; align-items: center; width: min(92vw, 48vh, 390px); margin: 0 auto; padding: 0; height: clamp(38px, 5.5vh, 44px); }
  .foot button {
    width: clamp(38px, 5.5vh, 44px); height: clamp(38px, 5.5vh, 44px); border-radius: 12px; border: 2px solid #e5c04b;
    background: linear-gradient(180deg, #1852b4, #0c2d6e); color: #ffffff; font-size: clamp(16px, 2.5vh, 20px);
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.4); cursor: pointer; display: flex; align-items: center; justify-content: center;
  }
  .foot button:active { transform: scale(0.94); }
  #msg {
    font-size: clamp(12px, 3.2vw, 15px); font-weight: 900; color: #ffe600; text-shadow: 0 1px 3px #000000, 0 0 6px rgba(255, 230, 0, 0.4);
    text-align: center; max-width: 65%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  }
  #gs {
    position: fixed; left: 50%; top: 48%; transform: translate(-50%, -50%); z-index: 50; padding: 8px 30px;
    background: linear-gradient(180deg, #0d3888, #051a44); border: 3.5px solid #ffd700; border-radius: 12px;
    font: 900 clamp(22px, 6vw, 32px) 'Roboto', sans-serif; color: #ffd700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8), 0 0 12px rgba(255, 215, 0, 0.6); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
    pointer-events: none; animation: gsBanner 1.8s forwards;
  }
  @keyframes gsBanner { 0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); } 20% { opacity: 1; transform: translate(-50%, -50%) scale(1.05); } 80% { opacity: 1; transform: translate(-50%, -50%) scale(1); } 100% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); visibility: hidden; } }
  .modal { position: fixed; inset: 0; background: rgba(0, 5, 20, 0.78); display: flex; align-items: center; justify-content: center; z-index: 100; backdrop-filter: blur(4px); }
  .modal[hidden] { display: none; }
  .box {
    width: min(88vw, 320px); padding: 24px 20px; background: linear-gradient(180deg, #103b8c 0%, #061e4e 100%);
    border: 3px solid #ffd700; border-radius: 18px; text-align: center;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.8), inset 0 1px 0 rgba(255, 255, 255, 0.2);
  }
  .pause-title { font-size: 22px; font-weight: 900; color: #ffd700; margin-bottom: 20px; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.6); }
  .gold {
    display: block; width: 100%; margin: 10px 0; padding: 12px; border-radius: 25px; border: 2.5px solid #ffd700;
    background: linear-gradient(180deg, #ffb300, #ff6f00); color: #ffffff; font: 900 16px 'Roboto', sans-serif;
    text-decoration: none; cursor: pointer; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.4);
  }
  .gold.quit-btn { background: linear-gradient(180deg, #e53935, #b71c1c); border-color: #ff8a80; }
  .gold:active { transform: scale(0.96); }
  .wscreen { position: fixed; inset: 0; z-index: 110; background: rgba(0, 8, 30, 0.85); display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
  .wscreen[hidden] { display: none; }
  .wbox { position: relative; width: min(90vw, 360px); }
  .logo { display: flex; justify-content: center; align-items: center; gap: 6px; position: relative; margin-bottom: 12px; font-family: 'Lilita One', sans-serif; }
  .logo b { width: 44px; height: 44px; border-radius: 50%; border: 3px solid #ffffff; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #ffffff; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); }
  .logo .crown { position: absolute; top: -34px; font-size: 38px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.6)); }
  .sub-king { position: absolute; bottom: -14px; font-size: 14px; letter-spacing: 2px; color: #ffd700; font-weight: 900; text-shadow: 0 2px 4px rgba(0,0,0,0.8); }
  .l1 { background: #0088ff; } .l2 { background: #e71d36; } .l3 { background: #00b050; } .l4 { background: #ffc000; color: #3e2200 !important; }
  .wp { border: 3px solid #ffd700; border-radius: 18px; padding: 18px 16px; background: linear-gradient(180deg, #103b8c 0%, #061e4e 100%); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7); }
  .wp h2 { margin: 0 0 12px; text-align: center; font: 900 26px 'Roboto', sans-serif; color: #ffd700; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.6); }
  .wr { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-bottom: 1px solid rgba(255, 255, 255, 0.12); }
  .cr { width: 28px; height: 24px; display: flex; align-items: center; justify-content: center; font: 900 13px 'Roboto', sans-serif; border-radius: 4px; }
  .cr.r1 { background: linear-gradient(#ffe066, #f59f00); color: #3e2200; }
  .cr.r2 { background: linear-gradient(#f1f3f5, #adb5bd); color: #212529; }
  .cr.r3 { background: linear-gradient(#ffc078, #d9480f); color: #3e2200; }
  .av { width: 22px; height: 30px; }
  .wn { flex: 1; font-weight: 700; font-size: 15px; }
  .wr b.wnr { color: #ffd700; } .wr b.los { color: #8ab4f8; }
  .wbtn { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-top: 16px; }
  .wbtn .gold { margin: 0; padding: 10px 16px; flex: 1; }
  .rb { width: 42px; height: 42px; border-radius: 50%; border: 2px solid #ffd700; background: linear-gradient(180deg, #1852b4, #0c2d6e); color: #ffffff; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
  #cf { position: fixed; inset: 0; z-index: 105; pointer-events: none; }
  </style>
</head>
<body>
  <!-- Token SVG Definitions -->
  <svg width="0" height="0" style="position:absolute">
    <defs>
      <linearGradient id="sg" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#ffffff"/>
        <stop offset="35%" stop-color="#f0f3f6"/>
        <stop offset="70%" stop-color="#cbd3da"/>
        <stop offset="100%" stop-color="#8d97a0"/>
      </linearGradient>
      <symbol id="pn" viewBox="0 0 40 52">
        <path d="M20 3C10 3 4 10 4 18c0 10 11 20 16 31 5-11 16-21 16-31C36 10 30 3 20 3z" fill="url(#sg)" stroke="#4a5568" stroke-width="1.6"/>
        <circle cx="20" cy="18" r="8.5" fill="var(--k)" stroke="#ffffff" stroke-width="2"/>
        <circle cx="17.5" cy="15.5" r="2.5" fill="#ffffff" opacity="0.6"/>
      </symbol>
    </defs>
  </svg>

  <div class="stage">
    <!-- Top Header Bar with Menu, Game Title, and Sound Toggle -->
    <div class="top-nav">
      <button class="top-btn" id="top-menu-btn" title="Game Menu">☰</button>
      <div class="top-brand">👑 LUDO KING</div>
      <button class="top-btn" id="top-sound-btn" title="Toggle Sound">🔊</button>
    </div>

    <!-- Top Player Bar -->
    <div class="rowS">
      <!-- Player Red (Top Left) -->
      <div class="slot l" data-c="red">
        <div class="pb">
          <div class="av-wrap">
            <svg class="av-svg" style="--k:#e8212c"><use href="#pn"/></svg>
            <span class="lvl">Lvl 1</span>
          </div>
          <div class="p-info">
            <span class="pn">Player 2</span>
          </div>
        </div>
        <div class="db" title="Roll Dice">
          <div class="die"></div>
          <div class="dots-timer"><i></i><i></i><i></i><i></i><i></i></div>
        </div>
      </div>

      <!-- Player Green (Top Right) -->
      <div class="slot r" data-c="green">
        <div class="pb">
          <div class="av-wrap">
            <svg class="av-svg" style="--k:#0c8f43"><use href="#pn"/></svg>
            <span class="lvl">Lvl 1</span>
          </div>
          <div class="p-info">
            <span class="pn">Player 3</span>
          </div>
        </div>
        <div class="db" title="Roll Dice">
          <div class="die"></div>
          <div class="dots-timer"><i></i><i></i><i></i><i></i><i></i></div>
        </div>
      </div>
    </div>

    <!-- Central Game Board -->
    <div id="board-container">
      <div id="board"></div>
    </div>

    <!-- Bottom Player Bar -->
    <div class="rowS">
      <!-- Player Blue (Bottom Left) -->
      <div class="slot l" data-c="blue">
        <div class="pb">
          <div class="av-wrap">
            <svg class="av-svg" style="--k:#1ea3f2"><use href="#pn"/></svg>
            <span class="lvl">Lvl 1</span>
          </div>
          <div class="p-info">
            <span class="pn">Player 1</span>
          </div>
        </div>
        <div class="db" title="Roll Dice">
          <div class="die"></div>
          <div class="dots-timer"><i></i><i></i><i></i><i></i><i></i></div>
        </div>
      </div>

      <!-- Player Yellow (Bottom Right) -->
      <div class="slot r" data-c="yellow">
        <div class="pb">
          <div class="av-wrap">
            <svg class="av-svg" style="--k:#fdd414"><use href="#pn"/></svg>
            <span class="lvl">Lvl 1</span>
          </div>
          <div class="p-info">
            <span class="pn">Player 4</span>
          </div>
        </div>
        <div class="db" title="Roll Dice">
          <div class="die"></div>
          <div class="dots-timer"><i></i><i></i><i></i><i></i><i></i></div>
        </div>
      </div>
    </div>

    <!-- Bottom Action Footer -->
    <div class="foot">
      <button id="menu" aria-label="Game Menu">☰</button>
      <div id="msg">Ludo King</div>
      <button id="mute" aria-label="Toggle Sound">🔊</button>
    </div>
  </div>

  <!-- Game Start Banner -->
  <div id="gs">GAME START</div>

  <!-- Victory Celebration Screen -->
  <div id="win" class="wscreen" hidden>
    <div class="wbox">
      <div class="logo">
        <span class="crown">👑</span>
        <b class="l1">L</b><b class="l2">U</b><b class="l3">D</b><b class="l4">O</b>
        <span class="sub-king">KING</span>
      </div>
      <div class="wp">
        <h2>Congratulations!</h2>
        <div id="wrows"></div>
        <div class="wbtn">
          <a class="gold" href="index.php" id="win-quit">Menu</a>
          <button class="rb" id="wsnd" title="Sound">🔊</button>
          <button class="rb" id="wshare" title="Share">↗</button>
          <button class="gold" id="replay">Replay</button>
        </div>
      </div>
    </div>
  </div>

  <!-- In-Game Pause Menu -->
  <div id="mn" class="modal" hidden>
    <div class="box">
      <div class="pause-title">GAME PAUSED</div>
      <button class="gold resume-btn" id="resume">▶ Resume Game</button>
      <button class="gold quit-btn" id="quit">✕ Quit Game</button>
    </div>
  </div>

  <script src="assets/js/security.js?v=<?=$v?>"></script>
  <script src="assets/js/sound.js?v=<?=$v?>"></script>
  <script src="assets/js/game.js?v=<?=$v?>"></script>
  <script src="assets/js/pwa.js?v=<?=$v?>"></script>
</body>
</html>
