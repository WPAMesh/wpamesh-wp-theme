<?php
/**
 * Display helpers and blocks for Sites. The Site post type and its fields come from the
 * wpamesh-sites plugin; without it these blocks render nothing.
 *
 * @package WPAMesh
 */

defined( 'ABSPATH' ) || exit;

const WPAMESH_SITE_BLOCKS = array( 'site-header', 'site-photo', 'site-info', 'site-pager', 'site-list' );

add_action( 'init', function () {
    wp_register_script(
        'wpamesh-site-blocks-editor',
        get_theme_file_uri( 'assets/js/site-blocks-editor.js' ),
        array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-server-side-render' ),
        wp_get_theme()->get( 'Version' ),
        true
    );
    wp_register_script(
        'wpamesh-site-list',
        get_theme_file_uri( 'assets/js/site-list.js' ),
        array(),
        wp_get_theme()->get( 'Version' ),
        true
    );
    foreach ( WPAMESH_SITE_BLOCKS as $block ) {
        register_block_type( get_template_directory() . '/blocks/' . $block );
    }
} );

function wpamesh_sites_available() {
    return post_type_exists( 'wpamesh_site' ) && function_exists( 'get_field' );
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
    $radios = wpamesh_sites_available() ? get_field( 'radios', $site_id ) : array();
    return is_array( $radios ) ? $radios : array();
}

/**
 * Protocols at a site, MeshCore first to match the design's ordering.
 */
function wpamesh_site_protocols( $site_id ) {
    $order = array( 'meshcore', 'meshtastic', 'reticulum' );
    $found = array_unique( array_column( wpamesh_site_radios( $site_id ), 'protocol' ) );
    return array_values( array_intersect( $order, $found ) );
}

function wpamesh_radio_link( $radio ) {
    if ( 'meshtastic' === $radio['protocol'] && ! empty( $radio['node_id'] ) ) {
        return array( 'https://map.wpamesh.net/node/' . wpamesh_hex_to_decimal( $radio['node_id'] ), __( 'Open on Meshview', 'wpamesh' ) );
    }
    return null;
}

/**
 * Where the Infrastructure listing lives. Used for breadcrumbs.
 */
function wpamesh_infrastructure_url() {
    return apply_filters( 'wpamesh_infrastructure_url', home_url( '/infrastructure/' ) );
}
