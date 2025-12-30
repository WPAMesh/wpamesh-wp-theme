/**
 * WPAMesh Navigation
 *
 * Handles mobile sidebar toggle and keyboard accessibility.
 * The WordPress Navigation block handles most accessibility automatically,
 * but we need custom JS for the sidebar slide-out behavior.
 */

document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.wpamesh-menu-toggle');
    const sidebar = document.querySelector('.wpamesh-sidebar');
    const overlay = document.querySelector('.wpamesh-sidebar-overlay');

    if (!menuToggle || !sidebar) {
        return;
    }

    /**
     * Open the mobile sidebar menu
     */
    function openMenu() {
        sidebar.classList.add('active');
        if (overlay) {
            overlay.classList.add('active');
        }
        menuToggle.setAttribute('aria-expanded', 'true');
        menuToggle.setAttribute('aria-label', 'Close menu');

        // Focus first focusable element in sidebar
        const firstFocusable = sidebar.querySelector('a, button, input, [tabindex]:not([tabindex="-1"])');
        if (firstFocusable) {
            firstFocusable.focus();
        }
    }

    /**
     * Close the mobile sidebar menu
     */
    function closeMenu() {
        sidebar.classList.remove('active');
        if (overlay) {
            overlay.classList.remove('active');
        }
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.setAttribute('aria-label', 'Open menu');
        menuToggle.focus();
    }

    /**
     * Toggle menu state
     */
    function toggleMenu() {
        if (sidebar.classList.contains('active')) {
            closeMenu();
        } else {
            openMenu();
        }
    }

    // Menu toggle click handler
    menuToggle.addEventListener('click', toggleMenu);

    // Overlay click closes menu
    if (overlay) {
        overlay.addEventListener('click', closeMenu);
    }

    // Escape key closes menu
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && sidebar.classList.contains('active')) {
            closeMenu();
        }
    });

    // Close menu when clicking a link (for single-page feel)
    sidebar.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            // Small delay to allow navigation to start
            setTimeout(closeMenu, 100);
        });
    });

    // Handle resize - close mobile menu if window becomes wide
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 900 && sidebar.classList.contains('active')) {
                closeMenu();
            }
        }, 250);
    });
});
