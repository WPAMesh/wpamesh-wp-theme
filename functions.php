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
    // Google Fonts - Barlow, Barlow Condensed, and JetBrains Mono
    wp_enqueue_style(
        'wpamesh-google-fonts',
        'https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:wght@600;700&family=JetBrains+Mono:wght@400;500;600&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'wpamesh-theme',
        get_theme_file_uri( 'assets/css/theme.css' ),
        array( 'wpamesh-google-fonts' ),
        wp_get_theme()->get( 'Version' )
    );

    wp_enqueue_style(
        'wpamesh-legacy',
        get_theme_file_uri( 'assets/css/legacy.css' ),
        array( 'wpamesh-theme' ),
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
    add_editor_style( array(
        'https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:wght@600;700&display=swap',
        'assets/css/theme.css',
        'assets/css/legacy.css',
    ) );
});

require_once get_template_directory() . '/inc/sites.php';
require_once get_template_directory() . '/inc/guides.php';

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

    foreach ( array(
        'wpamesh-primary' => __( 'Primary', 'wpamesh' ),
        'wpamesh-ghost'   => __( 'Outline', 'wpamesh' ),
        'wpamesh-discord' => __( 'Discord', 'wpamesh' ),
    ) as $name => $label ) {
        register_block_style( 'core/button', array( 'name' => $name, 'label' => $label ) );
    }
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
 * Network stats for the stat boxes, from the wpamesh-sites plugin's cache. The AJAX endpoint
 * lets sidebar-stats.js refresh numbers on pages served from a page cache.
 */
function wpamesh_get_sidebar_stats() {
    if ( function_exists( 'wpamesh_sites_mesh_stats' ) ) {
        return wpamesh_sites_mesh_stats();
    }
    return array(
        'total_nodes'         => 0,
        'active_nodes'        => 0,
        'routers'             => 0,
        'packets_24h'         => 0,
        'channel_utilization' => null,
        'air_util_tx'         => null,
        'updated'             => null,
    );
}

add_action( 'wp_ajax_wpamesh_sidebar_stats', 'wpamesh_ajax_sidebar_stats' );
add_action( 'wp_ajax_nopriv_wpamesh_sidebar_stats', 'wpamesh_ajax_sidebar_stats' );

function wpamesh_ajax_sidebar_stats() {
    $stats = wpamesh_get_sidebar_stats();
    wp_send_json_success( array(
        'total_nodes'         => number_format( $stats['total_nodes'] ),
        'active_nodes'        => number_format( $stats['active_nodes'] ),
        'routers'             => number_format( $stats['routers'] ),
        'packets_24h'         => number_format( $stats['packets_24h'] ),
        'channel_utilization' => null !== $stats['channel_utilization'] ? $stats['channel_utilization'] . '%' : '—',
        'air_util_tx'         => null !== $stats['air_util_tx'] ? $stats['air_util_tx'] . '%' : '—',
        'last_updated'        => $stats['updated'] ? human_time_diff( $stats['updated'] ) . ' ago' : 'never',
    ) );
}

add_action( 'wp_enqueue_scripts', function() {
    $version = wp_get_theme()->get( 'Version' );

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
    wp_localize_script( 'wpamesh-sidebar-stats', 'wpameshDiscord', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'action'  => 'wpamesh_discord_widget',
    ) );

    // Only does anything on pages with .wpa-toc-list elements.
    wp_enqueue_script(
        'wpamesh-guide-toc',
        get_theme_file_uri( 'assets/js/guide-toc.js' ),
        array(),
        $version,
        true
    );
});

add_filter( 'cron_schedules', function( $schedules ) {
    $schedules['wpamesh_fifteen_minutes'] = array(
        'interval' => 15 * MINUTE_IN_SECONDS,
        'display'  => __( 'Every 15 Minutes', 'wpamesh' ),
    );
    return $schedules;
});

add_action( 'switch_theme', function() {
    wp_clear_scheduled_hook( 'wpamesh_refresh_discord_widget' );
});

/**
 * =============================================================================
 * Discord Widget - Online Member Count with Background Refresh
 * =============================================================================
 * Fetches online member count from Discord's public widget API.
 * Uses WP-Cron for background refresh so page loads never block on API calls.
 */

/**
 * Get Discord widget data from cache
 *
 * Returns cached data immediately. If cache is empty, returns defaults
 * and schedules a background refresh.
 *
 * @return array Widget data with online_count and last_updated
 */
function wpamesh_get_discord_widget_data() {
    $cached = get_transient( 'wpamesh_discord_widget' );

    if ( false !== $cached ) {
        return $cached;
    }

    // Schedule background refresh if not already pending
    if ( ! wp_next_scheduled( 'wpamesh_refresh_discord_widget' ) ) {
        wp_schedule_single_event( time(), 'wpamesh_refresh_discord_widget' );
    }

    return array(
        'online_count' => null,
        'last_updated' => null,
    );
}

