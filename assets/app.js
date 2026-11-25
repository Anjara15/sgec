import './stimulus_bootstrap.js';
import './styles/app.css';

const menuToggle = document.querySelector('[data-sidebar-toggle]');
const menuBackdrop = document.querySelector('[data-sidebar-close]');
const sidebarCollapseToggle = document.querySelector('[data-sidebar-collapse]');

if (sidebarCollapseToggle) {
    sidebarCollapseToggle.addEventListener('click', () => {
        const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
        sidebarCollapseToggle.setAttribute('aria-pressed', String(isCollapsed));
        sidebarCollapseToggle.setAttribute('aria-label', isCollapsed ? 'Développer le menu' : 'Réduire le menu');
        sidebarCollapseToggle.innerHTML = `<i class="bi ${isCollapsed ? 'bi-layout-sidebar' : 'bi-layout-sidebar-inset'}" aria-hidden="true"></i>`;
    });
}

if (menuToggle && menuBackdrop) {
    const closeMenu = () => {
        document.body.classList.remove('sidebar-open');
        menuToggle.setAttribute('aria-expanded', 'false');
    };

    menuToggle.addEventListener('click', () => {
        const isOpen = document.body.classList.toggle('sidebar-open');
        menuToggle.setAttribute('aria-expanded', String(isOpen));
    });

    menuBackdrop.addEventListener('click', closeMenu);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });
}
