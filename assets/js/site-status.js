/* Swaps in current status on site pages and the site list, which may come from a page cache.
   Payload shape: see wpamesh_ajax_site_status() in inc/sites.php. */
document.addEventListener('DOMContentLoaded', async () => {
    const sites = document.querySelectorAll('[data-wpamesh-site]');
    const radios = document.querySelectorAll('[data-wpamesh-radio]');
    if (!sites.length && !radios.length) return;

    let data;
    try {
        const res = await fetch(wpameshSiteStatus.url, { credentials: 'same-origin' });
        data = (await res.json()).data;
    } catch {
        return;
    }
    if (!data) return;

    sites.forEach((el) => {
        const s = data.sites[el.dataset.wpameshSite];
        el.hidden = !s;
        if (!s) return;
        el.classList.toggle('off', s.class === 'off');
        el.querySelector('.label').textContent = s.label;
    });

    radios.forEach((el) => {
        const r = data.radios[el.dataset.wpameshRadio];
        if (!r) return;

        const seen = el.querySelector('.seen');
        if (seen) seen.textContent = r.seen;

        const live = el.querySelector('.live');
        if (live) {
            live.replaceChildren(...r.tiles.map(([value, label, cls]) => {
                const tile = document.createElement('div');
                const b = document.createElement('b');
                const span = document.createElement('span');
                b.textContent = value;
                b.className = cls;
                span.textContent = label;
                tile.append(b, span);
                return tile;
            }));
            live.hidden = !r.tiles.length;
        }

        if (el.classList.contains('util')) {
            el.querySelectorAll('strong').forEach((strong, i) => {
                if (!r.tiles[i]) return;
                strong.textContent = r.tiles[i][0];
                strong.className = r.tiles[i][2];
            });
        }
    });
});
