/* Construction-journey slider: slides in build order, autoplays, synced with stage rail. */
(() => {
  const track = document.getElementById('track');
  if (!track) return;
  const slides = [...track.children], rail = [...document.querySelectorAll('#rail button')];
  const bar = document.getElementById('bar'), cur = document.getElementById('cur');
  const DELAY = 3800;
  let i = 0, timer = null, paused = false, t0 = 0, raf = 0, programmatic = false;

  const stageOf = n => +slides[n].dataset.stage;
  const paint = () => {
    slides.forEach((s, n) => s.classList.toggle('is-on', n === i));
    const st = stageOf(i);
    rail.forEach(b => { const s = +b.dataset.stage; b.classList.toggle('is-on', s === st); b.classList.toggle('is-done', s < st); });
    cur.textContent = i + 1;
  };
  const go = (n, smooth = true) => {
    i = (n + slides.length) % slides.length;
    const s = slides[i];
    programmatic = true;
    track.scrollTo({ left: s.offsetLeft - (track.clientWidth - s.clientWidth) / 2, behavior: smooth ? 'smooth' : 'auto' });
    setTimeout(() => programmatic = false, 700);
    paint(); restart();
  };
  const loop = t => {
    if (!paused) { const p = Math.min(1, (t - t0) / DELAY); bar.style.width = p * 100 + '%'; if (p === 1) { go(i + 1); return; } }
    else t0 = t - (parseFloat(bar.style.width) || 0) / 100 * DELAY;
    raf = requestAnimationFrame(loop);
  };
  const restart = () => { cancelAnimationFrame(raf); bar.style.width = '0'; t0 = performance.now(); raf = requestAnimationFrame(loop); };

  document.getElementById('prev').onclick = () => go(i - 1);
  document.getElementById('next').onclick = () => go(i + 1);
  rail.forEach(b => b.onclick = () => { const n = slides.findIndex(s => +s.dataset.stage === +b.dataset.stage); if (n > -1) go(n); });
  slides.forEach((s, n) => s.addEventListener('click', e => { if (n !== i) { e.preventDefault(); e.stopPropagation(); go(n); } }, true));
  const box = document.getElementById('slider');
  ['mouseenter', 'focusin', 'touchstart'].forEach(ev => box.addEventListener(ev, () => paused = true, { passive: true }));
  ['mouseleave', 'focusout'].forEach(ev => box.addEventListener(ev, () => paused = false));
  box.addEventListener('touchend', () => setTimeout(() => paused = false, 4000));

  // user swipes → follow the nearest slide
  let st; track.addEventListener('scroll', () => {
    if (programmatic) return; clearTimeout(st);
    st = setTimeout(() => {
      const mid = track.scrollLeft + track.clientWidth / 2;
      const n = slides.reduce((b, s, k) => Math.abs(s.offsetLeft + s.clientWidth / 2 - mid) < Math.abs(slides[b].offsetLeft + slides[b].clientWidth / 2 - mid) ? k : b, 0);
      if (n !== i) { i = n; paint(); }
    }, 120);
  }, { passive: true });

  // only run while on screen
  new IntersectionObserver(es => es.forEach(e => paused = !e.isIntersecting), { threshold: .3 }).observe(box);
  matchMedia('(prefers-reduced-motion:reduce)').matches ? (paused = true, paint()) : (go(0, false));
})();
