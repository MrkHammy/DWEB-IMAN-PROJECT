/**
 * Fox Lab – Main JavaScript
 * Shared functionality across all pages
 */
document.addEventListener('DOMContentLoaded', () => {
    // ===== Mobile Navigation Toggle =====
    const hamburger = document.getElementById('hamburgerBtn');
    const mainNav = document.getElementById('mainNav');
    
    if (hamburger && mainNav) {
        hamburger.addEventListener('click', () => {
            mainNav.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    }

    // ===== Global Search with Predictive Suggestions =====
    const globalSearch = document.getElementById('globalSearch');
    const searchSuggestions = document.getElementById('globalSearchSuggestions');

    if (globalSearch && searchSuggestions) {
        let debounceTimer;
        const isSubPage = window.location.pathname.includes('/pages/');
        const basePath = isSubPage ? '' : 'pages/';
        const apiPath = isSubPage ? '../api/' : 'api/';

        // Navigate to glossary on Enter
        globalSearch.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = globalSearch.value.trim();
                if (query) {
                    searchSuggestions.classList.remove('show');
                    window.location.href = basePath + 'terms.php?q=' + encodeURIComponent(query);
                }
            }
            if (e.key === 'Escape') {
                searchSuggestions.classList.remove('show');
            }
        });

        // Live autocomplete on input
        globalSearch.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const query = globalSearch.value.trim();

            if (query.length < 2) {
                searchSuggestions.classList.remove('show');
                searchSuggestions.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch(apiPath + 'search_terms.php?q=' + encodeURIComponent(query));
                    const terms = await res.json();

                    if (!terms.length) {
                        searchSuggestions.innerHTML = '<div class="search-no-results">No terms found</div>';
                        searchSuggestions.classList.add('show');
                        return;
                    }

                    searchSuggestions.innerHTML = terms.map(t => `
                        <a href="${basePath}terms.php?id=${t.id}" class="search-suggestion-item">
                            <div class="suggestion-title">${escapeHtml(t.title)}</div>
                            <div class="suggestion-brief">${escapeHtml(t.brief)}</div>
                            <span class="suggestion-category">${escapeHtml(t.category)}</span>
                        </a>
                    `).join('');
                    searchSuggestions.classList.add('show');
                } catch (err) {
                    searchSuggestions.classList.remove('show');
                }
            }, 250);
        });

        // Close suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-bar')) {
                searchSuggestions.classList.remove('show');
            }
        });

        // Re-show on focus if there's content
        globalSearch.addEventListener('focus', () => {
            if (searchSuggestions.innerHTML.trim()) {
                searchSuggestions.classList.add('show');
            }
        });
    }

    // HTML escape helper
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ===== Flash message auto-dismiss =====
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(msg => {
        setTimeout(() => {
            msg.style.opacity = '0';
            msg.style.transform = 'translateY(-10px)';
            setTimeout(() => msg.remove(), 300);
        }, 4000);
    });

    // ===== Smooth scroll for anchor links =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ===== Output Tabs (Compiler) =====
    const outputTabs = document.querySelectorAll('.output-tab');
    outputTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            if (typeof switchOutputTab === 'function') {
                switchOutputTab(tab.getAttribute('data-tab'));
            } else {
                outputTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            }
        });
    });

    // ===== User Menu Dropdown =====
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        document.addEventListener('click', () => {
            userDropdown.classList.remove('show');
        });
    }

    // ===== Nav Dropdown Handler (reusable) =====
    function initNavDropdown(dropdownId, toggleId) {
        const dropdown = document.getElementById(dropdownId);
        const toggle = document.getElementById(toggleId);
        if (!dropdown || !toggle) return;

        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            // Close other open dropdowns
            document.querySelectorAll('.nav-dropdown.open').forEach(d => {
                if (d.id !== dropdownId) {
                    d.classList.remove('open');
                    const t = d.querySelector('.nav-dropdown-toggle');
                    if (t) t.setAttribute('aria-expanded', 'false');
                }
            });

            const isOpen = dropdown.classList.toggle('open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('#' + dropdownId)) {
                dropdown.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    initNavDropdown('securitySimDropdown', 'securitySimToggle');
    initNavDropdown('learnDropdown', 'learnToggle');

    // ===== Scroll-Reveal Animations =====
    // Auto-tag common sections & cards with reveal classes
    const revealSelectors = [
        '.hero-content', '.hero-image',
        '.section-title', '.section-subtitle',
        '.feature-card', '.stat-card', '.tip-card', '.tip-detail-card',
        '.blog-card', '.partner-card',
        '.auth-card', '.auth-image',
        '.compiler-panel', '.tutorial-card',
        '.term-card', '.phishing-card',
        '.checker-section', '.recommended-section',
        '.footer-grid',
        '.features-grid', '.stats-grid', '.tips-grid', '.blog-grid',
        '.partners-banner'
    ];

    revealSelectors.forEach(sel => {
        document.querySelectorAll(sel).forEach(el => {
            if (!el.classList.contains('reveal') &&
                !el.classList.contains('reveal-left') &&
                !el.classList.contains('reveal-right') &&
                !el.classList.contains('reveal-scale')) {
                el.classList.add('reveal');
            }
        });
    });

    // Add stagger index to cards inside grids
    document.querySelectorAll('.features-grid, .stats-grid, .tips-grid, .blog-grid').forEach(grid => {
        grid.classList.add('reveal-stagger');
        grid.querySelectorAll('.reveal').forEach((child, i) => {
            child.style.setProperty('--reveal-i', i);
        });
    });

    // Slide hero halves from left/right
    const heroContent = document.querySelector('.hero-content');
    const heroImage = document.querySelector('.hero-image');
    if (heroContent) { heroContent.classList.remove('reveal'); heroContent.classList.add('reveal-left'); }
    if (heroImage)   { heroImage.classList.remove('reveal');   heroImage.classList.add('reveal-right'); }

    // IntersectionObserver for triggering reveals
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale').forEach(el => {
            observer.observe(el);
        });
    } else {
        // Fallback: show everything immediately
        document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale').forEach(el => {
            el.classList.add('revealed');
        });
    }
});
