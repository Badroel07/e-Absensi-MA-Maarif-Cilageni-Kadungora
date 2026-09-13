<!-- Global Confirmation & Alert Modal (Ma'arif Design System — Minimalist Confirmation Dialogs) -->
<style>
    #confirmModalMessage strong {
        font-weight: 500;
        color: #1e293b;
    }
</style>
<div id="globalConfirmModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6 transition-all duration-200 opacity-0 pointer-events-none select-none" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
    <!-- Darkened Backdrop (tanpa blur agar ringan) -->
    <div id="confirmModalBackdrop" class="fixed inset-0 bg-slate-950/60 transition-opacity duration-200"></div>

    <!-- Modal Card Box -->
    <div id="confirmModalCard" class="relative w-full max-w-md bg-white rounded-2xl p-7 sm:p-8 border border-slate-100 shadow-[0_20px_25px_-5px_rgba(15,23,42,0.08),0_8px_10px_-6px_rgba(15,23,42,0.04)] flex flex-col transition-all duration-200 transform scale-95 opacity-0 z-10">

        <!-- Header Row: Icon Badge & Close Button -->
        <div id="confirmModalHeaderRow" class="flex items-start justify-between mb-5">
            <div id="confirmModalIconWrapper" class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors">
                <i id="confirmModalIcon" data-lucide="help-circle" class="w-5 h-5"></i>
            </div>
            <button type="button" id="confirmModalCloseX" class="text-slate-400 hover:text-slate-600 transition-colors p-1.5 rounded-lg hover:bg-slate-50 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400" aria-label="Tutup modal">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Text Content -->
        <div id="confirmModalBody" class="mb-6">
            <span id="confirmModalEyebrow" class="text-[11px] font-semibold tracking-wider heading-font uppercase mb-1.5 inline-block">Konfirmasi</span>
            <h3 id="confirmModalTitle" class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight heading-font">
                Konfirmasi Tindakan
            </h3>
            <div id="confirmModalMessage" class="text-slate-500 text-sm leading-relaxed mt-2.5 font-sans">
                Apakah Anda yakin ingin melanjutkan tindakan ini?
            </div>
        </div>

        <!-- Action Buttons Footer -->
        <div id="confirmModalFooter" class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
            <button type="button" id="confirmModalCancelBtn" class="px-4 py-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium heading-font text-sm transition-colors text-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                Batal
            </button>
            <button type="button" id="confirmModalSubmitBtn" class="px-5 py-2 rounded-xl text-white font-medium heading-font text-sm transition-colors inline-flex items-center justify-center gap-2 text-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-slate-400">
                <span id="confirmModalSubmitText">Lanjutkan</span>
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('globalConfirmModal');
    const backdrop = document.getElementById('confirmModalBackdrop');
    const card = document.getElementById('confirmModalCard');
    const headerRow = document.getElementById('confirmModalHeaderRow');
    const bodyEl = document.getElementById('confirmModalBody');
    const eyebrowEl = document.getElementById('confirmModalEyebrow');
    const titleEl = document.getElementById('confirmModalTitle');
    const messageEl = document.getElementById('confirmModalMessage');
    const footerEl = document.getElementById('confirmModalFooter');
    const iconWrapper = document.getElementById('confirmModalIconWrapper');
    const cancelBtn = document.getElementById('confirmModalCancelBtn');
    const submitBtn = document.getElementById('confirmModalSubmitBtn');
    const submitText = document.getElementById('confirmModalSubmitText');
    const closeXBtn = document.getElementById('confirmModalCloseX');

    let currentResolver = null;

    const ICON_WRAPPER_BASE = 'w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors';
    const EYEBROW_BASE = 'text-[11px] font-semibold tracking-wider heading-font uppercase mb-1.5 inline-block';
    const CANCEL_BASE = 'px-4 py-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium heading-font text-sm transition-colors text-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400';
    const SUBMIT_BASE = 'px-5 py-2 rounded-xl text-white font-medium heading-font text-sm transition-colors inline-flex items-center justify-center gap-2 text-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-slate-400';

    const TYPE_CONFIG = {
        danger: {
            wrapperClass: 'bg-rose-50 text-rose-600',
            eyebrowText: 'Tindakan Permanen',
            eyebrowClass: 'text-rose-600',
            submitClass: 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 focus-visible:ring-rose-500',
            defaultIcon: 'trash-2',
            defaultConfirm: 'Ya, Hapus'
        },
        delete: {
            wrapperClass: 'bg-rose-50 text-rose-600',
            eyebrowText: 'Tindakan Permanen',
            eyebrowClass: 'text-rose-600',
            submitClass: 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 focus-visible:ring-rose-500',
            defaultIcon: 'trash-2',
            defaultConfirm: 'Ya, Hapus'
        },
        warning: {
            wrapperClass: 'bg-amber-50 text-amber-600',
            eyebrowText: 'Perhatian Khusus',
            eyebrowClass: 'text-amber-700',
            submitClass: 'bg-amber-600 hover:bg-amber-700 active:bg-amber-800 focus-visible:ring-amber-500',
            defaultIcon: 'alert-triangle',
            defaultConfirm: 'Ya, Lanjutkan'
        },
        primary: {
            wrapperClass: 'bg-emerald-50 text-emerald-700',
            eyebrowText: 'Alur Final Presensi',
            eyebrowClass: 'text-emerald-700',
            submitClass: 'bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 focus-visible:ring-emerald-600',
            defaultIcon: 'save',
            defaultConfirm: 'Ya, Simpan'
        },
        save: {
            wrapperClass: 'bg-emerald-50 text-emerald-700',
            eyebrowText: 'Alur Final Presensi',
            eyebrowClass: 'text-emerald-700',
            submitClass: 'bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 focus-visible:ring-emerald-600',
            defaultIcon: 'save',
            defaultConfirm: 'Ya, Simpan'
        },
        lock: {
            wrapperClass: 'bg-emerald-50 text-emerald-700',
            eyebrowText: 'Alur Final Presensi',
            eyebrowClass: 'text-emerald-700',
            submitClass: 'bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 focus-visible:ring-emerald-600',
            defaultIcon: 'lock',
            defaultConfirm: 'Ya, Simpan & Kunci'
        },
        logout: {
            wrapperClass: 'bg-rose-50 text-rose-600',
            eyebrowText: 'Sesi Akun',
            eyebrowClass: 'text-rose-600',
            submitClass: 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 focus-visible:ring-rose-500',
            defaultIcon: 'log-out',
            defaultConfirm: 'Ya, Keluar'
        },
        key: {
            wrapperClass: 'bg-amber-50 text-amber-600',
            eyebrowText: 'Perhatian Khusus',
            eyebrowClass: 'text-amber-700',
            submitClass: 'bg-amber-600 hover:bg-amber-700 active:bg-amber-800 focus-visible:ring-amber-500',
            defaultIcon: 'key-round',
            defaultConfirm: 'Ya, Reset Sandi'
        },
        success: {
            wrapperClass: 'bg-emerald-50 text-emerald-600',
            eyebrowText: 'Sinkronisasi Selesai',
            eyebrowClass: 'text-emerald-700',
            submitClass: 'bg-slate-900 hover:bg-slate-800 active:bg-slate-950 focus-visible:ring-slate-500',
            defaultIcon: 'check-circle-2',
            defaultConfirm: 'Ya, Lanjutkan',
            centered: true
        },
        info: {
            wrapperClass: 'bg-sky-50 text-sky-600',
            eyebrowText: 'Petunjuk Teknis',
            eyebrowClass: 'text-sky-700',
            submitClass: 'bg-slate-900 hover:bg-slate-800 active:bg-slate-950 focus-visible:ring-slate-500',
            defaultIcon: 'info',
            defaultConfirm: 'Saya Mengerti'
        }
    };

    function setModalIcon(iconName, sizeClass) {
        if (!iconWrapper) return;
        iconWrapper.innerHTML = `<i data-lucide="${iconName}" class="${sizeClass}"></i>`;
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons({
                root: iconWrapper
            });
        } else if (typeof window.reinitLucideIcons === 'function') {
            // Vite build (app layout): lucide lives in the module bundle, not
            // on window. app.js exposes createIcons as reinitLucideIcons.
            window.reinitLucideIcons();
        }
    }

    function resetStyles() {
        headerRow.classList.remove('justify-center');
        closeXBtn.classList.remove('hidden');
        iconWrapper.className = ICON_WRAPPER_BASE;
        bodyEl.classList.remove('text-center', 'pt-2');
        footerEl.classList.remove('justify-center');
        eyebrowEl.className = EYEBROW_BASE;
        submitBtn.className = SUBMIT_BASE;
        cancelBtn.className = CANCEL_BASE;
        cancelBtn.classList.remove('hidden');
    }

    function applyLayout(config) {
        if (!config.centered) {
            return;
        }
        headerRow.classList.add('justify-center');
        closeXBtn.classList.add('hidden');
        iconWrapper.classList.add('w-12', 'h-12', 'rounded-full', 'mx-auto', 'mb-4');
        bodyEl.classList.add('text-center', 'pt-2');
        footerEl.classList.add('justify-center');
        submitBtn.classList.add('w-full');
    }

    function openModal() {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        card.classList.remove('scale-95', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
        document.body.classList.add('overflow-hidden');

        if (typeof window.triggerHaptic === 'function') {
            window.triggerHaptic([25]);
        }

        // Default focus on cancel button for safety
        setTimeout(() => {
            if (!cancelBtn.classList.contains('hidden')) {
                cancelBtn.focus();
            } else {
                submitBtn.focus();
            }
        }, 50);
    }

    function closeModal(result = false) {
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        document.body.classList.remove('overflow-hidden');

        if (currentResolver) {
            currentResolver(result);
            currentResolver = null;
        }
    }

    // Expose programmatic API
    window.confirmAction = function(options = {}) {
        return new Promise((resolve) => {
            currentResolver = resolve;
            resetStyles();

            const type = (options.type && TYPE_CONFIG[options.type]) ? options.type : 'primary';
            const config = TYPE_CONFIG[type];

            // Icon wrapper & button styles
            iconWrapper.className += ' ' + config.wrapperClass;
            eyebrowEl.className += ' ' + config.eyebrowClass;
            eyebrowEl.textContent = config.eyebrowText;
            submitBtn.className += ' ' + config.submitClass;
            applyLayout(config);

            // Render fresh Lucide icon
            const iconName = options.icon || config.defaultIcon;
            setModalIcon(iconName, config.centered ? 'w-6 h-6' : 'w-5 h-5');

            // Title and message
            titleEl.textContent = options.title || 'Konfirmasi Tindakan';
            messageEl.innerHTML = options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';

            // Button texts
            submitText.textContent = options.confirmText || config.defaultConfirm;
            cancelBtn.textContent = options.cancelText || 'Batal';

            openModal();
        });
    };

    window.showAlertDialog = function(options = {}) {
        return new Promise((resolve) => {
            currentResolver = resolve;
            resetStyles();

            const type = (options.type && TYPE_CONFIG[options.type]) ? options.type : 'info';
            const config = TYPE_CONFIG[type];

            iconWrapper.className += ' ' + config.wrapperClass;
            eyebrowEl.className += ' ' + config.eyebrowClass;
            eyebrowEl.textContent = config.eyebrowText;
            submitBtn.className += ' ' + config.submitClass;
            applyLayout(config);

            const iconName = options.icon || config.defaultIcon;
            setModalIcon(iconName, config.centered ? 'w-6 h-6' : 'w-5 h-5');

            titleEl.textContent = options.title || 'Pemberitahuan';
            messageEl.innerHTML = options.message || '';

            submitText.textContent = options.confirmText || 'Mengerti';
            cancelBtn.classList.add('hidden');

            openModal();
        });
    };

    // Event listeners for close
    cancelBtn.addEventListener('click', () => closeModal(false));
    closeXBtn.addEventListener('click', () => closeModal(false));
    backdrop.addEventListener('click', () => closeModal(false));
    submitBtn.addEventListener('click', () => closeModal(true));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('pointer-events-none')) {
            closeModal(false);
        }
    });

    // Helper to determine semantic type and icon if not explicitly provided
    function resolveActionConfig(source) {
        const customType = source.getAttribute('data-confirm-type');
        const customIcon = source.getAttribute('data-confirm-icon');
        const btnText = source.getAttribute('data-confirm-btn') || '';
        const title = source.getAttribute('data-confirm-title') || '';
        const message = source.getAttribute('data-confirm') || source.getAttribute('data-confirm-message') || '';

        let type = customType;
        let icon = customIcon;

        if (!type) {
            const lowerCombined = (btnText + ' ' + title + ' ' + message).toLowerCase();
            if (lowerCombined.includes('hapus') || lowerCombined.includes('delete')) {
                type = 'danger';
            } else if (lowerCombined.includes('keluar') || lowerCombined.includes('logout')) {
                type = 'logout';
            } else if (lowerCombined.includes('reset') && lowerCombined.includes('sandi')) {
                type = 'key';
            } else if (lowerCombined.includes('kunci') || lowerCombined.includes('lock')) {
                type = 'lock';
            } else if (lowerCombined.includes('simpan') || lowerCombined.includes('save') || lowerCombined.includes('koreksi')) {
                type = 'primary';
            } else {
                type = 'primary';
            }
        }

        return {
            title: title || 'Konfirmasi Tindakan',
            message: message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
            type: type,
            confirmText: btnText || undefined,
            cancelText: source.getAttribute('data-confirm-cancel') || 'Batal',
            icon: icon || undefined
        };
    }

    // Global interceptor for forms with data-confirm
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || !(form instanceof HTMLFormElement)) return;

        if (form.dataset.confirmed === 'true') {
            delete form.dataset.confirmed;
            return;
        }

        const submitter = e.submitter;
        const source = (submitter && submitter.hasAttribute('data-confirm'))
            ? submitter
            : (form.hasAttribute('data-confirm') ? form : null);

        if (!source) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        const config = resolveActionConfig(source);

        window.confirmAction(config).then(confirmed => {
            if (confirmed) {
                form.dataset.confirmed = 'true';
                if (submitter && submitter.name) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = submitter.name;
                    hidden.value = submitter.value;
                    form.appendChild(hidden);
                }
                if (window.MaarifSPA && typeof window.MaarifSPA.submitForm === 'function') {
                    window.MaarifSPA.submitForm(form, submitter);
                } else if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        });
    }, true);

    // Click interceptor for standalone buttons or links with data-confirm
    document.addEventListener('click', function(e) {
        const target = e.target.closest('[data-confirm]:not(form)');
        if (!target) return;
        if (target.tagName.toLowerCase() === 'button' && target.type === 'submit') return;

        e.preventDefault();
        const config = resolveActionConfig(target);

        window.confirmAction(config).then(confirmed => {
            if (confirmed) {
                if (target.tagName.toLowerCase() === 'a' && target.href) {
                    if (window.MaarifSPA && typeof window.MaarifSPA.navigate === 'function') {
                        window.MaarifSPA.navigate(target.href);
                    } else {
                        window.location.href = target.href;
                    }
                } else if (target.hasAttribute('data-action-url')) {
                    const actionUrl = target.getAttribute('data-action-url');
                    if (window.MaarifSPA && typeof window.MaarifSPA.navigate === 'function') {
                        window.MaarifSPA.navigate(actionUrl);
                    } else {
                        window.location.href = actionUrl;
                    }
                }
            }
        });
    });
})();
</script>
