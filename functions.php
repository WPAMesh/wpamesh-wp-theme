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
 * Register custom blocks
 */
add_action( 'init', function() {
    register_block_type( get_template_directory() . '/blocks/network-stats' );
    register_block_type( get_template_directory() . '/blocks/stat-box' );
    register_block_type( get_template_directory() . '/blocks/node-list' );
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
define( 'WPAMESH_DAYS_ACTIVE', 3 );        // Days to consider a node "active"
define( 'WPAMESH_DAYS_TOTAL', 7 );         // Days to include in total node count
define( 'WPAMESH_ONLINE_HOURS', 6 );       // Hours since last activity to consider "online"

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
    $weekly_nodes = wpamesh_api_fetch( '/nodes', array( 'days_active' => WPAMESH_DAYS_TOTAL ) );
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
    $active_nodes = wpamesh_api_fetch( '/nodes', array( 'days_active' => WPAMESH_DAYS_ACTIVE ) );
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
            $node['is_online'] = ( time() - $last_seen_seconds ) < ( WPAMESH_ONLINE_HOURS * HOUR_IN_SECONDS );
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
    $all_nodes = wpamesh_api_fetch( '/nodes', array( 'days_active' => WPAMESH_DAYS_ACTIVE ) );

    if ( ! $all_nodes || ! isset( $all_nodes['nodes'] ) || empty( $all_nodes['nodes'] ) ) {
        set_transient( $cache_key, $metrics, WPAMESH_CACHE_EXPIRY );
        return $metrics;
    }

    // Filter to router roles only
    $router_roles = array( 'ROUTER', 'ROUTER_LATE', 'REPEATER' );
    $routers      = array();
    foreach ( $all_nodes['nodes'] as $node ) {
        if ( in_array( $node['role'], $router_roles, true ) ) {
            $routers[] = $node;
        }
    }

    if ( empty( $routers ) ) {
        set_transient( $cache_key, $metrics, WPAMESH_CACHE_EXPIRY );
        return $metrics;
    }

    // Fetch metrics for each router individually
    // This ensures we get each router's telemetry even if they report infrequently
    $total_cu  = 0;
    $total_air = 0;
    $reporting = 0;

    foreach ( $routers as $router ) {
        $node_metrics = wpamesh_get_node_channel_metrics( $router['node_id'] );
        if ( $node_metrics && isset( $node_metrics['channel_utilization'] ) ) {
            $total_cu  += $node_metrics['channel_utilization'];
            $total_air += $node_metrics['air_util_tx'];
            $reporting++;
        }
    }

    if ( $reporting > 0 ) {
        $metrics['channel_utilization'] = round( $total_cu / $reporting, 1 );
        $metrics['air_util_tx']         = round( $total_air / $reporting, 2 );
        $metrics['reporting_nodes']     = $reporting;
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
        'length'       => 100,
    ));

    if ( ! $telemetry || ! isset( $telemetry['packets'] ) || empty( $telemetry['packets'] ) ) {
        set_transient( $cache_key, null, WPAMESH_CACHE_EXPIRY );
        return null;
    }

    // Only include packets within our activity window
    $max_age_seconds = WPAMESH_DAYS_ACTIVE * DAY_IN_SECONDS;
    $cutoff_time     = time() - $max_age_seconds;

    // Collect all packets with channel utilization data
    $samples = array();

    foreach ( $telemetry['packets'] as $packet ) {
        // Skip packets older than our window (import_time_us is in microseconds)
        $import_time_us = $packet['import_time_us'] ?? 0;
        if ( $import_time_us / 1000000 < $cutoff_time ) {
            continue;
        }

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
 * Sidebar Stats - Aggregated Cache with Background Refresh & AJAX Loading
 * =============================================================================
 * Combines all sidebar statistics into a single cache for better performance.
 * Uses WP-Cron for background refresh so page loads never wait for API calls.
 * Provides AJAX endpoint for lazy loading on the frontend.
 */

/**
 * Get all sidebar stats from aggregated cache
 *
 * Returns cached data immediately. If cache is empty, returns defaults
 * and schedules a background refresh.
 *
 * @return array Aggregated stats for sidebar display
 */
function wpamesh_get_sidebar_stats() {
    $cache_key = 'wpamesh_sidebar_stats';
    $cached = get_transient( $cache_key );

    if ( false !== $cached ) {
        return $cached;
    }

    // Return defaults and schedule background refresh
    // This ensures page loads are never blocked by API calls
    if ( ! wp_next_scheduled( 'wpamesh_refresh_sidebar_stats' ) ) {
        wp_schedule_single_event( time(), 'wpamesh_refresh_sidebar_stats' );
    }

    return array(
        'total_nodes'         => 0,
        'active_nodes'        => 0,
        'routers'             => 0,
        'packets_24h'         => 0,
        'channel_utilization' => null,
        'air_util_tx'         => null,
        'last_updated'        => null,
    );
}

/**
 * Refresh sidebar stats in background
 *
 * Called by WP-Cron. Fetches all stats and stores in a single cache.
 */
function wpamesh_do_refresh_sidebar_stats() {
    // Get network stats (makes API calls)
    $network_stats = wpamesh_get_network_stats();

    // Get channel metrics (makes API calls per router)
    $channel_metrics = wpamesh_get_channel_metrics();

    // Combine into single cache
    $stats = array(
        'total_nodes'         => $network_stats['total_nodes'],
        'active_nodes'        => $network_stats['active_nodes'],
        'routers'             => $network_stats['routers'],
        'packets_24h'         => $network_stats['packets_24h'],
        'channel_utilization' => $channel_metrics['channel_utilization'],
        'air_util_tx'         => $channel_metrics['air_util_tx'],
        'last_updated'        => time(),
    );

    // Cache for slightly longer than the cron interval to avoid gaps
    set_transient( 'wpamesh_sidebar_stats', $stats, 20 * MINUTE_IN_SECONDS );

    return $stats;
}
add_action( 'wpamesh_refresh_sidebar_stats', 'wpamesh_do_refresh_sidebar_stats' );

/**
 * Schedule recurring background refresh
 */
function wpamesh_schedule_stats_refresh() {
    if ( ! wp_next_scheduled( 'wpamesh_refresh_sidebar_stats' ) ) {
        wp_schedule_event( time(), 'wpamesh_fifteen_minutes', 'wpamesh_refresh_sidebar_stats' );
    }
}
add_action( 'init', 'wpamesh_schedule_stats_refresh' );

/**
 * Add custom cron interval
 */
add_filter( 'cron_schedules', function( $schedules ) {
    $schedules['wpamesh_fifteen_minutes'] = array(
        'interval' => 15 * MINUTE_IN_SECONDS,
        'display'  => __( 'Every 15 Minutes', 'wpamesh' ),
    );
    return $schedules;
});

/**
 * Clean up cron on theme deactivation
 */
function wpamesh_deactivate_cron() {
    $timestamp = wp_next_scheduled( 'wpamesh_refresh_sidebar_stats' );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'wpamesh_refresh_sidebar_stats' );
    }
}
add_action( 'switch_theme', 'wpamesh_deactivate_cron' );

