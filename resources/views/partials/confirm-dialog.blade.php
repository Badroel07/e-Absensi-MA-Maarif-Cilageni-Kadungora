<!-- Global Confirmation & Alert Modal (Ma'arif Design System) -->
<div id="globalConfirmModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6 transition-all duration-200 opacity-0 pointer-events-none select-none" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
    <!-- Darkened Blurred Backdrop -->
    <div id="confirmModalBackdrop" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-200"></div>

    <!-- Modal Card Box -->
    <div id="confirmModalCard" class="relative w-full max-w-sm sm:max-w-md bg-white rounded-3xl p-6 sm:p-7 shadow-2xl border border-slate-100 flex flex-col space-y-4 sm:space-y-5 transition-all duration-200 transform scale-95 opacity-0 z-10">
        
        <!-- Header Row: Icon Badge & Close Button -->
        <div class="flex items-start justify-between">
            <div id="confirmModalIconWrapper" class="w-12 h-12 rounded-2xl flex items-center justify-center border shadow-inner transition-colors">
                <i id="confirmModalIcon" data-lucide="help-circle" class="w-6 h-6"></i>
            </div>
            <button type="button" id="confirmModalCloseX" class="p-2 -mr-1 -mt-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-150 active:scale-95 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400" aria-label="Tutup modal">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Text Content -->
        <div class="space-y-1.5">
            <h3 id="confirmModalTitle" class="text-base sm:text-lg font-semibold text-slate-900 heading-font tracking-tight">
                Konfirmasi Tindakan
            </h3>
            <div id="confirmModalMessage" class="text-xs sm:text-sm text-slate-600 leading-relaxed font-sans">
                Apakah Anda yakin ingin melanjutkan tindakan ini?
            </div>
        </div>

        <!-- Action Buttons Footer -->
        <div class="pt-2 flex items-center gap-2.5 sm:gap-3">
            <button type="button" id="confirmModalCancelBtn" class="flex-1 py-3 px-4 rounded-xl sm:rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-semibold text-xs sm:text-sm border border-slate-200/60 transition-all duration-150 active:scale-[0.98] cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                Batal
            </button>
            <button type="button" id="confirmModalSubmitBtn" class="flex-1 py-3 px-4 rounded-xl sm:rounded-2xl font-semibold text-xs sm:text-sm transition-all duration-150 active:scale-[0.98] shadow-md flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2">
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
    const titleEl = document.getElementById('confirmModalTitle');
    const messageEl = document.getElementById('confirmModalMessage');
    const iconWrapper = document.getElementById('confirmModalIconWrapper');
    const cancelBtn = document.getElementById('confirmModalCancelBtn');
    const submitBtn = document.getElementById('confirmModalSubmitBtn');
    const submitText = document.getElementById('confirmModalSubmitText');
    const closeXBtn = document.getElementById('confirmModalCloseX');

    let currentResolver = null;

    const TYPE_CONFIG = {
        danger: {
            wrapperClass: 'bg-rose-100 text-rose-600 border-rose-200/80',
            submitClass: 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-rose-600/25 focus-visible:ring-rose-500',
            defaultIcon: 'trash-2',
            defaultConfirm: 'Ya, Hapus'
        },
        delete: {
            wrapperClass: 'bg-rose-100 text-rose-600 border-rose-200/80',
            submitClass: 'bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white shadow-rose-600/25 focus-visible:ring-rose-500',
            defaultIcon: 'trash-2',
            defaultConfirm: 'Ya, Hapus'
        },
        warning: {
            wrapperClass: 'bg-amber-100 text-amber-700 border-amber-200/80',
            submitClass: 'bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white shadow-amber-600/25 focus-visible:ring-amber-500',
            defaultIcon: 'alert-triangle',
            defaultConfirm: 'Ya, Lanjutkan'
        },
        primary: {
            wrapperClass: 'bg-maarif-100 text-maarif-700 border-maarif-200/80',
            submitClass: 'bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white shadow-maarif-700/25 focus-visible:ring-maarif-600',
            defaultIcon: 'save',
            defaultConfirm: 'Ya, Simpan'
        },
        save: {
            wrapperClass: 'bg-maarif-100 text-maarif-700 border-maarif-200/80',
            submitClass: 'bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white shadow-maarif-700/25 focus-visible:ring-maarif-600',
            defaultIcon: 'save',
            defaultConfirm: 'Ya, Simpan'
        },
        lock: {
            wrapperClass: 'bg-maarif-100 text-maarif-700 border-maarif-200/80',
            submitClass: 'bg-maarif-700 hover:bg-maarif-800 active:bg-maarif-900 text-white shadow-maarif-700/25 focus-visible:ring-maarif-600',
            defaultIcon: 'lock',
            defaultConfirm: 'Ya, Simpan & Kunci'
        },
        logout: {
            wrapperClass: 'bg-amber-100 text-amber-700 border-amber-200/80',
            submitClass: 'bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white shadow-amber-600/25 focus-visible:ring-amber-500',
            defaultIcon: 'log-out',
            defaultConfirm: 'Ya, Keluar'
        },
        key: {
            wrapperClass: 'bg-amber-100 text-amber-700 border-amber-200/80',
            submitClass: 'bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white shadow-amber-600/25 focus-visible:ring-amber-500',
            defaultIcon: 'key-round',
            defaultConfirm: 'Ya, Reset Sandi'
        },
        success: {
            wrapperClass: 'bg-emerald-100 text-emerald-700 border-emerald-200/80',
            submitClass: 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-emerald-600/25 focus-visible:ring-emerald-500',
            defaultIcon: 'check-circle-2',
            defaultConfirm: 'Ya, Lanjutkan'
        },
        info: {
            wrapperClass: 'bg-sky-100 text-sky-700 border-sky-200/80',
            submitClass: 'bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white shadow-sky-600/25 focus-visible:ring-sky-500',
            defaultIcon: 'info',
            defaultConfirm: 'Mengerti'
        }
    };

    function setModalIcon(iconName) {
        if (!iconWrapper) return;
        iconWrapper.innerHTML = `<i data-lucide="${iconName}" class="w-6 h-6"></i>`;
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons({
                root: iconWrapper
            });
        }
    }

    function resetStyles() {
        iconWrapper.className = 'w-12 h-12 rounded-2xl flex items-center justify-center border shadow-inner transition-colors';
        submitBtn.className = 'flex-1 py-3 px-4 rounded-xl sm:rounded-2xl font-bold text-xs sm:text-sm transition-all duration-150 active:scale-[0.98] shadow-md flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2';
        cancelBtn.classList.remove('hidden');
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
            submitBtn.className += ' ' + config.submitClass;

            // Render fresh Lucide icon
            const iconName = options.icon || config.defaultIcon;
            setModalIcon(iconName);

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
            submitBtn.className += ' ' + config.submitClass;

            const iconName = options.icon || config.defaultIcon;
            setModalIcon(iconName);

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
