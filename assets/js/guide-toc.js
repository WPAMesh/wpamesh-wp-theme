/**
 * Guide Page Table of Contents - Scroll Tracking
 *
 * Highlights the current section in the TOC sidebar as the user scrolls.
 * Works with .wpa-toc-list a links that have href="#section-id" attributes.
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const tocLinks = document.querySelectorAll('.wpa-toc-list a');

        // Exit early if no TOC links found
        if (tocLinks.length === 0) {
            return;
        }

        const sections = [];

        // Build array of sections with their elements and TOC links
        tocLinks.forEach(function(link) {
            const href = link.getAttribute('href');
            if (href && href.startsWith('#')) {
                const id = href.substring(1);
                const section = document.getElementById(id);
                if (section) {
                    sections.push({ id: id, element: section, link: link });
                }
            }
        });

        // Exit if no valid sections found
        if (sections.length === 0) {
            return;
        }

        /**
         * Update the active TOC link based on scroll position
         */
        function updateActiveLink() {
            const scrollPos = window.scrollY + 100; // Offset for header

            let currentSection = sections[0];

            // Find the current section based on scroll position
            for (var i = 0; i < sections.length; i++) {
                if (sections[i].element.offsetTop <= scrollPos) {
                    currentSection = sections[i];
                }
            }

            // Update active class on TOC links
            tocLinks.forEach(function(link) {
                link.classList.remove('active');
            });

            if (currentSection) {
                currentSection.link.classList.add('active');
            }
        }

        // Listen for scroll events with passive flag for performance
        window.addEventListener('scroll', updateActiveLink, { passive: true });

        // Initial call to set active state on page load
        updateActiveLink();
    });
})();
