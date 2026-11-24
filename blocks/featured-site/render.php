<?php
/**
 * Featured site card. Uses the chosen site, or else picks one with a photo, changing daily.
 *
 * @package WPAMesh
 */

if ( ! wpamesh_has_sites() ) {
    return;
}

$site_id = (int) $attributes['siteId'];
if ( ! $site_id || 'publish' !== get_post_status( $site_id ) ) {
    $with_photos = get_posts( array(
        'post_type'      => 'wpamesh_site',
        'posts_per_page' => -1,
        'orderby'        => 'ID',
        'order'          => 'ASC',
        'fields'         => 'ids',
        'meta_key'       => '_thumbnail_id',
    ) );
    if ( ! $with_photos ) {
        return;
    }
    $site_id = $with_photos[ (int) floor( time() / DAY_IN_SECONDS ) % count( $with_photos ) ];
}

$county = (string) get_field( 'county', $site_id );
$tier   = wpamesh_site_tier_label( (string) get_field( 'tier', $site_id ) );
?>
<aside <?php echo get_block_wrapper_attributes( array( 'class' => 'wpamesh-card wpamesh-featured' ) ); ?>>
    <?php if ( has_post_thumbnail( $site_id ) ) : ?>
        <?php echo get_the_post_thumbnail( $site_id, 'medium_large', array( 'class' => 'photo' ) ); ?>
    <?php endif; ?>
    <div class="body">
        <p class="wpamesh-eyebrow"><?php esc_html_e( 'Featured site', 'wpamesh' ); ?></p>
        <h3><a href="<?php echo esc_url( get_permalink( $site_id ) ); ?>"><?php echo esc_html( get_the_title( $site_id ) ); ?></a></h3>
        <p><?php echo esc_html( implode( ' · ', array_filter( array( $county, $tier ) ) ) ); ?></p>
        <div class="protos">
            <?php foreach ( wpamesh_site_radios( $site_id ) as $radio ) : ?>
                <span class="proto"><?php echo wpamesh_protocol_chip( $radio['protocol'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?> <?php echo esc_html( wpamesh_role_label( $radio['role'] ) ); ?></span>
            <?php endforeach; ?>
        </div>
        <a class="more" href="<?php echo esc_url( wpamesh_infrastructure_url() ); ?>"><?php esc_html_e( 'All infrastructure sites', 'wpamesh' ); ?> →</a>
    </div>
</aside>
