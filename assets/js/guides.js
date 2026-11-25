document.addEventListener('DOMContentLoaded', () => {
    const tocLinks = [...document.querySelectorAll('.wpamesh-toc ol a')];
    const targets = tocLinks.map((a) => document.getElementById(decodeURIComponent(a.hash.slice(1))));
    if (tocLinks.length && targets.every(Boolean)) {
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
