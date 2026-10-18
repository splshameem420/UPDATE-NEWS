document.addEventListener('DOMContentLoaded', function() {
    // Clock
    function updateClock() {
        const now = new Date();
        const timeEl = document.getElementById('v-time');
        if (timeEl) timeEl.textContent = now.toLocaleTimeString();
    }
    updateClock();
    setInterval(updateClock, 1000);

    // Dynamic Location & VPN Check using PHP IP
    const targetIp = "<?php echo esc_js( $visitor_ip ); ?>";
    
    fetch('https://ipwho.is/' + targetIp)
        .then(res => res.json())
        .then(data => {
            const countryEl = document.getElementById('v-country');
            const statusEl = document.getElementById('v-vpn-status');
            const vpnWarnEl = document.getElementById('topbar-vpn-warning');

            if (data && data.success) {
                countryEl.textContent = `${data.country || ''} (${data.country_code || ''})`;

                const isProxy = data.security ? data.security.proxy : false;
                const isVpn = data.security ? data.security.vpn : false;
                const org = (data.connection ? data.connection.isp + ' ' + data.connection.org : '').toLowerCase();
                const vpnKeywords = ['vpn', 'proxy', 'hosting', 'cloud', 'datacenter', 'digitalocean', 'linode', 'aws', 'vultr'];
                const isSuspicious = vpnKeywords.some(keyword => org.includes(keyword));

                if (isProxy || isVpn || isSuspicious) {
                    statusEl.textContent = 'VPN Detected';
                    statusEl.style.color = '#ef4444';
                    statusEl.style.fontWeight = 'bold';
                    if (vpnWarnEl) vpnWarnEl.style.display = 'block';
                } else {
                    statusEl.textContent = 'Clean';
                    statusEl.style.color = '#22c55e';
                }
            } else {
                countryEl.textContent = 'Localhost';
                statusEl.textContent = 'Connected';
            }
        })
        .catch(() => {
            document.getElementById('v-country').textContent = 'Online';
            document.getElementById('v-vpn-status').textContent = 'Clean';
        });
});