@extends('storefront::public.layout')

@section('title', 'AI Skin Analysis')

@push('styles')
<style>
.sa{max-width:1100px;margin:24px auto 60px;padding:0 16px;color:#2a1f26}
.sa h1{font-size:30px;margin:0 0 6px}.sa .lead{color:#7d6f78;margin-bottom:18px}
.sa-card{background:#fff;border:1px solid #f0dfe8;border-radius:18px;padding:20px;box-shadow:0 8px 30px #e91e8c0f}
.sa-btn{display:inline-flex;align-items:center;gap:8px;height:50px;padding:0 24px;border-radius:14px;border:0;background:#e91e8c;color:#fff;font-weight:800;font-size:16px;cursor:pointer}
.sa-btn.alt{background:#fde7f3;color:#c2127a}.sa-btn:disabled{opacity:.45;cursor:not-allowed}
.sa-steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin:16px 0}
.sa-steps div{background:#fff7fb;border-radius:14px;padding:14px}.sa-steps b{color:#c2127a}
.sa-cam{position:relative;max-width:520px;margin:0 auto;border-radius:20px;overflow:hidden;background:#000;aspect-ratio:3/4}
.sa-cam video{width:100%;height:100%;object-fit:cover;transform:scaleX(-1)}
.sa-cam.back video{transform:none}
.sa-oval{position:absolute;inset:0;pointer-events:none}
.sa-chip{position:absolute;left:50%;top:12px;transform:translateX(-50%);padding:8px 16px;border-radius:999px;font-weight:800;font-size:14px;background:#000a;color:#fff;white-space:nowrap}
.sa-chip.ok{background:#1f9d63}.sa-chip.bad{background:#d32f2f}
.sa-row{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:14px}
.sa-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.1fr);gap:20px}
@media(max-width:800px){.sa-grid{grid-template-columns:1fr}.sa h1{font-size:24px}}
.sa-img{position:relative;border-radius:16px;overflow:hidden;background:#f6f1f4}.sa-img canvas{width:100%;display:block}
.sa-layers{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}.sa-layers label{background:#fff7fb;border:1px solid #f3c6dd;border-radius:999px;padding:6px 12px;font-size:13px;font-weight:700;cursor:pointer;user-select:none}
.sa-score{display:flex;align-items:center;gap:18px;margin-bottom:14px}
.sa-ring{--v:0;width:110px;height:110px;border-radius:50%;background:conic-gradient(#e91e8c calc(var(--v)*1%),#f6e6ee 0);display:grid;place-items:center;flex:none}
.sa-ring span{width:84px;height:84px;border-radius:50%;background:#fff;display:grid;place-items:center;font-size:30px;font-weight:900;color:#c2127a}
.sa-pills{display:flex;gap:8px;flex-wrap:wrap}.sa-pill{background:#fde7f3;color:#8a0f55;border-radius:999px;padding:6px 12px;font-weight:700;font-size:13px;display:inline-flex;align-items:center;gap:6px}
.sa-sw{width:16px;height:16px;border-radius:50%;border:1px solid #0002;display:inline-block}
.sa-bar{margin:10px 0}.sa-bar .t{display:flex;justify-content:space-between;font-weight:700;font-size:14px}.sa-bar .t small{color:#7d6f78;font-weight:500}
.sa-bar .b{height:10px;border-radius:6px;background:#f6e6ee;overflow:hidden;margin-top:4px}.sa-bar .b i{display:block;height:100%;border-radius:6px}
.lv-Low{background:#1f9d63}.lv-Moderate{background:#e08a00}.lv-High{background:#d32f2f}
.sa h3{margin:26px 0 10px;font-size:20px}
.sa-tip{border:1px solid #f0dfe8;border-radius:12px;margin:8px 0;background:#fff}.sa-tip summary{padding:12px 14px;font-weight:800;cursor:pointer}.sa-tip ul{margin:0;padding:0 18px 12px 34px}.sa-tip li{margin:4px 0}
.sa-routine{display:grid;grid-template-columns:1fr 1fr;gap:14px}@media(max-width:700px){.sa-routine{grid-template-columns:1fr}}
.sa-routine ol{margin:6px 0 0;padding-left:20px}.sa-routine li{margin:6px 0}
.sa-prods{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
.sa-p{border:1px solid #f0dfe8;border-radius:14px;overflow:hidden;background:#fff;display:flex;flex-direction:column}
.sa-p img{width:100%;aspect-ratio:1;object-fit:cover;background:#fde7f3}.sa-p .n{padding:8px 10px 0;font-weight:700;font-size:13.5px;min-height:46px}.sa-p .pr{padding:2px 10px;color:#c2127a;font-weight:800}.sa-p .pr s{color:#999;font-weight:500;margin-left:6px;font-size:12px}
.sa-p .a{padding:8px 10px 12px;display:flex;gap:6px}.sa-p .a a,.sa-p .a button{flex:1;text-align:center;height:38px;line-height:38px;border:0;border-radius:10px;background:#e91e8c;color:#fff;font-weight:700;font-size:13px;cursor:pointer;text-decoration:none}.sa-p .a a.v{background:#fde7f3;color:#c2127a}
.sa-note{font-size:12.5px;color:#7d6f78;margin-top:16px}
.sa-warn{background:#fff4e5;border:1px solid #ffd59a;border-radius:12px;padding:10px 14px;margin:10px 0;font-size:14px}
.sa-load{text-align:center;padding:40px 10px}.sa-spin{width:46px;height:46px;border:5px solid #f6d3e5;border-top-color:#e91e8c;border-radius:50%;margin:0 auto 14px;animation:sp 1s linear infinite}@keyframes sp{to{transform:rotate(360deg)}}
.sa-ai{background:#f7f3ff;border:1px solid #d9ccff;border-radius:14px;padding:14px;margin-top:14px;white-space:pre-wrap}
</style>
@endpush

@section('content')
<div class="sa" id="sa" data-recommend="{{ route('skin_analysis.recommend') }}" data-save="{{ route('skin_analysis.save') }}" data-ai="{{ $aiAvailable ? route('skin_analysis.ai_review') : '' }}" data-cart="{{ url('cart/items') }}" data-logged="{{ auth()->check() ? 1 : 0 }}" data-history="{{ route('account.skin_analysis.index') }}" data-login="{{ route('login') }}" data-csrf="{{ csrf_token() }}">
    <h1>AI Skin Analysis</h1>
    <p class="lead">Take a selfie and get a skin report, personalised tips and products from our store that suit your skin.</p>

    <div class="sa-card" id="s-intro">
        <div class="sa-steps">
            <div><b>1. Prep</b><br>Remove makeup, tie hair back, face a window or soft daylight.</div>
            <div><b>2. Capture</b><br>Take a selfie with the camera, or upload a clear front-facing photo.</div>
            <div><b>3. Analyse</b><br>Face-mesh AI maps your face, then 7 skin engines measure your skin.</div>
            <div><b>4. Shop</b><br>Get a routine and products picked from our in-stock catalogue.</div>
        </div>
        <div class="sa-row" style="justify-content:flex-start">
            <button class="sa-btn" id="b-cam">&#128247; Start camera</button>
            <button class="sa-btn alt" id="b-up">&#128444; Upload a photo</button>
            <input type="file" id="f-up" accept="image/*" capture="user" style="display:none">
        </div>
        <p class="sa-note">&#128274; Your photo is analysed on your device and is not uploaded or stored. {{ $disclaimer }}</p>
        <div id="err" class="sa-warn" style="display:none"></div>
    </div>

    <div class="sa-card" id="s-cam" style="display:none">
        <div class="sa-cam" id="camBox"><video id="v" playsinline muted autoplay></video>
            <svg class="sa-oval" viewBox="0 0 300 400" preserveAspectRatio="none"><defs><mask id="hole"><rect width="300" height="400" fill="#fff"/><ellipse cx="150" cy="195" rx="95" ry="130" fill="#000"/></mask></defs><rect width="300" height="400" fill="#0007" mask="url(#hole)"/><ellipse id="ov" cx="150" cy="195" rx="95" ry="130" fill="none" stroke="#fff" stroke-width="3" stroke-dasharray="8 6"/></svg>
            <div class="sa-chip" id="chip">Starting camera...</div></div>
        <div class="sa-row"><button class="sa-btn" id="b-snap" disabled>&#9679; Capture</button><button class="sa-btn alt" id="b-flip">&#8644; Switch camera</button><button class="sa-btn alt" id="b-stop">Cancel</button></div>
    </div>

    <div class="sa-card sa-load" id="s-load" style="display:none"><div class="sa-spin"></div><b id="loadTxt">Loading AI model...</b></div>

    <div id="s-res" style="display:none">
        <div class="sa-grid">
            <div class="sa-card">
                <div class="sa-img"><canvas id="cv"></canvas></div>
                <div class="sa-layers"><label><input type="checkbox" data-layer="acne" checked> Blemishes</label><label><input type="checkbox" data-layer="spots" checked> Dark spots</label><label><input type="checkbox" data-layer="face" checked> Face map</label></div>
                <div id="qual"></div>
                <div class="sa-row" style="justify-content:flex-start"><button class="sa-btn alt" id="b-again">&#8635; Retake</button><button class="sa-btn alt" id="b-save" style="display:none">Save result</button></div>
                <div id="aiBox" style="display:none">
                    <label style="display:flex;gap:8px;margin-top:12px;font-size:13px"><input type="checkbox" id="aiok"> I agree to send this photo to an AI service (Anthropic) for a second opinion. It is not stored.</label>
                    <button class="sa-btn alt" id="b-ai" style="margin-top:8px" disabled>&#10024; AI second opinion</button>
                    <div id="aiOut"></div>
                </div>
            </div>
            <div class="sa-card">
                <div class="sa-score"><div class="sa-ring" id="ring"><span id="ovr">0</span></div><div><b style="font-size:18px">Skin health score</b><div class="sa-pills" id="pills" style="margin-top:8px"></div></div></div>
                <div id="bars"></div>
            </div>
        </div>
        <h3>Personalised tips</h3><div id="tips"></div>
        <h3>Your daily routine</h3><div class="sa-routine" id="routine"></div>
        <h3>Recommended for you</h3><div id="recs"><div class="sa-load"><div class="sa-spin"></div>Finding products...</div></div>
        <p class="sa-note">{{ $disclaimer }}</p>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
@verbatim
import {createLandmarker, analyze, faceBox} from '/skin-ai/engine.js';
const $ = s => document.querySelector(s), root = $('#sa'), D = root.dataset;
let landmarker = null, stream = null, facing = 'user', liveTimer = null, last = null, lastImage = null;
const show = id => ['s-intro', 's-cam', 's-load', 's-res'].forEach(x => $('#' + x).style.display = x === id ? '' : 'none');
const err = m => { const e = $('#err'); e.textContent = m; e.style.display = m ? '' : 'none'; };
const esc = s => String(s).replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));

async function ensureModel(msg) {
  if (landmarker || landmarker === false) return;
  $('#loadTxt').textContent = msg || 'Loading AI face model...';
  try { landmarker = await createLandmarker(); } catch (e) { console.warn('Face model failed', e); landmarker = false; }
}
function detect(src) { try { const r = landmarker && landmarker.detect(src); return r && r.faceLandmarks && r.faceLandmarks[0] ? r.faceLandmarks[0] : null; } catch (e) { return null; } }

/* ------------------------------------------------------------ camera */
async function startCam() {
  err(''); show('s-load'); await ensureModel();
  try {
    stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: facing, width: {ideal: 1280}, height: {ideal: 1280}}, audio: false});
  } catch (e) { show('s-intro'); err('Camera is not available (' + (e.message || e.name) + '). Camera needs HTTPS (or localhost) and permission. You can upload a photo instead.'); return; }
  const v = $('#v'); v.srcObject = stream; await v.play(); $('#camBox').classList.toggle('back', facing !== 'user'); show('s-cam'); live();
}
function stopCam() { clearInterval(liveTimer); if (stream) stream.getTracks().forEach(t => t.stop()); stream = null; }
function live() {
  clearInterval(liveTimer); let ok = 0; const chip = $('#chip'), snap = $('#b-snap');
  liveTimer = setInterval(() => {
    const v = $('#v'); if (!v.videoWidth) return;
    let msg = 'Position your face in the oval', good = false;
    if (landmarker) {
      const lm = detect(v);
      if (!lm) msg = 'No face found - look at the camera';
      else {
        const b = faceBox(lm), small = b.h < .42, big = b.h > .9, off = Math.abs(b.cx - .5) > .12 || Math.abs(b.cy - .5) > .14;
        const c = document.createElement('canvas'); c.width = 48; c.height = 48; const x = c.getContext('2d'); x.drawImage(v, 0, 0, 48, 48); const d = x.getImageData(0, 0, 48, 48).data; let l = 0; for (let i = 0; i < d.length; i += 4) l += d[i] * .3 + d[i + 1] * .59 + d[i + 2] * .11; l /= 2304;
        msg = small ? 'Move closer' : big ? 'Move back a little' : off ? 'Centre your face in the oval' : l < 55 ? 'Too dark - find more light' : l > 215 ? 'Too bright - avoid direct light' : 'Perfect - hold still';
        good = msg.startsWith('Perfect');
      }
    } else { msg = 'Fit your face in the oval, then capture'; good = true; }
    ok = good ? ok + 1 : 0; chip.textContent = msg; chip.className = 'sa-chip ' + (good ? 'ok' : (landmarker ? 'bad' : '')); snap.disabled = false;
    $('#ov').setAttribute('stroke', good ? '#4ade80' : '#fff');
  }, 400);
}
async function capture() {
  const v = $('#v'), c = document.createElement('canvas'); c.width = v.videoWidth; c.height = v.videoHeight;
  const x = c.getContext('2d'); if (facing === 'user') { x.translate(c.width, 0); x.scale(-1, 1); } x.drawImage(v, 0, 0);
  stopCam(); run(c);
}
async function fromFile(file) {
  err(''); const url = URL.createObjectURL(file), im = new Image(); im.src = url; await im.decode().catch(() => {});
  if (!im.naturalWidth) { err('Could not read that image.'); return; }
  const c = document.createElement('canvas'), k = Math.min(1, 1600 / Math.max(im.naturalWidth, im.naturalHeight)); c.width = im.naturalWidth * k; c.height = im.naturalHeight * k; c.getContext('2d').drawImage(im, 0, 0, c.width, c.height); URL.revokeObjectURL(url); run(c);
}

/* ------------------------------------------------------------ analysis */
async function run(canvas) {
  show('s-load'); $('#loadTxt').textContent = 'Detecting your face...'; await ensureModel();
  await new Promise(r => setTimeout(r, 30)); const lm = detect(canvas);
  const steps = ['Mapping 478 facial landmarks...', 'Reading skin tone & undertone...', 'Scanning for blemishes & redness...', 'Measuring pigmentation, pores & lines...', 'Checking oil balance & dark circles...'];
  for (const s of steps) { $('#loadTxt').textContent = s; await new Promise(r => setTimeout(r, 220)); }
  let res; try { res = analyze(canvas, lm); } catch (e) { console.error(e); show('s-intro'); err('Analysis failed: ' + e.message); return; }
  if (!lm && landmarker !== false) { res.quality.issues.unshift('No face detected - result is a rough estimate. Retake with your face centred.'); }
  last = res; const o = document.createElement('canvas'), k = Math.min(1, 640 / Math.max(canvas.width, canvas.height)); o.width = canvas.width * k; o.height = canvas.height * k; o.getContext('2d').drawImage(canvas, 0, 0, o.width, o.height); lastImage = o.toDataURL('image/jpeg', .85);
  render(res); show('s-res'); window.scrollTo({top: root.offsetTop - 20, behavior: 'smooth'});
}

/* ------------------------------------------------------------ results */
function drawOverlay() {
  const r = last, cv = $('#cv'), f = r.outlines.face, xs = f.map(p => p[0]), ys = f.map(p => p[1]);
  const mx = (Math.max(...xs) - Math.min(...xs)) * .25, my = (Math.max(...ys) - Math.min(...ys)) * .22;
  const x0 = Math.max(0, Math.min(...xs) - mx), y0 = Math.max(0, Math.min(...ys) - my), x1 = Math.min(r.W, Math.max(...xs) + mx), y1 = Math.min(r.H, Math.max(...ys) + my * .6);
  cv.width = Math.round(x1 - x0); cv.height = Math.round(y1 - y0); const c = cv.getContext('2d'); c.drawImage(r.canvas, x0, y0, cv.width, cv.height, 0, 0, cv.width, cv.height); c.translate(-x0, -y0);
  const on = n => document.querySelector(`[data-layer=${n}]`).checked;
  if (on('face')) { c.strokeStyle = '#ffffffaa'; c.lineWidth = 2; c.setLineDash([6, 5]); c.beginPath(); f.forEach((p, i) => i ? c.lineTo(p[0], p[1]) : c.moveTo(p[0], p[1])); c.closePath(); c.stroke(); c.setLineDash([]); }
  const ring = (b, col) => { c.strokeStyle = col; c.lineWidth = 2.5; c.beginPath(); c.arc(b.x, b.y, Math.max(8, b.r + 3), 0, 7); c.stroke(); };
  if (on('acne')) r.blobs.acne.forEach(b => ring(b, '#ff2d55'));
  if (on('spots')) r.blobs.spots.forEach(b => ring(b, '#ffb300'));
}
const TIPS = {
  acne: ['Wash twice daily with a gentle foaming cleanser; do not scrub.', 'Use salicylic acid, tea tree or niacinamide on blemish-prone areas.', 'Never pick or squeeze - it causes scars and dark marks.', 'Change pillowcases weekly and clean your phone screen.'],
  redness: ['Use fragrance-free, soothing products (aloe, green tea).', 'Wash with lukewarm, not hot, water.', 'Avoid harsh scrubs and over-exfoliating.', 'Always wear sunscreen - UV worsens redness.'],
  spots: ['Use vitamin C in the morning and sunscreen every day.', 'Niacinamide or kumkumadi serum helps fade marks over 8-12 weeks.', 'Do not pick blemishes; they leave dark marks.', 'Reapply SPF every 3-4 hours outdoors.'],
  tone: ['Gentle exfoliation 1-2x a week improves evenness.', 'Brightening serums (vitamin C, niacinamide) need consistent use.', 'Daily SPF 30+ prevents tone getting more uneven.'],
  darkcircles: ['Sleep 7-8 hours and keep a regular schedule.', 'Use an eye cream with vitamin C or almond oil; gently pat, do not rub.', 'Cold compress for 5 minutes in the morning reduces puffiness.', 'Stay hydrated and limit salty food at night.'],
  lines: ['Add a retinol or collagen night cream (start 2 nights a week).', 'Hyaluronic acid serum plumps fine lines instantly.', 'SPF every day is the best anti-ageing step.', 'Avoid sleeping face-down; try a silk pillowcase.'],
  pores: ['Niacinamide serum or clay mask once a week minimises the look of pores.', 'Double-cleanse at night to remove oil and makeup.', 'Use a toner after cleansing; keep skin moisturised.'],
  oil: ['Use a gel moisturiser - skipping it makes skin oilier.', 'Clay or charcoal mask 1-2x a week; blotting papers during the day.', 'Choose oil-free, non-comedogenic makeup and sunscreen.'],
  dry: ['Use a hydrating serum (hyaluronic acid) on damp skin, then a cream.', 'Take shorter, lukewarm showers; avoid foaming harsh cleansers.', 'Drink water and add honey / shea based moisturisers at night.', 'Exfoliate gently only once a week to remove dull flakes.'],
};
const label = {acne: 'Acne & blemishes', redness: 'Redness & irritation', spots: 'Dark spots', tone: 'Uneven tone', darkcircles: 'Dark circles', lines: 'Fine lines', pores: 'Pores & texture', oil: 'Oil / shine', dry: 'Dryness & dullness'};
function routine(r) {
  const top = r.concerns.filter(c => c.score >= 30).map(c => c.key), has = k => top.includes(k), t = r.skinType;
  const cleanser = t === 'Oily' || has('acne') ? 'Gel / foaming cleanser with tea tree or neem' : t === 'Dry' || has('dry') ? 'Creamy hydrating cleanser' : 'Gentle gel cleanser';
  const amTreat = has('spots') || has('tone') ? 'Vitamin C or niacinamide serum' : has('redness') ? 'Soothing aloe / green tea serum' : has('lines') ? 'Hyaluronic acid serum' : 'Hydrating serum';
  const moist = t === 'Oily' || t === 'Combination' ? 'Lightweight gel moisturiser' : 'Rich moisturiser (shea / honey)';
  const pmTreat = has('lines') || has('pores') ? 'Retinol / collagen night cream (2-3 nights a week to start)' : has('acne') ? 'Blemish treatment (salicylic acid / tea tree) on spots' : 'Night cream or repair serum';
  return {
    am: [cleanser, amTreat, has('darkcircles') ? 'Eye cream (vitamin C / almond)' : null, moist, 'Sunscreen SPF 30-50 (every day, even indoors)'].filter(Boolean),
    pm: ['Remove makeup / sunscreen with a cleansing oil or micellar water', cleanser, pmTreat, has('darkcircles') || has('lines') ? 'Eye cream' : null, moist, (has('pores') || has('oil') ? 'Clay / charcoal mask once a week' : 'Hydrating mask once a week')].filter(Boolean),
  };
}
function render(r) {
  drawOverlay(); document.querySelectorAll('[data-layer]').forEach(i => i.onchange = drawOverlay);
  $('#qual').innerHTML = r.quality.issues.length ? '<div class="sa-warn"><b>For a more accurate result:</b><ul style="margin:6px 0 0 18px">' + r.quality.issues.map(i => '<li>' + esc(i) + '</li>').join('') + '</ul></div>' : '<div class="sa-note" style="color:#1f9d63">&#10003; Good photo quality</div>';
  $('#ring').style.setProperty('--v', r.overall); $('#ovr').textContent = r.overall;
  $('#pills').innerHTML = `<span class="sa-pill">Skin type: ${esc(r.skinType)}</span><span class="sa-pill"><i class="sa-sw" style="background:${r.tone.hex}"></i>${esc(r.tone.tier)} &middot; ${esc(r.tone.undertone)} undertone</span><span class="sa-pill">Shade match: ${esc(r.tone.shade)}</span>`;
  $('#bars').innerHTML = r.concerns.map(c => `<div class="sa-bar"><div class="t"><span>${esc(c.label)}</span><span>${c.level} <small>${c.score}/100</small></span></div><div class="b"><i class="lv-${c.level}" style="width:${Math.max(3, c.score)}%"></i></div><small style="color:#7d6f78">${esc(c.detail)}</small></div>`).join('');
  const act = r.concerns.filter(c => c.score >= 30);
  $('#tips').innerHTML = (act.length ? act : [{key: 'dry', label: 'Keep your skin healthy'}]).slice(0, 5).map((c, i) => `<details class="sa-tip" ${i < 2 ? 'open' : ''}><summary>${esc(label[c.key] || c.label)}${c.score ? ` - ${c.level}` : ''}</summary><ul>${(TIPS[c.key] || TIPS.dry).map(t => '<li>' + esc(t) + '</li>').join('')}</ul></details>`).join('') +
    `<details class="sa-tip"><summary>Healthy-skin habits for everyone</summary><ul><li>Drink 2-3 litres of water a day and eat fruit and vegetables.</li><li>Sleep 7-8 hours; manage stress.</li><li>Wear SPF 30+ daily and reapply outdoors.</li><li>Introduce one new product at a time and patch-test first.</li></ul></details>`;
  const ro = routine(r);
  $('#routine').innerHTML = `<div class="sa-card"><b>&#9728;&#65039; Morning</b><ol>${ro.am.map(x => '<li>' + esc(x) + '</li>').join('')}</ol></div><div class="sa-card"><b>&#127769; Evening</b><ol>${ro.pm.map(x => '<li>' + esc(x) + '</li>').join('')}</ol></div>`;
  $('#b-save').style.display = D.logged === '1' ? '' : 'none';
  if (D.logged !== '1') $('#qual').insertAdjacentHTML('beforeend', `<div class="sa-note"><a href="${D.login}">Log in</a> to save your results and track progress.</div>`);
  $('#aiBox').style.display = D.ai ? '' : 'none';
  recommend(r);
}
async function recommend(r) {
  try {
    const res = await fetch(D.recommend, {method: 'POST', headers: {'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': D.csrf}, body: JSON.stringify({concerns: r.concerns.map(c => ({key: c.key, score: c.score})), shade: r.tone.shade, undertone: r.tone.undertone, skin_type: r.skinType})});
    const d = await res.json(); const card = p => `<div class="sa-p">${p.image ? `<img loading="lazy" src="${p.image}" alt="">` : '<img alt="">'}<div class="n">${esc(p.name)}${p.variant ? `<br><small>Shade: ${esc(p.variant)}</small>` : ''}</div><div class="pr">${p.price}${p.mrp ? `<s>${p.mrp}</s>` : ''}</div><div class="a">${p.simple ? `<button data-add="${p.id}">Add to cart</button>` : ''}<a class="${p.simple ? 'v' : ''}" href="${p.url}">${p.simple ? 'View' : 'Choose option'}</a></div></div>`;
    let h = '';
    if (d.foundation && d.foundation.length) h += `<h4>Foundation in your shade: ${esc(r.tone.shade)} (${esc(r.tone.undertone)} undertone)</h4><div class="sa-prods">${d.foundation.map(card).join('')}</div>`;
    d.groups.forEach(g => h += `<h4 style="margin-top:20px">For ${esc(g.label)}</h4><div class="sa-prods">${g.products.map(card).join('')}</div>`);
    if (d.protect && d.protect.length) h += `<h4 style="margin-top:20px">Daily sun protection</h4><div class="sa-prods">${d.protect.map(card).join('')}</div>`;
    $('#recs').innerHTML = h || '<p>Your skin looks great! Keep up your routine and sunscreen.</p>';
    $('#recs').querySelectorAll('[data-add]').forEach(b => b.onclick = async () => {
      b.disabled = true; const x = await fetch(D.cart, {method: 'POST', headers: {'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': D.csrf}, body: JSON.stringify({product_id: +b.dataset.add, qty: 1})});
      b.textContent = x.ok ? 'Added ✓' : 'Try again'; b.disabled = false; if (x.ok) setTimeout(() => location.reload === 0, 0);
    });
  } catch (e) { $('#recs').innerHTML = '<p>Could not load product suggestions.</p>'; }
}

/* ------------------------------------------------------------ events */
$('#b-cam').onclick = startCam; $('#b-up').onclick = () => $('#f-up').click(); $('#f-up').onchange = e => e.target.files[0] && fromFile(e.target.files[0]);
$('#b-snap').onclick = capture; $('#b-stop').onclick = () => { stopCam(); show('s-intro'); };
$('#b-flip').onclick = async () => { facing = facing === 'user' ? 'environment' : 'user'; stopCam(); startCam(); };
$('#b-again').onclick = () => { show('s-intro'); window.scrollTo({top: 0, behavior: 'smooth'}); };
$('#b-save').onclick = async () => {
  const b = $('#b-save'); b.disabled = true;
  const x = await fetch(D.save, {method: 'POST', headers: {'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': D.csrf}, body: JSON.stringify({overall: last.overall, skin_type: last.skinType, shade: last.tone.shade, undertone: last.tone.undertone, concerns: last.concerns.map(c => ({key: c.key, score: c.score}))})});
  b.innerHTML = x.ok ? 'Saved &#10003; <a href="' + D.history + '" style="color:inherit;text-decoration:underline">View history</a>' : 'Could not save';
};
$('#aiok').onchange = e => $('#b-ai').disabled = !e.target.checked;
$('#b-ai').onclick = async () => {
  const b = $('#b-ai'); b.disabled = true; b.textContent = 'Thinking...';
  try { const x = await fetch(D.ai, {method: 'POST', headers: {'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': D.csrf}, body: JSON.stringify({image: lastImage, metrics: last.metrics})}); const d = await x.json(); $('#aiOut').innerHTML = '<div class="sa-ai">' + esc(d.text || d.message || 'No answer') + '</div>'; }
  catch (e) { $('#aiOut').innerHTML = '<div class="sa-warn">AI review failed.</div>'; }
  b.textContent = '✨ AI second opinion'; b.disabled = false;
};
window.addEventListener('pagehide', stopCam);
window.__skinAnalysis = {run, fromFile};
@endverbatim
</script>
@endpush
