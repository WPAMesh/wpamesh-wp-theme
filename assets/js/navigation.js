document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.wpamesh-menu-toggle');
    const nav = document.getElementById('wpamesh-nav');
    if (!toggle || !nav) return;

    const setOpen = (open) => {
        nav.classList.toggle('open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    };

    toggle.addEventListener('click', () => setOpen(!nav.classList.contains('open')));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && nav.classList.contains('open')) {
            setOpen(false);
            toggle.focus();
        }
    });
});
