# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WPAMesh is a WordPress Full Site Editing (FSE) block theme for the Western Pennsylvania Meshtastic community (wpamesh.net). It's a dark theme with Pittsburgh Steelers-inspired gold (#FFB81C) accents.

**Stack:** WordPress 6.0+ FSE theme, PHP 7.4+, vanilla JavaScript, no build system

**Live site:** https://wpamesh.net
**Meshview API:** https://map.wpamesh.net/api
**Coverage area:** Allegheny, Westmoreland, Butler, Armstrong, Indiana, and Beaver counties in Western PA

## Development

**No build step required** - this is a pure WordPress theme with no npm/composer dependencies.

**Local development:** Install theme in WordPress, activate, and edit via Appearance > Editor (FSE).

**After changes:** Save permalinks (Settings > Permalinks) if URL structure is affected.

## Architecture

### Theme and plugin

Infrastructure data lives in the companion plugin **wpamesh-sites** (`~/Projects/PHP/wpamesh-sites`):
the Site post type (`wpamesh_site`, at `/sites/<slug>/`), its SCF fields and radios repeater, live
status from Meshview (Meshtastic) and CoreScope (MeshCore), the WP-Cron refresh, the
`/wp-json/wpamesh/v1/` REST API, and the migration from the old Node-Detail posts. Its README
covers all of that. The theme only displays it. Plugin functions are prefixed `wpamesh_sites_`
so they can't clash with the theme.

### Templates and parts (FSE)

Every template is header part + `<main class="wpamesh-wrap wpamesh-main">` + footer part. The
header uses a core Navigation block so admins can edit links in the Site Editor.

- `templates/single-wpamesh_site.html` - Site page, built from the site blocks below
- `parts/header.html`, `parts/footer.html`

Site Editor customizations saved in the database override these files. Reset them when a template
change doesn't show up.

### Custom Blocks

Located in `blocks/`, each with `block.json` and `render.php`:
- `wpamesh/site-header`, `site-photo`, `site-info`, `site-pager` - Pieces of the Site page
- `wpamesh/site-list` - All sites by tier with a network filter (the Infrastructure page)
- `wpamesh/network-stats`, `wpamesh/stat-box` - Meshtastic network stats

All blocks use **server-side rendering** via `render.php` to avoid editors accidentally "baking" live values into post content. The site blocks share one editor script (`assets/js/site-blocks-editor.js`) that previews them with ServerSideRender.

### Key Files

- **functions.php** - Theme setup, block registration, stat-box AJAX, Discord widget and webhook
- **inc/sites.php** - Site display helpers, status formatting, `wpamesh_site_status` AJAX
- **theme.json** - Design tokens (colors, typography, spacing, layout)
- **assets/css/theme.css** - Redesign stylesheet (native nesting, no preprocessing)
- **assets/css/legacy.css** - Pre-redesign components existing page content still uses (`.wpa-*` guides, stat boxes, old homepage patterns). Delete sections as their replacements ship
- **assets/js/site-status.js** - Refreshes status on site pages and the site list, since pages may come from a page cache
- **assets/js/navigation.js** - Mobile menu toggle

**Channel utilization thresholds** (from Meshtastic firmware airtime.h):
- Low: <25% (normal operation)
- Elevated: 25-40% (polite mode throttles telemetry)
- High: >40% (transmissions get skipped)

### Responsive Breakpoints

- Mobile: < 900px (menu toggle)
- Site page stacks to one column below 1000px

## Local development

`dev/setup.sh` builds a local copy at http://localhost:8080 (admin/admin) from the newest WXR
export in `exports/` (gitignored), with the theme and plugin mounted live.

## Utilities

**scripts/export_nodes.py** - Export core_router and supplemental nodes to CSV. Uses curl via temp file to bypass WAF.

```bash
python3 scripts/export_nodes.py > nodes.csv
```
