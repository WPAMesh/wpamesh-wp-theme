<?php
/**
 * Site photo: the featured image with the site's photo caption.
 *
 * @package WPAMesh
 */

$site_id = wpamesh_block_site_id( $block );
if ( ! $site_id || ! has_post_thumbnail( $site_id ) ) {
    return;
}

$caption = wpamesh_has_sites() ? (string) get_field( 'photo_caption', $site_id ) : '';
?>
<figure <?php echo get_block_wrapper_attributes( array( 'class' => 'photo wpamesh-node-photo' ) ); ?>>
    <?php echo get_the_post_thumbnail( $site_id, 'large' ); ?>
    <?php if ( $caption ) : ?>
        <figcaption><?php echo esc_html( $caption ); ?></figcaption>
    <?php endif; ?>
</figure>