/**
 * Refresh Discord widget data in background
 *
 * Called by WP-Cron. Fetches online count from Discord's widget API.
 *
 * @return array Widget data
 */
function wpamesh_do_refresh_discord_widget() {
    $guild_id = wpamesh_get_discord_guild_id();
    if ( empty( $guild_id ) ) {
        return array(
            'online_count' => null,
            'last_updated' => null,
        );
    }

    $response = wp_remote_get( 'https://discord.com/api/guilds/' . urlencode( $guild_id ) . '/widget.json', array(
        'timeout' => 10,
        'headers' => array(
            'Accept' => 'application/json',
        ),
    ) );

    if ( is_wp_error( $response ) ) {
        return array(
            'online_count' => null,
            'last_updated' => null,
        );
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( $code !== 200 ) {
        return array(
            'online_count' => null,
            'last_updated' => null,
        );
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( ! is_array( $body ) || ! isset( $body['presence_count'] ) ) {
        return array(
            'online_count' => null,
            'last_updated' => null,
        );
    }

    $data = array(
        'online_count' => intval( $body['presence_count'] ),
        'last_updated' => time(),
    );

    set_transient( 'wpamesh_discord_widget', $data, 20 * MINUTE_IN_SECONDS );

    return $data;
}
add_action( 'wpamesh_refresh_discord_widget', 'wpamesh_do_refresh_discord_widget' );

/**
 * Schedule recurring Discord widget refresh
 */
function wpamesh_schedule_discord_widget_refresh() {
    if ( ! wp_next_scheduled( 'wpamesh_refresh_discord_widget' ) ) {
        wp_schedule_event( time(), 'wpamesh_fifteen_minutes', 'wpamesh_refresh_discord_widget' );
    }
}
add_action( 'init', 'wpamesh_schedule_discord_widget_refresh' );

/**
 * AJAX endpoint for Discord widget data
 */
add_action( 'wp_ajax_wpamesh_discord_widget', 'wpamesh_ajax_discord_widget' );
add_action( 'wp_ajax_nopriv_wpamesh_discord_widget', 'wpamesh_ajax_discord_widget' );

function wpamesh_ajax_discord_widget() {
    $data = wpamesh_get_discord_widget_data();

    // If cache is empty, do a synchronous refresh (AJAX is already async)
    if ( $data['last_updated'] === null ) {
        $data = wpamesh_do_refresh_discord_widget();
    }

    wp_send_json_success( array(
        'online_count' => $data['online_count'],
    ) );
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
 * Get Discord guild ID for widget API
 */
function wpamesh_get_discord_guild_id() {
    if ( defined( 'WPAMESH_DISCORD_GUILD_ID' ) && WPAMESH_DISCORD_GUILD_ID ) {
        return WPAMESH_DISCORD_GUILD_ID;
    }
    $option = get_option( 'wpamesh_discord_guild_id', '' );
    if ( ! empty( $option ) ) {
        return $option;
    }
    return apply_filters( 'wpamesh_discord_guild_id', '' );
}

/**
 * Register Discord settings in admin
 */
add_action( 'admin_init', function() {
    // Register the setting
    register_setting( 'general', 'wpamesh_discord_guild_id', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => '',
    ) );

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
        __( 'Discord Integration', 'wpamesh' ),
        function() {
            echo '<p>' . esc_html__( 'Configure Discord widget and webhook notifications.', 'wpamesh' ) . '</p>';
        },
        'general'
    );

    // Guild ID field
    add_settings_field(
        'wpamesh_discord_guild_id',
        __( 'Discord Guild ID', 'wpamesh' ),
        function() {
            $value = get_option( 'wpamesh_discord_guild_id', '' );
            $disabled = defined( 'WPAMESH_DISCORD_GUILD_ID' ) && WPAMESH_DISCORD_GUILD_ID;
            ?>
            <input type="text"
                   name="wpamesh_discord_guild_id"
                   id="wpamesh_discord_guild_id"
                   value="<?php echo esc_attr( $disabled ? WPAMESH_DISCORD_GUILD_ID : $value ); ?>"
                   class="regular-text"
                   <?php echo $disabled ? 'disabled' : ''; ?>
            />
            <?php if ( $disabled ) : ?>
            <p class="description"><?php esc_html_e( 'Set via WPAMESH_DISCORD_GUILD_ID constant in wp-config.php', 'wpamesh' ); ?></p>
            <?php else : ?>
            <p class="description"><?php esc_html_e( 'Server Settings → Engagement → Server Widget → Server ID. Required for live member count.', 'wpamesh' ); ?></p>
            <?php endif; ?>
            <?php
        },
        'general',
        'wpamesh_discord_section'
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
