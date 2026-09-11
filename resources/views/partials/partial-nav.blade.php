<!-- Native SPA Runtime Engine (Ma'arif Seamless Navigation & Interactive Forms) -->
<div id="pjax-progress-bar" class="fixed top-0 left-0 right-0 z-[99999] h-[3px] pointer-events-none opacity-0 transition-all duration-200" style="width: 0%; background: linear-gradient(90deg, #10b981 0%, #34d399 50%, #f59e0b 100%); box-shadow: 0 0 10px rgba(16, 185, 129, 0.8);"></div>

<script>
(function() {
    'use strict';

    const progressBar = document.getElementById('pjax-progress-bar');
    let currentAbortController = null;
    let progressTimer = null;

    // Lightweight HTML cache for rapid back-and-forth (30s TTL)
    const pageCache = new Map(); // url -> {html, ts}
    const CACHE_TTL_MS = 30000;
    const prefetchInFlight = new Set();

    // Scroll positions map for precise Back/Forward scroll restoration
    const scrollPositions = new Map(); // url -> {x, y}

    // Lifecycle callbacks registered by pages
    const pageUnloadCallbacks = new Set();
    const pageLoadCallbacks = new Set();

    function getCachedHtml(url) {
        const entry = pageCache.get(url);
        if (!entry) return null;
        if (Date.now() - entry.ts > CACHE_TTL_MS) {
            pageCache.delete(url);
            return null;
        }
        return entry.html;
    }

    function setCachedHtml(url, html) {
        if (pageCache.size >= 25) {
            const firstKey = pageCache.keys().next().value;
            pageCache.delete(firstKey);
        }
        pageCache.set(url, { html, ts: Date.now() });
    }

    function clearCache() {
        pageCache.clear();
    }

    async function prefetchUrl(url) {
        if (prefetchInFlight.has(url) || getCachedHtml(url)) return;
        try {
            prefetchInFlight.add(url);
            const res = await fetch(url, {
                headers: { 'X-Partial-Nav': 'true', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const ct = res.headers.get('content-type') || '';
            if (!ct.includes('text/html')) return;
            const html = await res.text();
            if (html.includes('id="main-content"')) setCachedHtml(url, html);
        } catch(e) {} finally {
            prefetchInFlight.delete(url);
        }
    }

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

    function cleanupPageMedia() {
        try {
            const videos = document.querySelectorAll('video');
            videos.forEach(v => {
                if (v.srcObject && typeof v.srcObject.getTracks === 'function') {
                    v.srcObject.getTracks().forEach(track => track.stop());
                }
            });
            if (typeof window.html5QrCode !== 'undefined' && window.html5QrCode && typeof window.html5QrCode.stop === 'function') {
                window.html5QrCode.stop().catch(() => {});
            }
        } catch(e) {}
    }

    function triggerPageUnload() {
        pageUnloadCallbacks.forEach(cb => {
            try { cb(); } catch(e) { console.error('[MaarifSPA] Unload callback error:', e); }
        });
        pageUnloadCallbacks.clear();
    }

    function triggerPageLoad(url) {
        pageLoadCallbacks.forEach(cb => {
            try { cb({ url }); } catch(e) { console.error('[MaarifSPA] Load callback error:', e); }
        });
    }

    function startProgress() {
        if (!progressBar) return;
        if (progressTimer) clearInterval(progressTimer);
        
        progressBar.style.transition = 'width 0.2s ease, opacity 0.15s ease';
        progressBar.style.opacity = '1';
        progressBar.style.width = '25%';

        let currentWidth = 25;
        progressTimer = setInterval(() => {
            if (currentWidth < 85) {
                currentWidth += Math.random() * 12;
                progressBar.style.width = currentWidth + '%';
            }
        }, 150);
    }

    function finishProgress() {
        if (!progressBar) return;
        if (progressTimer) clearInterval(progressTimer);

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

    function executeScriptSafely(scriptEl) {
        const newScript = document.createElement('script');
        Array.from(scriptEl.attributes).forEach(attr => {
            newScript.setAttribute(attr.name, attr.value);
        });

        if (scriptEl.src) {
            document.head.appendChild(newScript);
        } else {
            const rawCode = scriptEl.textContent || '';
            const sanitizedCode = rawCode.replace(/(^|[;\r\n])\s*(?:const|let)\s+/g, '$1var ');
            newScript.textContent = sanitizedCode;
            document.body.appendChild(newScript);
            newScript.remove();
        }
    }

    // Apply DOM updates from newly parsed document
    function applyDocumentUpdates(newDoc, targetUrl, options = {}) {
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

        // 9. Update and Execute Page-Specific Scripts
        const newPageScripts = newDoc.getElementById('page-scripts-container');
        if (newPageScripts) {
            const scripts = newPageScripts.querySelectorAll('script');
            scripts.forEach(script => executeScriptSafely(script));
        }

        if (mainContent) {
            const inlineScripts = mainContent.querySelectorAll('script');
            inlineScripts.forEach(script => executeScriptSafely(script));
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

    // ── SKELETON TEMPLATES per rute ──────────────────────────────────────────
    // HTML diinject ke DOM SEBELUM fetch dimulai — halaman terasa ganti INSTAN

    function skeletonTableRows(count, colDefs) {
        let rows = '';
        for (let i = 0; i < count; i++) {
            rows += '<tr class="border-b border-slate-100">';
            colDefs.forEach(function(col) {
                rows += '<td class="py-3.5 px-5">';
                if (col.avatar) {
                    rows += '<div class="flex items-center gap-3">' +
                        '<div class="skeleton skeleton-circle w-8 h-8 rounded-lg shrink-0"></div>' +
                        '<div class="space-y-1.5 flex-1">' +
                            '<div class="skeleton skeleton-text ' + (col.line1 || 'w-32') + '"></div>' +
                            '<div class="skeleton skeleton-text ' + (col.line2 || 'w-24') + ' opacity-60"></div>' +
                        '</div></div>';
                } else {
                    rows += '<div class="skeleton skeleton-text ' + (col.cls || 'w-28') + '"></div>';
                    if (col.sub) {
                        rows += '<div class="skeleton skeleton-text ' + col.sub + ' mt-1 opacity-60"></div>';
                    }
                }
                rows += '</td>';
            });
            rows += '</tr>';
        }
        return rows;
    }

    function skeletonTheadCells(widths) {
        return widths.map(function(w) {
            return '<th class="py-3 px-5"><div class="skeleton skeleton-text ' + w + ' h-2.5 opacity-50"></div></th>';
        }).join('');
    }

    function skeletonPageHeader(hasBtn) {
        var btn = hasBtn
            ? '<div class="skeleton w-32 h-10 rounded-xl shrink-0"></div>'
            : '<div class="skeleton w-28 h-8 rounded-xl shrink-0"></div>';
        return '<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">' +
            '<div class="space-y-2">' +
                '<div class="skeleton skeleton-text w-48 h-7 rounded-lg"></div>' +
                '<div class="skeleton skeleton-text w-64 h-3 opacity-60"></div>' +
            '</div>' + btn +
        '</div>';
    }

    function skeletonFilterBar(hasSearch, selectCount) {
        var html = '<div class="bg-white rounded-2xl border border-slate-200/80 p-4"><div class="flex flex-wrap items-center gap-3">';
        if (hasSearch) {
            html += '<div class="skeleton w-52 h-9 rounded-xl flex-1 min-w-[200px] max-w-xs"></div>';
        }
        for (var i = 0; i < selectCount; i++) {
            html += '<div class="skeleton w-32 h-9 rounded-xl"></div>';
        }
        html += '<div class="skeleton w-20 h-9 rounded-xl"></div>';
        html += '</div></div>';
        return html;
    }

    function skeletonTable(colDefs, count, theadWidths) {
        var thead = theadWidths
            ? skeletonTheadCells(theadWidths)
            : colDefs.map(function() { return '<th class="py-3 px-5"><div class="skeleton skeleton-text w-16 h-2.5 opacity-50"></div></th>'; }).join('');
        return '<div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">' +
            '<div class="overflow-x-auto"><table class="w-full text-left border-collapse text-xs">' +
                '<thead><tr class="bg-slate-50 border-b border-slate-100">' + thead + '</tr></thead>' +
                '<tbody class="divide-y divide-slate-100">' + skeletonTableRows(count, colDefs) + '</tbody>' +
            '</table></div>' +
        '</div>';
    }

    function skeletonStatGrid(cols, height) {
        var cards = '';
        for (var i = 0; i < cols; i++) {
            cards += '<div class="skeleton rounded-2xl" style="height:' + (height || 120) + 'px"></div>';
        }
        return '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-' + cols + ' gap-4">' + cards + '</div>';
    }

    function getInstantSkeleton(url) {
        try {
            var path = (new URL(url, window.location.origin)).pathname.replace(/\/$/, '');

            // ── /admin atau /admin/dashboard ──
            if (path === '/admin' || path === '/admin/dashboard') {
                return '<div class="space-y-7">' +
                    '<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-1.5">' +
                        '<div class="space-y-2">' +
                            '<div class="skeleton skeleton-text w-36 h-3 opacity-60"></div>' +
                            '<div class="skeleton skeleton-text w-40 h-7 rounded-lg"></div>' +
                            '<div class="skeleton skeleton-text w-56 h-3 opacity-50"></div>' +
                        '</div>' +
                        '<div class="skeleton w-24 h-8 rounded-lg shrink-0"></div>' +
                    '</div>' +
                    '<div class="grid grid-cols-1 lg:grid-cols-5 gap-4">' +
                        '<div class="lg:col-span-2 skeleton rounded-2xl" style="height:160px"></div>' +
                        '<div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-4">' +
                            '<div class="skeleton rounded-2xl" style="height:140px"></div>' +
                            '<div class="skeleton rounded-2xl" style="height:140px"></div>' +
                            '<div class="skeleton rounded-2xl" style="height:140px"></div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">' +
                        '<div class="skeleton rounded-2xl" style="height:110px"></div>' +
                        '<div class="skeleton rounded-2xl" style="height:110px"></div>' +
                        '<div class="skeleton rounded-2xl" style="height:110px"></div>' +
                        '<div class="skeleton rounded-2xl" style="height:110px"></div>' +
                    '</div>' +
                    '<div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden">' +
                        '<div class="p-5 border-b border-slate-100 flex items-center gap-3">' +
                            '<div class="skeleton w-1 h-8 rounded-full shrink-0"></div>' +
                            '<div class="space-y-1.5">' +
                                '<div class="skeleton skeleton-text w-40 h-4 rounded"></div>' +
                                '<div class="skeleton skeleton-text w-56 h-3 opacity-50"></div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="overflow-x-auto"><table class="w-full text-xs border-collapse">' +
                            '<thead><tr class="bg-slate-50 border-b border-slate-100">' +
                                skeletonTheadCells(['w-20','w-24','w-28','w-20','w-16']) +
                            '</tr></thead>' +
                            '<tbody class="divide-y divide-slate-100">' +
                                skeletonTableRows(6, [
                                    { avatar: true, line1: 'w-28', line2: 'w-20' },
                                    { cls: 'w-20' }, { cls: 'w-24' }, { cls: 'w-16' }, { cls: 'w-14' }
                                ]) +
                            '</tbody>' +
                        '</table></div>' +
                    '</div>' +
                '</div>';
            }

            // ── /admin/siswa ──
            if (path === '/admin/siswa') {
                return '<div class="space-y-6">' +
                    skeletonPageHeader(true) +
                    skeletonFilterBar(true, 1) +
                    skeletonTable([
                        { cls: 'w-24' },
                        { avatar: true, line1: 'w-32', line2: 'w-28' },
                        { cls: 'w-20' }, { cls: 'w-20' }, { cls: 'w-24' }, { cls: 'w-12' }, { cls: 'w-20' }
                    ], 8) +
                '</div>';
            }

            // ── /admin/guru ──
            if (path === '/admin/guru') {
                return '<div class="space-y-6">' +
                    skeletonPageHeader(true) +
                    skeletonFilterBar(true, 0) +
                    skeletonTable([
                        { cls: 'w-28' },
                        { avatar: true, line1: 'w-36', line2: 'w-28' },
                        { cls: 'w-20' }, { cls: 'w-24' }, { cls: 'w-12' }, { cls: 'w-20' }
                    ], 8) +
                '</div>';
            }

            // ── /admin/kelas ──
            if (path === '/admin/kelas') {
                return '<div class="space-y-6">' +
                    skeletonPageHeader(true) +
                    skeletonTable([
                        { cls: 'w-24' }, { cls: 'w-32' }, { cls: 'w-28' }, { cls: 'w-14' }, { cls: 'w-20' }
                    ], 6) +
                '</div>';
            }

            // ── /admin/mapel ──
            if (path === '/admin/mapel') {
                return '<div class="space-y-6">' +
                    skeletonPageHeader(true) +
                    skeletonTable([
                        { cls: 'w-16' }, { cls: 'w-40' }, { cls: 'w-12' }, { cls: 'w-20' }
                    ], 6) +
                '</div>';
            }

            // ── /admin/jadwal ──
            if (path === '/admin/jadwal') {
                return '<div class="space-y-6">' +
                    skeletonPageHeader(true) +
                    skeletonFilterBar(false, 2) +
                    skeletonTable([
                        { cls: 'w-16' }, { cls: 'w-28' }, { cls: 'w-20' }, { cls: 'w-32' },
                        { avatar: true, line1: 'w-28', line2: 'w-20' }, { cls: 'w-20' }
                    ], 8) +
                '</div>';
            }

            // ── /admin/presensi-siswa ──
            if (path === '/admin/presensi-siswa') {
                return '<div class="space-y-6">' +
                    skeletonPageHeader(false) +
                    skeletonStatGrid(4, 120) +
                    skeletonFilterBar(true, 3) +
                    skeletonTable([
                        { avatar: true, line1: 'w-28', line2: 'w-20' },
                        { cls: 'w-24', sub: 'w-20' },
                        { avatar: true, line1: 'w-24', line2: 'w-16' },
                        { cls: 'w-16' }, { cls: 'w-20' }, { cls: 'w-12' }, { cls: 'w-20' }
                    ], 10) +
                '</div>';
            }

            // ── /admin/presensi-guru ──
            if (path === '/admin/presensi-guru') {
                return '<div class="space-y-6">' +
                    skeletonPageHeader(false) +
                    '<div class="grid grid-cols-1 md:grid-cols-4 gap-4">' +
                        '<div class="skeleton rounded-2xl" style="height:120px"></div>' +
                        '<div class="skeleton rounded-2xl" style="height:120px"></div>' +
                        '<div class="skeleton rounded-2xl" style="height:120px"></div>' +
                        '<div class="skeleton rounded-2xl" style="height:120px"></div>' +
                    '</div>' +
                    skeletonFilterBar(true, 2) +
                    skeletonTable([
                        { avatar: true, line1: 'w-32', line2: 'w-24' },
                        { cls: 'w-20' }, { cls: 'w-16' }, { cls: 'w-16' }, { cls: 'w-28' }, { cls: 'w-20' }
                    ], 10) +
                '</div>';
            }

            // ── /admin/audit ──
            if (path === '/admin/audit') {
                return '<div class="space-y-6">' +
                    '<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">' +
                        '<div class="space-y-2">' +
                            '<div class="skeleton skeleton-text w-52 h-7 rounded-lg"></div>' +
                            '<div class="skeleton skeleton-text w-64 h-3 opacity-60"></div>' +
                        '</div>' +
                        '<div class="skeleton w-36 h-8 rounded-xl shrink-0"></div>' +
                    '</div>' +
                    skeletonTable([
                        { cls: 'w-28' },
                        { avatar: true, line1: 'w-28', line2: 'w-20' },
                        { cls: 'w-24' },
                        { avatar: true, line1: 'w-24', line2: 'w-16' },
                        { cls: 'w-36' }
                    ], 8) +
                '</div>';
            }

            // ── /guru/history ──
            if (path === '/guru/history') {
                return '<div class="space-y-6">' +
                    '<div>' +
                        '<div class="skeleton skeleton-text w-52 h-8 rounded-lg"></div>' +
                        '<div class="skeleton skeleton-text w-72 h-3 mt-2 opacity-60"></div>' +
                    '</div>' +
                    '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">' +
                        '<div class="sm:col-span-2 lg:col-span-5 skeleton rounded-3xl" style="height:160px"></div>' +
                        '<div class="sm:col-span-2 lg:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-4">' +
                            '<div class="skeleton rounded-3xl" style="height:150px"></div>' +
                            '<div class="skeleton rounded-3xl" style="height:150px"></div>' +
                            '<div class="skeleton rounded-3xl" style="height:150px"></div>' +
                        '</div>' +
                    '</div>' +
                    skeletonFilterBar(true, 2) +
                    '<div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden">' +
                        '<div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-3">' +
                            '<div class="space-y-1.5">' +
                                '<div class="skeleton skeleton-text w-36 h-4 rounded"></div>' +
                                '<div class="skeleton skeleton-text w-24 h-3 opacity-50"></div>' +
                            '</div>' +
                            '<div class="skeleton w-28 h-8 rounded-xl"></div>' +
                        '</div>' +
                        '<div class="overflow-x-auto"><table class="w-full text-xs border-collapse">' +
                            '<thead><tr class="bg-slate-50 border-b border-slate-100">' +
                                skeletonTheadCells(['w-24','w-20','w-20','w-28','w-16','w-20']) +
                            '</tr></thead>' +
                            '<tbody class="divide-y divide-slate-100">' +
                                skeletonTableRows(8, [
                                    { cls: 'w-24' }, { cls: 'w-20' }, { cls: 'w-20' },
                                    { cls: 'w-28' }, { cls: 'w-16' }, { cls: 'w-20' }
                                ]) +
                            '</tbody>' +
                        '</table></div>' +
                    '</div>' +
                '</div>';
            }

        } catch (e) {}

        // Fallback generik
        return '<div class="space-y-6">' +
            skeletonPageHeader(true) +
            skeletonTable([
                { cls: 'w-24' }, { cls: 'w-40' }, { cls: 'w-28' }, { cls: 'w-20' }, { cls: 'w-20' }
            ], 7) +
        '</div>';
    }

    // ── TRUE DEFERRED NAVIGATION ──────────────────────────────────────────────
    // Skeleton diinject INSTAN saat klik (sebelum fetch dimulai).
    // Data server menyusul dan langsung menggantikan skeleton saat response tiba.

    async function navigateTo(targetUrl, pushState = true, options = {}) {
        // Save current scroll position before leaving
        scrollPositions.set(window.location.href, { x: window.scrollX, y: window.scrollY });

        const cachedHtml = options.skipCache ? null : getCachedHtml(targetUrl);

        if (currentAbortController) {
            currentAbortController.abort();
        }
        currentAbortController = new AbortController();

        window.__isLiveSearching = Boolean(options.isLiveSearch);

        const mainContent = document.getElementById('main-content');

        // ── DEFERRED: inject skeleton SEBELUM fetch jika tidak ada cache ──
        if (!cachedHtml && mainContent && !options.isLiveSearch) {
            mainContent.style.transition = 'none';
            mainContent.style.opacity = '1';
            mainContent.innerHTML = getInstantSkeleton(targetUrl);
        }

        if (!options.isLiveSearch) {
            startProgress();
        }

        window.dispatchEvent(new CustomEvent('app:before-page-unload', { detail: { targetUrl } }));
        cleanupPageMedia();
        clearPageIntervals();
        triggerPageUnload();

        try {
            let html;
            let responseUrl = targetUrl;

            if (cachedHtml) {
                html = cachedHtml;
            } else {
                const response = await fetch(targetUrl, {
                    signal: currentAbortController.signal,
                    headers: {
                        'X-Partial-Nav': 'true',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
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
                if (html.includes('id="main-content"')) {
                    setCachedHtml(targetUrl, html);
                }
            }

            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            const newMain = newDoc.getElementById('main-content');
            if (!newMain) {
                window.location.href = targetUrl;
                return;
            }

            applyDocumentUpdates(newDoc, responseUrl, options);

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
                cursorEnd: options.cursorEnd,
                skipCache: true
            });
            return;
        }

        // 2. POST / PUT / DELETE form handling (data mutations)
        clearCache(); // Invalidate cache on mutations

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
                    applyDocumentUpdates(newDoc, response.url || window.location.href, {
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

    // Prefetch on hover/focus/touchstart for instant feel
    let prefetchTimer = null;
    function schedulePrefetch(anchor) {
        if (!isEligibleLink(anchor)) return;
        const href = anchor.href;
        try {
            const url = new URL(href, window.location.origin);
            if (url.pathname === window.location.pathname && url.search === window.location.search) return;
        } catch(e) {}
        if (getCachedHtml(href) || prefetchInFlight.has(href)) return;
        clearTimeout(prefetchTimer);
        prefetchTimer = setTimeout(() => prefetchUrl(href), 70);
    }

    document.addEventListener('mouseenter', function(e) {
        const a = e.target.closest('a');
        if (a) schedulePrefetch(a);
    }, true);

    document.addEventListener('focusin', function(e) {
        const a = e.target.closest('a');
        if (a) schedulePrefetch(a);
    }, true);

    document.addEventListener('touchstart', function(e) {
        const a = e.target.closest('a');
        if (a) schedulePrefetch(a);
    }, { passive: true, capture: true });

    // Intercept click on links
    document.addEventListener('click', function(e) {
        if (e.defaultPrevented) return;
        if (e.button !== 0) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        const anchor = e.target.closest('a');
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
                    navigateTo(anchor.href, false, { skipCache: true });
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

    // Public API
    const spaApi = {
        navigate: (url, pushState = true, options = {}) => navigateTo(url, pushState, options),
        submitForm: (form, submitter = null, options = {}) => submitForm(form, submitter, options),
        reload: () => navigateTo(window.location.href, false, { skipCache: true }),
        clearCache: clearCache,
        onPageLoad: (callback) => { if (typeof callback === 'function') pageLoadCallbacks.add(callback); },
        onPageUnload: (callback) => { if (typeof callback === 'function') pageUnloadCallbacks.add(callback); }
    };

    window.MaarifSPA = spaApi;
    window.MaarifNav = spaApi; // Backward compatibility

})();
</script>
