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
  <title>Ludo King - Setup</title>
  <link href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/setup.css?v=<?=$v?>">

  <!-- INLINE CRITICAL SETUP CSS -->
  <style>
  *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
  html,body{margin:0;height:100%;overflow:hidden;background:#02050f}
  body{display:flex;justify-content:center;align-items:center;font-family:'Lilita One',system-ui,sans-serif;color:#fff}
  .stage{position:relative;height:min(100dvh,219.78vw);aspect-ratio:728/1600;container-type:inline-size;overflow:hidden;background:#020a22 url(assets/img/home.jpg) center/100% 100% no-repeat}
  .dim{position:absolute;inset:0;background:rgba(0,0,0,.86)}
  .ab{position:absolute}.ab img,button img{display:block;width:100%;height:100%}
  button{padding:0;border:0;background:none;cursor:pointer;font-family:inherit}
  .band{border-top:2px solid #ffb800;border-bottom:2px solid #ffb800;box-shadow:0 0 8px 2px rgba(255,160,0,.65)}
  .theme{overflow:visible}.theme img{object-fit:fill;-webkit-mask-image:radial-gradient(ellipse 100% 100% at 50% 50%,#000 70%,transparent 100%);mask-image:radial-gradient(ellipse 100% 100% at 50% 50%,#000 70%,transparent 100%)}
  .theme:active img{filter:brightness(1.25)}
  .tk1{position:absolute;height:6.25%;width:13.2%;top:34.56%;transition:transform .15s}.tk1 img{width:100%;height:100%;object-fit:contain}.tk1:active{transform:scale(.92)}
  .tk1.lk:active{animation:shake .35s}
  .tk1 .bx{position:absolute;left:19%;top:-2%;width:27%;height:29%;border:2px solid #f7b500;border-radius:16%;background:#0a2a7a;display:flex;align-items:center;justify-content:center;animation:pop .35s ease-out}
  .bx svg,.rad svg{width:78%;height:78%}
  @keyframes shake{25%{transform:translateX(-5px)}50%{transform:translateX(5px)}75%{transform:translateX(-3px)}}
  @keyframes pop{0%{transform:scale(0)}55%{transform:scale(1.4)}100%{transform:scale(1)}}
  .arw:active{transform:scale(.85)}.arw.dis{opacity:.4}
  .mrow{cursor:pointer}
  .pill{display:flex;align-items:center;justify-content:center;padding-left:calc(82/7.28*1cqw);padding-right:calc(12/7.28*1cqw);white-space:nowrap;border-radius:999px;border:calc(5/7.28*1cqw) solid #f6b100;background:linear-gradient(#5aa6ee 0,#2a73d3 35%,#0f3c9a 70%,#0a2a78);box-shadow:0 0 calc(5/7.28*1cqw) calc(2/7.28*1cqw) #ffb400,0 0 calc(22/7.28*1cqw) calc(8/7.28*1cqw) rgba(255,140,0,.55);transition:transform .12s}
  .pill.green{background:linear-gradient(#7be07a,#37b037 35%,#1b8a22 70%,#0f6a18)}
  .pill{font-family:'Lilita One',Impact,'Arial Narrow',sans-serif}.pill b{font-weight:400;font-size:calc(34/7.28*1cqw);text-shadow:0 calc(3/7.28*1cqw) 0 rgba(0,20,80,.8);letter-spacing:.3px}
  .two{display:flex;flex-direction:column;align-items:center;line-height:1;white-space:nowrap}.two small{font-size:calc(21/7.28*1cqw);text-shadow:0 2px 0 rgba(0,20,80,.8)}.two b{font-size:calc(30/7.28*1cqw)}
  .mrow:active .pill{transform:scale(.96)}.pill.press{animation:press .3s ease-out}
  @keyframes press{0%{transform:scale(1)}40%{transform:scale(1.07);filter:brightness(1.25)}100%{transform:scale(1)}}
  .rad{position:absolute;left:calc(-5/7.28*1cqw);top:calc(-5/7.28*1cqw);bottom:calc(-5/7.28*1cqw);aspect-ratio:1;border-radius:50%;border:calc(6/7.28*1cqw) solid #f6b100;background:radial-gradient(circle at 50% 40%,#0b2c80,#021244);box-shadow:inset 0 0 calc(12/7.28*1cqw) #000,0 0 calc(8/7.28*1cqw) #ffb400;display:flex;align-items:center;justify-content:center}
  .rad svg{display:none;width:68%;height:68%}.rad.on svg{display:block;animation:pop .38s ease-out}
  .nextb:active{transform:scale(.94)}.back{border-radius:18%;border:calc(5/7.28*1cqw) solid #f6b100;background:linear-gradient(#4a97e8,#1558c0);color:#ffc400;font-size:calc(46/7.28*1cqw);box-shadow:0 0 calc(14/7.28*1cqw) calc(4/7.28*1cqw) rgba(255,160,0,.8);line-height:1;text-shadow:0 2px 0 #3a1d00}
  .back:active{transform:scale(.9)}
  #s1[hidden],#s2[hidden]{display:none}
  #s2{position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;font-size:4.3cqw;padding-bottom:6%}
  .loadb{text-align:center;margin-bottom:6%;cursor:pointer}.loadb span{display:inline-block;padding:.35em 1.2em;border-radius:.6em;border:2px solid #f6b100;background:#123d9e;font-size:.9em;box-shadow:0 0 8px #ffb400}
  .s2band{border-top:2px solid #ffb800;border-bottom:2px solid #ffb800;box-shadow:0 0 8px 2px rgba(255,160,0,.65);background:linear-gradient(#0a3bb0,#062a8a 70%,#04195a);padding:.6em 0 .8em;text-align:center}
  .s2band h2{margin:0 0 .5em;font-weight:400;font-size:1.1em;color:#ffc400;text-shadow:-2px -2px 0 #3a1d00,2px -2px 0 #3a1d00,-2px 2px 0 #3a1d00,2px 2px 0 #3a1d00,0 3px 0 #3a1d00}
  .row{display:flex;align-items:center;gap:.6em;margin:.5em 0;justify-content:center}.row.off{opacity:.45;filter:grayscale(1)}
  .pin{width:1.1em;height:1.5em;border-radius:50% 50% 50% 50%/60% 60% 40% 40%;border:2px solid #fff;display:inline-block}.pin.blue{background:#1e88e5}.pin.red{background:#e53935}.pin.green{background:#2e9e4f}.pin.yellow{background:#f7c41f}
  .row input{width:9em;padding:.3em .5em;border:2px dashed #555;border-radius:6px;font:400 .85em 'Lilita One',system-ui;background:#fff;color:#111}.row input:disabled{background:#999}
  .counts{display:flex;justify-content:center;gap:.9em;padding:.5em 0;border-bottom:2px solid #f6b100;background:rgba(3,14,60,.9)}
  .counts button{width:2.8em;height:2.1em;border-radius:.6em;border:2px solid #f6b100;background:#0c3189;color:#fff;font-size:1em;box-shadow:0 0 8px #f6b100}.counts .act{background:#031047;box-shadow:none;border-color:#7a5b00}.counts button:disabled{opacity:.35}
  .pbtn{display:block;margin:.7em auto 0;min-width:7em;padding:.35em 1.2em;border-radius:999px;border:4px solid #f6b100;background:linear-gradient(#4a97e8,#0e3590);color:#fff;font:400 1.15em 'Lilita One',system-ui;text-shadow:0 2px 0 #0a1f5c;box-shadow:0 0 14px 3px rgba(255,150,0,.8);line-height:1.1}
  .pbtn:active{transform:scale(.95)}.pbtn small{display:block;font-size:.5em}
  .modal{position:fixed;inset:0;background:rgba(0,0,0,.75);display:flex;align-items:center;justify-content:center;z-index:50;font-size:16px}.modal[hidden]{display:none}
  .box{position:relative;width:min(86vw,340px);padding:24px 18px 14px;background:#02103f;border:3px solid #f6b100;border-radius:12px;text-align:center;box-shadow:0 0 14px rgba(255,160,0,.7)}.box.left{text-align:left;font:400 16px/1.35 system-ui}.box h3{font:400 22px 'Lilita One';color:#ffc400;margin:0 0 8px}
  .x{position:absolute;right:-12px;top:-12px;width:30px;height:30px;border-radius:50%;background:#e53935;color:#fff;border:2px solid #fff}.tipt{color:#4cff6a;font-size:22px;margin:8px 0 12px}
  .toast{position:fixed;left:50%;bottom:8%;transform:translateX(-50%);background:#000c;border:2px solid #f6b100;padding:8px 18px;border-radius:20px;z-index:60;font-size:16px}.toast[hidden]{display:none}
  @media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
  </style>
</head>
<body>
<div class="stage" id="stage">
  <div class="dim"></div>
  <!-- STEP 1: Choose Token & Mode -->
  <div id="s1">
    <button class="ab theme" id="theme" style="left:26.786%;top:24.688%;width:44.643%;height:6.375%" aria-label="Select theme"><img src="assets/img/ui/theme.jpg" alt="Theme"></button>
    <div class="ab band" style="left:0.000%;top:31.312%;width:100.000%;height:10.688%;background:linear-gradient(rgb(19,25,15) 0.0%,rgb(1,30,72) 7.3%,rgb(1,43,101) 14.5%,rgb(0,55,129) 21.8%,rgb(1,66,156) 29.1%,rgb(1,76,177) 36.4%,rgb(0,80,191) 43.6%,rgb(1,78,182) 50.9%,rgb(2,68,162) 58.2%,rgb(1,58,137) 65.5%,rgb(1,45,106) 72.7%,rgb(1,32,78) 80.0%,rgb(1,20,50) 87.3%,rgb(1,12,32) 94.5%)"></div>
    <div class="ab band" style="left:0.000%;top:43.625%;width:100.000%;height:31.750%;background:linear-gradient(rgb(3,19,32) 0.0%,rgb(1,29,68) 2.4%,rgb(0,31,75) 4.8%,rgb(0,36,84) 7.2%,rgb(0,38,93) 9.6%,rgb(2,44,104) 12.0%,rgb(1,46,111) 14.4%,rgb(0,50,119) 16.8%,rgb(0,54,126) 19.2%,rgb(1,58,137) 21.6%,rgb(0,61,144) 24.0%,rgb(0,65,152) 26.3%,rgb(0,68,159) 28.7%,rgb(1,71,167) 31.1%,rgb(0,74,173) 33.5%,rgb(0,75,177) 35.9%,rgb(0,79,184) 38.3%,rgb(2,80,188) 40.7%,rgb(0,81,189) 43.1%,rgb(0,81,189) 45.5%,rgb(0,81,189) 47.9%,rgb(1,79,187) 50.3%,rgb(1,78,182) 52.7%,rgb(1,74,176) 55.1%,rgb(2,72,170) 57.5%,rgb(1,70,165) 59.9%,rgb(1,66,156) 62.3%,rgb(1,63,150) 64.7%,rgb(1,60,140) 67.1%,rgb(0,56,131) 69.5%,rgb(0,53,123) 71.9%,rgb(1,49,115) 74.3%,rgb(0,45,104) 76.6%,rgb(0,41,95) 79.0%,rgb(1,39,88) 81.4%,rgb(1,33,80) 83.8%,rgb(0,29,69) 86.2%,rgb(0,26,61) 88.6%,rgb(0,22,53) 91.0%,rgb(2,19,45) 93.4%,rgb(1,16,39) 95.8%,rgb(0,13,30) 98.2%)"></div>
    <img class="ab" src="assets/img/ui/t_token.png" style="left:32.967%;top:31.688%;width:34.341%;height:2.688%" alt="Token">
    <button class="ab arw" id="tl" style="left:12.637%;top:36.438%;width:5.495%;height:2.750%" aria-label="Previous Token"><img src="assets/img/ui/arl.png" alt="Left"></button>
    <button class="ab arw" id="tr" style="left:81.868%;top:36.438%;width:5.495%;height:2.750%" aria-label="Next Token"><img src="assets/img/ui/arr.png" alt="Right"></button>
    <div id="toks"></div>
    <img class="ab" src="assets/img/ui/t_game.png" style="left:32.967%;top:44.250%;width:34.341%;height:2.750%" alt="Game Modes">
    <button class="ab help" id="help" style="left:76.648%;top:44.125%;width:7.418%;height:3.125%" aria-label="Help"><img src="assets/img/ui/help.png" alt="Help"></button>
    <div class="mrow" data-m="classic"><img class="ab" src="assets/img/ui/ico0.png" style="left:23.352%;top:47.812%;width:17.857%;height:7.750%" alt=""><div class="ab pill" style="left:45.192%;top:49.188%;width:32.005%;height:5.500%"><i class="rad on"></i><b>Classic</b></div></div>
    <div class="mrow" data-m="team"><img class="ab" src="assets/img/ui/ico1.png" style="left:23.352%;top:57.250%;width:17.857%;height:7.750%" alt=""><div class="ab pill" style="left:45.192%;top:58.625%;width:32.005%;height:5.500%"><i class="rad"></i><span class="two"><small>Classic</small><b>Team Up</b></span></div></div>
    <div class="mrow" data-m="quick"><img class="ab" src="assets/img/ui/ico2.png" style="left:23.352%;top:66.750%;width:17.857%;height:7.750%" alt=""><div class="ab pill green" style="left:45.192%;top:68.062%;width:32.005%;height:5.500%"><i class="rad"></i><b>QUICK</b></div></div>
    <button class="ab nextb" id="next" style="left:32.967%;top:75.625%;width:34.341%;height:6.562%"><img src="assets/img/ui/next.png" alt="Next"></button>
  </div>
  <!-- STEP 2: Choose Players & Name -->
  <div id="s2" hidden>
    <div class="loadb" id="load-saved-btn"><span>⚡ Resume Saved Game</span></div>
    <div class="s2band"><h2>CHOOSE COLOR AND NAME</h2><div id="rows"></div></div>
    <div class="counts" id="counts"><button data-n="2">2P</button><button data-n="3">3P</button><button data-n="4" class="act">4P</button></div>
    <button class="pbtn" id="play">Play</button>
    <button class="pbtn tall" id="play1">Play<small>- One Token Out -</small></button>
  </div>
  <button class="ab back" id="back" style="left:4.808%;top:94.125%;width:9.615%;height:3.750%" aria-label="Back">&#8617;</button>
</div>
<div class="modal" id="tip" hidden><div class="box"><button class="x" id="tipx">&#10005;</button><p class="tipt">Keep one token out for all!</p><button class="pbtn" id="ok">OKAY</button></div></div>
<div class="modal" id="hp" hidden><div class="box left"><button class="x" id="hpx">&#10005;</button><h3>Game modes</h3>
  <p><b>Classic:</b> every player for themselves. Bring all 4 tokens home first.</p>
  <p><b>Classic Team Up:</b> 4 players, 2 teams. Partners sit opposite and win together.</p>
  <p><b>QUICK:</b> shorter game. First token home wins.</p></div></div>
<div id="toast" class="toast" hidden></div>
<script>var G_SLOTS=[128, 224, 317, 410, 504];</script>
<script src="assets/js/security.js?v=<?=$v?>"></script>
<script src="assets/js/sound.js?v=<?=$v?>"></script>
<script src="assets/js/setup.js?v=<?=$v?>"></script>
<script src="assets/js/pwa.js?v=<?=$v?>"></script>
</body>
</html>