/**
 * AJAX endpoint for lazy loading sidebar stats
 *
 * If cache is empty, performs a synchronous refresh since AJAX requests
 * are already async from the user's perspective.
 */
add_action( 'wp_ajax_wpamesh_sidebar_stats', 'wpamesh_ajax_sidebar_stats' );
add_action( 'wp_ajax_nopriv_wpamesh_sidebar_stats', 'wpamesh_ajax_sidebar_stats' );

function wpamesh_ajax_sidebar_stats() {
    $stats = wpamesh_get_sidebar_stats();

    // If cache is empty (last_updated is null), do a synchronous refresh
    if ( $stats['last_updated'] === null ) {
        $stats = wpamesh_do_refresh_sidebar_stats();
    }

    wp_send_json_success( array(
        'total_nodes'         => number_format( $stats['total_nodes'] ),
        'active_nodes'        => number_format( $stats['active_nodes'] ),
        'routers'             => number_format( $stats['routers'] ),
        'packets_24h'         => number_format( $stats['packets_24h'] ),
        'channel_utilization' => $stats['channel_utilization'] !== null ? $stats['channel_utilization'] . '%' : '—',
        'air_util_tx'         => $stats['air_util_tx'] !== null ? $stats['air_util_tx'] . '%' : '—',
        'last_updated'        => $stats['last_updated'] ? human_time_diff( $stats['last_updated'] ) . ' ago' : 'never',
    ) );
}

/**
 * Enqueue AJAX scripts for lazy loading
 */
add_action( 'wp_enqueue_scripts', function() {
    $version = wp_get_theme()->get( 'Version' );

    // Sidebar stats script
    wp_enqueue_script(
        'wpamesh-sidebar-stats',
        get_theme_file_uri( 'assets/js/sidebar-stats.js' ),
        array(),
        $version,
        true
    );

    wp_localize_script( 'wpamesh-sidebar-stats', 'wpameshStats', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'action'  => 'wpamesh_sidebar_stats',
    ) );

    // Node list script
    wp_enqueue_script(
        'wpamesh-node-list',
        get_theme_file_uri( 'assets/js/node-list.js' ),
        array(),
        $version,
        true
    );

    wp_localize_script( 'wpamesh-node-list', 'wpameshNodes', array(
        'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
        'action'       => 'wpamesh_node_list_data',
        'singleAction' => 'wpamesh_single_node_data',
    ) );

    // Guide page TOC scroll tracking script
    // Only loads on pages with .wpa-toc-list elements
    wp_enqueue_script(
        'wpamesh-guide-toc',
        get_theme_file_uri( 'assets/js/guide-toc.js' ),
        array(),
        $version,
        true
    );
});

