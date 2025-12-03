document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle');

    if (!sidebar || !toggleBtn) return;
    // Toggle sidebar
    toggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        sidebar.classList.toggle('active');
        
        if (sidebar.classList.contains('active')) {
            createOverlay();
        } else {
            removeOverlay();
        }
    });

    function createOverlay() {
        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
            
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                removeOverlay();
            });
        }
    }

    function removeOverlay() {
        const overlay = document.querySelector('.sidebar-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    const links = sidebar.querySelectorAll('.nav-link, .btn-logout');
    links.forEach(link => {
        link.addEventListener('click', () => {
            sidebar.classList.remove('active');
            removeOverlay();
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 992) {
            sidebar.classList.remove('active');
            removeOverlay();
        }
    });
});