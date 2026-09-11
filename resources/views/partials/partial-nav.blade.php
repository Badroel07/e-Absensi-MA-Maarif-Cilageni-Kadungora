<!-- Native SPA Runtime Engine (Ma'arif Seamless Navigation & Interactive Forms) -->
<div id="pjax-progress-bar" class="fixed top-0 left-0 right-0 z-[99999] h-[3px] pointer-events-none opacity-0 transition-all duration-200" style="width: 0%; background: linear-gradient(90deg, #10b981 0%, #34d399 50%, #f59e0b 100%); box-shadow: 0 0 10px rgba(16, 185, 129, 0.8);"></div>

<script>
(function() {
    'use strict';

    const progressBar = document.getElementById('pjax-progress-bar');
    let currentAbortController = null;
    let progressTimer = null;
    let progressShowTimer = null;

    // Scroll positions map for precise Back/Forward scroll restoration
    const scrollPositions = new Map(); // url -> {x, y}

    // Lifecycle callbacks registered by pages
    const pageUnloadCallbacks = new Set();
    const pageLoadCallbacks = new Set();

    // Track active page intervals to prevent orphan background polling
    const pageIntervals = new Set();
    const nativeSetInterval = window.setInterval;
    const nativeClearInterval = window.clearInterval;

    window.setInterval = function(fn, delay, ...args) {
        const id = nativeSetInterval(fn, delay, ...args);
        pageIntervals.add(id);
        return id;
    };

    window.clearInterval = function(id) {
        pageIntervals.delete(id);
        return nativeClearInterval(id);
    };

    function clearPageIntervals() {
        pageIntervals.forEach(id => nativeClearInterval(id));
        pageIntervals.clear();
    }

    // Track page-scoped event listeners on window and document
    const pageScopedListeners = [];
    const nativeWindowAddEventListener = window.addEventListener.bind(window);
    const nativeDocAddEventListener = document.addEventListener.bind(document);

    let isTrackingPageListeners = false;

    window.addEventListener = function(type, listener, options) {
        if (isTrackingPageListeners) {
            pageScopedListeners.push({ target: window, type, listener, options });
        }
        return nativeWindowAddEventListener(type, listener, options);
    };

    document.addEventListener = function(type, listener, options) {
        if (isTrackingPageListeners) {
            pageScopedListeners.push({ target: document, type, listener, options });
        }
        return nativeDocAddEventListener(type, listener, options);
    };

    function clearPageEventListeners() {
        while (pageScopedListeners.length > 0) {
            const item = pageScopedListeners.pop();
            try {
                if (item.target === window) {
                    window.removeEventListener(item.type, item.listener, item.options);
                } else if (item.target === document) {
                    document.removeEventListener(item.type, item.listener, item.options);
                }
            } catch(e) {}
        }
    }

    async function cleanupPageMedia() {
        try {
            if (window.html5QrCode) {
                try {
                    if (window.html5QrCode.isScanning) {
                        await window.html5QrCode.stop().catch(() => {});
                    }
                    if (typeof window.html5QrCode.clear === 'function') {
                        window.html5QrCode.clear();
                    }
                } catch(e) {}
                window.html5QrCode = null;
            }

            const videos = document.querySelectorAll('video');
            videos.forEach(v => {
                if (v.srcObject && typeof v.srcObject.getTracks === 'function') {
                    v.srcObject.getTracks().forEach(track => {
                        try { track.stop(); } catch(e) {}
                    });
                    v.srcObject = null;
                }
            });
        } catch(e) {}
    }

    function triggerPageUnload() {
        pageUnloadCallbacks.forEach(cb => {
            try { cb(); } catch(e) { console.error('[MaarifSPA] Unload callback error:', e); }
        });
        pageUnloadCallbacks.clear();
        clearPageEventListeners();
    }

    function triggerPageLoad(url) {
        pageLoadCallbacks.forEach(cb => {
            try { cb({ url }); } catch(e) { console.error('[MaarifSPA] Load callback error:', e); }
        });
    }

    function startProgress() {
        if (!progressBar) return;
        if (progressTimer) clearInterval(progressTimer);
        if (progressShowTimer) clearTimeout(progressShowTimer);

        // Reset tanpa animasi
        progressBar.style.transition = 'none';
        progressBar.style.opacity = '0';
        progressBar.style.width = '0%';

        let currentWidth = 15;

        // Tunda tampil progress bar 500ms — navigasi cepat tidak perlu tampil sama sekali
        progressShowTimer = setTimeout(() => {
            progressBar.style.transition = 'width 0.2s ease, opacity 0.15s ease';
            progressBar.style.opacity = '1';
            progressBar.style.width = currentWidth + '%';

            progressTimer = setInterval(() => {
                if (currentWidth < 85) {
                    currentWidth += Math.random() * 12;
                    progressBar.style.width = Math.min(currentWidth, 85) + '%';
                }
            }, 150);
        }, 500);
    }

    function finishProgress() {
        if (!progressBar) return;
        if (progressTimer) clearInterval(progressTimer);

        // Jika bar belum sempat tampil (navigasi < 500ms), batalkan & reset diam-diam
        if (progressShowTimer) {
            clearTimeout(progressShowTimer);
            progressShowTimer = null;
            progressBar.style.transition = 'none';
            progressBar.style.opacity = '0';
            progressBar.style.width = '0%';
            return;
        }

        progressBar.style.transition = 'width 0.15s ease, opacity 0.25s ease';
        progressBar.style.width = '100%';

        setTimeout(() => {
            progressBar.style.opacity = '0';
            setTimeout(() => {
                progressBar.style.width = '0%';
            }, 250);
        }, 150);
    }

    function isEligibleLink(anchor) {
        if (!anchor || !anchor.href) return false;
        if (anchor.target && anchor.target !== '_self') return false;
        if (anchor.hasAttribute('download') || anchor.hasAttribute('data-no-pjax') || anchor.hasAttribute('data-native')) return false;

        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            return false;
        }

        // Pure in-page anchor hash jump
        if (href.startsWith('#')) return false;

        try {
            const url = new URL(anchor.href, window.location.origin);
            if (url.origin !== window.location.origin) return false;

            const path = url.pathname.toLowerCase();
            if (/\.(pdf|xlsx|xls|csv|zip|png|jpe?g|svg|webp)$/.test(path)) return false;
            if (path.includes('/export') || path.includes('/download') || path.includes('/cetak') || path.includes('/pdf')) return false;

            return true;
        } catch (e) {
            return false;
        }
    }

    function isEligibleForm(form) {
        if (!form || !(form instanceof HTMLFormElement)) return false;
        if (form.target && form.target !== '_self') return false;
        if (form.hasAttribute('data-no-pjax') || form.hasAttribute('data-native')) return false;

        const action = form.getAttribute('action') || window.location.href;
        try {
            const url = new URL(action, window.location.origin);
            if (url.origin !== window.location.origin) return false;
            const path = url.pathname.toLowerCase();
            if (/\.(pdf|xlsx|xls|csv|zip)$/.test(path)) return false;
            if (path.includes('/export') || path.includes('/download') || path.includes('/cetak') || path.includes('/pdf')) return false;
            return true;
        } catch(e) {
            return false;
        }
    }

    const loadedExternalScripts = new Set();
    document.querySelectorAll('script[src]').forEach(s => {
        if (s.src) loadedExternalScripts.add(s.src);
    });

    function loadExternalScript(scriptEl) {
        const src = scriptEl.src;
        if (!src) return Promise.resolve();

        if (loadedExternalScripts.has(src)) {
            return Promise.resolve();
        }

        const existingTag = document.querySelector(`script[src="${src}"]`);
        if (existingTag) {
            if (existingTag.dataset.loaded === 'true') {
                loadedExternalScripts.add(src);
                return Promise.resolve();
            }
            return new Promise((resolve) => {
                existingTag.addEventListener('load', () => {
                    loadedExternalScripts.add(src);
                    resolve();
                }, { once: true });
                existingTag.addEventListener('error', () => {
                    resolve();
                }, { once: true });
            });
        }

        return new Promise((resolve) => {
            const newScript = document.createElement('script');
            Array.from(scriptEl.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            newScript.dataset.loaded = 'false';

            newScript.onload = () => {
                newScript.dataset.loaded = 'true';
                loadedExternalScripts.add(src);
                resolve();
            };

            newScript.onerror = (e) => {
                console.warn('[MaarifSPA] Failed to load external script:', src, e);
                newScript.dataset.loaded = 'error';
                resolve();
            };

            document.head.appendChild(newScript);
        });
    }

    function executeInlineScript(scriptEl) {
        const newScript = document.createElement('script');
        Array.from(scriptEl.attributes).forEach(attr => {
            if (attr.name !== 'src') newScript.setAttribute(attr.name, attr.value);
        });
        const rawCode = scriptEl.textContent || '';
        try {
            // If the script is already wrapped in a self-executing function/IIFE, execute it directly
            if (/^\s*\(?\s*(?:!|\+|-|~)?\s*function\s*\(/.test(rawCode)) {
                newScript.textContent = rawCode;
            } else {
                const sanitizedCode = rawCode.replace(/(^|[;\r\n])\s*(?:const|let)\s+/g, '$1var ');
                newScript.textContent = sanitizedCode;
            }
            document.body.appendChild(newScript);
            newScript.remove();
        } catch(err) {
            console.warn('[MaarifSPA] Inline script execution failed:', err);
        }
    }

    async function executeScriptsSequentially(scriptElements) {
        for (const script of scriptElements) {
            if (script.src) {
                await loadExternalScript(script);
            } else {
                executeInlineScript(script);
            }
        }
    }

    // Apply DOM updates from newly parsed document
    async function applyDocumentUpdates(newDoc, targetUrl, options = {}) {
        const mainContent = document.getElementById('main-content');
        const newMain = newDoc.getElementById('main-content');
        if (!newMain) return false;

        // 1. Update Document Title
        document.title = newDoc.title;

        // 2. Update Desktop Header Title
        const curDesktopTitle = document.getElementById('desktop-header-title');
        const newDesktopTitle = newDoc.getElementById('desktop-header-title');
        if (curDesktopTitle && newDesktopTitle) {
            curDesktopTitle.innerHTML = newDesktopTitle.innerHTML;
        }

        // 3. Update Mobile Header Title
        const curMobileTitle = document.getElementById('mobile-header-title');
        const newMobileTitle = newDoc.getElementById('mobile-header-title');
        if (curMobileTitle && newMobileTitle) {
            curMobileTitle.innerHTML = newMobileTitle.innerHTML;
        }

        // 4. Update Sidebar Navigation (Active States) while preserving scroll
        const curSidebarNav = document.getElementById('sidebar-nav');
        const newSidebarNav = newDoc.getElementById('sidebar-nav');
        if (curSidebarNav && newSidebarNav) {
            const scrollPos = curSidebarNav.scrollTop;
            curSidebarNav.innerHTML = newSidebarNav.innerHTML;
            curSidebarNav.scrollTop = scrollPos;
        }

        // 5. Update Mobile Bottom Nav (Active States)
        const curBottomNav = document.getElementById('mobile-bottom-nav-inner');
        const newBottomNav = newDoc.getElementById('mobile-bottom-nav-inner');
        if (curBottomNav && newBottomNav) {
            curBottomNav.innerHTML = newBottomNav.innerHTML;
        }

        // 6. Update Main Content
        if (mainContent) {
            mainContent.innerHTML = newMain.innerHTML;
            mainContent.style.opacity = '1';
        }

        // 7. Update Page-Specific Styles
        const newPageStyles = newDoc.getElementById('page-styles-container');
        const curPageStyles = document.getElementById('page-styles-container');
        if (curPageStyles && newPageStyles) {
            curPageStyles.innerHTML = newPageStyles.innerHTML;
        }

        // 8. Update CSRF token across document if rotated
        const newCsrf = newDoc.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (newCsrf) {
            const curCsrf = document.querySelector('meta[name="csrf-token"]');
            if (curCsrf) curCsrf.setAttribute('content', newCsrf);
            document.querySelectorAll('input[name="_token"]').forEach(input => {
                input.value = newCsrf;
            });
        }

        // 8b. Sync theme-color so Chrome Android toolbar stays green across SPA navigations
        const newThemeColor = newDoc.querySelector('meta[name="theme-color"]')?.getAttribute('content');
        const curThemeColor = document.querySelector('meta[name="theme-color"]');
        if (newThemeColor) {
            if (curThemeColor) {
                curThemeColor.setAttribute('content', newThemeColor);
            } else {
                const meta = document.createElement('meta');
                meta.name = 'theme-color';
                meta.content = newThemeColor;
                document.head.appendChild(meta);
            }
        } else if (curThemeColor) {
            curThemeColor.remove();
        }

        // 9. Update and Execute Page-Specific Scripts sequentially (awaiting external dependencies)
        const scriptsToExecute = [];
        const newPageScripts = newDoc.getElementById('page-scripts-container');
        if (newPageScripts) {
            newPageScripts.querySelectorAll('script').forEach(s => scriptsToExecute.push(s));
        }

        if (mainContent) {
            mainContent.querySelectorAll('script').forEach(s => scriptsToExecute.push(s));
        }

        if (scriptsToExecute.length > 0) {
            await executeScriptsSequentially(scriptsToExecute);
        }

        // 10. Reinitialize Lucide Icons
        if (typeof window.reinitLucideIcons === 'function') {
            try { window.reinitLucideIcons(); } catch(e) {}
        } else if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }

        // 10b. Reinitialize Alpine Component Tree on injected main content
        if (window.Alpine && typeof window.Alpine.initTree === 'function' && mainContent) {
            try {
                window.Alpine.initTree(mainContent);
            } catch(e) {
                console.warn('[MaarifSPA] Alpine initTree error:', e);
            }
        }

        // 11. Handle Scroll Position
        if (options.restoreScroll && options.savedScroll) {
            window.scrollTo({ top: options.savedScroll.y, left: options.savedScroll.x, behavior: 'instant' });
        } else if (!options.isLiveSearch) {
            window.scrollTo({ top: 0, behavior: 'instant' });
        }

        // 12. If live search, restore input focus and cursor position seamlessly
        if (options.isLiveSearch && options.activeInputName) {
            const targetInput = document.querySelector(`input[name="${options.activeInputName}"]`);
            if (targetInput) {
                targetInput.focus();
                if (typeof options.cursorStart === 'number' && typeof options.cursorEnd === 'number') {
                    try { targetInput.setSelectionRange(options.cursorStart, options.cursorEnd); } catch(e) {}
                }
            }
        }

        // 13. Auto close open modal dialogs on successful form completion
        if (options.closeModals) {
            document.querySelectorAll('[id^="modal"]:not(.hidden), [role="dialog"]:not(.hidden)').forEach(modalEl => {
                modalEl.classList.add('hidden');
            });
            document.body.classList.remove('overflow-hidden');
        }

        // 14. Trigger Toast Notifications from Flash Messages
        if (typeof window.triggerFlashFromDocument === 'function') {
            window.triggerFlashFromDocument(newDoc);
        }

        // 15. Dispatch Lifecycle Events
        try {
            const dclEvent = new Event('DOMContentLoaded', { bubbles: true, cancelable: true });
            document.dispatchEvent(dclEvent);
            window.dispatchEvent(dclEvent);
            window.dispatchEvent(new CustomEvent('app:page-loaded', { detail: { url: targetUrl } }));
            triggerPageLoad(targetUrl);
        } catch(e) {}

        return true;
    }

    // ── PERFORM SMOOTH DOCUMENT UPDATE (Scoped in-DOM crossfade, never covers header/bottom bar) ──
    async function performDocumentUpdate(newDoc, targetUrl, options = {}) {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const mainContent = document.getElementById('main-content');

        if (!mainContent || options.isLiveSearch || prefersReducedMotion) {
            return await applyDocumentUpdates(newDoc, targetUrl, options);
        }

        // Clean GPU-accelerated enter animation strictly scoped inside #main-content
        mainContent.classList.remove('spa-content-enter');
        void mainContent.offsetWidth; // Force reflow so animation restarts cleanly

        await applyDocumentUpdates(newDoc, targetUrl, options);

        mainContent.classList.add('spa-content-enter');
        setTimeout(() => {
            if (mainContent) mainContent.classList.remove('spa-content-enter');
        }, 140);

        return true;
    }

    // ── 1:1 HIGH-PRECISION SKELETON BUILDERS ──────────────────────────────────
    function skelDark(widthCls, heightCls = 'h-3', roundedCls = 'rounded', extraCls = '') {
        return '<div class="skeleton-dark ' + widthCls + ' ' + heightCls + ' ' + roundedCls + ' ' + extraCls + '"></div>';
    }

    function skelLight(widthCls, heightCls = 'h-3', roundedCls = 'rounded', extraCls = '') {
        return '<div class="skeleton ' + widthCls + ' ' + heightCls + ' ' + roundedCls + ' ' + extraCls + '"></div>';
    }

    function skelPageHeader(titleW, subtitleW, btns = []) {
        let btnHtml = '';
        if (btns.length > 0) {
            btnHtml = '<div class="flex items-center gap-2 mt-2 sm:mt-0 shrink-0">';
            btns.forEach(btn => {
                btnHtml += '<div class="skeleton ' + (btn.w || 'w-28') + ' ' + (btn.h || 'h-9') + ' ' + (btn.rounded || 'rounded-xl') + ' shrink-0"></div>';
            });
            btnHtml += '</div>';
        }
        return '<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">' +
            '<div class="space-y-1.5">' +
                '<div class="skeleton skeleton-text ' + (titleW || 'w-48') + ' h-7 rounded-lg"></div>' +
                '<div class="skeleton skeleton-text ' + (subtitleW || 'w-64') + ' h-3.5 opacity-60"></div>' +
            '</div>' +
            btnHtml +
        '</div>';
    }

    function skelStatCardWhite(titleW, valW, subW, roundedCls = 'rounded-2xl') {
        return '<div class="bg-white ' + roundedCls + ' p-5 border border-slate-200/80 flex flex-col justify-between space-y-3">' +
            '<div class="flex items-center justify-between">' +
                '<div class="skeleton skeleton-text ' + (titleW || 'w-20') + ' h-2.5 opacity-60"></div>' +
                '<div class="skeleton w-8 h-8 rounded-xl shrink-0"></div>' +
            '</div>' +
            '<div>' +
                '<div class="skeleton skeleton-text ' + (valW || 'w-16') + ' h-8 rounded-lg"></div>' +
                '<div class="skeleton skeleton-text ' + (subW || 'w-24') + ' h-2.5 opacity-50 mt-2"></div>' +
            '</div>' +
        '</div>';
    }

    function skelFilterBar(fields = []) {
        let items = '';
        fields.forEach(f => {
            if (f.type === 'search') {
                items += '<div class="skeleton h-9 rounded-xl flex-1 min-w-[180px] max-w-xs"></div>';
            } else {
                items += '<div class="skeleton ' + (f.w || 'w-28') + ' h-9 rounded-xl shrink-0"></div>';
            }
        });
        return '<div class="bg-white rounded-2xl border border-slate-200/80 p-4">' +
            '<div class="flex flex-wrap items-center gap-3">' + items + '</div>' +
        '</div>';
    }

    function skelTable(theadCols = [], rowsCount = 6, options = {}) {
        let theadHtml = theadCols.map(c => {
            const align = c.align === 'center' ? 'text-center' : (c.align === 'right' ? 'text-right' : 'text-left');
            const justify = c.align === 'center' ? 'mx-auto' : (c.align === 'right' ? 'ml-auto' : '');
            return '<th class="py-3 px-5 ' + align + '"><div class="skeleton skeleton-text ' + (c.w || 'w-20') + ' h-2.5 opacity-50 ' + justify + '"></div></th>';
        }).join('');

        let rowsHtml = '';
        for (let i = 0; i < rowsCount; i++) {
            rowsHtml += '<tr class="border-b border-slate-100">';
            theadCols.forEach(c => {
                const align = c.align === 'center' ? 'text-center' : (c.align === 'right' ? 'text-right' : 'text-left');
                const justify = c.align === 'center' ? 'justify-center' : (c.align === 'right' ? 'justify-end' : '');
                const mAuto = c.align === 'center' ? 'mx-auto' : (c.align === 'right' ? 'ml-auto' : '');

                rowsHtml += '<td class="py-3.5 px-5 ' + align + '">';
                if (c.avatar) {
                    rowsHtml += '<div class="flex items-center gap-3 ' + justify + '">' +
                        '<div class="skeleton skeleton-circle w-8 h-8 rounded-lg shrink-0"></div>' +
                        '<div class="space-y-1.5 flex-1 min-w-0">' +
                            '<div class="skeleton skeleton-text ' + (c.rowW || 'w-32') + ' h-3"></div>' +
                            '<div class="skeleton skeleton-text ' + (c.subW || 'w-20') + ' h-2.5 opacity-60"></div>' +
                        '</div>' +
                    '</div>';
                } else if (c.badge) {
                    rowsHtml += '<div class="skeleton ' + (c.rowW || 'w-20') + ' h-6 rounded-md ' + mAuto + '"></div>';
                } else if (c.btn) {
                    rowsHtml += '<div class="skeleton ' + (c.rowW || 'w-16') + ' h-7 rounded-lg ' + mAuto + '"></div>';
                } else {
                    rowsHtml += '<div class="skeleton skeleton-text ' + (c.rowW || 'w-24') + ' h-3 ' + mAuto + '"></div>';
                    if (c.sub) {
                        rowsHtml += '<div class="skeleton skeleton-text ' + (c.subW || 'w-16') + ' h-2.5 opacity-60 mt-1 ' + mAuto + '"></div>';
                    }
                }
                rowsHtml += '</td>';
            });
            rowsHtml += '</tr>';
        }

        let headerBlock = '';
        if (options.title) {
            const stripe = options.accentColor ? '<div class="w-1 h-8 rounded-full ' + options.accentColor + ' shrink-0"></div>' : '';
            const rBtn = options.rightBtn ? '<div class="skeleton ' + (options.rightBtn.w || 'w-20') + ' h-8 rounded-xl shrink-0"></div>' : '';
            headerBlock = '<div class="p-5 border-b border-slate-100 flex items-center justify-between gap-3">' +
                '<div class="flex items-center gap-3">' +
                    stripe +
                    '<div>' +
                        '<div class="skeleton skeleton-text ' + (options.titleW || 'w-36') + ' h-4 rounded"></div>' +
                        '<div class="skeleton skeleton-text ' + (options.subtitleW || 'w-48') + ' h-2.5 opacity-50 mt-1"></div>' +
                    '</div>' +
                '</div>' +
                rBtn +
            '</div>';
        }

        return '<div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">' +
            headerBlock +
            '<div class="overflow-x-auto">' +
                '<table class="w-full text-left border-collapse text-xs">' +
                    '<thead><tr class="bg-slate-50 border-b border-slate-100">' + theadHtml + '</tr></thead>' +
                    '<tbody class="divide-y divide-slate-100">' + rowsHtml + '</tbody>' +
                '</table>' +
            '</div>' +
        '</div>';
    }

    // ── 1:1 ROUTE-SPECIFIC SKELETON GENERATORS ───────────────────────────────

    // 1. /admin (Admin Dashboard)
    function skelAdminDashboard() {
        return '<div class="space-y-7">' +
            '<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-1.5">' +
                '<div class="space-y-1.5">' +
                    skelLight('w-36', 'h-3', 'rounded', 'opacity-60') +
                    skelLight('w-44', 'h-7', 'rounded-lg') +
                    skelLight('w-64', 'h-3', 'rounded', 'opacity-50') +
                '</div>' +
                '<div class="skeleton w-28 h-8 rounded-lg shrink-0"></div>' +
            '</div>' +
            '<div class="grid grid-cols-1 lg:grid-cols-5 gap-4">' +
                '<div class="lg:col-span-2 bg-gradient-to-br from-maarif-800 to-maarif-900 rounded-2xl p-6 flex flex-col justify-between text-white relative overflow-hidden">' +
                    '<div>' +
                        skelDark('w-36', 'h-3', 'rounded', 'opacity-70') +
                        skelDark('w-44', 'h-10', 'rounded-xl', 'my-3') +
                        '<div class="w-full bg-white/15 rounded-full h-2 my-2"><div class="skeleton-dark h-2 rounded-full w-2/3"></div></div>' +
                    '</div>' +
                    '<div class="flex items-center gap-4 mt-5">' +
                        skelDark('w-14', 'h-3') + '<span class="text-white/20">|</span>' +
                        skelDark('w-14', 'h-3') + '<span class="text-white/20">|</span>' +
                        skelDark('w-14', 'h-3') +
                    '</div>' +
                '</div>' +
                '<div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-4">' +
                    skelStatCardWhite('w-20', 'w-16', 'w-24') +
                    skelStatCardWhite('w-20', 'w-14', 'w-24') +
                    skelStatCardWhite('w-24', 'w-14', 'w-24') +
                '</div>' +
            '</div>' +
            skelTable([
                { w: 'w-24', rowW: 'w-32', sub: true, subW: 'w-20' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-20' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-20' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-20' }
            ], 4, {
                title: true,
                titleW: 'w-40',
                subtitleW: 'w-56',
                accentColor: 'bg-maarif-700',
                rightBtn: { w: 'w-20' }
            }) +
            skelTable([
                { w: 'w-28', rowW: 'w-36' },
                { w: 'w-24', rowW: 'w-28' },
                { w: 'w-16', align: 'center', rowW: 'w-16' },
                { w: 'w-20', rowW: 'w-20' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-16' },
                { w: 'w-12', align: 'right', btn: true, rowW: 'w-12' }
            ], 3, {
                title: true,
                titleW: 'w-44',
                subtitleW: 'w-60',
                accentColor: 'bg-amber-500'
            }) +
        '</div>';
    }

    // 2. /admin/presensi-siswa
    function skelAdminPresensiSiswa() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-44', 'w-72', [{ w: 'w-44', h: 'h-9' }]) +
            '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">' +
                '<div class="bg-gradient-to-br from-maarif-800 to-maarif-900 rounded-2xl p-5 text-white flex flex-col justify-between">' +
                    '<div>' +
                        skelDark('w-24', 'h-2.5', 'rounded', 'opacity-70') +
                        skelDark('w-32', 'h-8', 'rounded-lg', 'my-2.5') +
                    '</div>' +
                    '<div class="w-full bg-white/15 rounded-full h-1.5 mt-3"><div class="skeleton-dark h-1.5 rounded-full w-3/4"></div></div>' +
                '</div>' +
                skelStatCardWhite('w-16', 'w-14', 'w-28') +
                skelStatCardWhite('w-16', 'w-14', 'w-28') +
                skelStatCardWhite('w-24', 'w-14', 'w-28') +
            '</div>' +
            skelFilterBar([{ w: 'w-40' }, { w: 'w-32' }, { w: 'w-32' }, { type: 'search' }, { w: 'w-24' }]) +
            skelTable([
                { w: 'w-28', avatar: true, rowW: 'w-32', subW: 'w-20' },
                { w: 'w-28', rowW: 'w-32' },
                { w: 'w-24', rowW: 'w-28' },
                { w: 'w-20', align: 'center', badge: true, rowW: 'w-20' },
                { w: 'w-16', align: 'center', rowW: 'w-16' },
                { w: 'w-14', align: 'center', badge: true, rowW: 'w-14' },
                { w: 'w-12', align: 'right', btn: true, rowW: 'w-12' }
            ], 8) +
        '</div>';
    }

    // 3. /admin/presensi-guru
    function skelAdminPresensiGuru() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-44', 'w-72') +
            '<div class="grid grid-cols-1 md:grid-cols-4 gap-4">' +
                skelStatCardWhite('w-20', 'w-16', 'w-24') +
                skelStatCardWhite('w-16', 'w-14', 'w-24') +
                skelStatCardWhite('w-16', 'w-14', 'w-24') +
                skelStatCardWhite('w-24', 'w-14', 'w-24') +
            '</div>' +
            skelFilterBar([{ w: 'w-40' }, { w: 'w-32' }, { type: 'search' }, { w: 'w-24' }]) +
            skelTable([
                { w: 'w-28', avatar: true, rowW: 'w-32', subW: 'w-24' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-16' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-16' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-20' },
                { w: 'w-24', rowW: 'w-28' },
                { w: 'w-14', align: 'right', btn: true, rowW: 'w-14' }
            ], 8) +
        '</div>';
    }

    // 4. /admin/siswa
    function skelAdminSiswa() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-44', 'w-64', [{ w: 'w-36', h: 'h-9' }]) +
            skelFilterBar([{ type: 'search' }, { w: 'w-36' }, { w: 'w-20' }]) +
            skelTable([
                { w: 'w-24', rowW: 'w-24' },
                { w: 'w-32', avatar: true, rowW: 'w-32', subW: 'w-20' },
                { w: 'w-20', badge: true, rowW: 'w-16' },
                { w: 'w-20', rowW: 'w-20' },
                { w: 'w-24', rowW: 'w-24' },
                { w: 'w-14', align: 'center', badge: true, rowW: 'w-14' },
                { w: 'w-16', align: 'right', btn: true, rowW: 'w-16' }
            ], 8) +
        '</div>';
    }

    // 5. /admin/siswa/*/riwayat
    function skelAdminSiswaRiwayat() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-52', 'w-72', [{ w: 'w-32', h: 'h-8' }, { w: 'w-24', h: 'h-8' }]) +
            '<div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">' +
                '<div class="flex items-center gap-4">' +
                    '<div class="skeleton w-14 h-14 rounded-xl shrink-0"></div>' +
                    '<div class="space-y-2">' +
                        '<div class="skeleton skeleton-text w-48 h-5 rounded"></div>' +
                        '<div class="flex gap-2">' +
                            '<div class="skeleton w-20 h-5 rounded"></div>' +
                            '<div class="skeleton w-24 h-5 rounded"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">' +
                skelStatCardWhite('w-16', 'w-12', 'w-20') +
                skelStatCardWhite('w-16', 'w-12', 'w-20') +
                skelStatCardWhite('w-16', 'w-12', 'w-20') +
                skelStatCardWhite('w-16', 'w-12', 'w-20') +
            '</div>' +
            skelFilterBar([{ w: 'w-36' }, { w: 'w-44' }]) +
            skelTable([
                { w: 'w-24', rowW: 'w-28' },
                { w: 'w-28', rowW: 'w-32' },
                { w: 'w-24', rowW: 'w-28' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-16' },
                { w: 'w-28', rowW: 'w-36' }
            ], 6) +
        '</div>';
    }

    // 6. /admin/guru
    function skelAdminGuru() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-44', 'w-64', [{ w: 'w-36', h: 'h-9' }]) +
            skelFilterBar([{ type: 'search' }, { w: 'w-20' }]) +
            skelTable([
                { w: 'w-28', rowW: 'w-28' },
                { w: 'w-36', avatar: true, rowW: 'w-36', subW: 'w-28' },
                { w: 'w-24', rowW: 'w-24' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-16' },
                { w: 'w-16', align: 'right', btn: true, rowW: 'w-16' }
            ], 8) +
        '</div>';
    }

    // 7. /admin/guru/*/riwayat
    function skelAdminGuruRiwayat() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-52', 'w-72', [{ w: 'w-32', h: 'h-8' }, { w: 'w-24', h: 'h-8' }]) +
            '<div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-4">' +
                '<div class="flex items-center gap-4">' +
                    '<div class="skeleton w-14 h-14 rounded-xl shrink-0"></div>' +
                    '<div class="space-y-2">' +
                        '<div class="skeleton skeleton-text w-48 h-5 rounded"></div>' +
                        '<div class="flex gap-2">' +
                            '<div class="skeleton w-24 h-5 rounded"></div>' +
                            '<div class="skeleton w-32 h-5 rounded"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">' +
                skelStatCardWhite('w-16', 'w-12', 'w-20') +
                skelStatCardWhite('w-16', 'w-12', 'w-20') +
                skelStatCardWhite('w-16', 'w-12', 'w-20') +
                skelStatCardWhite('w-16', 'w-12', 'w-20') +
            '</div>' +
            skelFilterBar([{ w: 'w-36' }]) +
            skelTable([
                { w: 'w-24', rowW: 'w-28' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-16' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-16' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-16' },
                { w: 'w-28', rowW: 'w-36' }
            ], 6) +
        '</div>';
    }

    // 8. /admin/kelas
    function skelAdminKelas() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-48', 'w-64', [{ w: 'w-32', h: 'h-9' }]) +
            skelTable([
                { w: 'w-24', badge: true, rowW: 'w-20' },
                { w: 'w-32', rowW: 'w-32' },
                { w: 'w-20', rowW: 'w-20' },
                { w: 'w-16', align: 'center', rowW: 'w-12' },
                { w: 'w-16', align: 'right', btn: true, rowW: 'w-14' }
            ], 6) +
        '</div>';
    }

    // 9. /admin/mapel
    function skelAdminMapel() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-40', 'w-64', [{ w: 'w-32', h: 'h-9' }]) +
            skelTable([
                { w: 'w-20', badge: true, rowW: 'w-16' },
                { w: 'w-44', rowW: 'w-40' },
                { w: 'w-28', rowW: 'w-24' },
                { w: 'w-16', align: 'right', btn: true, rowW: 'w-14' }
            ], 6) +
        '</div>';
    }

    // 10. /admin/jadwal
    function skelAdminJadwal() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-44', 'w-64', [{ w: 'w-32', h: 'h-9' }]) +
            skelFilterBar([{ w: 'w-32' }, { w: 'w-32' }, { w: 'w-40' }, { w: 'w-20' }]) +
            skelTable([
                { w: 'w-24', rowW: 'w-28' },
                { w: 'w-20', badge: true, rowW: 'w-16' },
                { w: 'w-32', rowW: 'w-36' },
                { w: 'w-28', avatar: true, rowW: 'w-28', subW: 'w-20' },
                { w: 'w-20', rowW: 'w-20' },
                { w: 'w-16', align: 'right', btn: true, rowW: 'w-14' }
            ], 8) +
        '</div>';
    }

    // 11. /admin/laporan
    function skelAdminLaporan() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-52', 'w-72', [{ w: 'w-28', h: 'h-9' }, { w: 'w-32', h: 'h-9' }]) +
            '<div class="bg-white rounded-2xl border border-slate-200/80 p-4">' +
                '<div class="flex flex-wrap items-center gap-3">' +
                    '<div class="skeleton w-36 h-9 rounded-xl shrink-0"></div>' +
                    '<div class="skeleton w-36 h-9 rounded-xl shrink-0"></div>' +
                    '<div class="skeleton w-36 h-9 rounded-xl shrink-0"></div>' +
                    '<div class="skeleton w-24 h-9 rounded-xl shrink-0"></div>' +
                '</div>' +
            '</div>' +
            '<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">' +
                skelStatCardWhite('w-20', 'w-16', 'w-24') +
                skelStatCardWhite('w-24', 'w-16', 'w-24') +
                skelStatCardWhite('w-20', 'w-16', 'w-24') +
            '</div>' +
            skelTable([
                { w: 'w-20', rowW: 'w-20' },
                { w: 'w-32', avatar: true, rowW: 'w-32', subW: 'w-20' },
                { w: 'w-16', badge: true, rowW: 'w-16' },
                { w: 'w-12', align: 'center', rowW: 'w-10' },
                { w: 'w-12', align: 'center', rowW: 'w-10' },
                { w: 'w-12', align: 'center', rowW: 'w-10' },
                { w: 'w-12', align: 'center', rowW: 'w-10' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-14' }
            ], 8) +
        '</div>';
    }

    // 12. /admin/lokasi
    function skelAdminLokasi() {
        return '<div class="max-w-3xl space-y-6">' +
            skelPageHeader('w-56', 'w-72') +
            '<div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/80 space-y-6">' +
                '<div class="flex items-center gap-3.5 pb-4 border-b border-slate-100">' +
                    '<div class="skeleton w-10 h-10 rounded-xl shrink-0"></div>' +
                    '<div class="space-y-1.5">' +
                        '<div class="skeleton skeleton-text w-48 h-4 rounded"></div>' +
                        '<div class="skeleton skeleton-text w-64 h-3 opacity-50"></div>' +
                    '</div>' +
                '</div>' +
                '<div class="space-y-4">' +
                    '<div class="space-y-1.5">' +
                        '<div class="skeleton skeleton-text w-36 h-3"></div>' +
                        '<div class="skeleton w-full h-10 rounded-xl"></div>' +
                    '</div>' +
                    '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">' +
                        '<div class="space-y-1.5">' +
                            '<div class="skeleton skeleton-text w-32 h-3"></div>' +
                            '<div class="skeleton w-full h-10 rounded-xl"></div>' +
                        '</div>' +
                        '<div class="space-y-1.5">' +
                            '<div class="skeleton skeleton-text w-32 h-3"></div>' +
                            '<div class="skeleton w-full h-10 rounded-xl"></div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="space-y-1.5">' +
                        '<div class="skeleton skeleton-text w-48 h-3"></div>' +
                        '<div class="skeleton w-full h-10 rounded-xl"></div>' +
                    '</div>' +
                    '<div class="skeleton w-full h-44 rounded-2xl"></div>' +
                    '<div class="skeleton w-36 h-10 rounded-xl"></div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    // 13. /admin/audit
    function skelAdminAudit() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-48', 'w-64') +
            skelFilterBar([{ w: 'w-36' }, { w: 'w-36' }, { w: 'w-36' }, { w: 'w-20' }]) +
            skelTable([
                { w: 'w-24', rowW: 'w-28' },
                { w: 'w-28', avatar: true, rowW: 'w-28', subW: 'w-20' },
                { w: 'w-20', badge: true, rowW: 'w-20' },
                { w: 'w-36', rowW: 'w-44' },
                { w: 'w-28', rowW: 'w-32' }
            ], 8) +
        '</div>';
    }

    // 14. /guru (Guru Dashboard)
    function skelGuruDashboard() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-36', 'w-72') +
            '<div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-maarif-900 via-maarif-800 to-emerald-950 text-white p-5 sm:p-7 shadow-xl border border-emerald-600/30 flex flex-col gap-4 sm:gap-5">' +
                '<div class="md:hidden flex items-center justify-between gap-2 pb-3 border-b border-white/10">' +
                    skelDark('w-32', 'h-4', 'rounded-full') +
                    skelDark('w-24', 'h-6', 'rounded-full') +
                '</div>' +
                '<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">' +
                    '<div class="flex items-center gap-4">' +
                        '<div class="skeleton-dark w-16 h-16 sm:w-20 sm:h-20 rounded-2xl shrink-0"></div>' +
                        '<div class="space-y-2 flex-1">' +
                            skelDark('w-24', 'h-4', 'rounded-md') +
                            skelDark('w-48', 'h-6', 'rounded-lg') +
                            skelDark('w-56', 'h-3', 'rounded', 'opacity-70') +
                        '</div>' +
                    '</div>' +
                    '<div class="hidden md:flex flex-col items-end shrink-0 pl-6 border-l border-white/15">' +
                        skelDark('w-32', 'h-7', 'rounded-lg') +
                        skelDark('w-28', 'h-3', 'rounded', 'mt-2 opacity-70') +
                    '</div>' +
                '</div>' +
                '<div class="rounded-2xl bg-black/20 p-3.5 sm:p-4 border border-white/15 flex items-center justify-between gap-3">' +
                    '<div class="flex items-center gap-3">' +
                        '<div class="skeleton-dark w-10 h-10 rounded-xl shrink-0"></div>' +
                        '<div class="space-y-1.5">' +
                            skelDark('w-40', 'h-3.5') +
                            skelDark('w-60', 'h-2.5', 'rounded', 'opacity-60') +
                        '</div>' +
                    '</div>' +
                    '<div class="skeleton-dark w-32 h-8 rounded-xl shrink-0"></div>' +
                '</div>' +
            '</div>' +
            '<div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-4 sm:p-5 flex items-center justify-between">' +
                '<div class="flex items-center gap-3">' +
                    '<div class="skeleton w-10 h-10 rounded-2xl shrink-0"></div>' +
                    '<div class="space-y-1.5">' +
                        '<div class="skeleton skeleton-text w-48 h-4 rounded"></div>' +
                        '<div class="skeleton skeleton-text w-56 h-2.5 opacity-50"></div>' +
                    '</div>' +
                '</div>' +
                '<div class="skeleton w-7 h-7 rounded-lg shrink-0"></div>' +
            '</div>' +
            '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">' +
                skelStatCardWhite('w-24', 'w-14', 'w-24') +
                skelStatCardWhite('w-20', 'w-14', 'w-24') +
                skelStatCardWhite('w-20', 'w-14', 'w-24') +
                skelStatCardWhite('w-24', 'w-14', 'w-24') +
            '</div>' +
            '<div class="space-y-3">' +
                '<div class="bg-white rounded-2xl sm:rounded-3xl p-5 border border-slate-200/80 flex items-center justify-between">' +
                    '<div class="space-y-2">' +
                        '<div class="skeleton skeleton-text w-40 h-5 rounded"></div>' +
                        '<div class="skeleton skeleton-text w-56 h-3 opacity-60"></div>' +
                    '</div>' +
                    '<div class="skeleton w-28 h-9 rounded-xl shrink-0"></div>' +
                '</div>' +
                '<div class="bg-white rounded-2xl sm:rounded-3xl p-5 border border-slate-200/80 flex items-center justify-between">' +
                    '<div class="space-y-2">' +
                        '<div class="skeleton skeleton-text w-36 h-5 rounded"></div>' +
                        '<div class="skeleton skeleton-text w-48 h-3 opacity-60"></div>' +
                    '</div>' +
                    '<div class="skeleton w-28 h-9 rounded-xl shrink-0"></div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    // 15. /guru/scan (QR Scanner)
    function skelGuruScan() {
        return '<div class="max-w-[860px] mx-auto space-y-4">' +
            '<div class="space-y-1">' +
                '<div class="skeleton skeleton-text w-48 h-7 rounded-lg"></div>' +
                '<div class="skeleton skeleton-text w-64 h-3.5 opacity-60"></div>' +
            '</div>' +
            '<div class="flex gap-2">' +
                '<div class="skeleton w-24 h-6 rounded-full"></div>' +
                '<div class="skeleton w-36 h-6 rounded-full"></div>' +
            '</div>' +
            '<div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-4 items-start">' +
                '<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden p-4 space-y-3">' +
                    '<div class="skeleton w-full h-11 rounded-xl"></div>' +
                    '<div class="w-full aspect-[4/3] bg-slate-900 rounded-xl relative flex items-center justify-center overflow-hidden">' +
                        '<div class="w-48 h-48 sm:w-56 sm:h-56 border-2 border-emerald-500/50 rounded-2xl relative overflow-hidden">' +
                            '<div class="skeleton-laser"></div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="skeleton skeleton-text w-48 h-3 mx-auto mt-2"></div>' +
                '</div>' +
                '<div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">' +
                    '<div class="space-y-1.5">' +
                        '<div class="skeleton skeleton-text w-36 h-4 rounded"></div>' +
                        '<div class="skeleton skeleton-text w-48 h-3 opacity-60"></div>' +
                    '</div>' +
                    '<div class="skeleton w-full h-16 rounded-xl"></div>' +
                    '<div class="space-y-2">' +
                        '<div class="skeleton skeleton-text w-full h-3"></div>' +
                        '<div class="skeleton skeleton-text w-3/4 h-3"></div>' +
                        '<div class="skeleton skeleton-text w-2/3 h-3"></div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    // 16. /guru/jadwal & /siswa/jadwal
    function skelWeeklySchedule(isSiswa = false) {
        return '<div class="space-y-6">' +
            skelPageHeader(isSiswa ? 'w-44' : 'w-44', 'w-72') +
            '<div class="space-y-6">' +
                [1, 2, 3].map(i =>
                    '<div class="bg-white rounded-3xl p-5 sm:p-7 border border-slate-200/80 space-y-4">' +
                        '<div class="flex items-center justify-between pb-4 border-b border-slate-100">' +
                            '<div class="flex items-center gap-3">' +
                                '<div class="skeleton w-10 h-10 rounded-2xl shrink-0"></div>' +
                                '<div class="space-y-1.5">' +
                                    '<div class="skeleton skeleton-text w-24 h-4 rounded"></div>' +
                                    '<div class="skeleton skeleton-text w-16 h-2.5 opacity-50"></div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="space-y-3">' +
                            '<div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">' +
                                '<div class="space-y-1.5">' +
                                    '<div class="skeleton skeleton-text w-36 h-4 rounded"></div>' +
                                    '<div class="skeleton skeleton-text w-24 h-2.5 opacity-50"></div>' +
                                '</div>' +
                                '<div class="skeleton w-20 h-6 rounded-md shrink-0"></div>' +
                            '</div>' +
                            '<div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">' +
                                '<div class="space-y-1.5">' +
                                    '<div class="skeleton skeleton-text w-44 h-4 rounded"></div>' +
                                    '<div class="skeleton skeleton-text w-28 h-2.5 opacity-50"></div>' +
                                '</div>' +
                                '<div class="skeleton w-20 h-6 rounded-md shrink-0"></div>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                ).join('') +
            '</div>' +
        '</div>';
    }

    // 17. /guru/riwayat (Teaching History)
    function skelGuruHistory() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-52', 'w-72') +
            '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">' +
                '<div class="sm:col-span-2 lg:col-span-5 bg-gradient-to-br from-emerald-800 via-emerald-800 to-emerald-900 rounded-3xl p-5 sm:p-6 text-white flex flex-col justify-between relative overflow-hidden">' +
                    '<div>' +
                        skelDark('w-32', 'h-3') +
                        skelDark('w-28', 'h-10', 'rounded-xl', 'my-3') +
                    '</div>' +
                    '<div class="w-full bg-white/15 rounded-full h-1.5 mt-2"><div class="skeleton-dark h-1.5 rounded-full w-4/5"></div></div>' +
                '</div>' +
                '<div class="sm:col-span-2 lg:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-4">' +
                    skelStatCardWhite('w-20', 'w-14', 'w-20', 'rounded-3xl') +
                    skelStatCardWhite('w-20', 'w-14', 'w-20', 'rounded-3xl') +
                    skelStatCardWhite('w-24', 'w-14', 'w-20', 'rounded-3xl') +
                '</div>' +
            '</div>' +
            skelFilterBar([{ type: 'search' }, { w: 'w-36' }, { w: 'w-32' }]) +
            '<div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-6 overflow-hidden">' +
                '<div class="overflow-x-auto">' +
                    '<table class="w-full text-left border-collapse text-xs">' +
                        '<thead><tr class="bg-slate-900 text-slate-200">' +
                            '<th class="py-3.5 px-5"><div class="skeleton-dark w-20 h-2.5"></div></th>' +
                            '<th class="py-3.5 px-5"><div class="skeleton-dark w-16 h-2.5"></div></th>' +
                            '<th class="py-3.5 px-5"><div class="skeleton-dark w-28 h-2.5"></div></th>' +
                            '<th class="py-3.5 px-5"><div class="skeleton-dark w-16 h-2.5"></div></th>' +
                            '<th class="py-3.5 px-5"><div class="skeleton-dark w-16 h-2.5"></div></th>' +
                            '<th class="py-3.5 px-5 text-center"><div class="skeleton-dark w-16 h-2.5 mx-auto"></div></th>' +
                        '</tr></thead>' +
                        '<tbody class="divide-y divide-slate-100">' +
                            [1,2,3,4,5,6].map(() =>
                                '<tr>' +
                                    '<td class="py-3.5 px-5"><div class="skeleton skeleton-text w-24 h-3"></div></td>' +
                                    '<td class="py-3.5 px-5"><div class="skeleton w-16 h-6 rounded-md"></div></td>' +
                                    '<td class="py-3.5 px-5"><div class="skeleton skeleton-text w-32 h-3"></div></td>' +
                                    '<td class="py-3.5 px-5"><div class="skeleton skeleton-text w-16 h-3"></div></td>' +
                                    '<td class="py-3.5 px-5"><div class="skeleton skeleton-text w-16 h-3"></div></td>' +
                                    '<td class="py-3.5 px-5 text-center"><div class="skeleton w-20 h-6 rounded-md mx-auto"></div></td>' +
                                '</tr>'
                            ).join('') +
                        '</tbody>' +
                    '</table>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    // 18. /guru/sessions/* (Live Session)
    function skelGuruSessionLive() {
        return '<div class="space-y-4">' +
            '<div class="flex items-center justify-between bg-white rounded-2xl p-4 border border-slate-200/80">' +
                '<div class="skeleton w-36 h-8 rounded-xl shrink-0"></div>' +
                '<div class="flex gap-2">' +
                    '<div class="skeleton w-20 h-7 rounded-lg shrink-0"></div>' +
                    '<div class="skeleton w-32 h-7 rounded-lg shrink-0"></div>' +
                '</div>' +
            '</div>' +
            '<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">' +
                '<div class="lg:col-span-5 space-y-4">' +
                    '<div class="bg-white rounded-3xl p-6 border border-slate-200/80 text-center space-y-5">' +
                        '<div class="skeleton w-44 h-6 rounded-full mx-auto"></div>' +
                        '<div class="space-y-2">' +
                            '<div class="skeleton skeleton-text w-36 h-3 mx-auto"></div>' +
                            '<div class="w-full h-24 bg-slate-950 rounded-3xl flex items-center justify-center">' +
                                '<div class="skeleton-dark w-44 h-12 rounded-xl"></div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="w-full bg-slate-200 rounded-full h-2.5"><div class="skeleton h-2.5 rounded-full w-2/3"></div></div>' +
                        '<div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">' +
                            '<div class="skeleton h-14 rounded-2xl"></div>' +
                            '<div class="skeleton h-14 rounded-2xl"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="lg:col-span-7 space-y-4">' +
                    '<div class="bg-white rounded-3xl p-6 border border-slate-200/80 space-y-4">' +
                        '<div class="flex items-center justify-between pb-3 border-b border-slate-100">' +
                            '<div class="skeleton skeleton-text w-44 h-4 rounded"></div>' +
                            '<div class="skeleton w-16 h-6 rounded-full"></div>' +
                        '</div>' +
                        '<div class="space-y-2">' +
                            [1,2,3,4,5].map(() =>
                                '<div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">' +
                                    '<div class="flex items-center gap-3">' +
                                        '<div class="skeleton skeleton-circle w-8 h-8 rounded-lg shrink-0"></div>' +
                                        '<div class="space-y-1">' +
                                            '<div class="skeleton skeleton-text w-32 h-3"></div>' +
                                            '<div class="skeleton skeleton-text w-20 h-2.5 opacity-60"></div>' +
                                        '</div>' +
                                    '</div>' +
                                    '<div class="skeleton w-16 h-6 rounded-md shrink-0"></div>' +
                                '</div>'
                            ).join('') +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    // 19. /guru/sessions/*/reconcile
    function skelGuruReconcile() {
        return '<div class="space-y-4 max-w-7xl mx-auto pb-8">' +
            '<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80">' +
                '<div class="flex items-center gap-3">' +
                    '<div class="skeleton w-10 h-10 rounded-2xl shrink-0"></div>' +
                    '<div class="space-y-1.5">' +
                        '<div class="skeleton skeleton-text w-44 h-4 rounded"></div>' +
                        '<div class="skeleton skeleton-text w-32 h-2.5 opacity-50"></div>' +
                    '</div>' +
                '</div>' +
                '<div class="flex gap-2">' +
                    '<div class="skeleton w-20 h-8 rounded-xl shrink-0"></div>' +
                    '<div class="skeleton w-20 h-8 rounded-xl shrink-0"></div>' +
                    '<div class="skeleton w-24 h-8 rounded-xl shrink-0"></div>' +
                '</div>' +
            '</div>' +
            '<div class="space-y-3">' +
                [1,2,3,4,5,6].map(() =>
                    '<div class="bg-white rounded-2xl p-4 border border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">' +
                        '<div class="flex items-center gap-3">' +
                            '<div class="skeleton w-10 h-10 rounded-xl shrink-0"></div>' +
                            '<div class="space-y-1">' +
                                '<div class="skeleton skeleton-text w-36 h-3.5"></div>' +
                                '<div class="skeleton skeleton-text w-24 h-2.5 opacity-60"></div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="flex items-center gap-1.5">' +
                            '<div class="skeleton w-16 h-8 rounded-xl shrink-0"></div>' +
                            '<div class="skeleton w-16 h-8 rounded-xl shrink-0"></div>' +
                            '<div class="skeleton w-16 h-8 rounded-xl shrink-0"></div>' +
                            '<div class="skeleton w-16 h-8 rounded-xl shrink-0"></div>' +
                        '</div>' +
                    '</div>'
                ).join('') +
            '</div>' +
            '<div class="skeleton w-44 h-11 rounded-2xl mx-auto mt-4"></div>' +
        '</div>';
    }

    // 20. /siswa (Siswa Dashboard)
    function skelSiswaDashboard() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-44', 'w-72') +
            '<div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-950 text-white p-5 sm:p-7 shadow-lg border border-emerald-700/50 flex flex-col gap-4 sm:gap-5">' +
                '<div class="md:hidden flex items-center justify-between gap-2 pb-3 border-b border-white/10">' +
                    skelDark('w-32', 'h-4', 'rounded-full') +
                    skelDark('w-24', 'h-6', 'rounded-full') +
                '</div>' +
                '<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">' +
                    '<div class="flex items-center gap-4">' +
                        '<div class="skeleton-dark w-16 h-16 sm:w-20 sm:h-20 rounded-2xl shrink-0"></div>' +
                        '<div class="space-y-2 flex-1">' +
                            skelDark('w-44', 'h-6', 'rounded-lg') +
                            skelDark('w-56', 'h-3', 'rounded', 'opacity-70') +
                            skelDark('w-36', 'h-4', 'rounded-md', 'mt-1') +
                        '</div>' +
                    '</div>' +
                    '<div class="hidden md:flex flex-col items-end shrink-0 pl-6 border-l border-white/15">' +
                        skelDark('w-32', 'h-7', 'rounded-lg') +
                        skelDark('w-28', 'h-3', 'rounded', 'mt-2 opacity-70') +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 p-4 sm:p-5 flex items-center justify-between">' +
                '<div class="flex items-center gap-3">' +
                    '<div class="skeleton w-10 h-10 rounded-2xl shrink-0"></div>' +
                    '<div class="space-y-1.5">' +
                        '<div class="skeleton skeleton-text w-44 h-4 rounded"></div>' +
                        '<div class="skeleton skeleton-text w-56 h-2.5 opacity-50"></div>' +
                    '</div>' +
                '</div>' +
                '<div class="skeleton w-7 h-7 rounded-lg shrink-0"></div>' +
            '</div>' +
            '<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">' +
                skelStatCardWhite('w-20', 'w-12', 'w-20', 'rounded-3xl') +
                skelStatCardWhite('w-16', 'w-12', 'w-20', 'rounded-3xl') +
                skelStatCardWhite('w-16', 'w-12', 'w-20', 'rounded-3xl') +
                skelStatCardWhite('w-16', 'w-12', 'w-20', 'rounded-3xl') +
            '</div>' +
            '<div class="bg-white rounded-3xl p-6 border border-slate-200/80 text-center space-y-3">' +
                '<div class="skeleton skeleton-circle w-8 h-8 mx-auto"></div>' +
                '<div class="skeleton skeleton-text w-48 h-4 mx-auto rounded"></div>' +
                '<div class="skeleton skeleton-text w-64 h-3 mx-auto opacity-50"></div>' +
            '</div>' +
            '<div class="space-y-3">' +
                '<div class="bg-white rounded-2xl p-5 border border-slate-200/80 flex items-center justify-between">' +
                    '<div class="space-y-2">' +
                        '<div class="skeleton skeleton-text w-36 h-5 rounded"></div>' +
                        '<div class="skeleton skeleton-text w-48 h-3 opacity-60"></div>' +
                    '</div>' +
                    '<div class="skeleton w-24 h-8 rounded-xl shrink-0"></div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    // 21. /siswa/riwayat
    function skelSiswaHistory() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-52', 'w-72') +
            '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">' +
                '<div class="sm:col-span-2 lg:col-span-5 bg-gradient-to-br from-emerald-800 via-emerald-800 to-emerald-900 rounded-3xl p-5 sm:p-6 text-white flex flex-col justify-between relative overflow-hidden">' +
                    '<div>' +
                        skelDark('w-32', 'h-3') +
                        skelDark('w-28', 'h-10', 'rounded-xl', 'my-3') +
                    '</div>' +
                    '<div class="w-full bg-white/15 rounded-full h-1.5 mt-2"><div class="skeleton-dark h-1.5 rounded-full w-4/5"></div></div>' +
                '</div>' +
                '<div class="sm:col-span-2 lg:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-4">' +
                    skelStatCardWhite('w-16', 'w-12', 'w-20', 'rounded-3xl') +
                    skelStatCardWhite('w-16', 'w-12', 'w-20', 'rounded-3xl') +
                    skelStatCardWhite('w-16', 'w-12', 'w-20', 'rounded-3xl') +
                '</div>' +
            '</div>' +
            skelFilterBar([{ type: 'search' }, { w: 'w-36' }, { w: 'w-36' }]) +
            skelTable([
                { w: 'w-24', rowW: 'w-28' },
                { w: 'w-28', rowW: 'w-32' },
                { w: 'w-24', rowW: 'w-28' },
                { w: 'w-16', align: 'center', rowW: 'w-16' },
                { w: 'w-16', align: 'center', rowW: 'w-16' },
                { w: 'w-16', align: 'center', badge: true, rowW: 'w-16' }
            ], 6) +
        '</div>';
    }

    // 22. /profile
    function skelProfile() {
        return '<div class="space-y-6">' +
            skelPageHeader('w-40', 'w-72') +
            '<div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 space-y-6">' +
                '<div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 pb-6 border-b border-slate-100 text-center sm:text-left">' +
                    '<div class="skeleton w-24 h-24 sm:w-28 sm:h-28 rounded-3xl shrink-0"></div>' +
                    '<div class="space-y-2 flex-1">' +
                        '<div class="flex items-center gap-2">' +
                            '<div class="skeleton skeleton-text w-44 h-6 rounded-lg"></div>' +
                            '<div class="skeleton w-24 h-5 rounded-full"></div>' +
                        '</div>' +
                        '<div class="skeleton skeleton-text w-36 h-3"></div>' +
                        '<div class="skeleton skeleton-text w-48 h-3"></div>' +
                    '</div>' +
                '</div>' +
                '<div class="space-y-4">' +
                    '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">' +
                        '<div class="space-y-1.5">' +
                            '<div class="skeleton skeleton-text w-24 h-3"></div>' +
                            '<div class="skeleton w-full h-10 rounded-xl"></div>' +
                        '</div>' +
                        '<div class="space-y-1.5">' +
                            '<div class="skeleton skeleton-text w-24 h-3"></div>' +
                            '<div class="skeleton w-full h-10 rounded-xl"></div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">' +
                        '<div class="space-y-1.5">' +
                            '<div class="skeleton skeleton-text w-32 h-3"></div>' +
                            '<div class="skeleton w-full h-10 rounded-xl"></div>' +
                        '</div>' +
                        '<div class="space-y-1.5">' +
                            '<div class="skeleton skeleton-text w-28 h-3"></div>' +
                            '<div class="skeleton w-full h-10 rounded-xl"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 space-y-4">' +
                '<div class="skeleton skeleton-text w-40 h-5 rounded"></div>' +
                '<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">' +
                    '<div class="space-y-1.5">' +
                        '<div class="skeleton skeleton-text w-28 h-3"></div>' +
                        '<div class="skeleton w-full h-10 rounded-xl"></div>' +
                    '</div>' +
                    '<div class="space-y-1.5">' +
                        '<div class="skeleton skeleton-text w-28 h-3"></div>' +
                        '<div class="skeleton w-full h-10 rounded-xl"></div>' +
                    '</div>' +
                    '<div class="space-y-1.5">' +
                        '<div class="skeleton skeleton-text w-36 h-3"></div>' +
                        '<div class="skeleton w-full h-10 rounded-xl"></div>' +
                    '</div>' +
                '</div>' +
                '<div class="skeleton w-36 h-10 rounded-xl mt-2"></div>' +
            '</div>' +
        '</div>';
    }

    // ── MASTER SKELETON DISPATCHER FOR ALL 23 ROUTES ──────────────────────────
    function getInstantSkeleton(url) {
        try {
            const u = new URL(url, window.location.origin);
            const path = u.pathname.replace(/\/$/, '') || '/';

            // 1. Portal Admin
            if (path === '/admin' || path === '/admin/dashboard') return skelAdminDashboard();
            if (path === '/admin/presensi-siswa') return skelAdminPresensiSiswa();
            if (path === '/admin/presensi-guru') return skelAdminPresensiGuru();
            if (path === '/admin/siswa') return skelAdminSiswa();
            if (/^\/admin\/siswa\/[^\/]+\/riwayat/.test(path)) return skelAdminSiswaRiwayat();
            if (path === '/admin/guru') return skelAdminGuru();
            if (/^\/admin\/guru\/[^\/]+\/riwayat/.test(path)) return skelAdminGuruRiwayat();
            if (path === '/admin/kelas') return skelAdminKelas();
            if (path === '/admin/mapel') return skelAdminMapel();
            if (path === '/admin/jadwal') return skelAdminJadwal();
            if (path === '/admin/laporan') return skelAdminLaporan();
            if (path === '/admin/lokasi') return skelAdminLokasi();
            if (path === '/admin/audit') return skelAdminAudit();

            // 2. Portal Dewan Guru
            if (path === '/guru' || path === '/guru/dashboard') return skelGuruDashboard();
            if (path === '/guru/scan') return skelGuruScan();
            if (path === '/guru/jadwal') return skelWeeklySchedule(false);
            if (path === '/guru/riwayat') return skelGuruHistory();
            if (/^\/guru\/sessions\/[^\/]+\/reconcile/.test(path)) return skelGuruReconcile();
            if (/^\/guru\/sessions\/[^\/]+/.test(path)) return skelGuruSessionLive();

            // 3. Portal Siswa
            if (path === '/siswa' || path === '/siswa/dashboard') return skelSiswaDashboard();
            if (path === '/siswa/jadwal') return skelWeeklySchedule(true);
            if (path === '/siswa/riwayat') return skelSiswaHistory();

            // 4. Common / Profile
            if (path === '/profile') return skelProfile();

        } catch (e) {}

        // Fallback generic table layout
        return skelAdminKelas();
    }

    // ── TRUE DEFERRED NAVIGATION ──────────────────────────────────────────────
    // Skeleton diinject INSTAN saat klik (sebelum fetch dimulai).
    // Data server menyusul dan langsung menggantikan skeleton saat response tiba.

    async function navigateTo(targetUrl, pushState = true, options = {}) {
        // Save current scroll position before leaving
        scrollPositions.set(window.location.href, { x: window.scrollX, y: window.scrollY });

        if (currentAbortController) {
            currentAbortController.abort();
        }
        currentAbortController = new AbortController();

        window.__isLiveSearching = Boolean(options.isLiveSearch);

        const mainContent = document.getElementById('main-content');

        // Inject skeleton instantly before fetch — every navigation hits server fresh
        if (mainContent && !options.isLiveSearch) {
            if (!options.restoreScroll) {
                window.scrollTo({ top: 0, behavior: 'instant' });
            }
            mainContent.classList.remove('spa-content-enter');
            mainContent.style.transition = 'none';
            mainContent.style.opacity = '1';
            mainContent.innerHTML = getInstantSkeleton(targetUrl);
        }

        if (!options.isLiveSearch) {
            startProgress();
        }

        window.dispatchEvent(new CustomEvent('app:before-page-unload', { detail: { targetUrl } }));
        await cleanupPageMedia();
        clearPageIntervals();
        triggerPageUnload();

        try {
            let html;
            let responseUrl = targetUrl;

            const response = await fetch(targetUrl, {
                signal: currentAbortController.signal,
                headers: {
                    'X-Partial-Nav': 'true',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-store'
            });

            if (response.redirected && response.url) {
                const redirectedUrl = new URL(response.url);
                if (redirectedUrl.pathname === '/login') {
                    window.location.href = response.url;
                    return;
                }
                responseUrl = response.url;
            }

            if (!response.ok) {
                window.location.href = targetUrl;
                return;
            }

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('text/html')) {
                window.location.href = targetUrl;
                return;
            }

            html = await response.text();

            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            const newMain = newDoc.getElementById('main-content');
            if (!newMain) {
                window.location.href = targetUrl;
                return;
            }

            await performDocumentUpdate(newDoc, responseUrl, options);

            if (pushState) {
                window.history.pushState({ spa: true, url: responseUrl }, newDoc.title, responseUrl);
            }

            finishProgress();
        } catch (err) {
            if (err.name === 'AbortError') return;
            console.warn('[MaarifSPA] Navigation failed, checking offline state:', err);
            finishProgress();
            if (!navigator.onLine) {
                if (window.toast && typeof window.toast.error === 'function') {
                    window.toast.error('Koneksi internet terputus.');
                }
            } else {
                window.location.href = targetUrl;
            }
        } finally {
            if (mainContent) mainContent.style.opacity = '1';
            setTimeout(() => { window.__isLiveSearching = false; }, 350);
        }
    }


    // Submit form asynchronously via SPA engine
    async function submitForm(form, submitter = null, options = {}) {
        if (!isEligibleForm(form)) {
            if (typeof form.requestSubmit === 'function') form.requestSubmit(submitter);
            else form.submit();
            return;
        }

        const method = (form.method || 'GET').toUpperCase();
        const action = form.getAttribute('action') || window.location.href;

        // 1. GET form handling (search & filters)
        if (method === 'GET') {
            const formData = new FormData(form);
            if (submitter && submitter.name) formData.append(submitter.name, submitter.value);
            const params = new URLSearchParams();
            for (const [key, val] of formData.entries()) {
                if (val !== '') params.append(key, val);
            }
            const cleanAction = action.split('?')[0];
            const targetUrl = cleanAction + (params.toString() ? '?' + params.toString() : '');
            navigateTo(targetUrl, true, {
                isLiveSearch: options.isLiveSearch,
                activeInputName: options.activeInputName,
                cursorStart: options.cursorStart,
                cursorEnd: options.cursorEnd
            });
            return;
        }

        // 2. POST / PUT / DELETE form handling (data mutations)

        const submitBtn = submitter || form.querySelector('button[type="submit"]');
        let originalBtnHtml = null;
        if (submitBtn) {
            originalBtnHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-wait');
            if (!options.isLiveSearch) {
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-3.5 w-3.5 text-current inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>${submitBtn.textContent.trim() || 'Menyimpan...'}</span>
                `;
            }
        }

        startProgress();

        try {
            const formData = new FormData(form);
            if (submitter && submitter.name && !formData.has(submitter.name)) {
                formData.append(submitter.name, submitter.value);
            }

            const response = await fetch(action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Partial-Nav': 'true',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.redirected && response.url) {
                const redirectUrl = new URL(response.url);
                if (redirectUrl.pathname === '/login') {
                    window.location.href = response.url;
                    return;
                }
            }

            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('text/html')) {
                const html = await response.text();
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');

                if (newDoc.getElementById('main-content')) {
                    const isSuccess = response.ok && !newDoc.querySelector('.is-invalid, [aria-invalid="true"]');
                    await performDocumentUpdate(newDoc, response.url || window.location.href, {
                        closeModals: isSuccess
                    });

                    if (response.url && response.url !== window.location.href) {
                        window.history.pushState({ spa: true, url: response.url }, newDoc.title, response.url);
                    }
                    finishProgress();
                    return;
                }
            }

            // Fallback for non-HTML or unexpected redirects
            if (response.redirected && response.url) {
                window.location.href = response.url;
            } else {
                window.location.reload();
            }
        } catch (err) {
            console.error('[MaarifSPA] Form submission error:', err);
            if (window.toast && typeof window.toast.error === 'function') {
                window.toast.error('Gagal mengirim data. Silakan periksa koneksi Anda.');
            }
        } finally {
            if (submitBtn && originalBtnHtml !== null) {
                submitBtn.innerHTML = originalBtnHtml;
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-75', 'cursor-wait');
                if (typeof window.reinitLucideIcons === 'function') window.reinitLucideIcons();
            }
            finishProgress();
        }
    }

    // Intercept click on links
    document.addEventListener('click', function(e) {
        if (e.defaultPrevented) return;
        if (e.button !== 0) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        const anchor = (e.target && typeof e.target.closest === 'function') ? e.target.closest('a') : null;
        if (!anchor) return;

        // Let confirm dialog handler manage links with data-confirm
        if (anchor.hasAttribute('data-confirm')) return;

        if (isEligibleLink(anchor)) {
            const url = new URL(anchor.href, window.location.origin);
            const isSamePage = (url.pathname === window.location.pathname && url.search === window.location.search);

            if (isSamePage) {
                // If it has a specific anchor hash (e.g. /page#section), allow standard in-page jump
                if (url.hash && url.hash !== '#') {
                    return;
                }

                // Prevent full browser page reload
                e.preventDefault();

                // If user is scrolled down, smoothly return to top
                if (window.scrollY > 20) {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    // Already at top: soft-refresh current page via SPA without full-page white flash
                    navigateTo(anchor.href, false);
                }
                return;
            }

            e.preventDefault();
            navigateTo(anchor.href, true);
        }
    }, false);

    // Intercept form submissions
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || !(form instanceof HTMLFormElement)) return;
        if (e.defaultPrevented) return;

        // If form has confirmation dialog and is not confirmed yet, let confirm-dialog handler manage it
        if (form.hasAttribute('data-confirm') && form.dataset.confirmed !== 'true') return;
        const submitter = e.submitter;
        if (submitter && submitter.hasAttribute('data-confirm') && form.dataset.confirmed !== 'true') return;

        if (isEligibleForm(form)) {
            e.preventDefault();
            submitForm(form, submitter);
        }
    }, false);

    // Live Search Debouncing on input fields
    let liveSearchTimer = null;
    document.addEventListener('input', function(e) {
        const input = e.target;
        if (!input || !(input instanceof HTMLInputElement)) return;
        if (input.type === 'password' || input.type === 'file' || input.type === 'checkbox' || input.type === 'radio') return;

        const isSearchField = input.name === 'search' || input.name === 'q' || input.hasAttribute('data-live-search');
        if (!isSearchField) return;

        const form = input.closest('form');
        if (!form || !isEligibleForm(form)) return;
        if ((form.method || 'GET').toUpperCase() !== 'GET') return;

        clearTimeout(liveSearchTimer);
        liveSearchTimer = setTimeout(() => {
            const cursorStart = input.selectionStart;
            const cursorEnd = input.selectionEnd;
            submitForm(form, null, {
                isLiveSearch: true,
                activeInputName: input.name,
                cursorStart,
                cursorEnd
            });
        }, 350);
    }, false);

    // Auto submit on <select> filter changes inside GET filter forms
    document.addEventListener('change', function(e) {
        const select = e.target;
        if (!select || !(select instanceof HTMLSelectElement)) return;
        const form = select.closest('form');
        if (!form || !isEligibleForm(form)) return;
        if ((form.method || 'GET').toUpperCase() !== 'GET') return;

        submitForm(form, null, { isLiveSearch: false });
    }, false);

    // Support Browser Back/Forward navigation with scroll restoration
    window.addEventListener('popstate', function(e) {
        const targetUrl = window.location.href;
        const savedScroll = scrollPositions.get(targetUrl);
        navigateTo(targetUrl, false, {
            restoreScroll: true,
            savedScroll: savedScroll || { x: 0, y: 0 }
        });
    });

    // Public API — no page cache anymore, every navigation hits server (fetch cache: no-store)
    const spaApi = {
        navigate: (url, pushState = true, options = {}) => navigateTo(url, pushState, options),
        submitForm: (form, submitter = null, options = {}) => submitForm(form, submitter, options),
        reload: () => navigateTo(window.location.href, false),
        clearCache: () => {}, // kept for backward compat, no-op (page cache removed)
        onPageLoad: (callback) => { if (typeof callback === 'function') pageLoadCallbacks.add(callback); },
        onPageUnload: (callback) => { if (typeof callback === 'function') pageUnloadCallbacks.add(callback); }
    };

    window.MaarifSPA = spaApi;
    window.MaarifNav = spaApi; // Backward compatibility

    // Enable page-scoped event listener tracking for all subsequently loaded scripts
    isTrackingPageListeners = true;

})();
</script>