/**
 * =============================================================================
 * Node List Cache with Background Refresh & AJAX Loading
 * =============================================================================
 * Caches node list data including live status to avoid per-node API calls.
 */

/**
 * Get all node list data from cache
 *
 * Returns cached data immediately. If cache is empty, returns basic node info
 * without live status and schedules a background refresh.
 *
 * @return array Array of nodes with live status data
 */
function wpamesh_get_node_list_data() {
    $cache_key = 'wpamesh_node_list_data';
    $cached = get_transient( $cache_key );

    if ( false !== $cached ) {
        return $cached;
    }

    // Schedule background refresh if not already scheduled
    if ( ! wp_next_scheduled( 'wpamesh_refresh_node_list_data' ) ) {
        wp_schedule_single_event( time(), 'wpamesh_refresh_node_list_data' );
    }

    // Return empty array - block will render without live data
    return array();
}

/**
 * Refresh node list data in background
 *
 * Called by WP-Cron. Fetches all nodes with their live status and metrics.
 */
function wpamesh_do_refresh_node_list_data() {
    // Define tier labels for grouping
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

    $nodes_data = array();

    if ( $nodes_query->have_posts() ) {
        while ( $nodes_query->have_posts() ) {
            $nodes_query->the_post();
            $post_id = get_the_ID();

            // Get tier field
            $node_tier_field = get_field( 'node_tier', $post_id );
            $node_tier = is_array( $node_tier_field ) ? $node_tier_field['value'] : ( $node_tier_field ?: 'uncategorized' );

            // Get node fields
            $long_name  = get_field( 'long_name', $post_id ) ?: get_the_title();
            $short_name = get_field( 'short_name', $post_id ) ?: '';
            $node_id    = get_field( 'node_id', $post_id );
            $location   = get_field( 'location_name', $post_id );

            // Get role field
            $role_field = get_field( 'role', $post_id );
            $role_label = is_array( $role_field ) ? $role_field['label'] : ( $role_field ?: '' );

            // Get live status and channel metrics if node_id is set
            $is_online     = null;
            $has_live_data = false;
            $channel_util  = null;
            $air_util      = null;
            $last_seen     = null;
            $last_seen_ts  = null;

            if ( $node_id ) {
                $live_node = wpamesh_get_node_by_hex_id( $node_id );
                if ( $live_node ) {
                    $has_live_data = true;
                    $is_online     = $live_node['is_online'];
                    $last_seen     = $live_node['last_seen_formatted'];
                    $last_seen_ts  = (int) ( $live_node['last_seen_us'] / 1000000 );

                    // Get channel metrics for this node
                    $node_metrics = wpamesh_get_node_channel_metrics( $live_node['node_id'] );
                    if ( $node_metrics ) {
                        $channel_util = $node_metrics['channel_utilization'];
                        $air_util     = $node_metrics['air_util_tx'];
                    }
                }
            }

            $node_data = array(
                'post_id'       => $post_id,
                'node_id'       => $node_id,
                'tier'          => $node_tier,
                'long_name'     => $long_name,
                'short_name'    => $short_name,
                'permalink'     => get_permalink(),
                'location'      => $location,
                'role'          => $role_label,
                'has_live_data' => $has_live_data,
                'is_online'     => $is_online,
                'last_seen'     => $last_seen,
                'last_seen_ts'  => $last_seen_ts,
                'channel_util'  => $channel_util,
                'air_util'      => $air_util,
            );

            $nodes_data[] = $node_data;

            // Also cache individual node data for single-node lookups
            if ( $node_id ) {
                set_transient( 'wpamesh_node_' . $node_id, $node_data, 20 * MINUTE_IN_SECONDS );
            }
        }
        wp_reset_postdata();
    }

    // Cache aggregated list for slightly longer than the cron interval
    set_transient( 'wpamesh_node_list_data', $nodes_data, 20 * MINUTE_IN_SECONDS );

    return $nodes_data;
}
add_action( 'wpamesh_refresh_node_list_data', 'wpamesh_do_refresh_node_list_data' );

/**
 * Get single node data from per-node cache
 *
 * Uses individual node transients for O(1) lookups instead of iterating
 * through the full node list. Falls back to list cache if per-node cache
 * is missing.
 *
 * @param string $node_id The hex node ID (e.g., "!abcd1234").
 * @return array|null Node data array or null if not found.
 */
function wpamesh_get_single_node_data( $node_id ) {
    if ( empty( $node_id ) ) {
        return null;
    }

    // Try per-node cache first (O(1) lookup)
    $cached = get_transient( 'wpamesh_node_' . $node_id );
    if ( false !== $cached ) {
        return $cached;
    }

    // Fall back to searching the list cache
    $all_nodes = wpamesh_get_node_list_data();
    foreach ( $all_nodes as $node ) {
        if ( $node['node_id'] === $node_id ) {
            // Populate per-node cache for next time
            set_transient( 'wpamesh_node_' . $node_id, $node, 20 * MINUTE_IN_SECONDS );
            return $node;
        }
    }

    return null;
}

