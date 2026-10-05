<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Juanito's Journey to Exactas: Platformer Edition</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:        #0a0e0c;
    --panel:     #10171388;
    --panel-solid:#0f1613;
    --line:      #1f2e27;
    --green:     #4ade80;
    --green-dim: #2f7a52;
    --amber:     #f5c451;
    --red:       #f4685e;
    --text:      #cfe8d9;
    --text-dim:  #7fa08d;
    --mono: 'IBM Plex Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
    --pixel: 'Press Start 2P', var(--mono);
  }

  * { box-sizing: border-box; }

  html, body { margin: 0; min-height: 100vh; background: var(--bg); color: var(--text); font-family: var(--mono); }
  body { display: flex; justify-content: center; align-items: flex-start; padding: 32px 16px 64px; position: relative; overflow-x: hidden; }

  body::before { content: ""; position: fixed; inset: 0; pointer-events: none; z-index: 50; background: repeating-linear-gradient(to bottom, rgba(0,0,0,0.0) 0px, rgba(0,0,0,0.0) 2px, rgba(0,0,0,0.18) 3px, rgba(0,0,0,0.0) 4px); mix-blend-mode: multiply; }
  body::after { content: ""; position: fixed; inset: 0; pointer-events: none; z-index: 51; background: radial-gradient(ellipse at center, rgba(0,0,0,0) 55%, rgba(0,0,0,0.55) 100%); }

  .terminal { width: 100%; max-width: 840px; background: var(--panel-solid); border: 1px solid var(--line); border-radius: 10px; box-shadow: 0 0 0 1px #000, 0 20px 60px -20px rgba(0,0,0,0.8), 0 0 40px -10px rgba(74,222,128,0.08); overflow: hidden; }

  .titlebar { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #0c1310; border-bottom: 1px solid var(--line); }
  .titlebar-left { display: flex; align-items: center; gap: 8px; }
  .dot { width: 10px; height: 10px; border-radius: 50%; background: #2a3530; }
  .path { font-size: 12px; color: var(--text-dim); letter-spacing: 0.02em; }
  
  /* Estilos del Temporizador */
  .timer { font-size: 14px; font-weight: bold; color: var(--amber); display: none; }
  .timer.active { display: block; }
  .timer.danger { color: var(--red); animation: pulse 1s infinite; }
  @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

  .screen { padding: 20px; position: relative; min-height: 500px; display: flex; flex-direction: column; align-items: center; }
  .game-title { font-family: var(--pixel); font-size: 14px; line-height: 1.8; color: var(--green); text-shadow: 0 0 12px rgba(74,222,128,0.35); margin: 0 0 6px; text-align: center; }
  .subtitle { color: var(--text-dim); font-size: 12px; margin: 0 0 20px; letter-spacing: 0.02em; text-align: center; }

  #gameCanvas { width: 100%; max-width: 800px; height: auto; aspect-ratio: 2/1; background: #081009; border: 1px solid var(--line); border-radius: 6px; image-rendering: pixelated; display: none; }
  #gameCanvas.active { display: block; }

  .controls-hint { margin-top: 12px; font-size: 11px; color: var(--text-dim); text-align: center; display: none; }
  .controls-hint.active { display: block; }
  .key { display: inline-block; border: 1px solid var(--green-dim); border-radius: 4px; padding: 2px 6px; color: var(--green); font-size: 10px; margin: 0 2px; }

  #quizOverlay { display: none; width: 100%; max-width: 800px; flex-direction: column; }
  #quizOverlay.active { display: flex; }
  .progress-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; font-size: 12px; color: var(--text-dim); }
  .progress-bar { font-size: 14px; letter-spacing: 2px; color: var(--green-dim); }
  .progress-bar .filled { color: var(--green); }
  .subject-tag { display: inline-block; font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase; padding: 3px 8px; border: 1px solid var(--green-dim); border-radius: 4px; color: var(--green); margin-bottom: 14px; }
  .subject-tag.chem { border-color: #c98bf0; color: #c98bf0; }
  .subject-tag.phys { border-color: #66b8f2; color: #66b8f2; }

  .story { font-size: 14px; line-height: 1.6; color: var(--text); margin: 0 0 16px; white-space: pre-line; }
  .question { font-size: 14px; color: var(--amber); margin: 0 0 14px; line-height: 1.5; }
  .question::before { content: "> "; color: var(--green); }

  .options { display: grid; gap: 10px; margin-bottom: 8px; }
  .opt { text-align: left; background: #0c1512; border: 1px solid var(--line); color: var(--text); font-family: var(--mono); font-size: 13px; padding: 12px 14px; border-radius: 6px; cursor: pointer; transition: all .15s ease; }
  .opt:hover:not(:disabled) { border-color: var(--green-dim); background: #0e1a15; }
  .opt .letter { color: var(--green-dim); margin-right: 10px; }
  .opt:disabled { cursor: default; opacity: 0.6; }
  .opt.correct { border-color: var(--green); background: rgba(74,222,128,0.08); opacity: 1; }
  .opt.wrong { border-color: var(--red); background: rgba(244,104,94,0.08); opacity: 1; }

  .feedback { margin-top: 16px; padding: 14px 16px; border-radius: 6px; font-size: 13px; line-height: 1.6; display: none; }
  .feedback.show { display: block; }
  .feedback.good { border: 1px solid var(--green-dim); background: rgba(74,222,128,0.06); color: var(--text); }
  .feedback.good .label { color: var(--green); font-weight: 600; }
  .feedback.bad { border: 1px solid var(--red); background: rgba(244,104,94,0.07); color: var(--text); }
  .feedback.bad .label { color: var(--red); font-weight: 600; }

  .actions { margin-top: 18px; display: flex; gap: 10px; justify-content: flex-end; }
  .btn { font-family: var(--mono); font-size: 12px; letter-spacing: 0.03em; padding: 10px 18px; border-radius: 6px; cursor: pointer; border: 1px solid var(--green-dim); background: transparent; color: var(--green); transition: all .15s ease; }
  .btn:hover { background: var(--green); color: #06120b; }
  .btn.danger { border-color: var(--red); color: var(--red); }
  .btn.danger:hover { background: var(--red); color: #1a0806; }

  .end-title { font-family: var(--pixel); font-size: 14px; margin: 0 0 16px; line-height: 1.7; text-align: center; }
  .end-title.good { color: var(--green); text-shadow: 0 0 12px rgba(74,222,128,0.4);}
  .end-title.bad  { color: var(--red);   text-shadow: 0 0 12px rgba(244,104,94,0.4);}

  #introScreen { display: flex; flex-direction: column; align-items: center; width: 100%; max-width: 800px; }
  #introScreen.hidden { display: none; }
  pre.ascii { font-family: var(--mono); font-size: 11px; line-height: 1.25; color: var(--green-dim); background: #081009; border: 1px solid var(--line); border-radius: 6px; padding: 14px 12px; overflow-x: auto; margin: 0 0 18px; text-shadow: 0 0 6px rgba(74,222,128,0.15); width: 100%; }
</style>
</head>
<body>

<div class="terminal">
  <div class="titlebar">
    <div class="titlebar-left">
      <div class="dot"></div><div class="dot"></div><div class="dot"></div>
      <div class="path">exactas_platformer.exe</div>
    </div>
    <!-- ELEMENTO DEL TEMPORIZADOR -->
    <div class="timer" id="gameTimer">TIEMPO: 02:00</div>
  </div>
  
  <div class="screen">
    <!-- INTRO -->
    <div id="introScreen">
      <h1 class="game-title">JUANITO'S JOURNEY<br>TO EXACTAS</h1>
      <p class="subtitle">a 5-level platformer adventure — math · physics · chemistry</p>
      <pre class="ascii">        .-""""""-.
       /  o    o  \      7:58 AM.
      |      __     |      Juanito is late.
      |    '  '    |      Exactas is far.
       \    ----   /       The clock is not on his side.
        '-.____.-'        
         /|    |\
        / |    | \
          |    |
         _|    |_
        [________]</pre>
      <p class="story" style="text-align:center; max-width: 600px;">
        Tienes exactamente <strong>2 minutos</strong> para superar los 5 niveles. Resuelve los problemas y avanza. Tu puntuación será el tiempo sobrante.
      </p>
      <div class="actions">
        <button class="btn" id="startBtn">&gt; Start the run</button>
      </div>
    </div>

    <!-- GAME CANVAS -->
    <canvas id="gameCanvas" width="800" height="400"></canvas>
    <div class="controls-hint" id="controlsHint">
      <span class="key">←</span> <span class="key">→</span> Move &nbsp;|&nbsp; <span class="key">↑</span> or <span class="key">Space</span> Jump &nbsp;|&nbsp; Reach the <span style="color:var(--amber)">■</span> door
    </div>

    <!-- QUIZ OVERLAY -->
    <div id="quizOverlay">
      <div class="progress-row">
        <span id="quizLevelText">LEVEL 1 / 5</span>
        <span class="progress-bar" id="quizProgressBar">■ □ □ □ □</span>
      </div>
      <span class="subject-tag" id="quizSubjectTag">Math</span>
      <p class="story" id="quizStory"></p>
      <p class="question" id="quizQuestion"></p>
      <div class="options" id="quizOptions"></div>
      <div class="feedback" id="quizFeedback"></div>
      <div class="actions" id="quizActions"></div>
    </div>

    <!-- END SCREEN -->
    <div id="endScreen" style="display:none; width:100%; max-width:800px; flex-direction:column; align-items:center;">
      <h2 class="end-title" id="endTitle"></h2>
      <pre class="ascii" id="endAscii"></pre>
      <p class="story" id="endStory" style="text-align:center; max-width:600px;"></p>
      <div class="actions">
        <button class="btn" id="endBtn">&gt; Play again</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  // --- GAME DATA (5 Levels) ---
  const LEVELS = [
    {
      subject: "math", subjectLabel: "Math",
      story: "The Courtyard. Jump across the gaps to reach the Main Gate keypad before 8:00 AM.",
      question: "The gate code is the sum of the first three prime numbers. What is it?",
      options: ["8", "9", "10", "12"], correct: 2,
      good: "2 + 3 + 5 = 10. Juanito types 10. The gate opens with a click!",
      bad: "Wrong code. The keypad turns red and locks. Juanito is stuck outside.",
      platforms: [{x: 0, y: 350, w: 250, h: 50}, {x: 300, y: 300, w: 100, h: 20}, {x: 450, y: 250, w: 100, h: 20}, {x: 600, y: 350, w: 200, h: 50}],
      hazards: [], goal: {x: 720, y: 290, w: 40, h: 60}, start: {x: 50, y: 300}
    },
    {
      subject: "physics", subjectLabel: "Physics",
      story: "The Stairwell. The elevator is broken. Climb the platforms quickly!",
      question: "Juanito must run 100 meters at 5 m/s. How many seconds does he need?",
      options: ["20 s", "30 s", "35 s", "50 s"], correct: 0,
      good: "Time = 100 ÷ 5 = 20 seconds. He reaches the top to spare.",
      bad: "He miscalculates, hesitates, and the doors close.",
      platforms: [{x: 0, y: 350, w: 150, h: 50}, {x: 180, y: 290, w: 100, h: 20}, {x: 320, y: 230, w: 100, h: 20}, {x: 180, y: 170, w: 100, h: 20}, {x: 350, y: 110, w: 100, h: 20}, {x: 500, y: 170, w: 100, h: 20}, {x: 650, y: 230, w: 150, h: 20}],
      hazards: [], goal: {x: 720, y: 170, w: 40, h: 60}, start: {x: 40, y: 300}
    },
    {
      subject: "chemistry", subjectLabel: "Chemistry",
      story: "The Chemistry Lab. A clear liquid has spilled, turning litmus paper bright red.",
      question: "A red paper strip means the liquid is...",
      options: ["basic (pH 8–14)", "neutral (pH 7)", "not related to pH", "acidic (pH 0–6)"], correct: 3,
      good: "Red means acidic. Juanito carefully jumps over the puddles.",
      bad: "He walks through the puddle. The acid damages his shoe.",
      platforms: [{x: 0, y: 350, w: 200, h: 50}, {x: 250, y: 350, w: 150, h: 50}, {x: 450, y: 350, w: 150, h: 50}, {x: 650, y: 350, w: 150, h: 50}],
      hazards: [{x: 200, y: 370, w: 50, h: 30}, {x: 400, y: 370, w: 50, h: 30}, {x: 600, y: 370, w: 50, h: 30}],
      goal: {x: 720, y: 290, w: 40, h: 60}, start: {x: 40, y: 300}
    },
    {
      subject: "physics", subjectLabel: "Physics",
      story: "The Hallway. A heavy shelf is blocking the path.",
      question: "The shelf is 10 kg. To move it at 2 m/s², how much force is needed?",
      options: ["12 N", "5 N", "20 N", "200 N"], correct: 2,
      good: "F = 10 × 2 = 20 N. He pushes with the right force.",
      bad: "He pushes awkwardly, and the shelf tips over.",
      platforms: [{x: 0, y: 350, w: 250, h: 50}, {x: 300, y: 280, w: 200, h: 70}, {x: 550, y: 350, w: 250, h: 50}],
      hazards: [], goal: {x: 720, y: 290, w: 40, h: 60}, start: {x: 40, y: 300}
    },
    {
      subject: "math", subjectLabel: "Math",
      story: "The Exactas Building. The final climb.",
      question: "The door code is the larger root of: x² − 5x + 6 = 0",
      options: ["x = 3", "x = 2", "x = 1", "x = 6"], correct: 0,
      good: "Factoring: (x − 2)(x − 3) = 0. The larger root is 3.",
      bad: "Wrong number. The doors stay shut.",
      platforms: [{x: 0, y: 350, w: 150, h: 50}, {x: 200, y: 290, w: 80, h: 20}, {x: 330, y: 230, w: 80, h: 20}, {x: 460, y: 170, w: 80, h: 20}, {x: 590, y: 110, w: 80, h: 20}, {x: 700, y: 110, w: 100, h: 20}],
      hazards: [], goal: {x: 740, y: 50, w: 40, h: 60}, start: {x: 40, y: 300}
    }
  ];

  // --- DOM ELEMENTS ---
  const introScreen = document.getElementById('introScreen');
  const gameCanvas = document.getElementById('gameCanvas');
  const controlsHint = document.getElementById('controlsHint');
  const quizOverlay = document.getElementById('quizOverlay');
  const endScreen = document.getElementById('endScreen');
  const timerDisplay = document.getElementById('gameTimer');
  const ctx = gameCanvas.getContext('2d');

  // --- GAME STATE ---
  let currentLevel = 0;
  let gameState = 'MENU';
  let animationId = null;
  
  // Timer variables
  let timeLeft = 120; 
  let timerInterval = null;

  const player = { x: 50, y: 300, w: 24, h: 36, vx: 0, vy: 0, speed: 4, jumpStrength: -9, grounded: false };
  const gravity = 0.5;
  const friction = 0.85;
  const keys = { left: false, right: false, up: false };

  // --- TIMER LOGIC ---
  function formatTime(seconds) {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `0${m}:${s < 10 ? '0' : ''}${s}`;
  }

  function startGlobalTimer() {
    clearInterval(timerInterval);
    timerDisplay.classList.add('active');
    timerDisplay.classList.remove('danger');
    timerDisplay.textContent = `TIEMPO: ${formatTime(timeLeft)}`;

    timerInterval = setInterval(() => {
      // Solo contar si estamos jugando o en un quiz
      if (gameState !== 'PLAYING' && gameState !== 'QUIZ') return;
      
      timeLeft--;
      timerDisplay.textContent = `TIEMPO: ${formatTime(timeLeft)}`;
      
      if (timeLeft <= 30) timerDisplay.classList.add('danger');
      
      if (timeLeft <= 0) {
        clearInterval(timerInterval);
        handleTimeout();
      }
    }, 1000);
  }

  // --- INITIALIZATION ---
  document.getElementById('startBtn').addEventListener('click', () => {
    currentLevel = 0;
    timeLeft = 120; // Resetear a 2 minutos
    startGlobalTimer();
    startLevel();
  });

  document.getElementById('endBtn').addEventListener('click', () => {
    endScreen.style.display = 'none';
    introScreen.classList.remove('hidden');
    timerDisplay.classList.remove('active');
    gameState = 'MENU';
  });

  // --- INPUT HANDLING ---
  window.addEventListener('keydown', (e) => {
    if (gameState !== 'PLAYING') return;
    if (e.key === 'ArrowLeft' || e.key === 'a') keys.left = true;
    if (e.key === 'ArrowRight' || e.key === 'd') keys.right = true;
    if (e.key === 'ArrowUp' || e.key === ' ' || e.key === 'w') {
      if (!keys.up && player.grounded) { player.vy = player.jumpStrength; player.grounded = false; }
      keys.up = true;
    }
  });

  window.addEventListener('keyup', (e) => {
    if (e.key === 'ArrowLeft' || e.key === 'a') keys.left = false;
    if (e.key === 'ArrowRight' || e.key === 'd') keys.right = false;
    if (e.key === 'ArrowUp' || e.key === ' ' || e.key === 'w') keys.up = false;
  });

  // --- GAME LOOP ---
  function startLevel() {
    introScreen.classList.add('hidden');
    quizOverlay.classList.remove('active');
    gameCanvas.classList.add('active');
    controlsHint.classList.add('active');
    
    const lvl = LEVELS[currentLevel];
    player.x = lvl.start.x; player.y = lvl.start.y;
    player.vx = 0; player.vy = 0; player.grounded = false;
    
    gameState = 'PLAYING';
    if (animationId) cancelAnimationFrame(animationId);
    gameLoop();
  }

  function gameLoop() {
    if (gameState === 'PLAYING') {
      update();
      draw();
      animationId = requestAnimationFrame(gameLoop);
    }
  }

  function update() {
    if (keys.left) player.vx -= 1;
    if (keys.right) player.vx += 1;
    player.vx *= friction;
    player.x += player.vx;

    if (player.x < 0) { player.x = 0; player.vx = 0; }
    if (player.x + player.w > 800) { player.x = 800 - player.w; player.vx = 0; }

    player.vy += gravity;
    player.y += player.vy;
    player.grounded = false;

    const lvl = LEVELS[currentLevel];

    for (let p of lvl.platforms) {
      if (rectIntersect(player.x, player.y, player.w, player.h, p.x, p.y, p.w, p.h)) {
        const overlapX = (player.w + p.w) / 2 - Math.abs((player.x + player.w/2) - (p.x + p.w/2));
        const overlapY = (player.h + p.h) / 2 - Math.abs((player.y + player.h/2) - (p.y + p.h/2));
        if (overlapX < overlapY) {
          if (player.x < p.x) player.x = p.x - player.w;
          else player.x = p.x + p.w;
          player.vx = 0;
        } else {
          if (player.y < p.y) { player.y = p.y - player.h; player.grounded = true; player.vy = 0; } 
          else { player.y = p.y + p.h; player.vy = 0; }
        }
      }
    }

    for (let h of lvl.hazards) {
      if (rectIntersect(player.x, player.y, player.w, player.h, h.x, h.y, h.w, h.h)) {
        renderGameOver(lvl, "cayó en el ácido"); return;
      }
    }

    if (player.y > 450) {
      player.x = lvl.start.x; player.y = lvl.start.y; player.vx = 0; player.vy = 0; return;
    }

    if (rectIntersect(player.x, player.y, player.w, player.h, lvl.goal.x, lvl.goal.y, lvl.goal.w, lvl.goal.h)) {
      triggerQuiz();
    }
  }

  function rectIntersect(x1, y1, w1, h1, x2, y2, w2, h2) { return x2 < x1 + w1 && x2 + w2 > x1 && y2 < y1 + h1 && y2 + h2 > y1; }

  function draw() {
    const lvl = LEVELS[currentLevel];
    ctx.fillStyle = '#081009'; ctx.fillRect(0, 0, 800, 400);

    ctx.fillStyle = '#1f2e27'; ctx.strokeStyle = '#2f7a52'; ctx.lineWidth = 2;
    for (let p of lvl.platforms) {
      ctx.fillRect(p.x, p.y, p.w, p.h); ctx.strokeRect(p.x, p.y, p.w, p.h);
      ctx.fillStyle = '#0f1613'; ctx.fillRect(p.x + 4, p.y + 4, p.w - 8, p.h - 8); ctx.fillStyle = '#1f2e27';
    }

    ctx.fillStyle = '#f4685e'; ctx.strokeStyle = '#f4685e';
    for (let h of lvl.hazards) {
      ctx.fillRect(h.x, h.y, h.w, h.h);
      ctx.fillStyle = '#081009'; ctx.fillRect(h.x + 5, h.y + 5, 8, 8); ctx.fillRect(h.x + 25, h.y + 10, 6, 6); ctx.fillStyle = '#f4685e';
    }

    ctx.fillStyle = '#f5c451'; ctx.fillRect(lvl.goal.x, lvl.goal.y, lvl.goal.w, lvl.goal.h); ctx.strokeRect(lvl.goal.x, lvl.goal.y, lvl.goal.w, lvl.goal.h);
    ctx.fillStyle = '#081009'; ctx.beginPath(); ctx.arc(lvl.goal.x + 30, lvl.goal.y + 35, 3, 0, Math.PI * 2); ctx.fill();

    ctx.fillStyle = '#4ade80'; ctx.fillRect(player.x, player.y, player.w, player.h);
    ctx.fillStyle = '#081009';
    if (keys.right || (!keys.left && !keys.right)) {
      ctx.fillRect(player.x + 14, player.y + 8, 4, 4); ctx.fillRect(player.x + 6, player.y + 8, 4, 4);
    } else {
      ctx.fillRect(player.x + 4, player.y + 8, 4, 4); ctx.fillRect(player.x + 12, player.y + 8, 4, 4);
    }
  }

  // --- QUIZ LOGIC ---
  function triggerQuiz() {
    gameState = 'QUIZ';
    cancelAnimationFrame(animationId);
    
    const lvl = LEVELS[currentLevel];
    document.getElementById('quizLevelText').textContent = `LEVEL ${currentLevel + 1} / ${LEVELS.length}`;
    
    let bar = ''; for(let i=0; i<LEVELS.length; i++) bar += i <= currentLevel ? '<span class="filled">■</span>' : '□';
    document.getElementById('quizProgressBar').innerHTML = bar;
    
    const tag = document.getElementById('quizSubjectTag');
    tag.textContent = lvl.subjectLabel;
    tag.className = 'subject-tag ' + (lvl.subject === 'chemistry' ? 'chem' : lvl.subject === 'physics' ? 'phys' : '');
    
    document.getElementById('quizStory').textContent = lvl.story;
    document.getElementById('quizQuestion').textContent = lvl.question;
    
    const optionsEl = document.getElementById('quizOptions');
    optionsEl.innerHTML = '';
    const letters = ['A','B','C','D'];
    lvl.options.forEach((optText, idx) => {
      const btn = document.createElement('button'); btn.className = 'opt';
      btn.innerHTML = `<span class="letter">${letters[idx]}</span>${optText}`;
      btn.addEventListener('click', () => handleAnswer(idx));
      optionsEl.appendChild(btn);
    });
    
    document.getElementById('quizFeedback').className = 'feedback';
    document.getElementById('quizFeedback').innerHTML = '';
    document.getElementById('quizActions').innerHTML = '';
    
    gameCanvas.classList.remove('active'); controlsHint.classList.remove('active'); quizOverlay.classList.add('active');
  }

  function handleAnswer(idx) {
    const lvl = LEVELS[currentLevel];
    const optionButtons = document.querySelectorAll('#quizOptions .opt');
    optionButtons.forEach(b => b.disabled = true);

    const isCorrect = idx === lvl.correct;
    optionButtons[idx].classList.add(isCorrect ? 'correct' : 'wrong');
    if (!isCorrect) optionButtons[lvl.correct].classList.add('correct');

    const feedback = document.getElementById('quizFeedback');
    const actions = document.getElementById('quizActions');

    if (isCorrect) {
      feedback.className = 'feedback show good';
      feedback.innerHTML = `<span class="label">Correct.</span> ${lvl.good}`;
      const isLast = currentLevel === LEVELS.length - 1;
      actions.innerHTML = `<button class="btn" id="nextBtn">${isLast ? '> Reach Exactas' : '> Keep going'}</button>`;
      document.getElementById('nextBtn').addEventListener('click', () => {
        if (isLast) renderVictory();
        else { currentLevel++; startLevel(); }
      });
    } else {
      feedback.className = 'feedback show bad';
      feedback.innerHTML = `<span class="label">Wrong.</span> ${lvl.bad}`;
      actions.innerHTML = `<button class="btn danger" id="endBtnQuiz">> See how it ends</button>`;
      document.getElementById('endBtnQuiz').addEventListener('click', () => renderGameOver(lvl, "error académico"));
    }
  }

  function handleTimeout() {
     clearInterval(timerInterval);
     renderGameOver(LEVELS[currentLevel], "timeout");
  }

  function renderGameOver(lvl, reason) {
    clearInterval(timerInterval);
    gameState = 'GAMEOVER';
    quizOverlay.classList.remove('active');
    gameCanvas.classList.remove('active');
    controlsHint.classList.remove('active');
    endScreen.style.display = 'flex';
    
    document.getElementById('endTitle').textContent = 'GAME OVER';
    document.getElementById('endTitle').className = 'end-title bad';
    document.getElementById('endAscii').textContent = `   .-""""""-.\n  /  x    x  \\\n |      __     |      8:00 AM.\n |    '  '    |      Too late.\n  \\    ----   /\n   '-.____.-'`;
    
    let endText = "";
    if (reason === "timeout") {
       endText = "El reloj marcó las 08:00 AM. La puerta se cerró y Juanito se quedó afuera. Te quedaste sin tiempo en el Nivel " + (currentLevel + 1) + ".";
    } else {
       endText = `${lvl.bad}\n\nJuanito's journey ends here. He failed at Level ${currentLevel + 1} (${lvl.subjectLabel}).`;
    }
    
    document.getElementById('endStory').textContent = endText;
    
    // GUARDAR EN MYSQL: Perdió, 0 segundos sobrantes
    saveGameToDatabase(currentLevel + 1, 'game_over', 0);
  }

  function renderVictory() {
    clearInterval(timerInterval);
    gameState = 'VICTORY';
    quizOverlay.classList.remove('active');
    gameCanvas.classList.remove('active');
    controlsHint.classList.remove('active');
    endScreen.style.display = 'flex';
    
    document.getElementById('endTitle').textContent = 'JUANITO MADE IT';
    document.getElementById('endTitle').className = 'end-title good';
    document.getElementById('endAscii').textContent = `   ___________________\n  |     E X A C T A S   |\n  |    ______________   |\n  |   |   welcome    |  |\n  |   |   juanito    |  |\n  |   |______________|  |\n  |___________________|\n        \\o/\n         |      Score: ${timeLeft}s\n        / \\`;
    document.getElementById('endStory').textContent = `The doors close behind him just as the proctor says "Good morning."\n\n¡Lo lograste! Completaste todos los niveles y te sobraron ${timeLeft} segundos.`;
    
    // GUARDAR EN MYSQL: Ganó, enviamos los segundos restantes como puntos
    saveGameToDatabase(LEVELS.length, 'victory', timeLeft);
  }

  // --- GUARDAR EN MYSQL ---
  function saveGameToDatabase(level, status, score) {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) { console.error("No se encontró el token CSRF. Revisa tu archivo .blade.php"); return; }
    
    const csrfToken = csrfMeta.getAttribute('content');

    fetch('/juanito-game/save', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({
        nivel_alcanzado: level,
        estado: status,
        tiempo_restante: score,
        puntuacion_final: (level * 100) + score * 100
      })
    })
    .then(response => response.json())
    .then(data => console.log("Guardado en BD:", data))
    .catch(error => console.error('Error guardando en BD:', error));
  }

})();
</script>

</body>
</html>