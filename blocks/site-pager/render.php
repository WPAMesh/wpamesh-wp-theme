<?php
/**
 * Previous and next site in the same tier, alphabetically.
 *
 * @package WPAMesh
 */

$site_id = wpamesh_block_site_id( $block );
if ( ! $site_id || ! wpamesh_sites_available() ) {
    return;
}

$tier = (string) get_field( 'tier', $site_id );
$ids  = get_posts( array(
    'post_type'      => 'wpamesh_site',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
    'meta_key'       => 'tier',
    'meta_value'     => $tier,
    'fields'         => 'ids',
) );

$pos  = array_search( $site_id, $ids, true );
$prev = ( false !== $pos && $pos > 0 ) ? $ids[ $pos - 1 ] : 0;
$next = ( false !== $pos && $pos < count( $ids ) - 1 ) ? $ids[ $pos + 1 ] : 0;
if ( ! $prev && ! $next ) {
    return;
}

$label = strtolower( wpamesh_site_tier_label( $tier ) );
?>
<nav <?php echo get_block_wrapper_attributes( array( 'class' => 'wpamesh-pager' ) ); ?> aria-label="<?php echo esc_attr( sprintf( __( 'More %s', 'wpamesh' ), wpamesh_site_tier_label( $tier, true ) ) ); ?>">
    <?php if ( $prev ) : ?>
        <a class="wpamesh-card prev" href="<?php echo esc_url( get_permalink( $prev ) ); ?>">
            <span>← <?php echo esc_html( sprintf( __( 'Previous %s', 'wpamesh' ), $label ) ); ?></span>
            <b><?php echo esc_html( get_the_title( $prev ) ); ?></b>
        </a>
    <?php endif; ?>
    <?php if ( $next ) : ?>
        <a class="wpamesh-card next" href="<?php echo esc_url( get_permalink( $next ) ); ?>">
            <span><?php echo esc_html( sprintf( __( 'Next %s', 'wpamesh' ), $label ) ); ?> →</span>
            <b><?php echo esc_html( get_the_title( $next ) ); ?></b>
        </a>
    <?php endif; ?>
</nav>
