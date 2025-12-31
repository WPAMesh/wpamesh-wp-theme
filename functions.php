<?php
/**
 * WPAMesh Theme Functions
 *
 * @package WPAMesh
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue theme styles and scripts
 */
add_action( 'wp_enqueue_scripts', function() {
    // Google Fonts - Barlow and Barlow Condensed
    wp_enqueue_style(
        'wpamesh-google-fonts',
        'https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:wght@600;700&display=swap',
        array(),
        null
    );

    // Main theme stylesheet (extracted from HTML redesign)
    wp_enqueue_style(
        'wpamesh-theme',
        get_theme_file_uri( 'assets/css/theme.css' ),
        array( 'wpamesh-google-fonts' ),
        wp_get_theme()->get( 'Version' )
    );

    // Mobile navigation script
    wp_enqueue_script(
        'wpamesh-navigation',
        get_theme_file_uri( 'assets/js/navigation.js' ),
        array(),
        wp_get_theme()->get( 'Version' ),
        true
    );
});

/**
 * Enqueue editor styles to match frontend
 */
add_action( 'after_setup_theme', function() {
    add_theme_support( 'editor-styles' );
    add_editor_style( 'assets/css/theme.css' );
});

/**
 * Register block pattern category
 */
add_action( 'init', function() {
    register_block_pattern_category( 'wpamesh', array(
        'label' => __( 'WPAMesh', 'wpamesh' ),
    ));
});

/**
 * Add tabindex to main content anchor for skip link accessibility
 * Block themes don't automatically add tabindex to anchored elements
 */
add_filter( 'render_block', function( $content, $block ) {
    if ( isset( $block['attrs']['anchor'] ) && $block['attrs']['anchor'] === 'main-content' ) {
        $content = str_replace(
            'id="main-content"',
            'id="main-content" tabindex="-1"',
            $content
        );
    }
    return $content;
}, 10, 2 );

/**
 * Register navigation menu locations for Site Editor
 */
add_action( 'after_setup_theme', function() {
    register_nav_menus( array(
        'getting-started' => __( 'Getting Started', 'wpamesh' ),
        'view-the-mesh'   => __( 'View The Mesh', 'wpamesh' ),
        'guides'          => __( 'Guides', 'wpamesh' ),
        'community'       => __( 'Community', 'wpamesh' ),
    ));
});

/**
 * Add custom block styles
 */
add_action( 'init', function() {
    // Gold accent border style for groups
    register_block_style( 'core/group', array(
        'name'  => 'gold-accent',
        'label' => __( 'Gold Accent Border', 'wpamesh' ),
    ));

    // Rust accent border style for events
    register_block_style( 'core/group', array(
        'name'  => 'rust-accent',
        'label' => __( 'Rust Accent Border', 'wpamesh' ),
    ));

    // Stats box style
    register_block_style( 'core/group', array(
        'name'  => 'stat-box',
        'label' => __( 'Stat Box', 'wpamesh' ),
    ));
});

/**
 * Disable WordPress emoji scripts (optional performance optimization)
 */
add_action( 'init', function() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
});

/**
 * Node helper functions for formatting ACF field values
 */

/**
 * Convert feet to meters
 *
 * @param float $feet Height in feet.
 * @return int Height in meters (rounded).
 */
function wpamesh_feet_to_meters( $feet ) {
    return round( $feet * 0.3048 );
}

/**
 * Format height with both feet and meters
 *
 * @param mixed $feet Height in feet (from ACF number field).
 * @return string Formatted height string or empty string if invalid.
 */
function wpamesh_format_height( $feet ) {
    if ( ! is_numeric( $feet ) || $feet === '' || $feet === null ) {
        return '';
    }
    $meters = wpamesh_feet_to_meters( $feet );
    return sprintf( '%s ft (%s m)', number_format( $feet ), number_format( $meters ) );
}

/**
 * Format antenna gain with dB unit
 *
 * @param mixed $gain Antenna gain value (from ACF number field).
 * @return string Formatted gain string or empty string if invalid.
 */
