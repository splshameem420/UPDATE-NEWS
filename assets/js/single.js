document.addEventListener('DOMContentLoaded', function() {
    const dateElement = document.querySelector('.user-local-date');
    if (!dateElement) return;

    const utcDateStr = dateElement.getAttribute('data-utc-time');
    if (!utcDateStr) return;

    const ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php';

    fetch(ajaxUrl + '?action=bd_news_visitor_info')
        .then(response => response.json())
        .then(res => {
            if (res.success && res.data && res.data.timezone) {
                const userTimezone = res.data.timezone;
                const dateObj = new Date(utcDateStr);
                
                const formattedDate = new Intl.DateTimeFormat('en-US', {
                    timeZone: userTimezone,
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: 'numeric',
                    hour12: true
                }).format(dateObj);

                dateElement.innerHTML = '📅 ' + formattedDate;
            }
        })
        .catch(error => {
            console.error('Meta timezone fetch error:', error);
        });
});