
document.addEventListener('DOMContentLoaded', function () {
    const AJAX_URL = bd_news_vars.ajax_url;
    const CACHE_KEY = 'bdnews_visitor_info';
    const CACHE_TTL = 30 * 60 * 1000; // ৩০ মিনিট

    // ডাটা আসার আগে ব্রাউজারের নিজের টাইমজোন ব্যবহার হবে
    let targetTimezone = undefined;

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value; // textContent, তাই XSS নিরাপদ
    }

    function updateIPClock() {
        try {
            const now = new Date();
            const localDate = new Intl.DateTimeFormat('en-US', {
                timeZone: targetTimezone,
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            }).format(now);
            const localTime = new Intl.DateTimeFormat('en-US', {
                timeZone: targetTimezone,
                hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true
            }).format(now);

            setText('v-date', localDate);
            setText('v-clock', localTime);
        } catch (err) {
            console.error('Timezone Error:', err);
        }
    }

    function applyInfo(info) {
        setText('v-ip', info.ip || 'N/A');
        setText('v-country', info.country || 'Unknown');
        setText('v-region', info.region || 'Unknown');
        setText('v-city', info.city || 'Unknown');
        setText('v-isp', info.isp || 'Unknown');
        setText('v-server', info.server || 'Unknown');
        if (info.timezone) {
            targetTimezone = info.timezone;
        }
        updateIPClock();
        // অন্য স্ক্রিপ্ট (যেমন ads-checker) চাইলে এই ইভেন্ট শুনতে পারবে
        document.dispatchEvent(new CustomEvent('bdnews:visitor-info', { detail: info }));
    }

    function readCache() {
        try {
            const raw = sessionStorage.getItem(CACHE_KEY);
            if (!raw) return null;
            const obj = JSON.parse(raw);
            if (obj && obj.t && (Date.now() - obj.t) < CACHE_TTL && obj.d) return obj.d;
        } catch (e) { }
        return null;
    }

    function writeCache(data) {
        try {
            sessionStorage.setItem(CACHE_KEY, JSON.stringify({ t: Date.now(), d: data }));
        } catch (e) { }
    }

    // ঘড়ি শুরু (প্রথমে লোকাল টাইমজোনে)
    updateIPClock();
    setInterval(updateIPClock, 1000);

    const cached = readCache();
    if (cached) {
        applyInfo(cached);
        return;
    }

    // POST ব্যবহার করা হয়েছে যাতে ক্যাশ/CDN রেসপন্স সংরক্ষণ না করে
    const body = new URLSearchParams({ action: 'bd_news_visitor_info' });
    fetch(AJAX_URL, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
    })
        .then(function (r) { return r.json(); })
        .then(function (json) {
            if (json && json.success && json.data) {
                // ব্যর্থ লুকআপ (IP আছে কিন্তু দেশ Unknown) সেশনে সেভ করা হবে না
                if (json.data.country && json.data.country !== 'Unknown') {
                    writeCache(json.data);
                }
                applyInfo(json.data);
            } else {
                applyInfo({});
            }
        })
        .catch(function () {
            applyInfo({});
        });
});
