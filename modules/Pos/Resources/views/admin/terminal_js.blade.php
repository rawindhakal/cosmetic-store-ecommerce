@verbatim
(function () {
'use strict';
const C = window.POS, U = C.urls, $ = s => document.querySelector(s);
const CSRF = document.querySelector('meta[name=csrf-token]').content;
const fmt = n => (C.currency + ' ' + (Math.round((+n || 0) * 100) / 100).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
let DB = {categories: [], products: []}, byCode = {}, byId = {};
let S = load() || fresh();
let cat = 0, sub = 0, query = '';

function fresh() { return {lines: [], discount: null, customer: null, redeem: 0, note: '', holdId: null}; }
function load() { try { return JSON.parse(localStorage.getItem('pos_cart') || 'null'); } catch (e) { return null; } }
function save() { try { localStorage.setItem('pos_cart', JSON.stringify(S)); } catch (e) {} }

async function api(url, opts = {}) {
  const o = Object.assign({headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF}}, opts);
  if (o.body && typeof o.body !== 'string') { o.headers['Content-Type'] = 'application/json'; o.body = JSON.stringify(o.body); }
  const r = await fetch(url, o); let d = {}; try { d = await r.json(); } catch (e) {}
  if (!r.ok) throw new Error(d.message || (d.errors && Object.values(d.errors)[0][0]) || 'Request failed (' + r.status + ')');
  return d;
}
let tt;
function toast(msg, kind) { const t = $('#toast'); t.textContent = msg; t.className = 'toast ' + (kind || ''); t.style.display = 'block'; clearTimeout(tt); tt = setTimeout(() => t.style.display = 'none', 2600); beep(kind === 'err'); }
function beep(bad) { try { const a = new (window.AudioContext || window.webkitAudioContext)(), o = a.createOscillator(), g = a.createGain(); o.frequency.value = bad ? 180 : 880; g.gain.value = .05; o.connect(g); g.connect(a.destination); o.start(); setTimeout(() => { o.stop(); a.close(); }, bad ? 220 : 90); } catch (e) {} }

/* ---------------------------------------------------------------- catalog */
async function loadCatalog() {
  DB = await api(U.catalog);
  byCode = {}; byId = {};
  DB.products.forEach(p => {
    byId[p.id] = p;
    [p.barcode, p.sku].forEach(c => { if (c) byCode[String(c).toLowerCase()] = {p, v: null}; });
    p.variants.forEach(v => [v.barcode, v.sku].forEach(c => { if (c) byCode[String(c).toLowerCase()] = {p, v}; }));
  });
  renderCats(); renderGrid(); renderCart();
}
function parents() { return DB.categories.filter(c => !c.parent_id); }
function renderCats() {
  $('#cats').innerHTML = '<button class="chip ' + (!cat ? 'on' : '') + '" data-cat="0">All</button>' + parents().map(c => `<button class="chip ${cat == c.id ? 'on' : ''}" data-cat="${c.id}">${esc(c.name)}</button>`).join('');
  const kids = DB.categories.filter(c => c.parent_id == cat);
  $('#subs').innerHTML = cat && kids.length ? '<button class="chip ' + (!sub ? 'on' : '') + '" data-sub="0">All ' + esc((DB.categories.find(c => c.id == cat) || {}).name) + '</button>' + kids.map(c => `<button class="chip ${sub == c.id ? 'on' : ''}" data-sub="${c.id}">${esc(c.name)}</button>`).join('') : '';
}
function stockOf(p) { return p.variants.length ? (p.variants.some(v => v.stock === null) ? null : p.variants.reduce((a, v) => a + v.stock, 0)) : p.stock; }
function renderGrid() {
  const q = query.trim().toLowerCase();
  let list = DB.products.filter(p => {
    if (sub) { if (!p.cats.includes(+sub)) return false; } else if (cat && !p.cats.includes(+cat)) return false;
    if (!q) return true;
    return p.name.toLowerCase().includes(q) || (p.sku || '').toLowerCase().includes(q) || (p.barcode || '').includes(q) || p.variants.some(v => (v.name || '').toLowerCase().includes(q) || (v.barcode || '').includes(q) || (v.sku || '').toLowerCase().includes(q));
  });
  $('#grid').innerHTML = list.slice(0, 200).map(p => {
    const st = stockOf(p), cls = st === null ? '' : (st <= 0 ? 'out' : st <= C.lowStock ? 'low' : '');
    const price = p.variants.length ? Math.min(...p.variants.map(v => v.price)) : p.price;
    return `<div class="pc" role="button" data-pid="${p.id}">
      ${p.image ? `<img loading="lazy" src="${p.image}" alt="">` : '<div class="ph"></div>'}
      ${p.variants.length ? '<span class="bdg">' + p.variants.length + ' options</span>' : (p.mrp > p.price ? '<span class="bdg">SALE</span>' : '')}
      ${st !== null ? `<span class="bdg st ${cls}">${st <= 0 ? 'Out' : st}</span>` : ''}
      <div class="t">${esc(p.name)}</div>
      <div class="pr"><b>${p.variants.length ? 'from ' : ''}${fmt(price).replace(C.currency + ' ', '')}</b>${!p.variants.length && p.mrp > p.price ? '<s>' + p.mrp + '</s>' : ''}</div></div>`;
  }).join('') || '<div class="empty" style="grid-column:1/-1">No products found.</div>';
}

/* ------------------------------------------------------------------- cart */
function addItem(p, v, qty) {
  qty = qty || 1;
  const key = p.id + '-' + (v ? v.id : 0), unit = v ? v.price : p.price, stock = v ? v.stock : p.stock;
  let l = S.lines.find(x => x.key === key);
  const have = l ? l.qty : 0;
  if (stock !== null && have + qty > stock) { toast('Only ' + stock + ' in stock', 'err'); return false; }
  if (l) l.qty += qty;
  else S.lines.push({key, product_id: p.id, variant_id: v ? v.id : null, name: p.name, variant: v ? v.name : null, unit, mrp: v ? v.mrp : p.mrp, qty, disc: 0, stock});
  save(); renderCart(); beep(); const bar = $('#cartBar'); bar.classList.remove('bump'); void bar.offsetWidth; bar.classList.add('bump'); return true;
}
function calc() {
  let gross = 0, ld = 0;
  S.lines.forEach(l => { gross += l.unit * l.qty; ld += Math.min(l.disc, l.unit * l.qty); });
  const after = gross - ld;
  let cd = 0;
  if (S.discount) cd = S.discount.type === 'percent' ? Math.round(after * Math.min(100, S.discount.value)) / 100 : Math.min(after, S.discount.value);
  let pts = 0, pd = 0;
  if (S.customer && C.points.enabled && S.redeem > 0) {
    const bal = S.customer.points || 0, max = Math.max(0, Math.min(bal, Math.floor((after - cd) * C.points.maxPercent / 100 / C.points.value)));
    pts = Math.min(S.redeem, max); if (pts < C.points.min) pts = 0; pd = pts * C.points.value;
  }
  let pay = Math.max(0, after - cd - pd), tax = 0;
  if (C.taxMode === 'inclusive' && C.taxRate > 0) tax = pay * C.taxRate / (100 + C.taxRate);
  else if (C.taxMode === 'exclusive' && C.taxRate > 0) { tax = pay * C.taxRate / 100; pay += tax; }
  let rnd = 0; if (C.round) { const r = Math.round(pay); rnd = r - pay; pay = r; }
  return {gross, ld, cd, pd, pts, tax, rnd, total: Math.round(pay * 100) / 100, qty: S.lines.reduce((a, l) => a + l.qty, 0)};
}
function renderCart() {
  const el = $('#lines');
  el.innerHTML = S.lines.length ? S.lines.map((l, i) => `<div class="ln">
    <div><div class="nm">${esc(l.name)}</div>${l.variant ? '<div class="vr">' + esc(l.variant) + '</div>' : ''}<div class="vr">${fmt(l.unit)}${l.disc ? ' &middot; disc -' + fmt(l.disc) : ''}</div></div>
    <div class="am">${fmt(l.unit * l.qty - l.disc)}</div>
    <div class="ctl"><button class="qb" data-q="-1" data-i="${i}">&minus;</button><button class="qv" data-edit="qty" data-i="${i}">${l.qty}</button><button class="qb" data-q="1" data-i="${i}">+</button>
    <button class="mini" data-edit="disc" data-i="${i}">Disc</button>${C.allowPriceOverride ? `<button class="mini" data-edit="price" data-i="${i}">Price</button>` : ''}<button class="mini rm" data-rm="${i}">&#128465;</button></div></div>`).join('')
    : '<div class="empty"><div style="font-size:48px">&#128717;</div><b>Cart is empty</b>Scan a barcode or tap a product</div>';
  const t = calc();
  $('#tots').innerHTML = `<div><span>Items</span><span>${t.qty}</span></div><div><span>Subtotal</span><span>${fmt(t.gross)}</span></div>` +
    (t.ld ? `<div><span>Item discounts</span><span>-${fmt(t.ld)}</span></div>` : '') + (t.cd ? `<div><span>Discount</span><span>-${fmt(t.cd)}</span></div>` : '') +
    (t.pd ? `<div><span>${esc(C.points.name)} (${t.pts})</span><span>-${fmt(t.pd)}</span></div>` : '') +
    (t.tax ? `<div><span>${esc(C.taxLabel)} ${C.taxMode === 'inclusive' ? '(incl.)' : ''}</span><span>${fmt(t.tax)}</span></div>` : '') +
    (t.rnd ? `<div><span>Rounding</span><span>${t.rnd > 0 ? '+' : ''}${t.rnd.toFixed(2)}</span></div>` : '') + `<div class="gt"><span>Total</span><span>${fmt(t.total)}</span></div>`;
  const pb = $('#payBtn'); pb.disabled = !S.lines.length; pb.textContent = S.lines.length ? 'Pay ' + fmt(t.total) : 'Pay';
  const cb = $('#custBtn');
  cb.innerHTML = S.customer ? `&#128100; ${esc(S.customer.name)}<small>${esc(S.customer.phone || '')}${C.points.enabled ? ' &middot; ' + S.customer.points + ' ' + esc(C.points.name) : ''}</small>` : '&#128100; Walk-in Customer<small>Tap to add customer</small>';
  const cbt = $('#cbTotal'); if (cbt) { cbt.textContent = fmt(t.total); $('#cbItems').textContent = S.lines.length ? t.qty + (t.qty === 1 ? ' item' : ' items') + (S.customer ? ' \u00b7 ' + S.customer.name : '') : 'Cart is empty'; $('#cbPay').disabled = !S.lines.length; }
  const hn = $('#holdN'); try { hn.textContent = hn.dataset.n || 0; } catch (e) {}
}

/* -------------------------------------------------------------- modals */
function modal(html, wide) { $('#md').className = 'md' + (wide ? ' wide' : ''); $('#md').innerHTML = html; $('#ov').classList.add('on'); }
function closeModal() { $('#ov').classList.remove('on'); $('#md').innerHTML = ''; stopCam(); }
const head = t => `<div class="mh"><span>${t}</span><button data-act="close">&#10005;</button></div>`;

function numpad(target, opts) {
  // returns a small numpad component bound to a buffer
  return `<div class="disp" id="npd">0</div><div class="np">${[7, 8, 9, 4, 5, 6, 1, 2, 3, '.', 0, '&#9003;'].map(k => `<button data-k="${k}">${k}</button>`).join('')}<button class="w2" data-k="C">Clear</button><button class="w2" style="background:var(--p);color:#fff" data-k="OK">OK</button></div>`;
}
function askNumber(title, initial, cb, hint) {
  let buf = initial ? String(initial) : '';
  modal(head(title) + `<div class="mb">${hint ? '<p style="margin:0 0 8px;color:var(--mut)">' + hint + '</p>' : ''}${numpad()}</div>`);
  const d = () => $('#npd').textContent = buf || '0'; d();
  $('#md').onclick = e => {
    const k = e.target.closest('[data-k]'); if (!k) return; const v = k.dataset.k;
    if (v === 'C') buf = ''; else if (v === 'OK') { const n = parseFloat(buf || '0'); closeModal(); $('#md').onclick = null; cb(isNaN(n) ? 0 : n); return; }
    else if (v === '⌫' || v === '⌫') buf = buf.slice(0, -1); else if (v === '.') { if (!buf.includes('.')) buf += (buf ? '.' : '0.'); } else buf += v;
    d();
  };
}
function variantPicker(p) {
  modal(head(esc(p.name)) + `<div class="mb"><div class="vgrid">${p.variants.map(v => `<button class="vb ${v.stock !== null && v.stock <= 0 ? 'out' : ''}" data-vid="${v.id}">${esc(v.name)}<small>${fmt(v.price)}${v.stock !== null ? ' &middot; ' + v.stock + ' left' : ''}</small></button>`).join('')}</div></div>`, true);
  $('#md').onclick = e => { const b = e.target.closest('[data-vid]'); if (!b) return; const v = p.variants.find(x => x.id == b.dataset.vid); if (addItem(p, v)) { /* keep open for multi-select */ toast(p.name + ' - ' + v.name + ' added', 'ok'); } };
}
function discountModal() {
  const cur = S.discount;
  modal(head('Bill discount') + `<div class="mb"><div class="seg"><button data-dt="percent" class="${!cur || cur.type === 'percent' ? 'on' : ''}">Percent %</button><button data-dt="fixed" class="${cur && cur.type === 'fixed' ? 'on' : ''}">Amount ${C.currency}</button></div>
  <div class="qc">${[5, 10, 15, 20, 25, 30].map(n => `<button data-dp="${n}">${n}%</button>`).join('')}</div>${numpad()}</div><div class="mf"><button class="btn bad" data-act="nodisc">Remove</button></div>`);
  let type = cur ? cur.type : 'percent', buf = cur ? String(cur.value) : ''; const d = () => $('#npd').textContent = buf || '0'; d();
  $('#md').onclick = e => {
    const dt = e.target.closest('[data-dt]'); if (dt) { type = dt.dataset.dt; document.querySelectorAll('[data-dt]').forEach(b => b.classList.toggle('on', b === dt)); return; }
    const dp = e.target.closest('[data-dp]'); if (dp) { S.discount = {type: 'percent', value: +dp.dataset.dp}; save(); closeModal(); renderCart(); return; }
    if (e.target.closest('[data-act=nodisc]')) { S.discount = null; save(); closeModal(); renderCart(); return; }
    const k = e.target.closest('[data-k]'); if (!k) return; const v = k.dataset.k;
    if (v === 'C') buf = ''; else if (v === 'OK') { const n = parseFloat(buf || '0'); S.discount = n > 0 ? {type, value: n} : null; save(); closeModal(); renderCart(); return; }
    else if (v === '⌫') buf = buf.slice(0, -1); else if (v === '.') { if (!buf.includes('.')) buf += (buf ? '.' : '0.'); } else buf += v; d();
  };
}
async function customerModal() {
  modal(head('Customer') + `<div class="mb"><input class="fld" id="cq" placeholder="Search name or phone" inputmode="search"><div class="list" id="cl"></div>
  <hr style="border:0;border-top:1px solid var(--line);margin:14px 0"><label class="l">New customer</label><input class="fld" id="cn" placeholder="Full name"><input class="fld" id="cp" placeholder="Phone" inputmode="tel"></div>
  <div class="mf"><button class="btn bad" data-act="nocust">Walk-in</button><button class="btn pri" data-act="addcust">Add &amp; select</button></div>`);
  const list = async () => { try { const r = await api(U.customers + '?q=' + encodeURIComponent($('#cq').value)); $('#cl').innerHTML = r.map(c => `<div class="row"><div><b>${esc(c.name)}</b><br><small>${esc(c.phone || c.email || '')}${C.points.enabled ? ' &middot; ' + c.points + ' ' + esc(C.points.name) : ''}</small></div><button class="btn pri" data-pick='${esc(JSON.stringify(c))}'>Select</button></div>`).join('') || '<div class="empty">No customers</div>'; } catch (e) { toast(e.message, 'err'); } };
  list(); let t; $('#cq').oninput = () => { clearTimeout(t); t = setTimeout(list, 250); };
  $('#md').onclick = async e => {
    const p = e.target.closest('[data-pick]'); if (p) { S.customer = JSON.parse(p.dataset.pick); S.redeem = 0; save(); closeModal(); renderCart(); if (C.points.enabled && S.customer.points >= C.points.min) pointsAsk(); return; }
    if (e.target.closest('[data-act=nocust]')) { S.customer = null; S.redeem = 0; save(); closeModal(); renderCart(); return; }
    if (e.target.closest('[data-act=addcust]')) { try { const c = await api(U.customers, {method: 'POST', body: {name: $('#cn').value, phone: $('#cp').value}}); S.customer = c; S.redeem = 0; save(); closeModal(); renderCart(); toast('Customer added', 'ok'); } catch (er) { toast(er.message, 'err'); } }
  };
}
function pointsAsk() {
  const t = calc(); const max = Math.max(0, Math.min(S.customer.points, Math.floor((t.gross - t.ld - t.cd) * C.points.maxPercent / 100 / C.points.value)));
  if (max < C.points.min) return;
  modal(head(esc(C.points.name)) + `<div class="mb"><p><b>${esc(S.customer.name)}</b> has <b>${S.customer.points}</b> ${esc(C.points.name)}.<br>Up to <b>${max}</b> can be used on this bill (worth ${fmt(max * C.points.value)}).</p></div><div class="mf"><button class="btn" data-act="close">Skip</button><button class="btn pri" data-usepts="${max}">Use ${max}</button></div>`);
  $('#md').onclick = e => { const b = e.target.closest('[data-usepts]'); if (b) { S.redeem = +b.dataset.usepts; save(); closeModal(); renderCart(); } };
}

/* -------------------------------------------------------------- payment */
function payModal() {
  const t = calc(); let pays = [], method = 'cash', buf = '';
  const paid = () => pays.reduce((a, p) => a + p.amount, 0), due = () => Math.max(0, Math.round((t.total - paid()) * 100) / 100);
  const names = {cash: 'Cash', card: 'Card', qr: 'QR Payment', bank: 'Bank Transfer'};
  function draw() {
    const d = due(), tend = paid(), chg = Math.max(0, tend - t.total);
    const next = [Math.ceil(d / 100) * 100, Math.ceil(d / 500) * 500, Math.ceil(d / 1000) * 1000, 2000, 5000].filter((v, i, a) => v >= d && a.indexOf(v) === i).slice(0, 4);
    modal(head('Payment') + `<div class="mb"><div class="paygrid"><div>
      <div class="sum"><div><span>Total</span><span class="big">${fmt(t.total)}</span></div><div><span>Paid</span><span>${fmt(tend)}</span></div><div><span>${d > 0 ? 'Remaining' : 'Change'}</span><b style="font-size:20px;color:${d > 0 ? 'var(--bad)' : 'var(--ok)'}">${fmt(d > 0 ? d : chg)}</b></div></div>
      <div class="seg" style="margin-top:12px">${Object.keys(names).map(k => `<button data-m="${k}" class="${method === k ? 'on' : ''}">${names[k]}</button>`).join('')}</div>
      ${method === 'cash' ? `<div class="qc"><button data-qc="${d}">Exact</button>${next.map(v => `<button data-qc="${v}">${v}</button>`).join('')}</div>` : (method !== 'cash' ? '<input class="fld" id="pref" placeholder="' + (method === 'card' ? 'Approval no. (optional)' : 'Transaction / voucher ref. (optional)') + '" style="font-size:16px">' + (method === 'qr' || method === 'bank' ? '<small style="color:var(--bad)">Not verified yet: the bill stays <b>Pending</b> until an admin confirms the money arrived.</small>' : '') : '')}
      ${pays.map((p, i) => `<div class="pl"><span>${names[p.method]}${p.ref ? ' (' + esc(p.ref) + ')' : ''}</span><span>${fmt(p.amount)} <button data-rp="${i}" style="color:var(--bad);margin-left:8px">&#10005;</button></span></div>`).join('')}
    </div><div>${numpad()}<button class="btn pri" style="width:100%;margin-top:8px" data-act="addpay">Add ${names[method]} payment</button></div></div></div>
    <div class="mf"><button class="btn" data-act="close">Cancel</button><button class="btn ok" id="done" data-act="finish" ${paid() + 0.001 < t.total ? 'disabled style="opacity:.4"' : ''}>Complete Sale</button></div>`, true);
    $('#npd').textContent = buf || (d > 0 ? String(d) : '0');
  }
  draw();
  $('#md').onclick = async e => {
    const m = e.target.closest('[data-m]'); if (m) { method = m.dataset.m; buf = ''; draw(); return; }
    const qc = e.target.closest('[data-qc]'); if (qc) { buf = String(qc.dataset.qc); draw(); return; }
    const rp = e.target.closest('[data-rp]'); if (rp) { pays.splice(+rp.dataset.rp, 1); draw(); return; }
    const k = e.target.closest('[data-k]');
    if (k) { const v = k.dataset.k; if (v === 'C') buf = ''; else if (v === 'OK') { addPay(); return; } else if (v === '⌫') buf = buf.slice(0, -1); else if (v === '.') { if (!buf.includes('.')) buf += (buf ? '.' : '0.'); } else buf += v; $('#npd').textContent = buf || '0'; return; }
    if (e.target.closest('[data-act=addpay]')) { addPay(); return; }
    if (e.target.closest('[data-act=finish]')) { if (paid() + 0.001 < t.total) return; finish(); }
  };
  function addPay() {
    const ref = $('#pref') ? $('#pref').value : '';
    let amt = parseFloat(buf || String(due())); if (!(amt > 0)) return;
    if (method !== 'cash' && amt > due() + 0.001) amt = due();
    pays.push({method, amount: Math.round(amt * 100) / 100, ref}); buf = ''; draw();
  }
  async function finish() {
    const b = $('#done'); b.disabled = true; b.textContent = 'Processing...';
    try {
      const r = await api(U.sale, {method: 'POST', body: {items: S.lines.map(l => ({product_id: l.product_id, variant_id: l.variant_id, qty: l.qty, unit_price: l.unit, line_discount: l.disc})), discount: S.discount, customer_id: S.customer ? S.customer.id : null, redeem_points: S.customer ? S.redeem : 0, payments: pays, note: S.note, hold_id: S.holdId}});
      const sold = S.lines.slice(); S.lines.forEach(l => { const p = byId[l.product_id]; if (!p) return; const v = l.variant_id ? p.variants.find(x => x.id == l.variant_id) : p; if (v && v.stock !== null) v.stock -= l.qty; });
      S = fresh(); save(); renderCart(); renderGrid();
      $('#cart').classList.remove('open'); successModal(r); if (C.autoPrint) printReceipt(r.order_id);
      if (r.hold) loadHolds();
    } catch (er) { toast(er.message, 'err'); b.disabled = false; b.textContent = 'Complete Sale'; }
  }
}
function successModal(r) {
  modal(`<div class="mb done"><div style="font-size:54px">${r.pending_verification ? '&#8987;' : '&#10004;'}</div><h1 style="${r.pending_verification ? 'color:var(--warn)' : ''}">${r.pending_verification ? 'Awaiting payment verification' : 'Sale complete'}</h1><div>${esc(r.invoice_no)} &middot; ${fmt(r.total)}</div>${r.change > 0 ? `<p>Change due</p><div class="chg">${fmt(r.change)}</div>` : ''}</div>
  <div class="mf" style="justify-content:center"><button class="btn" data-pr="${r.order_id}">&#128424; Print receipt</button><button class="btn pri" data-act="close">New sale</button></div>`);
  $('#md').onclick = e => { const p = e.target.closest('[data-pr]'); if (p) printReceipt(p.dataset.pr); };
}
function printReceipt(id) {
  const f = $('#pf'); f.onload = () => { try { f.contentWindow.focus(); f.contentWindow.print(); } catch (e) { window.open(U.receipt + id + '?print=1', '_blank'); } f.onload = null; };
  f.src = U.receipt + id + '?_=' + Date.now();
}

/* ------------------------------------------------------- hold / register */
async function loadHolds() { try { const h = await api(U.holds); const n = $('#holdN'); n.dataset.n = h.length; n.textContent = h.length; n.style.display = h.length ? 'inline-block' : 'none'; return h; } catch (e) { return []; } }
async function holdsModal() {
  const h = await loadHolds();
  modal(head('Held bills') + `<div class="mb list">${h.map(x => `<div class="row"><div><b>${esc(x.label)}</b><br><small>${x.payload.lines.length} items &middot; ${esc(x.created_at)}</small></div><div><button class="btn pri" data-resume="${x.id}">Resume</button> <button class="btn bad" data-del="${x.id}">&#10005;</button></div></div>`).join('') || '<div class="empty">No held bills</div>'}</div>`);
  $('#md').onclick = async e => {
    const r = e.target.closest('[data-resume]'), d = e.target.closest('[data-del]');
    if (r) { const x = h.find(y => y.id == r.dataset.resume); if (S.lines.length && !confirm('Replace the current cart?')) return; S = Object.assign(fresh(), x.payload, {holdId: x.id}); save(); closeModal(); renderCart(); }
    if (d) { await api(U.holds + '/' + d.dataset.del, {method: 'DELETE'}); holdsModal(); }
  };
}
async function holdCart() {
  if (!S.lines.length) return toast('Cart is empty', 'err');
  await api(U.holds, {method: 'POST', body: {label: (S.customer ? S.customer.name + ' ' : '') + new Date().toTimeString().slice(0, 5), payload: S}});
  if (S.holdId) { try { await api(U.holds + '/' + S.holdId, {method: 'DELETE'}); } catch (e) {} }
  S = fresh(); save(); renderCart(); loadHolds(); toast('Bill held', 'ok');
}
async function registerModal() {
  const r = await api(U.regSum);
  if (!r.register) {
    modal(head('Open register') + `<div class="mb"><p>Cashier: <b>${esc(C.cashier)}</b></p><label class="l">Opening cash (${C.currency})</label>${numpad()}</div>`);
    let buf = ''; $('#md').onclick = async e => { const k = e.target.closest('[data-k]'); if (!k) return; const v = k.dataset.k;
      if (v === 'C') buf = ''; else if (v === 'OK') { try { await api(U.regOpen, {method: 'POST', body: {opening_cash: parseFloat(buf || '0')}}); C.register = {open: 1}; closeModal(); toast('Register opened', 'ok'); } catch (er) { toast(er.message, 'err'); } return; }
      else if (v === '⌫') buf = buf.slice(0, -1); else if (v === '.') { if (!buf.includes('.')) buf += (buf ? '.' : '0.'); } else buf += v; $('#npd').textContent = buf || '0'; };
    return;
  }
  const s = r.summary;
  modal(head('Register') + `<div class="mb"><div class="sum"><div><span>Opening cash</span><span>${fmt(r.register.opening_cash)}</span></div><div><span>Bills</span><span>${s.sales_count}</span></div><div><span>Total sales</span><b>${fmt(s.total_sales)}</b></div>
  <div><span>Cash sales</span><span>${fmt(s.by_method.cash)}</span></div><div><span>Card</span><span>${fmt(s.by_method.card)}</span></div><div><span>QR / Wallet</span><span>${fmt(s.by_method.qr)}</span></div>
  <div><span>Cash in / out</span><span>${fmt(s.cash_in)} / ${fmt(s.cash_out)}</span></div><div><span>Cash refunds</span><span>${fmt(s.cash_refunds)}</span></div><div><span><b>Expected in drawer</b></span><b>${fmt(s.expected_cash)}</b></div></div>
  <div style="display:flex;gap:8px;margin-top:12px"><button class="btn" data-cash="in">+ Cash in</button><button class="btn" data-cash="out">- Cash out</button><button class="btn bad" data-act="closereg" style="margin-left:auto">Close register</button></div></div>`);
  $('#md').onclick = e => {
    const c = e.target.closest('[data-cash]');
    if (c) askNumber('Cash ' + c.dataset.cash, '', async n => { if (n > 0) { const why = prompt('Reason (optional)') || ''; try { await api(U.regCash, {method: 'POST', body: {type: c.dataset.cash, amount: n, reason: why}}); toast('Recorded', 'ok'); } catch (er) { toast(er.message, 'err'); } } });
    if (e.target.closest('[data-act=closereg]')) askNumber('Counted cash in drawer', '', async n => { try { const x = await api(U.regClose, {method: 'POST', body: {closing_cash: n}}); C.register = null; toast('Register closed', 'ok'); window.open(U.regReport + x.id + '?print=1', '_blank'); } catch (er) { toast(er.message, 'err'); } }, 'Expected: ' + fmt(s.expected_cash));
  };
}

/* ------------------------------------------------------ quick add product */
function quickModal(code) {
  modal(head('New product') + `<div class="mb"><label class="l">Barcode (scan or type)</label><div style="display:flex;gap:8px"><input class="fld" id="qb" value="${esc(code || '')}" placeholder="Scan now..." inputmode="numeric"><button class="btn" data-act="camfield" style="margin-top:4px">&#128247;</button></div>
  <label class="l">Name</label><input class="fld" id="qn"><div style="display:flex;gap:10px"><div style="flex:1"><label class="l">Price</label><input class="fld" id="qp" inputmode="decimal"></div><div style="flex:1"><label class="l">Stock qty</label><input class="fld" id="qq" inputmode="numeric" value="1"></div></div>
  <label class="l">Category</label><select class="fld" id="qc">${DB.categories.filter(c => c.parent_id).map(c => `<option value="${c.id}">${esc((DB.categories.find(p => p.id == c.parent_id) || {}).name)} / ${esc(c.name)}</option>`).join('')}</select>
  <small style="color:var(--mut)">Leave barcode blank to auto-generate one. For size/colour variants use Admin &rarr; Products.</small></div><div class="mf"><button class="btn" data-act="close">Cancel</button><button class="btn pri" data-act="saveq">Save product</button></div>`);
  setTimeout(() => $(code ? '#qn' : '#qb').focus(), 50);
  $('#md').onclick = async e => {
    if (e.target.closest('[data-act=camfield]')) camera(c => { $('#qb').value = c; $('#qn').focus(); }); 
    if (e.target.closest('[data-act=saveq]')) { try { await api(U.quick, {method: 'POST', body: {barcode: $('#qb').value, name: $('#qn').value, price: $('#qp').value, qty: $('#qq').value, category_id: $('#qc').value}}); closeModal(); toast('Product saved', 'ok'); await loadCatalog(); } catch (er) { toast(er.message, 'err'); } }
  };
}

/* ------------------------------------------------------------- scanning */
let camStream = null, camTimer = null;
function stopCam() { if (camTimer) clearInterval(camTimer); camTimer = null; if (camStream) camStream.getTracks().forEach(t => t.stop()); camStream = null; }
async function camera(cb) {
  if (!('BarcodeDetector' in window)) return toast('Camera scanning is not supported in this browser. Use a USB/Bluetooth scanner.', 'err');
  modal(head('Scan barcode') + '<div class="mb cam"><video id="cv" playsinline muted></video><p style="text-align:center;color:var(--mut)">Point the camera at a barcode</p></div>');
  try {
    camStream = await navigator.mediaDevices.getUserMedia({video: {facingMode: 'environment'}}); const v = $('#cv'); v.srcObject = camStream; await v.play();
    const det = new BarcodeDetector({formats: ['ean_13', 'ean_8', 'code_128', 'upc_a', 'upc_e', 'code_39', 'qr_code']});
    camTimer = setInterval(async () => { try { const r = await det.detect(v); if (r.length) { const code = r[0].rawValue; stopCam(); closeModal(); cb(code); } } catch (e) {} }, 250);
  } catch (e) { closeModal(); toast('Camera unavailable: ' + e.message, 'err'); }
}
async function handleScan(code) {
  code = String(code).trim(); if (!code) return;
  const quick = $('#qb');
  if (quick && $('#ov').classList.contains('on')) { quick.value = code; const hit = byCode[code.toLowerCase()]; if (hit) toast('Already exists: ' + hit.p.name, 'err'); else $('#qn').focus(); return; }
  let hit = byCode[code.toLowerCase()];
  if (!hit) { try { const r = await api(U.barcode + encodeURIComponent(code)); await loadCatalog(); hit = byCode[code.toLowerCase()] || (byId[r.product_id] ? {p: byId[r.product_id], v: r.variant_id ? byId[r.product_id].variants.find(v => v.id == r.variant_id) : null} : null); } catch (e) {} }
  if (!hit) { toast('Unknown barcode ' + code, 'err'); if (confirm('Barcode ' + code + ' not found. Add it as a new product?')) quickModal(code); return; }
  if (hit.p.variants.length && !hit.v) return variantPicker(hit.p);
  if (addItem(hit.p, hit.v)) toast(hit.p.name + (hit.v ? ' - ' + hit.v.name : ''), 'ok');
}
// hardware scanners act as a fast keyboard ending with Enter
let sbuf = '', slast = 0;
document.addEventListener('keydown', e => {
  const tag = (document.activeElement || {}).tagName, inField = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';
  if (e.key === 'F2') { e.preventDefault(); $('#q').focus(); $('#q').select(); return; }
  if (e.key === 'F4') { e.preventDefault(); if (S.lines.length) payModal(); return; }
  if (e.key === 'F8') { e.preventDefault(); holdCart(); return; }
  if (e.key === 'Escape') { closeModal(); return; }
  const now = Date.now();
  if (e.key === 'Enter') {
    if (document.activeElement === $('#q')) { const v = $('#q').value.trim(); if (v && (byCode[v.toLowerCase()] || /^\d{6,}$/.test(v))) { e.preventDefault(); $('#q').value = ''; query = ''; renderGrid(); handleScan(v); } return; }
    if (!inField && sbuf.length >= 4) { e.preventDefault(); handleScan(sbuf); }
    sbuf = ''; return;
  }
  if (e.key.length === 1) { sbuf = (now - slast > 80 ? '' : sbuf) + e.key; slast = now; }
});

/* --------------------------------------------------------------- events */
document.addEventListener('click', e => {
  const t = e.target;
  let b;
  if ((b = t.closest('[data-cat]'))) { cat = +b.dataset.cat; sub = 0; renderCats(); renderGrid(); return; }
  if ((b = t.closest('[data-sub]'))) { sub = +b.dataset.sub; renderCats(); renderGrid(); return; }
  if ((b = t.closest('[data-pid]'))) { const p = byId[b.dataset.pid]; if (p.variants.length) variantPicker(p); else if (addItem(p, null)) {} return; }
  if ((b = t.closest('[data-q]'))) { const l = S.lines[+b.dataset.i], n = l.qty + +b.dataset.q; if (n <= 0) S.lines.splice(+b.dataset.i, 1); else if (l.stock !== null && n > l.stock) toast('Only ' + l.stock + ' in stock', 'err'); else l.qty = n; save(); renderCart(); return; }
  if ((b = t.closest('[data-rm]'))) { S.lines.splice(+b.dataset.rm, 1); save(); renderCart(); return; }
  if ((b = t.closest('[data-edit]'))) {
    const l = S.lines[+b.dataset.i], k = b.dataset.edit;
    if (k === 'qty') askNumber('Quantity', l.qty, n => { n = Math.floor(n); if (n <= 0) S.lines.splice(+b.dataset.i, 1); else if (l.stock !== null && n > l.stock) toast('Only ' + l.stock + ' in stock', 'err'); else l.qty = n; save(); renderCart(); });
    if (k === 'disc') askNumber('Item discount (' + C.currency + ')', l.disc || '', n => { l.disc = Math.min(n, l.unit * l.qty); save(); renderCart(); });
    if (k === 'price') askNumber('Unit price', l.unit, n => { l.unit = n; save(); renderCart(); });
    return;
  }
  b = t.closest('[data-act]'); if (!b) return;
  switch (b.dataset.act) {
    case 'close': closeModal(); break;
    case 'clearq': $('#q').value = ''; query = ''; renderGrid(); break;
    case 'camera': camera(handleScan); break;
    case 'quick': quickModal(); break;
    case 'holds': holdsModal(); break;
    case 'register': registerModal(); break;
    case 'fs': (document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen()).catch(() => {}); break;
    case 'customer': customerModal(); break;
    case 'clearcart': if (S.lines.length && confirm('Clear the cart?')) { S = fresh(); save(); renderCart(); } break;
    case 'opencart': $('#cart').classList.add('open'); break;
    case 'closecart': $('#cart').classList.remove('open'); break;
    case 'discount': discountModal(); break;
    case 'hold': holdCart(); break;
    case 'note': { const n = prompt('Bill note', S.note || ''); if (n !== null) { S.note = n; save(); } break; }
    case 'pay':
      if (C.requireRegister && !C.register) { toast('Open the register first', 'err'); registerModal(); break; }
      if (S.customer && C.points.enabled && !S.redeem && S.customer.points >= C.points.min) { pointsAsk(); const m = $('#md'); const prev = m.onclick; m.onclick = e2 => { if (e2.target.closest('[data-usepts]') || e2.target.closest('[data-act=close]')) { prev(e2); setTimeout(payModal, 30); } }; }
      else payModal();
      break;
  }
});
$('#q').addEventListener('input', e => { query = e.target.value; renderGrid(); });

loadCatalog().then(() => { loadHolds(); if (C.requireRegister && !C.register) registerModal(); }).catch(e => toast(e.message, 'err'));
renderCart();
setInterval(() => { if (!$('#ov').classList.contains('on') && !S.lines.length) loadCatalog().catch(() => {}); }, 120000);
})();
@endverbatim
