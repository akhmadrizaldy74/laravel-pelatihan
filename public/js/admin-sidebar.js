(function () {
    function initAdminSidebar() {
        const shell = document.querySelector('.admin-shell');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        if (!shell || !toggleBtn || toggleBtn.dataset.bound === '1') {
            return;
        }
        toggleBtn.dataset.bound = '1';

        if (window.innerWidth > 980 && localStorage.getItem('ppsdmk_sidebar_collapsed') === '1') {
            shell.classList.add('sidebar-collapsed');
        }

        toggleBtn.addEventListener('click', function () {
            if (window.innerWidth <= 980) {
                shell.classList.toggle('sidebar-mobile-open');
            } else {
                const collapsed = shell.classList.toggle('sidebar-collapsed');
                localStorage.setItem('ppsdmk_sidebar_collapsed', collapsed ? '1' : '0');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initAdminSidebar);
    document.addEventListener('livewire:navigated', initAdminSidebar);
    if (window.Livewire) {
        Livewire.hook('morph.updated', initAdminSidebar);
    }
})();
