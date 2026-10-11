/* Sparkle — motion layer (vanilla, no libraries). Skipped for reduced-motion users. */
(() => {
  if (matchMedia('(prefers-reduced-motion:reduce)').matches) return;
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const fine = matchMedia('(hover:hover) and (pointer:fine)').matches;

  /* ---------- 1. word-by-word heading reveal ---------- */
  const unit = n => n.nodeType === 1 && /^(EM|MARK)$/.test(n.tagName);
  function split(el) {
    let k = 0;
    const word = t => { const w = document.createElement('span'), i = document.createElement('span'); w.className = 'w'; w.style.setProperty('--wi', k++); i.textContent = t; w.append(i); return w; };
    const walk = n => [...n.childNodes].forEach(c => {
      if (c.nodeType === 3) {
        const f = document.createDocumentFragment();
        c.textContent.split(/(\s+)/).forEach(t => { if (!t) return; if (/^\s+$/.test(t)) f.append(' '); else f.append(word(t)); });
        c.replaceWith(f);
      } else if (unit(c)) {
        const w = document.createElement('span'), i = document.createElement('span');
        w.className = 'w'; w.style.setProperty('--wi', k++);
        c.replaceWith(w); i.append(c); w.append(i);
      } else if (c.nodeType === 1 && c.tagName !== 'BR') walk(c);
    });
    el.setAttribute('aria-label', el.textContent.trim().replace(/\s+/g, ' '));
    walk(el);
    $$('.w', el).forEach(w => w.setAttribute('aria-hidden', 'true'));
    el.classList.add('fx-split');
  }
  const heads = $$('.hero h1, .phero h1, .sus__head h2, .sv__head h2, .stintro h2, .proc__head h2, .faqs__side h2, .cta-band h2, .section__head h2');
  const hio = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); hio.unobserve(e.target); } }), { threshold: .25 });
  heads.forEach(h => { split(h); hio.observe(h); });
  // safety net: reveal anything already on screen even if the observer is slow or throttled
  const reveal = () => heads.forEach(h => { const r = h.getBoundingClientRect(); if (r.top < innerHeight * .92 && r.bottom > 0) h.classList.add('in'); });
  setTimeout(reveal, 350); setTimeout(reveal, 1400); addEventListener('scroll', reveal, { passive: true });

  /* ---------- 2. scroll progress line ---------- */
  const bar = document.createElement('div'); bar.className = 'fx-progress'; document.body.append(bar);

  /* ---------- 3. parallax on the sustainable photo ---------- */
  const par = $$('.susx__img > img');
  let tick = false;
  const update = () => {
    const max = document.documentElement.scrollHeight - innerHeight;
    bar.style.transform = `scaleX(${max > 0 ? Math.min(1, scrollY / max) : 0})`;
    par.forEach(im => {
      const r = im.parentElement.getBoundingClientRect();
      if (r.bottom < -100 || r.top > innerHeight + 100) return;
      im.style.setProperty('--py', ((r.top + r.height / 2 - innerHeight / 2) / innerHeight * -46).toFixed(1) + 'px');
    });
    tick = false;
  };
  addEventListener('scroll', () => { if (!tick) { tick = true; requestAnimationFrame(update); } }, { passive: true });
  addEventListener('resize', update); update();

  if (!fine) return;

  /* ---------- 4. magnetic buttons ---------- */
  $$('.btn--lg, .pillbtn, .gbtn, .pbtn, .sv__all, .snav__cta').forEach(b => {
    b.addEventListener('pointermove', e => {
      const r = b.getBoundingClientRect();
      b.style.translate = `${((e.clientX - r.left) / r.width - .5) * 14}px ${((e.clientY - r.top) / r.height - .5) * 10}px`;
    });
    b.addEventListener('pointerleave', () => { b.style.translate = ''; });
  });

  /* ---------- 5. 3D tilt on cards ---------- */
  $$('.bt, .mcard, .plan, .miniplan, .pj').forEach(c => {
    c.addEventListener('pointermove', e => {
      const r = c.getBoundingClientRect();
      c.style.setProperty('--ry', (((e.clientX - r.left) / r.width - .5) * 7).toFixed(2) + 'deg');
      c.style.setProperty('--rx', (((e.clientY - r.top) / r.height - .5) * -7).toFixed(2) + 'deg');
    });
    c.addEventListener('pointerleave', () => { c.style.setProperty('--rx', '0deg'); c.style.setProperty('--ry', '0deg'); });
  });
})();
