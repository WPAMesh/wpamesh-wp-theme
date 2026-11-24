# Code Conventions

## CSS Naming

- Theme custom properties: `--wpamesh-*` prefix
- Theme component classes: `wpamesh-*` prefix (e.g., `.wpamesh-node-header`, `.wpamesh-badge`)
- Guide page classes: `wpa-*` prefix (e.g., `.wpa-intro`, `.wpa-alert`, `.wpa-role`)

## Color Palette

- Black: `#0a0a0a` / `--wpamesh-black`
- Gold (primary accent): `#FFB81C` / `--wpamesh-gold`
- Rust (secondary accent): `#c45c26` / `--wpamesh-rust`
- Green (online/success): `#4ade80` / `--wpamesh-green`
- Discord blue: `#5865F2` / `--wpamesh-discord`

## Typography

- Body: Barlow (400, 500, 600, 700)
- Headings: Barlow Condensed (600, 700)
- Code: JetBrains Mono / Consolas

## Blocks

All custom Gutenberg blocks use **server-side rendering** via `render.php`. Never save dynamic values (API data, timestamps, counts) in block content - always fetch at render time to prevent editors from accidentally "baking" live values.

## Transient Caching

API data is cached by the wpamesh-sites plugin, not the theme. Use 15-minute expiry for raw API
responses (`wpamesh_mv_*` for Meshview, `wpamesh_cs_*` for CoreScope). The WP-Cron refresh
writes the aggregates read on page load:
- `wpamesh_sites_status` - Live status for every radio, keyed `protocol:node_id`
- `wpamesh_sites_mesh_stats` - Network-wide Meshtastic stats