/**
 * Schedule recurring background refresh for node list
 */
add_action( 'init', function() {
    if ( ! wp_next_scheduled( 'wpamesh_refresh_node_list_data' ) ) {
        wp_schedule_event( time() + 60, 'wpamesh_fifteen_minutes', 'wpamesh_refresh_node_list_data' );
    }
}, 11 ); // After sidebar stats scheduling

/**
 * Clean up node list cron on theme deactivation
 */
add_action( 'switch_theme', function() {
    $timestamp = wp_next_scheduled( 'wpamesh_refresh_node_list_data' );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'wpamesh_refresh_node_list_data' );
    }
}, 11 );

/**
 * AJAX endpoint for lazy loading node list data
 *
 * If cache is empty, performs a synchronous refresh since AJAX requests
 * are already async from the user's perspective.
 */
add_action( 'wp_ajax_wpamesh_node_list_data', 'wpamesh_ajax_node_list_data' );
add_action( 'wp_ajax_nopriv_wpamesh_node_list_data', 'wpamesh_ajax_node_list_data' );

function wpamesh_ajax_node_list_data() {
    $nodes = wpamesh_get_node_list_data();

    // If cache is empty, do a synchronous refresh for AJAX callers
    // This is acceptable because AJAX is already async from user perspective
    if ( empty( $nodes ) ) {
        $nodes = wpamesh_do_refresh_node_list_data();
    }

    // Format for JSON response - index by node_id for easy lookup
    $formatted = array();
    foreach ( $nodes as $node ) {
        if ( ! $node['node_id'] ) {
            continue;
        }
        $formatted[ $node['node_id'] ] = array(
            'is_online'    => $node['is_online'],
            'last_seen'    => $node['last_seen'],
            'channel_util' => $node['channel_util'] !== null ? $node['channel_util'] . '%' : null,
            'air_util'     => $node['air_util'] !== null ? $node['air_util'] . '%' : null,
        );
    }

    wp_send_json_success( $formatted );
}

/**
 * Get channel load level based on Meshtastic firmware thresholds
 *
 * Based on airtime.h from Meshtastic firmware:
 * - polite_channel_util_percent = 25% (telemetry throttling starts)
 * - max_channel_util_percent = 40% (transmissions get skipped)
 *
 * @param float|null $channel_util Channel utilization percentage.
 * @return array{level: string, label: string}|null Load level info or null if no data.
 */
function wpamesh_get_channel_load_level( $channel_util ) {
    if ( $channel_util === null ) {
        return null;
    }

    if ( $channel_util < 25 ) {
        return array(
            'level' => 'low',
            'label' => __( 'Low', 'wpamesh' ),
        );
    } elseif ( $channel_util < 40 ) {
        return array(
            'level' => 'elevated',
            'label' => __( 'Elevated', 'wpamesh' ),
        );
    } else {
        return array(
            'level' => 'high',
            'label' => __( 'High', 'wpamesh' ),
        );
    }
}

/**
 * AJAX endpoint for single node data lookup
 *
 * Uses per-node cache for O(1) lookup. Accepts node_id parameter.
 * Used by node header pages to avoid fetching the full node list.
 */
add_action( 'wp_ajax_wpamesh_single_node_data', 'wpamesh_ajax_single_node_data' );
add_action( 'wp_ajax_nopriv_wpamesh_single_node_data', 'wpamesh_ajax_single_node_data' );

function wpamesh_ajax_single_node_data() {
    $node_id = isset( $_POST['node_id'] ) ? sanitize_text_field( $_POST['node_id'] ) : '';

    if ( empty( $node_id ) ) {
        wp_send_json_error( 'Missing node_id parameter' );
        return;
    }

    $node = wpamesh_get_single_node_data( $node_id );

    if ( ! $node ) {
        wp_send_json_error( 'Node not found' );
        return;
    }

    $load_level = wpamesh_get_channel_load_level( $node['channel_util'] );

    wp_send_json_success( array(
        'is_online'    => $node['is_online'],
        'last_seen'    => $node['last_seen'],
        'channel_util' => $node['channel_util'] !== null ? $node['channel_util'] . '%' : null,
        'air_util'     => $node['air_util'] !== null ? $node['air_util'] . '%' : null,
        'load_level'   => $load_level ? $load_level['level'] : null,
        'load_label'   => $load_level ? $load_level['label'] : null,
    ) );
}

/**
 * =============================================================================
 * REST API Endpoints
 * =============================================================================
 * Custom REST API endpoints to expose node data for external integrations.
 *
 * Endpoints:
 *   GET /wp-json/wpamesh/v1/nodes          - All nodes
 *   GET /wp-json/wpamesh/v1/nodes?tier=X   - Filtered by tier
 *   GET /wp-json/wpamesh/v1/nodes/{id}     - Single node by WP post ID
 */

