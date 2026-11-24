document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.wpamesh-menu-toggle');
    const nav = document.getElementById('wpamesh-nav');
    toggle?.addEventListener('click', () => {
        const open = nav.classList.toggle('open');
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    });

    const sections = document.querySelectorAll('.wpamesh-infra-section');

    function applyFilter(proto) {
        sections.forEach((sec) => {
            let shown = 0;
            sec.querySelectorAll('.wpamesh-site').forEach((site) => {
                const match = proto === 'all' || site.dataset.protos.split(' ').includes(proto);
                site.hidden = !match;
                if (match) shown++;
            });
            sec.hidden = shown === 0;
            sec.querySelector('.count').textContent = shown + (shown === 1 ? ' site' : ' sites');
        });
    }

    // The coverage map on the home page is a static image, so its filter only toggles button state.
    document.querySelectorAll('.wpamesh-filter').forEach((group) => {
        group.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-filter]');
            if (!btn) return;
            group.querySelectorAll('button').forEach((b) => b.setAttribute('aria-pressed', String(b === btn)));
            if (sections.length) applyFilter(btn.dataset.filter);
        });
    });

    if (sections.length) applyFilter('all');

    const tocLinks = [...document.querySelectorAll('.wpamesh-toc ol a')];
    if (tocLinks.length) {
        const targets = tocLinks.map((a) => document.querySelector(a.getAttribute('href')));
        const update = () => {
            let current = 0;
            targets.forEach((t, i) => { if (t.getBoundingClientRect().top < 160) current = i; });
            tocLinks.forEach((a, i) => a.classList.toggle('active', i === current));
        };
        addEventListener('scroll', update, { passive: true });
        update();
    }

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.wpamesh-copy');
        if (!btn) return;
        const block = btn.closest('.wpamesh-cli');
        const line = btn.closest('li');
        const text = line
            ? line.querySelector('code').textContent
            : [...block.querySelectorAll('li code, pre')].map((c) => c.textContent).join('\n');
        try {
            await navigator.clipboard.writeText(text);
        } catch {
            return;
        }
        const label = btn.textContent;
        btn.textContent = 'Copied';
        btn.classList.add('done');
        setTimeout(() => { btn.textContent = label; btn.classList.remove('done'); }, 1500);
    });
});
