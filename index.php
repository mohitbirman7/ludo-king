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
  
  <style>
    * {
      box-sizing: border-box;
      -webkit-tap-highlight-color: transparent;
      user-select: none;
    }
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      width: 100%;
      overflow: hidden;
      background: #0a2466;
      font-family: system-ui, -apple-system, sans-serif;
    }
    body {
      display: flex;
      justify-content: center;
      align-items: center;
    }
    /* Original Real Ludo King Mobile Screen UI */
    .screen {
      position: relative;
      height: 100vh;
      max-height: 100dvh;
      aspect-ratio: 728 / 1600;
      max-width: 100%;
      background: url("assets/img/home.jpg?v=<?=$v?>") center / 100% 100% no-repeat;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.7);
    }
    
    /* Sirf Pass N Play clickable button (Exactly positioned on original board button) */
    #pass {
      position: absolute;
      left: 52.5%;
      top: 55%;
      width: 27.3%;
      height: 9.6%;
      border-radius: 12px;
      display: block;
      cursor: pointer;
      z-index: 10;
    }
    #pass:active {
      background: rgba(255, 255, 255, 0.25);
      transform: scale(0.97);
    }

    /* Sound Mute Toggle Floating Button */
    #home-sound-btn {
      position: absolute;
      top: 1.2%;
      right: 3.5%;
      width: 38px;
      height: 38px;
      border-radius: 50%;
      border: 2px solid #ffd700;
      background: linear-gradient(180deg, #1852b4, #0c2d6e);
      color: #ffffff;
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 15;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.5);
    }
    #home-sound-btn:active {
      transform: scale(0.92);
    }

    /* Floating Download APK Button */
    #home-install-btn {
      position: absolute;
      top: 1.2%;
      left: 3.5%;
      padding: 6px 12px;
      border-radius: 20px;
      border: 2px solid #ffffff;
      background: linear-gradient(180deg, #ff9800, #ff6f00);
      color: #ffffff;
      font-size: 11px;
      font-weight: 900;
      display: flex;
      align-items: center;
      gap: 5px;
      cursor: pointer;
      z-index: 15;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.5);
      animation: pulseInstall 2s infinite ease-in-out;
    }
    @keyframes pulseInstall {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }

    @media (max-aspect-ratio: 728/1600) {
      .screen {
        height: auto;
        width: 100%;
        max-height: 100dvh;
      }
    }

    /* PWA Install Modal (Appears every time as requested) */
    .install-modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0, 8, 30, 0.82);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 200;
      padding: 16px;
      backdrop-filter: blur(5px);
      animation: fadeIn 0.25s ease-out;
    }
    .install-dialog {
      position: relative;
      width: min(92vw, 340px);
      background: linear-gradient(180deg, #0e3b96 0%, #061f52 100%);
      border: 3px solid #ffd700;
      border-radius: 22px;
      padding: 24px 20px 18px;
      text-align: center;
      box-shadow: 0 12px 35px rgba(0, 0, 0, 0.8), inset 0 1px 0 rgba(255, 255, 255, 0.3);
      animation: popIn 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);
    }
    .install-close {
      position: absolute;
      top: 10px;
      right: 12px;
      background: none;
      border: none;
      color: #8ab4f8;
      font-size: 20px;
      cursor: pointer;
    }
    .install-logo-wrap {
      position: relative;
      display: inline-block;
      margin-bottom: 8px;
    }
    .install-app-icon {
      width: 76px;
      height: 76px;
      border-radius: 18px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.6);
      border: 2px solid #ffd700;
    }
    .install-badge {
      position: absolute;
      bottom: -6px;
      left: 50%;
      transform: translateX(-50%);
      background: #e53935;
      color: #fff;
      font-family: system-ui, sans-serif;
      font-size: 8px;
      font-weight: 900;
      padding: 1px 6px;
      border-radius: 8px;
      border: 1px solid #fff;
    }
    .install-title {
      margin: 8px 0 2px;
      font-size: 24px;
      color: #ffd700;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.6);
    }
    .install-sub {
      margin: 0 0 14px;
      font-family: system-ui, sans-serif;
      font-size: 12px;
      color: #dbe6ff;
      line-height: 1.4;
    }
    .install-features {
      display: flex;
      justify-content: space-around;
      margin-bottom: 18px;
      background: rgba(0, 15, 50, 0.4);
      padding: 8px 6px;
      border-radius: 12px;
      border: 1px solid rgba(255, 215, 0, 0.3);
    }
    .feat-item {
      font-family: system-ui, sans-serif;
      font-size: 11px;
      font-weight: 700;
      color: #fffae0;
    }
    .btn-install-gold {
      width: 100%;
      padding: 13px;
      border-radius: 30px;
      border: 2.5px solid #ffffff;
      background: linear-gradient(180deg, #ffb300, #ff6f00);
      color: #ffffff;
      font: 900 16px system-ui, sans-serif;
      cursor: pointer;
      box-shadow: 0 6px 16px rgba(255, 111, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .btn-install-gold:active {
      transform: scale(0.96);
    }
    .install-instructions {
      margin: 12px 0 0;
      font-family: system-ui, sans-serif;
      font-size: 12px;
      line-height: 1.45;
      color: #ffe9a8;
      background: rgba(0, 0, 0, 0.35);
      padding: 8px 10px;
      border-radius: 8px;
      text-align: left;
    }
    .btn-play-browser {
      margin-top: 12px;
      background: none;
      border: none;
      color: #aac4ff;
      font-family: system-ui, sans-serif;
      font-size: 13px;
      cursor: pointer;
      text-decoration: underline;
    }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes popIn { from { transform: scale(0.85); opacity: 0; } to { transform: scale(1); opacity: 1; } }
  </style>
</head>
<body>
  <div class="screen" id="home-screen">
    <!-- Quick App Install Button -->
    <button id="home-install-btn" onclick="window.triggerAppDownload()">
      <span>📥</span> Install App
    </button>

    <!-- Sound Toggle Button -->
    <button id="home-sound-btn" title="Toggle Sound">🔊</button>

    <!-- Sirf Pass N Play Clickable (Takes to setup.php) -->
    <a id="pass" href="setup.php" aria-label="Pass N Play"></a>
  </div>

  <script src="assets/js/sound.js?v=<?=$v?>"></script>
  <script src="assets/js/home.js?v=<?=$v?>"></script>
  <script src="assets/js/pwa.js?v=<?=$v?>"></script>
</body>
</html>
