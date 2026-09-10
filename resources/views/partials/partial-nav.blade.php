<!-- Partial Navigation Engine (Seamless Navigation for Sidebar & Bottom Bar) -->
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
        // Simple LRU cap 20 entries
        if (pageCache.size >= 20) {
            const firstKey = pageCache.keys().next().value;
            pageCache.delete(firstKey);
        }
        pageCache.set(url, { html, ts: Date.now() });
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
            // Only cache if it looks like a partial-nav compatible page
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
        // Stop any active camera streams when navigating away
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
        
        // Ignore links with target (like _blank)
        if (anchor.target && anchor.target !== '_self') return false;
        
        // Ignore explicit opt-outs
        if (anchor.hasAttribute('download') || anchor.hasAttribute('data-no-pjax') || anchor.hasAttribute('data-native')) return false;

        // If link has confirmation dialog, let the confirmation handler manage it
        if (anchor.hasAttribute('data-confirm')) return false;

        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            return false;
        }

        try {
            const url = new URL(anchor.href, window.location.origin);
            // Must be same origin
            if (url.origin !== window.location.origin) return false;

            // Ignore if exact same pathname and search (anchor scroll on page)
            if (url.pathname === window.location.pathname && url.search === window.location.search) {
                return false;
            }

            // Exclude static assets or file download endpoints
            const path = url.pathname.toLowerCase();
            if (/\.(pdf|xlsx|xls|csv|zip|png|jpe?g|svg|webp)$/.test(path)) return false;
            if (path.includes('/export') || path.includes('/download') || path.includes('/cetak')) return false;

            return true;
        } catch (e) {
            return false;
        }
    }

    function executeScriptSafely(scriptEl) {
        const newScript = document.createElement('script');
        Array.from(scriptEl.attributes).forEach(attr => {
            newScript.setAttribute(attr.name, attr.value);
        });

        if (scriptEl.src) {
            // External script: append to head
            document.head.appendChild(newScript);
        } else {
            // Inline script:
            // Convert top-level `const ` and `let ` to `var ` so re-declaring on returning to the page won't cause SyntaxError
            const rawCode = scriptEl.textContent || '';
            const sanitizedCode = rawCode.replace(/(^|[;\r\n])\s*(?:const|let)\s+/g, '$1var ');

            newScript.textContent = sanitizedCode;
            document.body.appendChild(newScript);
            newScript.remove(); // Keep execution active, clean DOM
        }
    }

    async function navigateTo(targetUrl, pushState = true) {
        const cachedHtml = getCachedHtml(targetUrl);

        if (currentAbortController) {
            currentAbortController.abort();
        }
        currentAbortController = new AbortController();

        const mainContent = document.getElementById('main-content');
        // Only dim if we need to fetch (cached = instant, no dim)
        const shouldDim = !cachedHtml;
        if (shouldDim && mainContent) {
            mainContent.style.transition = 'opacity 0.08s ease';
            mainContent.style.opacity = '0.7';
        }

        startProgress();

        // 1. Dispatch lifecycle event and cleanup previous page tasks
        window.dispatchEvent(new CustomEvent('app:before-page-unload', { detail: { targetUrl } }));
        cleanupPageMedia();
        clearPageIntervals();

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
                    window.location.href = response.url;
                    return;
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
                // Cache for rapid revisit
                if (html.includes('id="main-content"')) setCachedHtml(targetUrl, html);
                responseUrl = response.url || targetUrl;
            }

            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            const newMain = newDoc.getElementById('main-content');
            if (!newMain) {
                // Not a compatible layout, fallback to full reload
                window.location.href = targetUrl;
                return;
            }

            // 1. Update Document Title
            document.title = newDoc.title;

            // 2. Update Desktop Header Title if available
            const curDesktopTitle = document.getElementById('desktop-header-title');
            const newDesktopTitle = newDoc.getElementById('desktop-header-title');
            if (curDesktopTitle && newDesktopTitle) {
                curDesktopTitle.innerHTML = newDesktopTitle.innerHTML;
            }

            // 3. Update Mobile Header Title if available
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
                window.scrollTo({ top: 0, behavior: 'instant' });
                mainContent.style.opacity = '1';
            }

            // 7. Update Page-Specific Styles (if any newly provided in head / stack)
            const newPageStyles = newDoc.getElementById('page-styles-container');
            const curPageStyles = document.getElementById('page-styles-container');
            if (curPageStyles && newPageStyles) {
                curPageStyles.innerHTML = newPageStyles.innerHTML;
            }

            // 8. Update and Execute Page-Specific Scripts
            const newPageScripts = newDoc.getElementById('page-scripts-container');
            if (newPageScripts) {
                const scripts = newPageScripts.querySelectorAll('script');
                scripts.forEach(script => executeScriptSafely(script));
            }

            // Also check for any inline scripts inside main content
            if (mainContent) {
                const inlineScripts = mainContent.querySelectorAll('script');
                inlineScripts.forEach(script => executeScriptSafely(script));
            }

            // 9. Reinitialize Lucide Icons (Vite bundle exposes window.reinitLucideIcons)
            if (typeof window.reinitLucideIcons === 'function') {
                try { window.reinitLucideIcons(); } catch(e) {}
            } else if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }

            // 10. Update History State
            if (pushState) {
                window.history.pushState({ partialNav: true, url: targetUrl }, newDoc.title, targetUrl);
            }

            // 11. Dispatch DOMContentLoaded & Custom page:loaded events for page scripts
            try {
                const dclEvent = new Event('DOMContentLoaded', { bubbles: true, cancelable: true });
                document.dispatchEvent(dclEvent);
                window.dispatchEvent(dclEvent);
                window.dispatchEvent(new CustomEvent('app:page-loaded', { detail: { url: targetUrl } }));
            } catch(e) {}

            finishProgress();
        } catch (err) {
            if (err.name === 'AbortError') {
                // Swallow abort — new navigation already in progress which will handle UI
                return;
            }
            console.warn('[PartialNav] Error fetching page, fallback to standard reload:', err);
            finishProgress();
            window.location.href = targetUrl;
        } finally {
            if (mainContent) {
                mainContent.style.opacity = '1';
            }
        }
    }

    // Prefetch on hover/focus for instant feel (only eligible links)
    let prefetchTimer = null;
    function schedulePrefetch(anchor) {
        if (!isEligibleLink(anchor)) return;
        const href = anchor.href;
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
        // If clicking inside a form or button that submits, ignore
        if (e.defaultPrevented) return;
        if (e.button !== 0) return; // Only left clicks
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return; // Allow open in new tab

        const anchor = e.target.closest('a');
        if (!anchor) return;

        if (isEligibleLink(anchor)) {
            e.preventDefault();
            navigateTo(anchor.href, true);
        }
    }, false);

    // Support Browser Back/Forward navigation
    window.addEventListener('popstate', function(e) {
        navigateTo(window.location.href, false);
    });

    // Expose API globally
    window.MaarifNav = {
        navigate: navigateTo,
        reload: () => navigateTo(window.location.href, false)
    };

})();
</script>
