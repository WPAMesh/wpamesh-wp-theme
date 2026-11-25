<?php
/**
 * Guide header: breadcrumb back to the guides index, section kicker, title, and the excerpt
 * as the lede.
 *
 * @package WPAMesh
 */

$post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();
if ( ! $post_id ) {
    return;
}

$sections = wpamesh_guide_sections();
$section  = (string) get_post_meta( $post_id, 'wpamesh_guide_section', true );
$parent   = (int) wp_get_post_parent_id( $post_id );
$chip     = in_array( $section, array( 'meshcore', 'meshtastic', 'reticulum' ), true ) ? wpamesh_protocol_chip( $section ) : '';
$lede     = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : '';
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wpamesh-guide-header' ) ); ?>>
    <?php if ( $parent ) : ?>
        <nav class="wpamesh-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'wpamesh' ); ?>">
            <a href="<?php echo esc_url( get_permalink( $parent ) ); ?>"><?php echo esc_html( get_the_title( $parent ) ); ?></a><?php if ( isset( $sections[ $section ] ) ) : ?><span aria-hidden="true">/</span><?php echo esc_html( $sections[ $section ] ); ?><?php endif; ?>
        </nav>
    <?php endif; ?>

    <div class="wpamesh-guide-head">
        <?php if ( isset( $sections[ $section ] ) ) : ?>
            <p class="kicker"><?php echo $chip; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?><?php echo esc_html( sprintf( __( '%s guide', 'wpamesh' ), $sections[ $section ] ) ); ?></p>
        <?php endif; ?>
        <h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
        <?php if ( $lede ) : ?>
            <p class="lede"><?php echo wp_kses_post( $lede ); ?></p>
        <?php endif; ?>
    </div>
</div>
