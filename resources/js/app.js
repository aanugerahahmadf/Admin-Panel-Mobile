import './bootstrap';
import './echo';
import './advanced-file-upload';
import './sidebar-auto-expand';
import 'emoji-picker-element';
import './emoji-picker';
import './pdf-preview-plugin';
import './phpProtocolAdapter';

// Dark mode synchronization (Follow System by Default)
function syncTheme() {
    const theme = localStorage.getItem('theme');
    const isDark = theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches);
    
    if (isDark) {
        document.documentElement.classList.add('dark');
        document.documentElement.style.colorScheme = 'dark';
    } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.style.colorScheme = 'light';
    }
}

syncTheme();

// Listen for system theme changes in real-time
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', syncTheme);
