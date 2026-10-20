@extends('layouts.buyer')

@section('title', 'MOVR — Technical Sportswear & Performance Gear')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #050507;
  --fg: #e9ecf2;
  --dim: #7b8090;
  --line: rgba(233, 236, 242, .22);
  --panel: rgba(12, 13, 18, .82);
  --accent-movr: #63A2BB;
  color-scheme: dark;
}

.movr-3d-landing {
  background: var(--bg);
  color: var(--fg);
  font-family: 'Share Tech Mono', ui-monospace, Menlo, monospace;
  font-size: 13px;
  line-height: 1.5;
  overflow-x: hidden;
  background-image: linear-gradient(rgba(255, 255, 255, .035) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, .035) 1px, transparent 1px);
  background-size: 14px 14px;
  margin: -1.5rem -1rem; /* Full bleed inside layout container */
}

@media (min-width: 640px) {
  .movr-3d-landing { margin: -1.5rem -1.5rem; }
}
@media (min-width: 1024px) {
  .movr-3d-landing { margin: -2rem -2rem; }
}

.movr-3d-landing a { color: inherit; text-decoration: none; }
.movr-3d-landing .btn {
  display: inline-flex;
  gap: 10px;
  align-items: center;
  border: 1px solid var(--line);
  background: rgba(0, 0, 0, .6);
  color: var(--fg);
  padding: 10px 20px;
  font: inherit;
  cursor: pointer;
  transition: background .2s, color .2s, border-color .2s;
  border-radius: 4px;
}
.movr-3d-landing .btn:hover, .movr-3d-landing .btn:focus-visible {
  background: var(--fg);
  color: #000;
  outline: none;
  border-color: var(--fg);
}

.movr-3d-landing h2 {
  font-weight: 400;
  font-size: clamp(34px, 5.4vw, 64px);
  line-height: 1;
  letter-spacing: .02em;
}
.movr-3d-landing .dim { color: var(--dim); }

.movr-3d-landing .hero { position: relative; height: 100vh; min-height: 640px; }
.movr-3d-landing #gl { position: absolute; inset: 0; width: 100%; height: 100%; display: block; }
.movr-3d-landing .hero .l, .movr-3d-landing .hero .r { position: absolute; top: 50%; max-width: 190px; z-index: 10; pointer-events: none; }
.movr-3d-landing .hero .l { left: 24px; }
.movr-3d-landing .hero .r { right: 24px; text-align: right; }
.movr-3d-landing .hero .c {
  position: absolute;
  bottom: 90px;
  left: 0;
  right: 0;
  text-align: center;
  display: grid;
  gap: 14px;
  justify-items: center;
  z-index: 10;
}
.movr-3d-landing .hero .c p { max-width: 440px; color: var(--dim); }
.movr-3d-landing .corner {
  position: absolute;
  bottom: 20px;
  display: flex;
  gap: 12px;
  align-items: center;
  border: 1px solid var(--line);
  padding: 8px 12px;
  max-width: 260px;
  font-size: 10px;
  color: var(--dim);
  background: var(--panel);
  z-index: 10;
}
.movr-3d-landing .corner.a { left: 24px; }
.movr-3d-landing .corner.b { right: 24px; }

.movr-3d-landing section { position: relative; padding: 110px 24px; }
.movr-3d-landing .details { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: end; min-height: 90vh; }
.movr-3d-landing .details .copy { display: grid; gap: 20px; align-self: start; }
.movr-3d-landing .details .copy p { max-width: 320px; color: var(--dim); }
.movr-3d-landing .feat { display: grid; gap: 10px; width: min(380px, 100%); justify-self: end; }
.movr-3d-landing .feat div { border: 1px solid var(--line); background: var(--panel); padding: 14px 16px; border-radius: 4px; }
.movr-3d-landing .feat b { font-weight: 400; display: block; font-size: 14px; color: var(--fg); }
.movr-3d-landing .feat span { color: var(--dim); font-size: 11px; }

