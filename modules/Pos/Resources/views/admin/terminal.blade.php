<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
<meta name="theme-color" content="#e91e8c">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="POS">
<link rel="manifest" href="/pos-manifest.json">
<link rel="apple-touch-icon" href="/pos-icon-192.png">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>POS - {{ $storeName }}</title>
<style>
:root{--p:#e91e8c;--pd:#c2127a;--pl:#fde7f3;--bg:#f6f1f4;--ink:#241b21;--mut:#7d6f78;--ok:#1f9d63;--warn:#e08a00;--bad:#d32f2f;--card:#fff;--line:#eadfe5;--r:14px}
*{box-sizing:border-box;-webkit-tap-highlight-color:transparent;touch-action:manipulation}
html,body{height:100%;height:100dvh;margin:0;overscroll-behavior:none;background:var(--bg);color:var(--ink);font:500 15px/1.35 -apple-system,"Segoe UI",Roboto,Arial,sans-serif;overflow:hidden;user-select:none;-webkit-user-select:none}
button{font:inherit;cursor:pointer;border:0;color:inherit;background:none}
input,select{font:inherit;user-select:text;-webkit-user-select:text}
.app{display:grid;grid-template-rows:auto 1fr;height:100%;padding-top:env(safe-area-inset-top)}
.top{display:flex;align-items:center;gap:10px;padding:8px 12px;background:#fff;border-bottom:1px solid var(--line)}
.brand{font-weight:800;color:var(--p);font-size:18px;white-space:nowrap;display:flex;align-items:center;gap:8px}
.brand i{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#ff7eb6,var(--p));display:inline-block}
.search{flex:1;position:relative;min-width:120px}
.search input{width:100%;height:46px;border:2px solid var(--line);border-radius:12px;padding:0 90px 0 14px;font-size:16px;outline:0;background:#fff}
.search input:focus{border-color:var(--p)}
.search .sbtn{position:absolute;right:4px;top:4px;height:38px;width:42px;border-radius:9px;background:var(--pl);font-size:18px}
.search .sbtn.cam{right:48px}
.tb{height:46px;padding:0 14px;border-radius:12px;background:#f2e9ee;font-weight:700;white-space:nowrap;position:relative}
.tb.pri{background:var(--p);color:#fff}
.tb .n{position:absolute;top:-5px;right:-4px;background:var(--bad);color:#fff;border-radius:10px;font-size:11px;padding:1px 6px}
.main{display:grid;grid-template-columns:minmax(0,1fr) 400px;gap:10px;padding:10px;min-height:0}
.left{display:flex;flex-direction:column;min-height:0;gap:8px}
.chips{display:flex;gap:8px;overflow-x:auto;padding-bottom:2px;flex:none}
.chip{flex:none;padding:10px 16px;border-radius:999px;background:#fff;border:1px solid var(--line);font-weight:700}
.chip.on{background:var(--p);color:#fff;border-color:var(--p)}
.sub .chip{padding:6px 12px;font-size:13px}
.grid{flex:1;overflow-y:auto;display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;align-content:start;grid-auto-rows:max-content;padding:2px 2px 20px}
.pc{cursor:pointer;background:var(--card);border-radius:var(--r);border:1px solid var(--line);overflow:hidden;text-align:left;display:flex;flex-direction:column;position:relative}
.pc:active{transform:scale(.97)}
.pc img,.pc .ph{width:100%;height:auto;aspect-ratio:1/1;object-fit:cover;background:var(--pl);display:block;min-height:110px}
.pc .t{padding:8px 10px 2px;font-weight:700;font-size:13.5px;line-height:1.25;min-height:36px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.pc .pr{padding:0 10px 10px;display:flex;justify-content:space-between;align-items:baseline}
.pc .pr b{color:var(--pd);font-size:15px}.pc .pr s{color:var(--mut);font-size:12px}
.bdg{position:absolute;top:6px;left:6px;background:var(--p);color:#fff;font-size:11px;font-weight:800;border-radius:8px;padding:2px 7px}
.bdg.st{left:auto;right:6px;background:#2b2b2b}.bdg.st.low{background:var(--warn)}.bdg.st.out{background:var(--bad)}
.cart{background:#fff;border-radius:var(--r);border:1px solid var(--line);display:flex;flex-direction:column;min-height:0}
.chead{display:flex;gap:8px;padding:10px;border-bottom:1px solid var(--line)}
.cust{flex:1;text-align:left;background:var(--pl);border-radius:10px;padding:8px 12px;font-weight:700;min-height:44px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.cust small{display:block;font-weight:500;color:var(--mut)}
.lines{flex:1;overflow-y:auto;padding:4px 8px}
.empty{height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--mut);text-align:center;padding:20px}
.ln{display:grid;grid-template-columns:1fr auto;gap:2px 8px;padding:8px 4px;border-bottom:1px dashed var(--line)}
.ln .nm{font-weight:700;font-size:14px}.ln .vr{font-size:12px;color:var(--mut)}
.ln .am{font-weight:800;text-align:right}
.ln .ctl{grid-column:1/3;display:flex;align-items:center;gap:6px;margin-top:4px}
.qb{width:40px;height:40px;border-radius:10px;background:#f2e9ee;font-size:20px;font-weight:800}
.qv{min-width:46px;height:40px;border-radius:10px;border:1px solid var(--line);font-weight:800;background:#fff}
.mini{height:40px;padding:0 10px;border-radius:10px;background:#f7f1f4;font-size:12.5px;font-weight:700}
.rm{margin-left:auto;background:#fde8e8;color:var(--bad)}
.tots{padding:8px 14px;border-top:1px solid var(--line);font-size:14px}
.tots div{display:flex;justify-content:space-between;padding:2px 0}
.tots .gt{font-size:22px;font-weight:900;color:var(--pd);padding-top:6px}
.btns{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;padding:0 10px 10px}
.btns button{height:48px;border-radius:12px;background:#f2e9ee;font-weight:800}
.pay{grid-column:1/4;height:62px!important;background:var(--p)!important;color:#fff;font-size:20px;box-shadow:0 6px 16px #e91e8c44}
.pay:disabled{opacity:.45;box-shadow:none}
.ov{position:fixed;inset:0;background:#241b2188;display:none;align-items:center;justify-content:center;z-index:50;padding:12px}
.ov.on{display:flex}
.md{background:#fff;border-radius:18px;max-height:96vh;width:min(560px,100%);display:flex;flex-direction:column;overflow:hidden;box-shadow:0 20px 60px #0004}
.md.wide{width:min(900px,100%)}
.mh{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--line);font-weight:800;font-size:18px}
.mh button{width:40px;height:40px;border-radius:50%;background:#f2e9ee;font-size:20px}
.mb{padding:16px 18px;overflow-y:auto}
.mf{padding:12px 18px;border-top:1px solid var(--line);display:flex;gap:10px;justify-content:flex-end}
.btn{height:50px;padding:0 22px;border-radius:12px;background:#f2e9ee;font-weight:800}
.btn.pri{background:var(--p);color:#fff}.btn.ok{background:var(--ok);color:#fff}.btn.bad{background:#fde8e8;color:var(--bad)}
.fld{width:100%;height:50px;border:2px solid var(--line);border-radius:12px;padding:0 14px;font-size:18px;outline:0;margin:4px 0 10px}
.fld:focus{border-color:var(--p)}
label.l{font-size:12px;font-weight:800;color:var(--mut);text-transform:uppercase;letter-spacing:.5px}
.vgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px}
.vb{padding:12px 8px;border-radius:12px;border:2px solid var(--line);background:#fff;font-weight:800;text-align:center}
.vb small{display:block;font-weight:600;color:var(--mut)}
.vb.out{opacity:.4}
.vb:active{border-color:var(--p);background:var(--pl)}
.np{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:8px}
.np button{height:58px;border-radius:12px;background:#f2e9ee;font-size:22px;font-weight:800}
.np .w2{grid-column:span 2}
.disp{height:64px;border-radius:12px;background:#faf5f8;border:2px solid var(--line);display:flex;align-items:center;justify-content:flex-end;padding:0 16px;font-size:30px;font-weight:900}
.seg{display:flex;gap:8px;margin:6px 0 10px}
.seg button{flex:1;height:48px;border-radius:12px;background:#f2e9ee;font-weight:800}
.seg button.on{background:var(--p);color:#fff}
.paygrid{display:grid;grid-template-columns:1.1fr 1fr;gap:16px}
.sum{background:#faf5f8;border-radius:14px;padding:12px 14px}
.sum div{display:flex;justify-content:space-between;padding:3px 0}
.sum .big{font-size:26px;font-weight:900;color:var(--pd)}
.qc{display:flex;flex-wrap:wrap;gap:8px;margin:8px 0}
.qc button{height:44px;padding:0 14px;border-radius:10px;background:var(--pl);font-weight:800}
.pl{display:flex;justify-content:space-between;align-items:center;padding:8px 10px;border-radius:10px;background:#f2e9ee;margin:4px 0;font-weight:700}
.toast{position:fixed;left:50%;bottom:28px;transform:translateX(-50%);background:#241b21;color:#fff;padding:14px 22px;border-radius:12px;font-weight:700;z-index:99;display:none;max-width:90vw;box-shadow:0 8px 24px #0005}
.toast.err{background:var(--bad)}.toast.ok{background:var(--ok)}
.done{text-align:center;padding:10px}.done h1{font-size:44px;color:var(--ok);margin:6px 0}.done .chg{font-size:34px;font-weight:900;color:var(--pd)}
.list .row{display:flex;justify-content:space-between;align-items:center;padding:12px;border-bottom:1px solid var(--line);gap:10px}
.cam video{width:100%;border-radius:12px;background:#000;max-height:60vh}

.top{flex-wrap:wrap}
#cartBar{display:none}.mclose{display:none}
@media(min-width:821px) and (max-width:1100px){.main{grid-template-columns:minmax(0,1fr) 340px}.tb span{display:none}.brand span{display:none}}
@media(max-width:820px){
  .app{grid-template-rows:auto 1fr}
  .top{padding:8px;gap:8px}
  .brand span{display:none}
  .search{order:9;flex:1 1 100%}
  .tb{height:44px;padding:0 13px}.tb span{display:none}
  .main{grid-template-columns:minmax(0,1fr);grid-template-rows:minmax(0,1fr);padding:8px 8px calc(78px + env(safe-area-inset-bottom))}
  .grid{grid-template-columns:repeat(auto-fill,minmax(128px,1fr));gap:8px}
  .pc .t{font-size:13px}
  .chip{padding:10px 14px}
  .cart{position:fixed;left:0;right:0;bottom:0;height:92dvh;border-radius:20px 20px 0 0;border:0;z-index:40;transform:translateY(105%);transition:transform .25s ease;box-shadow:0 -10px 40px #0005}
  .cart.open{transform:none}
  .mclose{display:inline-block;height:auto!important}
  .btns{padding-bottom:calc(10px + env(safe-area-inset-bottom))}
  #cartBar{display:flex;position:fixed;left:8px;right:8px;bottom:calc(8px + env(safe-area-inset-bottom));z-index:30;background:var(--ink);color:#fff;border-radius:18px;padding:8px 8px 8px 16px;align-items:center;gap:10px;box-shadow:0 8px 24px #0006}
  #cartBar .ci{flex:1;text-align:left;min-width:0;line-height:1.2}
  #cartBar .ci b{display:block;font-size:17px}#cartBar .ci span{font-size:12px;opacity:.75}
  #cartBar button{height:48px;padding:0 18px;border-radius:13px;font-weight:800;background:#ffffff22;color:#fff;white-space:nowrap}
  #cartBar button.go{background:var(--p)}
  #cartBar button:disabled{opacity:.4}
  #cartBar.bump{animation:bump .25s}@keyframes bump{50%{transform:scale(1.04)}}
  .ov{padding:0;align-items:stretch;justify-content:stretch}
  .md,.md.wide{width:100%;max-width:none;height:100dvh;max-height:100dvh;border-radius:0}
  .mh{padding:12px 16px;padding-top:calc(12px + env(safe-area-inset-top))}
  .mb{flex:1;padding:14px 14px}
  .mf{padding:10px 14px calc(10px + env(safe-area-inset-bottom));flex-wrap:wrap}
  .mf .btn{flex:1;min-width:120px}
  .paygrid{grid-template-columns:1fr;gap:10px}
  .np button{height:54px}
  .disp{height:56px;font-size:26px}
  .vgrid{grid-template-columns:repeat(auto-fill,minmax(108px,1fr))}
  .toast{bottom:auto;top:calc(12px + env(safe-area-inset-top));width:90vw;text-align:center;z-index:120}
  .ln .ctl{flex-wrap:wrap}
  .ln .ctl .mini{flex:1 1 auto}
  .cam video{max-height:50dvh}
}
@media(max-width:400px){.grid{grid-template-columns:repeat(2,1fr)}.tb{padding:0 10px}}
@media(max-height:520px) and (orientation:landscape) and (max-width:900px){#cartBar{padding:4px 6px 4px 12px}#cartBar button{height:40px}.main{padding-bottom:64px}}
</style>
</head>
<body>
<div class="app">
  <div class="top">
    <div class="brand"><i></i><span>{{ $storeName }} POS</span></div>
    <div class="search">
      <input id="q" type="search" placeholder="Search or scan barcode" autocomplete="off" inputmode="search">
      <button class="sbtn cam" data-act="camera" title="Scan with camera">&#128247;</button>
      <button class="sbtn" data-act="clearq" title="Clear">&#10005;</button>
    </div>
    <button class="tb" data-act="quick">+ <span>New Product</span></button>
    <button class="tb" data-act="holds"><span>Held</span> &#9208;<b class="n" id="holdN" style="display:none">0</b></button>
    <button class="tb" data-act="register"><span>Register</span> &#128176;</button>
    <button class="tb" data-act="fs" title="Fullscreen">&#9974;</button>
    <a class="tb" href="{{ route('admin.pos.sales') }}" style="display:flex;align-items:center;color:inherit;text-decoration:none"><span>Sales</span>&nbsp;&#128209;</a>
    <a class="tb" href="{{ route('admin.dashboard.index') }}" style="display:flex;align-items:center;color:inherit;text-decoration:none">&#10162;</a>
  </div>

  <div class="main">
    <div class="left">
      <div class="chips" id="cats"></div>
      <div class="chips sub" id="subs"></div>
      <div class="grid" id="grid"></div>
    </div>

    <div class="cart" id="cart">
      <div class="chead">
        <button class="cust" data-act="customer" id="custBtn">&#128100; Walk-in Customer<small>Tap to add customer</small></button>
        <button class="mini" data-act="clearcart" style="height:auto">Clear</button><button class="mini mclose" data-act="closecart" aria-label="Close cart">&#10005;</button>
      </div>
      <div class="lines" id="lines"></div>
      <div class="tots" id="tots"></div>
      <div class="btns">
        <button data-act="discount">% Discount</button>
        <button data-act="hold">Hold</button>
        <button data-act="note">Note</button>
        <button class="pay" id="payBtn" data-act="pay" disabled>Pay</button>
      </div>
    </div>
  </div>
</div>

<div id="cartBar"><div class="ci"><b id="cbTotal">NPR 0.00</b><span id="cbItems">Cart is empty</span></div><button data-act="opencart">View cart</button><button class="go" id="cbPay" data-act="pay" disabled>Pay</button></div>
<div class="ov" id="ov"><div class="md" id="md"></div></div>
<div class="toast" id="toast"></div>
<iframe id="pf" style="position:fixed;width:0;height:0;border:0;left:-99px" title="print"></iframe>

<script>
window.POS = @json($config);
window.POS.register = @json($register);
window.POS.cashier = @json($cashier);
window.POS.urls = {
  catalog: "{{ route('admin.pos.catalog') }}", sale: "{{ route('admin.pos.sale') }}", barcode: "{{ url('admin/pos/barcode') }}/",
  customers: "{{ route('admin.pos.customers') }}", holds: "{{ route('admin.pos.holds') }}", quick: "{{ route('admin.pos.quick_product') }}",
  regOpen: "{{ route('admin.pos.register.open') }}", regSum: "{{ route('admin.pos.register.summary') }}", regCash: "{{ route('admin.pos.register.cash') }}", regClose: "{{ route('admin.pos.register.close') }}",
  receipt: "{{ url('admin/pos/receipt') }}/", regReport: "{{ url('admin/pos/registers') }}/"
};
</script>
<script>
@include('pos::admin.terminal_js')
</script>
</body>
</html>
