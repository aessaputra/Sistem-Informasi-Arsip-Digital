/**
 * Theme Toggle for Sanapati Surel
 * Uses official Tabler mechanism with data-bs-theme and localStorage
 * 
 * Key: tabler-theme
 * Values: 'light' (default) or 'dark'
 */
(function () {
    'use strict';

    const THEME_KEY = 'tabler-theme';
    const THEMES = { LIGHT: 'light', DARK: 'dark' };

    /**
     * Get current theme from localStorage or default to light
     * @returns {string} Current theme value
     */
    function getCurrentTheme() {
        return localStorage.getItem(THEME_KEY) || THEMES.LIGHT;
    }

    /**
     * Set theme and persist to localStorage
     * Updates data-bs-theme attribute on <html> element
     * @param {string} theme - Theme to set ('light' or 'dark')
     */
    function setTheme(theme) {
        if (theme === THEMES.DARK) {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        } else {
            // Tabler default is light, so we remove the attribute for light mode
            document.documentElement.removeAttribute('data-bs-theme');
        }
        localStorage.setItem(THEME_KEY, theme);
    }

    /**
     * Toggle between light and dark themes
     */
    function toggleTheme() {
        const currentTheme = getCurrentTheme();
        const newTheme = currentTheme === THEMES.DARK ? THEMES.LIGHT : THEMES.DARK;
        setTheme(newTheme);
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function () {
        // Attach event listeners to toggle buttons
        const darkBtn = document.getElementById('theme-toggle-dark');
        const lightBtn = document.getElementById('theme-toggle-light');

        if (darkBtn) {
            darkBtn.addEventListener('click', function (e) {
                e.preventDefault();
                setTheme(THEMES.DARK);
            });
        }

        if (lightBtn) {
            lightBtn.addEventListener('click', function (e) {
                e.preventDefault();
                setTheme(THEMES.LIGHT);
            });
        }
    });

    // Expose functions globally for potential external use
    window.TablerTheme = {
        getCurrentTheme: getCurrentTheme,
        setTheme: setTheme,
        toggleTheme: toggleTheme,
        THEMES: THEMES
    };
})();
