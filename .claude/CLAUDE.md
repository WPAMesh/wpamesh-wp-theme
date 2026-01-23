# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WPAMesh is a WordPress Full Site Editing (FSE) block theme for the Western Pennsylvania Meshtastic community (wpamesh.net). It's a dark theme with Pittsburgh Steelers-inspired gold (#FFB81C) accents.

**Stack:** WordPress 6.0+ FSE theme, PHP 7.4+, vanilla JavaScript, no build system

## Development

**No build step required** - this is a pure WordPress theme with no npm/composer dependencies. Assets are served directly.

**Local development:** Install theme in WordPress, activate, and edit via Appearance > Editor (FSE).

**After changes:** Save permalinks (Settings > Permalinks) if URL structure is affected.

## Architecture

### Template Hierarchy (FSE)

```
templates/           # Full page layouts
├── front-page.html  # Homepage
├── single-node.html # Node custom post type
├── single.html      # Standard posts
└── ...

parts/               # Reusable template parts
├── header.html      # Mobile header + logo
├── sidebar-left.html
├── sidebar-right.html
└── footer.html

patterns/            # Block patterns (pre-designed content)
```

### Custom Blocks

Located in `blocks/`, each with `block.json`, optional `editor.js`, and `render.php`:
- `wpamesh/network-stats` - Stats container
- `wpamesh/stat-box` - Individual stat with AJAX loading
- `wpamesh/node-list` - Dynamic node listing

All blocks use **server-side rendering** via `render.php` to avoid baking values in the editor.

### Key Files

- **functions.php** - Theme setup, API integration, block registration, asset enqueuing
- **theme.json** - Design tokens (colors, typography, spacing, layout)
- **assets/css/theme.css** - Main stylesheet (no preprocessing)
- **assets/js/** - Vanilla JS: navigation, guide TOC, node list, sidebar stats

### External API

Meshview API at `https://map.wpamesh.net/api`:
- 15-minute cache using WordPress transients
- WP-Cron background cache warming
- AJAX endpoints for lazy loading (`wp_ajax_wpamesh_*` actions)
- Helper functions: `wpamesh_get_node_data()`, `wpamesh_format_node_id()`

### Responsive Breakpoints

- Mobile: < 900px (hamburger menu)
- Desktop: ≥ 900px (left sidebar visible)
- Wide: ≥ 1400px (right sidebar appears)

## Guide Page Workflow

Guide/documentation pages use a two-stage workflow:

1. **Preview stage** (`pages/previews/`) - Standalone HTML with inline CSS for browser preview
2. **WordPress stage** (`pages/wordpress/`) - Content fragments using `wpa-*` classes from theme CSS

### Slash Commands

- `/new-page <brief>` - Create a new preview page based on content description
- `/wp-convert` - Auto-detect and migrate preview pages to WordPress format

Preview pages use classes like `.intro-box`, `.alert`, `.info-card`. WordPress versions use `wpa-*` prefixed equivalents (`.wpa-intro`, `.wpa-alert`, `.wpa-card`) defined in `assets/css/theme.css` starting at line 1524.

## Code Conventions

- CSS custom properties use `--wpamesh-*` prefix
- Block styles registered in `functions.php` lines 99-117
- Navigation menus: Getting Started, View The Mesh, Guides, Community
- Color palette: black (#0a0a0a), gold accent (#FFB81C), rust (#c45c26), green (#4ade80)
- Fonts: Barlow (body), Barlow Condensed (headings), JetBrains Mono (code)
