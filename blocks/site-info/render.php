<?php
/**
 * Site aside: one card per radio, then the site-wide details.
 *
 * @package WPAMesh
 */

$site_id = wpamesh_block_site_id( $block );
if ( ! $site_id || ! wpamesh_sites_available() ) {
    return;
}

$radios = wpamesh_site_radios( $site_id );
$lat    = get_field( 'latitude', $site_id );
$lng    = get_field( 'longitude', $site_id );

$details = array(
    __( 'Height AGL', 'wpamesh' ) => esc_html( wpamesh_format_height( get_field( 'height_agl', $site_id ) ) ),
    __( 'Height MSL', 'wpamesh' ) => esc_html( wpamesh_format_height( get_field( 'height_msl', $site_id ) ) ),
    __( 'Location', 'wpamesh' )   => is_numeric( $lat ) && is_numeric( $lng )
        ? sprintf( '<a href="%s">%s, %s</a>', esc_url( "https://www.google.com/maps?q={$lat},{$lng}" ), esc_html( $lat ), esc_html( $lng ) )
        : '',
    __( 'Maintainer', 'wpamesh' ) => esc_html( (string) get_field( 'maintainer', $site_id ) ),
);
$details = array_filter( $details );
?>
<aside <?php echo get_block_wrapper_attributes( array( 'class' => 'wpamesh-site-info' ) ); ?>>
    <?php if ( $radios ) : ?>
        <p class="wpamesh-aside-title"><?php esc_html_e( 'Radios at this site', 'wpamesh' ); ?></p>

        <?php
        foreach ( $radios as $radio ) :
            $info  = wpamesh_protocol_info( $radio['protocol'] );
            $link  = wpamesh_radio_link( $radio );
            $specs = array_filter( array(
                __( 'Short name', 'wpamesh' ) => esc_html( $radio['short_name'] ),
                __( 'Node ID', 'wpamesh' )    => $radio['node_id'] ? '<code>' . esc_html( $radio['node_id'] ) . '</code>' : '',
                __( 'Role', 'wpamesh' )       => esc_html( $radio['role'] ),
                __( 'Hardware', 'wpamesh' )   => esc_html( $radio['hardware'] ),
                __( 'Antenna', 'wpamesh' )    => is_numeric( $radio['antenna_gain'] ) ? esc_html( $radio['antenna_gain'] . ' dBi' ) : '',
            ) );
            ?>
            <div class="wpamesh-card wpamesh-radio">
                <div class="top">
                    <span class="title">
                        <?php echo wpamesh_protocol_chip( $radio['protocol'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>
                        <?php echo esc_html( trim( $info['name'] . ' ' . strtolower( wpamesh_role_label( $radio['role'] ) ) ) ); ?>
                    </span>
                </div>
                <?php if ( $specs ) : ?>
                    <dl class="wpamesh-specs">
                        <?php foreach ( $specs as $label => $value ) : ?>
                            <div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo $value; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></dd></div>
                        <?php endforeach; ?>
                    </dl>
                <?php endif; ?>
                <?php if ( $link ) : ?>
                    <a class="link" href="<?php echo esc_url( $link[0] ); ?>"><?php echo esc_html( $link[1] ); ?> →</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ( $details ) : ?>
        <p class="wpamesh-aside-title"><?php esc_html_e( 'Site details', 'wpamesh' ); ?></p>
        <div class="wpamesh-card wpamesh-site-details">
            <dl class="wpamesh-specs">
                <?php foreach ( $details as $label => $value ) : ?>
                    <div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo $value; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></dd></div>
                <?php endforeach; ?>
            </dl>
        </div>
    <?php endif; ?>
</aside>
