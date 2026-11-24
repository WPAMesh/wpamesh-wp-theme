<?php
/**
 * Display helpers and blocks for Sites. The Site post type, its fields and live status come from
 * the wpamesh-sites plugin; without it these blocks render nothing.
 *
 * @package WPAMesh
 */

defined( 'ABSPATH' ) || exit;

const WPAMESH_SITE_BLOCKS = array( 'site-header', 'site-photo', 'site-info', 'site-pager', 'site-list' );

add_action( 'init', function () {
    $version = wp_get_theme()->get( 'Version' );
    wp_register_script(
        'wpamesh-site-blocks-editor',
        get_theme_file_uri( 'assets/js/site-blocks-editor.js' ),
        array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-server-side-render' ),
        $version,
        true
    );
    wp_register_script( 'wpamesh-site-list', get_theme_file_uri( 'assets/js/site-list.js' ), array(), $version, true );
    wp_register_script( 'wpamesh-site-status', get_theme_file_uri( 'assets/js/site-status.js' ), array(), $version, true );
    wp_localize_script( 'wpamesh-site-status', 'wpameshSiteStatus', array(
        'url' => add_query_arg( 'action', 'wpamesh_site_status', admin_url( 'admin-ajax.php' ) ),
    ) );
    foreach ( WPAMESH_SITE_BLOCKS as $block ) {
        register_block_type( get_template_directory() . '/blocks/' . $block );
    }
} );

function wpamesh_has_sites() {
    return post_type_exists( 'wpamesh_site' ) && function_exists( 'wpamesh_sites_status' ) && function_exists( 'get_field' );
}

/**
 * The site a block should describe: the post in block context, else the current post.
 */
function wpamesh_block_site_id( $block ) {
    $id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();
    return ( $id && 'wpamesh_site' === get_post_type( $id ) ) ? $id : 0;
}

function wpamesh_site_tier_labels() {
    return array(
        'core'         => array( __( 'Core site', 'wpamesh' ), __( 'Core sites', 'wpamesh' ) ),
        'supplemental' => array( __( 'Supplementary site', 'wpamesh' ), __( 'Supplementary sites', 'wpamesh' ) ),
        'gateway'      => array( __( 'Gateway', 'wpamesh' ), __( 'Gateways & services', 'wpamesh' ) ),
        'service'      => array( __( 'Service', 'wpamesh' ), __( 'Gateways & services', 'wpamesh' ) ),
    );
}

function wpamesh_site_tier_label( $tier, $plural = false ) {
    $labels = wpamesh_site_tier_labels();
    return isset( $labels[ $tier ] ) ? $labels[ $tier ][ $plural ? 1 : 0 ] : '';
}

function wpamesh_protocol_info( $protocol ) {
    $all = array(
        'meshcore'   => array( 'name' => 'MeshCore', 'abbr' => 'MC', 'class' => '' ),
        'meshtastic' => array( 'name' => 'Meshtastic', 'abbr' => 'MT', 'class' => 'mt' ),
        'reticulum'  => array( 'name' => 'Reticulum', 'abbr' => 'RNS', 'class' => 'rns' ),
    );
    return isset( $all[ $protocol ] ) ? $all[ $protocol ] : array( 'name' => $protocol, 'abbr' => '?', 'class' => '' );
}

function wpamesh_protocol_chip( $protocol ) {
    $info = wpamesh_protocol_info( $protocol );
    return sprintf(
        '<span class="wpamesh-chip %s" title="%s">%s</span>',
        esc_attr( $info['class'] ),
        esc_attr( $info['name'] ),
        esc_html( $info['abbr'] )
    );
}

/**
 * "ROUTER_LATE" → "Router late". MeshCore roles are already words.
 */
function wpamesh_role_label( $role ) {
    return ucfirst( strtolower( str_replace( '_', ' ', (string) $role ) ) );
}

function wpamesh_site_radios( $site_id ) {
    return wpamesh_has_sites() ? wpamesh_sites_radios( $site_id ) : array();
}

/**
 * Protocols at a site, MeshCore first to match the design's ordering.
 */
