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
    // Main theme stylesheet (extracted from HTML redesign)
    wp_enqueue_style(
        'wpamesh-theme',
        get_theme_file_uri( 'assets/css/theme.css' ),
        array(),
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
