<?php
/**
 * Callout: icon, bold title, then the inner blocks.
 *
 * @package WPAMesh
 */

$icons = array(
    'info'  => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
    'warn'  => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
    'tip'   => '<path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z"/>',
    'check' => '<circle cx="12" cy="12" r="10"/><path d="m8 12 3 3 5-6"/>',
    'map'   => '<path d="M9 3 3 6v15l6-3 6 3 6-3V3l-6 3-6-3zM9 3v15M15 6v15"/>',
    'globe' => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/>',
    'key'   => '<circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6M15.5 7.5l3 3L22 7l-3-3"/>',
);
$icon    = isset( $icons[ $attributes['icon'] ] ) ? $icons[ $attributes['icon'] ] : $icons['info'];
$variant = in_array( $attributes['variant'], array( 'danger', 'tip', 'success' ), true ) ? $attributes['variant'] : '';
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => trim( 'wpamesh-callout ' . $variant ) ) ); ?>>
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput -- fixed markup above. ?></svg>
    <?php if ( '' !== $attributes['title'] ) : ?>
        <span class="title"><?php echo wp_kses_post( $attributes['title'] ); ?></span>
    <?php endif; ?>
    <?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- rendered inner blocks. ?>
</div>
