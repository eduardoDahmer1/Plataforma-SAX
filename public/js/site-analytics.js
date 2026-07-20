(function () {
    'use strict';

    const endpoint = document.documentElement.dataset.analyticsEndpoint;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!endpoint || !csrf || location.pathname.startsWith('/admin')) return;

    let visitorId = localStorage.getItem('sax_analytics_visitor');
    if (!visitorId) {
        visitorId = (window.crypto?.randomUUID?.() || `${Date.now()}-${Math.random().toString(36).slice(2)}`);
        localStorage.setItem('sax_analytics_visitor', visitorId);
    }

    const deviceType = () => window.innerWidth < 768 ? 'mobile' : (window.innerWidth < 1024 ? 'tablet' : 'desktop');
    const referrerHost = () => {
        try { return document.referrer ? new URL(document.referrer).hostname : null; } catch (_) { return null; }
    };
    const clean = (value, max) => String(value || '').replace(/\s+/g, ' ').trim().slice(0, max);

    function send(eventType, extra) {
        fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: true,
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'},
            body: JSON.stringify(Object.assign({
                event_type: eventType,
                visitor_id: visitorId,
                path: location.pathname,
                page_title: clean(document.title, 255),
                device_type: deviceType(),
                referrer_host: referrerHost()
            }, extra || {}))
        }).catch(function () {});
    }

    send('page_view');

    document.addEventListener('click', function (event) {
        const element = event.target.closest('a, button, [data-analytics-click]');
        if (!element || element.closest('[data-no-analytics]')) return;

        const target = element.getAttribute('href') || element.dataset.analyticsClick || element.getAttribute('name') || element.id;
        const text = element.getAttribute('aria-label') || element.getAttribute('title') || element.innerText;
        if (!target && !text) return;

        send('click', {target: clean(target, 500), element_text: clean(text, 160)});
    }, true);
})();
