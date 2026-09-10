<!-- Global Floating Toast Notification Container (Ma'arif Design System) -->
<div id="toastNotificationContainer" class="fixed top-4 right-4 sm:top-5 sm:right-5 z-[9998] flex flex-col gap-2.5 max-w-[calc(100vw-2rem)] sm:max-w-md w-full pointer-events-none" aria-live="polite"></div>

<script>
(function() {
    const container = document.getElementById('toastNotificationContainer');

    const TOAST_TYPES = {
        success: {
            icon: 'check-circle-2',
            iconBadge: 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            progressBar: 'bg-emerald-400',
            title: 'Berhasil'
        },
        error: {
            icon: 'alert-circle',
            iconBadge: 'bg-rose-500/20 text-rose-400 border-rose-500/30',
            progressBar: 'bg-rose-500',
            title: 'Gagal'
        },
        warning: {
            icon: 'alert-triangle',
            iconBadge: 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            progressBar: 'bg-amber-400',
            title: 'Peringatan'
        },
        info: {
            icon: 'info',
            iconBadge: 'bg-sky-500/20 text-sky-400 border-sky-500/30',
            progressBar: 'bg-sky-400',
            title: 'Informasi'
        }
    };

    window.showToast = function(message, type = 'success', duration = 2000) {
        if (!container || !message) return;

        const config = TOAST_TYPES[type] || TOAST_TYPES.success;
        const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5);

        // Toast element creation
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = 'relative pointer-events-auto overflow-hidden bg-slate-900/95 backdrop-blur-md text-white border border-slate-700/80 rounded-2xl p-3.5 sm:p-4 shadow-2xl flex items-start gap-3 transition-all duration-300 transform -translate-y-2 opacity-0 scale-95 select-none';

        toast.innerHTML = `
            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 border shadow-xs ${config.iconBadge}">
                <i data-lucide="${config.icon}" class="w-4 h-4"></i>
            </div>
            <div class="flex-1 min-w-0 pr-1">
                <p class="text-xs sm:text-[13px] font-bold text-slate-100 leading-snug break-words">${message}</p>
            </div>
            <button type="button" class="toast-close-btn p-1 -mr-1 -mt-1 text-slate-400 hover:text-white rounded-lg hover:bg-white/10 transition cursor-pointer" aria-label="Tutup notifikasi">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
            <div class="toast-progress-bar absolute bottom-0 left-0 h-[2.5px] ${config.progressBar} rounded-full" style="width: 100%; transition: width ${duration}ms linear;"></div>
        `;

        container.appendChild(toast);

        // Render Lucide Icons inside the toast
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons({ root: toast });
        }

        // Haptic feedback if supported
        if (typeof window.triggerHaptic === 'function') {
            window.triggerHaptic([15]);
        }

        // Trigger entrance animation
        requestAnimationFrame(() => {
            toast.classList.remove('-translate-y-2', 'opacity-0', 'scale-95');
            toast.classList.add('translate-y-0', 'opacity-100', 'scale-100');

            // Trigger progress bar countdown
            const bar = toast.querySelector('.toast-progress-bar');
            if (bar) {
                requestAnimationFrame(() => {
                    bar.style.width = '0%';
                });
            }
        });

        let dismissTimeout = null;

        const removeToast = () => {
            if (dismissTimeout) clearTimeout(dismissTimeout);
            toast.classList.remove('translate-y-0', 'opacity-100', 'scale-100');
            toast.classList.add('-translate-y-2', 'opacity-0', 'scale-90');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        };

        // Close button click listener
        const closeBtn = toast.querySelector('.toast-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', removeToast);
        }

        // Auto dismiss after specified duration (default 2000ms)
        dismissTimeout = setTimeout(removeToast, duration);
    };

    // Global helper object
    window.toast = {
        success: (msg, duration = 2000) => window.showToast(msg, 'success', duration),
        error: (msg, duration = 2500) => window.showToast(msg, 'error', duration),
        warning: (msg, duration = 2500) => window.showToast(msg, 'warning', duration),
        info: (msg, duration = 2000) => window.showToast(msg, 'info', duration)
    };

    // Auto-trigger for Laravel Session Flash messages on initial real page load
    let hasShownInitialFlash = false;
    function triggerInitialFlash() {
        if (hasShownInitialFlash) return;
        hasShownInitialFlash = true;

        @if(session('success'))
            window.showToast(@json(session('success')), 'success', 2000);
        @endif
        @if(session('error'))
            window.showToast(@json(session('error')), 'error', 2500);
        @elseif($errors->any())
            window.showToast(@json($errors->first()), 'error', 2500);
        @endif
        @if(session('warning'))
            window.showToast(@json(session('warning')), 'warning', 2500);
        @endif
        @if(session('info'))
            window.showToast(@json(session('info')), 'info', 2000);
        @endif
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', triggerInitialFlash, { once: true });
    } else {
        triggerInitialFlash();
    }
})();
</script>
