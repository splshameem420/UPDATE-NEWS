
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


document.addEventListener("DOMContentLoaded", function () {
    const menu = document.getElementById("primary-menu");
    const container = document.querySelector(".nav-menu-wrapper");
    const moreLi = document.querySelector(".more-menu-item");
    const headerActions = document.querySelector(".header-actions");

    if (!menu || !container || !moreLi) return;

    const subMenu = moreLi.querySelector(".sub-menu");
    const moreLink = moreLi.querySelector("a");

    function handleMenuOverflow() {
        // যদি স্ক্রিন মোবাইল সাইজ (৯৯২ পিক্সেল বা তার কম) হয়, তবে ডেস্কটপ ওভারফ্লো কোড কাজ করবে না
        if (window.innerWidth <= 992) {
            const hiddenItems = Array.from(subMenu.children);
            hiddenItems.forEach(item => {
                menu.insertBefore(item, moreLi);
            });
            moreLi.classList.add("hidden-more");
            return;
        }

        // --- ডেস্কটপ ওভারফ্লো লজিক ---
        const hiddenItems = Array.from(subMenu.children);
        hiddenItems.forEach(item => {
            menu.insertBefore(item, moreLi);
        });

        moreLi.classList.add("hidden-more");

        let actionsWidth = headerActions ? headerActions.offsetWidth : 280;
        let availableWidth = container.offsetWidth - actionsWidth - 30; 
        let currentWidth = 0;
        let hasOverflow = false;

        const items = Array.from(menu.children);

        items.forEach(item => {
            if (item === moreLi) return;

            let itemWidth = item.offsetWidth + 10; 

            if (currentWidth + itemWidth > availableWidth) {
                hasOverflow = true;
            }

            if (hasOverflow) {
                subMenu.appendChild(item);
            } else {
                currentWidth += itemWidth;
            }
        });

        if (hasOverflow) {
            moreLi.classList.remove("hidden-more");
        } else {
            moreLi.classList.add("hidden-more");
        }
    }

    window.addEventListener("resize", handleMenuOverflow);
    setTimeout(handleMenuOverflow, 50);

    if (moreLink) {
        moreLink.addEventListener("click", function (e) {
            e.preventDefault();
            moreLi.classList.toggle("active");
        });
    }

    document.addEventListener("click", function (e) {
        if (!moreLi.contains(e.target)) {
            moreLi.classList.remove("active");
        }
    });
});

// মোবাইল হ্যামবার্গার মেনু ড্রয়ার টগল স্ক্রিপ্ট
document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.getElementById("menu-toggle");
    const navWrapper = document.querySelector(".nav-menu-wrapper");

    if (menuToggle && navWrapper) {
        menuToggle.addEventListener("click", function (e) {
            e.stopPropagation();
            navWrapper.classList.toggle("toggled");
            document.body.classList.toggle("mobile-menu-active");

            const expanded = menuToggle.getAttribute("aria-expanded") === "true";
            menuToggle.setAttribute("aria-expanded", !expanded);
        });

        document.addEventListener("click", function (e) {
            if (!navWrapper.contains(e.target) && !menuToggle.contains(e.target)) {
                navWrapper.classList.remove("toggled");
                document.body.classList.remove("mobile-menu-active");
                menuToggle.setAttribute("aria-expanded", "false");
            }
        });
    }
});