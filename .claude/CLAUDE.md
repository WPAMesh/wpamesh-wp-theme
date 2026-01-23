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
- `wpamesh/node-list` - Dynamic node listing with tier filtering

All blocks use **server-side rendering** via `render.php` to avoid editors accidentally "baking" live values into post content.

### Node Data Fields

Node posts use ACF/SCF fields:
- `node_id` - Hex ID for Meshview linking (e.g., "a5060ad0")
- `node_tier` - core_router, supplemental, gateway, or service
- `long_name`, `short_name` - Display names
- `role` - Meshtastic role (ROUTER, CLIENT, etc.)
- `location_name`, `latitude`, `longitude`
- `antenna_gain`, `height_agl`, `height_msl` (heights in feet)
- `hardware`, `maintainer`

### Key Files

- **functions.php** - Theme setup, API integration, block registration, caching, AJAX endpoints
- **theme.json** - Design tokens (colors, typography, spacing, layout)
- **assets/css/theme.css** - Main stylesheet (no preprocessing). Guide page styles start ~line 1524
- **assets/js/node-list.js** - AJAX updates for node lists and single node headers
- **assets/js/sidebar-stats.js** - AJAX updates for stats grid
- **assets/js/navigation.js** - Mobile menu toggle
- **patterns/node-header.php** - Node page header with live status and channel util

### External API Integration

Meshview API at `https://map.wpamesh.net/api`:
- 15-minute cache using WordPress transients
- WP-Cron background cache warming (no blocking API calls on page load)
- AJAX endpoints for lazy loading (`wp_ajax_wpamesh_*` actions)

**Caching strategy (hybrid):**
- Aggregated list cache (`wpamesh_node_list_data` transient) for bulk operations
- Per-node transients (`wpamesh_node_{id}`) for O(1) single lookups
- Both populated during background refresh every 15 minutes
- If cache empty, AJAX endpoints do synchronous refresh (AJAX is already async)

**Key helper functions:**
- `wpamesh_get_node_list_data()` - All nodes from cache
- `wpamesh_get_single_node_data($node_id)` - O(1) single node lookup
- `wpamesh_get_channel_load_level($util)` - Classify channel util as Low/Elevated/High
- `wpamesh_get_network_stats()` - Node counts, packets
- `wpamesh_get_channel_metrics()` - Network-wide channel/airtime averages

**Channel utilization thresholds** (from Meshtastic firmware airtime.h):
- Low: <25% (normal operation)
- Elevated: 25-40% (polite mode throttles telemetry)
- High: >40% (transmissions get skipped)

### REST API

Endpoints at `/wp-json/wpamesh/v1/`:
- `GET /nodes` - All nodes
- `GET /nodes?tier=core_router` or `?tier=supplemental` - Tower/infrastructure nodes
- `GET /nodes/{wp_id}` - Single node by WordPress post ID

Node tiers: `core_router`, `supplemental` (tower nodes), `gateway` (MQTT bridges), `service` (BBS, Matrix bridges)

### Responsive Breakpoints

- Mobile: < 900px (hamburger menu)
- Desktop: ≥ 900px (left sidebar visible)
- Wide: ≥ 1400px (right sidebar appears)

## Utilities

**scripts/export_nodes.py** - Export core_router and supplemental nodes to CSV. Uses curl via temp file to bypass WAF.

```bash
python3 scripts/export_nodes.py > nodes.csv
```