add_action( 'rest_api_init', 'wpamesh_register_rest_routes' );

/**
 * Fix floating-point precision in REST API JSON responses
 *
 * WordPress uses wp_json_encode which can produce floating-point artifacts.
 * This filter applies serialize_precision to limit decimal places in output.
 */
add_filter( 'rest_pre_serve_request', function( $served, $result, $request, $server ) {
    // Only apply to our namespace
    if ( strpos( $request->get_route(), '/wpamesh/v1/' ) === 0 ) {
        // Set serialize_precision to avoid floating-point artifacts
        ini_set( 'serialize_precision', 10 );
    }
    return $served;
}, 10, 4 );

/**
 * Register REST API routes
 */
function wpamesh_register_rest_routes() {
    register_rest_route( 'wpamesh/v1', '/nodes', array(
        'methods'             => 'GET',
        'callback'            => 'wpamesh_rest_get_nodes',
        'permission_callback' => '__return_true',
        'args'                => array(
            'tier' => array(
                'description'       => 'Filter nodes by tier (core_router, supplemental, gateway, service)',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'validate_callback' => function( $param ) {
                    $valid_tiers = array( 'core_router', 'supplemental', 'gateway', 'service' );
                    return empty( $param ) || in_array( $param, $valid_tiers, true );
                },
            ),
        ),
    ) );

    register_rest_route( 'wpamesh/v1', '/nodes/(?P<id>\d+)', array(
        'methods'             => 'GET',
        'callback'            => 'wpamesh_rest_get_single_node',
        'permission_callback' => '__return_true',
        'args'                => array(
            'id' => array(
                'description'       => 'WordPress post ID of the node',
                'type'              => 'integer',
                'required'          => true,
                'sanitize_callback' => 'absint',
            ),
        ),
    ) );
}

/**
 * Format a node post for REST API response
 *
 * @param int $post_id WordPress post ID.
 * @return array Formatted node data.
 */
function wpamesh_format_node_for_rest( $post_id ) {
    // Get ACF fields
    $long_name    = get_field( 'long_name', $post_id ) ?: get_the_title( $post_id );
    $short_name   = get_field( 'short_name', $post_id ) ?: '';
    $node_id      = get_field( 'node_id', $post_id ) ?: null;
    $latitude     = get_field( 'latitude', $post_id );
    $longitude    = get_field( 'longitude', $post_id );
    $height_agl   = get_field( 'height_agl', $post_id );
    $height_msl   = get_field( 'height_msl', $post_id );
    $antenna_gain = get_field( 'antenna_gain', $post_id );
    $hardware     = get_field( 'hardware', $post_id ) ?: null;
    $maintainer   = get_field( 'maintainer', $post_id ) ?: null;

    // Get node tier (may be array with value/label or string)
    $node_tier_field = get_field( 'node_tier', $post_id );
    $node_tier = is_array( $node_tier_field ) ? $node_tier_field['value'] : ( $node_tier_field ?: null );

    // Get role (may be array with value/label or string)
    $role_field = get_field( 'role', $post_id );
    $role = is_array( $role_field ) ? $role_field['value'] : ( $role_field ?: null );

    // Get live status from per-node cache (avoids blocking API calls)
    $is_online  = null;
    $last_seen  = null;

    if ( $node_id ) {
        $cached_node = wpamesh_get_single_node_data( $node_id );
        if ( $cached_node && $cached_node['has_live_data'] ) {
            $is_online = $cached_node['is_online'];
            // Convert Unix timestamp to ISO 8601 format for REST API
            if ( ! empty( $cached_node['last_seen_ts'] ) ) {
                $last_seen = gmdate( 'Y-m-d\TH:i:s\Z', $cached_node['last_seen_ts'] );
            }
        }
    }

    // Build response structure
    // Note: Using (float) number_format() to avoid PHP floating-point precision artifacts in JSON
    $node_data = array(
        'wp_id'      => $post_id,
        'long_name'  => $long_name,
        'short_name' => $short_name,
        'node_id'    => $node_id,
        'position'   => array(
            'latitude'  => is_numeric( $latitude ) ? (float) number_format( (float) $latitude, 6, '.', '' ) : null,
            'longitude' => is_numeric( $longitude ) ? (float) number_format( (float) $longitude, 6, '.', '' ) : null,
        ),
        'antenna'    => array(
            'agl_m'   => is_numeric( $height_agl ) ? (float) number_format( (float) $height_agl * 0.3048, 1, '.', '' ) : null,
            'msl_m'   => is_numeric( $height_msl ) ? (float) number_format( (float) $height_msl * 0.3048, 1, '.', '' ) : null,
            'gain_dbi' => is_numeric( $antenna_gain ) ? (float) $antenna_gain : null,
        ),
        'hardware'   => $hardware,
        'maintainer' => $maintainer,
        'node_tier'  => $node_tier,
        'role'       => $role,
        'status'     => array(
            'online'    => $is_online,
            'last_seen' => $last_seen,
        ),
    );

    return $node_data;
}

