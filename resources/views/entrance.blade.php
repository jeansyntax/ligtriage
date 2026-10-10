<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Scan to join the queue | LIGTriage</title>
<link rel="icon" href="{{ asset('images/favicon.png') }}">
<link rel="stylesheet" href="{{ asset('css/ligtriage.css') }}">
<style>
body{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(0,1fr);min-height:100vh;background:#fff}
.intro{background:var(--hero-grad);color:#fff;padding:6vh 5vw;display:flex;flex-direction:column;justify-content:center;gap:3.2vh}
.intro .brandrow{display:flex;align-items:center;gap:1rem}
.intro .logo-chip img{width:clamp(56px,6vw,84px);height:clamp(56px,6vw,84px)}
.intro .brandrow b{font-size:clamp(1.4rem,2.4vw,2.2rem)}
.intro .brandrow small{display:block;opacity:.9;font-size:clamp(.8rem,1.1vw,1rem)}
.intro h1{font-size:clamp(2.2rem,4.8vw,4.2rem);line-height:1.05;letter-spacing:-.02em;font-weight:800}
.intro .tl{font-size:clamp(1.1rem,1.8vw,1.6rem);margin:0;color:#fff7d6}
ol{list-style:none;margin:0;padding:0;display:grid;gap:2vh;counter-reset:s}
li{counter-increment:s;display:flex;gap:1.1rem;align-items:center;font-size:clamp(1.1rem,1.9vw,1.7rem)}
li::before{content:counter(s);flex:none;width:2.1em;height:2.1em;border-radius:50%;background:var(--amber);color:#3a2a00;display:grid;place-items:center;font-weight:800}
.note{opacity:.85;font-size:clamp(.9rem,1.2vw,1.1rem);margin:0}
.scan{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2.5vh;padding:5vh 4vw;background:#0c1352}
.tile{position:relative;background:#fff;padding:clamp(14px,2vw,28px);border-radius:22px;width:min(100%,62vh,560px);box-shadow:0 0 0 7px var(--amber),var(--shadow)}
#qr svg{display:block;width:100%;height:auto}
.timer{width:min(100%,62vh,560px)}
.track{height:10px;background:var(--line);border-radius:5px;overflow:hidden}
#bar{height:100%;background:var(--brand-grad);transform-origin:left;transform:scaleX(1);transition:transform .25s linear}
.timer p{margin:1vh 0 0;text-align:center;font-size:clamp(.95rem,1.3vw,1.2rem);color:var(--muted)}
#flash{position:absolute;inset:0;border-radius:22px;background:var(--brand-grad);color:#fff;display:grid;place-content:center;text-align:center;opacity:0;pointer-events:none;transition:opacity .2s}
#flash.on{opacity:1}
#flash small{font-size:clamp(1rem,2vw,1.6rem)}
#flash b{display:block;font-size:clamp(4rem,12vw,9rem);line-height:1}
#status{position:fixed;left:0;right:0;bottom:0;background:var(--amber);color:#3a2a00;text-align:center;padding:.6rem;font-weight:700}
@media (max-width:900px){body{grid-template-columns:1fr}}
</style>
</head>
<body>
<main class="intro">
  <div class="brandrow">
    <span class="logo-chip"><img src="{{ asset('images/ligtas-logo.png') }}" alt="LIGTAS logo"></span>
    <span><b>LIGTriage</b><small>Companion system of LIGTAS</small></span>
  </div>
  <h1>Scan to get your queue number</h1>
  <p class="tl">I-scan ang QR code para makakuha ng numero.</p>
  <ol>
    <li>Scan the code with your phone camera</li>
    <li>Answer the short symptom questions</li>
    <li>Watch your phone and wait for your number</li>
  </ol>
  <p class="note">The code refreshes by itself. If a scan says expired, scan the code shown now.</p>
</main>

<section class="scan">
  <div class="tile">
    <div id="qr" aria-label="Queue QR code"></div>
    <div id="flash" role="status" aria-live="polite"><div><small>Ticket issued</small><b id="flashNum"></b></div></div>
  </div>
  <div class="timer">
    <div class="track"><div id="bar"></div></div>
    <p>New code in <span id="secs">-</span> s</p>
  </div>
</section>

<div id="status" hidden>Connection lost. Reconnecting...</div>

<script>
const url = @json(route('entrance.current'));
const qrBox = document.getElementById('qr');
const bar = document.getElementById('bar');
const secs = document.getElementById('secs');
const flash = document.getElementById('flash');
const flashNum = document.getElementById('flashNum');
const status = document.getElementById('status');
let currentId = null, lastIssued = null, expiresAt = 0, lifetime = 115;

function showFlash(n) {
  flashNum.textContent = n;
  flash.classList.add('on');
  setTimeout(() => flash.classList.remove('on'), 4000);
}

async function poll() {
  try {
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' });
    if (res.status === 401 || res.status === 419) { location.reload(); return; }
    const d = await res.json();
    if (d.id !== currentId) { qrBox.innerHTML = d.svg; currentId = d.id; }
    lifetime = d.lifetime;
    expiresAt = performance.now() + d.expires_in * 1000;
    status.hidden = true;
    if (!d.issued) lastIssued = null;
    else if (d.issued !== lastIssued) { lastIssued = d.issued; showFlash(d.issued); }
  } catch (e) {
    status.hidden = false;
  }
}

setInterval(() => {
  const left = Math.max(0, (expiresAt - performance.now()) / 1000);
  bar.style.transform = 'scaleX(' + (left / lifetime) + ')';
  secs.textContent = Math.ceil(left);
}, 250);

poll();
setInterval(poll, 2000);
</script>
</body>
</html>
