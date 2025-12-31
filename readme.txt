=== WPAMesh ===
Contributors: wpamesh
Tags: dark, one-column, two-columns, three-columns, full-site-editing, block-patterns, custom-colors, custom-menu, editor-style
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A dark block theme for the Western Pennsylvania Meshtastic community.

== Description ==

WPAMesh is a custom WordPress block theme designed for the Western Pennsylvania Meshtastic community (wpamesh.net). It features a Pittsburgh-inspired dark design with gold accents, responsive sidebar navigation, and a 3-column layout on wide screens.

**Features:**

* Dark theme with Pittsburgh Steelers-inspired colors
* Left sidebar navigation with collapsible sections
* Right sidebar with network stats, events, and Discord widget (wide screens)
* Responsive design with mobile hamburger menu
* Full Site Editing (FSE) support
* Custom block patterns for quick layout building
* Accessibility features (skip link, focus styles, reduced motion support)

**Block Patterns Included:**

* Hero Banner - Pittsburgh skyline with call-to-action
* Coverage Map - Interactive map link
* Recent Posts Grid - 2-column post display
* Network Status Widget - Sidebar status indicators
* Network Stats - Node and coverage statistics
* Upcoming Events - Event listings
* Discord Widget - Community join button
* Featured Node - Highlight infrastructure nodes
* Node Header - Single node page header with featured image
* Node Specs - Node specifications table
* Member Nodes - Dynamic listing of recognized infrastructure nodes with live status

== Installation ==

1. Download the wpamesh-theme.zip file
2. Go to Appearance > Themes > Add New > Upload Theme
3. Upload the zip file and click "Install Now"
4. Click "Activate" to enable the theme
5. See SETUP-GUIDE.md for detailed configuration instructions

== Frequently Asked Questions ==

= Does this theme require any plugins? =

The theme works standalone, but for full Node functionality you'll need Secure Custom Fields (SCF) or Advanced Custom Fields (ACF) to manage node metadata.

= How do I customize the navigation? =

Go to Appearance > Editor, click on any navigation menu in the sidebar, and edit links directly in the preview.

= Why don't I see the right sidebar? =

The right sidebar only appears on screens wider than 1400px to maintain readability on smaller displays.

== Changelog ==

= 1.2.3 =
* Added Member Nodes pattern for dynamic infrastructure node listings
* Added [wpamesh_node_list] shortcode for flexible node list placement
* Shortcode supports tier filtering: [wpamesh_node_list tier="core_router"]
* Available tiers: core_router, supplemental, gateway, service
* Optional show_title="false" attribute to hide tier headings
* Shows live online/offline status dots for nodes with node_id set
* Requires new SCF fields: node_tier (select), location_name (text)

= 1.2.2 =
* Fixed API field names for channel metrics (from_node_id)
* Fixed role filtering to use client-side array filtering

= 1.2.1 =
* Added channel utilization and airtime metrics to Network Stats
* Added wpamesh_get_channel_metrics() for network-wide channel stats
* Added wpamesh_get_node_channel_metrics() for per-node metrics
* Added protobuf text format payload parser for telemetry data

= 1.2.0 =
* Added meshview API integration for live network statistics
* Network stats pattern now displays live data from map.wpamesh.net
* Added node_id field support for linking WordPress nodes to meshview
* Node header now shows live online/offline status when node_id is set
* Added API helper functions with WordPress transient caching (5 min TTL)
* Added hex/decimal node ID conversion utilities

= 1.1.0 =
* Added single-node.html template for Node custom post type
* Added node-header.php pattern with large featured image banner
* Added node-specs.php pattern with formatted specs table
* Added helper functions for height (feet/meters) and antenna gain formatting
* Added role-specific badge colors for node display
* Fixed WordPress block flow layout margin issues throughout theme
* Fixed template-part wrapper margin resets

= 1.0.0 =
* Initial release
* Dark theme with gold accents
* 3-column responsive layout
* Full Site Editing support
* 8 custom block patterns
* Mobile-first responsive design

== Copyright ==

WPAMesh WordPress Theme, (C) 2025 WPAMesh Community
WPAMesh is distributed under the terms of the GNU GPL.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

== Resources ==

Barlow and Barlow Condensed fonts
License: Open Font License
Source: https://fonts.google.com/specimen/Barlow

normalize.css
License: MIT
Source: https://necolas.github.io/normalize.css/