function wpamesh_site_protocols( $site_id ) {
    $order = array( 'meshcore', 'meshtastic', 'reticulum' );
    $found = array_unique( array_column( wpamesh_site_radios( $site_id ), 'protocol' ) );
    return array_values( array_intersect( $order, $found ) );
}

/**
 * Where the Infrastructure listing lives. Used for breadcrumbs.
 */
function wpamesh_infrastructure_url() {
    return apply_filters( 'wpamesh_infrastructure_url', home_url( '/infrastructure/' ) );
}

/**
 * Channel load per Meshtastic's airtime.h: telemetry is throttled from 25% and
 * transmissions are skipped from 40%.
 */
function wpamesh_channel_load_class( $util ) {
    if ( null === $util ) {
        return '';
    }
    return $util >= 40 ? 'wpamesh-load-high' : ( $util >= 25 ? 'wpamesh-load-elevated' : '' );
}

/**
 * A site's status pill or card status: class ('' online, 'off' offline) and label, or null
 * when no radio at the site has live data.
 */
function wpamesh_site_status_view( $site_id ) {
    $status = wpamesh_has_sites() ? wpamesh_sites_site_status( $site_id ) : null;
    if ( ! $status ) {
        return null;
    }
    if ( $status['online'] ) {
        return array( 'class' => '', 'label' => __( 'Online', 'wpamesh' ) );
    }
    return array(
        'class' => 'off',
        /* translators: %s: time since last heard, e.g. "3 days" */
        'label' => $status['last_seen'] ? sprintf( __( 'Offline · %s', 'wpamesh' ), human_time_diff( $status['last_seen'] ) ) : __( 'Offline', 'wpamesh' ),
    );
}

/**
 * Live readouts for a radio card: "seen" text plus up to two tiles of (value, label, class).
 */
function wpamesh_radio_live_view( $radio ) {
    $status = wpamesh_has_sites() ? wpamesh_sites_radio_status( $radio ) : null;
    if ( ! $status ) {
        return array( 'seen' => '', 'tiles' => array() );
    }

    $tiles = array();
    if ( 'meshtastic' === $radio['protocol'] && null !== $status['channel_util'] ) {
        $tiles[] = array( $status['channel_util'] . '%', __( 'Channel use', 'wpamesh' ), wpamesh_channel_load_class( $status['channel_util'] ) );
        $tiles[] = array( $status['air_util'] . '%', __( 'Airtime (TX)', 'wpamesh' ), '' );
    } elseif ( 'meshcore' === $radio['protocol'] && null !== $status['relay_24h'] ) {
        $tiles[] = array( number_format_i18n( $status['relay_1h'] ), __( 'Relayed, last hour', 'wpamesh' ), '' );
        $tiles[] = array( number_format_i18n( $status['relay_24h'] ), __( 'Relayed, last 24h', 'wpamesh' ), '' );
    }

    return array(
        /* translators: %s: time since, e.g. "5 mins" */
        'seen'  => $status['last_seen'] ? sprintf( __( 'Last heard %s ago', 'wpamesh' ), human_time_diff( $status['last_seen'] ) ) : '',
        'tiles' => $tiles,
    );
}

/**
 * Formatted status for every site and radio, for site-status.js. Pages can come from a page
 * cache, so the script swaps in current values after load.
 */
function wpamesh_ajax_site_status() {
    $out = array( 'sites' => array(), 'radios' => array() );
    if ( wpamesh_has_sites() ) {
        $sites = get_posts( array( 'post_type' => 'wpamesh_site', 'posts_per_page' => -1, 'fields' => 'ids' ) );
        foreach ( $sites as $site_id ) {
            $out['sites'][ $site_id ] = wpamesh_site_status_view( $site_id );
            foreach ( wpamesh_sites_radios( $site_id ) as $radio ) {
                $out['radios'][ wpamesh_sites_radio_key( $radio ) ] = wpamesh_radio_live_view( $radio );
            }
        }
    }
    wp_send_json_success( $out );
}
add_action( 'wp_ajax_wpamesh_site_status', 'wpamesh_ajax_site_status' );
add_action( 'wp_ajax_nopriv_wpamesh_site_status', 'wpamesh_ajax_site_status' );
