document.addEventListener('DOMContentLoaded', () => {
  // Confirm sensitive actions
  document.querySelectorAll('form[data-confirm]').forEach(f =>
    f.addEventListener('submit', e => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));

  // Auto-dismiss success alerts
  document.querySelectorAll('.alert.success').forEach(a =>
    setTimeout(() => { a.style.transition = 'opacity .5s'; a.style.opacity = 0; setTimeout(() => a.remove(), 500); }, 4000));

  // Keep amount inputs numeric with max 2 decimals
  document.querySelectorAll('input[name="amount"]').forEach(i =>
    i.addEventListener('input', () => {
      i.value = i.value.replace(/[^\d.]/g, '').replace(/^(\d*\.\d*)\./, '$1').replace(/(\.\d{2}).*/, '$1');
    }));
});
