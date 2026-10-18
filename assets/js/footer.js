document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.getElementById('brandSliderWrapper');
    const track = document.getElementById('brandSliderTrack');
    
    if (!wrapper || !track) return;

    function checkOverflow() {
        const group = track.querySelector('.brand-track-group:not(.duplicate-group)');
        if (!group) return;

        // Space Overflow Checker (Jotokkhon container height/width par nahobe scroll hobe na)
        if (group.scrollWidth > wrapper.clientWidth) {
            track.classList.add('is-overflowing');
        } else {
            track.classList.remove('is-overflowing');
        }
    }

    checkOverflow();
    window.addEventListener('resize', checkOverflow);
});