<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Symptom assistant</title>
<link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&amp;display=swap" rel="stylesheet">
<style>
  :root { --ink:#12302B; --accent:#0F766E; --line:#C9DAD6; --bg:#F3F7F6; }
  * { box-sizing:border-box; }
  body { margin:0; background:var(--bg); color:var(--ink); font-family:'Atkinson Hyperlegible',sans-serif; }
  .wrap { max-width:560px; height:100vh; margin:0 auto; display:flex; flex-direction:column; background:#fff; }
  header { padding:14px 20px; border-bottom:1px solid var(--line); font-size:20px; font-weight:700; }
  .notice { background:#FFF4E5; color:#6B3A00; padding:10px 20px; font-size:13px; }
  #log { flex:1; overflow-y:auto; padding:16px 20px; display:flex; flex-direction:column; gap:10px; }
  .msg { max-width:82%; padding:12px; border-radius:12px; font-size:16px; line-height:1.4; white-space:pre-wrap; }
  .bot { align-self:flex-start; background:#E3F2EF; }
  .me { align-self:flex-end; background:var(--accent); color:#fff; }
  #result { margin:0 20px 8px; padding:12px; border-left:6px solid var(--accent); background:#F3F7F6; display:none; }
  #result.red { border-left-color:#B42318; }
  #chips { padding:0 20px 8px; display:flex; flex-wrap:wrap; gap:8px; }
  #chips button { min-height:44px; border:2px solid var(--accent); border-radius:999px; background:#fff; color:#0A4F4A; font:700 15px inherit; font-family:inherit; padding:0 14px; }
  form { display:flex; gap:8px; padding:12px 20px 20px; border-top:1px solid var(--line); }
  input { flex:1; min-height:48px; border:1px solid #7A9A94; border-radius:10px; padding:0 12px; font:16px inherit; font-family:inherit; }
  .send, #restart { min-height:48px; border:0; border-radius:10px; background:var(--accent); color:#fff; font:700 16px inherit; font-family:inherit; padding:0 18px; }
  button:disabled, input:disabled { opacity:.5; }
  #restart { display:none; margin:0 20px 16px; }
</style>
</head>
<body>
<div class="wrap">
  <header>Symptom assistant</header>
  <div class="notice">This gives a suggestion only. Staff confirm every result. Please do not type your name or phone number. For severe symptoms, tell the staff right away.</div>
  <div id="log" aria-live="polite"></div>
  <div id="result" role="status"></div>
  <div id="chips"></div>
  <button id="restart" type="button">Start over</button>
  <form id="form" autocomplete="off">
    <input id="input" aria-label="Type your answer" placeholder="Type here">
    <button class="send" type="submit">Send</button>
  </form>
</div>
<script>
const log = document.getElementById('log'), form = document.getElementById('form'),
      input = document.getElementById('input'), chips = document.getElementById('chips'),
      result = document.getElementById('result'), restart = document.getElementById('restart');
const sendUrl = location.pathname.replace(/\/+$/, '') + '/send';
const QUICK = ['Fever', 'Cough', 'Vomiting', 'Stomach pain', 'Child', 'Adult', 'Elderly'];
const NEXT = {
  red_flag: ['Please tell the staff right now.', true],
  urgent: ['A nurse or doctor will confirm this and call you as soon as possible.', false],
  non_urgent: ['A nurse or doctor will confirm this. Please wait for your number.', false],
  needs_staff_review: ['Please see a nurse. A staff member will check your symptoms in person.', false]
};
let state = {}, finished = false;

function add(who, text) {
  const d = document.createElement('div');
  d.className = 'msg ' + who;
  d.textContent = text;
  log.appendChild(d);
  log.scrollTop = log.scrollHeight;
}
function busy(on) {
  input.disabled = on || finished;
  form.querySelector('.send').disabled = on || finished;
  chips.querySelectorAll('button').forEach(b => b.disabled = on || finished);
}
function finish(d) {
  finished = true;
  const n = NEXT[d.suggestion] || NEXT.needs_staff_review;
  result.textContent = n[0];
  result.className = n[1] ? 'red' : '';
  result.style.display = 'block';
  chips.innerHTML = '';
  restart.style.display = 'block';
  busy(false);
}
async function turn(message) {
  busy(true);
  try {
    const r = await fetch(sendUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ state: state, message: message })
    });
    if (!r.ok) throw new Error('bad response');
    const d = await r.json();
    add('bot', d.reply);
    if (d.done) { finish(d); } else { state = d.state; busy(false); input.focus(); }
  } catch (e) {
    add('bot', 'Sorry, the assistant is not available. Please see a nurse.');
    finish({ suggestion: 'needs_staff_review' });
  }
}
function start() {
  state = {}; finished = false; log.innerHTML = '';
  result.style.display = 'none'; restart.style.display = 'none';
  chips.innerHTML = '';
  QUICK.forEach(q => {
    const b = document.createElement('button');
    b.type = 'button'; b.textContent = q;
    b.onclick = () => { add('me', q); turn(q); };
    chips.appendChild(b);
  });
  turn('');
}
form.addEventListener('submit', e => {
  e.preventDefault();
  const text = input.value.trim();
  if (!text || finished) return;
  add('me', text);
  input.value = '';
  turn(text);
});
restart.onclick = start;
start();
</script>
</body>
</html>
