/* Sparkle admin — small shared helpers */
(() => {
  // Eye button beside password boxes: show / hide what is typed.
  document.addEventListener('click', e => {
    const b = e.target.closest('.pweye');
    if (!b) return;
    const input = b.parentElement.querySelector('input');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    b.classList.toggle('is-on', show);
    b.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    b.title = b.getAttribute('aria-label');
    input.focus({ preventScroll: true });
  });

  // Settings → Change password: live length and "passwords match" hints.
  const nw = document.getElementById('pwNew'), cf = document.getElementById('pwConfirm');
  if (!nw || !cf) return;
  const len = document.getElementById('pwLen'), match = document.getElementById('pwMatch');
  const hint = (el, ok, text) => { el.textContent = text; el.className = 'pwhint' + (text ? (ok ? ' is-ok' : ' is-bad') : ''); };
  const check = () => {
    const n = nw.value.length;
    hint(len, n >= 10, n ? (n >= 10 ? '✓ Long enough' : `${10 - n} more character${10 - n === 1 ? '' : 's'} needed`) : '');
    hint(match, cf.value === nw.value, cf.value ? (cf.value === nw.value ? '✓ Passwords match' : '✕ Does not match the new password yet') : '');
  };
  nw.addEventListener('input', check); cf.addEventListener('input', check);
})();
