<?php
/**
 * Ad Blocker / Ad-blocking DNS & Proxy Detector
 * File: inc/ads-checker.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// wp-config.php তে define( 'BDNEWS_ADS_WALL', false ); দিলে পুরো ফিচার বন্ধ হবে
if ( defined( 'BDNEWS_ADS_WALL' ) && ! BDNEWS_ADS_WALL ) {
    return;
}

add_action( 'wp_footer', 'bd_news_ads_checker_output', 99 );

function bd_news_ads_checker_output() {
    // অ্যাডমিন, কাস্টমাইজার প্রিভিউ আর সব ধরনের বটকে বাদ
    if ( is_admin() || is_customize_preview() || current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( ! empty( $GLOBALS['is_bot'] ) || ! empty( $GLOBALS['is_good_bot'] ) || ! empty( $GLOBALS['is_preview_bot'] ) ) {
        return;
    }

    // যে বিজ্ঞাপন স্ক্রিপ্টগুলো পরীক্ষা করা হবে (অন্য নেটওয়ার্ক যোগ করতে এই filter ব্যবহার করুন)
    $bait_urls = apply_filters(
        'bd_news_ads_bait_urls',
        array(
            'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js',
            'https://adservice.google.com/adsid/integrator.js',
        )
    );
    $bait_urls = array_values( array_filter( array_map( 'esc_url_raw', (array) $bait_urls ) ) );

    $config = array(
        'urls'    => $bait_urls,
        // নিজের সাইটের ফাইল (পেজের সাথে একই স্কিমে পরীক্ষা হবে)
        'control' => wp_parse_url( includes_url( 'images/blank.gif' ), PHP_URL_PATH ),
        'title'   => 'Ad Blocker Detected!',
        'message' => 'This site is supported by ads. Please turn off your ad blocker, ad-blocking DNS or proxy for this site, then refresh the page.',
        'steps'   => array(
            'Disable the ad blocker extension for this site',
            'Turn off ad-blocking DNS (AdGuard DNS, NextDNS, Private DNS) or proxy',
            'Refresh the page',
        ),
        'button'  => '🔄 I Have Disabled It, Refresh',
    );
    ?>
    <script>
    (function () {
        'use strict';
        var CFG = <?php echo wp_json_encode( $config ); ?>;
        var DEBUG = /[?&]adcheck=1/.test(window.location.search);
        var wall = null;
        var shown = false;

        function el(tag, css, text) {
            var n = document.createElement(tag);
            if (css) { n.style.cssText = css; }
            if (text) { n.textContent = text; }
            return n;
        }

        // রিকোয়েস্ট সফল = 'ok', দ্রুত ব্যর্থ = 'fail', সময় শেষ = 'timeout' (অনিশ্চিত)
        function reach(url, ms) {
            return new Promise(function (resolve) {
                if (typeof fetch !== 'function') { resolve('timeout'); return; }
                var done = false;
                var t = setTimeout(function () {
                    if (!done) { done = true; resolve('timeout'); }
                }, ms);
                fetch(url, { method: 'GET', mode: 'no-cors', credentials: 'omit' })
                    .then(function () { if (!done) { done = true; clearTimeout(t); resolve('ok'); } })
                    .catch(function () { if (!done) { done = true; clearTimeout(t); resolve('fail'); } });
            });
        }

        // টোপ এলিমেন্ট: অ্যাডব্লকার লুকিয়ে ফেললে true
        function baitBlocked() {
            return new Promise(function (resolve) {
                var b = document.createElement('div');
                b.className = 'adsbox ad-banner ad-placement textads banner-ads pub_300x250';
                b.style.cssText = 'position:absolute;left:-9999px;top:-9999px;width:10px;height:10px;';
                b.innerHTML = '&nbsp;';
                document.body.appendChild(b);
                setTimeout(function () {
                    var cs = window.getComputedStyle(b);
                    var hidden = b.offsetHeight === 0 || cs.display === 'none' || cs.visibility === 'hidden';
                    if (b.parentNode) { b.parentNode.removeChild(b); }
                    resolve(hidden);
                }, 150);
            });
        }

        function detect() {
            return Promise.all([
                baitBlocked(),
                reach(window.location.origin + CFG.control, 4000),
                Promise.all(CFG.urls.map(function (u) { return reach(u, 5000); }))
            ]).then(function (r) {
                var blocked = r[0] || (r[1] === 'ok' && r[2].indexOf('fail') !== -1);
                if (DEBUG) {
                    console.log('[adcheck] bait =', r[0], '| control =', r[1], '| ads =', r[2], '=> blocked =', blocked);
                }
                return blocked;
            });
        }

        function buildWall() {
            var w = el('div', 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(15,23,42,.96);z-index:2147483647;display:flex;align-items:center;justify-content:center;padding:20px;box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;');
            w.id = 'bdn-wall';
            w.setAttribute('role', 'alertdialog');

            var box = el('div', 'background:#fff;width:100%;max-width:480px;padding:30px;border-radius:12px;text-align:center;box-shadow:0 20px 25px -5px rgba(0,0,0,.5);border-top:6px solid #dc2626;box-sizing:border-box;');
            box.appendChild(el('div', 'font-size:50px;margin-bottom:10px;line-height:1;', '🚫'));
            box.appendChild(el('h2', 'color:#0f172a;margin:0 0 10px;font-size:22px;font-weight:bold;', CFG.title));
            box.appendChild(el('p', 'color:#475569;font-size:14px;line-height:1.6;margin:0 0 16px;', CFG.message));

            var list = el('ul', 'text-align:left;color:#334155;font-size:13px;line-height:1.8;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:12px 16px 12px 32px;margin:0 0 20px;');
            CFG.steps.forEach(function (s) { list.appendChild(el('li', '', s)); });
            box.appendChild(list);

            var btn = el('button', 'background:#2563eb;color:#fff;border:none;padding:12px 24px;font-size:14px;font-weight:bold;border-radius:6px;cursor:pointer;width:100%;', CFG.button);
            btn.type = 'button';
            btn.addEventListener('click', function () { window.location.reload(); });
            box.appendChild(btn);

            w.appendChild(box);
            return w;
        }

        function lock(on) {
            document.documentElement.style.overflow = on ? 'hidden' : '';
            Array.prototype.forEach.call(document.body.children, function (c) {
                if (c === wall || c.tagName === 'SCRIPT' || c.tagName === 'STYLE') { return; }
                c.style.filter = on ? 'blur(6px)' : '';
                c.style.pointerEvents = on ? 'none' : '';
            });
        }

        function showWall() {
            shown = true;
            if (!wall || !wall.isConnected) {
                wall = buildWall();
                document.body.appendChild(wall);
            }
            wall.style.display = 'flex';
            wall.style.visibility = 'visible';
            wall.style.opacity = '1';
            lock(true);
        }

        function hideWall() {
            shown = false;
            if (wall && wall.parentNode) { wall.parentNode.removeChild(wall); }
            wall = null;
            lock(false);
        }

        function run() {
            detect().then(function (blocked) {
                if (blocked) {
                    if (!shown) { showWall(); }
                } else if (shown) {
                    hideWall();
                }
            });
        }

        // পপআপ দেখানোর পর কেউ DevTools দিয়ে লুকালে আবার ফিরিয়ে আনে
        setInterval(function () {
            if (!shown) { return; }
            var cs = wall && wall.isConnected ? window.getComputedStyle(wall) : null;
            if (!cs || cs.display === 'none' || cs.visibility === 'hidden' || cs.opacity === '0' || document.documentElement.style.overflow !== 'hidden') {
                showWall();
            }
        }, 1000);

        if (document.readyState === 'complete') {
            setTimeout(run, 1200);
        } else {
            window.addEventListener('load', function () { setTimeout(run, 1200); });
        }
        setInterval(run, 10000);
    })();
    </script>
    <?php
}