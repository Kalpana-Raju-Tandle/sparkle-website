(() => {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const reduced = matchMedia('(prefers-reduced-motion:reduce)').matches;
  const fmt = new Intl.NumberFormat('en-IN');

  /* header + mobile menu */
  const header = $('#siteHeader'), burger = $('#burger'), menu = $('#menu');
  const onScroll = () => header.classList.toggle('is-stuck', scrollY > 10);
  onScroll(); addEventListener('scroll', onScroll, { passive: true });
  const setMenu = open => { menu.classList.toggle('open', open); burger.setAttribute('aria-expanded', open); document.body.style.overflow = open ? 'hidden' : ''; };
  burger?.addEventListener('click', () => setMenu(!menu.classList.contains('open')));
  $$('#menu a').forEach(a => a.addEventListener('click', () => setMenu(false)));

  /* scroll reveal — pages tag elements with .rv; inner pages are tagged here */
  $$('.card, .value, .plan, .steps li, .svc, .profile, .compare__col, .pcard, .mi, .faq details, .infocard, .reach li, .form, .est, .eng__team, .section__head').forEach(el => el.classList.add('rv'));
  if (reduced || !('IntersectionObserver' in window)) $$('.rv').forEach(el => el.classList.add('in'));
  else {
    const io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { threshold: .1, rootMargin: '0px 0px -30px' });
    $$('.rv').forEach((el, i) => { el.style.transitionDelay = (i % 4) * 70 + 'ms'; io.observe(el); });
  }

  /* counters */
  const co = new IntersectionObserver(es => es.forEach(e => {
    if (!e.isIntersecting) return; co.unobserve(e.target);
    const el = e.target, end = +el.dataset.count, pre = el.dataset.prefix || '', t0 = performance.now();
    const tick = t => { const p = Math.min(1, (t - t0) / 1600), v = Math.round(end * (1 - Math.pow(1 - p, 3))); el.textContent = pre + fmt.format(v) + (p === 1 && !pre && !el.dataset.plain ? '+' : ''); if (p < 1) requestAnimationFrame(tick); };
    requestAnimationFrame(tick);
  }), { threshold: .6 });
  $$('[data-count]').forEach(el => { if (reduced) el.textContent = (el.dataset.prefix || '') + fmt.format(+el.dataset.count); else co.observe(el); });

  /* hero slideshow + dots */
  const slides = $$('.hero__slide'), dots = $$('#heroDots button');
  if (slides.length > 1) {
    let i = 0, t;
    const go = n => { slides[i].classList.remove('is-on'); dots[i]?.classList.remove('is-on'); i = (n + slides.length) % slides.length; slides[i].classList.add('is-on'); dots[i]?.classList.add('is-on'); };
    const start = () => { if (!reduced) { clearInterval(t); t = setInterval(() => go(i + 1), 6500); } };
    dots.forEach(d => d.addEventListener('click', () => { go(+d.dataset.i); start(); }));
    start();
  }

  /* spotlight glow on feature tiles follows the cursor */
  $$('.afeat > div').forEach(t => t.addEventListener('pointermove', e => {
    const r = t.getBoundingClientRect(); t.style.setProperty('--mx', (e.clientX - r.left) + 'px'); t.style.setProperty('--my', (e.clientY - r.top) + 'px');
  }));

  /* services: exploded isometric stack */
  const stackEl = $('#sx');
  if (stackEl) {
    const slabs = $$('.sx__slab', stackEl), infos = $$('.sx__info', stackEl), tabs = $$('.sx__tabs button', stackEl);
    const GAP = innerWidth < 560 ? 44 : innerWidth < 900 ? 56 : 70;   // extra lift for layers above the active one
    let i = 0, t, hovering = false;
    const set = n => {
      i = (n + slabs.length) % slabs.length;
      slabs.forEach((s, k) => { s.classList.toggle('is-on', k === i); s.style.setProperty('--e', (k > i ? GAP : 0) + 'px'); });
      infos.forEach((f, k) => f.classList.toggle('is-on', k === i));
      tabs.forEach((b, k) => b.classList.toggle('is-on', k === i));
    };
    const start = () => { clearInterval(t); if (!reduced) t = setInterval(() => { if (!hovering) set(i + 1); }, 4500); };
    slabs.forEach((s, k) => { s.addEventListener('mouseenter', () => { if (k !== i) set(k); }); s.addEventListener('click', () => set(k)); });
    tabs.forEach((b, k) => b.addEventListener('click', () => { set(k); start(); }));
    stackEl.addEventListener('mouseenter', () => hovering = true);
    stackEl.addEventListener('mouseleave', () => hovering = false);
    set(0); start();
  }

  /* services page: scroll-spy for the sticky index */
  const snavLinks = $$('.snav li a');
  if (snavLinks.length) {
    const targets = snavLinks.map(a => $(a.getAttribute('href')));
    const spy = new IntersectionObserver(es => es.forEach(e => {
      if (e.isIntersecting) { const k = targets.indexOf(e.target); snavLinks.forEach((a, n) => a.classList.toggle('is-on', n === k)); }
    }), { rootMargin: '-35% 0px -55% 0px' });
    targets.forEach(t => t && spy.observe(t));
  }

  /* sustainable showcase: marker + dock tabs, auto-cycling */
  const susEl = $('#sus');
  if (susEl) {
    const hs = $$('.sus__hs', susEl), tabs = $$('.susx__tab', susEl), now = $('.susx__now', susEl);
    const noEl = $('#susNo'), tEl = $('#susTitle'), dEl = $('#susDesc');
    let i = 0, t;
    const set = n => {
      i = (n + tabs.length) % tabs.length;
      hs.forEach((h, k) => h.classList.toggle('is-on', k === i));
      tabs.forEach((b, k) => { b.classList.remove('is-on'); if (k === i) { void b.offsetWidth; b.classList.add('is-on'); } });
      noEl.textContent = '0' + (i + 1) + ' / 0' + tabs.length;
      tEl.textContent = tabs[i].dataset.t; dEl.textContent = tabs[i].dataset.d;
      now.classList.remove('is-swap'); void now.offsetWidth; now.classList.add('is-swap');
    };
    const start = () => { clearInterval(t); if (!reduced) t = setInterval(() => set(i + 1), 4600); };
    [...hs, ...tabs].forEach(el => { const k = +el.dataset.i; el.addEventListener('mouseenter', () => { clearInterval(t); susEl.classList.add('is-paused'); set(k); }); el.addEventListener('click', () => { set(k); }); el.addEventListener('focus', () => set(k)); });
    susEl.addEventListener('mouseleave', () => { susEl.classList.remove('is-paused'); set(i); start(); });
    start();
  }

  /* projects: duplicate each row once so the glide loops seamlessly; clones open the original photo */
  $$('.mq__track').forEach(tr => {
    const originals = [...tr.children];
    originals.forEach(c => {
      const d = c.cloneNode(true);
      d.setAttribute('aria-hidden', 'true'); d.setAttribute('tabindex', '-1'); d.removeAttribute('data-lb');
      d.addEventListener('click', e => { e.preventDefault(); c.click(); });
      tr.appendChild(d);
    });
  });

  /* experimental buttons: wrap the label so the text can roll on hover */
  $$('.pbtn, .sv__all').forEach(b => {
    const node = [...b.childNodes].find(n => n.nodeType === 3 && n.textContent.trim());
    if (!node) return;
    const label = node.textContent.trim(), em = document.createElement('em');
    em.className = 'pb__t'; em.setAttribute('aria-label', label);
    em.innerHTML = '<b>' + label.replace(/</g, '&lt;') + '</b><b aria-hidden="true">' + label.replace(/</g, '&lt;') + '</b>';
    node.replaceWith(em);
  });

  /* construction gallery viewer: big photo + filmstrip + stage chapter info */
  const gvEl = $('#gv');
  if (gvEl) {
    const figs = $$('.gv__img', gvEl), ths = $$('.gv__th', gvEl), chips = $$('.gv__chip', gvEl), infos = $$('.gi', gvEl), hid = $$('.gv__hid', gvEl);
    const strip = $('#gvStrip'), pill = $('#gvPill'), noEl = $('#gvNo'), capEl = $('#gvCap');
    const stageName = {}; chips.forEach(c => stageName[c.dataset.stage] = c.textContent.replace(/^\d+/, '').trim());
    let i = 0;
    const set = (n, fromStrip) => {
      i = (n + figs.length) % figs.length;
      const st = ths[i].dataset.stage;
      const nF = figs.length, narrow = innerWidth < 1000;
      figs.forEach((f, k) => {
        f.classList.toggle('is-on', k === i);
        let d = ((k - i + nF / 2) % nF + nF) % nF - nF / 2; d = Math.round(d); const a = Math.abs(d), sg = Math.sign(d);
        const x = d === 0 ? 0 : sg * ((narrow ? 60 : 54) + (a - 1) * (narrow ? 10 : 11));
        f.style.transform = `translateX(${x}%) translateZ(${-a * 70}px) rotateY(${-sg * (a ? 52 : 0)}deg) scale(${a ? 0.94 - a * 0.02 : 1})`;
        f.style.zIndex = 50 - a; f.style.opacity = a > 4 ? 0 : 1; f.style.pointerEvents = a > 4 ? 'none' : 'auto';
      });
      ths.forEach((t, k) => t.classList.toggle('is-on', k === i));
      chips.forEach(c => { c.classList.toggle('is-on', c.dataset.stage === st); c.classList.toggle('is-done', +c.dataset.stage < +st); });
      infos.forEach(g => g.classList.toggle('is-on', g.dataset.stage === st));
      pill.innerHTML = '<i>' + String(st).padStart(2, '0') + '</i>' + (stageName[st] || '');
      noEl.textContent = String(i + 1).padStart(2, '0'); capEl.textContent = ths[i].dataset.cap;
      if (!fromStrip || true) { const t = ths[i]; strip.scrollTo({ left: t.offsetLeft - strip.clientWidth / 2 + t.clientWidth / 2, behavior: 'smooth' }); }
    };
    ths.forEach(t => t.addEventListener('click', () => set(+t.dataset.i, true)));
    figs.forEach((f, k) => f.addEventListener('click', () => { if (k !== i && !drag.moved) set(k); }));
    const drag = { x: 0, on: false, moved: false }, vw = $('.gv__viewer', gvEl);
    vw.addEventListener('pointerdown', e => { drag.x = e.clientX; drag.on = true; drag.moved = false; });
    addEventListener('pointerup', e => { if (!drag.on) return; drag.on = false; const dx = e.clientX - drag.x; if (Math.abs(dx) > 50) { drag.moved = true; set(i + (dx < 0 ? 1 : -1)); setTimeout(() => drag.moved = false, 50); } });
    addEventListener('resize', () => set(i));
    chips.forEach(c => c.addEventListener('click', () => set(+c.dataset.i)));
    $('#gvPrev').addEventListener('click', () => set(i - 1)); $('#gvNext').addEventListener('click', () => set(i + 1));
    $('.gv__viewer', gvEl).addEventListener('click', e => { if (e.target.closest('.is-on') && !e.target.closest('.gv__nav') && !drag.moved) hid[i] && hid[i].click(); });
    $('#gvOpen').addEventListener('click', e => { e.stopPropagation(); hid[i] && hid[i].click(); });
    gvEl.addEventListener('keydown', e => { if (e.key === 'ArrowRight') set(i + 1); if (e.key === 'ArrowLeft') set(i - 1); });
    set(0);
  }

  /* pricing console: plan toggle + live price + plot-size estimate */
  const pzc = $('#pzc');
  if (pzc) {
    const tabs = $$('.pzc__tab', pzc), lists = $$('.pzc__feat', pzc), thumb = $('.pzc__thumb', pzc);
    const priceEl = $('#pzPrice'), tagEl = $('#pzTag'), range = $('#pzRange'), sqEl = $('#pzSq'), totEl = $('#pzTot');
    let cur = +pzc.dataset.start || 0;
    const inr = n => '₹' + Math.round(n).toLocaleString('en-IN');
    const short = n => n >= 1e7 ? '₹' + (n / 1e7).toFixed(2) + ' Cr' : '₹' + (n / 1e5).toFixed(1) + ' L';
    const calc = () => {
      const rate = +tabs[cur].dataset.rate, sq = +range.value;
      sqEl.textContent = sq.toLocaleString('en-IN') + ' sq.ft';
      totEl.textContent = short(rate * sq);
      range.style.setProperty('--p', ((sq - range.min) / (range.max - range.min) * 100) + '%');
    };
    const pick = (i, anim) => {
      cur = i;
      pzc.style.setProperty('--i', i);
      tabs.forEach((t, k) => { t.classList.toggle('is-on', k === i); t.setAttribute('aria-selected', k === i); });
      lists.forEach((l, k) => l.classList.toggle('is-on', k === i));
      priceEl.textContent = inr(+tabs[i].dataset.rate);
      tagEl.textContent = tabs[i].dataset.tag;
      if (anim) { priceEl.classList.remove('bump'); void priceEl.offsetWidth; priceEl.classList.add('bump'); }
      calc();
    };
    tabs.forEach((t, k) => t.addEventListener('click', () => pick(k, true)));
    range.addEventListener('input', calc);
    pick(cur, false);
  }

  /* services bento: footage plays on hover over matching tiles */
  $$('.bt video[data-v]').forEach(v => {
    const tile = v.closest('.bt');
    tile.addEventListener('mouseenter', () => { if (!v.getAttribute('src')) v.src = v.dataset.v; v.play().then(() => tile.classList.add('is-vid')).catch(() => {}); });
    tile.addEventListener('mouseleave', () => { tile.classList.remove('is-vid'); setTimeout(() => { if (!tile.classList.contains('is-vid')) v.pause(); }, 600); });
  });

  /* play craft videos only while visible */
  const vo = new IntersectionObserver(es => es.forEach(e => {
    const v = e.target;
    if (e.isIntersecting) { if (!v.src && v.dataset.src) v.src = v.dataset.src; v.play().catch(() => {}); } else v.pause();
  }), { threshold: .25 });
  $$('video[data-src]').forEach(v => vo.observe(v));

  /* filter chips */
  $$('.chips[data-filter]').forEach(box => {
    const grid = $(box.dataset.filter);
    box.addEventListener('click', e => {
      const b = e.target.closest('.chip'); if (!b) return;
      $$('.chip', box).forEach(c => c.classList.toggle('is-on', c === b));
      $$('[data-cat]', grid).forEach(it => it.classList.toggle('is-hidden', b.dataset.f !== '*' && it.dataset.cat !== b.dataset.f));
    });
  });

  /* lightbox */
  const lb = $('#lightbox'), lbImg = $('img', lb), lbVid = $('video', lb), lbCap = $('figcaption', lb);
  let group = [], idx = 0;
  const stopVideo = () => { lbVid.pause(); lbVid.removeAttribute('src'); lbVid.load(); lbVid.hidden = true; };
  const show = i => {
    idx = (i + group.length) % group.length; const a = group[idx], vid = a.dataset.video;
    stopVideo(); lbImg.hidden = !!vid;
    if (vid) { lbVid.poster = a.href; lbVid.src = vid; lbVid.hidden = false; lbVid.play().catch(() => {}); }
    else { lbImg.src = a.href; lbImg.alt = a.dataset.cap || ''; }
    lbCap.textContent = a.dataset.cap || '';
  };
  const close = () => { stopVideo(); lb.hidden = true; document.body.style.overflow = ''; };
  document.addEventListener('click', e => {
    const a = e.target.closest('[data-lb]');
    if (a) {
      e.preventDefault();
      group = $$(`[data-lb="${a.dataset.lb}"]`).filter(x => !x.classList.contains('is-hidden'));
      lb.hidden = false; document.body.style.overflow = 'hidden'; show(group.indexOf(a)); return;
    }
    if (e.target.closest('.lb__close') || e.target === lb) close();
    if (e.target.closest('.lb__prev')) show(idx - 1);
    if (e.target.closest('.lb__next')) show(idx + 1);
  });
  addEventListener('keydown', e => { if (lb.hidden) return; if (e.key === 'Escape') close(); if (e.key === 'ArrowLeft') show(idx - 1); if (e.key === 'ArrowRight') show(idx + 1); });
  let sx = 0; lb.addEventListener('touchstart', e => sx = e.touches[0].clientX, { passive: true });
  lb.addEventListener('touchend', e => { const d = e.changedTouches[0].clientX - sx; if (Math.abs(d) > 50) show(idx + (d < 0 ? 1 : -1)); });

  /* estimator */
  const est = $('.est');
  if (est) {
    const rates = JSON.parse(est.dataset.rates);
    const area = $('#area'), out = $('#areaOut'), total = $('#total'), detail = $('#detail'), msg = $('#estMsg');
    let floors = 1, pkg = 'classic';
    const calc = () => {
      const a = +area.value, rate = rates[pkg].r, sum = a * floors * rate;
      out.textContent = fmt.format(a) + ' sq.ft per floor';
      total.textContent = '₹' + fmt.format(sum);
      const d = `${fmt.format(a * floors)} sq.ft × ₹${fmt.format(rate)}/sq.ft (${rates[pkg].n})`;
      detail.textContent = d + ' — indicative only';
      msg.value = `Estimator: ${d} = ₹${fmt.format(sum)} (${floors === 1 ? 'Ground floor' : 'G+' + (floors - 1)})`;
    };
    const seg = (id, cb) => { const box = $(id); box.addEventListener('click', e => { const b = e.target.closest('button'); if (!b) return; $$('button', box).forEach(x => x.classList.toggle('is-on', x === b)); cb(b.dataset.v); calc(); }); };
    seg('#floors', v => floors = +v); seg('#pkg', v => pkg = v);
    area.addEventListener('input', calc);
    $$('[data-pick]').forEach(b => b.addEventListener('click', () => { pkg = b.dataset.pick; $$('#pkg button').forEach(x => x.classList.toggle('is-on', x.dataset.v === pkg)); calc(); }));
    calc();
  }
})();
