/**
 * Dark/Light Mode Toggle System
 * Responsive və user-friendly tema dəyişdirmə
 */

class ThemeManager {
    constructor() {
        this.theme = localStorage.getItem('theme') || 'light';
        this.init();
    }

    init() {
        // Apply saved theme on load
        this.applyTheme(this.theme);

        // Create toggle button if not exists
        if (!document.getElementById('themeToggle')) {
            this.createToggleButton();
        }

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) {
                this.applyTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    createToggleButton() {
        const button = document.createElement('button');
        button.id = 'themeToggle';
        button.className = 'theme-toggle-btn';
        button.innerHTML = `
            <i class="fas fa-sun sun-icon"></i>
            <i class="fas fa-moon moon-icon"></i>
        `;
        button.setAttribute('aria-label', 'Toggle theme');
        button.onclick = () => this.toggleTheme();

        document.body.appendChild(button);
    }

    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        this.applyTheme(this.theme);
        localStorage.setItem('theme', this.theme);
    }

    applyTheme(theme) {
        const root = document.documentElement;

        if (theme === 'dark') {
            root.style.setProperty('--bg-primary', '#1a1a2e');
            root.style.setProperty('--bg-secondary', '#16213e');
            root.style.setProperty('--bg-tertiary', '#0f3460');
            root.style.setProperty('--text-primary', '#ffffff');
            root.style.setProperty('--text-secondary', '#e0e0e0');
            root.style.setProperty('--text-muted', '#9ca3af');
            root.style.setProperty('--border-color', '#374151');
            root.style.setProperty('--shadow', 'rgba(0, 0, 0, 0.5)');
            root.style.setProperty('--card-bg', '#16213e');
            root.style.setProperty('--input-bg', '#0f3460');
            root.style.setProperty('--hover-bg', '#1f2937');

            document.body.classList.add('dark-mode');
            document.body.classList.remove('light-mode');
        } else {
            root.style.setProperty('--bg-primary', '#ffffff');
            root.style.setProperty('--bg-secondary', '#f9fafb');
            root.style.setProperty('--bg-tertiary', '#f3f4f6');
            root.style.setProperty('--text-primary', '#1f2937');
            root.style.setProperty('--text-secondary', '#374151');
            root.style.setProperty('--text-muted', '#6b7280');
            root.style.setProperty('--border-color', '#e5e7eb');
            root.style.setProperty('--shadow', 'rgba(0, 0, 0, 0.1)');
            root.style.setProperty('--card-bg', '#ffffff');
            root.style.setProperty('--input-bg', '#ffffff');
            root.style.setProperty('--hover-bg', '#f3f4f6');

            document.body.classList.add('light-mode');
            document.body.classList.remove('dark-mode');
        }

        // Update button state
        const toggleBtn = document.getElementById('themeToggle');
        if (toggleBtn) {
            if (theme === 'dark') {
                toggleBtn.classList.add('dark');
            } else {
                toggleBtn.classList.remove('dark');
            }
        }

        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
    }

    getCurrentTheme() {
        return this.theme;
    }
}

// Initialize theme manager when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.themeManager = new ThemeManager();
    });
} else {
    window.themeManager = new ThemeManager();
}

// CSS for toggle button
const themeStyles = document.createElement('style');
themeStyles.textContent = `
    :root {
        /* Light mode defaults */
        --bg-primary: #ffffff;
        --bg-secondary: #f9fafb;
        --bg-tertiary: #f3f4f6;
        --text-primary: #1f2937;
        --text-secondary: #374151;
        --text-muted: #6b7280;
        --border-color: #e5e7eb;
        --shadow: rgba(0, 0, 0, 0.1);
        --card-bg: #ffffff;
        --input-bg: #ffffff;
        --hover-bg: #f3f4f6;
    }

    .theme-toggle-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        cursor: pointer;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .theme-toggle-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.5);
    }

    .theme-toggle-btn .sun-icon {
        display: block;
    }

    .theme-toggle-btn .moon-icon {
        display: none;
    }

    .theme-toggle-btn.dark .sun-icon {
        display: none;
    }

    .theme-toggle-btn.dark .moon-icon {
        display: block;
    }

    /* Dark mode styles */
    body.dark-mode {
        background: #1a1a2e !important;
        color: #ffffff;
    }

    body.dark-mode .calendar-wrapper,
    body.dark-mode .courses-wrapper,
    body.dark-mode .form-wrapper {
        background: var(--card-bg) !important;
        border: 1px solid var(--border-color);
    }

    body.dark-mode .calendar-day {
        color: var(--text-primary);
        background: var(--bg-tertiary);
        border-color: var(--border-color);
    }

    body.dark-mode .calendar-day:hover {
        background: var(--hover-bg);
    }

    body.dark-mode .calendar-day.current {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    body.dark-mode .course-item-mini {
        background: var(--bg-tertiary);
        border-color: var(--border-color);
    }

    body.dark-mode input,
    body.dark-mode textarea,
    body.dark-mode select {
        background: var(--input-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }

    body.dark-mode .navbar {
        background: var(--bg-secondary) !important;
        border-bottom: 1px solid var(--border-color);
    }

    body.dark-mode .card,
    body.dark-mode .modal-content {
        background: var(--card-bg) !important;
        color: var(--text-primary);
        border-color: var(--border-color);
    }

    body.dark-mode h1,
    body.dark-mode h2,
    body.dark-mode h3,
    body.dark-mode h4,
    body.dark-mode h5,
    body.dark-mode h6 {
        color: var(--text-primary) !important;
    }

    body.dark-mode p,
    body.dark-mode span,
    body.dark-mode div {
        color: var(--text-secondary);
    }

    body.dark-mode .text-muted {
        color: var(--text-muted) !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .theme-toggle-btn {
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            font-size: 20px;
        }
    }

    /* Smooth transitions */
    * {
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    }

    /* Override for elements that shouldn't transition */
    .owl-carousel,
    .owl-carousel *,
    button,
    a {
        transition: all 0.3s ease !important;
    }
`;

document.head.appendChild(themeStyles);
