<?php
/**
 * Site page head: breadcrumb, name, county and tier, networks, and the optional notice.
 *
 * @package WPAMesh
 */

$site_id = wpamesh_block_site_id( $block );
if ( ! $site_id || ! wpamesh_sites_available() ) {
    return;
}

$tier   = (string) get_field( 'tier', $site_id );
$county = (string) get_field( 'county', $site_id );
$notice = (string) get_field( 'notice', $site_id );
$meta   = array_filter( array( $county, wpamesh_site_tier_label( $tier ) ) );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wpamesh-site-header' ) ); ?>>
    <nav class="wpamesh-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'wpamesh' ); ?>">
        <a href="<?php echo esc_url( wpamesh_infrastructure_url() ); ?>"><?php esc_html_e( 'Infrastructure', 'wpamesh' ); ?></a><span aria-hidden="true">/</span><?php echo esc_html( wpamesh_site_tier_label( $tier, true ) ); ?>
    </nav>

    <div class="wpamesh-node-head">
        <div>
            <h1><?php echo esc_html( get_the_title( $site_id ) ); ?></h1>
            <div class="meta">
                <?php if ( $meta ) : ?>
                    <span><?php echo esc_html( implode( ' · ', $meta ) ); ?></span>
                <?php endif; ?>
                <span class="protos">
                    <?php
                    foreach ( wpamesh_site_protocols( $site_id ) as $protocol ) {
                        echo wpamesh_protocol_chip( $protocol ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper.
                    }
                    ?>
                </span>
            </div>
        </div>
    </div>

    <?php if ( $notice ) : ?>
        <div class="wpamesh-notice" role="note">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>
            <div><strong><?php echo esc_html( $notice ); ?></strong></div>
        </div>
    <?php endif; ?>
</div>
