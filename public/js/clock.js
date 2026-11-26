(function () {
    function updateRealtimeWibClocks() {
        const now = new Date();
        const opts = {
            timeZone: 'Asia/Jakarta',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        const timeStr = new Intl.DateTimeFormat('id-ID', opts).format(now).replace(/\./g, ':');
        document.querySelectorAll('.realtime-wib-clock').forEach(function (el) {
            el.textContent = timeStr + ' WIB';
        });
    }

    updateRealtimeWibClocks();
    setInterval(updateRealtimeWibClocks, 1000);
    document.addEventListener('livewire:navigated', updateRealtimeWibClocks);
})();
