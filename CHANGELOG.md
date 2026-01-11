# Changelog

All notable changes to the WPAMesh theme will be documented in this file.

## [1.3.3] - 2026-01-11

### Added
- Channel utilization load indicator on node page headers
- Load levels based on Meshtastic firmware thresholds: Low (<25%), Elevated (25-40%), High (>40%)
- `wpamesh_get_channel_load_level()` helper function for load classification

## [1.3.2] - 2026-01-11

### Added
- Per-node cache for O(1) single node lookups (hybrid cache strategy)
- `wpamesh_get_single_node_data()` helper for efficient single node retrieval

### Changed
- Node page headers use per-node cache instead of list iteration
- REST API single node endpoint uses per-node cache for live status

## [1.3.1] - 2026-01-11

### Added
- Background cache warming for Meshview API data using WP-Cron
- AJAX lazy loading for sidebar stats and node lists
- Aggregated sidebar stats cache (single API fetch for all stats)
- Aggregated node list cache with live status data

### Changed
- Page loads no longer block on external API calls
- Stats and node status hydrate asynchronously via JavaScript
- AJAX endpoints perform synchronous refresh if cache is empty

## [1.3.0] - 2026-01-11

### Added
- Custom Gutenberg blocks: Network Stats, Stat Box, and Node List
- Blocks use server-side rendering to prevent value baking in editor
- Stat Box block has dropdown selector to choose which statistic to display
- Node List block with tier filter and heading toggle options
- Discord webhook notifications for new posts
- Discord notifications configurable via Settings > General
- Category-based filtering for Discord notifications (comma-separated slugs)
- Admin activity log shows last 20 notification attempts

## [1.2.3] - 2025-01-06

### Added
- Member Nodes pattern for dynamic infrastructure node listings
- `[wpamesh_node_list]` shortcode for flexible node list placement
- Shortcode supports tier filtering: `[wpamesh_node_list tier="core_router"]`
- Available tiers: core_router, supplemental, gateway, service
- Optional `show_title="false"` attribute to hide tier headings
- Live online/offline status dots for nodes with node_id set

### Changed
- Requires new SCF fields: node_tier (select), location_name (text)

## [1.2.2] - 2025-01-05

### Fixed
- API field names for channel metrics (from_node_id)
- Role filtering to use client-side array filtering

## [1.2.1] - 2025-01-04

### Added
- Channel utilization and airtime metrics to Network Stats
- `wpamesh_get_channel_metrics()` for network-wide channel stats
- `wpamesh_get_node_channel_metrics()` for per-node metrics
- Protobuf text format payload parser for telemetry data

## [1.2.0] - 2025-01-03

### Added
- Meshview API integration for live network statistics
- Network stats pattern now displays live data from map.wpamesh.net
- node_id field support for linking WordPress nodes to meshview
- Node header shows live online/offline status when node_id is set
- API helper functions with WordPress transient caching (5 min TTL)
- Hex/decimal node ID conversion utilities

## [1.1.0] - 2024-12-29

### Added
- single-node.html template for Node custom post type
- node-header.php pattern with large featured image banner
- node-specs.php pattern with formatted specs table
- Helper functions for height (feet/meters) and antenna gain formatting
- Role-specific badge colors for node display

### Fixed
- WordPress block flow layout margin issues throughout theme
- Template-part wrapper margin resets

## [1.0.0] - 2024-12-15

### Added
- Initial release
- Dark theme with Pittsburgh Steelers-inspired gold accents
- 3-column responsive layout
- Full Site Editing (FSE) support
- 8 custom block patterns
- Mobile-first responsive design
- Left sidebar navigation with collapsible sections
- Right sidebar with network stats, events, and Discord widget
- Accessibility features (skip link, focus styles, reduced motion support)