.movr-3d-landing .coll .head { display: flex; justify-content: space-between; align-items: end; gap: 24px; margin-bottom: 36px; }
.movr-3d-landing .coll .head p { max-width: 260px; color: var(--dim); font-size: 11px; }
.movr-3d-landing .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.movr-3d-landing .card {
  border: 1px solid var(--line);
  background: var(--panel);
  padding: 14px;
  display: grid;
  gap: 10px;
  position: relative;
  border-radius: 6px;
  transition: border-color .2s;
}
.movr-3d-landing .card:hover { border-color: rgba(99, 162, 187, 0.6); }
.movr-3d-landing .card .t { display: flex; justify-content: space-between; gap: 8px; text-transform: none; font-weight: bold; }
.movr-3d-landing .card canvas { width: 100%; height: auto; display: block; background: #05070D; border-radius: 4px; }
.movr-3d-landing .tags { display: flex; gap: 6px; flex-wrap: wrap; }
.movr-3d-landing .tags i { font-style: normal; border: 1px solid var(--line); color: var(--dim); font-size: 10px; padding: 3px 8px; border-radius: 2px; }
.movr-3d-landing .card .btn { justify-content: space-between; opacity: 0; transform: translateY(6px); transition: .2s; margin-top: 4px; }
.movr-3d-landing .card:hover .btn, .movr-3d-landing .card:focus-within .btn { opacity: 1; transform: none; }
.movr-3d-landing .center { text-align: center; margin-top: 36px; }

.movr-3d-landing .tech { display: grid; grid-template-columns: 1fr 1.2fr; gap: 24px; min-height: 140vh; }
.movr-3d-landing .tech .stick { position: sticky; top: 0; height: 100vh; display: grid; align-content: center; }
.movr-3d-landing .tech .copy { display: grid; gap: 24px; align-self: center; }
.movr-3d-landing .tech .copy p { max-width: 320px; color: var(--dim); }
.movr-3d-landing .stage { perspective: 900px; display: grid; place-items: center; height: 100%; }
.movr-3d-landing .stack { position: relative; width: 300px; height: 300px; transform-style: preserve-3d; transform: rotateX(62deg) rotateZ(-38deg); }
.movr-3d-landing .layer {
  position: absolute;
  inset: 0;
  border: 1px solid rgba(255, 255, 255, .3);
  transition: transform .1s linear;
  display: grid;
  place-items: center;
  font-size: 11px;
  color: #fff;
  letter-spacing: .1em;
}
.movr-3d-landing .l1 { background: linear-gradient(135deg, #0a2a44, #2c7aa8 60%, #0a1633); box-shadow: 0 0 40px rgba(99, 162, 187, .4); }
.movr-3d-landing .l2 { background: linear-gradient(135deg, #3b1d6e, #7a43d0); border-radius: 30px; }
.movr-3d-landing .l3 { background: linear-gradient(135deg, #2a1559, #5a2fa6); border-radius: 30px; }
.movr-3d-landing .l4 { background: #14121f; }
.movr-3d-landing .note {
  position: absolute;
  right: 0;
  top: 20%;
  width: min(280px, 100%);
  border: 1px solid var(--line);
  background: var(--panel);
  padding: 14px 16px;
  font-size: 11px;
  border-radius: 6px;
}
.movr-3d-landing .note b { font-weight: 400; display: block; font-size: 14px; margin-bottom: 4px; color: var(--fg); }

.movr-3d-landing .faq { display: grid; grid-template-columns: 1fr 1.1fr; gap: 32px; align-items: start; }
.movr-3d-landing .faq details { border: 1px solid var(--line); background: var(--panel); padding: 16px 18px; margin-bottom: 10px; border-radius: 4px; }
.movr-3d-landing .faq summary { cursor: pointer; list-style: none; display: flex; justify-content: space-between; gap: 16px; font-size: 14px; font-weight: bold; }
.movr-3d-landing .faq summary::after { content: "+"; font-size: 16px; color: var(--accent-movr); }
.movr-3d-landing .faq details[open] summary::after { content: "−"; }
.movr-3d-landing .faq p { color: var(--dim); margin-top: 12px; font-size: 12px; leading-height: 1.6; }

.movr-3d-landing footer {
  padding: 60px 24px calc(40px + env(safe-area-inset-bottom, 0px));
  border-top: 1px solid var(--line);
  display: flex;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
  color: var(--dim);
  font-size: 11px;
}
.movr-3d-landing #glitch { display: inline-block; }

@media (max-width: 820px) {
  .movr-3d-landing .hero .l, .movr-3d-landing .hero .r { top: 22%; max-width: 130px; font-size: 11px; }
  .movr-3d-landing .details, .movr-3d-landing .tech, .movr-3d-landing .faq { grid-template-columns: 1fr; }
  .movr-3d-landing .feat { justify-self: stretch; }
  .movr-3d-landing .grid { grid-template-columns: 1fr 1fr; }
  .movr-3d-landing .coll .head { flex-direction: column; align-items: start; }
  .movr-3d-landing .tech { min-height: auto; }
  .movr-3d-landing .tech .stick { position: relative; height: auto; padding: 40px 0; }
  .movr-3d-landing .stack { width: 220px; height: 220px; }
  .movr-3d-landing .note { position: static; margin-top: 20px; }
  .movr-3d-landing .corner { display: none; }
  .movr-3d-landing .card .btn { opacity: 1; transform: none; }
}
@media (prefers-reduced-motion: reduce) {
  html { scroll-behavior: auto; }
  * { transition: none !important; }
}
</style>
@endpush

@section('content')
<div class="movr-3d-landing">
  {{-- Hero Section --}}
  <header class="hero" id="top">
    <canvas id="gl" aria-label="Jaket puffer 3D interaktif MOVR" role="img"></canvas>
    <div class="l">ENGINEERED<br>FOR MOTION.<br>BUILT TO ENDURE.</div>
    <div class="r">DESIGNED<br>FOR ATHLETES.<br>READY FOR ANYTHING.</div>
    <div class="c">
      <p><span id="glitch">WEATHER-RESISTANT. THERMAL INSULATION.</span><br>OVERSIZED FIT. MOVR PERFORMANCE GEAR.</p>
      <a class="btn" href="#koleksi">SHOP NOW ↗</a>
    </div>
    <div class="corner a">◎ BUILT ON PERFORMANCE. DRIVEN BY INNOVATION. EST. 2026</div>
    <div class="corner b">◇ WORLDWIDE SHIPPING. FAST &amp; SECURE DELIVERY</div>
  </header>

  {{-- Details Section --}}
  <section class="details" id="detail">
    <div class="copy">
      <h2>DETAILS<br>MATTER.</h2>
      <p>Lapisan luar teknikal MOVR untuk bergerak, menghadapi cuaca ekstrim, dan semua tantangan di luar dugaan.</p>
      <a class="btn" href="#koleksi" style="justify-self:start">EXPLORE THE GEAR →</a>
    </div>
    <div class="feat">
      <div><b>Weather-resistant shell</b><span>Water-repellent outer shell menahan angin kencang dan hujan.</span></div>
      <div><b>Thermal insulation</b><span>Isian canggih menjaga panas tubuh tanpa menambah beban bobot.</span></div>
      <div><b>Reinforced construction</b><span>Jahitan bubble dan panel penguat ekstra di area tekanan tinggi.</span></div>
      <div><b>Functional storage</b><span>Kompartemen kantong zip internal &amp; eksternal untuk perlengkapan harian.</span></div>
      <div><b>Oversized athletic fit</b><span>Siluet ergonomis longgar untuk kebebasan gerak bebas dan layering.</span></div>
    </div>
  </section>

  {{-- Collections Section --}}
  <section class="coll" id="koleksi">
    <div class="head">
      <h2>COLLECTIONS.</h2>
      <p>Jaket teknikal &amp; sportswear MOVR untuk cuaca yang berubah, gerak cepat, dan kebutuhan harian.</p>
    </div>
    <div class="grid" id="grid"></div>
    <div class="center">
      <a class="btn" href="{{ route('product.index') }}">VIEW ALL PRODUCTS →</a>
    </div>
  </section>

  {{-- Technology Section --}}
  <section class="tech" id="teknologi">
    <div class="stick">
      <div class="copy">
        <h2>TECHNOLOGY<br>ENGINEERED<br>TO ENDURE</h2>
        <p>Setiap detail punya fungsi spesifik, dari outer shell pelindung di luar sampai insulasi di dalam. Dirancang untuk bekerja tanpa mengorbankan bentuknya.</p>
      </div>
    </div>
    <div class="stick">
      <div class="stage">
        <div class="stack" id="stack">
          <div class="layer l4">BASE LAYER</div>
          <div class="layer l3">COMFORT LINING</div>
          <div class="layer l2">THERMAL INSULATION</div>
          <div class="layer l1">OUTER SHELL</div>
        </div>
      </div>
      <div class="note" id="note">
        <b>Outer shell</b>
        <span class="dim">Lapisan luar tahan air yang menolak hujan dan angin kencang.</span>
      </div>
    </div>
  </section>

  {{-- FAQ Section --}}
  <section class="faq" id="faq">
    <h2>NEED<br>TO KNOW.</h2>
    <div>
      <details open>
        <summary>Bagaimana memilih ukuran yang tepat?</summary>
        <p>Gunakan panduan ukuran MOVR untuk mengukur badan. Potongannya dirancang athletic oversized, jadi ambil ukuran biasa untuk tampilan longgar yang nyaman.</p>
      </details>
      <details>
        <summary>Bahan apa yang dipakai pada produk MOVR?</summary>
        <p>Shell ripstop teknikal premium, insulasi termal menahan dingin, dan lapisan dalam micro-mesh yang lembut serta menyerap keringat.</p>
      </details>
      <details>
        <summary>Bagaimana cara merawat jaket teknikal ini?</summary>
        <p>Cuci dengan siklus lembut air dingin, hindari deterjen keras dan pemutih, lalu keringkan di udara terbuk tanpa pemanas langsung.</p>
      </details>
      <details>
        <summary>Apakah ada pengiriman internasional?</summary>
        <p>Ya, kami melayani pengiriman ke seluruh dunia dengan pelacakan (tracking) terintegrasi pada setiap pesanan MOVR.</p>
      </details>
      <details>
        <summary>Bisakah pesanan ditukar atau dikembalikan?</summary>
        <p>Produk yang belum dipakai dan tag masih utuh bisa dikembalikan atau ditukar dalam jangka waktu 14 hari setelah barang diterima.</p>
      </details>
    </div>
  </section>

  {{-- Footer --}}
  <footer>
    <span>MOVR ATHLETICS © 2026</span>
    <span>MOVR Performance Gear &amp; Technical Apparel. All Rights Reserved.</span>
  </footer>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
// ---------- 3D jacket builder ----------
function makeEnv(r) {
  var env = new THREE.Scene();
  [[0x2a4dff, -6, 3, 2], [0xff2d8a, 6, 0, 3], [0x7a5cff, 0, 6, -2], [0x20e0ff, -3, -4, 5], [0xffffff, 4, 5, 5], [0xffffff, -4, 2, 6]].forEach(function(p) {
    var m = new THREE.Mesh(new THREE.PlaneGeometry(6, 3), new THREE.MeshBasicMaterial({ color: p[0], side: 2 }));
    m.position.set(p[1], p[2], p[3]);
    m.lookAt(0, 0, 0);
    env.add(m);
  });
  env.add(new THREE.Mesh(new THREE.SphereGeometry(20, 16, 16), new THREE.MeshBasicMaterial({ color: 0x04050a, side: 1 })));
  return new THREE.PMREMGenerator(r).fromScene(env, 0.02).texture;
}

function jMat(c, m, r) {
  return new THREE.MeshPhysicalMaterial({
    color: c,
    metalness: m,
    roughness: r,
    clearcoat: 1,
    clearcoatRoughness: .06,
    envMapIntensity: 1.5,
    side: 2
  });
}

function baffled(rx, rz, h, n, bulge, hem) {
  var g = new THREE.CylinderGeometry(1, 1, h, 56, 90, true), p = g.attributes.position;
  for (var i = 0; i < p.count; i++) {
    var x = p.getX(i), y = p.getY(i), z = p.getZ(i), t = (y + h / 2) / h;
    var b = Math.pow(Math.abs(Math.sin(t * n * Math.PI)), .55);
    var k = (1 + bulge * b) * (1 - (1 - t) * hem);
    p.setXYZ(i, x * rx * k, y, z * rz * k);
  }
  g.computeVertexNormals();
  return g;
}

function makeJacket(mat, dark) {
  var J = new THREE.Group(), M = function(g, m, x, y, z) {
    var o = new THREE.Mesh(g, m || mat);
    o.position.set(x, y, z);
    J.add(o);
    return o;
  };
  M(baffled(1.2, .78, 2.7, 6, .13, .1), mat, 0, 0, 0);
  M(baffled(.62, .52, .7, 2, .2, 0), mat, 0, 1.6, 0);
  for (var s = -1; s <= 1; s += 2) {
    var sl = M(baffled(.44, .42, 2.4, 5, .17, .2), mat, s * 2.18, .28, .04);
    sl.rotation.z = s * .95;
  }
  M(new THREE.BoxGeometry(.05, 2.6, .03), dark, 0, 0, .92);
  M(new THREE.BoxGeometry(.55, .32, .09), dark, -.62, .1, .9);
  M(new THREE.BoxGeometry(.55, .32, .09), dark, .62, .1, .9);
  M(new THREE.BoxGeometry(.55, .24, .09), dark, -.62, -.55, .9);
  M(new THREE.BoxGeometry(.55, .24, .09), dark, .62, -.55, .9);
  return J;
}
var darkMat = jMat(0x15161c, .8, .35);

// ---------- Collections (real 3D renders) ----------
@php
  $collItems = [
    ["MOVR Shadow Puffer Jacket", "Rp 460.000", "0x63A2BB", ["OVERSIZED FIT", "LIMITED"]],
    ["MOVR Tactical Hooded Jacket", "Rp 395.000", "0xff3fa8", ["LAYERING FRIENDLY", "UTILITY"]],
    ["MOVR Thermal Bomber Jacket", "Rp 340.000", "0xff3a12", ["THERMAL INSULATION", "WATERPROOF"]],
    ["MOVR Tech Shell Jacket", "Rp 520.000", "0x1f4fff", ["WATER RESISTANT", "BREATHABLE"]]
  ];
@endphp

var items = [
  ["MOVR Shadow Puffer Jacket", "Rp 460.000", 0x63A2BB, ["OVERSIZED FIT", "LIMITED"]],
  ["MOVR Tactical Hooded Jacket", "Rp 395.000", 0xff3fa8, ["LAYERING FRIENDLY", "UTILITY"]],
  ["MOVR Thermal Bomber Jacket", "Rp 340.000", 0xff3a12, ["THERMAL INSULATION", "WATERPROOF"]],
  ["MOVR Tech Shell Jacket", "Rp 520.000", 0x1f4fff, ["WATER RESISTANT", "BREATHABLE"]]
];

var cart = 0, g = document.getElementById('grid'), cards = [];
items.forEach(function(it) {
  var d = document.createElement('div');
  d.className = 'card';
  d.innerHTML = '<div class="t"><span>' + it[0] + '</span><span>' + it[1] + '</span></div><canvas width="360" height="300" aria-hidden="true"></canvas>' +
    '<div class="tags">' + it[3].map(function(t) { return '<i>' + t + '</i>'; }).join('') + '</div>' +
    '<a class="btn" href="{{ route("product.index") }}">SHOP NOW <span>→</span></a>';
  g.appendChild(d);
  cards.push({ el: d, ctx: d.querySelector('canvas').getContext('2d'), color: it[2], hover: false, rot: Math.random() * 6, vis: false });
  d.onmouseenter = function() { cards[items.indexOf(it)].hover = true; };
  d.onmouseleave = function() { cards[items.indexOf(it)].hover = false; };
});

(function() {
  if (!window.THREE) return;
  var r = new THREE.WebGLRenderer({ antialias: true, alpha: true, preserveDrawingBuffer: true });
  r.setSize(360, 300, false);
  r.setPixelRatio(1);
  var cam = new THREE.PerspectiveCamera(30, 360 / 300, .1, 50);
  cam.position.set(0, .1, 10.5);
  var env = makeEnv(r), reduce = matchMedia('(prefers-reduced-motion:reduce)').matches;

  cards.forEach(function(c) {
    c.scene = new THREE.Scene();
    c.scene.environment = env;
    c.j = makeJacket(jMat(c.color, .25, .22), darkMat);
    c.j.scale.setScalar(.82);
    c.scene.add(c.j);
  });

  var io = new IntersectionObserver(function(es) {
    es.forEach(function(e) {
      cards.forEach(function(c) {
        if (c.el === e.target) c.vis = e.isIntersecting;
      });
    });
  });

  cards.forEach(function(c) { io.observe(c.el); });

  (function loop() {
    requestAnimationFrame(loop);
    cards.forEach(function(c) {
      if (!c.vis) return;
      c.rot += reduce ? 0 : (c.hover ? .035 : .006);
      c.j.rotation.y = c.hover ? c.rot : Math.sin(c.rot) * .5;
      r.render(c.scene, cam);
      c.ctx.clearRect(0, 0, 360, 300);
      c.ctx.drawImage(r.domElement, 0, 0);
    });
  })();
})();

// ---------- Hero 3D jacket ----------
(function() {
  var cv = document.getElementById('gl');
  if (!cv || !window.THREE) return;
  var r = new THREE.WebGLRenderer({ canvas: cv, antialias: true, alpha: true });
  r.setPixelRatio(Math.min(devicePixelRatio, 2));
  var sc = new THREE.Scene(), cam = new THREE.PerspectiveCamera(32, 1, .1, 50);
  cam.position.set(0, .2, 9);
  sc.environment = makeEnv(r);

  var J = makeJacket(jMat(0x0a0f35, .6, .14), darkMat);
  sc.add(J);

  var mx = 0, my = 0, tx = 0, ty = 0;
  addEventListener('pointermove', function(e) {
    mx = e.clientX / innerWidth - .5;
    my = e.clientY / innerHeight - .5;
  });

  var base = 1;
  function rs() {
    var w = cv.clientWidth, h = cv.clientHeight;
    r.setSize(w, h, false);
    cam.aspect = w / h;
    cam.updateProjectionMatrix();
    base = .7 + .3 * Math.min(1, w / 900);
    J.scale.setScalar(base);
  }
  addEventListener('resize', rs);
  rs();

  var reduce = matchMedia('(prefers-reduced-motion:reduce)').matches;
  (function loop(t) {
    requestAnimationFrame(loop);
    var sy = scrollY / innerHeight;
    tx += ((mx * 1.1) - tx) * .06;
    ty += ((my * .3) - ty) * .06;
    J.rotation.y = tx + (reduce ? 0 : Math.sin(t * .0004) * .3) + sy * 1.6;
    J.rotation.x = ty;
    J.position.y = -.1 - sy * .3;
    r.render(sc, cam);
  })(0);
})();

// ---------- Glitch text on hero ----------
(function() {
  var el = document.getElementById('glitch');
  if (!el || matchMedia('(prefers-reduced-motion:reduce)').matches) return;
  var o = el.textContent, ch = '#@%&01$▒';
  setInterval(function() {
    var a = o.split('');
    for (var i = 0; i < 3; i++) {
      a[Math.floor(Math.random() * a.length)] = ch[Math.floor(Math.random() * ch.length)];
    }
    el.textContent = a.join('');
    setTimeout(function() { el.textContent = o; }, 90);
  }, 2600);
})();

// ---------- Layer explode on scroll ----------
(function() {
  var L = document.querySelectorAll('.movr-3d-landing .layer');
  var sec = document.getElementById('teknologi');
  var note = document.getElementById('note');
  if (!sec || !note || !L.length) return;

  var info = [
    ["Outer shell", "Lapisan luar tahan air yang menolak hujan dan angin."],
    ["Thermal insulation", "Isian bubble menahan panas tubuh tanpa terasa berat."],
    ["Comfort lining", "Lapisan dalam lembut untuk nyaman seharian."],
    ["Base layer", "Lapisan dasar yang menahan kelembapan dari luar."]
  ];

  function up() {
    var b = sec.getBoundingClientRect();
    var p = Math.min(1, Math.max(0, -b.top / (b.height - innerHeight)));
    var gap = 20 + p * 70;
    L.forEach(function(el, i) {
      el.style.transform = 'translateZ(' + (i * gap) + 'px)';
    });
    var idx = Math.min(3, Math.floor(p * 4));
    note.innerHTML = '<b>' + info[idx][0] + '</b><span class="dim">' + info[idx][1] + '</span>';
  }
  addEventListener('scroll', up, { passive: true });
  up();
})();
</script>
@endsection
