<?php
/**
 * Guides: pages under /guides/ using the "Guide" template (templates/page-guide.html).
 *
 * A guide's section (which network, or hardware/community) is page meta, set from the editor
 * sidebar. The lede under the title is the page excerpt. The table of contents is built from
 * the page's h2 headings, which get ids here if the author didn't set one.
 *
 * @package WPAMesh
 */

defined( 'ABSPATH' ) || exit;

const WPAMESH_GUIDE_BLOCKS = array( 'callout', 'cli', 'guide-head', 'guide-toc', 'guide-index' );

function wpamesh_guide_sections() {
    return array(
        'meshcore'   => __( 'MeshCore', 'wpamesh' ),
        'meshtastic' => __( 'Meshtastic', 'wpamesh' ),
        'reticulum'  => __( 'Reticulum', 'wpamesh' ),
        'hardware'   => __( 'Hardware', 'wpamesh' ),
        'community'  => __( 'Community', 'wpamesh' ),
    );
}

add_action( 'init', function () {
    add_post_type_support( 'page', 'excerpt' );

    register_post_meta( 'page', 'wpamesh_guide_section', array(
        'type'              => 'string',
        'single'            => true,
        'show_in_rest'      => true,
        'sanitize_callback' => 'sanitize_key',
        'auth_callback'     => function () {
            return current_user_can( 'edit_pages' );
        },
    ) );

    $version = wp_get_theme()->get( 'Version' );
    wp_register_script( 'wpamesh-guides', get_theme_file_uri( 'assets/js/guides.js' ), array(), $version, true );
    wp_register_script(
        'wpamesh-guide-blocks-editor',
        get_theme_file_uri( 'assets/js/guide-blocks-editor.js' ),
        array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-data', 'wp-plugins', 'wp-editor', 'wp-core-data' ),
        $version,
        true
    );
    wp_localize_script( 'wpamesh-guide-blocks-editor', 'wpameshGuideSections', wpamesh_guide_sections() );

    foreach ( WPAMESH_GUIDE_BLOCKS as $block ) {
        register_block_type( get_template_directory() . '/blocks/' . $block );
    }
} );

// The section picker in the document sidebar lives in the same script as the block editors.
add_action( 'enqueue_block_editor_assets', function () {
    wp_enqueue_script( 'wpamesh-guide-blocks-editor' );
} );

/**
 * The id a heading gets: its anchor if set, else its text as a slug. Shared by the heading
 * filter below and the table of contents, so the two always agree.
 */
function wpamesh_heading_id( $block ) {
    if ( ! empty( $block['attrs']['anchor'] ) ) {
        return $block['attrs']['anchor'];
    }
    return sanitize_title( wp_strip_all_tags( $block['innerHTML'] ) );
}

/**
 * h2 headings of a post as id => text, in order.
 */
function wpamesh_guide_headings( $post_id ) {
    $headings = array();
    foreach ( parse_blocks( (string) get_post_field( 'post_content', $post_id ) ) as $block ) {
        if ( 'core/heading' === $block['blockName'] && 2 === ( isset( $block['attrs']['level'] ) ? (int) $block['attrs']['level'] : 2 ) ) {
            $headings[ wpamesh_heading_id( $block ) ] = trim( wp_strip_all_tags( $block['innerHTML'] ) );
        }
    }
    return $headings;
}

add_filter( 'render_block_core/heading', function ( $html, $block ) {
    if ( ! is_page() || false !== strpos( $html, ' id="' ) ) {
        return $html;
    }
    return preg_replace( '/^<h([1-6])\b/', '<h$1 id="' . esc_attr( wpamesh_heading_id( $block ) ) . '"', ltrim( $html ), 1 );
}, 10, 2 );

/**
 * Pages that moved keep their old path in `_wpamesh_old_path` (e.g. "/getting-started/").
 * Once the old page is gone, requests for that path land here.
 */
add_action( 'template_redirect', function () {
    if ( ! is_404() ) {
        return;
    }
    $path  = trailingslashit( (string) wp_parse_url( add_query_arg( array() ), PHP_URL_PATH ) );
    $found = get_posts( array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'meta_key'       => '_wpamesh_old_path',
        'meta_value'     => $path,
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ) );
    if ( $found ) {
        wp_safe_redirect( get_permalink( $found[0] ), 301 );
        exit;
    }
} );