/**
 * REST API callback: Get all nodes
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error Response object.
 */
function wpamesh_rest_get_nodes( $request ) {
    $tier_filter = $request->get_param( 'tier' );

    // Query posts in the Node-Detail category
    $query_args = array(
        'category_name'  => 'node-detail',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    );

    $nodes_query = new WP_Query( $query_args );
    $nodes = array();

    if ( $nodes_query->have_posts() ) {
        while ( $nodes_query->have_posts() ) {
            $nodes_query->the_post();
            $post_id = get_the_ID();

            // Filter by tier if specified
            if ( $tier_filter ) {
                $node_tier_field = get_field( 'node_tier', $post_id );
                $node_tier = is_array( $node_tier_field ) ? $node_tier_field['value'] : ( $node_tier_field ?: '' );

                if ( $node_tier !== $tier_filter ) {
                    continue;
                }
            }

            $nodes[] = wpamesh_format_node_for_rest( $post_id );
        }
        wp_reset_postdata();
    }

    return rest_ensure_response( $nodes );
}

/**
 * REST API callback: Get single node by WordPress post ID
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error Response object.
 */
function wpamesh_rest_get_single_node( $request ) {
    $post_id = $request->get_param( 'id' );

    // Get the post
    $post = get_post( $post_id );

    if ( ! $post || $post->post_status !== 'publish' ) {
        return new WP_Error(
            'node_not_found',
            __( 'Node not found.', 'wpamesh' ),
            array( 'status' => 404 )
        );
    }

    // Verify it's in the node-detail category
    if ( ! has_category( 'node-detail', $post_id ) ) {
        return new WP_Error(
            'node_not_found',
            __( 'Node not found.', 'wpamesh' ),
            array( 'status' => 404 )
        );
    }

    $node_data = wpamesh_format_node_for_rest( $post_id );

    return rest_ensure_response( $node_data );
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

    // Get nodes from aggregated cache (background-refreshed, no blocking API calls)
    $cached_nodes = wpamesh_get_node_list_data();

    // If cache is empty, fall back to basic WP query without live data
    if ( empty( $cached_nodes ) ) {
        $nodes_query = new WP_Query( array(
            'category_name'  => 'node-detail',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        $cached_nodes = array();
        if ( $nodes_query->have_posts() ) {
            while ( $nodes_query->have_posts() ) {
                $nodes_query->the_post();
                $post_id = get_the_ID();

                $node_tier_field = get_field( 'node_tier', $post_id );
                $node_tier = is_array( $node_tier_field ) ? $node_tier_field['value'] : ( $node_tier_field ?: 'uncategorized' );

                $role_field = get_field( 'role', $post_id );
                $role_label = is_array( $role_field ) ? $role_field['label'] : ( $role_field ?: '' );

                $cached_nodes[] = array(
                    'post_id'       => $post_id,
                    'node_id'       => get_field( 'node_id', $post_id ),
                    'tier'          => $node_tier,
                    'long_name'     => get_field( 'long_name', $post_id ) ?: get_the_title(),
                    'short_name'    => get_field( 'short_name', $post_id ) ?: '',
                    'permalink'     => get_permalink(),
                    'location'      => get_field( 'location_name', $post_id ),
                    'role'          => $role_label,
                    'has_live_data' => false,
                    'is_online'     => null,
                    'channel_util'  => null,
                    'air_util'      => null,
                );
            }
            wp_reset_postdata();
        }
    }

    // Group nodes by tier
    $grouped_nodes = array();
    foreach ( $tier_labels as $key => $label ) {
        $grouped_nodes[ $key ] = array();
    }
    $grouped_nodes['uncategorized'] = array();

    foreach ( $cached_nodes as $node ) {
        if ( $tier_filter && $node['tier'] !== $tier_filter ) {
            continue;
        }
        $tier_key = isset( $grouped_nodes[ $node['tier'] ] ) ? $node['tier'] : 'uncategorized';
        $grouped_nodes[ $tier_key ][] = $node;
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
                <li class="wpamesh-node-item"<?php echo $node['node_id'] ? ' data-node-id="' . esc_attr( $node['node_id'] ) . '"' : ''; ?>>
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

/**
 * =============================================================================
 * Discord Notifications
 * =============================================================================
 * Sends Discord webhook notifications when posts in specific categories are published.
 *
 * Configuration: Set WPAMESH_DISCORD_WEBHOOK in wp-config.php or use the filter below.
 * Categories: Add category slugs to the wpamesh_discord_categories filter.
 */

/**
 * Get Discord webhook URL
 */
function wpamesh_get_discord_webhook() {
    // Constant takes priority (for version-controlled configs)
    if ( defined( 'WPAMESH_DISCORD_WEBHOOK' ) && WPAMESH_DISCORD_WEBHOOK ) {
        return WPAMESH_DISCORD_WEBHOOK;
    }
    // Then check admin setting
    $option = get_option( 'wpamesh_discord_webhook', '' );
    if ( ! empty( $option ) ) {
        return $option;
    }
    // Finally allow filter override
    return apply_filters( 'wpamesh_discord_webhook', '' );
}

/**
 * Register Discord settings in admin
 */
add_action( 'admin_init', function() {
    // Register the setting
    register_setting( 'general', 'wpamesh_discord_webhook', array(
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default'           => '',
    ) );

    register_setting( 'general', 'wpamesh_discord_categories', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => 'announcements, news',
    ) );

    // Add settings section
    add_settings_section(
        'wpamesh_discord_section',
        __( 'Discord Notifications', 'wpamesh' ),
        function() {
            echo '<p>' . esc_html__( 'Configure Discord webhook notifications for new posts.', 'wpamesh' ) . '</p>';
        },
        'general'
    );

    // Webhook URL field
    add_settings_field(
        'wpamesh_discord_webhook',
        __( 'Discord Webhook URL', 'wpamesh' ),
        function() {
            $value = get_option( 'wpamesh_discord_webhook', '' );
            $disabled = defined( 'WPAMESH_DISCORD_WEBHOOK' ) && WPAMESH_DISCORD_WEBHOOK;
            ?>
            <input type="url"
                   name="wpamesh_discord_webhook"
                   id="wpamesh_discord_webhook"
                   value="<?php echo esc_attr( $disabled ? WPAMESH_DISCORD_WEBHOOK : $value ); ?>"
                   class="regular-text"
                   <?php echo $disabled ? 'disabled' : ''; ?>
            />
            <?php if ( $disabled ) : ?>
            <p class="description"><?php esc_html_e( 'Set via WPAMESH_DISCORD_WEBHOOK constant in wp-config.php', 'wpamesh' ); ?></p>
            <?php else : ?>
            <p class="description"><?php esc_html_e( 'Get this from Discord: Server Settings → Integrations → Webhooks', 'wpamesh' ); ?></p>
            <?php endif; ?>
            <?php
        },
        'general',
        'wpamesh_discord_section'
    );

    // Categories field
    add_settings_field(
        'wpamesh_discord_categories',
        __( 'Notify for Categories', 'wpamesh' ),
        function() {
            $value = get_option( 'wpamesh_discord_categories', 'announcements, news' );
            ?>
            <input type="text"
                   name="wpamesh_discord_categories"
                   id="wpamesh_discord_categories"
                   value="<?php echo esc_attr( $value ); ?>"
                   class="regular-text"
            />
            <p class="description"><?php esc_html_e( 'Comma-separated category slugs. Only posts in these categories will trigger notifications.', 'wpamesh' ); ?></p>
            <?php
        },
        'general',
        'wpamesh_discord_section'
    );
});

/**
 * Get categories that should trigger Discord notifications
 *
 * @return array Category slugs that trigger notifications
 */
function wpamesh_get_discord_categories() {
    $option = get_option( 'wpamesh_discord_categories', 'announcements, news' );
    $categories = array_map( 'trim', explode( ',', $option ) );
    $categories = array_map( 'strtolower', $categories ); // Normalize to lowercase
    $categories = array_filter( $categories ); // Remove empty values
    return apply_filters( 'wpamesh_discord_categories', $categories );
}

/**
 * Send Discord webhook notification
 *
 * @param array $payload Discord webhook payload
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function wpamesh_send_discord_webhook( $payload ) {
    $webhook_url = wpamesh_get_discord_webhook();
    if ( empty( $webhook_url ) ) {
        return new WP_Error( 'no_webhook', 'Discord webhook URL not configured' );
    }

    $response = wp_remote_post( $webhook_url, array(
        'headers' => array( 'Content-Type' => 'application/json' ),
        'body'    => wp_json_encode( $payload ),
        'timeout' => 10,
    ) );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( $code >= 200 && $code < 300 ) {
        return true;
    }

    return new WP_Error( 'discord_error', 'Discord returned HTTP ' . $code );
}

/**
 * Build Discord notification payload for a post
 *
 * @param WP_Post $post The post object
 * @return array Discord webhook payload
 */
function wpamesh_build_discord_payload( $post ) {
    // Get excerpt, trimmed to ~200 chars at word boundary
    $excerpt = $post->post_excerpt;
    if ( empty( $excerpt ) ) {
        $excerpt = wp_strip_all_tags( $post->post_content );
    }
    $excerpt = trim( $excerpt );
    if ( strlen( $excerpt ) > 200 ) {
        $excerpt = substr( $excerpt, 0, 200 );
        $excerpt = preg_replace( '/\s+\S*$/', '', $excerpt ); // Trim to word boundary
        $excerpt .= '...';
    }

    // Format: ## Title\n\nExcerpt\n-# URL
    $content = sprintf(
        "## %s\n\n%s\n-# %s",
        $post->post_title,
        $excerpt,
        get_permalink( $post )
    );

    $payload = array(
        'username' => get_bloginfo( 'name' ),
        'content'  => $content,
    );

    // Add featured image as embed if available
    $thumbnail_id = get_post_thumbnail_id( $post->ID );
    if ( $thumbnail_id ) {
        $image_url = wp_get_attachment_image_url( $thumbnail_id, 'large' );
        if ( $image_url ) {
            $payload['embeds'] = array(
                array(
                    'image' => array( 'url' => $image_url ),
                ),
            );
        }
    }

    return apply_filters( 'wpamesh_discord_payload', $payload, $post );
}

/**
 * Check if post should trigger Discord notification
 *
 * @param WP_Post $post The post object
 * @return bool True if notification should be sent
 */
function wpamesh_should_notify_discord( $post ) {
    $allowed_categories = wpamesh_get_discord_categories();
    if ( empty( $allowed_categories ) ) {
        return false;
    }

    foreach ( $allowed_categories as $cat_slug ) {
        if ( has_category( $cat_slug, $post ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Log Discord notification result (stores in option for admin visibility)
 *
 * @param string $message Log message
 * @param string $level   'info', 'error', or 'success'
 */
function wpamesh_discord_log( $message, $level = 'info' ) {
    $log = get_option( 'wpamesh_discord_log', array() );
    $log[] = array(
        'time'    => current_time( 'mysql' ),
        'level'   => $level,
        'message' => $message,
    );
    // Keep only last 20 entries
    $log = array_slice( $log, -20 );
    update_option( 'wpamesh_discord_log', $log, false );
}

/**
 * Send Discord notification when a post is published
 *
 * Uses 'publish_post' hook which fires after all post data (including categories) is saved.
 * We track which posts we've notified to prevent duplicates.
 */
add_action( 'publish_post', function( $post_id, $post ) {
    // Skip revisions and autosaves
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }

    // Check if we've already notified for this post (prevent duplicates on update)
    $notified = get_post_meta( $post_id, '_wpamesh_discord_notified', true );
    if ( $notified ) {
        return;
    }

    // Check category filter
    if ( ! wpamesh_should_notify_discord( $post ) ) {
        $cats = wp_get_post_categories( $post_id, array( 'fields' => 'slugs' ) );
        wpamesh_discord_log(
            sprintf( 'Skipped post "%s" (ID %d) - categories [%s] not in allowed list [%s]',
                $post->post_title,
                $post_id,
                implode( ', ', $cats ),
                implode( ', ', wpamesh_get_discord_categories() )
            ),
            'info'
        );
        return;
    }

    // Check webhook URL
    $webhook_url = wpamesh_get_discord_webhook();
    if ( empty( $webhook_url ) ) {
        wpamesh_discord_log(
            sprintf( 'Failed for post "%s" (ID %d) - No webhook URL configured', $post->post_title, $post_id ),
            'error'
        );
        return;
    }

    // Build and send
    $payload = wpamesh_build_discord_payload( $post );
    $result = wpamesh_send_discord_webhook( $payload );

    if ( is_wp_error( $result ) ) {
        wpamesh_discord_log(
            sprintf( 'Failed for post "%s" (ID %d) - %s', $post->post_title, $post_id, $result->get_error_message() ),
            'error'
        );
    } else {
        // Mark as notified to prevent duplicates on future updates
        update_post_meta( $post_id, '_wpamesh_discord_notified', time() );
        wpamesh_discord_log(
            sprintf( 'Sent notification for post "%s" (ID %d)', $post->post_title, $post_id ),
            'success'
        );
    }
}, 10, 2 );

/**
 * Add Discord log viewer to admin
 */
add_action( 'admin_init', function() {
    add_settings_field(
        'wpamesh_discord_log',
        __( 'Recent Activity', 'wpamesh' ),
        function() {
            $log = get_option( 'wpamesh_discord_log', array() );
            if ( empty( $log ) ) {
                echo '<p class="description">' . esc_html__( 'No activity yet.', 'wpamesh' ) . '</p>';
                return;
            }
            echo '<div style="max-height: 200px; overflow-y: auto; background: #f6f7f7; padding: 10px; font-family: monospace; font-size: 12px;">';
            foreach ( array_reverse( $log ) as $entry ) {
                $color = $entry['level'] === 'error' ? '#d63638' : ( $entry['level'] === 'success' ? '#00a32a' : '#666' );
                printf(
                    '<div style="color: %s; margin-bottom: 5px;">[%s] %s</div>',
                    esc_attr( $color ),
                    esc_html( $entry['time'] ),
                    esc_html( $entry['message'] )
                );
            }
            echo '</div>';
            echo '<p class="description">' . esc_html__( 'Shows last 20 notification attempts.', 'wpamesh' ) . '</p>';
        },
        'general',
        'wpamesh_discord_section'
    );
}, 20 ); // Priority 20 to run after main settings