function wpamesh_format_antenna_gain( $gain ) {
    if ( ! is_numeric( $gain ) || $gain === '' || $gain === null ) {
        return '';
    }
    return $gain . ' dB';
}

/**
 * =============================================================================
 * Meshview API Integration
 * =============================================================================
 * Functions to fetch and cache data from the meshview API at map.wpamesh.net
 */

define( 'WPAMESH_API_BASE', 'https://map.wpamesh.net/api' );
define( 'WPAMESH_CACHE_EXPIRY', 15 * MINUTE_IN_SECONDS );

/**
 * Convert hex node ID to decimal
 *
 * @param string $hex_id Hex node ID (with or without ! prefix, e.g., "!a5060ad0" or "a5060ad0").
 * @return int Decimal node ID.
 */
function wpamesh_hex_to_decimal( $hex_id ) {
    // Remove ! prefix if present
    $hex_id = ltrim( $hex_id, '!' );
    return hexdec( $hex_id );
}

/**
 * Convert decimal node ID to hex format
 *
 * @param int $decimal_id Decimal node ID.
 * @return string Hex node ID with ! prefix, zero-padded to 8 chars (e.g., "!a5060ad0").
 */
function wpamesh_decimal_to_hex( $decimal_id ) {
    return '!' . str_pad( dechex( $decimal_id ), 8, '0', STR_PAD_LEFT );
}

/**
 * Fetch data from meshview API with caching
 *
 * @param string $endpoint API endpoint (e.g., '/nodes', '/stats').
 * @param array  $params   Query parameters.
 * @param int    $expiry   Cache expiry in seconds (default: WPAMESH_CACHE_EXPIRY).
 * @return array|null API response data or null on error.
 */
