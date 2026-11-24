<?php
/**
 * All published sites grouped by tier, with a filter by network (assets/js/site-list.js).
 *
 * @package WPAMesh
 */

if ( ! wpamesh_sites_available() ) {
    return;
}

$sections = array(
    array(
        'title' => __( 'Core sites', 'wpamesh' ),
        'blurb' => __( 'Backbone sites that cover a county or more and link to other core sites.', 'wpamesh' ),
        'tiers' => array( 'core' ),
    ),
    array(
        'title' => __( 'Supplementary sites', 'wpamesh' ),
        'blurb' => __( 'Smaller coverage areas that connect otherwise isolated communities to a core site.', 'wpamesh' ),
        'tiers' => array( 'supplemental' ),
    ),
    array(
        'title' => __( 'Gateways & services', 'wpamesh' ),
        'blurb' => __( 'MQTT gateways, MeshCore observers, room servers and cross-mesh services.', 'wpamesh' ),
        'tiers' => array( 'gateway', 'service' ),
    ),
);

$sites = get_posts( array(
    'post_type'      => 'wpamesh_site',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
) );

$by_tier = array();
foreach ( $sites as $site ) {
    $by_tier[ (string) get_field( 'tier', $site->ID ) ][] = $site;
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wpamesh-site-list' ) ); ?>>
    <div class="wpamesh-infra-bar">
        <div class="wpamesh-filter" role="group" aria-label="<?php esc_attr_e( 'Filter by network', 'wpamesh' ); ?>">
            <button type="button" aria-pressed="true" data-filter="all"><?php esc_html_e( 'All networks', 'wpamesh' ); ?></button>
            <button type="button" aria-pressed="false" data-filter="meshcore" class="mc">MeshCore</button>
            <button type="button" aria-pressed="false" data-filter="meshtastic" class="mt">Meshtastic</button>
            <button type="button" aria-pressed="false" data-filter="reticulum" class="rns">Reticulum</button>
        </div>
    </div>

    <?php
    foreach ( $sections as $section ) :
        $members = array();
        foreach ( $section['tiers'] as $tier ) {
            $members = array_merge( $members, isset( $by_tier[ $tier ] ) ? $by_tier[ $tier ] : array() );
        }
        if ( ! $members ) {
            continue;
        }
        ?>
        <section class="wpamesh-infra-section">
            <div class="head"><h2><?php echo esc_html( $section['title'] ); ?></h2><span class="count"></span></div>
            <p class="blurb"><?php echo esc_html( $section['blurb'] ); ?></p>
            <div class="wpamesh-sites">
                <?php foreach ( $members as $site ) : ?>
                    <a class="wpamesh-card wpamesh-site" href="<?php echo esc_url( get_permalink( $site ) ); ?>" data-protos="<?php echo esc_attr( implode( ' ', wpamesh_site_protocols( $site->ID ) ) ); ?>">
                        <div class="top">
                            <div>
                                <div class="name"><?php echo esc_html( get_the_title( $site ) ); ?></div>
                                <div class="county"><?php echo esc_html( (string) get_field( 'county', $site->ID ) ); ?></div>
                            </div>
                        </div>
                        <div class="protos">
                            <?php foreach ( wpamesh_site_radios( $site->ID ) as $radio ) : ?>
                                <div class="proto">
                                    <?php echo wpamesh_protocol_chip( $radio['protocol'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper. ?>
                                    <?php echo esc_html( wpamesh_role_label( $radio['role'] ) ); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>
