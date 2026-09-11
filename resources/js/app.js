import { createIcons, icons } from 'lucide';
import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

// Make Alpine available globally for x-data inline directives
window.Alpine = Alpine;

// Register plugins before start
Alpine.plugin(intersect);

// Start Alpine — wraps the initial paint so x-cloak stays hidden until ready
Alpine.start();

// ─── Lucide icons ───────────────────────────────────────────────────
// Initialize after DOM ready; safe to call after Alpine because Alpine
// hydrates synchronously on DOMContentLoaded by default.
function initLucide() {
    try {
        createIcons({ icons });
    } catch (e) {
        // Silently ignore — icons are non-critical
    }
}

document.addEventListener('DOMContentLoaded', initLucide);

// Re-initialize after partial navigation / dynamic inserts
window.reinitLucideIcons = initLucide;



// ─── Form auto-loading helper ──────────────────────────────────────
// Submit any form with [data-loading-form] to add loading state to its
// submit button(s). Prevents double-submit and gives visual feedback.
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (!form.hasAttribute('data-loading-form')) return;

    // Don't interfere with forms that opt out
    if (form.hasAttribute('data-loading-form="off"')) return;

    const submitButtons = form.querySelectorAll(
        'button[type="submit"], input[type="submit"], button:not[type]'
    );
    submitButtons.forEach((btn) => {
        btn.disabled = true;
        btn.classList.add('btn-loading');
    });
}, true);

// ─── Native date inputs: click-anywhere-to-open ─────────────────────
// Clicking a facade date input only focuses it on desktop Chrome — the
// calendar popup does not open by itself. Open it explicitly via
// showPicker() for any click inside a [data-date-wrap] container.
// Safe when the native indicator was the click target: the duplicate
// call throws InvalidStateError and is ignored.
document.addEventListener('click', (event) => {
    const wrap = event.target instanceof Element
        ? event.target.closest('[data-date-wrap]')
        : null;
    if (!wrap) return;
    const input = wrap.querySelector('input[type="date"]');
    if (!input) return;
    try {
        input.showPicker();
    } catch (e) {
        try {
            input.focus({ preventScroll: true });
        } catch (err) {
            // Non-critical
        }
    }
});

// Expose for inline Alpine usage if needed
window.appLoading = {
    on(formEl) {
        formEl?.querySelectorAll('button[type="submit"], button:not([type])')
            .forEach((btn) => {
                btn.disabled = true;
                btn.classList.add('btn-loading');
            });
    },
};