function wpamesh_api_fetch( $endpoint, $params = array(), $expiry = WPAMESH_CACHE_EXPIRY ) {
    // Build cache key from endpoint and params
    $cache_key = 'wpamesh_api_' . md5( $endpoint . serialize( $params ) );

    // Check cache first
    $cached = get_transient( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    // Build URL
    $url = WPAMESH_API_BASE . $endpoint;
    if ( ! empty( $params ) ) {
        $url = add_query_arg( $params, $url );
    }

    // Fetch from API
    $response = wp_remote_get( $url, array(
        'timeout' => 10,
        'headers' => array(
            'Accept' => 'application/json',
        ),
    ));

    if ( is_wp_error( $response ) ) {
        error_log( 'WPAMesh API error: ' . $response->get_error_message() );
        return null;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        error_log( 'WPAMesh API JSON error: ' . json_last_error_msg() );
        return null;
    }

    // Cache the result
    set_transient( $cache_key, $data, $expiry );

    return $data;
}

/**
 * Get network statistics for badges
 *
 * @return array Network stats with keys: total_nodes, active_nodes, routers, packets_24h
 */
function wpamesh_get_network_stats() {
    $cache_key = 'wpamesh_network_stats';
    $cached = get_transient( $cache_key );

    if ( false !== $cached ) {
        return $cached;
    }

    $stats = array(
        'total_nodes'  => 0,
        'active_nodes' => 0,
        'routers'      => 0,
        'packets_24h'  => 0,
    );

    // Get nodes active in the last week (excludes flyover connections to distant meshes)
    $weekly_nodes = wpamesh_api_fetch( '/nodes', array( 'days_active' => 7 ) );
    if ( $weekly_nodes && isset( $weekly_nodes['nodes'] ) ) {
        $stats['total_nodes'] = count( $weekly_nodes['nodes'] );

        // Count routers from weekly active nodes
        foreach ( $weekly_nodes['nodes'] as $node ) {
            if ( in_array( $node['role'], array( 'ROUTER', 'ROUTER_LATE', 'REPEATER' ), true ) ) {
                $stats['routers']++;
            }
        }
    }

    // Get active nodes (last 3 days) for "recently active" count
    $active_nodes = wpamesh_api_fetch( '/nodes', array( 'days_active' => 3 ) );
    if ( $active_nodes && isset( $active_nodes['nodes'] ) ) {
        $stats['active_nodes'] = count( $active_nodes['nodes'] );
    }

    // Get packet stats for last 24 hours
    $packet_stats = wpamesh_api_fetch( '/stats', array(
        'period_type' => 'hour',
        'length'      => 24,
    ));
    if ( $packet_stats && isset( $packet_stats['data'] ) ) {
        foreach ( $packet_stats['data'] as $period ) {
            $stats['packets_24h'] += $period['count'];
        }
    }

    // Cache for 5 minutes
    set_transient( $cache_key, $stats, WPAMESH_CACHE_EXPIRY );

    return $stats;
}

/**
 * Get node information by hex ID
 *
 * @param string $hex_id Hex node ID (e.g., "!a5060ad0").
 * @return array|null Node data or null if not found.
 */
function wpamesh_get_node_by_hex_id( $hex_id ) {
    $decimal_id = wpamesh_hex_to_decimal( $hex_id );
    return wpamesh_get_node_by_id( $decimal_id );
}

/**
 * Get node information by decimal ID
 *
 * @param int $node_id Decimal node ID.
 * @return array|null Node data or null if not found.
 */
function wpamesh_get_node_by_id( $node_id ) {
    $node_id = intval( $node_id );
    $cache_key = 'wpamesh_node_' . $node_id;

    $cached = get_transient( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    // Fetch all nodes and find the one we want
    $all_nodes = wpamesh_api_fetch( '/nodes' );
    if ( ! $all_nodes || ! isset( $all_nodes['nodes'] ) ) {
        return null;
    }

    foreach ( $all_nodes['nodes'] as $node ) {
        if ( intval( $node['node_id'] ) === $node_id ) {
            // Add hex ID for convenience
            $node['hex_id'] = wpamesh_decimal_to_hex( $node['node_id'] );

            // Check for recent packet activity (more accurate than last_seen for routers)
            // Routers may only broadcast device info every 24h but still route packets
            $last_activity = wpamesh_get_node_last_activity( $node_id );

            // Use packet activity if more recent than last_seen
            $last_seen_seconds = $node['last_seen_us'] / 1000000;
            if ( $last_activity && $last_activity > $last_seen_seconds ) {
                $last_seen_seconds = $last_activity;
            }

            // Consider online if active in last 3 hours (routers may have long intervals)
            $node['is_online'] = ( time() - $last_seen_seconds ) < ( 3 * HOUR_IN_SECONDS );
            $node['last_seen_formatted'] = wpamesh_format_last_seen_seconds( $last_seen_seconds );

            // Cache individual node for 5 minutes
            set_transient( $cache_key, $node, WPAMESH_CACHE_EXPIRY );

            return $node;
        }
    }

    return null;
}

/**
 * Get node's last packet activity timestamp
 *
 * @param int $node_id Decimal node ID.
 * @return int|null Unix timestamp of last activity, or null if no recent activity.
 */
function wpamesh_get_node_last_activity( $node_id ) {
    // Check packet stats for last 24 hours
    $stats = wpamesh_api_fetch( '/stats', array(
        'from_node'   => intval( $node_id ),
        'period_type' => 'hour',
        'length'      => 24,
    ));

    if ( ! $stats || ! isset( $stats['data'] ) || empty( $stats['data'] ) ) {
        return null;
    }

    // Find the most recent period with activity
    $latest_period = null;
    foreach ( $stats['data'] as $period ) {
        if ( $period['count'] > 0 ) {
            $latest_period = $period['period'];
        }
    }

    if ( ! $latest_period ) {
        return null;
    }

    // Parse the period timestamp (format: "2025-12-30 18:00")
    $timestamp = strtotime( $latest_period . ' UTC' );
    // Add 1 hour since we know activity happened during this hour
    return $timestamp ? $timestamp + HOUR_IN_SECONDS : null;
}

/**
 * Format last seen timestamp to human-readable string (microseconds input)
 *
 * @param int $last_seen_us Timestamp in microseconds.
 * @return string Human-readable time string.
 */
function wpamesh_format_last_seen( $last_seen_us ) {
    return wpamesh_format_last_seen_seconds( $last_seen_us / 1000000 );
}

/**
 * Format last seen timestamp to human-readable string (seconds input)
 *
 * @param int $last_seen_seconds Unix timestamp in seconds.
 * @return string Human-readable time string.
 */
function wpamesh_format_last_seen_seconds( $last_seen_seconds ) {
    $diff = time() - $last_seen_seconds;

    if ( $diff < 60 ) {
        return __( 'Just now', 'wpamesh' );
    } elseif ( $diff < HOUR_IN_SECONDS ) {
        $minutes = floor( $diff / 60 );
        return sprintf( _n( '%d minute ago', '%d minutes ago', $minutes, 'wpamesh' ), $minutes );
    } elseif ( $diff < DAY_IN_SECONDS ) {
        $hours = floor( $diff / HOUR_IN_SECONDS );
        return sprintf( _n( '%d hour ago', '%d hours ago', $hours, 'wpamesh' ), $hours );
    } else {
        $days = floor( $diff / DAY_IN_SECONDS );
        return sprintf( _n( '%d day ago', '%d days ago', $days, 'wpamesh' ), $days );
    }
}

/**
 * Get node connections/edges
 *
 * @param int $node_id Decimal node ID.
 * @return array Array of edges for this node.
 */
function wpamesh_get_node_edges( $node_id ) {
    $edges = wpamesh_api_fetch( '/edges', array( 'node_id' => intval( $node_id ) ) );
    return $edges && isset( $edges['edges'] ) ? $edges['edges'] : array();
}

/**
 * Parse protobuf text format payload from telemetry packets
 *
 * The meshview API returns telemetry payloads in protobuf text format like:
 * "device_metrics {\n  channel_utilization: 12.5\n  air_util_tx: 1.2\n}"
 *
 * @param string $payload Raw payload string from API.
 * @return array Parsed key-value pairs.
 */
function wpamesh_parse_telemetry_payload( $payload ) {
    $result = array();

    if ( ! is_string( $payload ) ) {
        return $result;
    }

    // Extract values using regex for "key: value" patterns
    if ( preg_match( '/channel_utilization:\s*([\d.]+)/', $payload, $matches ) ) {
        $result['channel_utilization'] = floatval( $matches[1] );
    }

    if ( preg_match( '/air_util_tx:\s*([\d.]+)/', $payload, $matches ) ) {
        $result['air_util_tx'] = floatval( $matches[1] );
    }

    if ( preg_match( '/battery_level:\s*(\d+)/', $payload, $matches ) ) {
        $result['battery_level'] = intval( $matches[1] );
    }

    if ( preg_match( '/voltage:\s*([\d.-]+)/', $payload, $matches ) ) {
        $result['voltage'] = floatval( $matches[1] );
    }

    if ( preg_match( '/uptime_seconds:\s*(\d+)/', $payload, $matches ) ) {
        $result['uptime_seconds'] = intval( $matches[1] );
    }

    return $result;
}

/**
 * Get channel utilization metrics from router telemetry
 *
 * Fetches recent device telemetry (port 67) from routers and calculates
 * average channel utilization and air time percentages.
 *
 * @return array Metrics with keys: channel_utilization, air_util_tx, reporting_nodes
 */
function wpamesh_get_channel_metrics() {
    $cache_key = 'wpamesh_channel_metrics';
    $cached = get_transient( $cache_key );

    if ( false !== $cached ) {
        return $cached;
    }

    $metrics = array(
        'channel_utilization' => null,
        'air_util_tx'         => null,
        'reporting_nodes'     => 0,
    );

    // Get recently active nodes and filter to routers
    $all_nodes = wpamesh_api_fetch( '/nodes', array( 'days_active' => 3 ) );

    if ( ! $all_nodes || ! isset( $all_nodes['nodes'] ) || empty( $all_nodes['nodes'] ) ) {
        set_transient( $cache_key, $metrics, WPAMESH_CACHE_EXPIRY );
        return $metrics;
    }

    // Filter to router roles only
    $router_roles = array( 'ROUTER', 'ROUTER_LATE', 'REPEATER' );
    $router_ids   = array();
    foreach ( $all_nodes['nodes'] as $node ) {
        if ( in_array( $node['role'], $router_roles, true ) ) {
            $router_ids[] = $node['node_id'];
        }
    }

    if ( empty( $router_ids ) ) {
        set_transient( $cache_key, $metrics, WPAMESH_CACHE_EXPIRY );
        return $metrics;
    }

    // Fetch recent telemetry packets (port 67 = TELEMETRY_APP)
    $telemetry = wpamesh_api_fetch( '/packets', array(
        'port_num' => 67,
        'length'   => 200,
    ));

    if ( ! $telemetry || ! isset( $telemetry['packets'] ) ) {
        set_transient( $cache_key, $metrics, WPAMESH_CACHE_EXPIRY );
        return $metrics;
    }

    // Collect latest metrics per router (avoid double-counting)
    $node_metrics = array();

    foreach ( $telemetry['packets'] as $packet ) {
        $from_node_id = $packet['from_node_id'] ?? null;

        // Only include routers
        if ( ! $from_node_id || ! in_array( $from_node_id, $router_ids, true ) ) {
            continue;
        }

        // Skip if we already have data for this node (we want the most recent)
        if ( isset( $node_metrics[ $from_node_id ] ) ) {
            continue;
        }

        $payload = wpamesh_parse_telemetry_payload( $packet['payload'] ?? '' );

        // Check for channel utilization data
        if ( isset( $payload['channel_utilization'] ) ) {
            $node_metrics[ $from_node_id ] = array(
                'channel_utilization' => $payload['channel_utilization'],
                'air_util_tx'         => $payload['air_util_tx'] ?? 0,
            );
        }
    }

    // Calculate averages
    if ( ! empty( $node_metrics ) ) {
        $total_cu  = 0;
        $total_air = 0;

        foreach ( $node_metrics as $data ) {
            $total_cu  += $data['channel_utilization'];
            $total_air += $data['air_util_tx'];
        }

        $count = count( $node_metrics );
        $metrics['channel_utilization'] = round( $total_cu / $count, 1 );
        $metrics['air_util_tx']         = round( $total_air / $count, 2 );
        $metrics['reporting_nodes']     = $count;
    }

    set_transient( $cache_key, $metrics, WPAMESH_CACHE_EXPIRY );

    return $metrics;
}

/**
 * Get channel utilization metrics for a specific node
 *
 * Averages across recent telemetry packets to smooth out spikes.
 *
 * @param int $node_id Decimal node ID.
 * @return array|null Metrics with keys: channel_utilization, air_util_tx, sample_count, or null if not available.
 */
function wpamesh_get_node_channel_metrics( $node_id ) {
    $node_id   = intval( $node_id );
    $cache_key = 'wpamesh_node_metrics_' . $node_id;

    $cached = get_transient( $cache_key );
    if ( false !== $cached ) {
        return $cached;
    }

    // Fetch recent telemetry packets from this node
    $telemetry = wpamesh_api_fetch( '/packets', array(
        'port_num'     => 67,
        'from_node_id' => $node_id,
        'length'       => 20,
    ));

    if ( ! $telemetry || ! isset( $telemetry['packets'] ) || empty( $telemetry['packets'] ) ) {
        set_transient( $cache_key, null, WPAMESH_CACHE_EXPIRY );
        return null;
    }

    // Collect all packets with channel utilization data
    $samples = array();

    foreach ( $telemetry['packets'] as $packet ) {
        $payload = wpamesh_parse_telemetry_payload( $packet['payload'] ?? '' );

        if ( isset( $payload['channel_utilization'] ) ) {
            $samples[] = array(
                'channel_utilization' => $payload['channel_utilization'],
                'air_util_tx'         => $payload['air_util_tx'] ?? 0,
            );
        }
    }

    if ( empty( $samples ) ) {
        set_transient( $cache_key, null, WPAMESH_CACHE_EXPIRY );
        return null;
    }

    // Calculate averages across all samples
    $total_cu  = 0;
    $total_air = 0;

    foreach ( $samples as $sample ) {
        $total_cu  += $sample['channel_utilization'];
        $total_air += $sample['air_util_tx'];
    }

    $count   = count( $samples );
    $metrics = array(
        'channel_utilization' => round( $total_cu / $count, 1 ),
        'air_util_tx'         => round( $total_air / $count, 2 ),
        'sample_count'        => $count,
    );

    set_transient( $cache_key, $metrics, WPAMESH_CACHE_EXPIRY );
    return $metrics;
}

/**
 * Clear all meshview API caches
 * Useful when you need to force refresh data
 */
function wpamesh_clear_api_cache() {
    global $wpdb;

    // Delete all transients starting with wpamesh_
    $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpamesh_%' OR option_name LIKE '_transient_timeout_wpamesh_%'"
    );
}

/**
 * =============================================================================
 * Node List Shortcode
 * =============================================================================
 * Displays a list of member nodes, optionally filtered by tier.
 *
 * Usage:
 *   [wpamesh_node_list]                         - All nodes grouped by tier
 *   [wpamesh_node_list tier="core_router"]      - Only core routers
 *   [wpamesh_node_list tier="supplemental"]     - Only medium profile nodes
 *   [wpamesh_node_list tier="gateway"]          - Only gateways
 *   [wpamesh_node_list tier="service"]          - Only other services
 *   [wpamesh_node_list tier="core_router" show_title="false"] - Without heading
 */
add_shortcode( 'wpamesh_node_list', 'wpamesh_node_list_shortcode' );

function wpamesh_node_list_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'tier'       => '',
        'show_title' => 'true',
    ), $atts, 'wpamesh_node_list' );

    $tier_filter = sanitize_key( $atts['tier'] );
    $show_title  = $atts['show_title'] !== 'false';

    // Define tier labels
    $tier_labels = array(
        'core_router'  => __( 'Routers', 'wpamesh' ),
        'supplemental' => __( 'Medium Profile', 'wpamesh' ),
        'gateway'      => __( 'Gateways', 'wpamesh' ),
        'service'      => __( 'Other Services', 'wpamesh' ),
    );

    // Query posts in the Node-Detail category
    $nodes_query = new WP_Query( array(
        'category_name'  => 'node-detail',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    // Group nodes by tier
    $grouped_nodes = array();
    foreach ( $tier_labels as $key => $label ) {
        $grouped_nodes[ $key ] = array();
    }
    $grouped_nodes['uncategorized'] = array();

    if ( $nodes_query->have_posts() ) {
        while ( $nodes_query->have_posts() ) {
            $nodes_query->the_post();
            $post_id = get_the_ID();

            // Get tier field
            $node_tier_field = get_field( 'node_tier', $post_id );
            $node_tier = is_array( $node_tier_field ) ? $node_tier_field['value'] : ( $node_tier_field ?: 'uncategorized' );

            // If filtering by tier, skip non-matching nodes
            if ( $tier_filter && $node_tier !== $tier_filter ) {
                continue;
            }

            // Get node fields
            $long_name  = get_field( 'long_name', $post_id ) ?: get_the_title();
            $short_name = get_field( 'short_name', $post_id ) ?: '📡';
            $node_id    = get_field( 'node_id', $post_id );
            $location   = get_field( 'location_name', $post_id );

            // Get role field
            $role_field = get_field( 'role', $post_id );
            $role_label = is_array( $role_field ) ? $role_field['label'] : ( $role_field ?: '' );

            // Get live status and channel metrics if node_id is set
            $is_online          = null;
            $has_live_data      = false;
            $channel_util       = null;
            $air_util           = null;
            if ( $node_id ) {
                $live_node = wpamesh_get_node_by_hex_id( $node_id );
                if ( $live_node ) {
                    $has_live_data = true;
                    $is_online     = $live_node['is_online'];

                    // Get channel metrics for this node
                    $node_metrics = wpamesh_get_node_channel_metrics( $live_node['node_id'] );
                    if ( $node_metrics ) {
                        $channel_util = $node_metrics['channel_utilization'];
                        $air_util     = $node_metrics['air_util_tx'];
                    }
                }
            }

            $node_data = array(
                'long_name'     => $long_name,
                'short_name'    => $short_name,
                'permalink'     => get_permalink(),
                'location'      => $location,
                'role'          => $role_label,
                'has_live_data' => $has_live_data,
                'is_online'     => $is_online,
                'channel_util'  => $channel_util,
                'air_util'      => $air_util,
            );

            if ( isset( $grouped_nodes[ $node_tier ] ) ) {
                $grouped_nodes[ $node_tier ][] = $node_data;
            } else {
                $grouped_nodes['uncategorized'][] = $node_data;
            }
        }
        wp_reset_postdata();
    }

    // Remove empty groups
    $grouped_nodes = array_filter( $grouped_nodes );

    if ( empty( $grouped_nodes ) ) {
        return '';
    }

    // Build output
    ob_start();

    foreach ( $grouped_nodes as $tier_key => $nodes ) {
        if ( empty( $nodes ) ) {
            continue;
        }
        $tier_label = isset( $tier_labels[ $tier_key ] ) ? $tier_labels[ $tier_key ] : __( 'Other', 'wpamesh' );
        ?>
        <div class="wpamesh-node-tier wpamesh-tier-<?php echo esc_attr( $tier_key ); ?>">
            <?php if ( $show_title ) : ?>
            <h3 class="wpamesh-tier-title"><?php echo esc_html( $tier_label ); ?></h3>
            <?php endif; ?>
            <ul class="wpamesh-node-list">
                <?php foreach ( $nodes as $node ) : ?>
                <li class="wpamesh-node-item">
                    <div class="wpamesh-node-info">
                        <h4>
                            <?php if ( $node['has_live_data'] ) : ?>
                            <span class="wpamesh-status-dot <?php echo $node['is_online'] ? 'online' : 'offline'; ?>" title="<?php echo $node['is_online'] ? esc_attr__( 'Online', 'wpamesh' ) : esc_attr__( 'Offline', 'wpamesh' ); ?>"></span>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( $node['permalink'] ); ?>">
                                <?php echo esc_html( $node['long_name'] ); ?>
                            </a>
                        </h4>
                        <div class="meta">
                            <?php if ( $node['role'] ) : ?>
                            <span class="role"><?php echo esc_html( $node['role'] ); ?></span>
                            <?php endif; ?>
                            <?php if ( $node['role'] && $node['location'] ) : ?>
                            <span class="separator">·</span>
                            <?php endif; ?>
                            <?php if ( $node['location'] ) : ?>
                            <span class="location"><?php echo esc_html( $node['location'] ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ( $node['channel_util'] !== null || $node['air_util'] !== null ) : ?>
                    <div class="wpamesh-node-metrics">
                        <?php if ( $node['channel_util'] !== null ) : ?>
                        <span class="metric" title="<?php esc_attr_e( 'Channel Utilization', 'wpamesh' ); ?>"><?php echo esc_html( $node['channel_util'] ); ?>% <span class="label"><?php esc_html_e( 'Ch', 'wpamesh' ); ?></span></span>
                        <?php endif; ?>
                        <?php if ( $node['air_util'] !== null ) : ?>
                        <span class="metric" title="<?php esc_attr_e( 'Airtime TX', 'wpamesh' ); ?>"><?php echo esc_html( $node['air_util'] ); ?>% <span class="label"><?php esc_html_e( 'Air', 'wpamesh' ); ?></span></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    return ob_get_clean();
}
