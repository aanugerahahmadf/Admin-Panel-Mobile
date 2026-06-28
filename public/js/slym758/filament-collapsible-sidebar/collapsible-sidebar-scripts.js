document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    initSidebar();
});

function initSidebar() {
    console.log('initSidebar çağrıldı');

    const sidebar = document.querySelector('.fi-sidebar');
    console.log('Sidebar:', sidebar);

    if (!sidebar) {
        console.log('Sidebar bulunamadı');
        return;
    }

    // Mobilde çalışma
    if (window.innerWidth < 1024) {
        console.log('Mobile görünüm - plugin devre dışı');
        return;
    }

    // Eğer zaten init edildiyse, tekrar etme
    if (sidebar.dataset.collapsibleInit) {
        console.log('Sidebar zaten init edilmiş');
        return;
    }
    sidebar.dataset.collapsibleInit = 'true';

    // Button oluştur
    let toggleBtn = document.querySelector('.fi-sidebar-collapse-button');
    if (!toggleBtn) {
        toggleBtn = createToggleButton(sidebar);
    }

    // State yükle
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        sidebar.classList.add('fi-sidebar-collapsed');
    }

    // Click event
    toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('fi-sidebar-collapsed');
        const isCollapsed = sidebar.classList.contains('fi-sidebar-collapsed');
        localStorage.setItem('sidebar-collapsed', isCollapsed);
        console.log('Sidebar toggled:', isCollapsed);
    });
}

function createToggleButton(sidebar) {
    const button = document.createElement('button');
    button.className = 'fi-sidebar-collapse-button';
    button.type = 'button';
    button.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    `;

    sidebar.insertBefore(button, sidebar.firstChild);
    console.log('Toggle button oluşturuldu');
    return button;
}
