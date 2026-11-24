document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.wpamesh-site-list').forEach((list) => {
        const sections = list.querySelectorAll('.wpamesh-infra-section');
        const buttons = list.querySelectorAll('.wpamesh-filter button[data-filter]');

        const apply = (proto) => {
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
            buttons.forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.filter === proto)));
        };

        buttons.forEach((b) => b.addEventListener('click', () => apply(b.dataset.filter)));
        apply('all');
    });
});
